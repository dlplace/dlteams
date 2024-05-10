<?php

include_once('../../../inc/includes.php');

if (isset($_POST["object"]) && isset($_POST["linkid"]) && isset($_POST["itemtype"]) && isset($_POST["items_id"])) {
    global $DB;

    $object_str = $_POST["object"];
    $linkid = $_POST["linkid"];
    $itemtype_str = $_POST["itemtype"];
    $item = new $itemtype_str();
    $item->getFromDB($_POST["items_id"]);

    $object_str::showForItemForm($item, $linkid, isset($_POST["is_directory"]) && $_POST["is_directory"]);
}
