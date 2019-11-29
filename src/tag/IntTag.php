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

final class IntTag extends ImmutableTag{
	use IntegerishTagTrait;

	protected function min() : int{
		return -0x7fffffff - 1; //workaround parser bug https://bugs.php.net/bug.php?id=53934
	}

	protected function max() : int{ return 0x7fffffff; }

	protected function getTypeName() : string{
		return "Int";
	}

	public function getType() : int{
		return NBT::TAG_Int;
	}

	public static function read(NbtStreamReader $reader) : self{
		return new self($reader->readInt());
	}

	public function write(NbtStreamWriter $writer) : void{
		$writer->writeInt($this->value);
	}
}
