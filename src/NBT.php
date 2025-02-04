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
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\Tag;
use function is_array;
use function is_float;
use function is_int;
use function is_string;

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
	 * @return mixed
	 * @throws NbtDataException
	 */
	public static function readValue(int $type, NbtStreamReader $reader, ReaderTracker $tracker){
		return match ($type) {
			self::TAG_Byte => $reader->readSignedByte(),
			self::TAG_Short => $reader->readSignedShort(),
			self::TAG_Int => $reader->readInt(),
			self::TAG_Long => $reader->readLong(),
			self::TAG_Float => $reader->readFloat(),
			self::TAG_Double => $reader->readDouble(),
			self::TAG_ByteArray => $reader->readByteArray(),
			self::TAG_String => $reader->readString(),
			self::TAG_List => ListTag::read($reader, $tracker),
			self::TAG_Compound => CompoundTag::read($reader, $tracker),
			self::TAG_IntArray => $reader->readIntArray(),
			default => throw new \LogicException("Invalid tag type $type"),
		};
	}

	public static function writeValue(int $type, mixed $value, NbtStreamWriter $writer) : void{
		match (true) {
			is_int($value) => match ($type) {
				self::TAG_Byte => $writer->writeByte($value),
				self::TAG_Short => $writer->writeShort($value),
				self::TAG_Int => $writer->writeInt($value),
				self::TAG_Long => $writer->writeLong($value),
				default => throw new \LogicException("Didn't expect an int value for tag type $type"),
			},
			is_float($value) => match ($type) {
				self::TAG_Float => $writer->writeFloat($value),
				self::TAG_Double => $writer->writeDouble($value),
				default => throw new \LogicException("Didn't expect a float value for tag type $type"),
			},
			is_string($value) => match ($type) {
				self::TAG_ByteArray => $writer->writeByteArray($value),
				self::TAG_String => $writer->writeString($value),
				default => throw new \LogicException("Didn't expect a string value for tag type $type"),
			},
			is_array($value) => match ($type) {
				self::TAG_IntArray => $writer->writeIntArray($value),
				default => throw new \LogicException("Didn't expect an array value for tag type $type"),
			},
			$value instanceof CompoundTag || $value instanceof ListTag => $value->write($writer),
			default => throw new \LogicException("Invalid tag type $type"),
		};
	}

	public static function boxValue(int $type, mixed $value) : Tag{
		return match (true) {
			is_int($value) => match ($type) {
				self::TAG_Byte => new ByteTag($value),
				self::TAG_Short => new ShortTag($value),
				self::TAG_Int => new IntTag($value),
				self::TAG_Long => new LongTag($value),
				default => throw new \LogicException("Didn't expect an int value for tag type $type"),
			},
			is_float($value) => match ($type) {
				self::TAG_Float => new FloatTag($value),
				self::TAG_Double => new DoubleTag($value),
				default => throw new \LogicException("Didn't expect a float value for tag type $type"),
			},
			is_string($value) => match ($type) {
				self::TAG_ByteArray => new ByteArrayTag($value),
				self::TAG_String => new StringTag($value),
				default => throw new \LogicException("Didn't expect a string value for tag type $type"),
			},
			is_array($value) => match ($type) {
				self::TAG_IntArray => new IntArrayTag($value),
				default => throw new \LogicException("Didn't expect an array value for tag type $type"),
			},
			$value instanceof CompoundTag || $value instanceof ListTag => $value,
			default => throw new \LogicException("Invalid tag type $type"),
		};
	}

	public static function unboxValue(Tag $tag) : mixed{
		return $tag instanceof CompoundTag || $tag instanceof ListTag ? $tag : $tag->getValue();
	}

	public static function getClass(int $type) : string{
		return match ($type) {
			self::TAG_Byte => ByteTag::class,
			self::TAG_Short => ShortTag::class,
			self::TAG_Int => IntTag::class,
			self::TAG_Long => LongTag::class,
			self::TAG_Float => FloatTag::class,
			self::TAG_Double => DoubleTag::class,
			self::TAG_ByteArray => ByteArrayTag::class,
			self::TAG_String => StringTag::class,
			self::TAG_List => ListTag::class,
			self::TAG_Compound => CompoundTag::class,
			self::TAG_IntArray => IntArrayTag::class,
			default => throw new \LogicException("Invalid tag type $type"),
		};
	}
}
