<?php
include("../../../inc/includes.php");
Plugin::load('dlteams', true);

if (strpos($_SERVER['PHP_SELF'], "account_item_dropdown.php")) {

    $AJAX_INCLUDE = 1;

    header("Content-Type: text/html; charset=UTF-8");
    Html::header_nocache();
}

if(isset($_POST["itemtype_ref"])){
    switch ($_POST["itemtype_ref"]){
        case 1:
            echo "<input type='hidden' name='itemtype' value='" . Group::getType() . "' />";
            Group::dropdown(['name' => 'items_id', 'width' => '150px']);
            break;
        case 2:
            echo "<input type='hidden' name='itemtype' value='" . User::getType() . "' />";
            User::dropdown(['name' => 'items_id', 'width' => '150px', 'entity' => $_SESSION['glpiactive_entity'], 'right' => 'all']);
            break;
        case 3:
            echo "<input type='hidden' name='itemtype' value='" . Contact::getType() . "' />";
            Contact::dropdown(['name' => 'items_id', 'width' => '150px']);
            break;
        case 4:
            echo "<input type='hidden' name='itemtype' value='" . Supplier::getType() . "' />";
            Supplier::dropdown(['name' => 'items_id', 'width' => '150px']);
            break;
    }
}
