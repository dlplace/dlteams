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

$querys = [
"ALTER TABLE `glpi_plugin_dlteams_records_items`
    ADD IF NOT EXISTS `users_id_tech` INT UNSIGNED NULL AFTER `users_id`,
	ADD IF NOT EXISTS `groups_id` INT UNSIGNED NULL AFTER `users_id_tech`,
	ADD IF NOT EXISTS `groups_id_tech` INT UNSIGNED NULL AFTER `groups_id`,
	ADD IF NOT EXISTS `users_id_actor` varchar(255) NULL AFTER `groups_id_tech`,
	ADD IF NOT EXISTS `name` varchar(255) NULL AFTER `users_id_actor`;",
"ALTER TABLE `glpi_plugin_dlteams_datacatalogs`
    ADD IF NOT EXISTS `use_other_directory` TINYINT NOT NULL DEFAULT 0 AFTER `comment`,
	ADD IF NOT EXISTS `default_keytype` INT UNSIGNED NULL AFTER `directory_name`;",
"DROP TABLE IF EXISTS `glpi_plugin_dlteams_storageunittype`;",
"CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_storageunittypes` (
   `id` int unsigned NOT NULL AUTO_INCREMENT,
   `name` varchar(255) DEFAULT NULL,
   `comment` mediumtext DEFAULT NULL,
   `entities_id` int unsigned NOT NULL DEFAULT 0,
   `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
   `date_mod` timestamp NULL DEFAULT NULL,
   `date_creation` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE = InnoDB;",
"ALTER TABLE `glpi_plugin_dlteams_policieforms`
	ADD UNIQUE IF NOT EXISTS `name-entities` (`entities_id`, `name`);",
"ALTER TABLE `glpi_plugin_dlteams_physicalstorages`
    ADD IF NOT EXISTS `locations_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `comment`,
    ADD IF NOT EXISTS `plugin_dlteams_storageunittypes_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `locations_id`;",
"ALTER TABLE `glpi_plugin_dlteams_datacarriertypes`
    ADD IF NOT EXISTS `is_deleted` tinyint(1) NOT NULL DEFAULT 0 AFTER `comment`,
    ADD IF NOT EXISTS `date_creation` timestamp NULL DEFAULT NULL AFTER `is_deleted`,
    ADD IF NOT EXISTS `date_mod` timestamp NULL DEFAULT NULL AFTER `date_creation`;",
"ALTER TABLE `glpi_plugin_dlteams_policieforms`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `documents_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_plugin_dlteams_deliverables`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_plugin_dlteams_procedures`
    ADD IF NOT EXISTS `projects_id` int unsigned DEFAULT NULL AFTER `content`;",
"ALTER TABLE `glpi_plugin_dlteams_datacatalogs_items` DROP INDEX IF EXISTS `unicity`;",
"ALTER TABLE `glpi_appliances_items` DROP INDEX IF EXISTS `unicity`;",
"ALTER TABLE `glpi_plugin_dlteams_records`
	DROP IF EXISTS `copy_id`,
	DROP IF EXISTS `copy_entityid`,
	DROP IF EXISTS `copy_date`,
	DROP IF EXISTS `copy_update`;",
"ALTER TABLE `glpi_plugin_dlteams_records`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_plugin_dlteams_concernedpersons`
	DROP IF EXISTS `copy_id`,
	DROP IF EXISTS `copy_entityid`,
	DROP IF EXISTS `copy_date`,
	DROP IF EXISTS `copy_update`;",
"ALTER TABLE `glpi_plugin_dlteams_concernedpersons`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_plugin_dlteams_processeddatas`
	DROP IF EXISTS `copy_id`,
	DROP IF EXISTS `copy_entityid`,
	DROP IF EXISTS `copy_date`,
	DROP IF EXISTS `copy_update`;",
"ALTER TABLE `glpi_plugin_dlteams_processeddatas`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_plugin_dlteams_legalbasis`
	DROP IF EXISTS `copy_id`,
	DROP IF EXISTS `copy_entityid`,
	DROP IF EXISTS `copy_date`,
	DROP IF EXISTS `copy_update`;",
"ALTER TABLE `glpi_plugin_dlteams_legalbasis`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_plugin_dlteams_storageperiods`
	DROP IF EXISTS `copy_id`,
	DROP IF EXISTS `copy_entityid`,
	DROP IF EXISTS `copy_date`,
	DROP IF EXISTS `copy_update`;",
"ALTER TABLE `glpi_plugin_dlteams_storageperiods`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_plugin_dlteams_thirdpartycategories`
	DROP IF EXISTS `copy_id`,
	DROP IF EXISTS `copy_entityid`,
	DROP IF EXISTS `copy_date`,
	DROP IF EXISTS `copy_update`;",
"ALTER TABLE `glpi_plugin_dlteams_thirdpartycategories`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_plugin_dlteams_rightmeasures`
	DROP IF EXISTS `copy_id`,
	DROP IF EXISTS `copy_entityid`,
	DROP IF EXISTS `copy_date`,
	DROP IF EXISTS `copy_update`;",
"ALTER TABLE `glpi_plugin_dlteams_rightmeasures`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_plugin_dlteams_datacatalogs`
	DROP IF EXISTS `copy_id`,
	DROP IF EXISTS `copy_entityid`,
	DROP IF EXISTS `copy_date`,
	DROP IF EXISTS `copy_update`;",
"ALTER TABLE `glpi_plugin_dlteams_datacatalogs`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `date_mod`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_plugin_dlteams_riskassessments`
	DROP IF EXISTS `copy_id`,
	DROP IF EXISTS `copy_entityid`,
	DROP IF EXISTS `copy_date`,
	DROP IF EXISTS `copy_update`;",
"ALTER TABLE `glpi_plugin_dlteams_riskassessments`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `date_mod`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_plugin_dlteams_audits`
	DROP IF EXISTS `copy_id`,
	DROP IF EXISTS `copy_entityid`,
	DROP IF EXISTS `copy_date`,
	DROP IF EXISTS `copy_update`;",
"ALTER TABLE `glpi_plugin_dlteams_audits`	
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `date_mod`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_plugin_dlteams_protectivemeasures`
	DROP IF EXISTS `copy_id`,
	DROP IF EXISTS `copy_entityid`,
	DROP IF EXISTS `copy_date`,
	DROP IF EXISTS `copy_update`;",
"ALTER TABLE `glpi_plugin_dlteams_protectivemeasures`	
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `date_mod`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_plugin_dlteams_datacarriertypes`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `date_mod`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_appliances`
	DROP IF EXISTS `copy_id`,
	DROP IF EXISTS `copy_entityid`,
	DROP IF EXISTS `copy_date`,
	DROP IF EXISTS `copy_update`;",
"ALTER TABLE `glpi_appliances`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `date_mod`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
"ALTER TABLE `glpi_plugin_dlteams_policieforms`
    DROP INDEX IF EXISTS `name-entities`,
    ADD UNIQUE INDEX IF NOT EXISTS `name-entities-is_deleted` (`entities_id`, `name`, `is_deleted`) USING BTREE;",
"ALTER TABLE `glpi_projecttasks`
    ADD IF NOT EXISTS `timeline_position` tinyint(1) NOT NULL DEFAULT 0 AFTER `date_mod`;",
"ALTER TABLE `glpi_tickets`
    ADD IF NOT EXISTS `timeline_position` tinyint(1) NOT NULL DEFAULT 0 AFTER `date_mod`;",
"ALTER TABLE `glpi_plugin_dlteams_datacatalogs`
		CHANGE IF EXISTS `visible_datas` `visible_datas` text DEFAULT NULL,
		CHANGE IF EXISTS `profile_rights` `profile_rights` text DEFAULT NULL,		
		CHANGE IF EXISTS `access_means` `access_means` text DEFAULT NULL;",
"ALTER TABLE `glpi_plugin_dlteams_datacarriertypes_items`
		CHANGE IF EXISTS `datacarriertypes_id` `datacarriertypes_id` int unsigned DEFAULT NULL,
		CHANGE IF EXISTS `items_id` `items_id` int unsigned DEFAULT NULL;",
"DROP TABLE IF EXISTS `glpi_plugin_dlteams_deliverablevariables`;",
"ALTER TABLE `glpi_plugin_dlteams_accountkeys_items`
    ADD IF NOT EXISTS `groups_id` INT UNSIGNED NULL AFTER `users_id_tech`,
    ADD IF NOT EXISTS `groups_id_tech` INT UNSIGNED NULL AFTER `groups_id`;",
"DROP TABLE IF EXISTS `glpi_plugin_dlteams_records_externals`;",
"DROP TABLE IF EXISTS `glpi_plugin_dlteams_records_legalbasis`;",
"DROP TABLE IF EXISTS `glpi_plugin_dlteams_records_personalanddatacategories`;",
"DROP TABLE IF EXISTS `glpi_plugin_dlteams_records_storages`;",
"ALTER TABLE `glpi_plugin_dlteams_records`
    DROP COLUMN IF EXISTS `plugin_dlteams_activitycategories_id`,
	DROP COLUMN IF EXISTS `zzz_violation_impact`,
    DROP COLUMN IF EXISTS `zzz_violation_impact_level`,
	DROP COLUMN IF EXISTS `storage_medium`,
	DROP COLUMN IF EXISTS `pia_required`,
	DROP COLUMN IF EXISTS `pia_status`,
	DROP COLUMN IF EXISTS `conservation_time`,
	DROP COLUMN IF EXISTS `archive_time`,
	DROP COLUMN IF EXISTS `archive_required`;",
"ALTER TABLE `glpi_projecttasks`
    ADD IF NOT EXISTS `completename` MEDIUMTEXT DEFAULT NULL AFTER `comment`;",
"UPDATE `glpi_projecttasks` SET `completename` = `name` WHERE `completename` IS NULL;",
"ALTER TABLE `glpi_tickettasks`
    ADD IF NOT EXISTS `tickettasks_id` INT UNSIGNED NULL AFTER `sourceof_items_id`,
	ADD IF NOT EXISTS `estimate_duration` int(11) unsigned NOT NULL DEFAULT 0 AFTER `tickettasks_id`;",
"ALTER TABLE `glpi_users` 
	ADD IF NOT EXISTS `microsoft_guid` VARCHAR(30) NULL DEFAULT NULL AFTER `timeline_date_format`;",
"ALTER TABLE `glpi_plugin_dlteams_accountkeys_items`
    ADD IF NOT EXISTS `is_directory` tinyint(1) NOT NULL DEFAULT 0 AFTER `comment`;",
"ALTER TABLE `glpi_plugin_dlteams_activitycategories`
    DROP COLUMN IF EXISTS `number`,
	ADD IF NOT EXISTS `plugin_dlteams_activitycategories_id` INT UNSIGNED NULL AFTER `comment`,
	ADD IF NOT EXISTS `completename` MEDIUMTEXT DEFAULT NULL AFTER `plugin_dlteams_activitycategories_id`;",
"ALTER TABLE `glpi_plugin_dlteams_records`
    ADD IF NOT EXISTS `plugin_dlteams_activitycategories_id` INT UNSIGNED NULL AFTER `links_id`,
    ADD IF NOT EXISTS `forms_id` INT UNSIGNED NULL AFTER `plugin_dlteams_activitycategories_id`,
	ADD IF NOT EXISTS `parentnumber` INT UNSIGNED DEFAULT 1 AFTER `number`,
	ADD IF NOT EXISTS `completenumber` DECIMAL (10,2) AFTER `parentnumber`;",
"UPDATE `glpi_plugin_dlteams_records` SET `parentnumber` = 1 WHERE `parentnumber` IS NULL;",
"UPDATE `glpi_plugin_dlteams_records` SET `completenumber` = number + parentnumber/100 WHERE `completenumber` IS NULL;",
"CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_vehicles` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` INT unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `comment` mediumtext DEFAULT NULL,
 `manufacturers_id` INT UNSIGNED NULL,
 `peripheralmodels_id` INT UNSIGNED NULL,
 `motor_type` varchar(255) NOT NULL DEFAULT '',
 `doublekey` TINYINT(1) NOT NULL DEFAULT 0,
 `plugin_dlteams_vehicletypes_id` int unsigned NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `groups_id` INT unsigned NOT NULL DEFAULT 0,
 `taxbenefit` decimal (5,2) DEFAULT NULL,
 `otherbenefit` decimal (5,2) DEFAULT NULL,
 `nb` TINYINT(1) NOT NULL DEFAULT 1,
 `buyingdate` timestamp NULL DEFAULT NULL,
 `guarantee` INT unsigned NULL,
 `rentalamount` decimal (5,2) DEFAULT NULL,
 `firstrental` decimal (5,2) DEFAULT NULL,
 `lastrental` decimal (5,2) DEFAULT NULL,
 `soldprice` decimal (5,2) DEFAULT NULL,
 `typeofpurchase` varchar(255) NOT NULL DEFAULT '',
 `rentalperiod` varchar(255) NOT NULL DEFAULT '',
 `maintenance` decimal (5,2) DEFAULT NULL,
 `withtax` TINYINT(1) DEFAULT NULL,
 `suppliers_id` INT unsigned DEFAULT NULL,
 `locations_id` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsVehicle';",
"CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_vehicletypes` (
   `id` int unsigned NOT NULL AUTO_INCREMENT,
   `name` varchar(255) DEFAULT NULL,
   `comment` mediumtext DEFAULT NULL,
   `entities_id` int unsigned NOT NULL DEFAULT 0,
   `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
   `date_mod` timestamp NULL DEFAULT NULL,
   `date_creation` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE = InnoDB;",
"ALTER TABLE IF EXISTS `glpi_plugin_dlteams_policieforms_items`
    ADD IF NOT EXISTS `items_id1` int unsigned DEFAULT NULL AFTER `itemtype`,
    ADD IF NOT EXISTS `itemtype1` varchar(255) DEFAULT NULL AFTER `items_id1`;",
"CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_suppliers_items` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `suppliers_id` int UNSIGNED NOT NULL DEFAULT 0,
  `itemtype` varchar(255) NOT NULL,
  `items_id` int UNSIGNED NOT NULL DEFAULT 0,
  `itemtype1` varchar(255) DEFAULT NULL,
  `items_id1` int unsigned NOT NULL DEFAULT 0,
  `comment` varchar(255) DEFAULT NULL,
  `foreign_id` int UNSIGNED NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL,
  `date_mod` timestamp NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
"CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_sendingreasons_items` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `sendingreasons_id` int UNSIGNED NOT NULL,
  `itemtype` varchar(255) NOT NULL,
  `items_id` int UNSIGNED NOT NULL DEFAULT 0,
  `itemtype1` varchar(255) NOT NULL,
  `items_id1` int unsigned NOT NULL DEFAULT 0,
  `comment` varchar(255) NOT NULL,
  `foreign_id` int UNSIGNED NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
    "CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_accessopenings` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` INT unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `comment` mediumtext DEFAULT NULL,
 `locations_id` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsAccessOpening';",
"ALTER TABLE IF EXISTS `glpi_plugin_dlteams_audits`
	RENAME COLUMN IF EXISTS `plugin_dlregister_auditcategories_id` TO `plugin_dlteams_auditcategories_id`;",
"CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_accessopenings_items` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `accessopenings_id` int UNSIGNED NOT NULL DEFAULT 0,
  `itemtype` varchar(255) NOT NULL,
  `items_id` int UNSIGNED NOT NULL DEFAULT 0,
  `itemtype1` varchar(255) DEFAULT NULL,
  `items_id1` int unsigned NOT NULL DEFAULT 0,
  `comment` varchar(255) DEFAULT NULL,
  `foreign_id` int UNSIGNED NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL,
  `date_mod` timestamp NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
"CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_locations_items` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `locations_id` int UNSIGNED NOT NULL DEFAULT 0,
  `itemtype` varchar(255) NOT NULL,
  `items_id` int UNSIGNED NOT NULL DEFAULT 0,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL,
  `date_mod` timestamp NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
"ALTER TABLE `glpi_itilfollowups` ADD IF NOT EXISTS `parent_comment_id` INT UNSIGNED NULL AFTER `content`;",
"ALTER TABLE `glpi_plugin_dlteams_records` ADD IF NOT EXISTS `is_grouping` TINYINT NOT NULL DEFAULT '0' AFTER `content`;",
"ALTER TABLE `glpi_itilfollowups` ADD IF NOT EXISTS `date_creation` TIMESTAMP NULL AFTER `content`, ADD IF NOT EXISTS `date_mod` TIMESTAMP NULL AFTER `date_creation`;",
"ALTER TABLE `glpi_itilfollowups` ADD IF NOT EXISTS `entities_id` INT UNSIGNED NOT NULL DEFAULT 0;",
    "ALTER TABLE `glpi_plugin_dlteams_rightmeasures` ADD IF NOT EXISTS `rightmeasurecategories` LONGTEXT NULL AFTER `comment`;",
    "ALTER TABLE `glpi_plugin_dlteams_records` ADD IF NOT EXISTS `users_id_auditor` int(11) unsigned DEFAULT NULL AFTER `comment`;",
    "ALTER TABLE `glpi_plugin_dlteams_records` ADD IF NOT EXISTS `targettickets_id` int(11) unsigned DEFAULT 0 AFTER `users_id_auditor`;",
    "ALTER TABLE `glpi_plugin_dlteams_records` ADD IF NOT EXISTS `deliverables_id` int(11) unsigned DEFAULT 0 AFTER `targettickets_id`;",
    "ALTER TABLE `glpi_plugin_dlteams_policieforms_items` DROP INDEX IF EXISTS `unicity`;",
    "ALTER TABLE glpi_plugin_dlteams_policieforms_items ADD UNIQUE INDEX unicity (policieforms_id, itemtype, items_id, itemtype1, items_id1);",
"CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_projecttasks_items` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `projecttasks_id` int UNSIGNED NOT NULL DEFAULT 0,
  `itemtype` varchar(255) NOT NULL,
  `items_id` int UNSIGNED NOT NULL DEFAULT 0,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL,
  `date_mod` timestamp NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
    "CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_rgpdadequacies` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` INT unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `comment` mediumtext DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsRgpdAdequacy';",
    "CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_rgpdadequacies_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `rgpdadequacies_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'RELATION to various tables, according to itemtype1 (id)RELATION to various tables, according to itemtype1 (id)',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'RELATION to various tables, according to itemtype2 (id)',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'see .class.php file',
`items_id1` int unsigned NOT NULL DEFAULT 0 COMMENT 'RELATION to various tables, according to itemtype2 (id)',
`itemtype1` varchar(100) DEFAULT NULL COMMENT 'see .class.php file',
  `comment` mediumtext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `date_mod` (`date_mod`),
   KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;",
"ALTER TABLE `glpi_documents` ADD IF NOT EXISTS `policieforms_id` int(11) unsigned DEFAULT NULL AFTER `comment`;",
"ALTER TABLE `glpi_documents` ADD IF NOT EXISTS `deliverables_id` int(11) unsigned DEFAULT NULL AFTER `comment`;",
"UPDATE `glpi_configs` SET `value` = 255 WHERE `name` = 'url_maxlength' ",
"ALTER TABLE `glpi_plugin_dlteams_datacatalogs`	ADD IF NOT EXISTS `default_format` mediumtext DEFAULT NULL AFTER `directory_name`",
    "ALTER TABLE `glpi_plugin_dlteams_deliverables` ADD IF NOT EXISTS `documents_id` int(11) unsigned DEFAULT 0 AFTER `comment`;",
    "ALTER TABLE `glpi_plugin_dlteams_activitycategories`
    ADD IF NOT EXISTS `completename` MEDIUMTEXT DEFAULT NULL AFTER `comment`;",
    "ALTER TABLE `glpi_plugin_dlteams_activitycategories` ADD IF NOT EXISTS `activitycategories_id` int(11) unsigned DEFAULT NULL AFTER `comment`;",
    "ALTER TABLE `glpi_plugin_dlteams_activitycategories`
    ADD IF NOT EXISTS `level` INT(11) NOT NULL DEFAULT '0';",
    "ALTER TABLE `glpi_plugin_dlteams_activitycategories`
    ADD IF NOT EXISTS `ancestors_cache` LONGTEXT NULL,
    ADD IF NOT EXISTS `sons_cache` LONGTEXT NULL;",
    "ALTER TABLE `glpi_plugin_dlteams_policieforms`
    ADD IF NOT EXISTS `activitycategories_idx` LONGTEXT NULL AFTER `comment`;",
    "ALTER TABLE `glpi_groups`
	ADD IF NOT EXISTS `locations_id` INT UNSIGNED DEFAULT 1 AFTER `comment`,
	ADD IF NOT EXISTS `email` VARCHAR(255) NULL AFTER `comment`,
	ADD IF NOT EXISTS `phonenumber` VARCHAR(255) NULL AFTER `comment`,
	ADD IF NOT EXISTS `acronym` VARCHAR(255) NULL AFTER `comment`,
	ADD IF NOT EXISTS `number` INT UNSIGNED DEFAULT 1 AFTER `comment`,
	ADD IF NOT EXISTS `parentnumber` INT UNSIGNED DEFAULT 1 AFTER `number`,
	ADD IF NOT EXISTS `completenumber` DECIMAL (10,2) AFTER `parentnumber`;",
    "ALTER TABLE `glpi_plugin_dlteams_records`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `date_mod`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;",
    "ALTER TABLE glpi_plugin_dlteams_records MODIFY collect_comment TEXT;"
//    "CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_tickets_items` (
//  `id` int unsigned NOT NULL AUTO_INCREMENT,
//  `tickets_id` int unsigned NOT NULL DEFAULT 0,
//  `items_id` int unsigned NOT NULL DEFAULT 0,
//  `itemtype` varchar(100) DEFAULT NULL,
//  `items_id1` int unsigned NOT NULL DEFAULT 0,
//  `itemtype1` varchar(100) DEFAULT NULL,
//  `comment` mediumtext DEFAULT NULL,
//  `email` VARCHAR(255) NULL,
//  `text_notification` LONGTEXT NULL,
//  `object_notification` VARCHAR(255) NULL,
//  `date_notification` TIMESTAMP NULL DEFAULT NULL,
//  `date_creation` timestamp NULL DEFAULT NULL,
//  `date` timestamp NULL DEFAULT NULL,
//  `entities_id` int unsigned NOT NULL DEFAULT 0,
//  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
//  `date_mod` timestamp NULL DEFAULT NULL,
//   PRIMARY KEY (`id`)
//) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;"

];
$i = 1;
global $DB;
foreach ($querys as $query) {
		// echo $query . "<br>";
		$result = $DB->query($query) or die("Erreur". $DB->error());
		if ($DB->error) {
			try {    
				throw new Exception("MySQL error $DB->error <br> Query:<br> $query", $msqli->errno);    
			} catch(Exception $e ) {
			echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
			echo nl2br($e->getTraceAsString());
			}
		}
		echo($i . "..."); $i++;
}

// header("Refresh:0; url=config.form.php");
// Html::back();


