<?php

include_once('../../../inc/includes.php');

$content_id = $_GET["content_id"];
global $DB;

$contents = new PluginDlteamsDeliverable_Content();

$content_data = new PluginDlteamsDeliverable_Content();
$content_data->getFromDB($_GET["content_id"]);
$section_id = $content_data->fields["deliverable_sections_id"];

$contents->delete(["id" => $content_id]);


PluginDlteamsDeliverable_Section::showAllDeliverableParagraph($section_id);
