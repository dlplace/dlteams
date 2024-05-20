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

// 1------ renommage des contenus de champs en reférence aux classe PluginDlregister.class -> PluginDlteams.class
// Liste des tables qui contiennent uniquement itemtype 
	$table1s = ['accountkeys_items', 'activitycategories_items', 'auditcategories_items', 'audits_items', 'contacts_items', 'datacarriertypes_items', 'datacenters_items', 'deliverables_items', 'deliverables_variables_items', 'documents_items', 'groups_items', 'legalbasis_items', 'links_items', 'meansofacces_items', 'networkequipments_items', 'pdus_items', 'peripherals_items', 'phones_items', 'physicalstorages_items', 'printers_items', 'procedures_variables_items', 'processes_items', 'racks_items', 'rightmeasures_items', 'riskassessments_items', 'storageperiods_items', 'trainingsessions_items', 'users_items'];

// Liste des tables qui contiennent uniquement itemtype + itemtype1
	$table2s = ['concernedpersons_items', 'datacatalogs_items', 'policieforms_items', 'procedures_items', 'processeddatas_items', 'protectivemeasures_items', 'records_items', 'suppliers_items', 'thirdpartycategories_items'];

// Liste des objets à changer (PluginDlregister+object -> PluginDlteams+object)
	$fields = ['ControllerInfo', 'CreatePDF', 'AccountKey', 'Record', 'ConcernedPerson', 'ProcessedData', 'Legalbasi', 'StoragePeriod', 'ThirdPartyCategory', 'RightMeasure', 'PolicieForm', 'DataCarrier', 'DataCatalog', 'TicketTask', 'Appliance', 'Account', 'NetworkPort', 'RiskAssessment', 'Audit', 'PhysicalStorage', 'ProtectiveMeasure', 'TrainingCertification', 'TrainingSession', 'Deliverable', 'KnowbaseItem', 'Procedure', 'Step', 'Vehicle', 'AuditCategory', 'CatalogClassification', 'DataCarrierCategory', 'DataCarrierHosting', 'DataCarrierManagement', 'DataCarrierType', 'Deliverable_Variable', 'Procedure_Variable', 'DataCategory', 'Impact', 'Keytype', 'LegalBasisType', 'MeansOfAcce', 'MediaSupport', 'ProtectiveCategory', 'ProtectiveType', 'ActivityCategory', 'RightMeasureCategory', 'SendingReason', 'ServerType', 'SIIntegration', 'StorageEndAction', 'Storagetype', 'TransmissionMethod', 'Userprofile', 'Process', 'VehicleType'];

	//var_dump($fields_exports[0]);
    foreach ($table1s as $table) {
		foreach ($fields as $field) {
			$query = "UPDATE `glpi_plugin_dlregister_$table` SET itemtype = \"PluginDlteams$field\" WHERE itemtype = \"PluginDlregister$field\"";
			// $DB->queryOrDie($query, $DB->error());
			$DB->query($query);
		}
	}

    foreach ($table2s as $table) {
		foreach ($fields as $field) {
			$query = "UPDATE `glpi_plugin_dlregister_$table` SET itemtype = \"PluginDlteams$field\" WHERE itemtype = \"PluginDlregister$field\"";
			$DB->query($query);
			$query = "UPDATE `glpi_plugin_dlregister_$table` SET itemtype1 = \"PluginDlteams$field\" WHERE itemtype1 = \"PluginDlregister$field\"";
			$DB->query($query);
		}
	}

// prise en compte de displaypreferences
	foreach ($fields as $field) {
		$query = "UPDATE `glpi_displaypreferences` set itemtype = \"PluginDlteams$field\" WHERE itemtype = \"PluginDlregister$field\"";
		$DB->query($query);
	}

/*// prise en compte de glpiprofiles
	$helpdesk_item_type = "[\"Supplier\",\"Computer\",\"Monitor\",\"NetworkEquipment\",\"Peripheral\",\"Phone\",\"Printer\",\"Software\",\"DCRoom\",\"Rack\",\"Enclosure\",\"Appliance\",\"PluginDlregisterRecord\",\"PluginDlregisterConcernedPerson\",\"PluginDlteamsProcessedData\",\"PluginDlteamsLegalBasi\",\"PluginDlteamsStoragePeriod\",\"PluginDlteamsThirdPartyCategory\",\"PluginDlteamsRightMeasure\",\"PluginDlteamsPolicieForm\",\"PluginDlteamsDatabase\",\"PluginDlteamsDataCatalog\",\"PluginDlteamsAccountKey\",\"PluginDlteamsRiskAssessment\",\"PluginDlteamsAudit\",\"PluginDlteamsTrainingCertification\",\"PluginDlteamsDeliverable\"]";
	$query = "UPDATE `glpi_profiles` set `helpdesk_item_type` = $helpdesk_item_type WHERE `name` = 'Admin' or `name` = 'Super-Admin' or `name` = 'Hotliner' or `name` = 'Technician' or `name` = 'Supervisor'";
	$DB->queryOrDie($query, $DB->error());	*/

    $message = "contenu des champs renommés<br>";

// 2------ renommage des champs plugin_dlregister_ -> plugin_dlteams_
$querys =  ['ALTER TABLE `glpi_plugin_dlregister_accountkeys`
	RENAME COLUMN IF EXISTS `plugin_dlregister_keytypes_id` TO `plugin_dlteams_keytypes_id`,
	RENAME COLUMN IF EXISTS `plugin_dlregister_datacatalogs_id` TO `plugin_dlteams_datacatalogs_id`;',
	'ALTER TABLE `glpi_plugin_dlregister_datacatalogs`
	RENAME COLUMN IF EXISTS `plugin_dlregister_catalogclassifications_id` TO `plugin_dlteams_catalogclassifications_id`,
	RENAME COLUMN IF EXISTS `plugin_dlregister_datacarriertypes_id` TO `plugin_dlteams_datacarriertypes_id`,
	RENAME COLUMN IF EXISTS `plugin_dlregister_datacarriercategories_id` TO `plugin_dlteams_datacarriercategories_id`,
	RENAME COLUMN IF EXISTS `plugin_dlregister_datacarriermanagements_id` TO `plugin_dlteams_datacarriermanagements_id`,
	RENAME COLUMN IF EXISTS `plugin_dlregister_datacatalogs_id` TO `plugin_dlteams_datacatalogs_id`,	
	RENAME COLUMN IF EXISTS `plugin_dlregister_userprofiles_id` TO `plugin_dlteams_userprofiles_id`',
	'ALTER TABLE `glpi_plugin_dlregister_legalbasis`
	RENAME COLUMN IF EXISTS `plugin_dlregister_legalbasistypes_id` TO `plugin_dlteams_legalbasistypes_id`;',
	'ALTER TABLE `glpi_plugin_dlregister_physicalstorages`
	RENAME COLUMN IF EXISTS `plugin_dlregister_storageunittypes_id` TO `plugin_dlteams_storageunittypes_id`;',
	'ALTER TABLE `glpi_plugin_dlregister_policieforms_items`
	RENAME COLUMN IF EXISTS `plugin_dlregister_storageendactions_id` TO `plugin_dlteams_storageendactions_id`,
	RENAME COLUMN IF EXISTS `plugin_dlregister_storagetypes_id` TO `plugin_dlteams_storagetypes_id`;',
	'ALTER TABLE `glpi_plugin_dlregister_protectivemeasures`
	RENAME COLUMN IF EXISTS `plugin_dlregister_protectivetypes_id` TO `plugin_dlteams_protectivetypes_id`,
	RENAME COLUMN IF EXISTS `plugin_dlregister_protectivecategories_id` TO `plugin_dlteams_protectivecategories_id`;',
	'ALTER TABLE `glpi_plugin_dlregister_records` 
	RENAME COLUMN IF EXISTS `plugin_dlregister_recordcategories_id` TO `plugin_dlteams_recordcategories_id`,
	RENAME COLUMN IF EXISTS `plugin_dlregister_activitycategories_id` TO `plugin_dlteams_activitycategories_id`;',
	'ALTER TABLE `glpi_plugin_dlregister_records_items`
	RENAME COLUMN IF EXISTS `lugin_dlregister_storageendactions_id` TO `lugin_dlregister_storageendactions_id`,
	RENAME COLUMN IF EXISTS `plugin_dlregister_storagetypes_id` TO `plugin_dlteams_storagetypes_id`;',
	'ALTER TABLE `glpi_plugin_dlregister_rightmeasures`
	RENAME COLUMN IF EXISTS `plugin_dlregister_rightmeasurecategories_id` TO `plugin_dlteams_rightmeasurecategories_id`;',
	'ALTER TABLE `glpi_plugin_dlregister_storageperiods`
	RENAME COLUMN IF EXISTS `plugin_dlregister_storagetypes_id` TO `plugin_dlteams_storagetypes_id`,
	RENAME COLUMN IF EXISTS `plugin_dlregister_legalbasisacts_id_duree1s_id` TO `plugin_dlteams_legalbasisacts_id_duree1s_id`,
	RENAME COLUMN IF EXISTS `plugin_dlregister_legalbasisacts_id_duree2s_id` TO `plugin_dlteams_legalbasisacts_id_duree2s_id`,
	RENAME COLUMN IF EXISTS `plugin_dlregister_legalbasisacts_id_duree3s_id` TO `plugin_dlteams_legalbasisacts_id_duree3s_id`;', 
	'ALTER TABLE `glpi_plugin_dlregister_storageperiods_items`
	RENAME COLUMN IF EXISTS `lugin_dlregister_storageendactions_id` TO `lugin_dlregister_storageendactions_id`,
	RENAME COLUMN IF EXISTS `plugin_dlregister_storagetypes_id` TO `plugin_dlteams_storagetypes_id`;',
	'ALTER TABLE `glpi_plugin_dlregister_vehicles`
	RENAME COLUMN IF EXISTS `plugin_dlregister_vehicletypes_id` TO `plugin_dlteams_vehicletypes_id`;'
	];
	// var_dump($query);die;
    foreach ($querys as $query) {
		$DB->query($query);
	}
	$message .= "champs renommés<br>";
	
// 3------ renommage des tables plugin_dlregister_ -> plugin_dlteams_
	global $DB;
	$objects = ['accountkeys', 'accountkeys_items', 'activitycategories', 'activitycategories_items', 'allitems', 'auditcategories', 'auditcategories_items', 'audits', 'audits_items', 'catalogclassifications', 'concernedpersons', 'concernedpersons_items', 'configs', 'conservationcategory', 'consent', 'contacts_items', 'controllerinfos', 'databasehostings', 'databasemanagements',  'datacarriercategories', 'datacarrierhostings', 'datacarriermanagements', 'datacarriertypes', 'datacarriertypes_items', 'datacatalogs', 'datacatalogs_items', 'datacategories', 'datacenters_items', 'deliverables', 'deliverables_contents', 'deliverables_items', 'deliverables_sections', 'deliverables_variables', 'deliverables_variables_items', 'documents_items', 'groups_items', 'impacts', 'impactorganisms', 'keytypes', 'legalbasis', 'legalbasistypes', 'legalbasis_items', 'links_items', 'meansofacces', 'meansofacces_items', 'mediasupports', 'networkequipments_items', 'pdus_items', 'peripherals_items', 'phones_items', 'physicalstorages', 'physicalstorages_items', 'policieforms', 'policieforms_items', 'processeddatas', 'processeddatas_items', 'processes', 'processes_items', 'protectivecategories', 'protectivemeasures', 'protectivemeasures_items', 'protectivetypes', 'records', 'records_actionfinperiodes', 'records_contracts', 'records_items', 'records_legalbasis', 'records_legalbasisacts', 'records_personalanddatacategories', 'records_retentions', 'records_securitymeasures', 'records_softwares', 'records_consent', 'records_externals', 'storages', 'records_storages', 'records_impacts', 'records_elements', 'records_basedonnees', 'records_impactorganisms', 'rightmeasurecategories', 'rightmeasures', 'rightmeasures_items', 'riskassessments', 'riskassessments_items', 'sendingreasons', 'servertypes', 'storageendactions', 'storageperiods', 'storageperiods_items', 'storagetypes', 'suppliers_items','thirdpartycategories', 'thirdpartycategories_items', 'trainingcategories', 'trainingcertifications', 'trainings', 'userprofiles', 'users_items',  'printers_items', 'procedures', 'procedures_contents', 'procedures_items', 'procedures_sections', 'procedures_variables', 'procedures_variables_items', 'racks_items', 'siintegrations', 'trainingsessions', 'trainingsessions_items', 'transmissionmethods', 'steps_items', 'storageunittypes', 'vehicles', 'vehicletypes'];
	foreach ($objects as $object) {
		$old_table = "glpi_plugin_dlregister_" . $object;
		$new_table = "glpi_plugin_dlteams_" . $object;
		$query = "RENAME TABLE IF EXISTS $old_table TO $new_table";
		$DB->query($query);
	}
	$message .= "tables renommées<br>"; 
	Session::addMessageAfterRedirect($message);

//	renommer les profiles
$query = 'DELETE FROM `glpi_profilerights` WHERE `name` like "plugin_dlplacedpo%"'; 
$DB->query($query);
$query = 'DELETE FROM `glpi_profilerights` WHERE `name` like "plugin_dlteams%"';
$DB->query($query);
// 'UPDATE `glpi_profilerights` SET `name` = CONCAT("plugin_dlteams_", RIGHT(`name`,LENGTH(`name`)-18)) WHERE LEFT(`name`,18) = "plugin_dlregister_"'];
    $query = "UPDATE glpi_profilerights AS t1
    SET t1.name = REPLACE(t1.name, 'dlregister', 'dlteams')
    WHERE t1.name LIKE '%dlregister%'
    AND NOT EXISTS (
        SELECT 1
        FROM glpi_profilerights t2
        WHERE t2.name = REPLACE(t1.name, 'dlregister', 'dlteams')
    )";
    $DB->query($query);
	Html::back();

/*
allitems
glpi_plugin_dlregister_allitems -> itemtype1 & itemtype2
	
NB1 : 
`glpi_plugin_dlteams_trainingcertifications`
NB2 : oubli de vehicles_items ? 
*/
	



