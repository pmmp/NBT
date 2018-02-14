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
use pocketmine\nbt\NBTStream;

#include <rules/NBT.h>

class ListTag extends NamedTag implements \ArrayAccess, \Countable, \Iterator{
	use NoDynamicFieldsTrait;

	/** @var int */
	private $tagType;
	/** @var \SplDoublyLinkedList|NamedTag[] */
	protected $value;

	/**
	 * ListTag constructor.
	 *
	 * @param string     $name
	 * @param NamedTag[] $value
	 * @param int        $tagType
	 */
	public function __construct(string $name = "", array $value = [], int $tagType = NBT::TAG_End){
		$this->tagType = $tagType;
		parent::__construct($name, $value);
	}

	/**
	 * @return NamedTag[]
	 */
	public function getValue() : array{
		$value = [];
		foreach($this->value as $k => $v){
			$value[$k] = $v;
		}

		return $value;
	}

	/**
	 * Returns an array of tag values inserted into this list. ArrayAccess-implementing tags are returned as themselves
	 * (such as ListTag and CompoundTag) and others are returned as primitive values or arrays.
	 *
	 * @return array
	 */
	public function getAllValues() : array{
		$result = [];
		foreach($this->value as $tag){
			if($tag instanceof \ArrayAccess){
				$result[] = $tag;
			}else{
				$result[] = $tag->getValue();
			}
		}

		return $result;
	}

	/**
	 * @param NamedTag[] $value
	 *
	 * @throws \TypeError
	 */
	public function setValue($value) : void{
		if(is_array($value)){
			$oldType = $this->tagType;
			$newValue = new \SplDoublyLinkedList();
			foreach($value as $tag){
				if($tag instanceof NamedTag){
					$this->checkTagType($tag);
					$newValue->push($tag);
				}else{
					$this->tagType = $oldType;
					throw new \TypeError("ListTag members must be NamedTags, got " . gettype($tag) . " in given array");
				}
			}

			$this->value = $newValue;
		}else{
			throw new \TypeError("ListTag value must be NamedTag[], " . gettype($value) . " given");
		}
	}

	/**
	 * @param int $offset
	 *
	 * @return bool
	 */
	public function offsetExists($offset) : bool{
		return isset($this->value[$offset]);
	}

	/**
	 * @param int $offset
	 *
	 * @return CompoundTag|ListTag|mixed
	 */
	public function offsetGet($offset){
		/** @var NamedTag|null $value */
		$value = $this->value[$offset] ?? null;

		if($value instanceof \ArrayAccess){
			return $value;
		}elseif($value !== null){
			return $value->getValue();
		}

		return null;
	}

	/**
	 * @param int|null       $offset
	 * @param NamedTag|mixed $value
	 *
	 * @throws \TypeError if an incompatible tag type is given
	 * @throws \TypeError if a primitive value is given which is not compatible with the tag type
	 * @throws \OutOfRangeException if setting a primitive value at an offset that doesn't exist in the list
	 * @throws \OutOfRangeException if attempting to append a primitive value
	 */
	public function offsetSet($offset, $value) : void{
		if($value instanceof NamedTag){
			$this->checkTagType($value);
			$this->value[$offset] = $value;
		}else{
			if($offset === null){
				throw new \OutOfRangeException("Cannot append non-tag value to ListTag");
			}
			if(!isset($this->value[$offset])){
				throw new \OutOfRangeException("Cannot set non-tag value, no tag exists at offset $offset");
			}

			$this->value[$offset]->setValue($value);
		}
	}

	/**
	 * @param int $offset
	 */
	public function offsetUnset($offset) : void{
		unset($this->value[$offset]);
	}

	/**
	 * @return int
	 */
	public function count() : int{
		return $this->value->count();
	}

	/**
	 * @return int
	 */
	public function getCount() : int{
		return $this->value->count();
	}

	/**
	 * Appends the specified tag to the end of the list.
	 *
	 * @param NamedTag $tag
	 */
	public function push(NamedTag $tag) : void{
		$this->checkTagType($tag);
		$this->value->push($tag);
	}

	/**
	 * Removes the last tag from the list and returns it.
	 *
	 * @return NamedTag
	 */
	public function pop() : NamedTag{
		return $this->value->pop();
	}

	/**
	 * Adds the specified tag to the start of the list.
	 *
	 * @param NamedTag $tag
	 */
	public function unshift(NamedTag $tag) : void{
		$this->checkTagType($tag);
		$this->value->unshift($tag);
	}

	/**
	 * Removes the first tag from the list and returns it.
	 *
	 * @return NamedTag
	 */
	public function shift() : NamedTag{
		return $this->value->shift();
	}

	/**
	 * Inserts a tag into the list between existing tags, at the specified offset. Later values in the list are moved up
	 * by 1 position.
	 *
	 * @param int      $offset
	 * @param NamedTag $tag
	 *
	 * @throws \OutOfRangeException if the offset is not within the bounds of the list
	 */
	public function insert(int $offset, NamedTag $tag){
		$this->checkTagType($tag);
		$this->value->add($offset, $tag);
	}

	/**
	 * Removes a value from the list. All later tags in the list are moved down by 1 position.
	 *
	 * @param int $offset
	 */
	public function remove(int $offset) : void{
		unset($this->value[$offset]);
	}

	/**
	 * Returns the tag at the specified offset.
	 *
	 * @param int $offset
	 *
	 * @return NamedTag
	 * @throws \OutOfRangeException if the offset is not within the bounds of the list
	 */
	public function get(int $offset) : NamedTag{
		return $this->value[$offset];
	}

	/**
	 * Returns the element in the first position of the list, without removing it.
	 *
	 * @return NamedTag
	 */
	public function first() : NamedTag{
		return $this->value->bottom();
	}

	/**
	 * Returns the element in the last position in the list (the end), without removing it.
	 *
	 * @return NamedTag
	 */
	public function last() : NamedTag{
		return $this->value->top();
	}

	/**
	 * Overwrites the tag at the specified offset.
	 *
	 * @param int      $offset
	 * @param NamedTag $tag
	 *
	 * @throws \OutOfRangeException if the offset is not within the bounds of the list
	 */
	public function set(int $offset, NamedTag $tag) : void{
		$this->checkTagType($tag);
		$this->value[$offset] = $tag;
	}

	/**
	 * Returns whether a tag exists at the specified offset.
	 *
	 * @param int $offset
	 *
	 * @return bool
	 */
	public function isset(int $offset) : bool{
		return isset($this->value[$offset]);
	}

	/**
	 * Returns whether there are any tags in the list.
	 *
	 * @return bool
	 */
	public function empty() : bool{
		return $this->value->isEmpty();
	}

	public function getType() : int{
		return NBT::TAG_List;
	}

	/**
	 * Returns the type of tag contained in this list.
	 *
	 * @return int
	 */
	public function getTagType() : int{
		return $this->tagType;
	}

	/**
	 * Sets the type of tag that can be added to this list. If TAG_End is used, the type will be auto-detected from the
	 * first tag added to the list.
	 *
	 * @param int $type
	 * @throws \LogicException if the list is not empty
	 */
	public function setTagType(int $type){
		if(!$this->value->isEmpty()){
			throw new \LogicException("Cannot change tag type of non-empty ListTag");
		}
		$this->tagType = $type;
	}

	/**
	 * Type-checks the given NamedTag for addition to the list, updating the list tag type as appropriate.
	 * @param NamedTag $tag
	 *
	 * @throws \TypeError if the tag type is not compatible.
	 */
	private function checkTagType(NamedTag $tag) : void{
		$type = $tag->getType();
		if($type !== $this->tagType){
			if($this->tagType === NBT::TAG_End){
				$this->tagType = $type;
			}else{
				throw new \TypeError("Invalid tag of type " . get_class($tag) . " assigned to ListTag, expected " . get_class(NBT::createTag($this->tagType)));
			}
		}
	}

	public function read(NBTStream $nbt) : void{
		$this->value = new \SplDoublyLinkedList();
		$this->tagType = $nbt->getByte();
		$size = $nbt->getInt();

		if($this->tagType !== NBT::TAG_End){
			$tagBase = NBT::createTag($this->tagType);
			for($i = 0; $i < $size and !$nbt->feof(); ++$i){
				$tag = clone $tagBase;
				$tag->read($nbt);
				$this->value->push($tag);
			}
		}elseif($size !== 0){
			throw new \UnexpectedValueException("Unexpected non-empty list of TAG_End");
		}
	}

	public function write(NBTStream $nbt) : void{
		$nbt->putByte($this->tagType);
		$nbt->putInt($this->value->count());
		/** @var NamedTag $tag */
		foreach($this->value as $tag){
			$tag->write($nbt);
		}
	}

	public function __toString(){
		$str = get_class($this) . "{\n";
		/** @var NamedTag $tag */
		foreach($this->value as $tag){
			$str .= get_class($tag) . ":" . $tag->__toString() . "\n";
		}
		return $str . "}";
	}

	public function __clone(){
		$new = new \SplDoublyLinkedList();

		foreach($this->value as $tag){
			$new->push($tag->safeClone());
		}

		$this->value = $new;
	}

	public function next() : void{
		$this->value->next();
	}

	/**
	 * @return bool
	 */
	public function valid() : bool{
		return $this->value->valid();
	}

	/**
	 * @return NamedTag|null
	 */
	public function current() : ?NamedTag{
		return $this->value->current();
	}

	/**
	 * @return int
	 */
	public function key() : int{
		return (int) $this->value->key();
	}

	public function rewind() : void{
		$this->value->rewind();
	}

	protected function equalsValue(NamedTag $that) : bool{
		if(!($that instanceof $this) or $this->count() !== $that->count()){
			return false;
		}

		foreach($this as $k => $v){
			$other = $that->get($k);
			if($other === null or !$v->equalsValue($other)){ //ListTag members don't have names, don't bother checking it
				return false;
			}
		}

		return true;
	}
}
