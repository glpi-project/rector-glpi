<?php

// `Bar` is not a `CommonGLPI` class, value is preserved since we cannot rely on the
// `CommonGLPI::$rightname` contract.
if (!Session::haveRight('bar', UPDATE)) {
    return;
}

?>
-----
<?php

// `Bar` is not a `CommonGLPI` class, value is preserved since we cannot rely on the
// `CommonGLPI::$rightname` contract.
if (!Session::haveRight('bar', UPDATE)) {
    return;
}

?>
