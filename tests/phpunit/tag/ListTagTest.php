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

namespace pocketmine\nbt\tag;

use PHPUnit\Framework\TestCase;
use pocketmine\nbt\NBT;

class ListTagTest extends TestCase{

	public function testConstructorValues() : void{
		$array = [];

		for($i = 0; $i < 5; ++$i){
			$array[] = new StringTag("", "test$i");
		}

		$list = new ListTag("jelly beans", $array);

		self::assertEquals("jelly beans", $list->getName());
		self::assertEquals(NBT::TAG_String, $list->getTagType());
		self::assertCount(5, $list);
	}

	/**
	 * Lists of TAG_End will have their type auto-detected when something is inserted
	 * @throws \Exception
	 */
	public function testTypeDetection() : void{
		$list = new ListTag("test", [], NBT::TAG_End);
		$list->push(new StringTag("", "works"));

		self::assertEquals(NBT::TAG_String, $list->getTagType(), "Adding a tag to an empty list of TAG_End type should change its type");
	}

	/**
	 * Lists with a pre-set type can't have other tag types added to them
	 */
	public function testAddWrongTypeEmptyList() : void{
		$this->expectException(\TypeError::class);

		$list = new ListTag("test2", [], NBT::TAG_Compound);
		$list->push(new StringTag("", "shouldn't work"));
	}

	/**
	 * Empty lists can have their tag changed manually, no matter what type they are
	 */
	public function testSetEmptyListType() : void{
		$list = new ListTag("test3", [], NBT::TAG_String);

		$list->setTagType(NBT::TAG_Compound);
		$list->push(new CompoundTag());
		self::assertCount(1, $list);

		$list->shift(); //empty the list

		//once it's empty, we can set its type again
		$list->setTagType(NBT::TAG_Byte);
		$list->push(new ByteTag("", 0));
		self::assertCount(1, $list);
	}

	/**
	 * Non-empty lists should not be able to have their types changed
	 */
	public function testSetNotEmptyListType() : void{
		$this->expectException(\LogicException::class);

		$list = new ListTag("test4");
		$list->push(new StringTag("", "string"));

		$list->setTagType(NBT::TAG_Compound);
	}


	/**
	 * Cloning a list should clone all of its children
	 *
	 * @throws \Exception
	 */
	public function testClone() : void{
		$tag = new ListTag();
		for($i = 0; $i < 5; ++$i){
			$tag->push(new StringTag("", "hi"));
		}

		$tag2 = clone $tag;
		self::assertEquals($tag->getCount(), $tag2->getCount());

		foreach($tag2 as $index => $child){
			self::assertNotSame($child, $tag->get($index));
		}
	}
}
