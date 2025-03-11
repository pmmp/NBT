<?php

namespace pocketmine\nbt;

use PHPUnit\Framework\TestCase;
use pocketmine\nbt\tag\IntTag;
use pocketmine\utils\Limits;
use function str_repeat;

class TreeRootTest extends TestCase{

	public function testNameLength() : void{
		new TreeRoot(new IntTag(1), str_repeat(".", Limits::INT16_MAX)); //ok

		$this->expectException(\InvalidArgumentException::class);
		new TreeRoot(new IntTag(1), str_repeat(".", Limits::INT16_MAX + 1)); //error
	}
}
