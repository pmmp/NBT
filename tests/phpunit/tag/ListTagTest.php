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
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NBT;
use pocketmine\nbt\TreeRoot;
use function array_fill;
use function array_key_first;
use function array_keys;
use function array_map;

class ListTagTest extends TestCase{

	public function testConstructorValues() : void{
		$array = [];

		for($i = 0; $i < 5; ++$i){
			$array[] = new StringTag("test$i");
		}

		$list = new ListTag($array);

		self::assertEquals(NBT::TAG_String, $list->getTagType());
		self::assertCount(5, $list);
	}

	/**
	 * Empty lists will have their type auto-detected when something is inserted
	 * @throws \Exception
	 */
	public function testTypeDetection() : void{
		$list = new ListTag([], NBT::TAG_End);
		$list->push(new StringTag("works"));

		self::assertEquals(NBT::TAG_String, $list->getTagType(), "Adding a tag to an empty list should change its type to match the inserted tag");
	}

	/**
	 * Lists with a pre-set type can't have other tag types added to them
	 */
	public function testAddWrongTypeEmptyList() : void{
		$list = new ListTag([], NBT::TAG_Compound);
		$list->push(new StringTag("works"));

		self::assertEquals(NBT::TAG_String, $list->getTagType(), "Empty list type should change to match inserted values");
	}

	/**
	 * Cloning a list should clone all of its children
	 *
	 * @throws \Exception
	 */
	public function testClone() : void{
		$tag = new ListTag();
		for($i = 0; $i < 5; ++$i){
			$tag->push(new StringTag("hi"));
		}

		$tag2 = clone $tag;
		self::assertEquals($tag->getCount(), $tag2->getCount());

		foreach($tag2 as $index => $child){
			if($child instanceof ImmutableTag){
				self::assertSame($child, $tag->get($index));
			}else{
				self::assertNotSame($child, $tag->get($index));
			}
		}
	}

	/**
	 * Cloning a tag with a cyclic dependency should throw an exception
	 */
	public function testRecursiveClone() : void{
		//create recursive dependency
		$tag = new ListTag();
		$child = new ListTag();
		$child->push($tag);
		$tag->push($child);

		$this->expectException(\RuntimeException::class);
		clone $tag; //recursive dependency, throw exception
	}

	public function testTooManyConstructorArgs() : void{
		$this->expectException(\ArgumentCountError::class);

		new ListTag([new IntTag(1)], NBT::TAG_End, "world");
	}

	/**
	 * Modification during iteration should not have any effect on iteration (similarly to how array iteration operates
	 * on a copy of the array instead of the array itself).
	 */
	public function testModificationDuringIteration() : void{
		$tag = new ListTag(array_map(function(int $v) : IntTag{
			return new IntTag($v);
		}, array_fill(0, 10, 0)));

		foreach($tag as $k => $v){
			$tag->remove(0); //remove the first tag, all following tags shift down by one
		}
		//if we iterated by-ref, entries are likely to have been skipped
		self::assertCount(0, $tag);
	}

	public function testInsert() : void{
		$list = new ListTag();
		$list->push(new IntTag(0));

		$list->insert(1, new IntTag(2));
		$list->insert(1, new IntTag(1)); //displaces int(2)

		self::assertSame([0, 1, 2], $list->getAllValues());
		self::assertSame([0, 1, 2], array_keys($list->getValue()), "Key order should be consecutive");
	}

	public function testDelete() : void{
		$list = new ListTag();
		foreach(range(0, 2) as $value){
			$list->push(new IntTag($value));
		}
		$list->remove(1);
		self::assertSame([0, 2], $list->getAllValues());
		self::assertSame([0, 1], array_keys($list->getValue()));
	}

	/**
	 * Tests that empty lists remember their original type from deserialization
	 * Previously we were discarding these, creating problems for read/write integrity testing
	 */
	public function testEmptyBinarySymmetry() : void{
		$list = new ListTag([], NBT::TAG_Byte);

		$serializer = new BigEndianNbtSerializer();
		$list2 = $serializer->read($serializer->write(new TreeRoot($list)))->getTag();

		self::assertInstanceOf(ListTag::class, $list2);
		self::assertSame($list->getTagType(), $list2->getTagType());
		self::assertSame($list->getCount(), $list2->getCount());
	}

	public function testEquals() : void{
		$list1 = new ListTag([new IntTag(1)]);
		$list2 = new ListTag([new IntTag(1)]);

		self::assertTrue($list1->equals($list2));
		self::assertTrue($list2->equals($list1));

		$extraValue = new ListTag([new IntTag(1), new IntTag(2)]);
		self::assertFalse($list1->equals($extraValue));
		self::assertFalse($extraValue->equals($list1));

		$differentValue = new ListTag([new IntTag(2)]);
		self::assertFalse($list1->equals($differentValue));
		self::assertFalse($differentValue->equals($list1));
	}
}
