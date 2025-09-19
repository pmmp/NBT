# PocketMine-NBT
![CI](https://github.com/pmmp/NBT/workflows/CI/badge.svg)

PHP library for working with the NBT (Named Binary Tag) data storage format, as designed by Mojang.

## Examples

The library provides two NBT serializers: `BigEndianNbtSerializer` (typically suited for Minecraft Java) and `LittleEndianNbtSerializer` (typically used by Bedrock for storage).

Note: Bedrock network NBT (which uses varints in some places) is not implemented here. See [BedrockProtocol](https://github.com/pmmp/BedrockProtocol) for that.

Note: `TAG_LongArray` is not supported because Bedrock doesn't support it, so it's not clear how NBT trees intended for serialization by Bedrock would be restricted from using it.

### Reading data
```php
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\NbtDataException;
use pocketmine\nbt\NoSuchTagException;
use pocketmine\nbt\UnexpectedTagTypeException;
use pocketmine\nbt\tag\StringTag;

$serializer = new LittleEndianNbtSerializer();
$optionalStartOffset = 0;
$optionalMaxDepth = 0; //unlimited by default
$treeRoot = $serializer->read($yourInputBytes, $optionalStartOffset, $optionalMaxDepth);

try{
    //If you expect a TAG_Compound root (the most common case)
    $data = $treeRoot->mustGetCompoundTag());
}catch(NbtDataException $e){
    var_dump("root isn't a TAG_Compound");
}
//For other, less common cases where the root tag isn't a compound
var_dump($treeRoot->getTag());

var_dump($treeRoot->getName()); //typically empty

try{
    var_dump($data->getString("hello"));
}catch(UnexpectedTagTypeException $e){
    var_dump("not a TAG_String");
}catch(NoSuchTagException $e){
    var_dump("no such tag called \"hello\"");
}

try{
    $nestedCompound = $data->getCompoundTag("nestedCompound");
}catch(UnexpectedTagTypeException $e){
    var_dump("not a TAG_Compound");
}
if($nestedCompound === null){
    //For legacy BC reasons, this works differently than the primitive type getters like getString() getInt() etc
    var_dump("no such nested tag called \"nestedCompound\"");
}

try{
    $nestedList = $data->getListTag("listOfStrings", StringTag::class);
}catch(UnexpectedTagTypeException $e){
    var_dump("not a list of strings");
}
if($nestedList === null){
    //For legacy BC reasons, getListTag() returns NULL if the tag doesn't exist
    var_dump("no such nested tag called \"listOfStrings\"");
}
var_dump($nestedList->getValue()); //StringTag[]
```

### Writing data

```php
use pocketmine\nbt\LittleEndianNbtSerializer;
use pocketmine\nbt\TreeRoot;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;

$compound = CompoundTag::create()
    ->setByte("byte", 1)
    ->setInt("int", 2)
    ->setTag("list", new ListTag([
        new StringTag("item1"),
        new StringTag("item2")
    ]))
    ->setTag("compound", CompoundTag::create()
        ->setByte("nestedByte", 1)
    );

//empty lists infer their type from the first value added
$list = new ListTag();
$list->push(new StringTag("hello")); //list is now ListTag<StringTag>
try{
    $list->push(new IntTag(1));
}catch(\TypeError $e){
    var_dump("can't push an int into a string list");
}

$serializer = new LittleEndianNbtSerializer();
$bytes = $serializer->write($treeRoot);

//or if you have a Tag instance
$bytes = $serializer->write(new TreeRoot($data, "optionalRootName"));
```
