<?php
include_once('../../../inc/includes.php');


if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $chapter = new PluginDlteamsDeliverable_Section();
    $chapter->getFromDB($id);

    echo $chapter->getField("name");
}
