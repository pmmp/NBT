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
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\NbtStreamReader;
use pocketmine\nbt\NbtStreamWriter;
use pocketmine\nbt\ReaderTracker;
use function func_num_args;
use function get_class;
use function iterator_to_array;
use function str_repeat;

/**
 * @phpstan-implements \IteratorAggregate<int, Tag>
 */
final class ListTag extends Tag implements \Countable, \IteratorAggregate{
	use NoDynamicFieldsTrait;

	/** @var int */
	private $tagType;
	/**
	 * @var \SplDoublyLinkedList|Tag[]
	 * @phpstan-var \SplDoublyLinkedList<Tag>
	 */
	private $value;

	/**
	 * @param Tag[] $value
	 */
	public function __construct(array $value = [], int $tagType = NBT::TAG_End){
		self::restrictArgCount(__METHOD__, func_num_args(), 2);
		$this->tagType = $tagType;
		$this->value = new \SplDoublyLinkedList();
		foreach($value as $tag){
			$this->push($tag);
		}
	}

	/**
	 * @return Tag[]
	 */
	public function getValue() : array{
		$value = [];
		foreach($this->value as $k => $v){
			$value[$k] = $v;
		}

		return $value;
	}

	/**
	 * Returns an array of tag values inserted into this list.
	 * @return mixed[]
	 * @phpstan-return list<mixed>
	 */
	public function getAllValues() : array{
		$result = [];
		foreach($this->value as $tag){
			$result[] = $tag->getValue();
		}

		return $result;
	}

	public function count() : int{
		return $this->value->count();
	}

	public function getCount() : int{
		return $this->value->count();
	}

	/**
	 * Appends the specified tag to the end of the list.
	 */
	public function push(Tag $tag) : void{
		$this->checkTagType($tag);
		$this->value->push($tag);
	}

	/**
	 * Removes the last tag from the list and returns it.
	 */
	public function pop() : Tag{
		return $this->value->pop();
	}

	/**
	 * Adds the specified tag to the start of the list.
	 */
	public function unshift(Tag $tag) : void{
		$this->checkTagType($tag);
		$this->value->unshift($tag);
	}

	/**
	 * Removes the first tag from the list and returns it.
	 */
	public function shift() : Tag{
		return $this->value->shift();
	}

	/**
	 * Inserts a tag into the list between existing tags, at the specified offset. Later values in the list are moved up
	 * by 1 position.
	 *
	 * @return void
	 * @throws \OutOfRangeException if the offset is not within the bounds of the list
	 */
	public function insert(int $offset, Tag $tag){
		$this->checkTagType($tag);
		$this->value->add($offset, $tag);
	}

	/**
	 * Removes a value from the list. All later tags in the list are moved down by 1 position.
	 */
	public function remove(int $offset) : void{
		unset($this->value[$offset]);
	}

	/**
	 * Returns the tag at the specified offset.
	 *
	 * @throws \OutOfRangeException if the offset is not within the bounds of the list
	 */
	public function get(int $offset) : Tag{
		if(!isset($this->value[$offset])){
			throw new \OutOfRangeException("No such tag at offset $offset");
		}
		return $this->value[$offset];
	}

	/**
	 * Returns the element in the first position of the list, without removing it.
	 */
	public function first() : Tag{
		return $this->value->bottom();
	}

	/**
	 * Returns the element in the last position in the list (the end), without removing it.
	 */
	public function last() : Tag{
		return $this->value->top();
	}

	/**
	 * Overwrites the tag at the specified offset.
	 *
	 * @throws \OutOfRangeException if the offset is not within the bounds of the list
	 */
	public function set(int $offset, Tag $tag) : void{
		$this->checkTagType($tag);
		$this->value[$offset] = $tag;
	}

	/**
	 * Returns whether a tag exists at the specified offset.
	 */
	public function isset(int $offset) : bool{
		return isset($this->value[$offset]);
	}

	/**
	 * Returns whether there are any tags in the list.
	 */
	public function empty() : bool{
		return $this->value->isEmpty();
	}

	protected function getTypeName() : string{
		return "List";
	}

	public function getType() : int{
		return NBT::TAG_List;
	}

	/**
	 * Returns the type of tag contained in this list.
	 */
	public function getTagType() : int{
		return $this->tagType;
	}

	/**
	 * Sets the type of tag that can be added to this list. If TAG_End is used, the type will be auto-detected from the
	 * first tag added to the list.
	 *
	 * @return void
	 * @throws \LogicException if the list is not empty
	 */
	public function setTagType(int $type){
		if(!$this->value->isEmpty()){
			throw new \LogicException("Cannot change tag type of non-empty ListTag");
		}
		$this->tagType = $type;
	}

	/**
	 * Type-checks the given Tag for addition to the list, updating the list tag type as appropriate.
	 *
	 * @throws \TypeError if the tag type is not compatible.
	 */
	private function checkTagType(Tag $tag) : void{
		$type = $tag->getType();
		if($type !== $this->tagType){
			if($this->tagType === NBT::TAG_End){
				$this->tagType = $type;
			}else{
				//TODO: reintroduce type info
				throw new \TypeError("Invalid tag of type " . get_class($tag) . " assigned to ListTag");
			}
		}
	}

	public static function read(NbtStreamReader $reader, ReaderTracker $tracker) : self{
		$value = [];
		$tagType = $reader->readByte();
		$size = $reader->readInt();

		if($size > 0){
			if($tagType === NBT::TAG_End){
				throw new NbtDataException("Unexpected non-empty list of TAG_End");
			}

			$tracker->protectDepth(static function() use($size, $tagType, $reader, $tracker, &$value) : void{
				for($i = 0; $i < $size; ++$i){
					$value[] = NBT::createTag($tagType, $reader, $tracker);
				}
			});
		}else{
			$tagType = NBT::TAG_End; //Some older NBT implementations used TAG_Byte for empty lists.
		}
		return new self($value, $tagType);
	}

	public function write(NbtStreamWriter $writer) : void{
		$writer->writeByte($this->tagType);
		$writer->writeInt($this->value->count());
		/** @var Tag $tag */
		foreach($this->value as $tag){
			$tag->write($writer);
		}
	}

	protected function stringifyValue(int $indentation) : string{
		$str = "{\n";
		/** @var Tag $tag */
		foreach($this->value as $tag){
			$str .= str_repeat("  ", $indentation + 1) . $tag->toString($indentation + 1) . "\n";
		}
		return $str . str_repeat("  ", $indentation) . "}";
	}

	public function __clone(){
		/** @phpstan-var \SplDoublyLinkedList<Tag> $new */
		$new = new \SplDoublyLinkedList();

		foreach($this->value as $tag){
			$new->push($tag->safeClone());
		}

		$this->value = $new;
	}

	protected function makeCopy(){
		return clone $this;
	}

	/**
	 * @return \Generator|Tag[]
	 * @phpstan-return \Generator<int, Tag, void, void>
	 */
	public function getIterator() : \Generator{
		//we technically don't need iterator_to_array() here, but I don't feel comfortable relying on "yield from" to
		//copy the underlying dataset referenced by SplDoublyLinkedList
		yield from iterator_to_array($this->value, true);
	}

	public function equals(Tag $that) : bool{
		if(!($that instanceof $this) or $this->count() !== $that->count()){
			return false;
		}

		foreach($this as $k => $v){
			if(!$v->equals($that->get($k))){
				return false;
			}
		}

		return true;
	}
}
