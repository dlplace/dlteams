<?php

include_once('../../../inc/includes.php');

$parentcatalog = new PluginDlteamsDataCatalog();
$parentcatalog->getFromDB($_POST["id"]);
$currentcatalog = new PluginDlteamsDataCatalog();
$currentcatalog->getFromDB($_POST["currentcatalog"]);
$currentcatalog->showForm($_POST["currentcatalog"], ['parentcatalog' => $_POST["id"]]);
//PluginDlteamsDataCatalog::additionalShowRelatedParentFields($currentcatalog, $parentcatalog);