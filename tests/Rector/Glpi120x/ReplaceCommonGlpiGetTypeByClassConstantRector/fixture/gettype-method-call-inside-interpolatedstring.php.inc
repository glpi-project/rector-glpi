<?php

/** @var CommonGLPI $item */
$item = $something->getItem();

// The following occurences should not be replaced since using `{$var::CONSTANT}` produces a PHP syntax error
$out = "Processing {$item->getType()}...";

?>
-----
<?php

/** @var CommonGLPI $item */
$item = $something->getItem();

// The following occurences should not be replaced since using `{$var::CONSTANT}` produces a PHP syntax error
$out = "Processing {$item->getType()}...";

?>
