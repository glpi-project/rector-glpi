<?php

if (!Session::haveRight('uninstall:profile', UPDATE)) {
    return;
}

// no matching `Plugin*` class, value untouched.
if (!Session::haveRight('foo:bar', UPDATE)) {
    return;
}

?>
-----
<?php

if (!Session::haveRight(\PluginUninstallProfile::$rightname, UPDATE)) {
    return;
}

// no matching `Plugin*` class, value untouched.
if (!Session::haveRight('foo:bar', UPDATE)) {
    return;
}

?>
