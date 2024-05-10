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

include("../../../inc/includes.php");
global $DB;

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
			$query = "ALTER TABLE `glpi_plugin_formcreator_sections` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
			$DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE `glpi_plugin_formcreator_sections` ADD IF NOT EXISTS `plugin_formcreator_forms_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
			$DB->queryOrDie($query, $DB->error());
				$query = "ALTER TABLE `glpi_plugin_formcreator_questions` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
				$DB->queryOrDie($query, $DB->error());
				$query = "ALTER TABLE `glpi_plugin_formcreator_questions` ADD IF NOT EXISTS `plugin_formcreator_sections_id` INT UNSIGNED NOT NULL DEFAULT '0'";
				$DB->queryOrDie($query, $DB->error());
					$query = "ALTER TABLE `glpi_plugin_formcreator_categories` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
					$DB->queryOrDie($query, $DB->error());

// STEP 2 : update des oid
// `glpi_plugin_formcreator_forms` (id, entities_id, plugin_formcreator_categories_id)
	$query = "UPDATE `glpi_plugin_formcreator_forms` SET `oid` = `id`, plugin_formcreator_categories_oid = plugin_formcreator_categories_id  WHERE `entities_id` = '$modelrgpd_id'";
    $DB->queryOrDie($query, $DB->error());
			// `glpi_plugin_formcreator_sections` (id, plugin_formcreator_forms_id) 
			$query = "UPDATE `glpi_plugin_formcreator_sections` AS T1
					INNER JOIN `glpi_plugin_formcreator_forms` AS T2 
					ON T1.plugin_formcreator_forms_id = T2.id AND T2.`entities_id` = '$modelrgpd_id' 
					SET `oid` = `id`, `plugin_formcreator_forms_oid` = `plugin_formcreator_forms_id`";
			$DB->queryOrDie($query, $DB->error());
					// `glpi_plugin_formcreator_questions` (id, plugin_formcreator_sections_id)
					$query = "UPDATE `glpi_plugin_formcreator_questions`AS T1
							INNER JOIN `glpi_plugin_formcreator_sections` AS T2
							ON T1.`plugin_formcreator_sections_id` = T2.`id`
							INNER JOIN `glpi_plugin_formcreator_forms` AS T3
							ON T2.`plugin_formcreator_forms_id` = T3.`id` AND T3.`entities_id` = '$modelrgpd_id'
							SET T1.`oid` = T1.`id`, T1.plugin_formcreator_sections_oid = T1.plugin_formcreator_sections_id ";
					$DB->queryOrDie($query, $DB->error());
						// `glpi_plugin_formcreator_categories` (id)
						$query = "UPDATE `glpi_plugin_formcreator_categories` AS T1
							SET T1.`oid` = T1.`id`";

// STEP 3 : export without id
	$tables = [
	'glpi_plugin_formcreator_forms',
	'glpi_plugin_formcreator_sections',
	'glpi_plugin_formcreator_questions',
	'glpi_plugin_formcreator_categories',
	];
// `glpi_plugin_formcreator_forms` (id, entities_id, plugin_formcreator_categories_id)
// `glpi_plugin_formcreator_sections` (id, plugin_formcreator_forms_id) 
// `glpi_plugin_formcreator_questions` (id, plugin_formcreator_sections_id)
// `glpi_plugin_formcreator_categories` (id)
	$fields_exports = [
	['glpi_plugin_formcreator_forms', '`name`, `entities_id`, `is_recursive`, `icon`, `icon_color`, `background_color`, `access_rights`, `description`, `content`, `plugin_formcreator_categories_id`, `is_active`, `language`, `helpdesk_home`, `is_deleted`, `validation_required`, `usage_count`, `is_default`, `is_captcha_enabled`, `show_rule`, `formanswer_name`, `is_visible`, `uuid`, `oid`, `plugin_formcreator_categories_oid`'],
	['glpi_plugin_formcreator_sections', '`name`, `plugin_formcreator_forms_id`, `order`, `show_rule`, `uuid`, `oid`, `plugin_formcreator_forms_oid`'],
	['glpi_plugin_formcreator_questions', '`name`, `plugin_formcreator_sections_id`, `fieldtype`, `required`, `show_empty`, `default_values`, `itemtype`, `values`, `description`, `row`, `col`, `width`, `show_rule`, `uuid`, `oid`, `plugin_formcreator_sections_oid`'],
	['glpi_plugin_formcreator_categories', '`name`, `comment`, `completename`, `plugin_formcreator_categories_id`, `level`, `sons_cache`, `ancestors_cache`, `knowbaseitemcategories_id`, `oid`'],
	];
	//var_dump($fields_exports[0]);
   foreach ($fields_exports as list($table, $fields_export)) {
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		unlink($file_pointer);
		$query = "SELECT $fields_export FROM $table WHERE `oid` <> 0 INTO OUTFILE '".plugin_dlteams_root."/install/datas/".$table.".dat' CHARACTER SET utf8mb4";
		$DB->queryOrDie($query, $DB->error());
		//Session::addMessageAfterRedirect($table);
   }
// STEP 4 Delete oid
   foreach ($tables as $table) {
		$query = "ALTER TABLE $table DROP IF EXISTS `oid`";
		$DB->queryOrDie($query, $DB->error());
		}
	$query = "ALTER TABLE `glpi_plugin_formcreator_forms` DROP IF EXISTS `plugin_formcreator_categories_oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_formcreator_sections` DROP IF EXISTS `plugin_formcreator_forms_oid`";
	$DB->queryOrDie($query, $DB->error());
Session::addMessageAfterRedirect("Questionnaires exportés");
header("Refresh:0; url=config.form.php");