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

/**
 * Named Binary Tag handling classes
 */
namespace pocketmine\nbt;

use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntArrayTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\LongTag;
use pocketmine\nbt\tag\NamedTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;

abstract class NBT{

	public const TAG_End = 0;
	public const TAG_Byte = 1;
	public const TAG_Short = 2;
	public const TAG_Int = 3;
	public const TAG_Long = 4;
	public const TAG_Float = 5;
	public const TAG_Double = 6;
	public const TAG_ByteArray = 7;
	public const TAG_String = 8;
	public const TAG_List = 9;
	public const TAG_Compound = 10;
	public const TAG_IntArray = 11;

	/**
	 * @param int             $type
	 * @param string          $name
	 * @param NbtStreamReader $reader
	 *
	 * @return NamedTag
	 * @throws NbtDataException
	 */
	public static function createTag(int $type, string $name, NbtStreamReader $reader) : NamedTag{
		switch($type){
			case self::TAG_Byte:
				return ByteTag::read($name, $reader);
			case self::TAG_Short:
				return ShortTag::read($name, $reader);
			case self::TAG_Int:
				return IntTag::read($name, $reader);
			case self::TAG_Long:
				return LongTag::read($name, $reader);
			case self::TAG_Float:
				return FloatTag::read($name, $reader);
			case self::TAG_Double:
				return DoubleTag::read($name, $reader);
			case self::TAG_ByteArray:
				return ByteArrayTag::read($name, $reader);
			case self::TAG_String:
				return StringTag::read($name, $reader);
			case self::TAG_List:
				return ListTag::read($name, $reader);
			case self::TAG_Compound:
				return CompoundTag::read($name, $reader);
			case self::TAG_IntArray:
				return IntArrayTag::read($name, $reader);
			default:
				throw new NbtDataException("Unknown NBT tag type $type");
		}
	}
}
