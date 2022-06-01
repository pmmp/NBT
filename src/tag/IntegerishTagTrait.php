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

use pocketmine\nbt\InvalidTagValueException;
use function func_num_args;

/**
 * This trait implements common parts of tags containing integer values.
 */
trait IntegerishTagTrait{

	abstract protected function min() : int;

	abstract protected function max() : int;

	/** @var int */
	private $value;

	public function __construct(int $value){
		if(func_num_args() > 1){
			throw new \ArgumentCountError(__METHOD__ . "() expects at most 1 parameters, " . func_num_args() . " given");
		}
		if($value < $this->min() or $value > $this->max()){
			throw new InvalidTagValueException("Value $value is outside the allowed range " . $this->min() . " - " . $this->max());
		}
		$this->value = $value;
	}

	public function getValue() : int{
		return $this->value;
	}

	protected function stringifyValue(int $indentation) : string{
		return (string) $this->value;
	}
}
