<?php

$_SESSION['glpiID'] = $users_id;
$_SESSION["glpilanguage"] = 'fr_FR';
$_SESSION['glpiactiveentities'][] = 3;
$_SESSION['glpiactiveentities'][0] = 3;
$_SESSION['glpi_currenttime'] .= '';
$_SESSION['glpiactive_entity'] += 1;
$_SESSION['glpiactive_entity']++;
--$_SESSION['glpiactive_entity'];
unset($_SESSION['glpiID'], $_SESSION['glpiactiveentities'][0]);
[$_SESSION['glpiID'], $_SESSION['glpilanguage']] = $values;
list($_SESSION['glpiactive_entity']) = $values;
$_SESSION['glpiID'] = &$users_id;
$reference = &$_SESSION['glpiactiveentities'];

?>
