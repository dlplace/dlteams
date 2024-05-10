<?php

include_once('../../../inc/includes.php');

if (isset($_POST["id"])) {
    try {
        $accountkey_item = new PluginDlteamsAccountKey_Item();
        $accountkey_item->getFromDB($_POST["id"]);
        $accountkey_item->delete(["id" => $_POST["id"]]);
        Session::addMessageAfterRedirect(sprintf("%s supprimé avec succès", $accountkey_item->fields["itemtype"]::getTypeName()));
    } catch (Exception $e) {
        Session::addMessageAfterRedirect("Une erreur s'est produite");
    }
} else {
    Session::addMessageAfterRedirect("Une erreur s'est produite");
}