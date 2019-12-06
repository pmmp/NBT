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

namespace pocketmine\nbt;

use pocketmine\nbt\tag\Tag;
use function get_class;

/**
 * This class wraps around the root Tag for NBT files to avoid losing the name information.
 */
class TreeRoot{

	/** @var Tag */
	private $root;
	/** @var string */
	private $name;

	public function __construct(Tag $root, string $name = ""){
		$this->root = $root;
		$this->name = $name;
	}

	/**
	 * @return Tag
	 */
	public function getTag() : Tag{
		return $this->root;
	}

	/**
	 * @return string
	 */
	public function getName() : string{
		return $this->name;
	}

	public function equals(TreeRoot $that) : bool{
		return $this->name === $that->name and $this->root->equals($that->root);
	}

	public function __toString(){
		return "ROOT {\n  " . ($this->name !== "" ? "\"$this->name\" => " : "") . $this->root->toString(1) . "\n}";
	}
}
