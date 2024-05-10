<?php

include_once('../../../inc/includes.php');

if(isset($_POST["policieforms_id"]) && $_POST["datacatalogs_id"]){
    $rand = mt_rand();
    $instID = $_POST["policieforms_id"];
    $catalogid = $_POST["datacatalogs_id"];
    echo "<form name='attribution_form$rand' id='attribution_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
    echo "<input type='hidden' name='itemtype1' value='" .PluginDlteamsPolicieForm::class. "'>";
    echo "<input type='hidden' name='itemtype' value='" .PluginDlteamsRecord::class. "'>";
    echo "<input type='hidden' name='items_id1' value='" . $instID . "'>";

    echo "<table class='tab_cadre_fixe'>";
    $title = "Ajouter un traitement à ce type de document";
    $entitled = "";
    echo "<tr class='tab_bg_2'><th colspan='3'>" . __($title, 'dlteams') .
        "</th>";
    echo "</tr>";


    echo "<tr class='tab_bg_1'>";
//            echo "<td class='right' style='text-wrap: nowrap;'>" ;
//            echo "</td>";
    echo "<td class='left'>";
//            echo "<td style='display: none' id='td2'>";
    echo "<span style='float:left;'>";
    global $CFG_GLPI;

    $canedit = PluginDlteamsDataCatalog::canUpdate();
    $rand = mt_rand(1, mt_getrandmax());

//            echo __("Type d'usager", "dlteams");
//            echo "&nbsp";
    PluginDlteamsRecord::dropdown([
        "name" => "items_id",
        "addicon" => PluginDlteamsRecord::canCreate(),
        "width" => "250px"
    ]);
    echo "</span>";
    echo "<span id='itemtype_row' style='margin-left:5px!important'>";
    echo "</span>";

    echo "</td>";
    echo "</tr>";

    echo "<tr class='tab_bg_1'>";
    echo "<td class='left' id='field_comment_label' >" . __("Comment");
    echo "</td>";

    echo "<tr>";
    echo "<td class='left comment-td' id='field_comment'>";
    echo "<div style='display: flex; gap: 4px;'>";
    echo "<textarea type='text' style='width:60%' maxlength=1000 rows='1' name='comment' class='comment'></textarea>";
    echo "</div>";
    echo "</td>";
    echo "</tr>";
    echo "<table>";

    echo "<div style='width: 100%; display: flex; justify-content: center'>";
    echo "<input for='attribution_form$rand' type='submit' name='link_element' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
    echo "</div>";
    Html::closeForm();
}
elseif($_POST["delete"] && $_POST["linkid"]){

    $record_item = new PluginDlteamsRecord_Item();
    $record_item->delete(["id" => $_POST["linkid"]]);

    Session::addMessageAfterRedirect("Supprimé avec succès");
}