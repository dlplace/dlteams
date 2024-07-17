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

include ('../../../inc/includes.php');

//$family = new PluginGenericobjectTypeFamily();
$_GET['id']=4;
//if (!isset($_GET['id']) || !$family->getFromDB($_GET['id'])) {
if (!isset($_GET['id']) ) {
  // Html::header(__("Objects management", "genericobject"), $_SERVER['PHP_SELF'], "dlteams", "plugindlteamsmenu");
  // echo "<table class='tab_cadre_fixe'>";
  //  echo "<tr class='tab_bg_2'><th>".__("Empty family", "genericobject")."</th></tr>";
  // echo "</table>";
} else {
   //$family->getFromDB($_GET['id']);
   Html::header(__("RGPD", "genericobject"), $_SERVER['PHP_SELF'], "dlteams", "plugindlteamsmenu");

   echo "<table class='tab_cadre_fixe'>";
    //echo "<tr class='tab_bg_2'><th>".Dropdown::getDropdownName("glpi_plugin_genericobject_typefamilies", $_GET['id'])."</th></tr>";
	//echo "<tr><td align='center' style='text-align:center'>" . "<strong> Éléments pour la protection de la vie privée </strong>";
    //echo "</td></tr>";
	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsRecord::getSearchURL()."'>";
        echo __("Registre des traitements", 'dlteams');
    echo "</a></td></tr>";
    echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsConcernedPerson::getSearchURL()."'>";
        echo __("Personnes concernés", 'dlteams');
    echo "</a></td></tr>";
    echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsProcessedData::getSearchURL()."'>";
        echo "Données à Caractère Personnel (DCP) traitées";
    echo "</a></td></tr>";
    echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsLegalBasi::getSearchURL()."'>";
        //echo PluginDlteamsLegalBasisAct::getTypeName();
        echo "Bases légales, normes, référentiels";
    echo "</a></td></tr>";
    echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsStoragePeriod::getSearchURL()."'>";
        echo "Durées de Conservation";
    echo "</a></td></tr>";
	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsThirdPartyCategory::getSearchURL()."'>";
        echo "Tiers (catégories)";
    echo "</a></td></tr>";
	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsRightMeasure::getSearchURL()."'>";
        echo "Droits (mesures, demandes et incidents)";
    echo "</a></td></tr>";
		echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
		echo "-----------------------" ;
    echo "</a></td></tr>";

	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsPolicieForm::getSearchURL()."'>";
        echo _n("Processing document", "Processing documents", 2, 'dlteams');
	echo "</a></td></tr>";
	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsDataCatalog::getSearchURL()."'>";
        echo _n("Data catalog", "Data catalogs", 2, 'dlteams');
	echo "</a></td></tr>";
	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a target='_blank' href='". PluginDlteamsAppliance::getSearchURL()."'>";
        echo "Applications et logiciels métiers";
	echo "</a></td></tr>";
	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
		echo "<a target='_blank' href='". PluginDlteamsAccountKey::getSearchURL()."'>";
        echo "Comptes et clés, accès, attributions";
	echo "</a></td></tr>";
    echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
		echo "<a target='_blank' href='". User::getSearchURL()."'>";
        echo "Utilisateurs | ";
		echo "</a>";
		echo "<a target='_blank' href='". Group::getSearchURL()."'>";
		echo "Groupes | ";
		echo "</a>";
		echo "<a target='_blank' href='". PluginDlteamsUserProfile::getSearchURL()."'>";
		echo "Profils et rôles";
		echo "</a>";
    echo "</td></tr>";
	/*echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsPhysicalStorage::getSearchURL()."'>";
        echo "Stockages physiques  | ";
	echo "</a></td></tr>";*/
	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". AllAssets::getSearchURL()."'>";
        echo "Stockages | ";
	echo "</a>";
	    echo "<a href='". Location::getSearchURL()."'>";
        echo "Lieux | ";
	echo "</a>";
        echo "<a href='". PluginDlteamsNetworkPort::getSearchURL()."'>";
        echo "Ports réseau";
	echo "</a></td></tr>";

	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
		echo "-----------------------" ;
	echo "</a></td></tr>";
	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsRiskAssessment::getSearchURL()."'>";
        echo "Evaluation des Risques";
	echo "</a></td></tr>";

    echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsAudit::getSearchURL()."'>";
        echo "Audits | ";
    echo "</a>";
		echo "<a target='_blank' href='" .
		"https://drive.google.com/open?id=1S6WAlHpobLGXpbar-Ksbhl0P0AZbcAi2&authuser=gestion.dlplace%40gmail.com&usp=drive_fs" ."'>";
        echo "Analyses d'impact (PIA)";
	echo "</a></td></tr>";

	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsProtectiveMeasure::getSearchURL()."'>";
        echo "Protection et Surveillance (mesures, suivi)";
	echo "</a></td></tr>";
	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
		echo "-----------------------" ;
    echo "</a></td></tr>";
	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
    $project = new Project();
    $project->getFromDBByCrit([
        "name" => "Conformité RGPD",
        "entities_id" => Session::getActiveEntity(),
        "is_deleted" => 0
    ]);
    // $projects_id = $project->fields["id"];
		$projects_id = "Conformité RGPD";
		echo "<a target='_blank' href='". PluginDlteamsStep::getSearchURL()."'>";
        echo "Plans d'actions | ";

    echo "</a>";
        echo "<a href='". PluginDlteamsTrainingSession::getSearchURL()."'>";
        echo "Suivi des formations";
	echo "</a></td></tr>";

    /*echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsTrainingCertification::getSearchURL()."'>";
        echo "Procédures, bonnes pratiques, formations";
	echo "</a></td></tr>";*/
    echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsProcedure::getSearchURL()."'>";
        echo "Procédures, bonnes pratiques, formations";
	echo "</a></td></tr>";

    echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsDocumentRGPD::getSearchURL()."'>";
        echo "Documentation (livrables, rapports)";
    echo "</a></td></tr>";

	/*echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsDataBreach::getSearchURL()."'>";
        echo "Suivi des demandes de droits et incidents";
    echo "</a></td></tr>";*/
	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
		echo "-----------------------" ;
    echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
		//echo "<a target='_blank' href='" .
		// "https://drive.google.com/open?id=1S6WAlHpobLGXpbar-Ksbhl0P0AZbcAi2&authuser=gestion.dlplace%40gmail.com&usp=drive_fs" ."'>";
        // echo "Ressources | ";
		// echo "</a>";
		echo "<a target='_blank' href='". Knowbaseitem::getSearchURL() ."'>";
		echo "Base de connaissances | ";
		echo "</a>";
		echo "<a target='_blank' href='" . Reminder::getSearchURL() ."'>";
		echo "Actualités et rappels";
		echo "</a>";
    echo "</td></tr>";
    // echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        // echo "<a href='". PluginDlteamsBestPractice::getSearchURL()."'>";
        // echo PluginDlteamsBestPractice::getTypeName();
        // echo "Procédures, Bonnes Pratiques RGPD";
    // echo "</a></td></tr>";
	echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";

    echo "</a></td></tr>";
    echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
		echo "<a target='_blank' href='" .
		"config.form.php" ."'>";
        echo "Configuration | ";
		echo "</a>";
		echo "<a target='_blank' href='" . "https://dlregister.app/marketplace/formcreator/front/formdisplay.php?id=16" ."'>";
		echo "Demande de support";
		echo "</a>";
    echo "</td></tr>";

/*    echo "<tr class='tab_bg_1'><td align='center' style='text-align:center'>";
        echo "<a href='". PluginDlteamsProcess::getSearchURL()."'>";
        // echo PluginDlteamsProcess::getTypeName();
        echo "Processus";
    echo "</a></td></tr>";*/
    echo "</table>";
}

Html::footer();
