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

use pocketmine\nbt\NBT;
use pocketmine\nbt\NbtStreamReader;
use pocketmine\nbt\NbtStreamWriter;
use pocketmine\nbt\NoSuchTagException;
use pocketmine\nbt\ReaderTracker;
use pocketmine\nbt\UnexpectedTagTypeException;
use function count;
use function func_num_args;
use function get_class;
use function is_int;
use function str_repeat;
use function strval;

/**
 * @phpstan-implements \IteratorAggregate<string, Tag>
 */
final class CompoundTag extends Tag implements \Countable, \IteratorAggregate{
	use NoDynamicFieldsTrait;

	/** @var Tag[] */
	private $value = [];

	public function __construct(){
		self::restrictArgCount(__METHOD__, func_num_args(), 0);
	}

	/**
	 * Helper method for easier fluent usage.
	 */
	public static function create() : self{
		return new self;
	}

	public function count() : int{
		return count($this->value);
	}

	/**
	 * @return int
	 */
	public function getCount(){
		return count($this->value);
	}

	/**
	 * @return Tag[]
	 */
	public function getValue(){
		return $this->value;
	}

	/*
	 * Here follows many functions of misery for the sake of type safety. We really needs generics in PHP :(
	 */

	/**
	 * Returns the tag with the specified name, or null if it does not exist.
	 */
	public function getTag(string $name) : ?Tag{
		return $this->value[$name] ?? null;
	}

	/**
	 * Returns the ListTag with the specified name, or null if it does not exist. Triggers an exception if a tag exists
	 * with that name and the tag is not a ListTag.
	 */
	public function getListTag(string $name) : ?ListTag{
		$tag = $this->getTag($name);
		if($tag !== null && !($tag instanceof ListTag)){
			throw new UnexpectedTagTypeException("Expected a tag of type " . ListTag::class . ", got " . get_class($tag));
		}
		return $tag;
	}

	/**
	 * Returns the CompoundTag with the specified name, or null if it does not exist. Triggers an exception if a tag
	 * exists with that name and the tag is not a CompoundTag.
	 */
	public function getCompoundTag(string $name) : ?CompoundTag{
		$tag = $this->getTag($name);
		if($tag !== null && !($tag instanceof CompoundTag)){
			throw new UnexpectedTagTypeException("Expected a tag of type " . CompoundTag::class . ", got " . get_class($tag));
		}
		return $tag;
	}

	/**
	 * Sets the specified Tag as a child tag of the CompoundTag at the offset specified by the tag's name.
	 *
	 * @return $this
	 */
	public function setTag(string $name, Tag $tag) : self{
		$this->value[$name] = $tag;
		return $this;
	}

	/**
	 * Removes the child tags with the specified names from the CompoundTag. This function accepts a variadic list of
	 * strings.
	 */
	public function removeTag(string ...$names) : void{
		foreach($names as $name){
			unset($this->value[$name]);
		}
	}

	/**
	 * Returns the value of the child tag with the specified name, or $default if the tag doesn't exist. If the child
	 * tag is not of type $expectedType, an exception will be thrown.
	 *
	 * @param mixed  $default
	 *
	 * @phpstan-template T of Tag
	 * @phpstan-param class-string<T> $expectedClass
	 *
	 * @return mixed
	 *
	 * @throws UnexpectedTagTypeException
	 * @throws NoSuchTagException
	 */
	private function getTagValue(string $name, string $expectedClass, $default = null){
		$tag = $this->getTag($name);
		if($tag instanceof $expectedClass){
			return $tag->getValue();
		}
		if($tag !== null){
			throw new UnexpectedTagTypeException("Expected a tag of type $expectedClass, got " . get_class($tag));
		}

		if($default === null){
			throw new NoSuchTagException("Tag \"$name\" does not exist");
		}

		return $default;
	}

	/*
	 * The following methods are wrappers around getTagValue() with type safety.
	 */

	public function getByte(string $name, ?int $default = null) : int{
		return $this->getTagValue($name, ByteTag::class, $default);
	}

	public function getShort(string $name, ?int $default = null) : int{
		return $this->getTagValue($name, ShortTag::class, $default);
	}

	public function getInt(string $name, ?int $default = null) : int{
		return $this->getTagValue($name, IntTag::class, $default);
	}

	public function getLong(string $name, ?int $default = null) : int{
		return $this->getTagValue($name, LongTag::class, $default);
	}

	public function getFloat(string $name, ?float $default = null) : float{
		return $this->getTagValue($name, FloatTag::class, $default);
	}

	public function getDouble(string $name, ?float $default = null) : float{
		return $this->getTagValue($name, DoubleTag::class, $default);
	}

	public function getByteArray(string $name, ?string $default = null) : string{
		return $this->getTagValue($name, ByteArrayTag::class, $default);
	}

	public function getString(string $name, ?string $default = null) : string{
		return $this->getTagValue($name, StringTag::class, $default);
	}

	/**
	 * @param int[]|null $default
	 *
	 * @return int[]
	 */
	public function getIntArray(string $name, ?array $default = null) : array{
		return $this->getTagValue($name, IntArrayTag::class, $default);
	}

	/*
	 * The following methods are wrappers around setTag() which create appropriate tag objects on the fly.
	 */

	/**
	 * @return $this
	 */
	public function setByte(string $name, int $value) : self{
		return $this->setTag($name, new ByteTag($value));
	}

	/**
	 * @return $this
	 */
	public function setShort(string $name, int $value) : self{
		return $this->setTag($name, new ShortTag($value));
	}

	/**
	 * @return $this
	 */
	public function setInt(string $name, int $value) : self{
		return $this->setTag($name, new IntTag($value));
	}

	/**
	 * @return $this
	 */
	public function setLong(string $name, int $value) : self{
		return $this->setTag($name, new LongTag($value));
	}

	/**
	 * @return $this
	 */
	public function setFloat(string $name, float $value) : self{
		return $this->setTag($name, new FloatTag($value));
	}

	/**
	 * @return $this
	 */
	public function setDouble(string $name, float $value) : self{
		return $this->setTag($name, new DoubleTag($value));
	}

	/**
	 * @return $this
	 */
	public function setByteArray(string $name, string $value) : self{
		return $this->setTag($name, new ByteArrayTag($value));
	}

	/**
	 * @return $this
	 */
	public function setString(string $name, string $value) : self{
		return $this->setTag($name, new StringTag($value));
	}

	/**
	 * @param int[]  $value
	 *
	 * @return $this
	 */
	public function setIntArray(string $name, array $value) : self{
		return $this->setTag($name, new IntArrayTag($value));
	}

	protected function getTypeName() : string{
		return "Compound";
	}

	public function getType() : int{
		return NBT::TAG_Compound;
	}

	public static function read(NbtStreamReader $reader, ReaderTracker $tracker) : self{
		$result = new self;
		$tracker->protectDepth(static function() use($reader, $tracker, $result) : void{
			for($type = $reader->readByte(); $type !== NBT::TAG_End; $type = $reader->readByte()){
				$name = $reader->readString();
				$tag = NBT::createTag($type, $reader, $tracker);
				if($result->getTag($name) !== null){
					//this is technically a corruption case, but it's very common on older PM worlds (pretty much every
					//furnace in PM worlds prior to 2017 is affected), and since we can't extricate this borked data
					//from the rest in Anvil/McRegion worlds, we can't barf on this - it would result in complete loss
					//of the chunk.
					//TODO: add a flag to enable throwing on this (strict mode)
					continue;
				}
				$result->setTag($name, $tag);
			}
		});
		return $result;
	}

	public function write(NbtStreamWriter $writer) : void{
		foreach($this->value as $name => $tag){
			if(is_int($name)){
				//PHP sucks
				//we only cast on seeing an int, because forcibly casting other types might conceal bugs.
				$name = (string) $name;
			}
			$writer->writeByte($tag->getType());
			$writer->writeString($name);
			$tag->write($writer);
		}
		$writer->writeByte(NBT::TAG_End);
	}

	protected function stringifyValue(int $indentation) : string{
		$str = "{\n";
		foreach($this->value as $name => $tag){
			$str .= str_repeat("  ", $indentation + 1) . "\"$name\" => " . $tag->toString($indentation + 1) . "\n";
		}
		return $str . str_repeat("  ", $indentation) . "}";
	}

	public function __clone(){
		foreach($this->value as $key => $tag){
			$this->value[$key] = $tag->safeClone();
		}
	}

	protected function makeCopy(){
		return clone $this;
	}

	/**
	 * @return \Generator|Tag[]
	 * @phpstan-return \Generator<string, Tag, void, void>
	 */
	public function getIterator() : \Generator{
		foreach($this->value as $name => $tag){
			// PHP arrays are idiotic and cast keys like "1" to int(1)
			// this also stops us using "yield from". REEEEEEEEEE
			yield strval($name) => $tag;
		}
	}

	public function equals(Tag $that) : bool{
		if(!($that instanceof $this) or $this->count() !== $that->count()){
			return false;
		}

		foreach($this as $k => $v){
			$other = $that->getTag($k);
			if($other === null or !$v->equals($other)){
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns a copy of this CompoundTag with values from the given CompoundTag merged into it. Tags that exist both in
	 * this tag and the other will be overwritten by the tag in the other.
	 *
	 * This deep-clones all tags.
	 */
	public function merge(CompoundTag $other) : CompoundTag{
		$new = clone $this;

		foreach($other as $k => $namedTag){
			$new->setTag($k, clone $namedTag);
		}

		return $new;
	}
}
