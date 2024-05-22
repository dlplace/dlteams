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
$procedure = new PluginDlteamsProcedure();
/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();
if (isset($_GET["procedure_id"]))
    $data = $_GET;
else
    $data = $_POST;

if(isset($data["edit_pdf"])){

    $pdfoutput = new PluginDlteamsCreatePDF();
    $print_options = PluginDlteamsCreatePDF::preparePrintOptionsFromForm($_GET);

    if (!isset($_GET["procedure_id"]))
    updateEditProcedureSettings();

    $procedure = new PluginDlteamsProcedure();
    $procedure->getFromDB($data['procedure_id']);
//    $pdfoutput->generateReport($_GET, $print_options);

    if($procedure){
        $print_options["ispdf"] = true;
        $print_options["print_first_page"] = isset($data["print_first_page"])?$data["print_first_page"]:false;
        $print_options["print_comments"] = isset($data["print_comments"])?$data["print_comments"]:false;
        $pdfoutput->procedureGenerateReport($print_options, $procedure);
//        $_GET["report_type"] = 7;
//        $pdfoutput->showPDF($_GET);
    }

}
elseif (isset($_POST["edit_html"])){
    $pdfoutput = new PluginDlteamsCreatePDF();
    $print_options = PluginDlteamsCreatePDF::preparePrintOptionsFromForm($_GET);

    updateEditProcedureSettings();

    $procedure = new PluginDlteamsProcedure();
    $procedure->getFromDB($_POST['procedure_id']);
//    $print_options['print_first_page'] = $_POST['print_first_page'];
    $print_options["ispdf"] = false;
    $print_options["print_first_page"] = isset($_POST["print_first_page"])?$_POST["print_first_page"]:false;
    $print_options["print_comments"] = isset($_POST["print_comments"])?$_POST["print_comments"]:false;
    $pdfoutput->procedureGenerateHtml($print_options, $procedure);
}
elseif (isset($_POST["publish_dlteams"])){

    $print_options["ispdf"] = false;
    if(!isset($_POST["choosen_publication_folder"]) || !$_POST["choosen_publication_folder"] || $_POST["choosen_publication_folder"] == "0"){
        Session::addMessageAfterRedirect("Veuillez choisir un dossier de publication", false, ERROR);
        Html::back();
    }

    updateEditProcedureSettings();

    $procedure = new PluginDlteamsProcedure();
    $procedure->getFromDB($_POST['procedure_id']);
    $print_options['print_first_page'] = $_POST['print_first_page'];
    $print_options['print_comments'] = $_POST['print_comments'];
    $pdfoutput = new PluginDlteamsCreatePDF();
    $print_options = PluginDlteamsCreatePDF::preparePrintOptionsFromForm($_GET);
    $glpiRoot=str_replace('\\', '/', GLPI_ROOT);
//    $valeurp= $pdfoutput->getGuidValue($_GET, $print_options);

    $link_folder = new Link();
    $link_folder->getFromDB($_POST['choosen_publication_folder']);
    $str_link = $link_folder->fields['link'];

    $server_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

    $str_link = str_replace($server_url. "/" . "pub" . "/", "", $str_link);
    $str_guid = str_replace("/", "", $str_link);

    if(isset($str_guid) && $str_guid !=0 && $str_guid !=NULL){
        if (!file_exists($glpiRoot. "/" . "pub"."/" .$str_guid."/")) {
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

        $procedure_items = new PluginDlteamsProcedure_Item();

        $exist = $procedure_items->find([
            "itemtype" => "Link",
            "items_id" => $_POST["choosen_publication_folder"],
            "procedures_id" => $_POST["procedure_id"]
        ]);

        if(count($exist)>0){
            $id = 0;
            foreach ($exist as $di){
                $id = $di["id"];
            }

            $linkitem_id = 0;

            $link_item_exist = new PluginDlteamsLink_Item();
            $link_item_exist->deleteByCriteria([
                "links_id" => $_POST["choosen_publication_folder"],
                "itemtype" => "PluginDlteamsProcedure",
                "items_id" => $_POST["procedure_id"]
            ]);


            $link_item_exist->add([
                "links_id" => $_POST["choosen_publication_folder"],
                "itemtype" => "PluginDlteamsProcedure",
                "items_id" => $_POST["procedure_id"]
            ]);

            $procedure_items->deleteByCriteria([
                "itemtype" => "Link",
                "items_id" => $_POST["choosen_publication_folder"],
                "procedures_id" => $_POST["procedure_id"],
            ]);

            $procedure_items->add([
                "itemtype" => "Link",
                "items_id" => $_POST["choosen_publication_folder"],
                "procedures_id" => $_POST["procedure_id"]
            ]);

        }
        else{

            $procedure_items->add([
                "itemtype" => "Link",
                "items_id" => $_POST["choosen_publication_folder"],
                "procedures_id" => $_POST["procedure_id"]
            ]);

            $link_item = new PluginDlteamsLink_Item();
            $link_item->add([
                "links_id" => $_POST["choosen_publication_folder"],
                "itemtype" => "PluginDlteamsProcedure",
                "items_id" => $_POST["procedure_id"]
            ]);
        }


        $print_options["print_first_page"] = isset($_POST["print_first_page"])?$_POST["print_first_page"]:false;
        $print_options["print_comments"] = $_POST["print_comments"];

        $pdfoutput->procedurePublishDlRegister($print_options, $procedure);
        Session::addMessageAfterRedirect(sprintf( __('Fichier crée avec Succès')));
        Html::back();
    }
    else {
        $pdfoutput->generateGuid($_GET, $print_options);
        $pdfoutput->procedurePublishDlRegister($print_options, $_GET);
        Session::addMessageAfterRedirect(sprintf(__('Fichier crée avec Succès')));
        Html::back();
    }
}
elseif (isset($_POST['add'])) {

   $procedure->check(-1, CREATE, $_POST);
   $id = $procedure->add($_POST);
   Html::redirect($procedure->getFormURLWithID($id));

}
else if (isset($_POST['update'])) {

   $procedure->check($_POST['id'], UPDATE);
   $procedure->update($_POST);
   Html::back();

}
else if (isset($_POST['delete'])) {

   $procedure->check($_POST['id'], DELETE);
   $procedure->delete($_POST);
   $procedure->redirectToList();
}
else if(isset($_POST['save'])){

//    if(!isset($_POST["choosen_publication_folder"]) || !$_POST["choosen_publication_folder"] || $_POST["choosen_publication_folder"] == '0' ){
//        Session::addMessageAfterRedirect("Veuillez choisir un dossier de publication", false, ERROR);
//        Html::back();
//    }

    if(updateEditProcedureSettings())
    Session::addMessageAfterRedirect("Mise à jour avec succès");
    else
        Session::addMessageAfterRedirect("Une erreur s'est produite", false, ERROR);
    Html::back();
}
else {

    $procedure->checkGlobal(READ);

    if (Session::getCurrentInterface() == 'central') {
		Html::header(PluginDlteamsProcedure::getTypeName(2), '', 'dlteams', 'plugindlteamsmenu', 'procedure');
    } else {
        Html::helpHeader(PluginDlteamsProcedure::getTypeName(0));
    }

    $procedure->display(['id' => $_GET['id']]);

    if (Session::getCurrentInterface() == 'central') {
        Html::footer();
    } else {
        Html::helpFooter();
    }
}

function updateEditProcedureSettings(){
    $procedure = new PluginDlteamsProcedure();

    $procedure->update([
        "is_firstpage" => isset($_POST["print_first_page"]) && $_POST["print_first_page"] === "on"?true:false,
        "is_comment" => isset($_POST["print_comments"]) && $_POST["print_comments"] === "on"?true:false,
        "print_logo" => isset($_POST["print_logo"]) && $_POST["print_logo"] === "on"?true:false,
        "document_name" => $_POST["document_url"],
        "document_title" => $_POST["document_title"],
        "document_content" => $_POST["document_content"],
        "document_comment" => $_POST["document_comment"],
        "links_id" => $_POST["choosen_publication_folder"],
        "id" => $_POST['procedure_id']
    ]);

    return true;
}
