<?php

include_once('../../../inc/includes.php');

$content_id = $_GET["content_id"];
global $DB;

$contents = new PluginDlteamsDeliverable_Content();

$content_data = new PluginDlteamsProcedure_Content();
$content_data->getFromDB($_GET["content_id"]);
$section_id = $content_data->fields["procedure_sections_id"];

//$contents->delete(["id" => $content_id]);
$DB->delete(PluginDlteamsProcedure_Content::getTable(), ["id" => $content_id]);

PluginDlteamsProcedure_Section::showAllProcedureParagraph($section_id);