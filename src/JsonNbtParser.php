<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\nbt;

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use pocketmine\utils\BinaryDataException;
use pocketmine\utils\BinaryStream;
use function is_numeric;
use function strpos;
use function strtolower;
use function substr;
use function trim;

class JsonNbtParser{

	/**
	 * Parses JSON-formatted NBT into a CompoundTag and returns it. Used for parsing tags supplied with the /give command.
	 *
	 * @throws NbtDataException
	 */
	public static function parseJson(string $data) : CompoundTag{
		$stream = new BinaryStream(trim($data, " \r\n\t"));

		try{
			if(($b = $stream->get(1)) !== "{"){
				throw new NbtDataException("Syntax error: expected compound start but got '$b'");
			}
			$ret = self::parseCompound($stream); //don't return directly, syntax needs to be validated
		}catch(NbtDataException $e){
			throw new NbtDataException($e->getMessage() . " at offset " . $stream->getOffset());
		}catch(BinaryDataException $e){
			throw new NbtDataException("Syntax error: " . $e->getMessage() . " at offset " . $stream->getOffset());
		}
		if(!$stream->feof()){
			throw new NbtDataException("Syntax error: unexpected trailing characters after end of tag: " . $stream->getRemaining());
		}

		return $ret;
	}

	/**
	 * @throws BinaryDataException
	 * @throws NbtDataException
	 */
	private static function parseList(BinaryStream $stream) : ListTag{
		$retval = new ListTag();

		if(self::skipWhitespace($stream, "]")){
			while(!$stream->feof()){
				try{
					$value = self::readValue($stream);
				}catch(InvalidTagValueException $e){
					throw new NbtDataException("Data error: " . $e->getMessage());
				}
				$expectedType = $retval->getTagType();
				if($expectedType !== NBT::TAG_End && $expectedType !== $value->getType()){
					throw new NbtDataException("Data error: lists can only contain one type of value");
				}
				$retval->push($value);
				if(self::readBreak($stream, "]")){
					return $retval;
				}
			}

			throw new NbtDataException("Syntax error: unexpected end of stream");
		}

		return $retval;
	}

	/**
	 * @throws BinaryDataException
	 * @throws NbtDataException
	 */
	private static function parseCompound(BinaryStream $stream) : CompoundTag{
		$retval = new CompoundTag();

		if(self::skipWhitespace($stream, "}")){
			while(!$stream->feof()){
				$k = self::readKey($stream);
				if($retval->getTag($k) !== null){
					throw new NbtDataException("Syntax error: duplicate compound leaf node '$k'");
				}
				try{
					$retval->setTag($k, self::readValue($stream));
				}catch(InvalidTagValueException $e){
					throw new NbtDataException("Data error: " . $e->getMessage());
				}

				if(self::readBreak($stream, "}")){
					return $retval;
				}
			}

			throw new NbtDataException("Syntax error: unexpected end of stream");
		}

		return $retval;
	}

	/**
	 * @throws BinaryDataException
	 * @throws NbtDataException
	 */
	private static function skipWhitespace(BinaryStream $stream, string $terminator) : bool{
		while(!$stream->feof()){
			$b = $stream->get(1);
			if($b === $terminator){
				return false;
			}
			if($b === " " or $b === "\n" or $b === "\t" or $b === "\r"){
				continue;
			}

			$stream->setOffset($stream->getOffset() - 1);
			return true;
		}

		throw new NbtDataException("Syntax error: unexpected end of stream, expected start of key");
	}

	/**
	 * @return bool true if terminator has been found, false if comma was found
	 * @throws BinaryDataException
	 * @throws NbtDataException
	 */
	private static function readBreak(BinaryStream $stream, string $terminator) : bool{
		if($stream->feof()){
			throw new NbtDataException("Syntax error: unexpected end of stream, expected '$terminator'");
		}
		$offset = $stream->getOffset();
		$c = $stream->get(1);
		if($c === ","){
			return false;
		}
		if($c === $terminator){
			return true;
		}

		throw new NbtDataException("Syntax error: unexpected '$c' end at offset $offset");
	}

	/**
	 * @throws BinaryDataException
	 * @throws NbtDataException
	 * @throws InvalidTagValueException
	 */
	private static function readValue(BinaryStream $stream) : Tag{
		$value = "";
		$inQuotes = false;

		$offset = $stream->getOffset();

		$foundEnd = false;

		/** @var Tag|null $retval */
		$retval = null;

		while(!$stream->feof()){
			$offset = $stream->getOffset();
			$c = $stream->get(1);

			if($inQuotes){ //anything is allowed inside quotes, except unescaped quotes
				if($c === '"'){
					$inQuotes = false;
					$retval = new StringTag($value);
					$foundEnd = true;
				}elseif($c === "\\"){
					$value .= $stream->get(1);
				}else{
					$value .= $c;
				}
			}else{
				if($c === "," or $c === "}" or $c === "]"){ //end of parent tag
					$stream->setOffset($stream->getOffset() - 1); //the caller needs to be able to read this character
					$foundEnd = true;
					break;
				}

				if($value === "" or $foundEnd){
					if($c === "\r" or $c === "\n" or $c === "\t" or $c === " "){ //leading or trailing whitespace, ignore it
						continue;
					}

					if($foundEnd){ //unexpected non-whitespace character after end of value
						throw new NbtDataException("Syntax error: unexpected '$c' after end of value at offset $offset");
					}
				}

				if($c === '"'){ //start of quoted string
					if($value !== ""){
						throw new NbtDataException("Syntax error: unexpected quote at offset $offset");
					}
					$inQuotes = true;

				}elseif($c === "{"){ //start of compound tag
					if($value !== ""){
						throw new NbtDataException("Syntax error: unexpected compound start at offset $offset (enclose in double quotes for literal)");
					}

					$retval = self::parseCompound($stream);
					$foundEnd = true;

				}elseif($c === "["){ //start of list tag - TODO: arrays
					if($value !== ""){
						throw new NbtDataException("Syntax error: unexpected list start at offset $offset (enclose in double quotes for literal)");
					}

					$retval = self::parseList($stream);
					$foundEnd = true;

				}else{ //any other character
					$value .= $c;
				}
			}
		}

		if($retval !== null){
			return $retval;
		}

		if($value === ""){
			throw new NbtDataException("Syntax error: empty value at offset $offset");
		}
		if(!$foundEnd){
			throw new NbtDataException("Syntax error: unexpected end of stream at offset $offset");
		}

		$last = strtolower(substr($value, -1));
		$part = substr($value, 0, -1);

		if($last !== "b" and $last !== "s" and $last !== "l" and $last !== "f" and $last !== "d"){
			$part = $value;
			$last = null;
		}

		if(is_numeric($part)){
			if($last === "f" or $last === "d" or strpos($part, ".") !== false or strpos($part, "e") !== false){ //e = scientific notation
				$value = (float) $part;
				switch($last){
					case "d":
						return new DoubleTag($value);
					case "f":
					default:
						return new FloatTag($value);
				}
			}else{
				$value = (int) $part;
				switch($last){
					case "b":
						return new ByteTag($value);
					case "s":
						return new ShortTag($value);
					case "l":
						return new LongTag($value);
					default:
						return new IntTag($value);
				}
			}
		}else{
			return new StringTag($value);
		}
	}

	/**
	 * @throws BinaryDataException
	 * @throws NbtDataException
	 */
	private static function readKey(BinaryStream $stream) : string{
		$key = "";
		$offset = $stream->getOffset();

		$inQuotes = false;
		$foundEnd = false;

		while(!$stream->feof()){
			$c = $stream->get(1);

			if($inQuotes){
				if($c === '"'){
					$inQuotes = false;
					$foundEnd = true;
				}elseif($c === "\\"){
					$key .= $stream->get(1);
				}else{
					$key .= $c;
				}
			}else{
				if($c === ":"){
					$foundEnd = true;
					break;
				}

				if($key === "" or $foundEnd){
					if($c === "\r" or $c === "\n" or $c === "\t" or $c === " "){ //leading or trailing whitespace, ignore it
						continue;
					}

					if($foundEnd){ //unexpected non-whitespace character after end of value
						throw new NbtDataException("Syntax error: unexpected '$c' after end of value at offset $offset");
					}
				}

				if($c === '"'){ //start of quoted string
					if($key !== ""){
						throw new NbtDataException("Syntax error: unexpected quote at offset $offset");
					}
					$inQuotes = true;

				}elseif($c === "{" or $c === "}" or $c === "[" or $c === "]" or $c === ","){
					throw new NbtDataException("Syntax error: unexpected '$c' at offset $offset (enclose in double quotes for literal)");
				}else{ //any other character
					$key .= $c;
				}
			}
		}

		if($key === ""){
			throw new NbtDataException("Syntax error: invalid empty key at offset $offset");
		}
		if(!$foundEnd){
			throw new NbtDataException("Syntax error: unexpected end of stream at offset $offset");
		}

		return $key;
	}
}
