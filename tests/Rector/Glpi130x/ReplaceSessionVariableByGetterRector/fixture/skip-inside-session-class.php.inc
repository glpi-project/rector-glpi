<?php

// Getters are reading the session themselves.
class Session
{
    public static function getLoginUserID()
    {
        return $_SESSION['glpiID'] ?? false;
    }

    public static function isLogged(): bool
    {
        return isset($_SESSION['glpiID']);
    }
}

?>
