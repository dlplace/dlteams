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

global $DB;
if (isset($_POST['add'])) {

    if (isset($_POST["datacatalogs_idx"]) && count($_POST["datacatalogs_idx"]) > 0) {

//        supprimer les doublons
        $_POST["datacatalogs_idx"] = array_unique($_POST["datacatalogs_idx"]);

        $DB->beginTransaction();

        foreach ($_POST["datacatalogs_idx"] as $datacatalogs_id){
            addRelation($datacatalogs_id);
        }
        if($DB->commit()){
            Session::addMessageAfterRedirect("Opération réalisée avec succès");
        }
    }
}
else{

}
Html::back();


function addRelation($datacatalogs_id)
{

    $datacatalog_item = new PluginDlteamsDataCatalog_Item();
    $data = [
        "datacatalogs_id" => $datacatalogs_id,
        "itemtype" => "PluginDlteamsProtectiveMeasure",
        "items_id" => $_POST["protectivemeasures_id"],
//        "itemtype1" => "PluginDlteamsDeliverable",
//        "items_id1" => $_POST["deliverables_id"],
        "comment" => $_POST["comment"]
    ];
    global $DB;

    if (!$datacatalog_item->add($data)) {

        Session::addMessageAfterRedirect($DB->error(), false, ERROR);
        Session::addMessageAfterRedirect("Une erreur s'est produite", false, ERROR);
        Html::back();

    }

    $data = [
        "protectivemeasures_id" => $_POST["protectivemeasures_id"],
        "itemtype" => "PluginDlteamsDataCatalog",
        "items_id" => $datacatalogs_id,
//        "itemtype1" => "PluginDlteamsDeliverable",
//        "items_id1" => $_POST["deliverables_id"],
        "comment" => $_POST["comment"]
    ];

    $protectivemeasure_item = new PluginDlteamsProtectiveMeasure_Item();
    if (!$protectivemeasure_item->add($data)) {
        Session::addMessageAfterRedirect("Une erreur s'est produite");
        Html::back();
    }

//
//    $data = [
//        "deliverables_id" => $_POST["deliverables_id"],
//        "itemtype" => "PluginDlteamsDataCatalog",
//        "items_id" => $datacatalogs_id,
//        "itemtype1" => "PluginDlteamsProtectiveMeasure",
//        "items_id1" => $_POST["protectivemeasures_id"],
//        "comment" => $_POST["comment"]
//    ];
//    $deliverable_item = new PluginDlteamsDeliverable_Item();
//    if (!$deliverable_item->add($data)) {
//        Session::addMessageAfterRedirect("Une erreur s'est produite");
//        Html::back();
//    }

}
