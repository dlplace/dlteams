<?php

include_once('../../../inc/includes.php');

$rand = mt_rand();
$instID = $_POST["accountkeys_id"];
$catalogid = $_POST["datacatalogs_id"];
echo "<form name='attribution_form$rand' id='attribution_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsAccountKey_Attribution::class) . "'>";
echo "<input type='hidden' name='accountkeys_id' value='$instID'>";
echo "<input type='hidden' name='itemtype1' value='" . PluginDlteamsAccountKey::class . "'>";
echo "<input type='hidden' name='items_id1' value='" . $instID . "'>";

echo "<table class='tab_cadre_fixe'>";
$title = "Attributions à ce compte";
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
$rand = Dropdown::showFromArray("itemtype_ref", [
    __("------", 'dlteams'),
    __("Groupe", 'dlteams'),
    __("Utilisateur", 'dlteams'),
    __(Contact::getTypeName(), 'dlteams'),
    __(Supplier::getTypeName(), 'dlteams'),
], [
    'value' => 0,
    'width' => 'auto'
]);


$params = [
    'itemtype_ref' => '__VALUE__',
    'datacatalogs_id' => $catalogid,
];
Ajax::updateItemOnSelectEvent(
    "dropdown_itemtype_ref$rand",
    'itemtype_row',
    $CFG_GLPI['root_doc'] . '/marketplace/dlteams/ajax/account_item_dropdown.php',
    $params
);
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
echo "<input for='attribution_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
echo "</div>";
Html::closeForm();

