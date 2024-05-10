<?php

include_once('../../../inc/includes.php');
global $DB;
$idx = $_POST["idx"];
if ($_POST["tomove_id"] == $idx[0] && $_POST["direction"] == "up") {
    return;
}
if ($_POST["tomove_id"] == $idx[count($idx) - 1] && $_POST["direction"] == "down") {
    return;
}
if($_POST["direction"] == "down"){
    $idx = array_reverse($idx);
}

$content_data = new PluginDlteamsProcedure_Content();
$content_data->getFromDB($_POST["tomove_id"]);
$section_id = $content_data->fields["procedure_sections_id"];

$procedure_content = new PluginDlteamsProcedure_Content();
$previous_procedure_content = new PluginDlteamsProcedure_Content();

//echange de position pour les elements concernés
foreach ($idx as $key => $id) {
//    si c'est le premier element on ne peut plus le remonter
    if ($procedure_content->getFromDB($id) && $id == $_POST["tomove_id"] && ($_POST["direction"] == "up" || $_POST["direction"] == "down")) {

        $previous_content_position = $key - 1;

        $previous_content_id = $idx[$previous_content_position];
        $previous_procedure_content->getFromDB($previous_content_id);
        if ($previous_procedure_content) {

            $current_content_timeline_position = $procedure_content->fields["timeline_position"];
            $procedure_content->update([
                "timeline_position" => $previous_procedure_content->fields["timeline_position"],
                "id" => $id,
            ]);

            $previous_procedure_content->update([
                "timeline_position" => $current_content_timeline_position,
                "id" => $previous_procedure_content->fields["id"],
            ]);
        }

    }
}

PluginDlteamsProcedure_Section::showAllProcedureParagraph($section_id);
