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
use function strlen;

final class StringTag extends NamedTag{
	/** @var string */
	private $value;

	/**
	 * @param string $name
	 * @param string $value
	 */
	public function __construct(string $name, string $value){
		parent::__construct($name);
		if(strlen($value) > 32767){
			throw new \InvalidArgumentException("StringTag cannot hold more than 32767 bytes, got string of length " . strlen($value));
		}
		$this->value = $value;
	}

	public function getType() : int{
		return NBT::TAG_String;
	}

	public static function read(string $name, NbtStreamReader $reader) : NamedTag{
		return new self($name, $reader->readString());
	}

	public function write(NbtStreamWriter $writer) : void{
		$writer->writeString($this->value);
	}

	/**
	 * @return string
	 */
	public function getValue() : string{
		return $this->value;
	}
}
