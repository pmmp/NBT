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

use pocketmine\utils\BinaryDataException;

/**
 * @internal
 */
interface NbtStreamReader{

	/**
	 * @throws BinaryDataException
	 */
	public function readByte() : int;

	/**
	 * @throws BinaryDataException
	 */
	public function readSignedByte() : int;

	/**
	 * @throws BinaryDataException
	 */
	public function readShort() : int;

	/**
	 * @throws BinaryDataException
	 */
	public function readSignedShort() : int;

	/**
	 * @throws BinaryDataException
	 */
	public function readInt() : int;

	/**
	 * @throws BinaryDataException
	 */
	public function readLong() : int;

	/**
	 * @throws BinaryDataException
	 */
	public function readFloat() : float;

	/**
	 * @throws BinaryDataException
	 */
	public function readDouble() : float;

	/**
	 * @throws BinaryDataException
	 */
	public function readByteArray() : string;

	/**
	 * @throws BinaryDataException
	 */
	public function readString() : string;

	/**
	 * @return int[]
	 * @throws BinaryDataException
	 */
	public function readIntArray() : array;
}
