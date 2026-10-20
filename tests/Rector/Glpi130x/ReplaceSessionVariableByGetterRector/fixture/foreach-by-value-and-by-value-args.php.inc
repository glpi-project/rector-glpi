<?php

foreach ($_SESSION['glpiactiveentities'] as $entity) {
}
$count = count($_SESSION['glpiactiveentities']);
array_push($stack, $_SESSION['glpiID']);

?>
-----
<?php

foreach (\Session::getActiveEntities() as $entity) {
}
$count = count(\Session::getActiveEntities());
array_push($stack, \Session::getLoginUserID());

?>
