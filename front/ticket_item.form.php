<?php
/*
 -------------------------------------------------------------------------
 DLTeams plugin for GLPI
 -------------------------------------------------------------------------
 LICENSE : This file is part of DLTeams Plugin.

 DLTeams Plugin is a GNU Free Copylefted software.
 It disallow others people than DLPlace developers to distribute, sell,
 or add additional requirements to this software.
 Though, a limited set of safe added requirements can be allowed, but
 for private or internal usage only ;  without even the implied warranty
 of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

 You should have received a copy of the GNU General Public License
 along with DLTeams Plugin. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
  @package   dlteams
  @author    DLPlace developers
  @copyright Copyright (c) 2022 DLPlace
  @inspired	 DPO register plugin (Karhel Tmarr) & gdprropa (Yild)
  @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
  @link      https://github.com/dlplace/dlteams
  @since     2021
 --------------------------------------------------------------------------
 */

include("../../../inc/includes.php");

if(!isset($_POST["link_item"])){
    $tablename = getTableNameForForeignKeyField(array_key_first($_POST));
    $itemtype = getItemTypeForTable($tablename);
    /*highlight_string("<?php\n\$data =\n" . var_export($tablename, true) . ";\n?>");*/
//die();
    $_POST["itemtype"] = $itemtype;
    $_POST["items_id"] = $_POST[array_key_first($_POST)];
    $_POST["itemtype1"] = Ticket::class;
    $_POST["items_id1"] = $_POST["tickets_id"];
    Session::checkLoginUser();

    if (isset($_POST['add'])) {
        $itemtype = $_POST['itemtype']; // ex: PluginDlteamsProtectiveMeasure
        $itemtype1 = $_POST['itemtype1']; // ex: PluginDlteamsRecord
        $itemtype_item = $itemtype . '_Item'; // ex: PluginDlteamsProtectiveMeasure_Item
        $itemtype1_item = $itemtype1 . '_Item'; // ex: PluginDlteamsRecord_Item
        $baseItem = new $itemtype();
//    $baseItem->check($_POST[$itemtype::getForeignKeyField()], UPDATE);

        global $DB;

        if (isset($_POST['items_id'])) {
            //add baseItem side
            if (class_exists($itemtype_item)) {
                $baseItem_items = new $itemtype_item();
                $i1_itemsid_column = strtolower(str_replace("PluginDlteams", "", $itemtype)) . "s_id";
                $i1 = [
                    $i1_itemsid_column => $_POST["items_id"],
                    "items_id" => $_POST["items_id1"],
                    "itemtype" => $_POST["itemtype1"],
//            "comment" => $_POST["comment"],
                ];
                if (getTableForItemType($itemtype_item))
                    $test = $baseItem_items->add($i1);
            }

            $i2_itemsid_column = strtolower(str_replace("PluginDlteams", "", $itemtype1)) . "s_id";
            $i2 = [
                $i2_itemsid_column => $_POST["items_id1"],
                "items_id" => $_POST["items_id"],
                "itemtype" => $_POST["itemtype"],
                "comment" => $_POST["comment"],
            ];
            $second_items = new Item_Ticket();
            $test1 = $second_items->add([
                "itemtype" => $_POST["itemtype"],
                "items_id" => $_POST["items_id"],
                "tickets_id" => $_POST["tickets_id"],
            ]);

            Session::addMessageAfterRedirect("Ajouté avec succès");
        }
    }
}

if(isset($_POST["link_item"])){
//    depuis ticket
    $itemtype = $_POST['itemtype']; // ex: PluginDlteamsProtectiveMeasure
    $itemtype1 = $_POST['itemtype1']; // ex: PluginDlteamsRecord
    $itemtype_item = $itemtype . '_Item'; // ex: PluginDlteamsProtectiveMeasure_Item
//    $itemtype1_item = $itemtype1 . '_Item'; // ex: PluginDlteamsRecord_Item
    $baseItem = new $itemtype();
//    $baseItem->check($_POST[$itemtype::getForeignKeyField()], UPDATE);

    global $DB;

    if (isset($_POST['items_id'])) {
//        ticket
        $i2_itemsid_column = strtolower(str_replace("PluginDlteams", "", $itemtype1)) . "s_id";
        $second_items = new Item_Ticket();
        $i2 = [
            $i2_itemsid_column => $_POST["items_id1"],
            "items_id" => $_POST["items_id"],
            "itemtype" => $_POST["itemtype"],
//            "comment" => $_POST["comment"],
        ];


        $test1 = $second_items->add($i2);

        //add baseItem side
        $baseItem_items = new $itemtype_item();

        $i1_itemsid_column = strtolower(str_replace("PluginDlteams", "", $itemtype)) . "s_id";
        /*        highlight_string("<?php\n\$data =\n" . var_export($i1_itemsid_column, true) . ";\n?>");*/
//        die();
        $i1 = [
            $i1_itemsid_column => $_POST["items_id"],
            "items_id" => $_POST["items_id1"],
            "itemtype" => $_POST["itemtype1"],
//            "comment" => $_POST["comment"],
        ];

        $test = $baseItem_items->add($i1);
        Session::addMessageAfterRedirect("Ajouté avec succès");
    }
}

Html::back();
