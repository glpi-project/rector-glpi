<?php

class Test extends CommonGLPI
{
    public function doSomething(): void
    {
        $type = self::getType();
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
