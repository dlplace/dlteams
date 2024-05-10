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

$message = "Export de la base de connaisance l'entité model-rgpd". nl2br("\n") . "Enregistrements exportés : <br>" ; 
// On exporte les formulaires de rgpd-model
	// recup de l'id rgpd-model
	$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
	$modelrgpd_id = 0;
	// if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $modelrgpd_id = $data['id']; } //la base + les catégories sont en root sans quoi on ne peut diffuser....0

	$tables = [
	'glpi_entities_knowbaseitems',
	'glpi_knowbaseitems',
	'glpi_knowbaseitemcategories',
	'glpi_knowbaseitems_knowbaseitemcategories'
	];

// STEP 1 : on ajoute les oid
	$query = "ALTER TABLE `glpi_entities_knowbaseitems` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `knowbaseitems_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
    $DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_knowbaseitems` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
    $DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_knowbaseitemcategories` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `knowbaseitemcategories_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_knowbaseitems_knowbaseitemcategories` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `knowbaseitems_oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `knowbaseitemcategories_oid` INT UNSIGNED NOT NULL DEFAULT '0'";
	$DB->queryOrDie($query, $DB->error());
	//$query = "ALTER TABLE `glpi_knowbaseitems_items` ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0', ADD IF NOT EXISTS `knowbaseitems_oid` INT UNSIGNED NOT NULL DEFAULT '0'";

// STEP 2 : update des oid
	// `glpi_entities_knowbaseitems`
	$query = "UPDATE `glpi_entities_knowbaseitems` SET `oid` = `id`, `knowbaseitems_oid` = `knowbaseitems_id` WHERE `entities_id` = '$modelrgpd_id'";
    $DB->queryOrDie($query, $DB->error());
	// `glpi_knowbaseitems` 
	$query = "UPDATE `glpi_knowbaseitems` AS T1 
	INNER JOIN `glpi_entities_knowbaseitems` AS T2 ON T2.`knowbaseitems_id` = T1.`id` AND T2.`entities_id` = '$modelrgpd_id'
	SET T1.`oid` = T1.`id`";
    $DB->queryOrDie($query, $DB->error());
	// `glpi_knowbaseitemcategories` 
	/*$query = "UPDATE `glpi_knowbaseitemcategories` AS T1
			INNER JOIN `glpi_knowbaseitems_knowbaseitemcategories` AS T2 ON T2.`knowbaseitemcategories_id` = T1.`id`
			INNER JOIN `glpi_knowbaseitems` AS T3 ON T3.`id` = T2.`knowbaseitems_id` 
			SET T1.`oid` = T1.`id`, T1.`knowbaseitemcategories_oid` = T1.`knowbaseitemcategories_id`";*/
	$query = "UPDATE `glpi_knowbaseitemcategories` AS T1 
			SET T1.`oid` = T1.`id`, T1.`knowbaseitemcategories_oid` = T1.`knowbaseitemcategories_id`
			WHERE T1.`entities_id` = '$modelrgpd_id'";

	$DB->queryOrDie($query, $DB->error());
	// `glpi_knowbaseitems_knowbaseitemcategories` (id, plugin_formcreator_forms_id) 
	$query = "UPDATE `glpi_knowbaseitems_knowbaseitemcategories` AS T1
			INNER JOIN `glpi_knowbaseitems` AS T2 ON T2.`id` = T1.`knowbaseitems_id` AND T2.`oid` <> 0 
			INNER JOIN `glpi_knowbaseitemcategories` AS T3 ON T3.`id` = T1.`knowbaseitemcategories_id` 
			SET T1.`oid` = T1.`id`, T1.`knowbaseitems_oid` = T1.`knowbaseitems_id`, T1.`knowbaseitemcategories_oid` = T1.`knowbaseitemcategories_id`";
	$DB->queryOrDie($query, $DB->error());

// STEP 3 : export without id
	$fields_exports = [
	['glpi_entities_knowbaseitems', '`oid`, `knowbaseitems_oid`, `knowbaseitems_oid`'],
	['glpi_knowbaseitems','`name`, `answer`, `is_faq`, `view`, `date_creation`, `begin_date`, `end_date`, `oid`'],
	['glpi_knowbaseitemcategories', '`is_recursive`, `name`, `completename`, `comment`, `level`, `sons_cache`, `ancestors_cache`, `date_creation`, `oid`, `knowbaseitemcategories_oid`'],
	['glpi_knowbaseitems_knowbaseitemcategories', '`oid`, `knowbaseitems_oid`, `knowbaseitemcategories_oid`'],
	];
	//var_dump($fields_exports[0]);
   foreach ($fields_exports as list($table, $fields_export)) {
		$file_pointer = "/var/www/test_dlteams_app/marketplace/dlteams/install/datas/" . $table . ".dat";
		// $file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat"; //dossier courant
		unlink($file_pointer);
		$query = "SELECT $fields_export FROM $table WHERE `oid` <> 0 INTO OUTFILE '".$file_pointer."' CHARACTER SET utf8mb4";
		$DB->queryOrDie($query, $DB->error());
			$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0"; 
			$row = $DB->query($query)->fetch_assoc();
			$message .= $table . " : " . strval($row["COUNT(*)"]) . nl2br("\n") ; 
   }

// STEP 4 Delete oid
   foreach ($tables as $table) {
		$query = "ALTER TABLE $table DROP IF EXISTS `oid`";
		$DB->queryOrDie($query, $DB->error());
		}
	$query = "ALTER TABLE `glpi_entities_knowbaseitems` DROP IF EXISTS `knowbaseitems_oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_knowbaseitemcategories` DROP IF EXISTS `knowbaseitemcategories_oid`";
	$DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_knowbaseitems_knowbaseitemcategories` DROP IF EXISTS `knowbaseitems_oid`, DROP IF EXISTS `knowbaseitemcategories_oid`";
	$DB->queryOrDie($query, $DB->error());
		
$message .= "Fichiers .dat créés dans le dossier export";
Session::addMessageAfterRedirect($message);
header("Refresh:0; url=config.form.php");