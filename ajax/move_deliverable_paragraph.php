<?php

include_once('../../../inc/includes.php');
global $DB;
$idx = $_POST["idx"];
/*highlight_string("<?php\n\$data =\n" . var_export($_POST["tomove_id"], true) . ";\n?>");*/
if ($_POST["tomove_id"] == $idx[0] && $_POST["direction"] == "up") {
    return;
}
if ($_POST["tomove_id"] == $idx[count($idx) - 1] && $_POST["direction"] == "down") {
    return;
}
if($_POST["direction"] == "down"){
    $idx = array_reverse($idx);
}

$content_data = new PluginDlteamsDeliverable_Content();
$content_data->getFromDB($_POST["tomove_id"]);
$section_id = $content_data->fields["deliverable_sections_id"];

$deliverable_content = new PluginDlteamsDeliverable_Content();
$previous_deliverable_content = new PluginDlteamsDeliverable_Content();

//echange de position pour les elements concernés
foreach ($idx as $key => $id) {
//    si c'est le premier element on ne peut plus le remonter
    if ($deliverable_content->getFromDB($id) && $id == $_POST["tomove_id"] && ($_POST["direction"] == "up" || $_POST["direction"] == "down")) {

        $previous_content_position = $key - 1;

        $previous_content_id = $idx[$previous_content_position];
        $previous_deliverable_content->getFromDB($previous_content_id);
        if ($previous_deliverable_content) {

            $current_content_timeline_position = $deliverable_content->fields["timeline_position"];
            $deliverable_content->update([
                "timeline_position" => $previous_deliverable_content->fields["timeline_position"],
                "id" => $id,
            ]);

            $previous_deliverable_content->update([
                "timeline_position" => $current_content_timeline_position,
                "id" => $previous_deliverable_content->fields["id"],
            ]);
        }

    }
}

PluginDlteamsDeliverable_Section::showAllDeliverableParagraph($section_id);
