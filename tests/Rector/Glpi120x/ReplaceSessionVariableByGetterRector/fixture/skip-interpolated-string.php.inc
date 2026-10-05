<?php

// Static calls are not allowed inside string interpolations.
$a = "User {$_SESSION['glpiID']}";
$b = "User $_SESSION[glpiID]";
$c = <<<TXT
    Language {$_SESSION['glpilanguage']}
    TXT;

?>
