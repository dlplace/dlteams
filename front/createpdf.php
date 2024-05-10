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
if (isset($_POST['createpdf']) || isset($_POST['createhtml']) || isset($_POST['createhtmlppd'])) {

    $print_options = PluginDlteamsCreatePDF::preparePrintOptionsFromForm($_POST);
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'prepare') {
            Html::header(PluginDlteamsRecord::getTypeName(0), '', "grc", "plugindlteamsmenu");
            PluginDlteamsCreatePDF::showPrepareForm(3);
        }
        else if ($_POST['action'] == 'print' && isset($_POST['report_type'])) {
            $pdfoutput = new PluginDlteamsCreatePDF();
            if (isset($_POST['createhtml'])) {
                $pdfoutput->generateHtml($_POST, $print_options);
//            Html::back();
            }
//            publier dlteams
            elseif (isset($_POST['createhtmlppd'])) {

                $print_options["ispdf"] = false;

                if (!isset($_POST["choosen_publication_folder"]) || !$_POST["choosen_publication_folder"] || $_POST["choosen_publication_folder"] == "0") {
                    Session::addMessageAfterRedirect("Veuillez choisir un dossier de publication", false, ERROR);
                    Html::back();
                }

                updateEditRecordSettings();

                $record = new PluginDlteamsRecord();
                $record->getFromDB($_POST['record_id']);
                $print_options['print_first_page'] = isset($_POST['print_first_page']) && $_POST['print_first_page'] ? true : false;
                $print_options['print_comments'] = $_POST['print_comments'];
                $pdfoutput = new PluginDlteamsCreatePDF();
                $print_options = PluginDlteamsCreatePDF::preparePrintOptionsFromForm($_POST);
                $glpiRoot = str_replace('\\', '/', GLPI_ROOT);


                $link_folder = new Link();
                $link_folder->getFromDB($_POST['choosen_publication_folder']);
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

                    $record_item = new PluginDlteamsRecord_Item();

                    $exist = $record_item->find([
                        "itemtype" => "Link",
                        "items_id" => $_POST["choosen_publication_folder"],
                        "records_id" => $_POST["record_id"]
                    ]);

                    if (count($exist) > 0) {
                        $id = 0;
                        foreach ($exist as $di) {
                            $id = $di["id"];
                        }

                        $linkitem_id = 0;

                        $link_item_exist = new PluginDlteamsLink_Item();
                        $link_item_exist->deleteByCriteria([
                            "links_id" => $_POST["choosen_publication_folder"],
                            "itemtype" => "PluginDlteamsDeliverable",
                            "items_id" => $_POST["record_id"]
                        ]);


                        $link_item_exist->add([
                            "links_id" => $_POST["choosen_publication_folder"],
                            "itemtype" => "PluginDlteamsDeliverable",
                            "items_id" => $_POST["record_id"]
                        ]);

                        $record_item->deleteByCriteria([
                            "itemtype" => "Link",
                            "items_id" => $_POST["choosen_publication_folder"],
                            "records_id" => $_POST["record_id"],
                        ]);

                        $record_item->add([
                            "itemtype" => "Link",
                            "items_id" => $_POST["choosen_publication_folder"],
                            "records_id" => $_POST["record_id"]
                        ]);

                    }
                    else {

                        $record_item->add([
                            "itemtype" => "Link",
                            "items_id" => $_POST["choosen_publication_folder"],
                            "records_id" => $_POST["record_id"]
                        ]);

                        $link_item = new PluginDlteamsLink_Item();
                        $link_item->add([
                            "links_id" => $_POST["choosen_publication_folder"],
                            "itemtype" => PluginDlteamsRecord::class,
                            "items_id" => $_POST["record_id"]
                        ]);
                    }


                    $print_options["print_first_page"] = isset($_POST["print_first_page"]) ? $_POST["print_first_page"] : false;
                    $print_options["print_comments"] = $_POST["print_comments"];


                    updateEditRecordSettings();
                    $pdfoutput->publishDlRegister($_POST, $print_options);
                    Session::addMessageAfterRedirect(sprintf(__('Fichier crée avec Succès')));
                    Html::back();
                } else {
                    updateEditRecordSettings();
                    $pdfoutput->generateGuid($_POST, $print_options);
                    $pdfoutput->publishDlRegister($_POST, $print_options);
                    Session::addMessageAfterRedirect(sprintf(__('Fichier crée avec Succès')));
                    Html::back();
                }


            }
//            editer pdf
            else {
                updateEditRecordSettings();
                $pdfoutput->generateReport($_POST, $print_options);

//            $pdfoutput->showPDF($_POST);
            }
        }
        else Html::back();
    }
}
elseif(isset($_POST["save"])){
    updateEditRecordSettings();
    Session::addMessageAfterRedirect("Paramètres d'impression mis à jour");
    Html::back();
}

//Html::back();
function updateEditRecordSettings()
{
    $record = new PluginDlteamsRecord();
    $record->getFromDB($_POST["record_id"]);

    global $DB;
    $re = $DB->update(
        $record->getTable(),
        [
            "print_logo" => isset($_POST["print_logo"]) && $_POST["print_logo"] ? true : false,
            "links_id" => isset($_POST["choosen_publication_folder"])?$_POST["choosen_publication_folder"]:null,
            "print_comments" => isset($_POST["print_comments"]) && $_POST["print_comments"] ? true : false,
        ],
        [
            "id" => $_POST['record_id']
        ]
    );

    return true;
}
