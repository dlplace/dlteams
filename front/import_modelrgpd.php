<?php

include("../../../inc/includes.php");
global $DB;

	$DB->request("SET @currententity_id := ".Session::getActiveEntity());
	$DB->request("SET @currentuser_id := ".Session::getLoginUserID());

	// model-rgpd & user & profils & rights creation
	$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
    $rgpdmodel_id = 0;
    if ($result && $DB->numrows($result) > 0) {
        $data = $DB->fetchAssoc($result);
        $rgpdmodel_id = $data['id'];
    }

	$tables = ['glpi_plugin_dlteams_allitems',
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

	header("Refresh:0; url=config.form.php");
	Session::addMessageAfterRedirect("Import ok");
