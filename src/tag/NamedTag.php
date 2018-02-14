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


use pocketmine\nbt\NBTStream;

abstract class NamedTag{
	/** @var string */
	protected $__name;

	protected $value;

	/**
	 * Used for recursive cloning protection when cloning tags with child tags.
	 * @var bool
	 */
	protected $cloning = false;

	/**
	 * @param string $name
	 * @param mixed  $value
	 */
	public function __construct(string $name = "", $value = null){
		$this->__name = $name;
		if($value !== null){
			$this->setValue($value);
		}
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->__name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name) : void{
		$this->__name = $name;
	}

	public function getValue(){
		return $this->value;
	}

	abstract public function getType() : int;

	public function setValue($value) : void{
		$this->value = $value;
	}

	abstract public function write(NBTStream $nbt) : void;

	abstract public function read(NBTStream $nbt) : void;

	public function __toString(){
		return (string) $this->value;
	}

	/**
	 * Clones this tag safely, detecting recursive dependencies which would otherwise cause an infinite cloning loop.
	 * Used for cloning tags in tags that have children.
	 *
	 * @return NamedTag
	 * @throws \RuntimeException if a recursive dependency was detected
	 */
	public function safeClone() : NamedTag{
		if($this->cloning){
			throw new \RuntimeException("Recursive NBT tag dependency detected");
		}
		$this->cloning = true;

		$retval = clone $this;

		$this->cloning = false;
		$retval->cloning = false;

		return $retval;
	}

	/**
	 * Compares this NamedTag to the given NamedTag and determines whether or not they are equal, based on name, type
	 * and value.
	 *
	 * @param NamedTag $that
	 *
	 * @return bool
	 */
	public function equals(NamedTag $that) : bool{
		return $this->__name === $that->__name and $this->equalsValue($that);
	}

	/**
	 * Compares this NamedTag to the given NamedTag and determines whether they are equal, based on type and value only.
	 * Complex tag types should override this to provide proper value comparison.
	 *
	 * @param NamedTag $that
	 *
	 * @return bool
	 */
	protected function equalsValue(NamedTag $that) : bool{
		return $that instanceof $this and $this->getValue() === $that->getValue();
	}
}
