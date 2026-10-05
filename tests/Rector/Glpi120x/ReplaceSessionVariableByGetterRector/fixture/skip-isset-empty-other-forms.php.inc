<?php

// Other getters return a default value, `isset()`/`empty()` checks cannot be converted.
$a = isset($_SESSION['glpilanguage']);
$b = empty($_SESSION['glpiactiveentities']);
$c = isset($_SESSION['glpiactiveentities'][0]);
$d = isset($_SESSION['glpiID'], $other);

?>
