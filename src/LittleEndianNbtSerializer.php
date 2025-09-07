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

use pmmp\encoding\LE;
use function count;

class LittleEndianNbtSerializer extends BaseNbtSerializer{

	public function readShort() : int{
		return LE::readUnsignedShort($this->reader);
	}

	public function readSignedShort() : int{
		return LE::readSignedShort($this->reader);
	}

	public function writeShort(int $v) : void{
		LE::writeUnsignedShort($this->writer, $v);
	}

	public function readInt() : int{
		return LE::readSignedInt($this->reader);
	}

	public function writeInt(int $v) : void{
		LE::writeSignedInt($this->writer, $v);
	}

	public function readLong() : int{
		return LE::readSignedLong($this->reader);
	}

	public function writeLong(int $v) : void{
		LE::writeSignedLong($this->writer, $v);
	}

	public function readFloat() : float{
		return LE::readFloat($this->reader);
	}

	public function writeFloat(float $v) : void{
		LE::writeFloat($this->writer, $v);
	}

	public function readDouble() : float{
		return LE::readDouble($this->reader);
	}

	public function writeDouble(float $v) : void{
		LE::writeDouble($this->writer, $v);
	}

	public function readIntArray() : array{
		$len = $this->readInt();
		if($len < 0){
			throw new NbtDataException("Array length cannot be less than zero ($len < 0)");
		}
		return LE::readSignedIntArray($this->reader, $len);
	}

	public function writeIntArray(array $array) : void{
		$this->writeInt(count($array));
		LE::writeSignedIntArray($this->writer, $array);
	}
}
