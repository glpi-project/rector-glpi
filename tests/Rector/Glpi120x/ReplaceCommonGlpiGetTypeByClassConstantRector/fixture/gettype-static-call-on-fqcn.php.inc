<?php

class Test extends CommonGLPI
{
    public function doSomething(): void
    {
        $type = Computer::getType();
        //...
    }
}

?>
-----
<?php

class Test extends CommonGLPI
{
    public function doSomething(): void
    {
        $type = Computer::class;
        //...
    }
}

?>
