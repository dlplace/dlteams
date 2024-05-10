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

	$message = "Export des jeux de données et éléments liés" . "<br>";

//STEP1 : on ajoute les oid aux tables
	// records
	$table = "glpi_plugin_dlteams_policieforms";
	$query = "ALTER TABLE " . $table . " ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
    $DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE ".$table."_items ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid1` INT UNSIGNED NULL";
	$DB->queryOrDie($query, $DB->error());

// oid pour les autres class
 $objects = [
'processeddatas',
'datacarriertypes',
'legalbasis',
'storageperiods',
//'rightmeasures',
//'protectivemeasures'
];
	foreach ($objects as $object) {
	// 	$object & $object_items : ajout des oid
		$table = "glpi_plugin_dlteams_" . $object;
		$query = "ALTER TABLE ".$table." ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
		$DB->queryOrDie($query, $DB->error());
		$query = "ALTER TABLE ".$table."_items ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL";
		$DB->queryOrDie($query, $DB->error());
   }

// recup de l'id rgpd-model
	$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
	$modelrgpd_id = 0;
	if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $modelrgpd_id = $data['id']; }
	$entities_id_origin = $modelrgpd_id ;

//STEP2 : on approvisionne les oid
	// records & records_items pour entities_id = origin
	$query = "UPDATE `glpi_plugin_dlteams_policieforms` SET `oid` = `id` WHERE `entities_id`= " . $entities_id_origin . " AND `is_deleted` = 0";
	$DB->queryOrDie($query, $DB->error());
		$query = "SELECT COUNT(*) FROM glpi_plugin_dlteams_policieforms WHERE `oid` <> 0";
		$row = $DB->query($query)->fetch_assoc();
	    $message .= strval($row["COUNT(*)"]) . " jeux de données copiés" . "<br>";
	$query = "UPDATE `glpi_plugin_dlteams_policieforms_items` as t1 INNER JOIN `glpi_plugin_dlteams_policieforms` as t2 ON t1.`policieforms_id` = t2.`id` and t2.`entities_id` = $entities_id_origin 
	SET t1.`oid` = t1.`policieforms_id`, t1.`items_oid` = t1.`items_id`";
	$DB->queryOrDie($query, $DB->error());
// autres classes
	foreach ($objects as $object) {
	// on approvisionne $object & $object_items pour itemtype = "PluginDlteamsPolicieForm" and entities_id = origin)
		$table = 'glpi_plugin_dlteams_' . $object;
		$query = "UPDATE ".$table." as t1 SET t1.`oid` = t1.`id` WHERE entities_id= " . $entities_id_origin . " AND `is_deleted` = 0";
		$DB->queryOrDie($query, $DB->error());
		$query = "UPDATE ".$table."_items AS t1 INNER JOIN `glpi_plugin_dlteams_policieforms` as t2 ON t1.`items_id` = t2.`id` and t1.`itemtype` = 'PluginDlteamsPolicieForm' and t2.`entities_id` = $entities_id_origin 
		SET t1.`oid` = t1.`" . $object . "_id`, t1.`items_oid` = t1.`items_id`";
		// var_dump ($query) ; die;
		$DB->queryOrDie($query, $DB->error());
			$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0";
			$row = $DB->query($query)->fetch_assoc();
			$message .= strval($row["COUNT(*)"]) . " " . $table . " copiés" . "<br>";
   }

// STEP 3 Export tables
   FolderExport () ; //verify if export folder exist
   $glpiRoot=str_replace('\\', '/', GLPI_ROOT);
   $fields_exports = [
	['glpi_plugin_dlteams_policieforms','`is_recursive`, `is_deleted`, `date_creation`, `name`, `content`, `documentcategories_id`, `oid`'],
	['glpi_plugin_dlteams_policieforms_items','`itemtype`, `comment`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
	['glpi_plugin_dlteams_processeddatas', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
	['glpi_plugin_dlteams_processeddatas_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
	['glpi_plugin_dlteams_datacarriertypes', '`is_recursive`, `name`, `comment`, `date_creation`, `oid`'],
	['glpi_plugin_dlteams_datacarriertypes_items', '`itemtype`, `comment`, `date_creation`, `oid`, `items_oid`'],
	['glpi_plugin_dlteams_legalbasis', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
	['glpi_plugin_dlteams_legalbasis_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
	['glpi_plugin_dlteams_storageperiods', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
	['glpi_plugin_dlteams_storageperiods_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
];

   $exportable_itemtype = ['"PluginDlteamsPolicieForm", "PluginDlteamsProcessedData", "PluginDlteamsDataCarrierType", "PluginDlteamsLegalBasi", "PluginDlteamsStoragePeriod"'];
   $in = '(' . implode(',', $exportable_itemtype) .')';

   foreach ($fields_exports as list($table, $fields_export)) {
		// si model-rgpd alors dossier install/datas, sinon dossier files/_plugins/dlteams/
		$file_pointer = $glpiRoot. "/marketplace/dlteams/install/datas/" . $table . ".dat";
			// pour tests, on met dans empty
			//$file_pointer = "/var/www/empty_dlteams_app/marketplace/dlteams/install/datas/" . $table . ".dat";
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

// STEP 4 Delete oid
   foreach ($fields_exports as list($table, $fields_export)) {
		$query = "ALTER TABLE $table DROP IF EXISTS `oid`, DROP IF EXISTS `items_oid`, DROP IF EXISTS `items_oid1` ";
		$DB->queryOrDie($query, $DB->error());
		}

	$message .= "Fichiers .dat créés dans le dossier export";
Session::addMessageAfterRedirect($message, false, INFO);
header("Refresh:0; url=config.form.php");
