<?php
include_once('../../../inc/includes.php');

PluginDlteamsAccountKey_Item::showForItemForm(null, null, isset($_POST["is_directory"]) && $_POST["is_directory"]);