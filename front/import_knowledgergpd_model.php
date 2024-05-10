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

// `glpi_knowbaseitems` (id, users_id) -> les articles
// `glpi_entities_knowbaseitems` (id, knowbaseitems_id, entities_id) -> la publication dans les entités (ici rgpd-model)
// `glpi_knowbaseitemcategories` (id, entities_id, knowbaseitemcategories_id) -> catégorie d'articles
// `glpi_knowbaseitems_knowbaseitemcategories` (id, knowbaseitems_id, knowbaseitemcategories_id)-> relations articles <-> catégories d'articles
// `glpi_knowbaseitems_items` (id, knowbaseitems_id, item_type, items_id)  -> relations avec les objets
// 'glpi_knowbaseitems_comments' -> véritable fil de discussion (il manque juste le nom du user + le cadre devrait être HTML)

include("../../../inc/includes.php");
global $DB;

$DB->request("SET @currententity_id := ".Session::getActiveEntity());
$DB->request("SET @currentuser_id := ".Session::getLoginUserID());
$message = "Import de la base de connaissance" . nl2br("\n") ; 

	// recup de l'id rgpd-model
	$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
	$modelrgpd_id = 0;
	if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $modelrgpd_id = $data['id']; }

	$tables = [
	'glpi_entities_knowbaseitems',
	'glpi_knowbaseitems',
	'glpi_knowbaseitemcategories',
	'glpi_knowbaseitems_knowbaseitemcategories'
	];

// STEP 1 : on supprime la base de connaissance présente
	$DB->request("SET @modelrgpd_id := ".$modelrgpd_id);
		$query = "SELECT COUNT(*) FROM `glpi_knowbaseitems` AS T1 
		INNER JOIN `glpi_entities_knowbaseitems` AS T2 ON T2.`knowbaseitems_id` = T1.`id` AND T2.`entities_id` = '$modelrgpd_id'";
		$row = $DB->query($query)->fetch_assoc();
		$message .= strval($row["COUNT(*)"]) . " articles précédents supprimés <br>" . "Ajouts : " . nl2br("\n");
	$querys = ['DELETE FROM `glpi_entities_knowbaseitems` WHERE EXISTS (SELECT 1 FROM `glpi_knowbaseitems` AS t2 WHERE t2.`entities_id` = '.$modelrgpd_id.' AND t2.`id` =`knowbaseitems_id`)',
	'DELETE T1 FROM `glpi_knowbaseitems_knowbaseitemcategories` AS T1 INNER JOIN `glpi_knowbaseitems` AS T2 ON T2.`id` = T1.`knowbaseitems_id` INNER JOIN `glpi_knowbaseitemcategories` AS T3 ON T3.`id` = T1.`knowbaseitemcategories_id`',
	'DELETE T1 FROM `glpi_knowbaseitems` AS T1 WHERE `entities_id` = '. $modelrgpd_id,
	'DELETE FROM `glpi_knowbaseitemcategories` WHERE `entities_id` = ' . $modelrgpd_id,];
	foreach ($querys as $query) {
		$DB->queryOrDie($query, $DB->error());
    }
	// réduction de l'auto_increment
	$tables = ['glpi_knowbaseitems', 'glpi_knowbaseitemcategories', 'glpi_knowbaseitems_knowbaseitemcategories', 'glpi_entities_knowbaseitems'];
	foreach ($tables as $table) {
		$result = $DB->query("SELECT MAX(`id`)+1 FROM $table"); 
		if ($result && $DB->numrows($result) > 0) {
			$data = $DB->fetchAssoc($result);
			$last_id = $data['MAX(`id`)+1']; // print_r ("<br>". $data['MAX(`id`)+1']);
			if ($last_id <> NULL) {$result = $DB->query("ALTER TABLE $table AUTO_INCREMENT = $last_id");} else {$result = $DB->query("ALTER TABLE $table AUTO_INCREMENT = 1");}
		}
	}

// STEP 2 : on ajoute les oid
$querys = [
'ALTER TABLE `glpi_entities_knowbaseitems` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT 0, ADD IF NOT EXISTS `knowbaseitems_oid` INT UNSIGNED NOT NULL DEFAULT 0',
'ALTER TABLE `glpi_knowbaseitems` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT 0',
'ALTER TABLE `glpi_knowbaseitemcategories` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT 0, ADD IF NOT EXISTS `knowbaseitemcategories_oid` INT UNSIGNED NOT NULL DEFAULT 0',
'ALTER TABLE `glpi_knowbaseitems_knowbaseitemcategories` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT 0, ADD IF NOT EXISTS `knowbaseitems_oid` INT UNSIGNED NOT NULL DEFAULT 0, ADD IF NOT EXISTS `knowbaseitemcategories_oid` INT UNSIGNED NOT NULL DEFAULT 0'
];
	foreach ($querys as $query) {
		$DB->queryOrDie($query, $DB->error());
    }

// STEP 3 : import without id
	$fields_exports = [
	['glpi_entities_knowbaseitems', '`oid`, `knowbaseitems_oid`, `knowbaseitems_oid`'],
	['glpi_knowbaseitems','`name`, `answer`, `is_faq`, `view`, `date_creation`, `begin_date`, `end_date`, `oid`'],
	['glpi_knowbaseitemcategories', '`is_recursive`, `name`, `completename`, `comment`, `level`, `sons_cache`, `ancestors_cache`, `date_creation`, `oid`, `knowbaseitemcategories_oid`'],
	['glpi_knowbaseitems_knowbaseitemcategories', '`oid`, `knowbaseitems_oid`, `knowbaseitemcategories_oid`'],
	];
	//var_dump($fields_exports[0]);
   foreach ($fields_exports as list($table, $fields_export)) {
		$glpiRoot=str_replace('\\', '/', GLPI_ROOT); 
		// pour tests, on prend le dossier de prod
		// $file_pointer = "/var/www/dlteams_app/marketplace/dlteams/install/datas/" . $table . ".dat";
		// si import d'une entité files/_plugins/dlteams/
		// $file_pointer = $glpiRoot. "/files/_plugins/" . "dlteams"."/" . $table . ".dat";
		// si model : dossier install/datas ; 
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		$query = "LOAD DATA INFILE '".$file_pointer."' IGNORE INTO TABLE ".$table." FIELDS TERMINATED BY '\t' ($fields_export)";
		$DB->queryOrDie($query, $DB->error());
			$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0"; 
			$row = $DB->query($query)->fetch_assoc();
			$message .= $table . " : " . strval($row["COUNT(*)"]) . nl2br("\n") ; 
   }

// STEP 4 : update relation id
	// `glpi_knowbaseitems` 
	$query = "UPDATE `glpi_knowbaseitems` SET `entities_id` = '$modelrgpd_id' WHERE `oid` <> 0";
    $DB->queryOrDie($query, $DB->error());	
	// `glpi_entities_knowbaseitems`
	$query = "UPDATE `glpi_entities_knowbaseitems` AS T1 INNER JOIN `glpi_knowbaseitems` AS T2 ON T1.`knowbaseitems_oid` = T2.`oid`
	SET T1.`knowbaseitems_id` = T2.`id`, T1.`entities_id` = 0, T1.is_recursive = 1";
    $DB->queryOrDie($query, $DB->error());
	// `glpi_knowbaseitemcategories` 
	$query = "UPDATE `glpi_knowbaseitemcategories` SET `entities_id` = '$modelrgpd_id' WHERE `oid` <> 0";
    $DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_knowbaseitemcategories` AS T1 INNER JOIN `glpi_knowbaseitemcategories` AS T2 ON T1.`knowbaseitemcategories_oid` = T2.`oid`
			SET T1.`knowbaseitemcategories_id` = T2.`id`";
	$DB->queryOrDie($query, $DB->error());
	// `glpi_knowbaseitems_knowbaseitemcategories` 
	$query = "UPDATE `glpi_knowbaseitems_knowbaseitemcategories` AS T1
			INNER JOIN `glpi_knowbaseitems` AS T2 ON T2.`oid` = T1.`knowbaseitems_oid`
			SET T1.`knowbaseitems_id` = T2.`id`";
	$DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_knowbaseitems_knowbaseitemcategories` AS T1
			INNER JOIN `glpi_knowbaseitemcategories` AS T2 ON T2.`oid` = T1.`knowbaseitemcategories_oid` 
			SET T1.`knowbaseitemcategories_id` = T2.`id`";
	$DB->queryOrDie($query, $DB->error());

// STEP 4 Delete oid
/*   foreach ($tables as $table) {
		$query = "ALTER TABLE $table DROP IF EXISTS `oid`";
		$DB->queryOrDie($query, $DB->error());
		}
	$query = "ALTER TABLE `glpi_entities_knowbaseitems` DROP IF EXISTS `knowbaseitems_oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_knowbaseitemcategories` DROP IF EXISTS `knowbaseitemcategories_oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_knowbaseitems_knowbaseitemcategories` DROP IF EXISTS `knowbaseitems_oid`, DROP IF EXISTS `knowbaseitemcategories_oid`";
	$DB->queryOrDie($query, $DB->error());*/
		
$message .= "Importation effectuée";
Session::addMessageAfterRedirect($message);
	echo "<script>window.location.href='config.form.php';</script>";// revient sur la page