<?php

include_once('../../../inc/includes.php');

$itemtype = $_POST["itemtype"];
$items_id = $_POST["items_id"];

$receiver = new $itemtype();
$receiver->getFromDB($items_id);

if($receiver){
    $name = $receiver->fields["name"];

    if(NotificationMailing::isUserAddressValid($name)){
        echo "<div style='width: 100%; display: flex; justify-content: center;'><input type='submit' class='submit' name='send_notification' value='" . __("Envoyer la notification", 'dlteams') . "' /></div>";
    }
    else{
        echo "<span style='color: red; font-style: italic;'><i>La notification ne peut etre envoyé, le destinataire ne possède pas d'adresse mail</i></span>";
        echo "<div style='width: 100%; display: flex; justify-content: center;'><input type='submit' class='submit' disabled name='send_notification' value='" . __("Envoyer la notification", 'dlteams') . "' /></div>";
    }
}
