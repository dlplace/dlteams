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

// `glpi_plugin_formcreator_forms` (id, entities_id, plugin_formcreator_categories_id)
// `glpi_plugin_formcreator_sections` (id, plugin_formcreator_forms_id) 
// `glpi_plugin_formcreator_questions` (id, plugin_formcreator_sections_id)
// `glpi_plugin_formcreator_categories` (id)
// `glpi_plugin_formcreator_targettickets` (id, plugin_formcreator_forms_id, tickettemplates_id) --> onglet cible vers un ticket

/* `glpi_plugin_formcreator_conditions` (id, plugin_formcreator_questions_id) --> condition pour la réponse 		
// si itemtype = "PluginFormcreatorQuestion" -> "items_id" <-> (ex question 1540 : items_id 1548 pour réponse 2 & 1549 pour réponses 1,3,4  
// `glpi_plugin_formcreator_questions`.`id` = `glpi_plugin_formcreator_conditions`.`items_id` quand `glpi_plugin_formcreator_conditions`.`itemtype` = "PluginFormcreatorQuestion"
// `glpi_plugin_formcreator_???`.`id` = `glpi_plugin_formcreator_??? `.`items_id` quand `glpi_plugin_formcreator_conditions`.`itemtype` = "PluginFormcreatorSection"
// `glpi_plugin_formcreator_???`.`id` = `glpi_plugin_formcreator_??? `.`items_id` quand `glpi_plugin_formcreator_conditions`.`itemtype` = "PluginFormcreatorForm"
SELECT * from `glpi_plugin_formcreator_conditions` as t1
INNER JOIN `glpi_plugin_formcreator_questions` as t2
on t1.items_id = t2.id
INNER JOIN `glpi_plugin_formcreator_sections` as t3
on t2.plugin_formcreator_sections_id = t3.id
INNER JOIN `glpi_plugin_formcreator_forms` as t4
on t3.`plugin_formcreator_forms_id` = t4.id
WHERE t1.`itemtype` = "PluginFormcreatorQuestion"
AND  t4.`entities_id` = 32
*/

include("../../../inc/includes.php");
global $DB;

$message = "Export des formulaires de l'entité model-rgpd". nl2br("\n"); 
// On exporte les formulaires de rgpd-model
	// recup de l'id rgpd-model
	$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
	$modelrgpd_id = 0;
	if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $modelrgpd_id = $data['id']; }
	// recup de l'id du projet "Conformité RGPD"
	$result = $DB->query('SELECT * FROM `glpi_projects` WHERE entities_id = '.$modelrgpd_id.' and name = "Référentiel RGPD"');
	$project_id = 0;
	if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $project_id = $data['id']; }
	//var_dump($result,$DB->numrows($result), $modelrgpd_id, $project_id);

// STEP 1 : on ajoute les oid
	$query = "ALTER TABLE `glpi_plugin_formcreator_forms` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
    $DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_formcreator_forms` ADD IF NOT EXISTS `plugin_formcreator_categories_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
    $DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_formcreator_sections` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `plugin_formcreator_forms_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_formcreator_questions` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `plugin_formcreator_sections_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_formcreator_categories` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_formcreator_targettickets` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `plugin_formcreator_forms_oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `tickettemplates_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_formcreator_conditions` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `items_oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `plugin_formcreator_questions_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
	$DB->queryOrDie($query, $DB->error());

// STEP 2 : update des oid
// `glpi_plugin_formcreator_forms` (id, entities_id, plugin_formcreator_categories_id)
	$query = "UPDATE `glpi_plugin_formcreator_forms` SET `oid` = `id`, plugin_formcreator_categories_oid = plugin_formcreator_categories_id  WHERE `entities_id` = '$modelrgpd_id'";
    $DB->queryOrDie($query, $DB->error());
			// `glpi_plugin_formcreator_sections` (id, plugin_formcreator_forms_id) 
			$query = "UPDATE `glpi_plugin_formcreator_sections` AS T1
					INNER JOIN `glpi_plugin_formcreator_forms` AS T2 
					ON T1.`plugin_formcreator_forms_id` = T2.`id` AND T2.`entities_id` = '$modelrgpd_id' 
					SET T1.`oid` = T1.`id`, T1.`plugin_formcreator_forms_oid` = T1.`plugin_formcreator_forms_id`";
			$DB->queryOrDie($query, $DB->error());
					// `glpi_plugin_formcreator_questions` (id, plugin_formcreator_sections_id)
			$query = "UPDATE `glpi_plugin_formcreator_questions`AS T1
					INNER JOIN `glpi_plugin_formcreator_sections` AS T2
					ON T1.`plugin_formcreator_sections_id` = T2.`id`
					INNER JOIN `glpi_plugin_formcreator_forms` AS T3
					ON T2.`plugin_formcreator_forms_id` = T3.`id` AND T3.`entities_id` = '$modelrgpd_id'
					SET T1.`oid` = T1.`id`, T1.`plugin_formcreator_sections_oid` = T1.`plugin_formcreator_sections_id` ";
			$DB->queryOrDie($query, $DB->error());
				// `glpi_plugin_formcreator_categories` (id)
			$query = "UPDATE `glpi_plugin_formcreator_categories` AS T1
					INNER JOIN `glpi_plugin_formcreator_forms` AS T2 ON T1.`id` = T2.`plugin_formcreator_categories_id` AND T2.`entities_id` = '$modelrgpd_id'
					SET T1.`oid` = T1.`id`";
					$DB->queryOrDie($query, $DB->error());
			$query = "UPDATE `glpi_plugin_formcreator_targettickets` AS T1 
					INNER JOIN `glpi_plugin_formcreator_forms` AS T2 ON T1.`plugin_formcreator_forms_id` = T2.`id` AND T2.`entities_id` = '$modelrgpd_id'
					SET T1.`oid` = T1.`id`, T1.`plugin_formcreator_forms_oid` = T1.`plugin_formcreator_forms_id`";
			$DB->queryOrDie($query, $DB->error());
			$query = "UPDATE `glpi_plugin_formcreator_conditions` as T1
					INNER JOIN `glpi_plugin_formcreator_questions` as T2 on T1.items_id = T2.id
					INNER JOIN `glpi_plugin_formcreator_sections` as T3 on T2.plugin_formcreator_sections_id = T3.id
					INNER JOIN `glpi_plugin_formcreator_forms` as T4 on T3.`plugin_formcreator_forms_id` = T4.id
					SET T1.`oid` = T1.`id`, T1.`items_oid` = T1.`items_id`, T1.`plugin_formcreator_questions_oid` = T1.`plugin_formcreator_questions_id`
					WHERE T1.`itemtype` = 'PluginFormcreatorQuestion' AND  T4.`entities_id` = '$modelrgpd_id'";
			$DB->queryOrDie($query, $DB->error());

// STEP 3 : export without id
	$tables = [
	'glpi_plugin_formcreator_forms',
	'glpi_plugin_formcreator_sections',
	'glpi_plugin_formcreator_questions',
	'glpi_plugin_formcreator_categories',
	'glpi_plugin_formcreator_targettickets',
	'glpi_plugin_formcreator_conditions',
	];
// `glpi_plugin_formcreator_forms` (id, entities_id, plugin_formcreator_categories_id)
// `glpi_plugin_formcreator_sections` (id, plugin_formcreator_forms_id) 
// `glpi_plugin_formcreator_questions` (id, plugin_formcreator_sections_id)
// `glpi_plugin_formcreator_categories` (id)
// `glpi_plugin_formcreator_targettickets` (id, plugin_formcreator_forms_id, tickettemplates_id) 
	$fields_exports = [
	['glpi_plugin_formcreator_forms', '`name`, `entities_id`, `is_recursive`, `icon`, `icon_color`, `background_color`, `access_rights`, `description`, `content`, `is_active`, `language`, `helpdesk_home`, `is_deleted`, `validation_required`, `usage_count`, `is_default`, `is_captcha_enabled`, `show_rule`, `formanswer_name`, `is_visible`, `uuid`, `oid`, `plugin_formcreator_categories_oid`'],
	['glpi_plugin_formcreator_sections', '`name`, `plugin_formcreator_forms_id`, `order`, `show_rule`, `uuid`, `oid`, `plugin_formcreator_forms_oid`'],
	['glpi_plugin_formcreator_questions', '`name`, `plugin_formcreator_sections_id`, `fieldtype`, `required`, `show_empty`, `default_values`, `itemtype`, `values`, `description`, `row`, `col`, `width`, `show_rule`, `uuid`, `oid`, `plugin_formcreator_sections_oid`'],
	['glpi_plugin_formcreator_categories', '`name`, `comment`, `completename`, `plugin_formcreator_categories_id`, `level`, `sons_cache`, `ancestors_cache`, `knowbaseitemcategories_id`, `oid`'],
	['glpi_plugin_formcreator_targettickets', '`name`, `target_name`, `source_rule`, `source_question`, `type_rule`, `type_question`, `content`, `due_date_rule`, `due_date_question`, `due_date_value`, `due_date_period`, `urgency_rule`, `urgency_question`, `validation_followup`, `destination_entity`, `destination_entity_value`, `tag_type`, `tag_questions`, `tag_specifics`, `category_rule`, `category_question`, `associate_rule`, `associate_question`, `location_rule`, `location_question`, `commonitil_validation_rule`, `commonitil_validation_question`, `show_rule`, `sla_rule`, `sla_question_tto`, `sla_question_ttr`, `ola_rule`, `ola_question_tto`, `ola_question_ttr`, `uuid`, `oid`, `plugin_formcreator_forms_oid`, `tickettemplates_oid`'],
	['glpi_plugin_formcreator_conditions', '`oid`, `itemtype`, `items_oid`, `plugin_formcreator_questions_oid`, `show_condition`, `show_value`, `show_logic`, `order`, `uuid`'],
	];
	//var_dump($fields_exports[0]);
	$folder = plugin_dlteams_root . "/install/datas/";
	chmod($folder, 0777); // ouverture des droits d'écriture

	foreach ($fields_exports as list($table, $fields_export)) {
		// $file_pointer = "/var/www/test_dlteams_app/marketplace/dlteams/install/datas/" . $table . ".dat";
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat"; //dossier courant
		unlink($file_pointer);
		$query = "SELECT $fields_export FROM $table WHERE `oid` <> 0 INTO OUTFILE '".$file_pointer."' CHARACTER SET utf8mb4";
		$DB->queryOrDie($query, $DB->error());
		$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0"; 
		$result = $DB->query($query);
		$row = $result->fetch_assoc();
		// 	if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $modelrgpd_id = $data['id']; }
		// var_dump ("Nombre de projet : " . strval($row["COUNT(*)"]));
	    $message .= $table . " : " . strval($row["COUNT(*)"]) . nl2br("\n") ; // strval($result); // . $table .  . "exportés"; 
	}
	chmod($folder, 0755); // fermeture des droits
	
// STEP 4 Delete oid
	foreach ($tables as $table) {
		$query = "ALTER TABLE $table DROP IF EXISTS `oid`";
		$DB->queryOrDie($query, $DB->error());
		}
	$query = "ALTER TABLE `glpi_plugin_formcreator_forms` DROP IF EXISTS `plugin_formcreator_categories_oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_formcreator_sections` DROP IF EXISTS `plugin_formcreator_forms_oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_formcreator_targettickets` DROP IF EXISTS `plugin_formcreator_forms_oid`, DROP IF EXISTS `tickettemplates_oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_formcreator_conditions` DROP IF EXISTS `items_oid`, DROP IF EXISTS `plugin_formcreator_questions_oid`";
	$DB->queryOrDie($query, $DB->error());
		
$message .= "Fichiers .dat créés dans le dossier export";
Session::addMessageAfterRedirect($message);
header("Refresh:0; url=config.form.php");