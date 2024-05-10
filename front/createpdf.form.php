<?php

include("../../../inc/includes.php");

$datacatalog = new PluginDlteamsCreatePDF();
//$datacatalog->checkGlobal(READ);

if (Session::getCurrentInterface() == 'central') {
    Html::header(PluginDlteamsCreatePDF::getTypeName(2), '', 'dlteams', 'plugindlteamsmenu', 'plugindlteamscreatepdf');
} else {
    Html::helpHeader(PluginDlteamsCreatePDF::getTypeName(0));
}

$datacatalog->display();

if (Session::getCurrentInterface() == 'central') {
    Html::footer();
} else {
    Html::helpFooter();
}
