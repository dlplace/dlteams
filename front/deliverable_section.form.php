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
Session::checkLoginUser();

global $DB;



if (isset($_POST["rename_tab"])) {
    $tab = new PluginDlteamsDeliverable_Section();
    $tab->getFromDB($_POST["tabnum"]);
    $tab->update([
        "tab_name" => $_POST["tabname"],
        "id" => $_POST["tabnum"]
    ]);

    Session::addMessageAfterRedirect("Onglet renommé avec succès");
}

if (isset($_POST["moveup_tab"])) {
    $allchapter = $DB->request(PluginDlteamsDeliverable_Section::getTable(), ['ORDER' => ['timeline_position ASC'], 'deliverables_id' => $_POST["deliverables_id"]]);
    $tab = new PluginDlteamsDeliverable_Section();
    $tab->getFromDB($_POST["tabnum"]);
    $previous_tab = null;
    $current_tabnum = $tab->fields["timeline_position"];
    foreach ($allchapter as $chapter) {
        if ($_POST["tabnum"] == $chapter["id"]) {
//            echanger le numero de tab avec le précédent item
            $current_tabnum = $tab->fields["timeline_position"];
            if ($previous_tab) {
//                on donne le numero de l'onglet précédent a notre onglet
                $tab->update([
                    "timeline_position" => $previous_tab["timeline_position"],
                    "id" => $_POST["tabnum"]
                ]);

//                et on donne le numero de notre onglet a l'onglet précédent
                $pt = new PluginDlteamsDeliverable_Section();
                $pt->update([
                    "timeline_position" => $current_tabnum,
                    "id" => $previous_tab["id"]
                ]);

                Session::addMessageAfterRedirect("Onglet remonté avec succès");
            }
        } else {
            $previous_tab = $chapter;
        }
    }
}

if (isset($_POST["movedown_tab"])) {
    $allchapter = $DB->request(PluginDlteamsDeliverable_Section::getTable(), ['ORDER' => ['timeline_position DESC'], 'deliverables_id' => $_POST["deliverables_id"]]);
    $tab = new PluginDlteamsDeliverable_Section();
    $tab->getFromDB($_POST["tabnum"]);
    $previous_tab = null;
    $current_tabnum = $tab->fields["timeline_position"];
    foreach ($allchapter as $chapter) {
        if ($_POST["tabnum"] == $chapter["id"]) {
//            echanger le numero de tab avec le précédent item
            $current_tabnum = $tab->fields["timeline_position"];
            if ($previous_tab) {
//                on donne le numero de l'onglet précédent a notre onglet
                $tab->update([
                    "timeline_position" => $previous_tab["timeline_position"],
                    "id" => $_POST["tabnum"]
                ]);

//                et on donne le numero de notre onglet a l'onglet précédent
                $pt = new PluginDlteamsDeliverable_Section();
                $pt->update([
                    "timeline_position" => $current_tabnum,
                    "id" => $previous_tab["id"]
                ]);

                Session::addMessageAfterRedirect("Onglet descendu avec succès");
            }
        } else {
            $previous_tab = $chapter;
        }
    }
}

if (isset($_POST["delete_tab"])) {
    $tab = new PluginDlteamsDeliverable_Section();
    $tab->delete(["id" => $_POST["tabnum"]]);
    Session::addMessageAfterRedirect("Onglet supprimé avec succès");
}

if (isset($_GET["add_tab"])) {
    $position = 0;
    $chapter = new PluginDlteamsDeliverable_Section();
    $condition = [
        'FROM' => 'glpi_plugin_dlteams_deliverables_sections',
        'ORDER' => 'id DESC',
        'LIMIT' => 1,
        'deliverables_id' => $_GET["deliverables_id"]
    ];

    $chapters = $DB->request($condition);

    foreach ($chapters as $key => $c) {
        $lastRecord = $c["timeline_position"];

        $position = $lastRecord + 1;
    }
    if ($position == 0)
        $position++;

    $result = $chapter->add([
        "tab_name" => "tab $position",
        "timeline_position" => $position,
        "deliverables_id" => $_GET["deliverables_id"]
    ]);


    Session::addMessageAfterRedirect("Onglet ajouté avec succès");
}

if (isset($_POST["save_section"])) {

    foreach ($_POST["name"] as $key => $name) {
        if ($key === 0) {
            $section = new PluginDlteamsDeliverable_Section();

            $section->update([
                "name" => $_POST["name"][$key],
                "comment" => $_POST["comment"][$key],
                "content" => $_POST["content"][$key],
                'id' => $_POST["deliverables_sections_id"]
            ]);

            Session::addMessageAfterRedirect("Section mis à jour avec succès");
        } else {
            $deliverablecontent = new PluginDlteamsDeliverable_Content();

            if (isset($_POST["id_field"]) && $_POST["id_field"][$key]) {
                $deliverablecontent->update([
                    "name" => $_POST["name"][$key],
                    "comment" => $_POST["comment"][$key],
                    "content" => $_POST["content"][$key],
                    "timeline_position" => $key,
                    "deliverable_sections_id" => $_POST["deliverables_sections_id"],
                    "id" => $_POST["id_field"][$key]
                ]);

                Session::addMessageAfterRedirect("Des paragraphes ont été mis à jour");
            }
            else{
                $deliverablecontent->add([
                    "name" => $_POST["name"][$key],
                    "comment" => $_POST["comment"][$key],
                    "content" => $_POST["content"][$key],
                    "timeline_position" => $key,
                    "deliverable_sections_id" => $_POST["deliverables_sections_id"],
                ]);

                Session::addMessageAfterRedirect("Des paragraphes ont été ajouté");
            }


        }
    }
}
Html::back();
