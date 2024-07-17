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

if (!isset($_GET['id'])) {
    $_GET['id'] = "";
}
/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();

$datacatalog = new PluginDlteamsDataCatalog();

if (isset($_POST['add'])) {

    $datacatalog->check(-1, CREATE, $_POST);
    $data = $_POST;
    //$data["completename"] = addslashes($_POST["name"]);
    $data["completename"] = $_POST["name"];
    if (isset($_POST["plugin_dlteams_datacatalogs_id"]) && $_POST["plugin_dlteams_datacatalogs_id"]) {
        $datacatalog_parent = new PluginDlteamsDataCatalog();
        $datacatalog_parent->getFromDB($_POST["plugin_dlteams_datacatalogs_id"]);

        //$data["completename"] = $datacatalog_parent->fields["name"] . " > " . addslashes($_POST["name"]);
        $data["completename"] = $datacatalog_parent->fields["name"] . " > " . $_POST["name"];
    }


    unset($data["contact_itemtype"]);
    unset($data["contacts_id"]);

    $id = $datacatalog->add($data);

    Html::redirect($datacatalog->getFormURLWithID($id));

}
else if (isset($_POST['update'])) {
    $data = $_POST;

    //$data["completename"] = addslashes($_POST["name"]);
    $data["completename"] = $_POST["name"];
    if (isset($_POST["plugin_dlteams_datacatalogs_id"]) && $_POST["plugin_dlteams_datacatalogs_id"]) {
        $datacatalog_parent = new PluginDlteamsDataCatalog();

        $datacatalog_parent->getFromDB($_POST["plugin_dlteams_datacatalogs_id"]);

        //$data["completename"] = $datacatalog_parent->fields["name"] . " > " . addslashes($_POST["name"]);
        $data["completename"] = $datacatalog_parent->fields["name"] . " > " . $_POST["name"];
    }
    $datacatalog->check($data['id'], UPDATE);


    unset($data["contact_itemtype"]);
    unset($data["contacts_id"]);

//    if(isset($_POST["contact_itemtype"])){
//        $contact_item_str = $_POST["contact_itemtype"];
//        $contact_item = new $contact_item_str();
//        $column_id_name = strtolower(str_replace("PluginDlteams", "", $contact_item_str))."s_id";
//        $contact_item->add([
//            $column_id_name => $_POST["contacts_id"],
//            "itemtype" => "PluginDlteamsDataCatalog",
//            "items_id" => $data['id'],
//        ]);
//
//        $datacatalog_item = new PluginDlteamsDataCatalog_Item();
//        $datacatalog_item->add([
//            "datacatalogs_id" => $data['id'],
//            "itemtype" => $_POST["contact_itemtype"],
//            "items_id " => $_POST["contacts_id"]
//        ]);
//    }

    //$data["name"] = addslashes($data["name"]);


    if($datacatalog->fields["is_helpdesk_visible"] != $_POST["is_helpdesk_visible"]){
        global $DB;
        $count = countElementsInTable(PluginDlteamsAccountKey::getTable(), ["plugin_dlteams_datacatalogs_id" => $data["id"]]);
        if($count>0){
            $result = $DB->update(
                PluginDlteamsAccountKey::getTable(),
                [
                    "plugin_dlteams_datacatalogs_id" => 0
                ],
                [
                    "plugin_dlteams_datacatalogs_id" => $data["id"]
                ]
            );

            Session::addMessageAfterRedirect(sprintf("%s clé(s) ont été supprimé de cet annuaire", $count));
        }
        
    }
    $datacatalog->update($data);

    Html::back();
}
elseif (isset($_POST["update_as_child"])) {
    $datacatalog = new PluginDlteamsDataCatalog();
    $datacatalog->getFromDB($_POST["son_id"]);

    $data = ["plugin_dlteams_datacatalogs_id" => $_POST["id"]];

    $datacatalog_parent = new PluginDlteamsDataCatalog();
    $datacatalog_parent->getFromDB($_POST["id"]);

    $data["completename"] = $datacatalog_parent->fields["name"] . " > " . $datacatalog->fields["name"];
    $data["id"] = $_POST["son_id"];

    $datacatalog->check($data['id'], UPDATE);
    $data["update"] = true;

    $datacatalog->update([
        ...$data
    ]);

    Html::back();

}
else if (isset($_POST['delete'])) {

    $datacatalog->check($_POST['id'], DELETE);
    $datacatalog->delete($_POST);
    $datacatalog->redirectToList();

}
else if (isset($_POST['restore'])) {
    $datacatalog->check($_POST['id'], UPDATE);
    $datacatalog->restore($_POST);
    $datacatalog->redirectToList();

}
else if (isset($_POST['purge'])) {
    $datacatalog->check($_POST['id'], PURGE);


//

    if (
        $datacatalog->isUsed()
        && empty($_POST["forcepurge"])
    ) {
        Html::header(
            $datacatalog->getTypeName(1),
            $_SERVER['PHP_SELF'],
            "admin",
            "datacatalog",
            str_replace('glpi_', '', $datacatalog->getTable())
        );

        $datacatalog->showDeleteConfirmForm($_SERVER['PHP_SELF']);
        Html::footer();
    } else {

        //    supprimer tous les comptes de l'annuaire
        $accountkey = new PluginDlteamsAccountKey();
        global $DB;
        $DB->update(
            $accountkey->getTable(),
            [
                "plugin_dlteams_datacatalogs_id" => 0
            ],
            [
                "plugin_dlteams_datacatalogs_id" => $_POST['id']
            ]
        );
        Session::addMessageAfterRedirect("Tous les comptes ont été supprimés de l'annuaire");

        $datacatalog->delete($_POST, 1);
        Event::log(
            $_POST["id"],
            "datacatalogs",
            4,
            "setup",
            //TRANS: %s is the user login
            sprintf(__('%s purges an item'), $_SESSION["glpiname"])
        );
        $datacatalog->redirectToList();
    }

//

}
elseif (isset($_POST["add_other_directory"])) {

    $datacatalog_item = new PluginDlteamsDataCatalog_Item();
    $datacatalog_item->add([
        "datacatalogs_id" => $_POST["datacatalogs_id"],
        "itemtype" => PluginDlteamsDataCatalog::class,
        "items_id" => $_POST["items_id"],
        "is_directory" => 1
    ]);


    Session::addMessageAfterRedirect("Ajouté avec succès");
    Html::back();
}
else {
    $datacatalog->checkGlobal(READ);

    if (Session::getCurrentInterface() == 'central') {
        Html::header(PluginDlteamsDataCatalog::getTypeName(2), '', 'dlteams', 'plugindlteamsmenu', 'datacatalog');
    } else {
        Html::helpHeader(PluginDlteamsDataCatalog::getTypeName(0));
    }

    $datacatalog->display(['id' => $_GET['id']]);

    if (Session::getCurrentInterface() == 'central') {
        Html::footer();
    } else {
        Html::helpFooter();
    }

}
