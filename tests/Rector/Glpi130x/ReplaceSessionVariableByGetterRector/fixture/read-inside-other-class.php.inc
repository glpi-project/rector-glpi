<?php

class SessionVariableReader
{
    public function bar(): int
    {
        return $_SESSION['glpiactive_entity'];
    }
}

?>
-----
<?php

class SessionVariableReader
{
    public function bar(): int
    {
        return \Session::getActiveEntity();
    }
}

?>
