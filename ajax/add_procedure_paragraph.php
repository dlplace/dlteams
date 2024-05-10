<?php

include_once('../../../inc/includes.php');

$section_id = $_GET["section_id"];
global $DB;
$position = 0;
$contents = new PluginDlteamsProcedure_Content();
$condition = [
    'FROM' => 'glpi_plugin_dlteams_procedures_contents',
    'ORDER' => 'timeline_position DESC',
    'LIMIT' => 1,
    'procedure_sections_id' => $section_id
];

$contents = $DB->request($condition);

foreach ($contents as $key => $c) {
    $lastRecord = $c["timeline_position"];

    $position = $lastRecord + 1;

}

$content = new PluginDlteamsProcedure_Content();
$added = $content->add([
    "procedure_sections_id" => $section_id,
    "name" => "paragraphe $position",
    "comment" => "",
    "content" => "",
    "timeline_position" => $position
]);

PluginDlteamsProcedure_Section::showAllProcedureParagraph($section_id);
