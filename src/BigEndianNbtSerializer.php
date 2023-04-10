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

use function array_values;
use function assert;
use function count;
use function pack;
use function unpack;

class BigEndianNbtSerializer extends BaseNbtSerializer{

	public function readShort() : int{
		return $this->buffer->getShort();
	}

	public function readSignedShort() : int{
		return $this->buffer->getSignedShort();
	}

	public function writeShort(int $v) : void{
		$this->buffer->putShort($v);
	}

	public function readInt() : int{
		return $this->buffer->getInt();
	}

	public function writeInt(int $v) : void{
		$this->buffer->putInt($v);
	}

	public function readLong() : int{
		return $this->buffer->getLong();
	}

	public function writeLong(int $v) : void{
		$this->buffer->putLong($v);
	}

	public function readFloat() : float{
		return $this->buffer->getFloat();
	}

	public function writeFloat(float $v) : void{
		$this->buffer->putFloat($v);
	}

	public function readDouble() : float{
		return $this->buffer->getDouble();
	}

	public function writeDouble(float $v) : void{
		$this->buffer->putDouble($v);
	}

	public function readIntArray() : array{
		$len = $this->readInt();
		if($len < 0){
			throw new NbtDataException("Array length cannot be less than zero ($len < 0)");
		}
		$unpacked = unpack("N*", $this->buffer->get($len * 4));
		assert($unpacked !== false, "The formatting string is valid, and we gave a multiple of 4 bytes");
		return array_values($unpacked);
	}

	public function writeIntArray(array $array) : void{
		$this->writeInt(count($array));
		$this->buffer->put(pack("N*", ...$array));
	}
}
