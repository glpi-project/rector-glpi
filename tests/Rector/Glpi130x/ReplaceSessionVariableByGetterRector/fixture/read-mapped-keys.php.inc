<?php

$users_id = $_SESSION['glpiID'];
$language = $_SESSION["glpilanguage"];
$now = $_SESSION['glpi_currenttime'];
$entity = $_SESSION['glpiactive_entity'];
$entities = $_SESSION['glpiactiveentities'];

?>
-----
<?php

$users_id = \Session::getLoginUserID();
$language = \Session::getLanguage();
$now = \Session::getCurrentTime();
$entity = \Session::getActiveEntity();
$entities = \Session::getActiveEntities();

?>
