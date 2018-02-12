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

namespace pocketmine\nbt\tag;

use PHPUnit\Framework\TestCase;


class CompoundTagTest extends TestCase{

	public function testIteration() : void{
		$tag = new CompoundTag();

		for($i = 0; $i < 10; ++$i){
			$tag->setString("hello$i", "$i");
		}

		$count = 0;
		/**
		 * @var string $name
		 * @var StringTag $value
		 */
		foreach($tag as $name => $value){
			// we used to get other stuff when iterating, like the tag's __name property (before Iterator was implemented by CompoundTag)
			self::assertRegExp('/hello[0-9]/', $name);
			self::assertInstanceOf(StringTag::class, $value);
			++$count;
		}

		self::assertEquals(10, $count);
	}

	public function testSetValue() : void{
		$tag = new CompoundTag();
		for($i = 0; $i < 10; ++$i){
			$tag->setString("hello$i", "$i");
		}
		self::assertCount(10, $tag);


		$newValue = [];
		for($i = 0; $i < 3; ++$i){
			$newValue[] = new StringTag("test$i", "$i");
		}
		$tag->setValue($newValue);
		self::assertCount(3, $tag);
	}

	/**
	 * $tag[] = $value is not allowed on CompoundTags
	 */
	public function testAppendSyntax() : void{
		$this->expectException(\InvalidArgumentException::class);

		$tag = new CompoundTag();
		$tag[] = new StringTag("test", "tag");
	}

	/**
	 * Cloning a CompoundTag should clone all of its children
	 *
	 * @throws \Exception
	 */
	public function testClone() : void{
		$tag = new CompoundTag();
		$tag->setString("hello", "world");
		$tag->setFloat("float", 5.5);
		$tag->setTag(new ListTag("list"));

		$tag2 = clone $tag;
		self::assertEquals($tag->getCount(), $tag2->getCount());

		foreach($tag2 as $name => $child){
			self::assertNotSame($child, $tag->getTag($name));
		}
	}

	//TODO: add more tests
}
