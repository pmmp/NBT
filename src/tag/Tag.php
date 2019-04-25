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

use pocketmine\nbt\NbtStreamWriter;
use function get_class;

abstract class Tag{

	/**
	 * Used for recursive cloning protection when cloning tags with child tags.
	 * @var bool
	 */
	protected $cloning = false;

	abstract public function getValue();

	abstract public function getType() : int;

	abstract public function write(NbtStreamWriter $writer) : void;

	public function __toString(){
		return $this->toString();
	}

	public function toString(int $indentation = 0) : string{
		return get_class($this) . ": value='" . (string) $this->getValue() . "'";
	}

	/**
	 * Clones this tag safely, detecting recursive dependencies which would otherwise cause an infinite cloning loop.
	 * Used for cloning tags in tags that have children.
	 *
	 * @return Tag
	 * @throws \RuntimeException if a recursive dependency was detected
	 */
	public function safeClone() : Tag{
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
	 * Compares this Tag to the given Tag and determines whether or not they are equal, based on type and value.
	 * Complex tag types should override this to provide proper value comparison.
	 *
	 * @param Tag $that
	 *
	 * @return bool
	 */
	public function equals(Tag $that) : bool{
		return $that instanceof $this and $this->getValue() === $that->getValue();
	}

	protected static function restrictArgCount(string $func, int $haveArgs, int $wantMaxArgs) : void{
		if($haveArgs > $wantMaxArgs){
			throw new \ArgumentCountError("$func() expects at most $wantMaxArgs parameters, $haveArgs given");
		}
	}
}
