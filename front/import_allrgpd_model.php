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
global $DB, $message, $modelrgpd_id;

$DB->request("SET @currententity_id := ".Session::getActiveEntity());
$DB->request("SET @currentuser_id := ".Session::getLoginUserID());
$message = "Import des traitements et éléments reliés" . nl2br("\n");

	// récupé&ration de l'id model-rgpd
	$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
    if ($result && $DB->numrows($result) > 0) {
        $data = $DB->fetchAssoc($result);
        $modelrgpd_id = $data['id'];
    }

	/*$DB->request("SET @modelrgpd_id := ".$modelrgpd_id);
	$query = "SELECT COUNT(*) FROM `glpi_plugin_dlteams_records` WHERE `entities_id` = '$modelrgpd_id'";
	$row = $DB->query($query)->fetch_assoc();
	$message .= "Pour l'entité <br>" . $modelrgpd_id . nl2br("\n");
	$message .= strval($row["COUNT(*)"]) . " traitements précédents supprimés <br>" . "Ajouts : " . nl2br("\n");*/

		$fields_exports = [
		['glpi_plugin_dlteams_records','`is_recursive`, `date_creation`, `number`, `name`, `content`, `additional_info`, `states_id`, `first_entry_date`, `consent_json`, `consent_type`, `consent_type1`, 
		`consent_explicit`, `diffusion`, `right_information`, `right_correction`, `right_opposition`, `right_portability`, 
		`sensitive`, `profiling`, `profiling_auto`, `mediasupport`, `siintegration`, `transmissionmethod`, `external_group`, `external_supplier`, `external_process`, `impact_person`, `impact_organism`, `specific_security_measures`, `collect_comment`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_plugin_dlteams_records_items','`itemtype`, `itemtype1`, `comment`, `json`, `timeline_position`, `date_creation`, `plugin_dlteams_storageendactions_id`, `plugin_dlteams_storagetypes_id`,
		`mandatory`, `oid`, `items_oid`, `items_oid1`'],
		['glpi_plugin_dlteams_concernedpersons', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_plugin_dlteams_concernedpersons_items', '`itemtype`, `itemtype1`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`, `items_oid1`'],
		['glpi_plugin_dlteams_processeddatas', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_plugin_dlteams_processeddatas_items', '`itemtype`, `itemtype1`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`, `items_oid1`'],
		['glpi_plugin_dlteams_legalbasis', '`is_recursive`, `name`, `plugin_dlteams_legalbasistypes_id`, `content`, `comment`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_plugin_dlteams_legalbasis_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
		['glpi_plugin_dlteams_storageperiods', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_plugin_dlteams_storageperiods_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `plugin_dlteams_storageendactions_id`, `plugin_dlteams_storagetypes_id`, `oid`, `items_oid`'],
		['glpi_plugin_dlteams_thirdpartycategories', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_plugin_dlteams_thirdpartycategories_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
		['glpi_plugin_dlteams_rightmeasures', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_plugin_dlteams_rightmeasures_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
		['glpi_plugin_dlteams_policieforms','`is_recursive`, `date_creation`, `name`, `content`, `documentcategories_id`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_plugin_dlteams_policieforms_items','`itemtype`, `comment`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
		['glpi_plugin_dlteams_datacatalogs','`is_recursive`, `date_creation`, `name`, `completename`, `content`, `comment`, `plugin_dlteams_catalogclassifications_id`, `plugin_dlteams_datacarriercategories_id`, `is_directoryservice`, `directory_name`, `default_keytype`, `level`, `ancestors_cache`, `sons_cache`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_plugin_dlteams_datacatalogs_items','`itemtype`, `itemtype1`, `comment`, `timeline_position`, `date_creation`, `oid`, `items_oid`, `items_oid1`'],
		['glpi_plugin_dlteams_riskassessments','`is_recursive`, `date_creation`, `name`, `content`, `comment`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_plugin_dlteams_riskassessments_items','`itemtype`, `comment`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
		['glpi_plugin_dlteams_audits','`is_recursive`, `date_creation`, `name`, `content`, `comment`, `plugin_dlteams_auditcategories_id`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_plugin_dlteams_audits_items','`itemtype`, `comment`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
		['glpi_plugin_dlteams_protectivemeasures', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `plugin_dlteams_protectivetypes_id`, `plugin_dlteams_protectivecategories_id`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_plugin_dlteams_protectivemeasures_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
		['glpi_plugin_dlteams_datacarriertypes', '`is_recursive`, `name`, `comment`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_plugin_dlteams_datacarriertypes_items', '`itemtype`, `comment`, `date_creation`, `oid`, `items_oid`'],
		['glpi_appliances', '`is_recursive`, `name`, `is_deleted`, `appliancetypes_id`, `comment`, `manufacturers_id`, `applianceenvironments_id`, `date_mod`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_appliances_items', '`itemtype`, `comment`, `oid`, `items_oid`'],
		['glpi_plugin_dlteams_deliverables', '`name`, `content`, `comment`, `document_name`, `document_title`, `document_content`,`document_comment`,`object_notification`, `object_approval`, `text_notification`, `text_approval`, `date_mod`, `date_creation`, `id_model`, `entity_model`, `date_majmodel`, `type_majmodel`, `oid`'],
		['glpi_plugin_dlteams_deliverables_items', '`itemtype`, `itemtype1`, `comment`, `date_creation`, `oid`, `items_oid`'],
		['glpi_plugin_dlteams_deliverables_sections', '`name`, `tab_name`, `comment`, `content`, `timeline_position`, `date_mod`, `date_creation`, `oid`,`deliverables_oid`'],
		['glpi_plugin_dlteams_deliverables_contents', '`name`, `comment`, `content`, `timeline_position`, `date_mod`, `date_creation`, `oid`, `deliverable_sections_oid`'],
		];

	$entities_id = $modelrgpd_id ; 
	DeleteRgpdRecords($entities_id); //nb : si on efface pas les imports s'ajouteront
	AddingOID();
	ImportDAT ();
	// IncrementCorrectionon pourrait "recaler les enregistrement importés dans les trous des id puis recaler l'auto-incrément
	UpdateIds ($entities_id); 
	// après l'update, si on a pas initialement effacé, on se retrouve donc avec des doublons -> on pourrait procéder à des mises à jour
	// mise à jour des records : facile / puis mise à jour des liaisons : plus difficile
	Delete_oid ();
	
 Session::addMessageAfterRedirect($message);
 echo "<script>window.location.href='config.form.php';</script>";// revient sur la page
		
    function DeleteRgpdRecords($entities_id) {
	// Suppression des tables et _items dépendants pour une entité
		print_r ("Lancement effacement des enregistrements pour entité = $entities_id" . "<br>");
		global $DB, $message;
		$objects = ['records', 'concernedpersons', 'processeddatas', 'datacatalogs', 'legalbasis', 'storageperiods', 'thirdpartycategories', 'rightmeasures', 
		'policieforms', 'riskassessments', 'audits', 'protectivemeasures', 'datacarriertypes', 'appliances', 'deliverables' ];
		// classes deliverable_contents & deliverable_sections
		$table1 = 'glpi_plugin_dlteams_deliverables_contents' ; $table2 = 'glpi_plugin_dlteams_deliverables_sections' ; $table3 = 'glpi_plugin_dlteams_deliverables';
		$query = "DELETE t1 FROM $table1 as t1 INNER JOIN $table2 as t2 ON t2.`id` = t1.`deliverable_sections_id` INNER JOIN $table3 as t3 ON t3.`id` = t2.`deliverables_id` AND t3.`entities_id` = $entities_id";
		$DB->queryOrDie($query, $DB->error());
			// réduction de l'auto_increment
		$result = $DB->query("SELECT MAX(`id`)+1 FROM $table1"); 
		if ($result && $DB->numrows($result) > 0) {
			$data = $DB->fetchAssoc($result);
			$last_id = $data['MAX(`id`)+1']; // print_r ("<br>". $data['MAX(`id`)+1']);
			if ($last_id <> NULL) {$result = $DB->query("ALTER TABLE $table1 AUTO_INCREMENT = $last_id");} else {$result = $DB->query("ALTER TABLE $table1 AUTO_INCREMENT = 1");}
		}

		$query = "DELETE t2 FROM $table2 as t2 INNER JOIN $table3 as t3 ON t2.`deliverables_id` = t3.`id` AND t3.`entities_id` = $entities_id";
		$DB->queryOrDie($query, $DB->error()); print_r ("Deliverable section et content effacés"."<br>");
		$result = $DB->query("SELECT MAX(`id`)+1 FROM $table2");
			// réduction de l'auto_increment
		if ($result && $DB->numrows($result) > 0) {
			$data = $DB->fetchAssoc($result);
			$last_id = $data['MAX(`id`)+1']; // print_r ("<br>". $data['MAX(`id`)+1']);
			if ($last_id <> NULL) {$result = $DB->query("ALTER TABLE $table2 AUTO_INCREMENT = $last_id");} else {$result = $DB->query("ALTER TABLE $table2 AUTO_INCREMENT = 1");}
		}

		foreach ($objects as $object) {
			if ($object === "appliances") {$table = "glpi_" . $object;} else {$table = "glpi_plugin_dlteams_" . $object;}
			$query = "DELETE t1 FROM ".$table."_items as t1 INNER JOIN ".$table." as t2 ON t2.`id` = t1.`".$object."_id` WHERE t2.`entities_id` = ".$entities_id;
			$DB->queryOrDie($query, $DB->error());
			$query = "DELETE FROM ".$table." WHERE `entities_id` =  ".$entities_id;
			$DB->queryOrDie($query, $DB->error());
			// réduction de l'auto_increment
			$result = $DB->query("SELECT MAX(`id`)+1 FROM $table"."_items");
			if ($result && $DB->numrows($result) > 0) {
				$data = $DB->fetchAssoc($result);
				$last_id = $data['MAX(`id`)+1']; // print_r ("<br>". $data['MAX(`id`)+1']);
				if ($last_id <> NULL) {$result = $DB->query("ALTER TABLE $table"."_items AUTO_INCREMENT = $last_id");} else {$result = $DB->query("ALTER TABLE $table"."_items AUTO_INCREMENT = 1");}
			}
			$result = $DB->query("SELECT MAX(`id`)+1 FROM $table");
			if ($result && $DB->numrows($result) > 0) {
				$data = $DB->fetchAssoc($result);
				$last_id = $data['MAX(`id`)+1']; // print_r ("<br>". $data['MAX(`id`)+1']);
				if ($last_id <> NULL) {$result = $DB->query("ALTER TABLE $table AUTO_INCREMENT = $last_id");} else {$result = $DB->query("ALTER TABLE $table AUTO_INCREMENT = 1");}
			}
		}
		// $DB->request("SET @modelrgpd_id := ".$modelrgpd_id);
		$query = "SELECT COUNT(*) FROM `glpi_plugin_dlteams_records` WHERE `entities_id` = '$entities_id'";
		$row = $DB->query($query)->fetch_assoc();
		$message .= "Pour l'entité " . $entities_id . nl2br("\n");
		$message .= strval($row["COUNT(*)"]) . " traitements précédents supprimés <br>" . nl2br("\n");
		// $message .= "Tables de l'entité n° " . $entities_id . " supprimées " . nl2br("\n") ;
		print_r ("Enregistrements effacés"."<br>");
	}

    function AddingOID () {
		print_r ("Lancement ajout des OIDs" . "<br>");
		global $DB, $message;
		//STEP2 : on ajoute les oid aux tables
		// pour les class avec oid + oid1
		$object1s = ['records', 'concernedpersons', 'processeddatas', 'datacatalogs'];
		foreach ($object1s as $object1) {
			$table = "glpi_plugin_dlteams_" . $object1;
			$query = "ALTER TABLE ".$table." ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
			$DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE ".$table." ADD INDEX IF NOT EXISTS `oid` (`oid`) USING BTREE";
			$DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE ".$table."_items ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid1` INT UNSIGNED NULL";
			$DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE ".$table."_items ADD INDEX IF NOT EXISTS `oid` (`oid`) USING BTREE, ADD INDEX IF NOT EXISTS `items_oid` (`items_oid`) USING BTREE, ADD INDEX IF NOT EXISTS `items_oid1` (`items_oid1`) USING BTREE";
			$DB->queryOrDie($query, $DB->error());
			$query = "DELETE FROM ".$table." WHERE `oid` IS NOT NULL"; $DB->queryOrDie($query, $DB->error()); // evite les erreurs d'index for key 'unicity' si il restait des oid
			$query = "DELETE FROM ".$table."_items WHERE `oid` IS NOT NULL"; $DB->queryOrDie($query, $DB->error());
		}
		// pour les autres class avec seulement oid
		$object2s = ['legalbasis', 'storageperiods', 'thirdpartycategories', 'rightmeasures',
		'policieforms', 'riskassessments', 'audits', 'protectivemeasures', 'datacarriertypes', 'deliverables'];
		foreach ($object2s as $object2) {
			$table = "glpi_plugin_dlteams_" . $object2;
			$query = "ALTER TABLE ".$table." ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
			$DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE " . $table . " ADD INDEX IF NOT EXISTS `oid` (`oid`) USING BTREE";
			$DB->queryOrDie($query, $DB->error());

			$query = "ALTER TABLE ".$table."_items ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL";
			$DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE ".$table."_items ADD INDEX IF NOT EXISTS `oid` (`oid`) USING BTREE, ADD INDEX IF NOT EXISTS `items_oid` (`items_oid`) USING BTREE";
			$DB->queryOrDie($query, $DB->error());
			$query = "DELETE FROM ".$table." WHERE `oid` IS NOT NULL"; $DB->queryOrDie($query, $DB->error());
			$query = "DELETE FROM ".$table."_items WHERE `oid` IS NOT NULL"; $DB->queryOrDie($query, $DB->error());
		}
		// pour les class sans _items
		$object4s = ['deliverables_sections', 'deliverables_contents'];
		foreach ($object4s as $object4) {
			$table = "glpi_plugin_dlteams_" . $object4;
			$query = "ALTER TABLE " . $table . " ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
			$DB->queryOrDie($query, $DB->error());
			$query = "DELETE FROM " . $table . " WHERE `oid` IS NOT NULL"; $DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE glpi_plugin_dlteams_deliverables_sections ADD IF NOT EXISTS `deliverables_oid` INT UNSIGNED NULL";
			$DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE glpi_plugin_dlteams_deliverables_contents ADD IF NOT EXISTS `deliverable_sections_oid` INT UNSIGNED NULL";
			$DB->queryOrDie($query, $DB->error());
		}
		// pour les class glpi avec seulement oid
		$object3s = ['appliances'];
		foreach ($object3s as $object3) {
			$table = "glpi_" . $object3;
			$query = "ALTER TABLE ".$table." ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
			$DB->queryOrDie($query, $DB->error());
			$query = "ALTER TABLE ".$table."_items ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL";
			$DB->queryOrDie($query, $DB->error());
			$query = "DELETE FROM " . $table . " WHERE `oid` IS NOT NULL"; $DB->queryOrDie($query, $DB->error());
			$query = "DELETE FROM " . $table . "_items WHERE `oid` IS NOT NULL"; $DB->queryOrDie($query, $DB->error());
		}
	$message .= "OID ajoutés aux tables" . nl2br("\n") ;
	print_r ("OID ajoutés aux tables" . "<br>");
	}
	
	/*	$DB->queryOrDie($query, $DB->error());
			$query = "SELECT COUNT(*) FROM " . $table. " WHERE `entities_id` = '$modelrgpd_id'";
			$row = $DB->query($query)->fetch_assoc();
			$message .= strval($row["COUNT(*)"]) . $table . " importés <br>";
	*/

    function ImportDAT () {
		print_r ("Importation des .dat" . "<br>");
		global $DB, $message, $fields_exports;
		// STEP 3 : import datas without id
		foreach ($fields_exports as list($table, $fields_export)) {
		// si model dossier install/datas ; sinon dossier files/_plugins/dlteams/
			//$glpiRoot=str_replace('\\', '/', GLPI_ROOT);
			//$file_pointer = $glpiRoot. "/marketplace/dlteams/install/datas/" . $table . ".dat";
			$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat"; 
		// pour export d'une entité, on envoie dans files/plugins
			// $file_pointer = $glpiRoot. "/files/_plugins/" . "dlteams"."/" . $table . ".dat";
		// pour tests, on prend le dossier de prod
			//$file_pointer = "/var/www/dlteams_app/marketplace/dlteams/install/datas/" . $table . ".dat";
			$query = "LOAD DATA INFILE '".$file_pointer."' IGNORE INTO TABLE ".$table." FIELDS TERMINATED BY '\t' ($fields_export)";
			$DB->queryOrDie($query, $DB->error());
			$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0";
			$row = $DB->query($query)->fetch_assoc();
			$message .= $table . " : " . strval($row["COUNT(*)"]) . nl2br("\n") ;
		}
		print_r ("Importations effectuées"."<br>"."<br>");
	}
   
// manque `entity_model` + `id_model` `date_majmodel` + `type_majmodel` pour se baser dessus au lien de name
  
    function UpdateIds ($entities_id) {
		$step = "STEP 4 - Mise à jour des Ids" ; // print_r ($step."<br>");
		global $DB, $message, $fields_exports, $entities_id;

		/* echec d'une maj avec update -> on efface tous les records de l'entité et on recrée tout à partir de l'import
		// 1/ sur la base du name, on ajoute à entity_cible les enregistrements présents dans entity_source et absent dans entity_cible -> on change l'entity_id de 0 quand name n'existe pas 
			$table = "glpi_plugin_dlteams_records"; // t1 = source ; t2=cible
			$query = "UPDATE `glpi_plugin_dlteams_records` as t1
					SET t1.`entities_id` = 1 
					WHERE t1.`name` NOT IN (SELECT t2.`name` FROM `glpi_plugin_dlteams_records` as t2 WHERE t2.entities_id = 1);";
			$DB->queryOrDie($query, $DB->error());
		// STEP 1b : update relation id
		$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.oid = t2.oid 
		SET t1.`records_id` = t2.`id`, t1.`date_mod` = " . '"' . $date . '"';
		$DB->queryOrDie($query, $DB->error());
		// 2/ on efface dans entities_cible les enregistrements non présents dans entity_source
		// 3/ on update dans entity_cible les enregistrements identique dans entity_source
		$table = "glpi_plugin_dlteams_records"; // t1 = source ; t2=cible
		$query = "UPDATE `glpi_plugin_dlteams_records` as t2
					INNER JOIN `glpi_plugin_dlteams_records` as t1
                    ON t2.`name` =  t1.`name` AND t2.`entities_id` = t2_id
					SET t2.`content` = t1.`content`";
		$DB->queryOrDie($query, $DB->error());
		// 4 on met à la jour les _items, puisque tous les enregistrements principaux existent*/
 
		// update entities_id + date (+ copy_id) pour les tables objets,
		$date = date('Y-m-d H:i:s');
		foreach ($fields_exports as list($table, $fields_export)) {
			$endoftable = substr($table, -6);
			if ($endoftable <> "_items" && $table <> "glpi_plugin_dlteams_deliverables_sections" && $table <> "glpi_plugin_dlteams_deliverables_contents") {
			$query = "UPDATE IGNORE ".$table." SET `entities_id` = " . $entities_id . ", `date_mod` = " . '"' . $date . '"' . ", `id_model` =  `oid` WHERE `oid` is not null" ;
			$DB->queryOrDie($query, $DB->error());
			}
			// $query = "UPDATE ".$table." SET `date_creation` =  WHERE `oid` is not null" ; $DB->queryOrDie($query, $DB->error());
		}
		
		// update records_items
		$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.oid = t2.oid 
		SET t1.`records_id` = t2.`id`, t1.`date_mod` = " . '"' . $date . '"';
		$DB->queryOrDie($query, $DB->error()); print_r (" records, id->oid ok" . "<br>");
		// update records_items pour liaisons avec PluginDlteamsSendingReason		
		$table = "glpi_plugin_dlteams_sendingreasons"; $table2 = "glpi_plugin_dlteams_records"; // exception pas de oid pour sendindreasons
		$query = "UPDATE $table2". "_items" ." as t1 INNER JOIN $table as t2 ON t1.`items_oid1` = t2.`id` AND t1.`itemtype1` = ". '"PluginDlteamsSendingReason"' ." SET t1.`items_id1` = t2.`id`";
		$DB->queryOrDie($query, $DB->error());
				
		// Update concernedpersons_items pour liaisons records (avec processeddatas)
		$table = "glpi_plugin_dlteams_concernedpersons"; $table2 = "glpi_plugin_dlteams_records";
		$query = "UPDATE $table2". "_items" ." as t1 INNER JOIN $table as t2 ON t1.`items_oid` = t2.`oid` AND t1.`itemtype` = ". '"PluginDlteamsConcernedPerson"' ." SET t1.`items_id` = t2.`id`";
		$DB->queryOrDie($query, $DB->error());
		$query = "UPDATE $table". "_items" ." as t1 INNER JOIN $table as t2 ON t2.`oid` = t1.`oid` SET t1.`concernedpersons_id` = t2.`id`";
		$DB->queryOrDie($query, $DB->error());
		$query = "UPDATE $table". "_items" ." as t1 INNER JOIN $table2 as t2 ON t2.`oid` = t1.`items_oid` AND t1.`itemtype` = " . '"PluginDlteamsRecord"' . " SET t1.`items_id` = t2.`id`";
		$DB->queryOrDie($query, $DB->error());
		$query = "UPDATE $table". "_items" ." as t1 INNER JOIN $table2 as t2 ON t2.`oid` = t1.`items_oid1` AND t1.`itemtype1` = ". '"PluginDlteamsProcessedData"' . " SET t1.`items_id1` = t2.`id`";
		$DB->queryOrDie($query, $DB->error()); print_r ("Concernedpersons_items pour liaisons records ok" . "<br>");
				
		// Update processeddatas_items pour liaisons records (avec ConcernedPerson)
		$table = "glpi_plugin_dlteams_processeddatas"; $table2 = "glpi_plugin_dlteams_records";
		$query = "UPDATE $table2". "_items" ." as t1 INNER JOIN $table as t2 ON t1.`items_oid1` = t2.`oid` AND t1.`itemtype1` = ". '"PluginDlteamsProcessedData"' . " SET t1.`items_id1` = t2.`id`";
		$DB->queryOrDie($query, $DB->error());
		$query = "UPDATE $table". "_items" ." as t1 INNER JOIN $table as t2 ON t2.`oid` = t1.`oid` SET t1.`processeddatas_id` = t2.`id`";
		$DB->queryOrDie($query, $DB->error());
		$query = "UPDATE $table". "_items" ." as t1 INNER JOIN $table2 as t2 ON t2.`oid` = t1.`items_oid` AND t1.`itemtype` = " . '"PluginDlteamsRecord"' . " SET t1.`items_id` = t2.`id`";
		$DB->queryOrDie($query, $DB->error());
		$query = "UPDATE $table". "_items" ." as t1 INNER JOIN $table2 as t2 ON t2.`oid` = t1.`items_oid1` AND t1.`itemtype1` = ". '"PluginDlteamsConcernedPerson"' . " SET t1.`items_id1` = t2.`id`";
		$DB->queryOrDie($query, $DB->error()); print_r ("ProcessedData_items pour liaisons records ok" . "<br>");

		// print_r ("update autres _items pour liaisons avec records");
		$fields = [
		['glpi_plugin_dlteams_legalbasis','glpi_plugin_dlteams_records', '"PluginDlteamsLegalBasi"', '`legalbasis_id`', '"PluginDlteamsRecord"'],
		['glpi_plugin_dlteams_storageperiods','glpi_plugin_dlteams_records', '"PluginDlteamsStoragePeriod"','`storageperiods_id`','"PluginDlteamsRecord"'],
		['glpi_plugin_dlteams_rightmeasures','glpi_plugin_dlteams_records',' "PluginDlteamsRightMeasure"','`rightmeasures_id`','"PluginDlteamsRecord"'],
		['glpi_plugin_dlteams_thirdpartycategories','glpi_plugin_dlteams_records','"PluginDlteamsThirdPartyCategory"','`thirdpartycategories_id`','"PluginDlteamsRecord"'],
		['glpi_plugin_dlteams_protectivemeasures','glpi_plugin_dlteams_records','"PluginDlteamsProtectiveMeasure"','`protectivemeasures_id`','"PluginDlteamsRecord"'],
		['glpi_plugin_dlteams_policieforms','glpi_plugin_dlteams_records','"PluginDlteamsPolicieForm"','`policieforms_id`','"PluginDlteamsRecord"'],
		['glpi_plugin_dlteams_riskassessments','glpi_plugin_dlteams_records','"PluginDlteamsRiskAssessment"','`riskassessments_id`','"PluginDlteamsRecord"'],
		];	
		foreach ($fields as list($table1, $table2, $class1, $class1_id, $class2 )) {
			$query = "UPDATE $table2". "_items" ." as t1 INNER JOIN $table1 as t2 ON t1.`items_oid` = t2.`oid` AND t1.`itemtype` = $class1 SET t1.`items_id` = t2.`id`";
			$DB->queryOrDie($query, $DB->error()); // print_r ($query. "<br>"); 
			$query = "UPDATE $table1". "_items" ." as t1 INNER JOIN $table1 as t2 ON t2.`oid` = t1.`oid` SET t1.$class1_id = t2.`id`";
			$DB->queryOrDie($query, $DB->error()); // print_r ($query. "<br>");
			$query = "UPDATE $table1". "_items" ." as t1 INNER JOIN $table2 as t2 ON t2.`oid` = t1.`items_oid` AND t1.`itemtype` = $class2 SET t1.`items_id` = t2.`id`";
			$DB->queryOrDie($query, $DB->error()); // print_r ($query. "<br>");
		}

		// print_r ("update liaisons deliverables_sections avec deliverables"); 
			$table1 = "glpi_plugin_dlteams_deliverables_sections" ; $table2 = "glpi_plugin_dlteams_deliverables";
			$query = "UPDATE $table1 as t1 INNER JOIN $table2 as t2 ON t1.`deliverables_oid` = t2.`oid` SET t1.`deliverables_id` = t2.`id`";
			$DB->queryOrDie($query, $DB->error()); // print_r ($query. "<br>"); 
		// print_r ("update liaisons deliverables_contents avec deliverables_sections"); 
			$table1 = "glpi_plugin_dlteams_deliverables_contents" ; $table2 = "glpi_plugin_dlteams_deliverables_sections";
			$query = "UPDATE $table1 as t1 INNER JOIN $table2 as t2 ON t1.`deliverable_sections_oid` = t2.`oid` SET t1.`deliverable_sections_id` = t2.`id`";
			$DB->queryOrDie($query, $DB->error()); // print_r ($query. "<br>"); 

		//$result = $DB->query('SELECT ROW_COUNT() AS nombre_enregistrements');*/
		print_r ("MAJ Ids effectués"."<br>"."<br>");
	}

	function Delete_oid () {
	// print_r ("Effacement des champs OIDs"."<br>");
	global $DB, $fields_exports ;
		foreach ($fields_exports as list($table, $fields_export)) {
			$query = "ALTER TABLE $table DROP IF EXISTS `oid`, DROP IF EXISTS `items_oid`, DROP IF EXISTS `items_oid1`";
			$DB->queryOrDie($query, $DB->error());
		}
		$table = "glpi_plugin_dlteams_deliverables_sections" ; $query = "ALTER TABLE $table DROP IF EXISTS `deliverables_oid`";$DB->queryOrDie($query, $DB->error());
		$table = "glpi_plugin_dlteams_deliverables_contents" ; $query = "ALTER TABLE $table DROP IF EXISTS `deliverable_sections_oid`";$DB->queryOrDie($query, $DB->error());
	}


////////////////// OLD
/*	$tables = ['glpi_plugin_dlteams_allitems',
	'glpi_plugin_dlteams_records_externals',
	 'glpi_plugin_dlteams_records_personalanddatacategories',
	'glpi_plugin_dlteams_records_storages'];
	foreach ($tables as $table) {
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		// delete datas in database
		$DB->queryOrDie("delete from `".$table."`");
		$DB->queryOrDie("LOAD DATA INFILE '".$file_pointer."' INTO TABLE `".$table."` FIELDS TERMINATED BY '\t'");
		Session::addMessageAfterRedirect($table);
	}

	// IMPORT .dat in rgpd-model records
   $tables = [
'glpi_plugin_dlteams_records',
'glpi_plugin_dlteams_concernedpersons',
'glpi_plugin_dlteams_processeddatas',
'glpi_plugin_dlteams_legalbasis',
'glpi_plugin_dlteams_storageperiods',
'glpi_plugin_dlteams_thirdpartycategories',
'glpi_plugin_dlteams_rightmeasures',
'glpi_plugin_dlteams_policieforms',
'glpi_plugin_dlteams_protectivemeasures',
'glpi_plugin_dlteams_deliverables',
'glpi_plugin_dlteams_audits',
'glpi_plugin_dlteams_riskassessments'
];

	// import datas from files in plugin/install/datas/
	foreach ($tables as $table) {
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		// delete datas in database
		$DB->queryOrDie("delete from `".$table."`");
		// import datas from files in plugin/install/datas/
		$DB->queryOrDie("LOAD DATA INFILE '".$file_pointer."' INTO TABLE `".$table."` FIELDS TERMINATED BY '\t'");
		Session::addMessageAfterRedirect($table);
	}

	// idem form _items tables
	foreach ($tables as $table) {
		$table = $table . "_items";
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		// delete datas in database
		$DB->queryOrDie("delete from `".$table."`");
		// import datas from files in plugin/install/datas/
		$DB->queryOrDie("LOAD DATA INFILE '".$file_pointer."' INTO TABLE `".$table."` FIELDS TERMINATED BY '\t'");
		Session::addMessageAfterRedirect($table);
	}

	// then entities_id  = @modelrgpd_id for records where id < 1000
 	foreach ($tables as $table) {
		$DB->queryOrDie("UPDATE `".$table."` SET entities_id = ".$rgpdmodel_id." WHERE id BETWEEN 1 and 999 ");
	}

*/
