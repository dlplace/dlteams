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

//var_dump(json_encode($_POST["profiles_idx"]));
//die();
/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();


if (isset($_POST["add"])) {
    global $DB;
    $error = false;

    $DB->beginTransaction();
    if (isset($_POST["itemtype"]) && isset($_POST["items_id"])) {
        $array1 = [
            "accountkeys_id" => $_POST["accountkeys_id"],
            "itemtype" => $_POST["itemtype"],
            "items_id" => $_POST["items_id"],
            "comment" => $_POST["comment"],
        ];

//        if (isset($_POST["profiles_id"])) {
//            $array1["plugin_dlteams_userprofiles_id"] = $_POST["profiles_id"];
//        }
        $accountkey_item = new PluginDlteamsAccountKey_Item();

        $result = $accountkey_item->add($array1);
        if(!$result)
            $error = true;


        $itemtype_item_str = "PluginDlteams" . $_POST["itemtype"] . "_Item";
        $itemtype_item_column_id = strtolower($_POST["itemtype"]) . "s_id";
        $itemtype_item = new $itemtype_item_str();
        if(!$itemtype_item->add([
            $itemtype_item_column_id => $_POST["items_id"],
            "itemtype" => PluginDlteamsAccountKey::class,
            "items_id" => $_POST["accountkeys_id"],
            "comment" => $_POST["comment"],
        ]))
            $error = true;

        if ($error) {
            if (Session::DEBUG_MODE)
                Session::addMessageAfterRedirect($DB->error(), false, ERROR);

            Session::addMessageAfterRedirect("Une erreur s'est produite", false, ERROR);

            $DB->rollBack();

        } else {
            Session::addMessageAfterRedirect("Opération éffectuée avec succès");
            $DB->commit();
        }
    }
}
Html::back();
