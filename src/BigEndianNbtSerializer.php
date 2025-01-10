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

use pmmp\encoding\BE;
use function array_values;
use function assert;
use function count;
use function pack;
use function unpack;

class BigEndianNbtSerializer extends BaseNbtSerializer{

	public function readShort() : int{
		return BE::readUnsignedShort($this->buffer);
	}

	public function readSignedShort() : int{
		return BE::readSignedShort($this->buffer);
	}

	public function writeShort(int $v) : void{
		BE::writeUnsignedShort($this->buffer, $v);
	}

	public function readInt() : int{
		return BE::readSignedInt($this->buffer);
	}

	public function writeInt(int $v) : void{
		BE::writeSignedInt($this->buffer, $v);
	}

	public function readLong() : int{
		return BE::readSignedLong($this->buffer);
	}

	public function writeLong(int $v) : void{
		BE::writeSignedLong($this->buffer, $v);
	}

	public function readFloat() : float{
		return BE::readFloat($this->buffer);
	}

	public function writeFloat(float $v) : void{
		BE::writeFloat($this->buffer, $v);
	}

	public function readDouble() : float{
		return BE::readDouble($this->buffer);
	}

	public function writeDouble(float $v) : void{
		BE::writeDouble($this->buffer, $v);
	}

	public function readIntArray() : array{
		$len = $this->readInt();
		if($len < 0){
			throw new NbtDataException("Array length cannot be less than zero ($len < 0)");
		}
		/** @var array<int>|false $unpacked */
		$unpacked = unpack("N*", $this->buffer->readByteArray($len * 4));
		assert($unpacked !== false, "The formatting string is valid, and we gave a multiple of 4 bytes");
		return array_values($unpacked);
	}

	public function writeIntArray(array $array) : void{
		$this->writeInt(count($array));
		$this->buffer->writeByteArray(pack("N*", ...$array));
	}
}
