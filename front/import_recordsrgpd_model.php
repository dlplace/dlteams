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

$DB->request("SET @currententity_id := ".Session::getActiveEntity());
$DB->request("SET @currentuser_id := ".Session::getLoginUserID());
$message = "Import des traitements et éléments reliés" ;

// model-rgpd & user & profils & rights creation
	$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
    $modelrgpd_id = 0;
    if ($result && $DB->numrows($result) > 0) {
        $data = $DB->fetchAssoc($result);
        $modelrgpd_id = $data['id'];
    }

// STEP 1 : on supprime les traitements et dépendances de model-rgpd
$DB->request("SET @modelrgpd_id := ".$modelrgpd_id);
		$query = "SELECT COUNT(*) FROM `glpi_plugin_dlteams_records` WHERE `entities_id` = '$modelrgpd_id'";
		$row = $DB->query($query)->fetch_assoc();
		$message .= strval($row["COUNT(*)"]) . " traitements précédents supprimés <br>" . "Ajouts : " . nl2br("\n");

$querys = [
'DELETE t1 FROM `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t2.`id` = t1.`records_id` WHERE t2.`entities_id` =  @modelrgpd_id',
'DELETE t1 FROM `glpi_plugin_dlteams_concernedpersons_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t2.`id` = t1.`items_id` WHERE t2.`entities_id` =  @modelrgpd_id AND t1.`itemtype` = "PluginDlteamsRecord"' ,
'DELETE t1 FROM `glpi_plugin_dlteams_processeddatas_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t2.`id` = t1.`items_id` WHERE t2.`entities_id` =  @modelrgpd_id AND t1.`itemtype` = "PluginDlteamsRecord"' ,
'DELETE t1 FROM `glpi_plugin_dlteams_legalbasis_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t2.`id` = t1.`items_id` WHERE t2.`entities_id` =  @modelrgpd_id AND t1.`itemtype` = "PluginDlteamsRecord"' ,
'DELETE t1 FROM `glpi_plugin_dlteams_storageperiods_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t2.`id` = t1.`items_id` WHERE t2.`entities_id` =  @modelrgpd_id AND t1.`itemtype` = "PluginDlteamsRecord"' ,
'DELETE t1 FROM `glpi_plugin_dlteams_rightmeasures_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t2.`id` = t1.`items_id` WHERE t2.`entities_id` =  @modelrgpd_id AND t1.`itemtype` = "PluginDlteamsRecord"' ,
'DELETE t1 FROM `glpi_plugin_dlteams_thirdpartycategories_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t2.`id` = t1.`items_id` WHERE t2.`entities_id` =  @modelrgpd_id AND t1.`itemtype` = "PluginDlteamsRecord"' ,
'DELETE t1 FROM `glpi_plugin_dlteams_protectivemeasures_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t2.`id` = t1.`items_id` WHERE t2.`entities_id` =  @modelrgpd_id AND t1.`itemtype` = "PluginDlteamsRecord"' ,

'DELETE FROM `glpi_plugin_dlteams_records` WHERE `entities_id` =  @modelrgpd_id',
'DELETE FROM `glpi_plugin_dlteams_concernedpersons` WHERE `entities_id` =  @modelrgpd_id',
'DELETE FROM `glpi_plugin_dlteams_processeddatas` WHERE `entities_id` =  @modelrgpd_id',
'DELETE FROM `glpi_plugin_dlteams_legalbasis` WHERE `entities_id` =  @modelrgpd_id',
'DELETE FROM `glpi_plugin_dlteams_storageperiods` WHERE `entities_id` =  @modelrgpd_id',
'DELETE FROM `glpi_plugin_dlteams_thirdpartycategories` WHERE `entities_id` =  @modelrgpd_id',
'DELETE FROM `glpi_plugin_dlteams_rightmeasures` WHERE `entities_id` =  @modelrgpd_id',
'DELETE FROM `glpi_plugin_dlteams_policieforms` WHERE `entities_id` =  @modelrgpd_id',
'DELETE FROM `glpi_plugin_dlteams_protectivemeasures` WHERE `entities_id` =  @modelrgpd_id',
'DELETE FROM `glpi_plugin_dlteams_deliverables` WHERE `entities_id` =  @modelrgpd_id',
'DELETE FROM `glpi_plugin_dlteams_audits` WHERE `entities_id` =  @modelrgpd_id',
'DELETE FROM `glpi_plugin_dlteams_riskassessments` WHERE `entities_id` =  @modelrgpd_id',
];

	foreach ($querys as $query) {
//var_dump ($query); die;
		$DB->queryOrDie($query, $DB->error());
    }

// STEP 2 : adding oid
//records : on ajoute les oid
	$query = "ALTER TABLE `glpi_plugin_dlteams_records` ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
    $DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_dlteams_records_items` ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid1` INT UNSIGNED NULL";
	$DB->queryOrDie($query, $DB->error());
// 	concerned_persons : ajout des oid
	$query = "ALTER TABLE `glpi_plugin_dlteams_concernedpersons` ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_dlteams_concernedpersons_items` ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid1` INT UNSIGNED NULL";
	$DB->queryOrDie($query, $DB->error());
// 	processeddatas & processeddatas_items : ajout des oid
	$table = "glpi_plugin_dlteams_processeddatas";
	$query = "ALTER TABLE ".$table." ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE ".$table."_items ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid1` INT UNSIGNED NULL";
	$DB->queryOrDie($query, $DB->error());

// autres class
 $objects = [
'legalbasis',
'storageperiods',
'rightmeasures',
'thirdpartycategories',
'protectivemeasures'
];
	foreach ($objects as $object) {
	// 	$object & $object_items : ajout des oid
		$table = "glpi_plugin_dlteams_" . $object;
		$query = "ALTER TABLE ".$table." ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
		$DB->queryOrDie($query, $DB->error());
		$query = "ALTER TABLE ".$table."_items ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL";
		$DB->queryOrDie($query, $DB->error());
			$query = "SELECT COUNT(*) FROM " . $table. " WHERE `entities_id` = '$modelrgpd_id'";
			$row = $DB->query($query)->fetch_assoc();
			$message .= strval($row["COUNT(*)"]) . $table . " importés <br>";
   }

// STEP 3 : import datas without id
$fields_exports = [
	['glpi_plugin_dlteams_records','`is_recursive`, `is_deleted`, `date_creation`, `number`, `name`, `content`, `additional_info`, `states_id`, `first_entry_date`, `consent_json`, `consent_type`, `consent_type1`, `consent_explicit`, 
	`plugin_dlteams_recordcategories_id`, `diffusion`, `conservation_time`, `archive_time`, `archive_required`, `right_information`, `right_correction`, `right_opposition`, `right_portability`, `sensitive`, `external_process`, `impact_person`, `impact_organism`, `oid`'],
	['glpi_plugin_dlteams_records_items','`itemtype`, `itemtype1`, `comment`, `json`, `timeline_position`, `date_creation`, `plugin_dlteams_storageendactions_id`, `plugin_dlteams_storagetypes_id`,
	`mandatory`, `oid`, `items_oid`, `items_oid1`'],
	['glpi_plugin_dlteams_concernedpersons', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
	['glpi_plugin_dlteams_concernedpersons_items', '`itemtype`, `itemtype1`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`, `items_oid1`'],
	['glpi_plugin_dlteams_processeddatas', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
	['glpi_plugin_dlteams_processeddatas_items', '`itemtype`, `itemtype1`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`, `items_oid1`'],
	['glpi_plugin_dlteams_legalbasis', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
	['glpi_plugin_dlteams_legalbasis_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
	['glpi_plugin_dlteams_storageperiods', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
	['glpi_plugin_dlteams_storageperiods_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
	['glpi_plugin_dlteams_rightmeasures', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
	['glpi_plugin_dlteams_rightmeasures_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
	['glpi_plugin_dlteams_thirdpartycategories', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
	['glpi_plugin_dlteams_thirdpartycategories_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
	['glpi_plugin_dlteams_protectivemeasures', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `plugin_dlteams_protectivetypes_id`, `plugin_dlteams_protectivecategories_id`, `oid`'],
	['glpi_plugin_dlteams_protectivemeasures_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
];
   foreach ($fields_exports as list($table, $fields_export)) {
		// si model dossier install/datas ; sinon dossier files/_plugins/dlteams/
			//$glpiRoot=str_replace('\\', '/', GLPI_ROOT);
			//$file_pointer = $glpiRoot. "/marketplace/dlteams/install/datas/" . $table . ".dat";
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		// pour export d'une entité, on envoie dans files/plugins
			// $file_pointer = $glpiRoot. "/files/_plugins/" . "dlteams"."/" . $table . ".dat";
		// pour tests, on prend le dossier de prod
			//$file_pointer = "/var/www/dlteams_app/marketplace/dlteams/install/datas/" . $table . ".dat";
		$query = "LOAD DATA INFILE '".$file_pointer."' INTO TABLE ".$table." FIELDS TERMINATED BY '\t' ($fields_export)";
		//var_dump($query); die;
		$DB->queryOrDie($query, $DB->error());
			$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0";
			$row = $DB->query($query)->fetch_assoc();
			$message .= $table . " : " . strval($row["COUNT(*)"]) . nl2br("\n") ;
   }

// STEP 4 : update relation id
	// pour les tables objets, on met à jour entities_id = origin + date + copy_id
	$entities_id_target = $modelrgpd_id;
	$date = date('Y-m-d H:i:s');
    foreach ($fields_exports as list($table, $fields_export)) {
		$endoftable = substr($table, -6);
		if ($endoftable <> "_items") {
		$query = "UPDATE ".$table." SET `entities_id` = " . $entities_id_target . ", `date_mod` = " . '"' . $date . '"' . ", `copy_id` =  `oid` WHERE `oid` is not null" ;
		$DB->queryOrDie($query, $DB->error());
		}
		// $query = "UPDATE ".$table." SET `date_creation` =  WHERE `oid` is not null" ;
		// $DB->queryOrDie($query, $DB->error());
	}

	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.oid = t2.oid 
	SET t1.`records_id` = t2.`id`, t1.`date_mod` = " . '"' . $date . '"';
    $DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_concernedpersons` as t3 ON t1.`items_oid` = t3.`oid` AND t1.`itemtype` = ". '"PluginDlteamsConcernedPerson"' .
	" SET t1.`items_id` = t3.`id`";
    $DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_processeddatas` as t3 ON t1.`items_oid1` = t3.`oid` AND t1.`itemtype1` = ". '"PluginDlteamsProcessedData"' .
	"SET t1.`items_id1` = t3.`id`";
    $DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_legalbasis` as t3 ON t1.`items_oid` = t3.`oid` AND t1.`itemtype` = " . '"PluginDlteamsLegalbasi"' .
	"SET t1.`items_id` = t3.`id`";
	$DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_storageperiods` as t3 ON t1.`items_oid` = t3.`oid` AND t1.`itemtype` = " . '"PluginDlteamsStoragePeriod"' .
	"SET t1.`items_id` = t3.`id`";
	$DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_rightmeasures` as t3 ON t1.`items_oid` = t3.`oid` AND t1.`itemtype` = " . '"PluginDlteamsRightMeasure"'.
	"SET t1.`items_id` = t3.`id`";
	$DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_thirdpartycategories` as t3 ON t1.`items_oid` = t3.`oid` AND t1.`itemtype` = " . '"PluginDlteamsThirdPartyCategory"'.
	"SET t1.`items_id` = t3.`id`";
	$DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_protectivemeasures` as t3 ON t1.`items_oid` = t3.`oid` AND t1.`itemtype` = " . '"PluginDlteamsProtectiveMeasure"'.
	"SET t1.`items_id` = t3.`id`";
	$DB->queryOrDie($query, $DB->error());

	$query = "UPDATE `glpi_plugin_dlteams_concernedpersons_items` as t1 
	INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t2.`oid` = t1.`items_oid`  
	INNER JOIN `glpi_plugin_dlteams_concernedpersons` as t3 ON t3.`oid` = t1.`oid`
	INNER JOIN `glpi_plugin_dlteams_processeddatas` as t4 ON t4.`oid` = t1.`items_oid1`
	SET t1.`concernedpersons_id` = t3.`id`, t1.`items_id` = t2.`id`, t1.`items_id1` = t4.`id`";
    $DB->queryOrDie($query, $DB->error());

	$query = "UPDATE `glpi_plugin_dlteams_processeddatas_items` as t1 
	INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.items_oid = t2.oid 
	INNER JOIN `glpi_plugin_dlteams_processeddatas` as t3 ON t1.oid = t3.oid
	INNER JOIN `glpi_plugin_dlteams_concernedpersons` as t4 ON t1.items_oid1 = t4.oid
	SET t1.`processeddatas_id` = t3.`id`, t1.`items_id` = t2.`id`, t1.`items_id1` = t4.`id`";
    $DB->queryOrDie($query, $DB->error());

	$query = "UPDATE `glpi_plugin_dlteams_legalbasis_items` as t1 
	INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_oid` = t2.`oid` 
	INNER JOIN `glpi_plugin_dlteams_legalbasis` as t3 ON t1.`oid` = t3.`oid` 
	SET t1.`legalbasis_id` = t3.`id`, t1.`items_id` = t2.`id`";
	$DB->queryOrDie($query, $DB->error());

	//$result = $DB->query('SELECT ROW_COUNT() AS nombre_enregistrements');
	$query = "UPDATE `glpi_plugin_dlteams_storageperiods_items` as t1 
	INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_oid` = t2.`oid` 
	INNER JOIN `glpi_plugin_dlteams_storageperiods` as t3 ON t1.`oid` = t3.`oid` 
	SET t1.`storageperiods_id` = t3.`id`, t1.`items_id` = t2.`id`";
	$DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_thirdpartycategories_items` as t1 
	INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_oid` = t2.`oid` 
	INNER JOIN `glpi_plugin_dlteams_thirdpartycategories` as t3 ON t1.`oid` = t3.`oid` 
	SET t1.`thirdpartycategories_id` = t3.`id`, t1.`items_id` = t2.`id`";
	$DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_protectivemeasures_items` as t1 
	INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_oid` = t2.`oid` 
	INNER JOIN `glpi_plugin_dlteams_protectivemeasures` as t3 ON t1.`oid` = t3.`oid` 
	SET t1.`protectivemeasures_id` = t3.`id`, t1.`items_id` = t2.`id`";
	$DB->queryOrDie($query, $DB->error());

// STEP 5 Delete oid
   foreach ($fields_exports as list($table, $fields_export)) {
		$query = "ALTER TABLE $table DROP IF EXISTS `oid`, DROP IF EXISTS `items_oid`, DROP IF EXISTS `items_oid1`";
		$DB->queryOrDie($query, $DB->error());
		}

Session::addMessageAfterRedirect($message);
header("Refresh:0; url=config.form.php");

////////////////// OLD
/*	$tables = ['glpi_plugin_dlteams_allitems',
	'glpi_plugin_dlteams_records_externals',
	 'glpi_plugin_dlteams_records_personalanddatacategories',
	'glpi_plugin_dlteams_records_storages'];
	foreach ($tables as $table) {
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		// delete datas in database
		$DB->queryOrDie("delete from `".$table."`");
		$DB->queryOrDie("LOAD DATA INFILE '".$file_pointer."' INTO TABLE `".$table."` FIELDS TERMINATED BY '\t'");
		Session::addMessageAfterRedirect($table);
	}

	// IMPORT .dat in rgpd-model records
   $tables = [
'glpi_plugin_dlteams_records',
'glpi_plugin_dlteams_concernedpersons',
'glpi_plugin_dlteams_processeddatas',
'glpi_plugin_dlteams_legalbasis',
'glpi_plugin_dlteams_storageperiods',
'glpi_plugin_dlteams_thirdpartycategories',
'glpi_plugin_dlteams_rightmeasures',
'glpi_plugin_dlteams_policieforms',
'glpi_plugin_dlteams_protectivemeasures',
'glpi_plugin_dlteams_deliverables',
'glpi_plugin_dlteams_audits',
'glpi_plugin_dlteams_riskassessments'
];

	// import datas from files in plugin/install/datas/
	foreach ($tables as $table) {
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		// delete datas in database
		$DB->queryOrDie("delete from `".$table."`");
		// import datas from files in plugin/install/datas/
		$DB->queryOrDie("LOAD DATA INFILE '".$file_pointer."' INTO TABLE `".$table."` FIELDS TERMINATED BY '\t'");
		Session::addMessageAfterRedirect($table);
	}

	// idem form _items tables
	foreach ($tables as $table) {
		$table = $table . "_items";
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		// delete datas in database
		$DB->queryOrDie("delete from `".$table."`");
		// import datas from files in plugin/install/datas/
		$DB->queryOrDie("LOAD DATA INFILE '".$file_pointer."' INTO TABLE `".$table."` FIELDS TERMINATED BY '\t'");
		Session::addMessageAfterRedirect($table);
	}

	// then entities_id  = @modelrgpd_id for records where id < 1000
 	foreach ($tables as $table) {
		$DB->queryOrDie("UPDATE `".$table."` SET entities_id = ".$rgpdmodel_id." WHERE id BETWEEN 1 and 999 ");
	}

	header("Refresh:0; url=config.form.php");
	Session::addMessageAfterRedirect("Import ok");
*/
