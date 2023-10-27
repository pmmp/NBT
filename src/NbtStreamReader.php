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

use pmmp\encoding\DataDecodeException;

/**
 * @internal
 */
interface NbtStreamReader{

	/**
	 * @throws DataDecodeException
	 */
	public function readByte() : int;

	/**
	 * @throws DataDecodeException
	 */
	public function readSignedByte() : int;

	/**
	 * @throws DataDecodeException
	 */
	public function readShort() : int;

	/**
	 * @throws DataDecodeException
	 */
	public function readSignedShort() : int;

	/**
	 * @throws DataDecodeException
	 */
	public function readInt() : int;

	/**
	 * @throws DataDecodeException
	 */
	public function readLong() : int;

	/**
	 * @throws DataDecodeException
	 */
	public function readFloat() : float;

	/**
	 * @throws DataDecodeException
	 */
	public function readDouble() : float;

	/**
	 * @throws DataDecodeException
	 */
	public function readByteArray() : string;

	/**
	 * @throws DataDecodeException
	 */
	public function readString() : string;

	/**
	 * @return int[]
	 * @throws DataDecodeException
	 */
	public function readIntArray() : array;
}
