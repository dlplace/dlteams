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

/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();
if (!isset($_GET['id'])) {
    $_GET['id'] = "";
}

$record = new PluginDlteamsAccountKey();

if (isset($_POST['add'])) {

    $record->check(-1, CREATE, $_POST);

    $id = $record->add($_POST);
    addAccountCatalogueRelation($id);
    Html::redirect($record->getFormURLWithID($id));

} else if (isset($_POST['update'])) {

    $record->check($_POST['id'], UPDATE);
    $record->update($_POST);
    addAccountCatalogueRelation($_POST['id']);
    Html::back();

} else if (isset($_POST['delete'])) {

    $record->check($_POST['id'], DELETE);
    $record->delete($_POST);
    $record->redirectToList();

} else if (isset($_POST['purge'])) {

    $record->check($_POST['id'], PURGE);
    $record->delete($_POST, true);
    $record->redirectToList();

} else {

    $record->checkGlobal(READ);

    if (Session::getCurrentInterface() == 'central') {
        Html::header(PluginDlteamsAccountKey::getTypeName(2), '', 'dlteams', 'PluginDlteamsmenu', 'accountkey');
    } else {
        Html::helpHeader(PluginDlteamsAccountKey::getTypeName(0));
    }

    /*    highlight_string("<?php\n\$data =\n" . var_export($_REQUEST, true) . ";\n?>");*/
    /*    highlight_string("<?php\n\$data =\n" . var_export($_GET, true) . ";\n?>");*/
    /*    highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
    /*        highlight_string("<?php\n\$data =\n" . var_export($_, true) . ";\n?>");*/
//    die();

    $record->display(['id' => $_GET['id']]);

    if (Session::getCurrentInterface() == 'central') {
        Html::footer();
    } else {
        Html::helpFooter();
    }

}

function addAccountCatalogueRelation($accountkey_id)
{
//    if(isset($_POST["storage_location"])){
    global $DB;
    $accountkey_item = new PluginDlteamsAccountKey_Item();
    $user_item = new PluginDlteamsUser_Item();

    $accountkey = new PluginDlteamsAccountKey();
    $accountkey->getFromDB($accountkey_id);
    $accountkey->update([
        "plugin_dlteams_datacatalogs_id" => $_POST["plugin_dlteams_datacatalogs_id"],
        "id" => $accountkey_id
    ]);

    Session::addMessageAfterRedirect("Compte de l'annuaire ajouté avec succès");
    $accountkey = new PluginDlteamsAccountKey();
    $accountkey->getFromDB($accountkey_id);

    $errormessage = "";
    if (isset($_POST["users_idx"]) && count($_POST["users_idx"]) > 0) {
        foreach ($_POST["users_idx"] as $user_id) {
            $user = new User();
            $user->getFromDB($user_id);
            if ($response = $accountkey_item->add([
                    "itemtype" => User::class,
                    "items_id" => $user_id,
                    "accountkeys_id" => $accountkey_id,
                    "users_id" => isset($_POST["profiles_idx"]) && $_POST["profiles_idx"] && in_array(PluginDlteamsAccountKey_Item::$PROFILE_ADMIN, $_POST["profiles_idx"]) ? 0 : $user_id,
                    "users_id_tech" => isset($_POST["profiles_idx"]) && $_POST["profiles_idx"] && in_array(PluginDlteamsAccountKey_Item::$PROFILE_ADMIN, $_POST["profiles_idx"]) ? $user_id : 0,
                    "name" => strtolower($accountkey->fields["name"])
                ]) && $response1 =$user_item->add([
                    "itemtype" => PluginDlteamsAccountKey::class,
                    "items_id" => $accountkey_id,
                    "users_id" => $user_id
                ])) {
                Session::addMessageAfterRedirect(sprintf("Compte <a href='%s'>%s</a> créé et attribué avec succès à %s ", PluginDlteamsAccountKey::getFormURLWithID($accountkey_id), $accountkey->fields["name"], $user->fields["name"]));
                $DB->commit();
            } else {
                $errormessage .= sprintf("erreur d'attribution de %s à %s <br/>", $accountkey->fields["name"], $user->fields["name"]);
                Session::addMessageAfterRedirect($errormessage, 0, ERROR);
            }
        }
    }
//    }

}