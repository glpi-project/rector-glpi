<?php

$is_logged = isset($_SESSION['glpiID']);
if (!isset($_SESSION["glpiID"])) {
    return;
}
if (empty($_SESSION['glpiID'])) {
    return;
}
if (isset($_SESSION['glpiID']) && ($_SESSION['glpiID'] > 0)) {
    return;
}

?>
-----
<?php

$is_logged = \Session::isAuthenticated();
if (!\Session::isAuthenticated()) {
    return;
}
if (\Session::isAuthenticated() === false) {
    return;
}
if (\Session::isAuthenticated() && (\Session::getLoginUserID() > 0)) {
    return;
}

?>
