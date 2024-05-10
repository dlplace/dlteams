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

/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();

include("../../../inc/includes.php");
Session::checkLoginUser();
$_SESSION['debug']['post'] = $_POST;
if (isset($_POST['add'])) {
//if (isset($_POST['add']) && isset($_POST['items_id'])) {

    global $DB;
    //add to item
    $dbu  = new DbUtils();

    $tab = [
        'items_id'  => $_POST['items_id'], // id de l'élément courant
        'itemtype'  => $_POST['itemtype'], // objet courant
        'items_id1'  => $_POST['items_id1'],  // id de l'élément relié
        'itemtype1'  => $_POST['itemtype1']   // objet relié
    ];

    if (isset($_POST['comment'])) {
        $tab['comment'] = $_POST['comment'];
    };

$class = $_POST['itemtype'] ;
$class1 = $_POST['itemtype1'] ;

/*
comme exemple, on a soit la classe "Document" qui donne la table 'glpi_documents_items'
soit la classe PluginDlteamsRecord qui donne la classe 'glpi_plugin_dlteams_records_items'
cela donne :
si les 16 premiers caractère sont "PluginDlteams" alors table$ = "glpi_plugin_dlteams_" + minucule (droite($_POST['itemtype'], longueur$_POST['itemtype']-16)) + "s_items";
si les 16 premiers caractère <> "PluginDlteams" alors table$ = "glpi_" + minucule ($_POST['itemtype'] + "s_items";

si les 16 premiers caractère sont "PluginDlteams" alors $field  = minucule (droite($_POST['itemtype'], longueur$_POST['itemtype']-16)) + "s_id";
si les 16 premiers caractère <> "PluginDlteams" alors $field = minucule ($_POST['itemtype'] + "s_id";
$field = minucule _id"
*/

if(str_contains($class, "PluginDlteams"))
		  $table = "glpi_plugin_dlteams_".strtolower(str_replace("PluginDlteams","", $_POST['itemtype'])."s_items");
	  else
		  $table = "glpi_".strtolower($_POST['itemtype'])."s_items";
if(str_contains($class, "PluginDlteams"))
		  $field = strtolower(str_replace("PluginDlteams","", $_POST['itemtype'])."s_id");
	  else
		  $field = strtolower($_POST['itemtype'])."s_id";

if(str_contains($class1, "PluginDlteams"))
		  $table1 = "glpi_plugin_dlteams_".strtolower(str_replace("PluginDlteams","", $_POST['itemtype1'])."s_items");
	  else
		  $table1 = "glpi_".strtolower($_POST['itemtype1'])."s_items";
if(str_contains($class, "PluginDlteams"))
		  $field1 = strtolower(str_replace("PluginDlteams","", $_POST['itemtype1'])."s_id");
	  else
		  $field1 = strtolower($_POST['itemtype1'])."s_id";


//var_dump ($class, $class1);
//var_dump ("INSER 1 = ", $table, $field, $_POST['items_id'], $_POST['itemtype1'], $_POST['items_id1'], "INSER 2 = ", $table1, $field1, $_POST['items_id1'], $_POST['itemtype'], $_POST['items_id']);

		$DB->insert(
				$table, [
					$field  		=> $_POST['items_id'],
					'items_id'  	=> $_POST['items_id1'],
					'itemtype'      => $_POST['itemtype1'],
					'comment' 		=> $_POST['comment'],
				]
			);
		$DB->insert(
				$table1, [
					$field1			=> $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
				]
			);


/*
    if ($_POST['itemtype'] == 'Document') {
			$DB->insert(
				'glpi_documents_items', [
					'documents_id'  => $_POST['items_id'],
					'items_id'  	=> $_POST['items_id1'],
					'itemtype'      => $_POST['itemtype1'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype'] == 'PluginDlteamsDataCatalog') {
			$DB->insert(
				'glpi_plugin_dlteams_datacatalogs_items', [
					'datacatalogs_id'  => $_POST['items_id'],
					'items_id'  	=> $_POST['items_id1'],
					'itemtype'      => $_POST['itemtype1'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id'],
				]
			);
	};
	if ($_POST['itemtype'] == 'PluginDlteamsDatabaseType') {
			$DB->insert(
				'glpi_plugin_dlteams_databasetypes_items', [
					'databasetypes_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment'  		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype'] == 'PluginDlteamsDataCategory') {
			$DB->insert(
				'glpi_plugin_dlteams_datacategories_items', [
					'datacategories_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment'  		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};

//// itemtype1

	if ($_POST['itemtype1'] == 'PluginDlteamsRecord') {
			$DB->insert(
				'glpi_plugin_dlteams_records_items', [
					'records_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => isset($_POST['entities_id'])?$_POST['entities_id']:0
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsConcernedPerson') {
			$DB->insert(
				'glpi_plugin_dlteams_concernedpersons_items', [
					'concernedpersons_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsProcessedData') {
		$DB->insert(
				'glpi_plugin_dlteams_processeddatas_items', [
					'processeddatas_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsLegalbasi') {
			$DB->insert(
				'glpi_plugin_dlteams_legalbasis_items', [
					'legalbasis_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsStoragePeriod') {
			$DB->insert(
				'glpi_plugin_dlteams_storageperiods_items', [
					'storageperiods_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsThirdPartyCategory') {
			$DB->insert(
				'glpi_plugin_dlteams_thirdpartycategories_items', [
					'thirdpartycategories_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsRightMeasure') {
			$DB->insert(
				'glpi_plugin_dlteams_rightmeasures_items', [
					'rightmeasures_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsPolicieForm') {
			$DB->insert(
				'glpi_plugin_dlteams_policieforms_items', [
					'policieforms_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsDatabase') {
			$DB->insert(
				'glpi_plugin_dlteams_databases_items', [
					'databases_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'entities_id'   => $_POST['entities_id'],
					'comment'   	=> $_POST['comment']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsDataCatalog') {
			$DB->insert(
				'glpi_plugin_dlteams_datacatalogs_items', [
					'datacatalogs_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'entities_id'   => $_POST['entities_id'],
					'comment'   => $_POST['comment']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsAccountKey') {
		$DB->insert(
				'glpi_plugin_dlteams_accountkeys_items', [
					'accountkeys_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsAppliance') {
		$DB->insert(
				'glpi_plugin_dlteams_appliance_items', [
					'appliances_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsRiskAssessment') {
		$DB->insert(
				'glpi_plugin_dlteams_riskassessments_items', [
					'riskassessments_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsAudit') {
		$DB->insert(
				'glpi_plugin_dlteams_audits_items', [
					'audits_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsProtectiveMeasure') {
		$DB->insert(
				'glpi_plugin_dlteams_protectivemeasures_items', [
					'protectivemeasures_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsTrainingCertification') {
		$DB->insert(
				'glpi_plugin_dlteams_trainingcertifications_items', [
					'trainingcertifications_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
	if ($_POST['itemtype1'] == 'PluginDlteamsDeliverable') {
		$DB->insert(
				'glpi_plugin_dlteams_deliverables_items', [
					'deliverables_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id'],
					'itemtype'      => $_POST['itemtype'],
					'comment' 		=> $_POST['comment'],
					'entities_id'   => $_POST['entities_id']
				]
			);
	};
*/
/*
//$ma->addMessage(__("delete item" . $id . $item->getType()));

	if ($_POST['itemtype1'] == 'PluginDlteamsRecord' || $_POST['itemtype'] == 'PluginDlteamsLegalBasi') {

			$DB->insert(
				'glpi_plugin_dlteams_allitems', [
				'items_id1'  => $_POST['items_id1'],
				'itemtype1'  => $_POST['itemtype1'],
				'items_id2'  => $_POST['items_id'],
				'itemtype2'  => 'PluginDlteamsLegalbasi',
				'comment'  => $_POST['comment'],
				'plugin_dlteams_records_id' => $_POST['items_id1'],
				'plugin_dlteams_legalbasis_id' => $_POST['items_id'],
				]
			);


	}
	else if ($_POST['itemtype'] == 'PluginDlteamsProtectiveMeasure') {
			$DB->insert(
				'glpi_plugin_dlteams_allitems', [
				'items_id1'  => $_POST['items_id1'],
				'itemtype1'  => $_POST['itemtype1'],
				'items_id2'  => $_POST['items_id_m'],
				'itemtype2'  => $_POST['itemtype'],
				'comment'  => $_POST['comment'],
				'plugin_dlteams_records_id' => $_POST['items_id1'],
				'plugin_dlteams_protectivemeasures_id' => $_POST['items_id_m'],
				]
			);
	}
	else if ($_POST['itemtype'] == 'PluginDlteamsDatabase') {
			$DB->insert(
				'glpi_plugin_dlteams_allitems', [
				'items_id1'  => $_POST['items_id1'],
				'itemtype1'  => $_POST['itemtype1'],
				'items_id2'  => $_POST['items_id'],
				'itemtype2'  => $_POST['itemtype'],
				'comment'  => $_POST['comment'],
				'plugin_dlteams_records_id' => $_POST['items_id1'],
				'plugin_dlteams_databases_id' => $_POST['items_id'],
				]
			);
	}
	else if ($_POST['itemtype'] == 'PluginDlteamsThirdPartyCategory') {
			$DB->insert(
				'glpi_plugin_dlteams_allitems', [
				'items_id1'  => $_POST['items_id1'],
				'itemtype1'  => $_POST['itemtype1'],
				'items_id2'  => $_POST['items_id'],
				'itemtype2'  => $_POST['itemtype'],
				'comment'  => $_POST['comment'],
				'plugin_dlteams_records_id' => $_POST['items_id1'],
				'plugin_dlteams_thirdpartycategories_id' => $_POST['items_id'],
				]
			);

			//record external
			$DB->insert(
				'glpi_plugin_dlteams_allitems', [
				'items_id1'  => $_POST['items_id1'],
				'itemtype1'  => $_POST['itemtype1'],
				'items_id2'  => $_POST['items_id'],
				'itemtype2'  => $_POST['itemtype'],
				'comment'  => $_POST['comment'],
				'plugin_dlteams_records_id' => $_POST['items_id1'],
				'plugin_dlteams_thirdpartycategories_id' => $_POST['items_id'],
				]
			);
			//record external
	}

	else if ($_POST['itemtype'] == 'PluginDlteamsStoragePeriod') {
			$DB->insert(
				'glpi_plugin_dlteams_allitems', [
				'items_id1'  => $_POST['items_id1'],
				'itemtype1'  => $_POST['itemtype1'],
				'items_id2'  => $_POST['items_id'],
				'itemtype2'  => $_POST['itemtype'],
				'comment'  => $_POST['comment'],
				'plugin_dlteams_records_id' => $_POST['items_id1'],
				'plugin_dlteams_storageperiods_id' => $_POST['items_id'],
				]
			);
	}

	else if ($_POST['itemtype1'] == 'PluginDlteamsDataCatalog') {
			$DB->insert(
				'glpi_plugin_dlteams_allitems', [
				'items_id1'  => $_POST['items_id1'],
				'itemtype1'  => $_POST['itemtype1'],
				'items_id2'  => $_POST['items_id'],
				'itemtype2'  => $_POST['itemtype'],
				'comment'  => $_POST['comment'],
				'plugin_dlteams_datacatalogs_id' => $_POST['items_id1'],
				//'glpi_plugin_dlteams_storageperiods_id' => $_POST['items_id'],
				]
			);
	}

	else if ($_POST['itemtype1'] == 'PluginDlteamsAccountkey') {
			$DB->insert(
				'glpi_plugin_dlteams_allitems', [
				'items_id1'  => $_POST['items_id1'],
				'itemtype1'  => $_POST['itemtype1'],
				'items_id2'  => $_POST['items_id'],
				'itemtype2'  => $_POST['itemtype'],
				'comment'  => $_POST['comment'],
				'glpi_plugin_dlteams_accontkeys_id' => $_POST['items_id1'],
				]
			);
	}
	else{
		$_SESSION['debug']['db'] = $DB->insert(PluginDlteamsAllItem::getTable(), $tab);
	}
*/
/*
    if ($_POST['itemtype1'] == 'Document' || $_POST['itemtype'] == 'Document') {
        $one = $_POST['itemtype1'] == 'Document';
        $tabDoc = [
            'documents_id' => $one ? $_POST['items_id1'] : $_POST['items_id'],
            'items_id' => $one ? $_POST['items_id'] : $_POST['items_id1'],
            'itemtype' => $one ? $_POST['itemtype'] : $_POST['itemtype1'],
        ];
        $document_item   = new Document_Item();
        $document_item->check(-1, CREATE, $tabDoc);
        $document_item->add($tabDoc);
    }
*/
}
Html::back();
