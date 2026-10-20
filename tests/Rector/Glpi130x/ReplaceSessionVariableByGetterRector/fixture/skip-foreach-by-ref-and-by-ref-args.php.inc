<?php

foreach ($_SESSION['glpiactiveentities'] as &$entity) {
    $entity = (int) $entity;
}
foreach ($values as $_SESSION['glpiactive_entity']) {
}
sort($_SESSION['glpiactiveentities']);
array_push($_SESSION['glpiactiveentities'], 3);
preg_match('/(.*)/', 'foo', $_SESSION['glpilanguage']);

?>
