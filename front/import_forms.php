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

// Importation des formulaires dans l'entite rgpd-model

include("../../../inc/includes.php");
global $DB;

$message = "Import des formulaires dans l'entité courante". nl2br("\n");
$target_entities_id = $_POST["entities_id_target"]?? Session::getActiveEntity();

  function delete_form($target_entities_id) {
	global $DB;
		// `glpi_plugin_formcreator_targettickets`
				$query = "DELETE T1 FROM `glpi_plugin_formcreator_targettickets` AS T1 
				INNER JOIN `glpi_plugin_formcreator_forms` AS T2 ON T1.`plugin_formcreator_forms_id` =  T2.`id` AND T2.`entities_id` = '$target_entities_id'";
				$DB->queryOrDie($query, $DB->error());
		// `glpi_plugin_formcreator_categories`
				$query = "DELETE T1 FROM `glpi_plugin_formcreator_categories` AS T1 
				INNER JOIN `glpi_plugin_formcreator_forms` AS T2 ON T1.`id` =  T2.`plugin_formcreator_categories_id` AND T2.`entities_id` = '$target_entities_id'";
				$DB->queryOrDie($query, $DB->error());
		// `glpi_plugin_formcreator_questions`
				$query = "DELETE T1 FROM `glpi_plugin_formcreator_questions`AS T1
				INNER JOIN `glpi_plugin_formcreator_sections` AS T2 ON T1.`plugin_formcreator_sections_id` = T2.`id`
				INNER JOIN `glpi_plugin_formcreator_forms` AS T3 ON T2.`plugin_formcreator_forms_id` = T3.`id` AND T3.`entities_id` = '$target_entities_id'";
				$DB->queryOrDie($query, $DB->error());
		// `glpi_plugin_formcreator_sections`
				$query = "DELETE T1 FROM `glpi_plugin_formcreator_sections` AS T1 
				INNER JOIN `glpi_plugin_formcreator_forms` AS T2 ON T1.`plugin_formcreator_forms_id` = T2.`id` AND T2.`entities_id` = '$target_entities_id'";
				$DB->queryOrDie($query, $DB->error());
		// `glpi_plugin_formcreator_forms`
				$query = "DELETE T1 FROM `glpi_plugin_formcreator_forms` AS T1 
				WHERE T1.`entities_id` = '$target_entities_id'";
				$DB->queryOrDie($query, $DB->error());
  }

// STEP1 : id de l'entité modele si elle existe, sinon exit
	$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
	$modelrgpd_id = 0;
	if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $modelrgpd_id = $data['id']; }
// var_dump($result,$DB->numrows($result), $modelrgpd_id, $project_id); die;

// STEP 2 : on supprime les formulaires existants si ils existent
				$query = "SELECT COUNT(*) FROM glpi_plugin_formcreator_forms WHERE `entities_id` = '$target_entities_id'";
				$row = $DB->query($query)->fetch_assoc();
				$message .= strval($row["COUNT(*)"]) . " formulaires précédents supprimés" . nl2br("\n") . "Ajouts : " . nl2br("\n");
		delete_form($target_entities_id);
		//header("Refresh:0; url=config.form.php");

// STEP 3 : on ajoute les oid
	$query = "ALTER TABLE `glpi_plugin_formcreator_forms` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
    $DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_formcreator_forms` ADD IF NOT EXISTS `plugin_formcreator_categories_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
    $DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE `glpi_plugin_formcreator_sections` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
			$DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE `glpi_plugin_formcreator_sections` ADD IF NOT EXISTS `plugin_formcreator_forms_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
			$DB->queryOrDie($query, $DB->error());
				$query = "ALTER TABLE `glpi_plugin_formcreator_questions` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
				$DB->queryOrDie($query, $DB->error());
				$query = "ALTER TABLE `glpi_plugin_formcreator_questions` ADD IF NOT EXISTS `plugin_formcreator_sections_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
				$DB->queryOrDie($query, $DB->error());
					$query = "ALTER TABLE `glpi_plugin_formcreator_categories` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
					$DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE `glpi_plugin_formcreator_targettickets` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `plugin_formcreator_forms_oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `tickettemplates_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
			$DB->queryOrDie($query, $DB->error());

// STEP 4 : import without id
	$tables = [
	'glpi_plugin_formcreator_forms',
	'glpi_plugin_formcreator_sections',
	'glpi_plugin_formcreator_questions',
	'glpi_plugin_formcreator_categories',
	'glpi_plugin_formcreator_targettickets'
	];
// `glpi_plugin_formcreator_forms` (id, entities_id, plugin_formcreator_categories_id)
// `glpi_plugin_formcreator_sections` (id, plugin_formcreator_forms_id)
// `glpi_plugin_formcreator_questions` (id, plugin_formcreator_sections_id)
// `glpi_plugin_formcreator_categories` (id)
	$fields_exports = [
	['glpi_plugin_formcreator_forms', '`name`, `entities_id`, `is_recursive`, `icon`, `icon_color`, `background_color`, `access_rights`, `description`, `content`, `is_active`, `language`, `helpdesk_home`, `is_deleted`, `validation_required`, `usage_count`, `is_default`, `is_captcha_enabled`, `show_rule`, `formanswer_name`, `is_visible`, `uuid`, `oid`, `plugin_formcreator_categories_oid`'],
	['glpi_plugin_formcreator_sections', '`name`, `plugin_formcreator_forms_id`, `order`, `show_rule`, `uuid`, `oid`, `plugin_formcreator_forms_oid`'],
	['glpi_plugin_formcreator_questions', '`name`, `plugin_formcreator_sections_id`, `fieldtype`, `required`, `show_empty`, `default_values`, `itemtype`, `values`, `description`, `row`, `col`, `width`, `show_rule`, `uuid`, `oid`, `plugin_formcreator_sections_oid`'],
	['glpi_plugin_formcreator_categories', '`name`, `comment`, `completename`, `plugin_formcreator_categories_id`, `level`, `sons_cache`, `ancestors_cache`, `knowbaseitemcategories_id`, `oid`'],
	['glpi_plugin_formcreator_targettickets', '`name`, `target_name`, `source_rule`, `source_question`, `type_rule`, `type_question`, `content`, `due_date_rule`, `due_date_question`, `due_date_value`, `due_date_period`, `urgency_rule`, `urgency_question`, `validation_followup`, `destination_entity`, `destination_entity_value`, `tag_type`, `tag_questions`, `tag_specifics`, `category_rule`, `category_question`, `associate_rule`, `associate_question`, `location_rule`, `location_question`, `commonitil_validation_rule`, `commonitil_validation_question`, `show_rule`, `sla_rule`, `sla_question_tto`, `sla_question_ttr`, `ola_rule`, `ola_question_tto`, `ola_question_ttr`, `uuid`, `oid`, `plugin_formcreator_forms_oid`, `tickettemplates_oid`']
	];
	//var_dump($fields_exports[0]);
   foreach ($fields_exports as list($table, $fields_export)) {
		// $file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		$file_pointer = "/var/www/dlteams_app/marketplace/dlteams/install/datas/" . $table . ".dat";
		$query = "LOAD DATA INFILE '".$file_pointer."' INTO TABLE ".$table." FIELDS TERMINATED BY '\t' ($fields_export)";
		//var_dump($query); die;
		$DB->queryOrDie($query, $DB->error());
		$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0";
		$row = $DB->query($query)->fetch_assoc();
// 	if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $target_entities_id = $data['id']; }
// var_dump ("Nombre de projet : " . strval($row["COUNT(*)"]));
	    $message .= $table . " : " . strval($row["COUNT(*)"]) . nl2br("\n") ; // strval($result); // . $table .  . "exportés";
   }

// STEP 5 : update des oid
// `glpi_plugin_formcreator_forms` (id, entities_id, plugin_formcreator_categories_id)
	$query = "UPDATE `glpi_plugin_formcreator_forms` SET `entities_id` = '$target_entities_id' WHERE `oid` is not null ";
    $DB->queryOrDie($query, $DB->error());
			// `glpi_plugin_formcreator_targettickets`
			$query = "UPDATE `glpi_plugin_formcreator_targettickets` AS T1 
					 INNER JOIN `glpi_plugin_formcreator_forms` AS T2 ON T1.`plugin_formcreator_forms_oid` = T2.`oid` AND T2.`entities_id` = '$target_entities_id'
					 SET T1.`plugin_formcreator_forms_id` = T2.`id`";
			$DB->queryOrDie($query, $DB->error());
			// `glpi_plugin_formcreator_sections` (id, plugin_formcreator_forms_id)
			$query = "UPDATE `glpi_plugin_formcreator_sections` AS T1
					INNER JOIN `glpi_plugin_formcreator_forms` AS T2 ON T1.`plugin_formcreator_forms_oid` = T2.`oid` AND T2.`entities_id` = '$target_entities_id' 
					SET T1.`plugin_formcreator_forms_id` = T2.`id`";
			$DB->queryOrDie($query, $DB->error());
					// `glpi_plugin_formcreator_questions` (id, plugin_formcreator_sections_id)
					$query = "UPDATE `glpi_plugin_formcreator_questions`AS T1
							INNER JOIN `glpi_plugin_formcreator_sections` AS T2 ON T1.`plugin_formcreator_sections_oid` = T2.`oid`
							INNER JOIN `glpi_plugin_formcreator_forms` AS T3 ON T2.`plugin_formcreator_forms_oid` = T3.`oid` AND T3.`entities_id` = '$target_entities_id'
							SET T1.`plugin_formcreator_sections_id` = T2.`id`";
					$DB->queryOrDie($query, $DB->error());
						// `glpi_plugin_forms`
						$query = "UPDATE `glpi_plugin_formcreator_forms` AS T1
								INNER JOIN `glpi_plugin_formcreator_categories` AS T2 ON T1.`plugin_formcreator_categories_oid` = T2.`oid`
								SET T1.`plugin_formcreator_categories_id` = T2.`id`";
					$DB->queryOrDie($query, $DB->error());

// STEP 6 Delete oid
   foreach ($tables as $table) {
		$query = "ALTER TABLE $table DROP IF EXISTS `oid`";
		$DB->queryOrDie($query, $DB->error());
		}
	$query = "ALTER TABLE `glpi_plugin_formcreator_forms` DROP IF EXISTS `plugin_formcreator_categories_oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_formcreator_sections` DROP IF EXISTS `plugin_formcreator_forms_oid`";
	$DB->queryOrDie($query, $DB->error());


$message .= "Fichiers .dat créés dans le dossier export";
Session::addMessageAfterRedirect($message);
header("Refresh:0; url=config.form.php");
