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
$delivrable = new PluginDlteamsDeliverable();
/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();
if (isset($_GET["deliverable_id"]))
    $data = $_GET;
else
    $data = $_POST;
if (isset($data["edit_pdf"])) {

    $pdfoutput = new PluginDlteamsCreatePDF();
    $print_options = PluginDlteamsCreatePDF::preparePrintOptionsFromForm($_GET);

    if (!isset($_GET["deliverable_id"]))
        updateEditDeliverableSettings();

    $deliverable = new PluginDlteamsDeliverable();
    $delivrable->getFromDB($data['deliverable_id']);
//    $pdfoutput->generateReport($_GET, $print_options);

    if ($delivrable) {
        $print_options["ispdf"] = true;
        $print_options["prevent_contextmenu"] = isset($data["prevent_contextmenu"]) ? $data["prevent_contextmenu"] : false;
        $print_options["print_first_page"] = isset($data["print_first_page"]) ? $data["print_first_page"] : false;
        $print_options["print_comments"] = isset($data["print_comments"]) ? $data["print_comments"] : false;
        $pdfoutput->deliverableGenerateReport($print_options, $delivrable);
//        $_GET["report_type"] = 7;
//        $pdfoutput->showPDF($_GET);
    }

} elseif (isset($_POST["edit_html"])) {
    $pdfoutput = new PluginDlteamsCreatePDF();
    $print_options = PluginDlteamsCreatePDF::preparePrintOptionsFromForm($_GET);

    updateEditDeliverableSettings();

    $deliverable = new PluginDlteamsDeliverable();
    $delivrable->getFromDB($_POST['deliverable_id']);
//    $print_options['print_first_page'] = $_POST['print_first_page'];
    $print_options["ispdf"] = false;
    $print_options["prevent_contextmenu"] = isset($_POST["prevent_contextmenu"]) ? $_POST["prevent_contextmenu"] : false;
    $print_options["print_first_page"] = isset($_POST["print_first_page"]) ? $_POST["print_first_page"] : false;
    $print_options["print_comments"] = isset($_POST["print_comments"]) ? $_POST["print_comments"] : false;
    $pdfoutput->deliverableGenerateHtml($print_options, $delivrable);
} elseif (isset($_POST["publish_dlteams"])) {

    $print_options["ispdf"] = false;
    $print_options["prevent_contextmenu"] = isset($_POST["prevent_contextmenu"]) ? $_POST["prevent_contextmenu"] : false;
    if (!isset($_POST["choosen_publication_folder"]) || !$_POST["choosen_publication_folder"] || $_POST["choosen_publication_folder"] == "0") {
        Session::addMessageAfterRedirect("Veuillez choisir un dossier de publication", false, ERROR);
        Html::back();
    }

    updateEditDeliverableSettings();

    $deliverable = new PluginDlteamsDeliverable();
    $delivrable->getFromDB($_POST['deliverable_id']);
    $print_options['print_first_page'] = $_POST['print_first_page'];
    $print_options["prevent_contextmenu"] = isset($_POST["prevent_contextmenu"]) ? $_POST["prevent_contextmenu"] : false;
    $print_options['print_comments'] = $_POST['print_comments'];
    $pdfoutput = new PluginDlteamsCreatePDF();
    $print_options = PluginDlteamsCreatePDF::preparePrintOptionsFromForm($_GET);
    $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
//    $valeurp= $pdfoutput->getGuidValue($_GET, $print_options);

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

        $deliverable_items = new PluginDlteamsDeliverable_Item();

        $exist = $deliverable_items->find([
            "itemtype" => "Link",
            "items_id" => $_POST["choosen_publication_folder"],
            "deliverables_id" => $_POST["deliverable_id"]
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
                "items_id" => $_POST["deliverable_id"]
            ]);


            $link_item_exist->add([
                "links_id" => $_POST["choosen_publication_folder"],
                "itemtype" => "PluginDlteamsDeliverable",
                "items_id" => $_POST["deliverable_id"]
            ]);

            $deliverable_items->deleteByCriteria([
                "itemtype" => "Link",
                "items_id" => $_POST["choosen_publication_folder"],
                "deliverables_id" => $_POST["deliverable_id"],
            ]);

            $deliverable_items->add([
                "itemtype" => "Link",
                "items_id" => $_POST["choosen_publication_folder"],
                "deliverables_id" => $_POST["deliverable_id"]
            ]);

        } else {

            $deliverable_items->add([
                "itemtype" => "Link",
                "items_id" => $_POST["choosen_publication_folder"],
                "deliverables_id" => $_POST["deliverable_id"]
            ]);

            $link_item = new PluginDlteamsLink_Item();
            $link_item->add([
                "links_id" => $_POST["choosen_publication_folder"],
                "itemtype" => "PluginDlteamsDeliverable",
                "items_id" => $_POST["deliverable_id"]
            ]);
        }


        $print_options["print_first_page"] = isset($_POST["print_first_page"]) ? $_POST["print_first_page"] : false;
        $print_options["print_comments"] = $_POST["print_comments"];
        $print_options["prevent_contextmenu"] = isset($_POST["prevent_contextmenu"]) ? $_POST["prevent_contextmenu"] : false;

        $pdfoutput->deliverablePublishDlRegister($print_options, $delivrable);
        Session::addMessageAfterRedirect(sprintf(__('Fichier crée avec Succès')));
        Html::back();
    } else {
        $pdfoutput->generateGuid($_GET, $print_options);
        $pdfoutput->deliverablePublishDlRegister($print_options, $_GET);
        Session::addMessageAfterRedirect(sprintf(__('Fichier crée avec Succès')));
        Html::back();
    }
} elseif (isset($_POST['add'])) {

    $delivrable->check(-1, CREATE, $_POST);
    $id = $delivrable->add($_POST);
    Html::redirect($delivrable->getFormURLWithID($id));

} else if (isset($_POST['update'])) {

    $delivrable->check($_POST['id'], UPDATE);
    $delivrable->update($_POST);
    Html::back();

} else if (isset($_POST['delete'])) {

    $delivrable->check($_POST['id'], DELETE);
    $delivrable->delete($_POST);
    $delivrable->redirectToList();
} else if (isset($_POST['save'])) {

//    if(!isset($_POST["choosen_publication_folder"]) || !$_POST["choosen_publication_folder"] || $_POST["choosen_publication_folder"] == '0' ){
//        Session::addMessageAfterRedirect("Veuillez choisir un dossier de publication", false, ERROR);
//        Html::back();
//    }

    if (updateEditDeliverableSettings())
        Session::addMessageAfterRedirect("Mise à jour avec succès");
    else
        Session::addMessageAfterRedirect("Une erreur s'est produite", false, ERROR);
    Html::back();
} else {

    $delivrable->checkGlobal(READ);

    if (Session::getCurrentInterface() == 'central') {
        Html::header(PluginDlteamsDeliverable::getTypeName(2), '', 'dlteams', 'plugindlteamsmenu', 'deliverable');
    } else {
        Html::helpHeader(PluginDlteamsDeliverable::getTypeName(0));
    }

    $delivrable->display(['id' => $_GET['id']]);

    if (Session::getCurrentInterface() == 'central') {
        Html::footer();
    } else {
        Html::helpFooter();
    }
}

function updateEditDeliverableSettings()
{
    $deliverable = new PluginDlteamsDeliverable();

    $deliverable->update([
        "is_firstpage" => isset($_POST["print_first_page"]) && $_POST["print_first_page"] === "on" ? true : false,
        "is_comment" => isset($_POST["print_comments"]) && $_POST["print_comments"] === "on" ? true : false,
        "print_logo" => isset($_POST["print_logo"]) && $_POST["print_logo"] === "on" ? true : false,
        "document_name" => $_POST["document_url"],
        "document_title" => $_POST["document_title"],
        "document_content" => $_POST["document_content"],
        "document_comment" => $_POST["document_comment"],
        "links_id" => isset($_POST["choosen_publication_folder"]) ? $_POST["choosen_publication_folder"] : null,
        "id" => $_POST['deliverable_id']
    ]);

    return true;
}
