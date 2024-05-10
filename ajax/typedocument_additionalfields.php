<?php
include_once('../../../inc/includes.php');

$id = $_POST["id"];
echo "<div style='display: flex; flex-direction: column; gap: 4px;'>";
    $output = "";


        echo "<span>";

        $object = new Document();
        $object->getFromDB($id);

        $output.="<a href='/front/document.send.php?docid=".$id."' target='_blank'>".$object->getField("filename")."</a>";
        echo $output;
        echo "</span>";

        echo "<span>";
        echo sprintf("<a href='%s' target='_blank'>%s</a>", $object->getField("link"), $object->getField("link"));
        echo "</span>";


        echo "<span>";
            $documentcategorie = new DocumentCategory();
            $documentcategorie->getFromDB($object->fields["documentcategories_id"]);

            echo $documentcategorie->fields["name"];
        echo "</span>";

        echo "<span>";
        echo $object->fields["comment"];
        echo "</span>";



echo "</div>";
