<?php

$values[$_SESSION['glpiID']] = 1;
$_SESSION['glpi_plannings']['user_' . $_SESSION['glpiID']] = [];

?>
-----
<?php

$values[\Session::getLoginUserID()] = 1;
$_SESSION['glpi_plannings']['user_' . \Session::getLoginUserID()] = [];

?>
