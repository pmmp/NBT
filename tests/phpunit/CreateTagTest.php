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

use PHPUnit\Framework\TestCase;
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

class CreateTagTest extends TestCase{

	/**
	 * Test that all known tag types can be deserialized
	 *
	 * @throws \Exception
	 */
	public function testCreateTags() : void{
		$root = new CompoundTag("compound", [
			new ByteTag("byte", 1),
			new ShortTag("short", 1),
			new IntTag("int", 1),
			new LongTag("long", 1),
			new FloatTag("float", 1),
			new DoubleTag("double", 1),
			new ByteArrayTag("bytearray", "\x01"),
			new StringTag("string", "string"),
			new ListTag("list", [new ByteTag("", 0)]),
			new IntArrayTag("intarray", [1])
		]);

		$dat = (new BigEndianNbtSerializer())->write($root);
		$root2 = (new BigEndianNbtSerializer())->read($dat);

		self::assertTrue($root->equals($root2));
	}
}
