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

// `glpi_plugin_dlteams_deliverables` (id, entities_id)
// `glpi_plugin_dlteams_deliverables_sections` (id, deliverables_id)
// `glpi_plugin_dlteams_deliverables_contents` (id, deliverable_sections_id)		

include("../../../inc/includes.php");
global $DB;

public static function export_deliverablergpd($modelrgpd_id = 0) {
	$message = "Export des livrables de l'entité model-rgpd". nl2br("\n"); 
	// On exporte les deliverables de rgpd-model
		// recup de l'id rgpd-model
		$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
		$modelrgpd_id = 0;
		if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $modelrgpd_id = $data['id']; }

	// STEP 1 : on ajoute les oid
		$query = "ALTER TABLE `glpi_plugin_dlteams_deliverables` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
		$DB->queryOrDie($query, $DB->error());
				$query = "ALTER TABLE `glpi_plugin_dlteams_deliverables_sections` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `deliverables_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
				$DB->queryOrDie($query, $DB->error());
						$query = "ALTER TABLE `glpi_plugin_dlteams_deliverables_contents` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `deliverable_sections_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
						$DB->queryOrDie($query, $DB->error());
	// STEP 2 : update des oid
	// `glpi_plugin_dlteams_deliverables` (id, entities_id)
		$query = "UPDATE `glpi_plugin_dlteams_deliverables` SET `oid` = `id` WHERE `entities_id` = '$modelrgpd_id'";
		$DB->queryOrDie($query, $DB->error());
			$query = "UPDATE `glpi_plugin_dlteams_deliverables_sections` AS T2
						LEFT JOIN `glpi_plugin_dlteams_deliverables` AS T1 
						ON T2.`deliverables_id` = T1.`id` 
					  SET T2.`oid` = T2.`id`, T2.`deliverables_oid` = T2.`deliverables_id`
					  WHERE T1.`entities_id` = '$modelrgpd_id'";
			$DB->queryOrDie($query, $DB->error());
					// `glpi_plugin_dlteams_deliverables_contents` (id, deliverable_sections_id)
			$query = "UPDATE `glpi_plugin_dlteams_deliverables_contents` AS T3
						LEFT JOIN `glpi_plugin_dlteams_deliverables_sections` AS T2
						ON T3.`deliverable_sections_id` = T2.`id`
					SET T3.`oid` = T3.`id`, T3.`deliverable_sections_oid` = T3.`deliverable_sections_id`
					WHERE T2.`oid` <> 0";
			$DB->queryOrDie($query, $DB->error());
	// STEP 3 : export without id
	$tables = [
	'glpi_plugin_dlteams_deliverables',
	'glpi_plugin_dlteams_deliverables_sections',
	'glpi_plugin_dlteams_deliverables_contents',
	];
	// `glpi_plugin_dlteams_deliverables` (id, entities_id, plugin_formcreator_categories_id)
	// `glpi_plugin_dlteams_deliverables_sections` (id, plugin_formcreator_forms_id) 
	// `glpi_plugin_dlteams_deliverables_contents` (id, plugin_formcreator_sections_id)
	$fields_exports = [
	['glpi_plugin_dlteams_deliverables', '`oid`, `name`, `content`, `comment`, `document_name`, `document_title`, `document_content`, `document_comment`'],
	['glpi_plugin_dlteams_deliverables_sections', '`oid`, `deliverables_oid`, `name`, `tab_name`, `comment`, `content`, `deliverables_id`, `timeline_position`'],
	['glpi_plugin_dlteams_deliverables_contents', '`oid`, `deliverable_sections_oid`, `name`, `comment`, `content`, `timeline_position`'],
	];
	//var_dump($fields_exports[0]);
    foreach ($fields_exports as list($table, $fields_export)) {
		// $file_pointer = "/var/www/test_dlteams_app/marketplace/dlteams/install/datas/" . $table . ".dat";
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		unlink($file_pointer);
		$query = "SELECT $fields_export FROM $table WHERE `oid` <> 0 INTO OUTFILE '". $file_pointer ."' CHARACTER SET utf8mb4";
		$DB->queryOrDie($query, $DB->error());
		$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0"; 
		$result = $DB->query($query);
		$row = $result->fetch_assoc();
	// 	if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $modelrgpd_id = $data['id']; }
	// var_dump ("Nombre de projet : " . strval($row["COUNT(*)"]));
	    $message .= $table . " : " . strval($row["COUNT(*)"]) . nl2br("\n") ; // strval($result); // . $table .  . "exportés"; 
    }

	// STEP 4 Delete oid
    foreach ($tables as $table) {
		$query = "ALTER TABLE $table DROP IF EXISTS `oid`";
		$DB->queryOrDie($query, $DB->error());
	}
	$query = "ALTER TABLE `glpi_plugin_dlteams_deliverables` DROP IF EXISTS `oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_dlteams_deliverables_sections` DROP IF EXISTS `oid`, DROP IF EXISTS `deliverables_oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_dlteams_deliverables_contents` DROP IF EXISTS `oid`, DROP IF EXISTS `deliverable_sections_oid`";
	$DB->queryOrDie($query, $DB->error());
		
	$message .= "Fichiers .dat créés dans le dossier export";
	Session::addMessageAfterRedirect($message);
	header("Refresh:0; url=config.form.php");
}