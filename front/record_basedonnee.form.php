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

/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();
if (isset($_POST['add']) && isset($_POST['plugin_dlteams_records_id'])) {

    $record = new PluginDlteamsRecord();
//    $record->check($_POST[PluginDlteamsRecord::getForeignKeyField()], UPDATE);

    global $DB;

    /* if ($_POST['plugin_genericobject_documentsetcontrats_id'] != 0) {
        //add legal basis
        $item = new PluginDlteamsRecord_Element();

        $item->add($_POST);
     }*/


    $record_item = new PluginDlteamsRecord_Item();
    $id_record_item = $record_item->add([
        "records_id" => $_POST["items_id1"],
        "itemtype" => $_POST["itemtype"],
        "items_id" => $_POST["datacarrier_items_id"],
        "comment" => $_POST["comment"]
    ]);


    $database_item = new PluginDlteamsDataCarrier_Item();
    $db_i = $database_item->add([
        "datacarriers_id" => $_POST["datacarrier_items_id"],
        "itemtype" => $_POST["itemtype1"],
        "items_id" => $_POST["items_id1"],
        "foreign_id" => $id_record_item,
        "comment" => $_POST["comment"]
    ]);

    Session::addMessageAfterRedirect("Ajoutée avec succès");
}

Html::back();
