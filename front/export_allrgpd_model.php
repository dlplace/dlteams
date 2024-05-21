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
	$message = "Export des tables et éléments reliés" . "<br>";

// recup de l'id rgpd-model
	$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
	$modelrgpd_id = 0;
	if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $modelrgpd_id = $data['id']; }
	$entities_id_origin = $modelrgpd_id ;

//STEP1 : on ajoute les oid aux tables
	// pour les class avec oid + oid1
	$object1s = ['records', 'concernedpersons', 'processeddatas', 'datacatalogs', 'protectivemeasures'];
	foreach ($object1s as $object1) {
		$table = "glpi_plugin_dlteams_" . $object1;
		$query = "ALTER TABLE " . $table . " ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
		$DB->queryOrDie($query, $DB->error());
		$query = "ALTER TABLE ".$table."_items ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid1` INT UNSIGNED NULL";
		$DB->queryOrDie($query, $DB->error());
	}
	// pour les autres class avec seulement oid
	$object2s = ['legalbasis', 'storageperiods', 'thirdpartycategories', 'rightmeasures', 'policieforms', 'riskassessments', 'audits', 'datacarriertypes', 'deliverables', 'procedures'];
	foreach ($object2s as $object2) {
		$table = "glpi_plugin_dlteams_" . $object2;
		$query = "ALTER TABLE ".$table." ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
		$DB->queryOrDie($query, $DB->error());
		$query = "ALTER TABLE ".$table."_items ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL";
		$DB->queryOrDie($query, $DB->error());
    } 
	// pour les class glpi avec seulement oid
	$object3s = ['appliances'];
	foreach ($object3s as $object3) {
		$table = "glpi_" . $object3;
		$query = "ALTER TABLE ".$table." ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
		$DB->queryOrDie($query, $DB->error());
		$query = "ALTER TABLE ".$table."_items ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL";
		$DB->queryOrDie($query, $DB->error());
    } 

	// complément pour les class glpi deliverable et procedure
	$object4s = ['deliverables_sections', 'deliverables_contents'];
	/*foreach ($object4s as $object4) {
		$table = "glpi_plugin_dlteams_" . $object4;
		$query = "ALTER TABLE ".$table." ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
		$DB->queryOrDie($query, $DB->error());
	}*/
	$query = "ALTER TABLE glpi_plugin_dlteams_deliverables_sections ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `deliverables_oid` INT UNSIGNED NULL";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE glpi_plugin_dlteams_deliverables_contents ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `deliverable_sections_oid` INT UNSIGNED NULL";
	$DB->queryOrDie($query, $DB->error());

//STEP2 : on approvisionne $object pour entities_id = origin puis les $object_items liés
	// classes avec deux liaisons items_id & items_id1
	foreach ($object1s as $object1) {
	$table = "glpi_plugin_dlteams_" . $object1;
	$query = "UPDATE ".$table." SET `oid` = `id` WHERE `entities_id`= " . $entities_id_origin . " AND `is_deleted` = 0";
	$DB->queryOrDie($query, $DB->error());
		$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0";
		$row = $DB->query($query)->fetch_assoc();
	    $message .= strval($row["COUNT(*)"]) . " " . $table . " copiés" . "<br>";
	$query = "UPDATE ".$table."_items as t1 INNER JOIN ".$table." as t2 ON t1.`".$object1."_id` = t2.`id` and t2.`entities_id` = $entities_id_origin 
	SET t1.`oid` = t1.`".$object1."_id`, t1.`items_oid` = t1.`items_id`, t1.`items_oid1` = t1.`items_id1`";
	$DB->queryOrDie($query, $DB->error());
	}
		$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0";
		$row = $DB->query($query)->fetch_assoc();
	    $message .= strval($row["COUNT(*)"]) . " " . $table . " copiés" . "<br>";

	// classes avec une seule liaison items_id 
	foreach ($object2s as $object2) {
		$table = 'glpi_plugin_dlteams_' . $object2;
		$query = "UPDATE ".$table." as t1 SET t1.`oid` = t1.`id` WHERE entities_id= " . $entities_id_origin . " AND `is_deleted` = 0";
		$DB->queryOrDie($query, $DB->error());
		$query = "UPDATE ".$table."_items AS t1 INNER JOIN ".$table." as t2 ON t1.`".$object2."_id` = t2.`id` and t2.`entities_id` = $entities_id_origin 
		SET t1.`oid` = t1.`" . $object2 . "_id`, t1.`items_oid` = t1.`items_id`";
		// var_dump ($query) ; die;
		$DB->queryOrDie($query, $DB->error());
			$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0";
			$row = $DB->query($query)->fetch_assoc();
			$message .= strval($row["COUNT(*)"]) . " " . $table . " copiés" . "<br>";
	}

	//classes GLPI
	foreach ($object3s as $object3) {
		$table = 'glpi_' . $object3;
		$query = "UPDATE ".$table." as t1 SET t1.`oid` = t1.`id` WHERE entities_id= " . $entities_id_origin . " AND `is_deleted` = 0";
		$DB->queryOrDie($query, $DB->error());
		$query = "UPDATE ".$table."_items AS t1 INNER JOIN ".$table." as t2 ON t1.`".$object3."_id` = t2.`id` and t2.`entities_id` = $entities_id_origin 
		SET t1.`oid` = t1.`" . $object3 . "_id`, t1.`items_oid` = t1.`items_id`";
		// var_dump ($query) ; die;
		$DB->queryOrDie($query, $DB->error());
			$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0";
			$row = $DB->query($query)->fetch_assoc();
			$message .= strval($row["COUNT(*)"]) . " " . $table . " copiés" . "<br>";
	}
	
	// classes deliverable & procedures
	foreach ($object4s as $object4) {
		$table1 = 'glpi_plugin_dlteams_deliverables' ; $table2 = 'glpi_plugin_dlteams_deliverables_sections' ; $table3 = 'glpi_plugin_dlteams_deliverables_contents';
		$query = "UPDATE $table2 AS t1 INNER JOIN $table1 as t2 ON t1.`deliverables_id` = t2.`id` and t2.`entities_id` = $entities_id_origin 
		SET t1.`deliverables_oid` = t2.id, t1.`oid` = t1.`id`";
		print_r ($query . "<br>");
		$DB->queryOrDie($query, $DB->error());
		$query = "UPDATE $table3 AS t1 INNER JOIN $table2 as t2 ON t1.`deliverable_sections_id` = t2.`id` and t2.`oid` <> 0
		SET t1.`deliverable_sections_oid` = t2.id, t1.`oid` = t1.`id`";
		print_r ($query . "<br>");
		$DB->queryOrDie($query, $DB->error());
	}

    ExportDAT () ; 
	
    function ExportDAT () {
		global $DB;
		// STEP 3 Export tables
		FolderExport () ; //verify if export folder exist
		$glpiRoot=str_replace('\\', '/', GLPI_ROOT);
		$fields_exports = [
			['glpi_plugin_dlteams_records','`is_recursive`, `date_creation`, `number`, `name`, `content`, `additional_info`, `states_id`, `first_entry_date`, `consent_json`, `consent_type`, `consent_type1`, 
			`consent_explicit`, `diffusion`, `right_information`, `right_correction`, `right_opposition`, `right_portability`, `sensitive`, `profiling`, `profiling_auto`, `mediasupport`, `siintegration`, `transmissionmethod`, `external_group`, `external_supplier`, `external_process`, `impact_person`, `impact_organism`, `specific_security_measures`, `collect_comment`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_plugin_dlteams_records_items','`itemtype`, `itemtype1`, `comment`, `json`, `timeline_position`, `date_creation`, `plugin_dlteams_storageendactions_id`, `plugin_dlteams_storagetypes_id`,
		`mandatory`, `oid`, `items_oid`, `items_oid1`'],
			['glpi_plugin_dlteams_concernedpersons', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_plugin_dlteams_concernedpersons_items', '`itemtype`, `itemtype1`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`, `items_oid1`'],
			['glpi_plugin_dlteams_processeddatas', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_plugin_dlteams_processeddatas_items', '`itemtype`, `itemtype1`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`, `items_oid1`'],
			['glpi_plugin_dlteams_legalbasis', '`is_recursive`, `name`, `plugin_dlteams_legalbasistypes_id`, `content`, `comment`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_plugin_dlteams_legalbasis_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
			['glpi_plugin_dlteams_storageperiods', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_plugin_dlteams_storageperiods_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `plugin_dlteams_storageendactions_id`, `plugin_dlteams_storagetypes_id`, `oid`, `items_oid`'],
			['glpi_plugin_dlteams_thirdpartycategories', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_plugin_dlteams_thirdpartycategories_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
			['glpi_plugin_dlteams_rightmeasures', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_plugin_dlteams_rightmeasures_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
			['glpi_plugin_dlteams_policieforms','`is_recursive`, `date_creation`, `name`, `content`, `documentcategories_id`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_plugin_dlteams_policieforms_items','`itemtype`, `comment`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
			['glpi_plugin_dlteams_datacatalogs','`is_recursive`, `date_creation`, `name`, `completename`, `content`, `comment`, `plugin_dlteams_catalogclassifications_id`, `plugin_dlteams_datacarriercategories_id`, `is_directoryservice`, `directory_name`, `default_keytype`, `level`, `ancestors_cache`, `sons_cache`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_plugin_dlteams_datacatalogs_items','`itemtype`, `itemtype1`, `comment`, `timeline_position`, `date_creation`, `oid`, `items_oid`, `items_oid1`'],
			['glpi_plugin_dlteams_riskassessments','`is_recursive`, `date_creation`, `name`, `content`, `comment`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_plugin_dlteams_riskassessments_items','`itemtype`, `comment`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
			['glpi_plugin_dlteams_audits','`is_recursive`, `date_creation`, `name`, `content`, `comment`, `plugin_dlteams_auditcategories_id`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_plugin_dlteams_audits_items','`itemtype`, `comment`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
			['glpi_plugin_dlteams_protectivemeasures', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `plugin_dlteams_protectivetypes_id`, `plugin_dlteams_protectivecategories_id`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_plugin_dlteams_protectivemeasures_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
			['glpi_plugin_dlteams_datacarriertypes', '`is_recursive`, `name`, `comment`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_plugin_dlteams_datacarriertypes_items', '`itemtype`, `comment`, `date_creation`, `oid`, `items_oid`'],
			['glpi_appliances', '`is_recursive`, `name`, `is_deleted`, `appliancetypes_id`, `comment`, `manufacturers_id`, `applianceenvironments_id`, `date_mod`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_appliances_items', '`itemtype`, `comment`, `oid`, `items_oid`'],
			['glpi_plugin_dlteams_deliverables', '`name`, `content`, `comment`, `document_name`, `document_title`, `document_content`,`document_comment`,`object_notification`, `object_approval`, `text_notification`, `text_approval`, `date_mod`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
			['glpi_plugin_dlteams_deliverables_items', '`itemtype`, `itemtype1`, `comment`, `date_creation`, `oid`, `items_oid`'],
			['glpi_plugin_dlteams_deliverables_sections', '`name`, `tab_name`, `comment`, `content`, `timeline_position`, `date_mod`, `date_creation`, `oid`,`deliverables_oid`'],
			['glpi_plugin_dlteams_deliverables_contents', '`name`, `comment`, `content`, `timeline_position`, `date_mod`, `date_creation`, `oid`, `deliverable_sections_oid`'],
			];

		$exportable_itemtype = ['"PluginDlteamsRecord", "PluginDlteamsConcernedPerson", "PluginDlteamsProcessedData", "PluginDlteamsLegalBasi", "PluginDlteamsStoragePeriod", "PluginDlteamsThirdPartyCategory",
		"PluginDlteamsRightMeasure", "PluginDlteamsPolicieform", "PluginDlteamsDataCatalog", "PluginDlteamsRiskassement", "PluginDlteamsAudit", "PluginDlteamsProtectiveMeasure",
		"PluginDlteamsDataCarrierType", "Appliance", "PluginDlteamsDeliverable" '];
		$in = '(' . implode(',', $exportable_itemtype) .')';

		// chmod("/var/www/test_dlteams_app/marketplace/dlteams/install/datas/", 0755);
		print_r ($glpiRoot. "/marketplace/dlteams/install/datas/");
		chmod($glpiRoot. "/marketplace/dlteams/install/datas/", 0777);

		foreach ($fields_exports as list($table, $fields_export)) {
		// si model-rgpd alors dossier install/datas, sinon dossier files/_plugins/dlteams/
		$file_pointer = $glpiRoot. "/marketplace/dlteams/install/datas/" . $table . ".dat";
		// pour tests, on met dans test - $file_pointer = "/var/www/test_dlteams_app/marketplace/dlteams/install/datas/" . $table . ".dat";
			unlink($file_pointer);
			$endoftable = substr($table, -6);
			//var_dump ($endoftable) ;
			if ($endoftable == "_items") {
				$query = "SELECT $fields_export FROM $table WHERE `oid` IS NOT NULL AND `itemtype` IN " . $in . " INTO OUTFILE '" . $file_pointer . "' CHARACTER SET utf8mb4";
			} else {
				$query = "SELECT $fields_export FROM $table WHERE `oid` IS NOT NULL INTO OUTFILE '" . $file_pointer . "' CHARACTER SET utf8mb4";
			}
		$DB->queryOrDie($query, $DB->error());
		}
		chmod($glpiRoot. "/marketplace/dlteams/install/datas/", 0755);
	

	// STEP 4 Delete oid
		foreach ($fields_exports as list($table, $fields_export)) {
			$query = "ALTER TABLE $table DROP IF EXISTS `oid`, DROP IF EXISTS `items_oid`, DROP IF EXISTS `items_oid1` ";
			$DB->queryOrDie($query, $DB->error());
		}
	}
	
	$message .= "Fichiers .dat créés dans le dossier export";
	Session::addMessageAfterRedirect($message, false, INFO);
	echo "<script>window.location.href='config.form.php';</script>";// revient sur la page
	//header("Refresh:0; url=config.form.php");
