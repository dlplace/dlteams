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
global $DB;
$controller_info = new PluginDlteamsControllerInfo();
$glpiRoot = str_replace('\\', '/', GLPI_ROOT);
function rrmdir($dir)
{
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir . "/" . $object) == "dir") rrmdir($dir . "/" . $object); else unlink($dir . "/" . $object);
            }
        }
        reset($objects);
        rmdir($dir);
    }
}

if (isset($_POST['add'])) {
    $controller_info->check(-1, CREATE, $_POST);
    $controller_info->add($_POST);
} else if (isset($_POST['update'])) {
    $controller_info->check($_POST['id'], UPDATE, $_POST);
    $controller_info->update($_POST);
} else if (isset($_POST['delete_guid'])) {
    $guid_value = $_POST['guid_value'];
    $id_value = $_POST['id_value'];
    if (file_exists($glpiRoot . "/" . "pub" . "/" . $guid_value . "/")) {
        rrmdir($glpiRoot . "/" . "pub" . "/" . $guid_value . "/");
    }
    if (!file_exists($glpiRoot . "/" . "pub" . "/" . $guid_value . "/")) {
        $query = "UPDATE `glpi_plugin_dlteams_controllerinfos` SET guid='0' WHERE entities_id='$id_value'";
        $DB->queryOrDie($query, $DB->error());
    }
} else if (isset($_POST['generate_guid'])) {
    $controller_info->check(-1, CREATE, $_POST);
    $controller_info->add($_POST);
    $guid_value = $_POST['guid_value'];
    $id_value = $_POST['id_value'];

    
    if ($guid_value == 0 || $guid_value == NULL) {
        $guidgenerated = bin2hex(openssl_random_pseudo_bytes(16));
        $query = "UPDATE `glpi_plugin_dlteams_controllerinfos` SET guid='$guidgenerated' WHERE entities_id='$id_value'";
        $DB->queryOrDie($query, $DB->error());
        Session::addMessageAfterRedirect(sprintf(__('Token Généré avec Succès')));
        if (!file_exists($glpiRoot . "/" . "pub" . "/")) {
            mkdir($glpiRoot . "/" . "pub" . "/");
            chmod($glpiRoot . "/" . "pub" . "/", 0310);
        }
        if (!file_exists($glpiRoot . "/" . "pub" . "/" . $guidgenerated . "/")) {
            mkdir($glpiRoot . "/" . "pub" . "/" . $guidgenerated . "/");
        }
    } else if ($guid_value != 0 && $guid_value != NULL || (!file_exists($glpiRoot . "/" . "pub" . "/" . $guid_value . "/"))) {
        mkdir($glpiRoot . "/" . "pub" . "/" . $guid_value . "/");
    }
} else if (isset($_POST['generate_publication_folder'])) {
    $guid_value = $guidgenerated = bin2hex(openssl_random_pseudo_bytes(16));

    $link = new Link();
    $server_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $path = $server_url. "/" . "pub" . "/";
    $link_id = $link->add([
        "is_recursive " => false,
        "name" => "Dossier de publication",
        "link" => $path.$guid_value,
        "entities_id" => $_SESSION["glpiactive_entity"]
    ]);

    mkdir($glpiRoot . "/" . "pub" . "/" . $guid_value . "/");
    chmod($path, 0310);

//    $deliverable_item = new PluginDlteamsDeliverable_Item();
//    $deliverable_item->add([
//        "deliverables_id" => $_POST["deliverables_id"],
//        "items_id" => $link_id,
//        "itemtype" => "Link"
//    ]);

    Session::addMessageAfterRedirect("Dossier de publication ajouté avec succès");

}

Html::back();
