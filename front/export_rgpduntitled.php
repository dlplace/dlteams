<?php

include("../../../inc/includes.php");
global $DB;
    // $DB->query("SET NAMES utf8mb4");
	// Delete file with unlink() function
	//if (!unlink($file_pointer)) 
	//{ echo ("$file_pointer cannot be deleted due to an error");
	//} else { echo ("$file_pointer has been deleted");}
	// export datas in new file.dat
	//$filename = plugin_dlteams_root . "\install\datas\databasecategories.dat" ; 
	//$filename = "databasecategories.dat" ; 
/*
	$file = "glpi_plugin_dlteams_databasecategories" ; 
	$file_pointer = plugin_dlteams_root . "/install/datas/" . $file . ".dat";
	unlink($file_pointer);
	$DB->queryOrDie("SELECT * FROM `".$file."` WHERE entites_id = 0 INTO OUTFILE '".plugin_dlteams_root."/install/datas/".$file.".dat'");
*/

	// ouverture du dossier en écriture
	$folder = plugin_dlteams_root . "/install/datas/";
	chmod($folder, 0777); // ouverture des droits d'écriture
	// chown (-R www-data:www-data plugin_dlteams_root); 
	// $result = exec("chown -R www-data:www-data plugin_dlteams_root", $output, $return_var);
	// if ($return_var === 0) {print_r ("Propriétaire du dossier mis à jour");} else {print_r ("Mise à jour du propriétaire du dossier impossible");}

	// id of model-rgpd entity
	$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
	$modelrgpd_id = 0;
	if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $modelrgpd_id = $data['id']; } 

	// Export from model-rgpd to .dat
	$file = "glpi_documentcategories";
	$file_pointer = plugin_dlteams_root . "/install/datas/" . $file . ".dat";
	unlink($file_pointer);
	$DB->queryOrDie("SELECT name, comment, documentcategories_id, completename, level, ancestors_cache, sons_cache, date_mod, date_creation FROM `".$file."`  WHERE completename LIKE \"Conformité RGPD%\" INTO OUTFILE '" . $file_pointer . "' CHARACTER SET utf8mb4");

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
'glpi_itilcategories', // catégories d'événements
];
   foreach ($tables as $table) {
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		unlink($file_pointer);
		$DB->queryOrDie("SELECT * FROM `".$table."` WHERE entities_id = '0' INTO OUTFILE '" . $file_pointer . "' CHARACTER SET utf8mb4");
		// comptage des enregistrements exportés
		$query = "SELECT COUNT(*) FROM $table"; 
		$result = $DB->query($query);
		$row = $result->fetch_assoc();
	    $message .= $table . " : " . strval($row["COUNT(*)"]) . nl2br("\n") ; 
		// Session::addMessageAfterRedirect($table);
   }

	// gabarits d'évenement + gabarits de tâche
	$tables = [ 'glpi_tickettemplates', 'glpi_tickettemplatepredefinedfields', 'glpi_tickettemplatemandatoryfields', 'glpi_tickettemplatehiddenfields', 'glpi_tasktemplates'];
	// STEP 1 : on ajoute les oid
	foreach ($tables as $table) {
			$query = "ALTER TABLE $table ADD IF NOT EXISTS `oid` INT UNSIGNED NOT NULL DEFAULT '0'";
			$DB->queryOrDie($query, $DB->error());
   }
// STEP 2 : update des oid
	$query = "UPDATE `glpi_tickettemplates` SET `oid` = `id` WHERE `entities_id` = 0";
	$query = "UPDATE `glpi_tasktemplates` SET `oid` = `id` WHERE `entities_id` = 0";
    $DB->queryOrDie($query, $DB->error());
			$query = "UPDATE `glpi_tickettemplatepredefinedfields` AS T1
					INNER JOIN `glpi_tickettemplates` AS T2
					ON T1.`tickettemplates_id` = T2.`id`
					SET T1.`oid` = T1.`tickettemplates_id` 
					WHERE T2.`entities_id` = 0"; //oid<->tickettemplates_oid
			$DB->queryOrDie($query, $DB->error());
			$query = "UPDATE `glpi_tickettemplatemandatoryfields` AS T1
					INNER JOIN `glpi_tickettemplates` AS T2
					ON T1.`tickettemplates_id` = T2.`id`
					SET T1.`oid` = T1.`tickettemplates_id`
					WHERE T2.`entities_id` = 0";
			$DB->queryOrDie($query, $DB->error());
			$query = "UPDATE `glpi_tickettemplatehiddenfields` AS T1
					INNER JOIN `glpi_tickettemplates` AS T2
					ON T1.`tickettemplates_id` = T2.`id`
					SET T1.`oid` = T1.`tickettemplates_id`
					WHERE T2.`entities_id` = 0";
			$DB->queryOrDie($query, $DB->error());

// 	STEP 3 : export without id
	$fields_exports = [
	['glpi_tickettemplates', '`name`, `entities_id`, `is_recursive`, `comment`, `oid`'],
	['glpi_tickettemplatepredefinedfields', '`tickettemplates_id`, `num`, `value`, `oid`'],
	['glpi_tickettemplatemandatoryfields', '`tickettemplates_id`, `num`, `oid`'],
	['glpi_tickettemplatehiddenfields', '`tickettemplates_id`, `num`, `oid`'],
	['glpi_tasktemplates', '`is_recursive`, `name`, `content`, `actiontime`, `comment`, `state`, `oid`'],
	];
	foreach ($fields_exports as list($table, $fields_export)) {
		$file_pointer = $folder . $table . ".dat";
		unlink($file_pointer);
		$query = "SELECT $fields_export FROM $table WHERE `oid` <> 0 INTO OUTFILE '".$file_pointer."' CHARACTER SET utf8mb4";
		$DB->queryOrDie($query, $DB->error());
		$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0"; 
		$result = $DB->query($query);
		$row = $result->fetch_assoc();
	    $message .= $table . " : " . strval($row["COUNT(*)"]) . nl2br("\n") ; // strval($result); // . $table .  . "exportés"; 
	}
	chmod($folder, 0755); // fermeture des droits

// 	STEP 4 Delete oid
	/*foreach ($tables as $table) {
		$query = "ALTER TABLE $table DROP IF EXISTS `oid`";
		$DB->queryOrDie($query, $DB->error());
		}*/

	$message .= "Modèles d'évenements exportés"; 
	Session::addMessageAfterRedirect($message);
	echo "<script>window.location.href='config.form.php';</script>";// revient sur la page
