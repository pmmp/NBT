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

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\utils\Binary;
use pocketmine\utils\BinaryDataException;
use pocketmine\utils\BinaryStream;
use function strlen;
use function zlib_decode;
use function zlib_encode;

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
	 * @return TreeRoot
	 *
	 * @throws BinaryDataException
	 * @throws NbtDataException
	 */
	private function readRoot() : TreeRoot{
		$type = $this->readByte();
		if($type !== NBT::TAG_Compound){
			throw new NbtDataException("Expected TAG_Compound at the start of buffer");
		}

		$rootName = $this->readString();
		return new TreeRoot(CompoundTag::read($this), $rootName);
	}

	/**
	 * Decodes NBT from the given binary string and returns it.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return TreeRoot
	 * @throws NbtDataException
	 */
	public function read(string $buffer, int &$offset = 0) : TreeRoot{
		$this->buffer->setBuffer($buffer, $offset);

		try{
			$data = $this->readRoot();
		}catch(BinaryDataException $e){
			throw new NbtDataException($e->getMessage(), 0, $e);
		}
		$offset = $this->buffer->getOffset();
		$this->buffer->reset();

		return $data;
	}

	/**
	 * Decodes a list of TAG_Compound into objects and returns them.
	 *
	 * TODO: This is only necessary because we don't have a streams API worth mentioning. Get rid of this in the future.
	 *
	 * @param string $buffer
	 *
	 * @return TreeRoot[]
	 * @throws NbtDataException
	 */
	public function readMultiple(string $buffer) : array{
		$this->buffer->setBuffer($buffer);

		$retval = [];

		while(!$this->buffer->feof()){
			try{
				$retval[] = $this->readRoot();
			}catch(BinaryDataException $e){
				throw new NbtDataException($e->getMessage(), 0, $e);
			}
		}

		$this->buffer->reset();

		return $retval;
	}

	/**
	 * Decodes NBT from the given compressed binary string and returns it. Anything decodable by zlib_decode() can be
	 * processed.
	 *
	 * TODO: This is only necessary because we don't have a streams API worth mentioning. Get rid of this in the future.
	 *
	 * @param string $buffer
	 *
	 * @return TreeRoot
	 * @throws NbtDataException
	 */
	public function readCompressed(string $buffer) : TreeRoot{
		$raw = @zlib_decode($buffer); //silence useless warning
		if($raw === false){
			throw new NbtDataException("Failed to decompress NBT data");
		}
		return $this->read($raw);
	}

	private function writeRoot(TreeRoot $root) : void{
		$this->writeByte(NBT::TAG_Compound);
		$this->writeString($root->getName());
		$root->getTag()->write($this);
	}

	/**
	 * @param TreeRoot $data
	 *
	 * @return string
	 */
	public function write(TreeRoot $data) : string{
		$this->buffer->reset();

		$this->writeRoot($data);

		return $this->buffer->getBuffer();
	}

	/**
	 * @param TreeRoot[] $data
	 *
	 * @return string
	 */
	public function writeMultiple(array $data) : string{
		$this->buffer->reset();
		foreach($data as $root){
			$this->writeRoot($root);
		}
		return $this->buffer->getBuffer();
	}

	/**
	 * @param TreeRoot $data
	 * @param int      $compression
	 * @param int      $level
	 *
	 * @return string
	 */
	public function writeCompressed(TreeRoot $data, int $compression = ZLIB_ENCODING_GZIP, int $level = 7) : string{
		return zlib_encode($this->write($data), $compression, $level);
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
		return $this->buffer->get($this->readInt());
	}

	public function writeByteArray(string $v) : void{
		$this->writeInt(strlen($v)); //TODO: overflow
		$this->buffer->put($v);
	}

	public function readString() : string{
		return $this->buffer->get($this->readShort());
	}

	/**
	 * @param string $v
	 * @throws \InvalidArgumentException if the string is too long
	 */
	public function writeString(string $v) : void{
		$len = strlen($v);
		if($len > 32767){
			throw new \InvalidArgumentException("NBT strings cannot be longer than 32767 bytes, got $len bytes");
		}
		$this->writeShort($len);
		$this->buffer->put($v);
	}
}
