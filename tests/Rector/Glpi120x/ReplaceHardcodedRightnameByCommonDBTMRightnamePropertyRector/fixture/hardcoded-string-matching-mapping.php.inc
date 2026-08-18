<?php

if (!Session::haveRight('dashboard', UPDATE)) {
    return;
}

if (!Session::haveRightsAnd('knowbase', [CREATE, UPDATE])) {
    return;
}

if (!Session::haveRight('dropdown', DELETE)) {
    // too ambiguous, it should refer to the concrete class
    return;
}

?>
-----
<?php

if (!Session::haveRight(\Glpi\Dashboard\Dashboard::$rightname, UPDATE)) {
    return;
}

if (!Session::haveRightsAnd(\KnowbaseItem::$rightname, [CREATE, UPDATE])) {
    return;
}

if (!Session::haveRight('dropdown', DELETE)) {
    // too ambiguous, it should refer to the concrete class
    return;
}

?>
