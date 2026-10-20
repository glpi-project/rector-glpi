<?php

$language = $_SESSION['glpilanguage'] ?? 'en_GB';
$now = $_SESSION['glpi_currenttime'] ?? null;
$users_id = $_SESSION['glpiID'] ?? false;
$entity = $_SESSION['glpiactive_entity'] ?? 0;
$entities = $_SESSION['glpiactiveentities'] ?? [];
$first_entity = $_SESSION['glpiactiveentities'][0] ?? null;

?>
-----
<?php

$language = \Session::getLanguage() ?? 'en_GB';
$now = \Session::getCurrentTime() ?? null;
$users_id = \Session::getLoginUserID();
$entity = \Session::getActiveEntity();
$entities = \Session::getActiveEntities();
$first_entity = \Session::getActiveEntities()[0] ?? null;

?>
