<?php

if ($item->fields['users_id'] == $_SESSION['glpiID']) {
    $criteria = ['entities_id' => $_SESSION['glpiactiveentities'], 'users_id' => $_SESSION['glpiID']];
    $first_entity = $_SESSION['glpiactiveentities'][0];
    $label = 'User ' . $_SESSION['glpiID'];
    foo($_SESSION['glpilanguage']);
    $item->bar($_SESSION['glpi_currenttime']);
}

?>
-----
<?php

if ($item->fields['users_id'] == \Session::getLoginUserID()) {
    $criteria = ['entities_id' => \Session::getActiveEntities(), 'users_id' => \Session::getLoginUserID()];
    $first_entity = \Session::getActiveEntities()[0];
    $label = 'User ' . \Session::getLoginUserID();
    foo(\Session::getLanguage());
    $item->bar(\Session::getCurrentTime());
}

?>
