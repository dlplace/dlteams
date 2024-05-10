SET @modelrgpd_id = (SELECT id FROM `glpi_entities` WHERE name = 'model-rgpd');
SET @adminrgpd_id = (SELECT id FROM `glpi_users` WHERE name = 'admin-rgpd');
SET @profilvuemodele_id = (SELECT id FROM `glpi_profiles` WHERE name = 'Vue-Modele');

DELETE t1 FROM `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`records_id` = t2.`id` AND t2.`entities_id` =  @modelrgpd_id;

DELETE t1 FROM `glpi_plugin_dlteams_concernedpersons_items` as t1 LEFT JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_id` = t2.`id` AND t2.`entities_id` =  @modelrgpd_id WHERE t1.`itemtype` = 'PluginDlteamsRecord';
DELETE t1 FROM `glpi_plugin_dlteams_processeddatas_items` as t1 LEFT JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_id` = t2.`id` AND t2.`entities_id` =  @modelrgpd_id WHERE t1.`itemtype` = 'PluginDlteamsRecord';
DELETE t1 FROM `glpi_plugin_dlteams_legalbasis_items` as t1 LEFT JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_id` = t2.`id` AND t2.`entities_id` =  @modelrgpd_id WHERE  t1.`itemtype` = 'PluginDlteamsRecord' ;
DELETE t1 FROM `glpi_plugin_dlteams_storageperiods_items` as t1 LEFT JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_id` = t2.`id` AND t2.`entities_id` =  @modelrgpd_id WHERE t1.`itemtype` = 'PluginDlteamsRecord';
DELETE t1 FROM `glpi_plugin_dlteams_rightmeasures_items` as t1 LEFT JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_id` = t2.`id` AND t2.`entities_id` =  @modelrgpd_id WHERE t1.`itemtype` = 'PluginDlteamsRecord';
DELETE t1 FROM `glpi_plugin_dlteams_thirdpartycategories_items` as t1 LEFT JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_id` = t2.`id` AND t2.`entities_id` =  @modelrgpd_id WHERE t1.`itemtype` = 'PluginDlteamsRecord';
DELETE t1 FROM `glpi_plugin_dlteams_protectivemeasures_items` as t1 LEFT JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_id` = t2.`id` AND t2.`entities_id` =  @modelrgpd_id WHERE t1.`itemtype` = 'PluginDlteamsRecord';

DELETE FROM `glpi_plugin_dlteams_records` WHERE `entities_id` =  @modelrgpd_id;
DELETE FROM `glpi_plugin_dlteams_concernedpersons` WHERE `entities_id` =  @modelrgpd_id;
DELETE FROM `glpi_plugin_dlteams_processeddatas` WHERE `entities_id` =  @modelrgpd_id;
DELETE FROM `glpi_plugin_dlteams_legalbasis` WHERE `entities_id` =  @modelrgpd_id;
DELETE FROM `glpi_plugin_dlteams_storageperiods` WHERE `entities_id` =  @modelrgpd_id;
DELETE FROM `glpi_plugin_dlteams_thirdpartycategories` WHERE `entities_id` =  @modelrgpd_id;
DELETE FROM `glpi_plugin_dlteams_rightmeasures` WHERE `entities_id` =  @modelrgpd_id;
DELETE FROM `glpi_plugin_dlteams_policieforms` WHERE `entities_id` =  @modelrgpd_id;
DELETE FROM `glpi_plugin_dlteams_protectivemeasures` WHERE `entities_id` =  @modelrgpd_id;
DELETE FROM `glpi_plugin_dlteams_deliverables` WHERE `entities_id` =  @modelrgpd_id;
DELETE FROM `glpi_plugin_dlteams_audits` WHERE `entities_id` =  @modelrgpd_id;
DELETE FROM `glpi_plugin_dlteams_riskassessments` WHERE `entities_id` =  @modelrgpd_id;

DELETE FROM `glpi_profiles_users` WHERE `entities_id` = @modelrgpd_id;
DELETE FROM `glpi_profiles` WHERE `glpi_profiles`.`name` = 'Vue-Modele';
DELETE FROM `glpi_entities` WHERE `glpi_entities`.`name` = 'model-rgpd';
DELETE FROM `glpi_users` WHERE `glpi_users`.`name` = 'admin-rgpd';
DELETE FROM `glpi_profilerights` WHERE `profiles_id` = @profilvuemodele_id;