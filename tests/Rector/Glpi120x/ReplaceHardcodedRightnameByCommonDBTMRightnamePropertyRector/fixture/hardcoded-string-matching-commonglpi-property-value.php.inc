<?php

if (!Session::haveRight('computer', UPDATE)) {
    return;
}

if (!Session::haveRightsAnd('computer', [CREATE, UPDATE])) {
    return;
}

if (!Session::haveRightsOr('monitor', [DELETE, PURGE])) {
    return;
}

if (!Session::checkRight('computer', CREATE)) {
    return;
}

if (!Session::checkRightsOr('computer', [DELETE, PURGE])) {
    return;
}

if (!Session::haveRight(module: 'monitor', right: UPDATE)) {
    // named argument in expected order
    return;
}

if (!Session::haveRight('computer', right: UPDATE)) {
    // mixed between named and positional arguments
    return;
}

if (!Session::haveRight(right: UPDATE, module: 'monitor')) {
    // named argument in mixed order
    return;
}

?>
-----
<?php

if (!Session::haveRight(\Computer::$rightname, UPDATE)) {
    return;
}

if (!Session::haveRightsAnd(\Computer::$rightname, [CREATE, UPDATE])) {
    return;
}

if (!Session::haveRightsOr(\Monitor::$rightname, [DELETE, PURGE])) {
    return;
}

if (!Session::checkRight(\Computer::$rightname, CREATE)) {
    return;
}

if (!Session::checkRightsOr(\Computer::$rightname, [DELETE, PURGE])) {
    return;
}

if (!Session::haveRight(module: \Monitor::$rightname, right: UPDATE)) {
    // named argument in expected order
    return;
}

if (!Session::haveRight(\Computer::$rightname, right: UPDATE)) {
    // mixed between named and positional arguments
    return;
}

if (!Session::haveRight(right: UPDATE, module: \Monitor::$rightname)) {
    // named argument in mixed order
    return;
}

?>
