<?php

include_once('../../../inc/includes.php');

$comment_id = $_POST["comment_id"];
$deliverable_item = new PluginDlteamsDeliverable_Item();

if($deliverable_item->getFromDB($comment_id)){
    echo "<div style='display: flex; gap: 4px;'>";
        echo "<div style='width: 100%'>";
            echo "<h3> Notification </h3>";

            echo "<div style='background-color: #e7e7e7; padding: 15px; width: 100%; border-radius: 5px; margin-bottom: 10px;'>";
            echo html_entity_decode($deliverable_item->fields["object_notification"]);
            echo "</div>";

            echo "<div style='background-color: #e7e7e7; padding: 15px; width: 100%; border-radius: 5px'>";
            echo html_entity_decode($deliverable_item->fields["comment"]);
            echo "</div>";
        echo "</div>";


        echo "<div style='width: 100%'>";
            echo "<h3> Approbation </h3>";

            echo "<div style='background-color: #e7e7e7; padding: 15px; width: 100%; border-radius: 5px; margin-bottom: 10px;'>";
            echo html_entity_decode($deliverable_item->fields["object_approval"]);
            echo "</div>";

            if($deliverable_item->fields["text_approval"]) {
                echo "<div style='background-color: #e7e7e7; padding: 15px; width: 100%; border-radius: 5px'>";
                echo $deliverable_item->fields["text_approval"] ? html_entity_decode($deliverable_item->fields["text_approval"]) : "";
                echo "</div>";
            }
        echo "</div>";


    echo "</div>";
}
