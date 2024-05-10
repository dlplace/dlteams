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


Session::checkLoginUser();
/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();
if (isset($_POST['add'])) {

    $mandatory = $_POST["mandatory"];

    $base_id = $_POST["base_id"];
    $base = $_POST["baseitem"];
    $base_item = $base . "_Item";


    $items_id = $_POST["items_id"];
    $itemtype = $_POST['itemtype']; // ex: PluginDlteamsConcernedPerson
    $itemtype_item = $itemtype . '_Item'; // ex: PluginDlteamsConcernedPerson_Item

    $items_id1 = $_POST["items_id1"];
    $itemtype1 = $_POST['itemtype1']; // ex: PluginDlteamsProcessedData
    $itemtype1_item = $itemtype1 . '_Item'; // ex: PluginDlteamsProcessedData_Item


    global $DB;
    if (isset($_POST["recordsrelies"])) {
        if (isset($_POST['items_id']) || $_POST["items_id1"]) {
            //add baseItem side
            $base_item_instance = new $base_item();

            $baseitemid_column_str = strtolower(str_replace("PluginDlteams", "", $base)) . "s_id";
            $base_array = [
                $baseitemid_column_str => $base_id,
                "items_id" => $items_id,
                "itemtype" => $itemtype,
                "itemtype1" => $itemtype1,
                "items_id1" => $items_id1,
                "mandatory" => $mandatory
            ];

            $base_insert_result = $base_item_instance->add($base_array);


            //add item 1 side
            if ($items_id && $items_id > 0) {
                $itemtype_item_instance = new $itemtype_item();

                $itemtypeid_column_str = strtolower(str_replace("PluginDlteams", "", $itemtype)) . "s_id";
                $itemtype_array = [
                    $itemtypeid_column_str => $items_id,
                    "items_id" => $base_id,
                    "itemtype" => $base,
                    "itemtype1" => $itemtype1,
                    "items_id1" => $items_id1,
                    "foreign_id" => $base_insert_result,
                ];

                $itemtype_insert_result = $itemtype_item_instance->add($itemtype_array);
            }


            //add item 2 side
            if ($items_id && $items_id > 0) {
                $itemtype1_item_instance = new $itemtype1_item();

                $itemtype1id_column_str = strtolower(str_replace("PluginDlteams", "", $itemtype1)) . "s_id";
                $itemtype1_array = [
                    $itemtype1id_column_str => $items_id1,
                    "items_id" => $base_id,
                    "itemtype" => $base,
                    "itemtype1" => $itemtype,
                    "items_id1" => $items_id,
                    "foreign_id" => $base_insert_result,
                ];

                $itemtype1_insert_result = $itemtype1_item_instance->add($itemtype1_array);
            }

        }
    }
    else{
        if (isset($_POST['items_id']) || $_POST["items_id1"]) {
            //add baseItem side
            $base_item_instance = new $base_item();

            $baseitemid_column_str = strtolower(str_replace("PluginDlteams", "", $base)) . "s_id";
            $base_array = [
                $baseitemid_column_str => $base_id,
                "items_id" => $items_id,
                "itemtype" => $itemtype,
                "itemtype1" => $itemtype1,
                "items_id1" => $items_id1,
                "mandatory" => $mandatory
            ];

            $base_insert_result = $base_item_instance->add($base_array);


            //add item 1 side
            if ($items_id && $items_id > 0) {
                $itemtype_item_instance = new $itemtype_item();

                $itemtypeid_column_str = strtolower(str_replace("PluginDlteams", "", $itemtype)) . "s_id";
                $itemtype_array = [
                    $itemtypeid_column_str => $items_id,
                    "items_id" => $base_id,
                    "itemtype" => $base,
                    "itemtype1" => $itemtype1,
                    "items_id1" => $items_id1,
                    "foreign_id" => $base_insert_result,
                ];

                $itemtype_insert_result = $itemtype_item_instance->add($itemtype_array);
            }


            //add item 2 side
            if ($items_id && $items_id > 0) {
                $itemtype1_item_instance = new $itemtype1_item();

                $itemtype1id_column_str = strtolower(str_replace("PluginDlteams", "", $itemtype1)) . "s_id";
                $itemtype1_array = [
                    $itemtype1id_column_str => $items_id1,
                    "items_id" => $base_id,
                    "itemtype" => $base,
                    "itemtype1" => $itemtype,
                    "items_id1" => $items_id,
                    "foreign_id" => $base_insert_result,
                ];

                $itemtype1_insert_result = $itemtype1_item_instance->add($itemtype1_array);
            }

        }
    }
}

Session::addMessageAfterRedirect(__('Ajoutée avec succès'));

Html::back();
