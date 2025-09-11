<?php

namespace pocketmine\nbt;

use PHPUnit\Framework\TestCase;
use pocketmine\nbt\tag\IntTag;
use function str_repeat;

class TreeRootTest extends TestCase{

	public function testNameLength() : void{
		new TreeRoot(new IntTag(1), str_repeat(".", NBT::MAX_STRING_LENGTH)); //ok

		$this->expectException(\InvalidArgumentException::class);
		new TreeRoot(new IntTag(1), str_repeat(".", NBT::MAX_STRING_LENGTH + 1)); //error
	}
}
