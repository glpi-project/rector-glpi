<?php

// Getters do not return `null` when the key is not set, the fallback value would never be used.
$users_id = $_SESSION['glpiID'] ?? 0;
$revoked_by = $_SESSION['glpiID'] ?? null;
$entity = $_SESSION['glpiactive_entity'] ?? -1;
$entities = $_SESSION['glpiactiveentities'] ?? [0];

?>
