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

use pocketmine\nbt\tag\Tag;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryDataException;
use pocketmine\utils\BinaryStream;
use function strlen;

/**
 * Base Named Binary Tag encoder/decoder
 */
abstract class BaseNbtSerializer implements NbtStreamReader, NbtStreamWriter{
	/** @var BinaryStream */
	protected $buffer;

	public function __construct(){
		$this->buffer = new BinaryStream();
	}

	/**
	 * @throws BinaryDataException
	 * @throws NbtDataException
	 */
	private function readRoot(int $maxDepth) : TreeRoot{
		$type = $this->readByte();
		if($type === NBT::TAG_End){
			throw new NbtDataException("Found TAG_End at the start of buffer");
		}

		$rootName = $this->readString();
		return new TreeRoot(NBT::createTag($type, $this, new ReaderTracker($maxDepth)), $rootName);
	}

	/**
	 * Decodes NBT from the given binary string and returns it.
	 *
	 * @param int    $offset reference parameter
	 *
	 * @throws NbtDataException
	 */
	public function read(string $buffer, int &$offset = 0, int $maxDepth = 0) : TreeRoot{
		$this->buffer = new BinaryStream($buffer, $offset);

		try{
			$data = $this->readRoot($maxDepth);
		}catch(BinaryDataException $e){
			throw new NbtDataException($e->getMessage(), 0, $e);
		}
		$offset = $this->buffer->getOffset();

		return $data;
	}

	/**
	 * Reads a tag without a header from the buffer and returns it. The tag does not have a name, and the type is not
	 * specified by the binary data. Only the tag's raw binary value is present. This could be used if the expected root
	 * type is always the same.
	 *
	 * This format is not usually seen in the wild, but it is used in some places in the Minecraft: Bedrock network
	 * protocol.
	 *
	 * @throws NbtDataException
	 */
	public function readHeadless(string $buffer, int $rootType, int &$offset = 0, int $maxDepth = 0) : Tag{
		$this->buffer = new BinaryStream($buffer, $offset);

		$data = NBT::createTag($rootType, $this, new ReaderTracker($maxDepth));
		$offset = $this->buffer->getOffset();

		return $data;
	}

	/**
	 * Decodes a list of NBT tags into objects and returns them.
	 *
	 * TODO: This is only necessary because we don't have a streams API worth mentioning. Get rid of this in the future.
	 *
	 * @return TreeRoot[]
	 * @throws NbtDataException
	 */
	public function readMultiple(string $buffer, int $maxDepth = 0) : array{
		$this->buffer = new BinaryStream($buffer);

		$retval = [];

		while(!$this->buffer->feof()){
			try{
				$retval[] = $this->readRoot($maxDepth);
			}catch(BinaryDataException $e){
				throw new NbtDataException($e->getMessage(), 0, $e);
			}
		}

		return $retval;
	}

	private function writeRoot(TreeRoot $root) : void{
		$this->writeByte($root->getTag()->getType());
		$this->writeString($root->getName());
		$root->getTag()->write($this);
	}

	public function write(TreeRoot $data) : string{
		$this->buffer = new BinaryStream();

		$this->writeRoot($data);

		return $this->buffer->getBuffer();
	}

	/**
	 * Writes a nameless tag without any header information. The reader of the data must know what type to expect, as
	 * it is not specified in the data.
	 *
	 * @see BaseNbtSerializer::readHeadless()
	 */
	public function writeHeadless(Tag $data) : string{
		$this->buffer = new BinaryStream();
		$data->write($this);
		return $this->buffer->getBuffer();
	}

	/**
	 * @param TreeRoot[] $data
	 */
	public function writeMultiple(array $data) : string{
		$this->buffer = new BinaryStream();
		foreach($data as $root){
			$this->writeRoot($root);
		}
		return $this->buffer->getBuffer();
	}

	public function readByte() : int{
		return $this->buffer->getByte();
	}

	public function readSignedByte() : int{
		return Binary::signByte($this->buffer->getByte());
	}

	public function writeByte(int $v) : void{
		$this->buffer->putByte($v);
	}

	public function readByteArray() : string{
		$length = $this->readInt();
		if($length < 0){
			throw new NbtDataException("Array length cannot be less than zero ($length < 0)");
		}
		return $this->buffer->get($length);
	}

	public function writeByteArray(string $v) : void{
		$this->writeInt(strlen($v)); //TODO: overflow
		$this->buffer->put($v);
	}

	/**
	 * @throws NbtDataException
	 */
	protected static function checkReadStringLength(int $len) : int{
		if($len > 32767){
			throw new NbtDataException("NBT string length too large ($len > 32767)");
		}
		return $len;
	}

	/**
	 * @throws \InvalidArgumentException
	 */
	protected static function checkWriteStringLength(int $len) : int{
		if($len > 32767){
			throw new \InvalidArgumentException("NBT string length too large ($len > 32767)");
		}
		return $len;
	}

	public function readString() : string{
		return $this->buffer->get(self::checkReadStringLength($this->readShort()));
	}

	/**
	 * @throws \InvalidArgumentException if the string is too long
	 */
	public function writeString(string $v) : void{
		$this->writeShort(self::checkWriteStringLength(strlen($v)));
		$this->buffer->put($v);
	}
}
