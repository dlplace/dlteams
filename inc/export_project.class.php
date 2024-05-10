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

// `glpi_projects` (id, name, enetities_id)
// `glpi_projecttasks` (id, uuid, name, entities_id, projects_id) // une tâche ne peut appartenir qu'à un seul projet
// `glpi_projecttasks_tickets` (tickets_id - projecttasks_id)
// `glpi_tickets` (id, name, entities_id)
// (glpi_tickets_users) (tickets_id - users_id - type)
// `glpi_tickettasks` (id, uuid, tickets_id)

// l'utilisateur selectionne son projet
$project_id = "select projet indiqué par l'utilisateur"; 

// STEP 1 : on ajoute les oid
	$query = "ALTER TABLE `glpi_projects` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'"
    $DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE `glpi_projecttasks` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'"
			$DB->queryOrDie($query, $DB->error());
				$query = "ALTER TABLE `glpi_projecttasks_tickets` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'"
				$DB->queryOrDie($query, $DB->error());
				$query = "ALTER TABLE `glpi_projecttasks_tickets` ADD IF NOT EXISTS `projecttasks_oid` INT UNSIGNED NOT NULL DEFAULT '0'"
				$DB->queryOrDie($query, $DB->error());
					$query = "ALTER TABLE `glpi_tickets` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'"
					$DB->queryOrDie($query, $DB->error());
						$query = "ALTER TABLE `glpi_tickettasks` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'"
						$DB->queryOrDie($query, $DB->error());
						$query = "ALTER TABLE `glpi_tickettasks` ADD IF NOT EXISTS `tickets_oid` INT UNSIGNED NOT NULL DEFAULT '0'"
						$DB->queryOrDie($query, $DB->error());

// STEP 2 : update des oid
	$query = "UPDATE `glpi_projects` SET `oid` = `id` WHERE `id` = '$project_id'"
    $DB->queryOrDie($query, $DB->error());
			$query = "UPDATE `glpi_projecttasks` SET `oid` = `id` WHERE `projects_id` = '$project_id'";
			$DB->queryOrDie($query, $DB->error());
				$query = "UPDATE `glpi_projecttasks_tickets` AS T1 LEFT JOIN `glpi_projecttasks` AS T2
							ON T1.`projecttasks_id` = T2.`id` AND T2.`projects_id` = 1
							SET T1.`oid` = T1.`tickets_id`, T1.`projecttasks_oid` = T1.`projecttasks_id`";
				$DB->queryOrDie($query, $DB->error());
					$query = "UPDATE `glpi_tickets`AS T1
								LEFT JOIN `glpi_projecttasks_tickets` AS T2
								ON T1.`id` = T2.`tickets_id` AND T2.`projecttasks_id`
								LEFT JOIN `glpi_projecttasks` AS T3
								ON T2.`projecttasks_id` = T3.`id` AND T3.`projects_id` = 1
								SET T1.`oid` = T1.`id`";
					$DB->queryOrDie($query, $DB->error());
						$query = "UPDATE `glpi_tickettasks`AS T1
								LEFT JOIN `glpi_tickets` AS T2
								ON T1.`tickets_id` =  T2.`id`
								LEFT JOIN `glpi_projecttasks_tickets` AS T3
								ON T2.`id` = T3.`tickets_id`
								LEFT JOIN `glpi_projecttasks` AS T4
								ON T3.`projecttasks_id` = T4.`id` AND T4.`projects_id` = 1
								SET T1.`oid` = T1.`id`, T1.`tickets_oid` = T1.`tickets_id`";
						$DB->queryOrDie($query, $DB->error());
// STEP 3 : export without id
   $tables = [
'glpi_projects',
'glpi_projecttasks',
'glpi_projecttasks_tickets',
'glpi_tickets',
'glpi_tickettasks'
];
   // 'glpi_projects' : no id, entities_id, users_id, groups_id
   // 'glpi_projecttasks' : no id, entities_id, projects_id, users_id
   // 'glpi_projecttasks_tickets' : no id, 
   $fields_exports = [
'`name`, `code`, `priority`, `is_recursive`, `projects_id`, `projectstates_id`, `projecttypes_id`, `date`, `date_mod`, `plan_start_date`, `plan_end_date`, `real_start_date`, `real_end_date`, `percent_done`, `auto_percent_done`, `show_on_global_gantt`, `content`, `comment`, `is_deleted`, `date_creation`, `projecttemplates_id`, `is_template`, `template_name`, `oid`',
'`uuid`, `name`, `content`, `comment`, `is_recursive`, `projecttasks_id`, `date_creation`, `date_mod`, `plan_start_date`, `plan_end_date`, `real_start_date`, `real_end_date`, `planned_duration`, `effective_duration`, `projectstates_id`, `projecttasktypes_id`, `percent_done`, `auto_percent_done`, `is_milestone`, `projecttasktemplates_id`, `is_template`, `template_name`, `oid`',
'`tickets_id`, `projecttasks_id`, `oid`, `projecttasks_oid`',
'`entities_id`, `name`, `date`, `closedate`, `solvedate`, `takeintoaccountdate`, `date_mod`, `users_id_lastupdater`, `status`, `users_id_recipient`, `requesttypes_id`, `content`, `urgency`, `impact`, `priority`, `itilcategories_id`, `type`, `global_validation`, `slas_id_ttr`, `slas_id_tto`, `slalevels_id_ttr`, `time_to_resolve`, `time_to_own`, `begin_waiting_date`, `sla_waiting_duration`, `ola_waiting_duration`, `olas_id_tto`, `olas_id_ttr`, `olalevels_id_ttr`, `ola_ttr_begin_date`, `internal_time_to_resolve`, `internal_time_to_own`, `waiting_duration`, `close_delay_stat`, `solve_delay_stat`, `takeintoaccount_delay_stat`, `actiontime`, `is_deleted`, `locations_id`, `validation_percent`, `date_creation`, `oid`',
'`uuid`, `tickets_id`, `taskcategories_id`, `date`, `users_id`, `users_id_editor`, `content`, `is_private`, `actiontime`, `begin`, `end`, `state`, `users_id_tech`, `groups_id_tech`, `date_mod`, `date_creation`, `tasktemplates_id`, `timeline_position`, `sourceitems_id`, `sourceof_items_id`, `oid`, `tickets_oid`'
];
   foreach ($tables as $table) {
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		unlink($file_pointer);
		$DB->queryOrDie("SELECT ".$fields_export." FROM `".$table."` WHERE `oid` IS NOT NULL INTO OUTFILE '".plugin_dlteams_root."/install/datas/".$table.".dat' CHARACTER SET utf8mb4");
		Session::addMessageAfterRedirect($table);
   }
// STEP 4 : import
   $tables = [
'glpi_projects',
'glpi_projecttasks',
'glpi_projecttasks_tickets',
'glpi_tickets',
'glpi_tickettasks'
];
	foreach ($tables as $table) {
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		// import datas from files in plugin/install/datas/
		$DB->queryOrDie("LOAD DATA INFILE '".$file_pointer."' INTO TABLE `".$table."` FIELDS TERMINATED BY '\t' (".$fields_export.")";
		Session::addMessageAfterRedirect($table);
	}
// STEP 4 : import






// on supprime les oid
	$query = "ALTER TABLE `glpi_projects` DROP IF EXISTS `oid`"
    $DB->queryOrDie($query, $DB->error());
