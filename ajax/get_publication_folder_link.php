<?php

include_once('../../../inc/includes.php');

if(isset($_POST["folder_id"])){
    $link = new Link();
    $link->getFromDB($_POST["folder_id"]);

    echo $link->fields["link"];
}
