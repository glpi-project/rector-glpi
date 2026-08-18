<?php

class Test extends CommonGLPI
{
    public function doSomething(): void
    {
        $type = $this->getType();
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
