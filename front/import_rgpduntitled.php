<?php

include("../../../inc/includes.php");
global $DB;
//var_dump("importation des intitulés");

	// récupération de l'id model-rgpd
	$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
    if ($result && $DB->numrows($result) > 0) {
        $data = $DB->fetchAssoc($result);
        $modelrgpd_id = $data['id'];
    }

	$entities_target = $modelrgpd_id;
	$folder = plugin_dlteams_root . "/install/datas/";
// Import des dossiers pour les documents 
	$table = "glpi_documentcategories";
	$file_pointer = $folder . $table . ".dat";
	$DB->queryOrDie("DELETE FROM `".$table."` WHERE `completename` LIKE \"Conformité RGPD%\""); //effacement de la catégorie "conformité RGPD" ?? pour l'entité
	$result = $DB->query("SELECT MAX(`id`)+1 FROM $table");  //remise à niveau de auto_increment
	if ($result && $DB->numrows($result) > 0) {
			$data = $DB->fetchAssoc($result);
			$last_id = $data['MAX(`id`)+1']; // print_r ("<br>". $data['MAX(`id`)+1']);
			if ($last_id <> NULL) {$result = $DB->query("ALTER TABLE $table AUTO_INCREMENT = $last_id");} else {$result = $DB->query("ALTER TABLE $table AUTO_INCREMENT = 1");}
		}
	$DB->queryOrDie("LOAD DATA INFILE '".$file_pointer."' IGNORE INTO TABLE `".$table."` FIELDS TERMINATED BY '\t' (name, comment, documentcategories_id, completename, level, ancestors_cache, sons_cache, date_mod, date_creation);");
	//!! il n'y a pas entities dans documentcategories -> on ajoute	
	$DB->queryOrDie("ALTER TABLE $table ADD IF NOT EXISTS `entities_id` int unsigned NOT NULL DEFAULT 0, ADD IF NOT EXISTS `is_recursive` tinyint(1) NOT NULL DEFAULT 0");
	$DB->queryOrDie("UPDATE $table SET entities_id = $entities_target WHERE completename LIKE \"Conformité RGPD%\"");
	Session::addMessageAfterRedirect($table);
	print_r ("documentcategories mis à jour");

// IMPORT DES INTITULES
   $tables = [
'glpi_plugin_dlteams_activitycategories',
'glpi_plugin_dlteams_auditcategories',
'glpi_plugin_dlteams_catalogclassifications',
'glpi_plugin_dlteams_datacarriercategories',
'glpi_plugin_dlteams_datacarrierhostings',
'glpi_plugin_dlteams_datacarriertypes',
'glpi_plugin_dlteams_datacarriermanagements',
'glpi_plugin_dlteams_datacategories',
'glpi_plugin_dlteams_impacts',
'glpi_plugin_dlteams_keytypes',
'glpi_plugin_dlteams_legalbasistypes',
'glpi_plugin_dlteams_meansofacces',
'glpi_plugin_dlteams_mediasupports',
'glpi_plugin_dlteams_protectivecategories',
'glpi_plugin_dlteams_rightmeasurecategories',
'glpi_plugin_dlteams_protectivetypes',
'glpi_plugin_dlteams_sendingreasons',
'glpi_plugin_dlteams_servertypes',
'glpi_plugin_dlteams_siintegrations',
'glpi_plugin_dlteams_storagetypes',
'glpi_plugin_dlteams_storageendactions',
'glpi_plugin_dlteams_transmissionmethods',
'glpi_plugin_dlteams_userprofiles',
'glpi_itilcategories',
];
	// import datas from files in plugin/install/datas/
	foreach ($tables as $table) {
		$file_pointer = $folder . $table . ".dat";
		// delete datas in database -> pourquoi effacer ??? avec ignore, pas besoin ? ou il faut ajouter un update ? 
		$DB->queryOrDie("delete from `".$table."` WHERE entities_id = 0");
		// import datas from files in plugin/install/datas/
		$DB->queryOrDie("LOAD DATA INFILE '".$file_pointer."' IGNORE INTO TABLE `".$table."` FIELDS TERMINATED BY '\t'");
		// comptage des enregistrements exportés
		$query = "SELECT COUNT(*) FROM $table"; 
		$result = $DB->query($query);
		$row = $result->fetch_assoc();
	    $message .= $table . " : " . strval($row["COUNT(*)"]) . nl2br("\n") ; 
		// Session::addMessageAfterRedirect($table);
	}
		$message .= "Importation des intitulés effectués";

// gabarits d'évenement
	$tables = [ 'glpi_tickettemplates', 'glpi_tickettemplatepredefinedfields', 'glpi_tickettemplatemandatoryfields', 'glpi_tickettemplatehiddenfields', 'glpi_tasktemplates'];
	// STEP 1 : on ajoute les oid
	foreach ($tables as $table) {
			$query = "ALTER TABLE $table ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
			$DB->queryOrDie($query, $DB->error());
	}
	// 	STEP 3 : import without id
	$fields_exports = [
	['glpi_tickettemplates', '`name`, `entities_id`, `is_recursive`, `comment`, `oid`'],
	['glpi_tickettemplatepredefinedfields', '`tickettemplates_id`, `num`, `value`, `oid`'],
	['glpi_tickettemplatemandatoryfields', '`tickettemplates_id`, `num`, `oid`'],
	['glpi_tickettemplatehiddenfields', '`tickettemplates_id`, `num`, `oid`'],
	['glpi_tasktemplates', '`is_recursive`, `name`, `content`, `actiontime`, `comment`, `state`, `oid`'],
	];
	foreach ($fields_exports as list($table, $fields_export)) {
		$file_pointer = $folder . $table . ".dat";
		$DB->queryOrDie("delete from `".$table."`");
		$DB->queryOrDie ("LOAD DATA INFILE '".$file_pointer."' IGNORE INTO TABLE `".$table."` FIELDS TERMINATED BY '\t' ($fields_export)");
		// comptage des enregistrements exportés
		$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0"; 
		$result = $DB->query($query);
		$row = $result->fetch_assoc();
	    $message .= $table . " : " . strval($row["COUNT(*)"]) . nl2br("\n") ; // strval($result); // . $table .  . "exportés"; 
	}
	// STEP 3 : update des id
    $DB->queryOrDie($query, $DB->error());
			$query = "UPDATE `glpi_tickettemplatepredefinedfields` AS T1
					INNER JOIN `glpi_tickettemplates` AS T2
					ON T1.`oid` = T2.`oid`
					SET T1.`tickettemplates_id` = T2.`id`";  //oid<->tickettemplates_oid
			$DB->queryOrDie($query, $DB->error());
			$query = "UPDATE `glpi_tickettemplatemandatoryfields` AS T1
					INNER JOIN `glpi_tickettemplates` AS T2
					ON T1.`oid` = T2.`oid`
					SET T1.`tickettemplates_id` = T2.`id`";
			$DB->queryOrDie($query, $DB->error());
			$query = "UPDATE `glpi_tickettemplatehiddenfields` AS T1
					INNER JOIN `glpi_tickettemplates` AS T2
					ON T1.`oid` = T2.`oid`
					SET T1.`tickettemplates_id` = T2.`id`";
			$DB->queryOrDie($query, $DB->error());
	// 	STEP 4 Delete oid
	/*foreach ($tables as $table) {
		$query = "ALTER TABLE $table DROP IF EXISTS `oid`";
		$DB->queryOrDie($query, $DB->error());
		}*/

// jointer les itilcategory avec les tickettemplates_id avec <-> name 
	$query = "UPDATE `glpi_itilcategories` AS T1
			INNER JOIN `glpi_tickettemplates` AS T2
			ON T1.`name` = T2.`name`
			SET T1.`tickettemplates_id_demand` = T2.`id`, T1.`tickettemplates_id_incident` = T2.`id`";
			$DB->queryOrDie($query, $DB->error());

// jointer les champs prédéfinios avec tasktemplates_id  ; le num pour "Tâche = 175" 
	$query = "UPDATE `glpi_tickettemplatepredefinedfields` AS T1
			INNER JOIN `glpi_tickettemplates` AS T2
			ON T1.`tickettemplates_id` = T2.`id`
			INNER JOIN `glpi_tasktemplates` AS T3
			ON T3.`name` = T2.`name`
			SET T1.`value` = T3.`id`
			WHERE T1.`num` = 175";
			$DB->queryOrDie($query, $DB->error());

	$message .= "Modèles d'évenements importés"; 
	Session::addMessageAfterRedirect($message);
	echo "<script>window.location.href='config.form.php';</script>";// revient sur la page
