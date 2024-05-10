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

if (isset($_POST['consent_type']) && $_POST['consent_type'] && $_POST['plugin_dlteams_records_id'] && $_POST['add1']) {
    $record_item = new PluginDlteamsRecord_Item();

    switch ($_POST["consent_type"]) {
        case 1:
            if ($_POST['groups_id']) {
                $r_id = $record_item->add([
                    'records_id' => $_POST['plugin_dlteams_records_id'],
                    'itemtype' => 'Group',
                    'items_id' => $_POST['groups_id'],
                    'comment' => $_POST['comment']
                ]);

                $group_item = new PluginDlteamsGroup_Item();
                $group_item->add([
                    'groups_id' => $_POST['groups_id'],
                    'itemtype' => 'PluginDlteamsRecord',
                    'items_id' => $_POST['plugin_dlteams_records_id'],
                    'comment' => $_POST['comment'],
                    'foreign_id' => $r_id
                ]);

                Session::addMessageAfterRedirect("Groupe responsable ajouté avec succès");
            }
            break;
        case 2:
            if ($_POST['users_id']) {
                $r_id = $record_item->add([
                    'records_id' => $_POST['plugin_dlteams_records_id'],
                    'itemtype' => 'User',
                    'items_id' => $_POST['users_id'],
                    'comment' => $_POST['comment']
                ]);

                $group_item = new PluginDlteamsUser_Item();
                $u = $group_item->add([
                    'users_id' => $_POST['users_id'],
                    'itemtype' => 'PluginDlteamsRecord',
                    'items_id' => $_POST['plugin_dlteams_records_id'],
                    'comment' => $_POST['comment'],
                    'foreign_id' => $r_id
                ]);

                Session::addMessageAfterRedirect("Utilisateur responsable ajouté avec succès");
            }
            break;
        case 3:
            if ($_POST['plugin_dlteams_thirdpartycategories_id1']) {
                $r_id = $record_item->add([
                    'records_id' => $_POST['plugin_dlteams_records_id'],
                    'itemtype' => 'PluginDlteamsThirdPartyCategory',
                    'items_id' => $_POST['plugin_dlteams_thirdpartycategories_id1'],
                    'comment' => $_POST['comment']
                ]);

                $group_item = new PluginDlteamsThirdPartyCategory_Item();
                $group_item->add([
                    'thirdpartycategories_id' => $_POST['plugin_dlteams_thirdpartycategories_id1'],
                    'itemtype' => 'PluginDlteamsRecord',
                    'items_id' => $_POST['plugin_dlteams_records_id'],
                    'comment' => $_POST['comment'],
                    'foreign_id' => $r_id
                ]);

                Session::addMessageAfterRedirect("Tier catégorie responsable ajouté avec succès");
            }
            break;
        case 4:
            if ($_POST['suppliers_id1']) {
                $r_id = $record_item->add([
                    'records_id' => $_POST['plugin_dlteams_records_id'],
                    'itemtype' => 'Supplier',
                    'items_id' => $_POST['suppliers_id1'],
                    'comment' => $_POST['comment']
                ]);

                $group_item = new PluginDlteamsSupplier_Item();
                $group_item->add([
                    'suppliers_id' => $_POST['suppliers_id1'],
                    'itemtype' => 'PluginDlteamsRecord',
                    'items_id' => $_POST['plugin_dlteams_records_id'],
                    'comment' => $_POST['comment'],
                    'foreign_id' => $r_id
                ]);

                Session::addMessageAfterRedirect("Fournisseur responsable ajouté avec succès");
            }
            break;
    }

} else {
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
            $baseItem_items = new $itemtype_item();

            $i1_itemsid_column = strtolower(str_replace("PluginDlteams", "", $itemtype)) . "s_id";
            $i1 = [
                $i1_itemsid_column => $_POST["items_id"],
                "items_id" => $_POST["items_id1"],
                "itemtype" => $_POST["itemtype1"],
                "comment" => $_POST["comment"],
            ];
            $test = $baseItem_items->add($i1);

            if ($itemtype1 == "PluginDlteamsThirdPartyCategory")
                $i2_itemsid_column = "thirdpartycategories_id";
            else
                $i2_itemsid_column = strtolower(str_replace("PluginDlteams", "", $itemtype1)) . "s_id";
            $i2 = [
                $i2_itemsid_column => $_POST["items_id1"],
                "items_id" => $_POST["items_id"],
                "itemtype" => $_POST["itemtype"],
                "comment" => $_POST["comment"],
            ];
            $second_items = new $itemtype1_item();
            $test1 = $DB->insert($second_items->getTable(), $i2);

            Session::addMessageAfterRedirect("Ajouté avec succès");
        }
    }

    Html::back();
}

Html::back();
