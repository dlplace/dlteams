<?php

include_once('../../../inc/includes.php');

if (isset($_POST["id_field"])) {
    $content = new PluginDlteamsDeliverable_Content();

    $content->update([
        "name" => $_POST["name"],
        "comment" => $_POST["comment"],
        "content" => $_POST["content"],
        'id' => $_POST["id_field"]
    ]);

    $content->getFromDB($_POST["id_field"]);

    Session::addMessageAfterRedirect("Section mis à jour avec succès");
    PluginDlteamsDeliverable_Section::showAllDeliverableParagraph($content->fields["deliverable_sections_id"]);
}
