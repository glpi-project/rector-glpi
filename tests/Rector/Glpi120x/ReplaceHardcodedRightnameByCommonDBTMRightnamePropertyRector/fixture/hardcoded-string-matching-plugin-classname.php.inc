<?php

if (!Session::haveRight('uninstall:profile', UPDATE)) {
    return;
}

// no `Plugin*` class, but a matching `GlpiPlugin\*\*` namespaced class.
if (!Session::haveRight('namespacedplugin:profile', UPDATE)) {
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

// no `Plugin*` class, but a matching `GlpiPlugin\*\*` namespaced class.
if (!Session::haveRight(\GlpiPlugin\Namespacedplugin\Profile::$rightname, UPDATE)) {
    return;
}

// no matching `Plugin*` class, value untouched.
if (!Session::haveRight('foo:bar', UPDATE)) {
    return;
}

?>
