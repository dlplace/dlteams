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


if (isset($_POST["add"])) {

    global $DB;
    $DB->beginTransaction();
    
    if ((!isset($_POST["accountkeys_id"]) || !$_POST["accountkeys_id"]) && $_POST["itemtype1"] == PluginDlteamsDataCatalog::class) {
        Session::addMessageAfterRedirect("Veuillez indiquez le comptes pouvant accéder à ce catalogue", false, ERROR);
    }

    if ($_POST["accountkeys_id"] && $_POST["itemtype1"]) {
        $itemtype1_item_str = $_POST["itemtype1"];
        switch ($itemtype1_item_str) {
            case User::class:
            case Contact::class:
            case Supplier::class:
            case Group::class:
                $itemtype1_item_str = "PluginDlteams" . $itemtype1_item_str . "_Item";
                break;
            default:
                $itemtype1_item_str = $itemtype1_item_str . "_Item";
                break;
        }


        $column_id_name = strtolower(str_replace("PluginDlteams", "", $_POST["itemtype1"])) . "s_id";
        $itemtype1_item = new $itemtype1_item_str();


        $array2 = [
            $column_id_name => $_POST["items_id1"],
            "itemtype" => PluginDlteamsAccountKey::class,
            "items_id" => $_POST["accountkeys_id"],
            "comment" => $_POST["comment"],
        ];



        if (isset($_POST["profiles_id"])) {
            $array2["plugin_dlteams_userprofiles_id"] = $_POST["profiles_id"];
        }

        if ($DB->fieldExists($itemtype1_item->getTable(), 'profiles_json') && isset($_POST["profiles_idx"]))
            $array2["profiles_json"] = json_encode($_POST["profiles_idx"]);

        if(isset($_POST["is_directory"]) && $_POST["is_directory"] && $DB->fieldExists($itemtype1_item::getTable(), 'is_directory')){
            $array2["is_directory"] = true;
        }

//        var_dump($_POST);
//        die();
        if(isset($_POST["users_idx"])){
            $accountkey = new  PluginDlteamsAccountKey();
            $accountkey->getFromDB($_POST["accountkeys_id"]);
            foreach ($_POST["users_idx"] as $user_id){
                $accountkey_item = new PluginDlteamsAccountKey_Item();
                $user_item = new PluginDlteamsUser_Item();
                $user = new User();
                $user->getFromDB($user_id);
                if ($accountkey_item->add([
                        "itemtype" => User::class,
                        "items_id" => $user_id,
                        "accountkeys_id" => $_POST["accountkeys_id"],
                        "users_id" => isset($_POST["profiles_idx"]) && $_POST["profiles_idx"] && in_array(PluginDlteamsAccountKey_Item::$PROFILE_ADMIN, $_POST["profiles_idx"])?0:$user_id,
                        "users_id_tech" => isset($_POST["profiles_idx"]) && $_POST["profiles_idx"] && in_array(PluginDlteamsAccountKey_Item::$PROFILE_ADMIN, $_POST["profiles_idx"])?$user_id:0,
                        "name" => strtolower($accountkey->fields["name"])
                    ]) && $user_item->add([
                        "itemtype" => PluginDlteamsAccountKey::class,
                        "items_id" => $_POST["items_id1"],
                        "users_id" => $user_id
                    ])) {
                    Session::addMessageAfterRedirect(sprintf("Compte <a href='%s'>%s</a> créé et attribué avec succès à %s ", PluginDlteamsAccountKey::getFormURLWithID($_POST["accountkeys_id"]), $accountkey->fields["name"], $user->fields["name"]));
                    $DB->commit();
                } else {
                    $errormessage .= sprintf("erreur d'attribution de %s à %s <br/>", $accountkey->fields["name"], $user->fields["name"]);
                    // Example of ko count
                    $DB->rollback();
                    //$ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                }
            }
        }

//        if(in_array(PROFILE_ADMIN, $_POST["profiles_idx"]))
//            $array2["users_id_tech"] =


        /*        highlight_string("<?php\n\$data =\n" . var_export($array2, true) . ";\n?>");*/
//        die();


        $result_array2 = $itemtype1_item->add($array2);

//        if (isset($_POST["profiles_id"])) {
//            $array1["plugin_dlteams_userprofiles_id"] = $_POST["profiles_id"];
//        }

        if(!isset($_POST["is_directory"]) || !$_POST["is_directory"]){
            $array1 = [
                "accountkeys_id" => $_POST["accountkeys_id"],
                "itemtype" => $_POST["itemtype1"],
                "items_id" => $_POST["items_id1"],
                "comment" => $_POST["comment"],
            ];

            $accountkey_item = new PluginDlteamsAccountKey_Item();

            if ($DB->fieldExists($accountkey_item->getTable(), 'profiles_json') && isset($_POST["profiles_idx"]))
                $array1["profiles_json"] = json_encode($_POST["profiles_idx"]);

            if ($DB->fieldExists($accountkey_item->getTable(), 'users_id_tech') && $_POST["itemtype1"] == User::class)
                $array1["users_id_tech"] = $_POST["items_id1"];

            if ($DB->fieldExists($accountkey_item->getTable(), 'groups_id_tech') && $_POST["itemtype1"] == Group::class)
                $array1["groups_id_tech"] = $_POST["items_id1"];

            if ($DB->fieldExists($accountkey_item->getTable(), 'suppliers_id_tech') && $_POST["itemtype1"] == Supplier::class)
                $array1["suppliers_id_tech"] = $_POST["items_id1"];

            if ($DB->fieldExists($accountkey_item->getTable(), 'contacts_id_tech') && $_POST["itemtype1"] == Contact::class)
                $array1["contacts_id_tech"] = $_POST["items_id1"];

            $accountkey = new PluginDlteamsAccountKey();
            $accountkey->getFromDB($_POST["accountkeys_id"]);

            $array1["name"] = $accountkey->fields["name"];

            $account = $accountkey_item->add($array1);
        }
        else{
            $accountkey = new PluginDlteamsAccountKey();
            $DB->update($accountkey->getTable(), [
                "plugin_dlteams_datacatalogs_id" => $_POST["items_id1"]
            ], ["id" => $_POST["accountkeys_id"]]);
        }
    }

    if ($DB->error()) {
        if (Session::DEBUG_MODE)
            Session::addMessageAfterRedirect($DB->error(), false, ERROR);
        else
            Session::addMessageAfterRedirect("Une erreur s'est produite", false, ERROR);
        $DB->rollBack();
    } else {
        $DB->commit();
        Session::addMessageAfterRedirect("Opération éfectuée avec succès");
    }


}
elseif (isset($_POST["linkid"])) {
    global $DB;
    $DB->beginTransaction();

    if(!isset($_POST["is_directory"]) || !$_POST["is_directory"]){
        $accountkey_item = new PluginDlteamsAccountKey_Item();

        $accountkey_item->getFromDB($_POST["linkid"]);
        $ak_oldvalues = $accountkey_item->fields;
        $newvalues = [
            "accountkeys_id" => $_POST["accountkeys_id"],
            "itemtype" => $_POST["itemtype1"],
            "items_id" => $_POST["items_id1"],
            "profiles_json" => json_encode($_POST["profiles_idx"]),
            "comment" => $_POST["comment"]
        ];

        $result1 = $DB->update($accountkey_item->getTable(), $newvalues, ["id" => $_POST["linkid"]]);

        $itemtype_str = $ak_oldvalues["itemtype"];
    }
    else{
        $datacatalog_item = new PluginDlteamsDataCatalog_Item();
        $datacatalog_item->getFromDB($_POST["linkid"]);
        $ak_oldvalues = $datacatalog_item->fields;


        $itemtype_str = PluginDlteamsDataCatalog::class;
    }



    $itemtype_item_str = $itemtype_str . "_Item";
    if (class_exists($itemtype_item_str))
        $itemtype_item = new $itemtype_item_str();
    else{
        $itemtype_item_str = "PluginDlteams".$itemtype_item_str;
        $itemtype_item = new $itemtype_item_str();
    }
//    if (str_contains($itemtype_str, "PluginDlteams")) {
    $item = new $itemtype_item();

    if(!isset($_POST["is_directory"])){
        $column_id_name = strtolower(str_replace("PluginDlteams", "", $itemtype_str)) . "s_id";
        $criteria = [
            $column_id_name => $ak_oldvalues["items_id"],
            "itemtype" => "PluginDlteamsAccountKey",
            "items_id" => $ak_oldvalues["accountkeys_id"],
            "comment" => $ak_oldvalues["comment"]
        ];

        if ($DB->fieldExists($itemtype_item->getTable(), "profiles_json"))
            $criteria["profiles_json"] = $ak_oldvalues["profiles_json"];

        $itemtype_item->getFromDBByCrit($criteria);
        $newvalues = [
            $column_id_name => $_POST["items_id1"],
            "itemtype" => "PluginDlteamsAccountKey",
            "items_id" => $_POST["accountkeys_id"],
            "comment" => $_POST["comment"]
        ];

        if ($DB->fieldExists($itemtype_item->getTable(), "profiles_json"))
            $newvalues["profiles_json"] = json_encode($_POST["profiles_idx"]);


        $result2 = $DB->update($itemtype_item->getTable(), $newvalues, ["id" => $itemtype_item->fields["id"]]);
    }
    else{
//

        $newvalues = [
            "datacatalogs_id" => $_POST["items_id1"],
            "itemtype" => "PluginDlteamsAccountKey",
            "items_id" => $_POST["accountkeys_id"],
            "comment" => $_POST["comment"]
        ];



        $result2 = $DB->update(PluginDlteamsDataCatalog_Item::getTable(), $newvalues, ["id" => $_POST["linkid"]]);
    }


    if ($result2 && $result1) {
        $DB->commit();
        Session::addMessageAfterRedirect("Opération éffectuée avec succès");
    } else {
        if (Session::DEBUG_MODE)
            Session::addMessageAfterRedirect($DB->error(), false, ERROR);
        else
            Session::addMessageAfterRedirect("Une erreur s'est produite", false, ERROR);
        $DB->rollBack();
    }

    Html::back();

//    }

//    Session::addMessageAfterRedirect("Une erreur s'est produite", false, ERROR);
}
Html::back();
