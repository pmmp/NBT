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

/**
 * @internal
 */
interface NbtStreamWriter{

	public function writeByte(int $v) : void;

	public function writeShort(int $v) : void;

	public function writeInt(int $v) : void;

	public function writeLong(int $v) : void;

	public function writeFloat(float $v) : void;

	public function writeDouble(float $v) : void;

	public function writeByteArray(string $v) : void;

	/**
	 * @throws \InvalidArgumentException if the string is too long
	 */
	public function writeString(string $v) : void;

	/**
	 * @param int[] $array
	 */
	public function writeIntArray(array $array) : void;
}
