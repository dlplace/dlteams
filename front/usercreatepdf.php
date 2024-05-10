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

Plugin::load('dlteams', true);
Session::checkCentralAccess();
$glpiRoot = str_replace('\\', '/', GLPI_ROOT);

$options = $_GET;


if(isset($options["save"])){
    updateEditUserSettings($options);
    Session::addMessageAfterRedirect("Opération éffectuée avec succès");
    Html::back();
}

if(isset($options["createpdf"])){
    $user_id = $options["user_id"];
    $options["ispdf"] = true;
    $pdfoutput = new PluginDlteamsUserCreatePDF();
    $pdfoutput->generatePDF($user_id, $options);
}

if(isset($options["createhtml"])){
    $user_id = $options["user_id"];
    $pdfoutput = new PluginDlteamsUserCreatePDF();
    $options["ispdf"] = false;

    $pdfoutput->generateHTML($user_id, $options);
}

if(isset($options["createhtmlppd"])){
    updateEditUserSettings($options);

    $print_options["ispdf"] = false;

    if (!isset($options["choosen_publication_folder"]) || !$options["choosen_publication_folder"] || $options["choosen_publication_folder"] == "0") {
        Session::addMessageAfterRedirect("Veuillez choisir un dossier de publication", false, ERROR);
        Html::back();
    }

    $record = new PluginDlteamsRecord();
    $record->getFromDB($options['user_id']);
    $print_options['print_first_page'] = isset($options['print_first_page']) && $options['print_first_page'] ? true : false;
    $print_options['print_comments'] = $options['print_comments'];
    $pdfoutput = new PluginDlteamsUserCreatePDF();
    $print_options = PluginDlteamsUserCreatePDF::preparePrintOptionsFromForm($options);
    $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
//    $valeurp= $pdfoutput->getGuidValue($_POST, $print_options);

    $link_folder = new Link();
    $link_folder->getFromDB($options['choosen_publication_folder']);
    $str_link = $link_folder->fields['link'];

    $server_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

    $str_link = str_replace($server_url . "/" . "pub" . "/", "", $str_link);
    $str_guid = str_replace("/", "", $str_link);

    if (isset($str_guid) && $str_guid != 0 && $str_guid != NULL) {
        if (!file_exists($glpiRoot . "/" . "pub" . "/" . $str_guid . "/")) {
            $parent_directory = $glpiRoot . "/pub";
            if (!is_dir($parent_directory)) {
                $cre = mkdir($parent_directory, 0755, true);
            }
            $new_directory = $parent_directory . "/" . $str_guid;
            if (!is_dir($new_directory)) {
                $cre = mkdir($new_directory, 0755, true);
            }

        }
        $print_options['guid_value'] = $str_guid;


        $print_options["print_first_page"] = isset($options["print_first_page"]) ? $options["print_first_page"] : false;
        $print_options["print_comments"] = $_POST["print_comments"];

        updateEditUserSettings($options);
        $options["ispdf"] = false;
        $pdfoutput->publishDlRegister($options, $print_options);
        Session::addMessageAfterRedirect(sprintf(__('Fichier crée avec Succès')));
        Html::back();
    } else {
        updateEditUserSettings($options);
        $pdfoutput->generateGuid($options, $print_options);
        $pdfoutput->publishDlRegister($options, $print_options);
        Session::addMessageAfterRedirect(sprintf(__('Fichier crée avec Succès')));
        Html::back();
    }
}
/*highlight_string("<?php\n\$data =\n" . var_export($_GET, true) . ";\n?>");*/


function updateEditUserSettings($options)
{
//    highlight_string("<?php\n\$data =\n" . var_export([
//            "print_logo" => isset($options["print_logo"]) && $options["print_logo"] ? true : false,
//            "links_id" => isset($options["choosen_publication_folder"])?$options["choosen_publication_folder"]:null,
//            "print_comments" => isset($_POST["print_comments"]) && $options["print_comments"] ? true : false,
//            "comment" => isset($options["edition_comment"]) && $options["edition_comment"] ? true : false,
/*        ], true) . ";\n?>");*/
//    die();
    $user = new User();
    $user->getFromDB($options["user_id"]);

    global $DB;
    $re = $DB->update(
        $user->getTable(),
        [
            "print_logo" => isset($options["print_logo"]) && $options["print_logo"] ? true : false,
            "links_id" => isset($options["choosen_publication_folder"])?$options["choosen_publication_folder"]:null,
            "print_comments" => isset($_POST["print_comments"]) && $options["print_comments"] ? true : false,
            "edition_comment" => isset($options["edition_comment"]) && $options["edition_comment"] ?  $options["edition_comment"] : "",
        ],
        [
            "id" => $options['user_id']
        ]
    );

    return true;
}
