<?php

class Test extends CommonGLPI
{
    public function doSomething(): void
    {
        $type = static::getType();
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
        $type = static::class;
        //...
    }
}

?>
