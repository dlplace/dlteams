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

//var_dump(json_encode($_POST));
/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();


$accountkey = new PluginDlteamsAccountKey();
if($accountkey->getFromDB($_POST["accountkeys_id"])){

    $dt_item = new PluginDlteamsDataCatalog_Item();

    $replaced = false;

    if($accountkey->fields["plugin_dlteams_datacatalogs_id"]){
        $replaced = true;
        $catalog_id = $accountkey->fields["plugin_dlteams_datacatalogs_id"];
        $datacatalog = new PluginDlteamsDataCatalog();
        $datacatalog->getFromDB($accountkey->fields["plugin_dlteams_datacatalogs_id"]);
        $name = $datacatalog->fields["name"];
    }


    if($accountkey->update([
        "plugin_dlteams_datacatalogs_id" => $_POST["items_id1"],
        "id" => $_POST["accountkeys_id"]
    ])){
        if($replaced)
            Session::addMessageAfterRedirect(sprintf("Le compte de l'annuaire <a href='%s'>%s</a> a été remplacé avec succès", PluginDlteamsDataCatalog::getFormURLWithID($catalog_id), $name), 0, WARNING);

        Session::addMessageAfterRedirect("Compte ajouté à l'annuaire avec succès");
    }
}
Html::back();