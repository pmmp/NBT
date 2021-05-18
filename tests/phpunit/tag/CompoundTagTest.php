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
use function array_fill;
use function str_repeat;


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
			self::assertMatchesRegularExpression('/hello[0-9]/', $name);
			self::assertInstanceOf(StringTag::class, $value);
			++$count;
		}

		self::assertEquals(10, $count);
	}

	/**
	 * Cloning a CompoundTag should clone all of its children
	 *
	 * @throws \Exception
	 */
	public function testClone() : void{
		$tag = CompoundTag::create()
			->setString("hello", "world")
			->setFloat("float", 5.5)
			->setTag("list", new ListTag());

		$tag2 = clone $tag;
		self::assertEquals($tag->getCount(), $tag2->getCount());

		foreach($tag2 as $name => $child){
			if($child instanceof ImmutableTag){
				self::assertSame($child, $tag->getTag($name));
			}else{
				self::assertNotSame($child, $tag->getTag($name));
			}
		}
	}

	/**
	 * Cloning a tag with a cyclic dependency should throw an exception
	 */
	public function testRecursiveClone() : void{
		//create recursive dependency
		$tag = new CompoundTag();
		$child = new CompoundTag();
		$child->setTag("parent", $tag);
		$tag->setTag("child", $child);

		$this->expectException(\RuntimeException::class);
		clone $tag; //recursive dependency, throw exception
	}

	public function testCountable() : void{
		$tag = new CompoundTag();
		for($i = 0; $i < 5; ++$i){
			$tag->setString("hello$i", "hello$i");
		}

		self::assertEquals(5, count($tag)); //don't use assertCount() because that allows iterators, which we don't want
	}

	/**
	 * Different object trees of tags should be considered the same if they hold the same data
	 * @throws \Exception
	 */
	public function testEquals() : void{
		$random = mt_rand();
		$prepare = function() use($random) : CompoundTag{
			$tag = new CompoundTag();

			for($i = 0; $i < 10; ++$i){
				$tag->setTag(
					"child$i",
					CompoundTag::create()
						->setString("test", "hello")
						->setIntArray("array", array_fill(0, 25, $random))
						->setTag("list", new ListTag([
							new ByteTag(1),
							new ByteTag(2)
						]))
				);
			}

			return $tag;
		};

		$tag1 = $prepare();
		$tag2 = $prepare();

		self::assertTrue($tag1->equals($tag2));

		$tag2->getCompoundTag("child9")->setFloat("hello", 1.0);
		self::assertNotTrue($tag1->equals($tag2));
	}

	public function testMerge() : void{
		$t1 = CompoundTag::create()
			->setString("test1", "test1")
			->setInt("test2", 2);
		$t2 = CompoundTag::create()
			->setString("test1", "replacement");

		$merged = $t1->merge($t2);
		self::assertSame("replacement", $merged->getString("test1"));
		self::assertCount(2, $merged);
		self::assertEquals(2, $merged->getInt("test2"));
	}

	public function testNumericStringKeys() : void{
		$t = new CompoundTag();
		for($i = 0; $i < 10; ++$i){
			$t->setTag("$i", new StringTag("$i"));
		}

		for($i = 0; $i < 10; ++$i){
			self::assertTrue($t->getTag("$i") !== null);
		}

		$check = 0;
		foreach($t as $k => $namedTag){
			self::assertTrue(is_string($k));
			self::assertSame("$check", $k); //must be identical, don't coerce key
			$check++;
		}
	}

	public function testTooManyConstructorArgs() : void{
		$this->expectException(\ArgumentCountError::class);

		new CompoundTag("hello");
	}

	/**
	 * Modification during iteration should not have any effect on iteration (similarly to how array iteration operates
	 * on a copy of the array instead of the array itself).
	 */
	public function testModificationDuringIteration() : void{
		$tag = CompoundTag::create();
		for($i = 0; $i < 10; ++$i){
			$tag->setString(str_repeat("a", $i), str_repeat("b", $i));
		}
		foreach($tag as $name => $value){
			$tag->removeTag($name);
		}
		self::assertCount(0, $tag);
	}

	//TODO: add more tests
}
