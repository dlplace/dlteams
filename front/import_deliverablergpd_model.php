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

// `glpi_plugin_dlteams_deliverables` (id, entities_id, deliverables_id)
// `glpi_plugin_dlteams_deliverables_sections` (id, deliverables_id)
// `glpi_plugin_dlteams_deliverables_contents` (id, deliverable_sections_id)

$message = "Import des livrables de l'entité model-rgpd". nl2br("\n");
// STEP 1 : fonction de suppression des livrables
  function delete_form($modelrgpd_id) {
	global $DB;
		// `glpi_plugin_formcreator_categories`
				/*$query = "DELETE T1 FROM `glpi_plugin_formcreator_categories` AS T1 
				INNER JOIN `glpi_plugin_formcreator_forms` AS T2 ON T1.`id` =  T2.`plugin_formcreator_categories_id` AND T2.`entities_id` = '$modelrgpd_id'";
				$DB->queryOrDie($query, $DB->error());*/
		// `glpi_plugin_dlteams_deliverables_contents`
				$query = "DELETE T1 FROM `glpi_plugin_dlteams_deliverables_contents`AS T3
				LEFT JOIN `glpi_plugin_dlteams_deliverables_sections` AS T2 ON T3.`deliverable_sections_id` = T2.`id`
				LEFT JOIN `glpi_plugin_dlteams_deliverables` AS T1 ON T2.`deliverables_id` = T1.`id` AND T1.`entities_id` = '$modelrgpd_id'";
				$DB->queryOrDie($query, $DB->error());
		// `glpi_plugin_dlteams_deliverables_sections`
				$query = "DELETE T2 FROM `glpi_plugin_dlteams_deliverables_sections` AS T2 
				LEFT JOIN `glpi_plugin_dlteams_deliverables` AS T1 ON T2.`deliverables_id` = T1.`id` AND T1.`entities_id` = '$modelrgpd_id'";
				$DB->queryOrDie($query, $DB->error());
		// `glpi_plugin_dlteams_deliverables`
				$query = "DELETE T1 FROM `glpi_plugin_dlteams_deliverables` AS T1 
				WHERE T1.`entities_id` = '$modelrgpd_id'";
				$DB->queryOrDie($query, $DB->error());
  }

// repérage de l'id de l'entité modele si elle existe, sinon exit
	$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
	$modelrgpd_id = 0;
	if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $modelrgpd_id = $data['id']; }
// var_dump($result,$DB->numrows($result), $modelrgpd_id, $project_id); die;

// STEP 2 : suppression des livrables existants, comptage et affichage
				$query = "SELECT COUNT(*) FROM glpi_plugin_dlteams_deliverables WHERE `entities_id` = '$modelrgpd_id'";
				$row = $DB->query($query)->fetch_assoc();
				$message .= strval($row["COUNT(*)"]) . " livrables précédents supprimés" . nl2br("\n") . "Ajouts : " . nl2br("\n");
		delete_form($modelrgpd_id);
		//header("Refresh:0; url=config.form.php");

// STEP 3 : on ajoute les oid
	$query = "ALTER TABLE `glpi_plugin_dlteams_deliverables` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
    $DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE `glpi_plugin_dlteams_deliverables_sections` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `deliverables_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
			$DB->queryOrDie($query, $DB->error());
					$query = "ALTER TABLE `glpi_plugin_dlteams_deliverables_contents` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `deliverable_sections_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
					$DB->queryOrDie($query, $DB->error());
// STEP 4 : import without id
	$tables = [
	'glpi_plugin_dlteams_deliverables',
	'glpi_plugin_dlteams_deliverables_sections',
	'glpi_plugin_dlteams_deliverables_contents',
	];
// `glpi_plugin_dlteams_deliverables` (id, entities_id, deliverables_id)
// `glpi_plugin_dlteams_deliverables_sections` (id, deliverables_id)
// `glpi_plugin_dlteams_deliverables_contents` (id, deliverable_sections_id)
	$fields_exports = [
	['glpi_plugin_dlteams_deliverables', '`oid`, `name`, `content`, `comment`, `document_name`, `document_title`, `document_content`, `document_comment`'],
	['glpi_plugin_dlteams_deliverables_sections', '`oid`, `deliverables_oid`, `name`, `tab_name`, `comment`, `content`, `deliverables_id`, `timeline_position`'],
	['glpi_plugin_dlteams_deliverables_contents', '`oid`, `deliverable_sections_oid`, `name`, `comment`, `content`, `timeline_position`'],
	];
	//var_dump($fields_exports[0]);
   foreach ($fields_exports as list($table, $fields_export)) {
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		// $file_pointer = "/var/www/dlteams_app/marketplace/dlteams/install/datas/" . $table . ".dat";
		$query = "LOAD DATA INFILE '".$file_pointer."' INTO TABLE ".$table." FIELDS TERMINATED BY '\t' ($fields_export)";
		//var_dump($query); die;
		$DB->queryOrDie($query, $DB->error());
		$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0";
		$row = $DB->query($query)->fetch_assoc();
// 	if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $modelrgpd_id = $data['id']; }
//var_dump ("Nombre d'éléments : " . strval($row["COUNT(*)"]));
	    $message .= $table . " : " . strval($row["COUNT(*)"]) . nl2br("\n") ; // strval($result); // . $table .  . "exportés";
   }

// STEP 5 : update des id
// `glpi_plugin_dlteams_deliverables` (id, entities_id, plugin_formcreator_categories_id)
	$query = "UPDATE `glpi_plugin_dlteams_deliverables` SET `entities_id` = '$modelrgpd_id' WHERE `oid` is not null ";
    $DB->queryOrDie($query, $DB->error());
			// `glpi_plugin_dlteams_deliverables_sections` (id, deliverables_id)
			$query = "UPDATE `glpi_plugin_dlteams_deliverables_sections` AS T2
					LEFT JOIN `glpi_plugin_dlteams_deliverables` AS T1 ON T2.`deliverables_oid` = T1.`oid` AND T1.`entities_id` = '$modelrgpd_id' 
					SET T2.`deliverables_id` = T1.`id`";
			$DB->queryOrDie($query, $DB->error());
					// `glpi_plugin_dlteams_deliverables_contents` (id, deliverable_sections_id)
					/*$query = "UPDATE `glpi_plugin_dlteams_deliverables_contents`AS T3
							LEFT JOIN `glpi_plugin_dlteams_deliverables_sections` AS T2 ON T3.`deliverable_sections_oid` = T2.`oid`
							LEFT JOIN `glpi_plugin_dlteams_deliverables` AS T1 ON T2.`deliverables_oid` = T1.`oid` AND T1.`entities_id` = '$modelrgpd_id'
							SET T3.`deliverable_sections_id` = T2.`id`";*/
					$query = "UPDATE `glpi_plugin_dlteams_deliverables_contents`AS T3
							LEFT JOIN `glpi_plugin_dlteams_deliverables_sections` AS T2 ON T3.`deliverable_sections_oid` = T2.`oid`
							SET T3.`deliverable_sections_id` = T2.`id`
							WHERE T2.`oid` <> 0";
					$DB->queryOrDie($query, $DB->error());

// STEP 6 Delete oid
   foreach ($tables as $table) {
		$query = "ALTER TABLE $table DROP IF EXISTS `oid`";
		$DB->queryOrDie($query, $DB->error());
		}
$message .= "Eléments bien importés depuis fichiers .dat";
Session::addMessageAfterRedirect($message);
header("Refresh:0; url=config.form.php");
