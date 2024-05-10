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
//    if(isset($_POST["itemtype1"]) && isset($_POST["items_id1"]) && isset($_POST["itemtype"]) && isset($_POST["items_id"])){
//    }
//    else{
//        Session::addMessageAfterRedirect("Veuillez indiquez les comptes pouvant accéder à ce catalogue et leur rôle", false, ERROR);
//        Html::back();
//    }


    global $DB;
    $DB->beginTransaction();


    if ((!isset($_POST["accountkeys_id"]) || !$_POST["accountkeys_id"]) && $_POST["itemtype1"] == PluginDlteamsDataCatalog::class) {
        Session::addMessageAfterRedirect("Veuillez indiquez le comptes pouvant accéder à ce catalogue", false, ERROR);
    }

    if($_POST["accountkeys_id"] && $_POST["itemtype1"] == PluginDlteamsDataCatalog::class){
        $datacatalog_item = new PluginDlteamsDataCatalog_Item();

        $array2 = [
            "datacatalogs_id" => $_POST["items_id1"],
            "itemtype" => $_POST["itemtype"],
            "items_id" => $_POST["items_id"],
            "comment" => $_POST["comment"],
        ];
        if (isset($_POST["profiles_id"])) {
            $array2["plugin_dlteams_userprofiles_id"] = $_POST["profiles_id"];
        }

        $datacatalog_item->add($array2);



    }


    $array1 = [
        "plugin_accounts_accounts_id" => $_POST["items_id"],
        "itemtype" => $_POST["itemtype1"],
        "items_id" => $_POST["items_id1"],
        "comment" => $_POST["comment"],
    ];
    if (isset($_POST["userprofiles_id"])) {
        $array1["plugin_dlteams_userprofiles_id"] = $_POST["userprofiles_id"];
    }



//    $datacatalog->get

    $temp = $_POST["itemtype"] . "_Item";
    $itemtype_item1 = new $temp();
    if ($result = $DB->insert($itemtype_item1->getTable(), $array1)) {

    } else {
        $DB->rollBack();
        Session::addMessageAfterRedirect("Une erreur s'est produite dans la relation " . $itemtype_item1::getTypeName(), false, ERROR);
        Html::back();
    }


    $temp = $_POST["itemtype1"] . "_Item";
    $itemtype_item = new $temp();
    if (!$DB->insert($itemtype_item->getTable(), $array2)) {
        if (Session::DEBUG_MODE)
            Session::addMessageAfterRedirect($DB->error(), false, ERROR);
        $DB->rollBack();
        Session::addMessageAfterRedirect("Une erreur s'est produite dans la relation " . $itemtype_item1::getTypeName(), false, ERROR);
        Html::back();
    }


//    apply on children
    if (isset($_POST["apply_on_childs"]) && $_POST["apply_on_childs"] && $_POST["apply_on_childs"] == '1') {
        $children_request = [
            "FROM" => PluginDlteamsDataCatalog::getTable(),
            "WHERE" => [
                "plugin_dlteams_datacatalogs_id" => $_POST["items_id1"]
            ]
        ];
        $iterator = $DB->request($children_request);
        if ($iterator) {
            foreach ($iterator as $key => $child) {
                $array1 = [
                    "plugin_accounts_accounts_id" => $_POST["items_id"],
                    "itemtype" => $_POST["itemtype1"],
                    "items_id" => $child["id"],
                    "comment" => $_POST["comment"],
                ];
                if (isset($_POST["userprofiles_id"])) {
                    $array1["plugin_dlteams_userprofiles_id"] = $_POST["userprofiles_id"];
                }


                $array2 = [
                    "datacatalogs_id" => $child["id"],
                    "itemtype" => $_POST["itemtype"],
                    "items_id" => $_POST["items_id"],
                    "comment" => $_POST["comment"],
                ];
                if (isset($_POST["userprofiles_id"])) {
                    $array2["plugin_dlteams_userprofiles_id"] = $_POST["userprofiles_id"];
                }


                $temp = $_POST["itemtype"] . "_Item";
                $itemtype_item1 = new $temp();
                if ($result = $DB->insert($itemtype_item1->getTable(), $array1)) {
                    highlight_string("<?php\n\$data =\n" . var_export("result " . $key, true) . ";\n?>");
                    highlight_string("<?php\n\$data =\n" . var_export($result, true) . ";\n?>");
                } else {
                    $DB->rollBack();
                    Session::addMessageAfterRedirect("Une erreur s'est produite dans la relation enfant " . $itemtype_item1::getTypeName(), false, ERROR);
                    Html::back();
                }


                $temp = $_POST["itemtype1"] . "_Item";
                $itemtype_item = new $temp();
                if ($DB->insert($itemtype_item->getTable(), $array2)) {
                    highlight_string("<?php\n\$data =\n" . var_export("result2 " . $key, true) . ";\n?>");
                    highlight_string("<?php\n\$data =\n" . var_export($result, true) . ";\n?>");
                } else {
                    $DB->rollBack();
                    Session::addMessageAfterRedirect("Une erreur s'est produite dans la relation enfant " . $itemtype_item1::getTypeName(), false, ERROR);
                    Html::back();
                }
            }
        }
    }

    $DB->commit();
    Session::addMessageAfterRedirect("Opération rélisé avec succès");
    Html::back();
} else
    Html::back();
