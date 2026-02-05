<?php

class Foo
{
    public function getItem(): CommonGLPI
    {
        return new Computer();
    }
}

$foo = new Foo();

$item = $foo->getItem();
$itemtype = $item->getType();

?>
-----
<?php

class Foo
{
    public function getItem(): CommonGLPI
    {
        return new Computer();
    }
}

$foo = new Foo();

$item = $foo->getItem();
$itemtype = $item::class;

?>
