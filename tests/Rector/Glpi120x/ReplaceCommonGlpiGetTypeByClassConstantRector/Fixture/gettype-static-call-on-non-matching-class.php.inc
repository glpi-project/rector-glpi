<?php

class Whatever
{
    public function getType(): string
    {
        return random_bytes(32);
    }

    public function doSomething(): void
    {
        $type = static::getType(); // it should not be altered
        //...
    }
}

?>
-----
<?php

class Whatever
{
    public function getType(): string
    {
        return random_bytes(32);
    }

    public function doSomething(): void
    {
        $type = static::getType(); // it should not be altered
        //...
    }
}

?>
