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

include("../../../inc/includes.php");
global $DB;
$message = "Import du projet conformité RGPD" . nl2br("\n") ; 

  function delete_project($project_id) {
	global $DB;	   
		// `glpi_tickettasks` (id, uuid, tickets_id)
			$query = "DELETE T1 FROM `glpi_tickettasks`AS T1 INNER JOIN `glpi_tickets` AS T2
				ON T1.`tickets_id` =  T2.`id`
				INNER JOIN `glpi_projecttasks_tickets` AS T3
				ON T2.`id` = T3.`tickets_id`
				INNER JOIN `glpi_projecttasks` AS T4
				ON T3.`projecttasks_id` = T4.`id` AND T4.`projects_id` = '$project_id'";
				$DB->queryOrDie($query, $DB->error());
		// `glpi_tickets` (id, name, entities_id)
				$query = "DELETE T1 FROM `glpi_tickets`AS T1
				INNER JOIN `glpi_projecttasks_tickets` AS T2
				ON T1.`id` = T2.`tickets_id` AND T2.`projecttasks_id`
				INNER JOIN `glpi_projecttasks` AS T3
				ON T2.`projecttasks_id` = T3.`id` AND T3.`projects_id` = '$project_id'";
				$DB->queryOrDie($query, $DB->error());
		// `glpi_projecttasks_tickets` (id, uuid, name, entities_id, projects_id, projecttasks_id)
				$query = "DELETE T1 FROM `glpi_projecttasks_tickets` AS T1 
				INNER JOIN `glpi_projecttasks` AS T2
				ON T1.`projecttasks_id` = T2.`id` AND T2.`projects_id` = '$project_id'";
				$DB->queryOrDie($query, $DB->error());
		// `glpi_projecttasks` (id, uuid, name, entities_id, projects_id, projecttasks_id)
				$query = "DELETE FROM `glpi_projecttasks` 
				WHERE `projects_id` = '$project_id'";
				$DB->queryOrDie($query, $DB->error());
		// `glpi_projects` (id, name, enetities_id)
				$query = "DELETE FROM `glpi_projects` WHERE `id` = '$project_id'";
				$DB->queryOrDie($query, $DB->error());
		//	global $passage; var_dump($passage, $project_id); $passage = $passage+1; 	die;
		//Return true;
	}

// importation du projet vers l'entité modele
// STEP 1 : id de l'entité modele si elle existe, sinon exit
	$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
	$modelrgpd_id = 0;
	if ($result && $DB->numrows($result) > 0) { 
		$data = $DB->fetchAssoc($result); $modelrgpd_id = $data['id']; 
	} else {
	var_dump ("Il n'existe pas d'entité modèle");
	}	
/*
// STEP 2 : on supprime le projet existant si il existe
	$result = $DB->query('SELECT `id` FROM `glpi_projects` WHERE `name` = "Référentiel RGPD" and `entities_id` = ' . $modelrgpd_id);
	$project_id = 0;
	if ($result && $DB->numrows($result) > 0) {
		$data = $DB->fetchAssoc($result); $project_id = $data['id']; 
		delete_project($project_id);
		$message .= "Ancien projet effacé <br>";
	}
// var_dump ("Projet n° " . $project_id . " de l'entité n° " . $modelrgpd_id . " a été effacé") ; die ; 
	
// STEP 3 : on ajoute les oid
	$query = "ALTER TABLE `glpi_projects` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
    $DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE `glpi_projecttasks` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
			$DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE `glpi_projecttasks` ADD IF NOT EXISTS `projects_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
			$DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE `glpi_projecttasks` ADD IF NOT EXISTS `projecttasks_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
			$DB->queryOrDie($query, $DB->error());
				$query = "ALTER TABLE `glpi_projecttasks_tickets` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
				$DB->queryOrDie($query, $DB->error());
				$query = "ALTER TABLE `glpi_projecttasks_tickets` ADD IF NOT EXISTS `tickets_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
				$DB->queryOrDie($query, $DB->error());
				$query = "ALTER TABLE `glpi_projecttasks_tickets` ADD IF NOT EXISTS `projecttasks_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
				$DB->queryOrDie($query, $DB->error());
					$query = "ALTER TABLE `glpi_tickets` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
					$DB->queryOrDie($query, $DB->error());
						$query = "ALTER TABLE `glpi_tickettasks` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
						$DB->queryOrDie($query, $DB->error());
						$query = "ALTER TABLE `glpi_tickettasks` ADD IF NOT EXISTS `tickets_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
						$DB->queryOrDie($query, $DB->error());
	//Session::addMessageAfterRedirect("oid ok");
// STEP 4 : import without id
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
	['glpi_projects', '`name`, `code`, `priority`, `is_recursive`, `projects_id`, `projectstates_id`, `projecttypes_id`, `date`, `date_mod`, `plan_start_date`, `plan_end_date`, `real_start_date`, `real_end_date`, `percent_done`, `auto_percent_done`, `show_on_global_gantt`, `content`, `comment`, `is_deleted`, `date_creation`, `projecttemplates_id`, `is_template`, `template_name`, `oid`'],
	['glpi_projecttasks', '`uuid`, `name`, `content`, `comment`, `is_recursive`, `projecttasks_id`, `date_creation`, `date_mod`, `plan_start_date`, `plan_end_date`, `real_start_date`, `real_end_date`, `planned_duration`, `effective_duration`, `projectstates_id`, `projecttasktypes_id`, `percent_done`, `auto_percent_done`, `is_milestone`, `projecttasktemplates_id`, `is_template`, `template_name`, `oid`, `projects_oid`, `projecttasks_oid`'],
	['glpi_tickets', '`entities_id`, `name`, `date`, `closedate`, `solvedate`, `takeintoaccountdate`, `date_mod`, `status`, `requesttypes_id`, `content`, `urgency`, `impact`, `priority`, `itilcategories_id`, `type`, `global_validation`, `slas_id_ttr`, `slas_id_tto`, `slalevels_id_ttr`, `time_to_resolve`, `time_to_own`, `begin_waiting_date`, `sla_waiting_duration`, `ola_waiting_duration`, `olas_id_tto`, `olas_id_ttr`, `olalevels_id_ttr`, `ola_ttr_begin_date`, `internal_time_to_resolve`, `internal_time_to_own`, `waiting_duration`, `close_delay_stat`, `solve_delay_stat`, `takeintoaccount_delay_stat`, `actiontime`, `is_deleted`, `locations_id`, `validation_percent`, `date_creation`, `oid`'],
	['glpi_projecttasks_tickets', '`tickets_id`, `projecttasks_id`, `tickets_oid`, `projecttasks_oid`, `oid`'],
	['glpi_tickettasks', '`uuid`, `tickets_id`, `taskcategories_id`, `date`, `content`, `is_private`, `actiontime`, `begin`, `end`, `state`, `date_mod`, `date_creation`, `tasktemplates_id`, `timeline_position`, `sourceitems_id`, `sourceof_items_id`, `oid`, `tickets_oid`']
	];
	//var_dump($fields_exports[0]);
	// ajouter itil categfory et integer à ticket -> crrer une catégorie "Suivi d'un référentiel" + l'affecter à tous les tickets
	
   foreach ($fields_exports as list($table, $fields_export)) {
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		// $file_pointer = "/var/www/dlteams_app/marketplace/dlteams/install/datas/" . $table . ".dat"; 
		$query = "LOAD DATA INFILE '".$file_pointer."' IGNORE INTO TABLE ".$table." FIELDS TERMINATED BY '\t' ($fields_export)";
		//var_dump($query); die;
		$DB->queryOrDie($query, $DB->error());
   }
  // Session::addMessageAfterRedirect("fields ok");
  	var_dump ("import ok");die;
*/
  // STEP 4 : update relation id  
	// `glpi_projects` (id, name, enetities_id)
	$query = "UPDATE `glpi_projects` SET entities_id = $modelrgpd_id where `oid` <> 0";
    $DB->queryOrDie($query, $DB->error());
	$message .= "1 projet(s) importé(s) <br>";
	// `glpi_projecttasks` (id, uuid, name, entities_id, projects_id, projecttasks_id) // une tâche ne peut appartenir qu'à un seul projet
			$query = "UPDATE `glpi_projecttasks` AS T1 INNER JOIN `glpi_projects` AS T2  
						ON T1.`projects_oid` = T2.`oid` 
						SET T1.`entities_id` = $modelrgpd_id, T1.`projects_id` = T2.`id`";
			$DB->queryOrDie($query, $DB->error());
			$query = "UPDATE `glpi_projecttasks` AS T1 
							INNER JOIN `glpi_projecttasks` AS T2 ON T1.`projecttasks_oid` = T2.`oid` 
							SET T1.`projecttasks_id` = T2.`id`";
			$DB->queryOrDie($query, $DB->error());
			$query = "SELECT COUNT(*) FROM `glpi_projecttasks` WHERE `entities_id` = '$modelrgpd_id'";
			$row = $DB->query($query)->fetch_assoc();
			$message .= strval($row["COUNT(*)"]) . " sous-projets importées <br>";
					// `glpi_tickets` (id, name, entities_id)
					$query = "UPDATE `glpi_tickets`AS T1
								SET T1.`entities_id` = $modelrgpd_id
								WHERE T1.`oid` <> 0";
					$DB->queryOrDie($query, $DB->error());
					$query = "SELECT COUNT(*) FROM `glpi_tickets` WHERE `oid` <> 0"; 
					$row = $DB->query($query)->fetch_assoc();
					$message .= strval($row["COUNT(*)"]) . " événements importés <br>" ; 
						// `glpi_projecttasks_tickets` (tickets_id - projecttasks_id)
						$query = "UPDATE `glpi_projecttasks_tickets` AS T1 INNER JOIN `glpi_tickets` AS T2
								ON T1.`tickets_oid` = T2.`oid`
								SET T1.`tickets_id` = T2.`id`";
						$DB->queryOrDie($query, $DB->error());
						$query = "UPDATE `glpi_projecttasks_tickets` AS T1 INNER JOIN `glpi_projecttasks` AS T2
								ON T1.`projecttasks_oid` = T2.`oid`
								SET T1.`projecttasks_id` = T2.`id`";
						$DB->queryOrDie($query, $DB->error());
								// `glpi_tickettasks` (id, uuid, tickets_id)
								$query = "UPDATE `glpi_tickettasks`AS T1 INNER JOIN `glpi_tickets` AS T2
										ON T1.`tickets_oid` =  T2.`oid`
										SET T1.`tickets_id` = T2.`id`";
								$DB->queryOrDie($query, $DB->error());
							$query = "SELECT COUNT(*) FROM `glpi_tickettasks` WHERE `oid` <> 0"; 
							$row = $DB->query($query)->fetch_assoc();
							$message .= strval($row["COUNT(*)"]) . " tâches importées <br>" ; 

				// (glpi_tickets_users) (tickets_id - users_id - type)
		
/*
// STEP 4 Delete oid
   foreach ($tables as $table) {
		$query = "ALTER TABLE $table DROP IF EXISTS `oid`";
		$DB->queryOrDie($query, $DB->error());
		}
	$query = "ALTER TABLE `glpi_projecttasks` DROP IF EXISTS `projecttasks_oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_projecttasks` DROP IF EXISTS `projects_oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_projecttasks_tickets` DROP IF EXISTS `tickets_oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_projecttasks_tickets` DROP IF EXISTS `projecttasks_oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_tickettasks` DROP IF EXISTS `tickets_oid`";
	$DB->queryOrDie($query, $DB->error());
*/	
Session::addMessageAfterRedirect($message);
echo "<script>window.location.href='config.form.php';</script>";// revient sur la page