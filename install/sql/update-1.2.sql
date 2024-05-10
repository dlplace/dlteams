ALTER TABLE `glpi_plugin_dlteams_datacatalogs_items`
    ADD IF NOT EXISTS `plugin_dlteams_userprofiles_id` INT UNSIGNED NOT NULL DEFAULT '0',
	ADD IF NOT EXISTS `items_id1` int unsigned DEFAULT NULL AFTER `itemtype`,
	ADD IF NOT EXISTS `itemtype1` varchar(255) DEFAULT NULL AFTER `items_id1`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_accounts_accounts_items` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `plugin_accounts_accounts_id` int unsigned NOT NULL,
    `itemtype` varchar(255) DEFAULT NULL,
    `items_id` int unsigned NOT NULL,
    `comment` varchar(255) DEFAULT NULL,
    `plugin_dlteams_userprofiles_id` INT UNSIGNED NOT NULL DEFAULT '0',
    `date_creation` timestamp,
    `date_mod` timestamp,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE IF EXISTS `glpi_databases`
    ADD IF NOT EXISTS `comment` varchar(255) DEFAULT NULL AFTER `name`;

ALTER TABLE IF EXISTS `glpi_plugin_dlteams_protectivemeasures_items`
	ADD IF NOT EXISTS `items_id1` int unsigned DEFAULT NULL AFTER `itemtype`,
	ADD IF NOT EXISTS `itemtype1` varchar(255) DEFAULT NULL AFTER `items_id1`;

ALTER TABLE IF EXISTS `glpi_plugin_dlteams_deliverables_items`
	ADD IF NOT EXISTS `items_id1` int unsigned DEFAULT NULL AFTER `itemtype`,
	ADD IF NOT EXISTS `itemtype1` varchar(255) DEFAULT NULL AFTER `items_id1`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_meansofacces` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `date_mod` timestamp,
 `date_creation` timestamp,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='PluginDlteamsMeansOfAcces';
INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
('PluginDlteamsMeansOfAcce', 7, 1, 0),
('PluginDlteamsMeansOfAcce', 3, 3, 0),
('PluginDlteamsMeansOfAcce', 4, 4, 0),
('PluginDlteamsMeansOfAcce', 5, 5, 0) ON DUPLICATE KEY UPDATE `num` = `num`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_meansofacces_items` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `meansofacces_id` int unsigned NOT NULL,
    `itemtype` varchar(255) DEFAULT NULL,
    `items_id` int unsigned NOT NULL,
    `comment` varchar(255) DEFAULT NULL,
    `datacatalog_mandatory` TINYINT(1) NOT NULL DEFAULT '0',
    `date_creation` timestamp,
    `date_mod` timestamp,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

DROP TABLE IF EXISTS `glpi_plugin_dlteams_datacarriers`;
DELETE FROM `glpi_displaypreferences` WHERE `itemtype` = 'PluginDlteamsDatabase';
DELETE FROM `glpi_displaypreferences` WHERE `itemtype` = 'PluginDlteamsDataCarrier';
DROP TABLE IF EXISTS `glpi_plugin_dlteams_datacarriers_items`;

ALTER TABLE IF EXISTS `glpi_plugin_dlteams_datacatalogs_items`
    ADD IF NOT EXISTS `meansofacces_mandatory` TINYINT(1) NOT NULL DEFAULT '0';

ALTER TABLE IF EXISTS `glpi_plugin_dlteams_datacatalogs`
    ADD IF NOT EXISTS `plugin_dlteams_datacarriercategories_id` INT UNSIGNED NULL AFTER `comment`,
	ADD IF NOT EXISTS `plugin_dlteams_datacarriermanagements_id` INT UNSIGNED NULL AFTER `plugin_dlteams_datacarriercategories_id`,
    ADD IF NOT EXISTS `users_id_contact` INT UNSIGNED NULL AFTER `plugin_dlteams_datacarriermanagements_id`,
    ADD IF NOT EXISTS `groups_id_contact` INT UNSIGNED NULL AFTER `users_id_contact`,
    ADD IF NOT EXISTS `suppliers_id_contact` INT UNSIGNED NULL AFTER `groups_id_contact`,
    ADD IF NOT EXISTS `contacts_id_contact` INT UNSIGNED NULL AFTER `suppliers_id_contact`,
	ADD IF NOT EXISTS `is_directoryservice` TINYINT(1) NOT NULL DEFAULT '0' AFTER `contacts_id_contact`,
	ADD IF NOT EXISTS `directory_name` varchar(255) DEFAULT NULL AFTER `is_directoryservice`;

ALTER TABLE IF EXISTS `glpi_plugin_dlteams_deliverables`
	ADD IF NOT EXISTS `projecttasks_id` int unsigned NULL AFTER `content`;

ALTER TABLE IF EXISTS `glpi_plugin_dlteams_records`
    ADD IF NOT EXISTS `users_id_tech` INT UNSIGNED NULL AFTER `content`;

INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
('PluginDlteamsDataCatalog', 3, 3, 0),
('PluginDlteamsDataCatalog', 4, 5, 0),
('PluginDlteamsDataCatalog', 5, 9, 0),
('PluginDlteamsDatacatalog', 6, 3, 0),
('PluginDlteamsDataCatalog', 7, 13, 0),
('PluginDlteamsDataCatalog', 8, 14, 0),
('PluginDlteamsDataCatalog', 10, 6, 0),
('PluginDlteamsDataCatalog', 11, 7, 0),
('PluginDlteamsDatacatalog', 12, 12, 0),
('PluginDlteamsDataCatalog', 101, 16, 0),
('PluginDlteamsDataCatalog', 102, 17, 0),
('PluginDlteamsDataCatalog', 103, 18, 0),
('PluginDlteamsDataCatalog', 104, 19, 0),
('PluginDlteamsDataCatalog', 105, 20, 0),
('PluginDlteamsDataCatalog', 106, 21, 0),
('PluginDlteamsDataCatalog', 118, 15, 0),
('PluginDlteamsDataCatalog', 119, 22, 0) ON DUPLICATE KEY UPDATE `num` = `num`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_deliverables_variables` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL ,
    `content` TEXT NOT NULL ,
    `comment` TEXT NOT NULL ,
    `date_mod` TIMESTAMP NULL ,
    `date_creation` TIMESTAMP NULL,
    `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
    `entities_id` int unsigned NOT NULL DEFAULT 0,
    `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_deliverables_variables_items` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `deliverable_variables_id` int unsigned NOT NULL,
    `items_id` INT unsigned NOT NULL,
    `itemtype` VARCHAR(255) NOT NULL,
    `comment` TEXT NOT NULL DEFAULT '',
    `date_mod` TIMESTAMP NULL,
    `date_creation` TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

ALTER TABLE `glpi_plugin_dlteams_accountkeys_items`
    ADD IF NOT EXISTS `profiles_json` LONGTEXT NULL AFTER `comment`;

ALTER TABLE `glpi_plugin_dlteams_datacatalogs_items`
    ADD IF NOT EXISTS `profiles_json` LONGTEXT NULL AFTER `comment`;

# ALTER TABLE `glpi_plugin_dlteams_accountkeys_items`
#     DROP IF EXISTS `users_id_tech`;

ALTER TABLE `glpi_plugin_dlteams_accountkeys_items`
    ADD IF NOT EXISTS `name` VARCHAR(255) NULL AFTER `comment`,
    ADD IF NOT EXISTS `users_id` INT UNSIGNED NULL AFTER `comment`;

ALTER TABLE `glpi_plugin_dlteams_datacatalogs_items`
    ADD IF NOT EXISTS `is_directory` tinyint(1) NOT NULL DEFAULT 0 AFTER `comment`;

# ALTER TABLE `glpi_plugin_dlteams_datacatalogs_items`
#     CHANGE `profiles_json` `profiles_json` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

ALTER TABLE `glpi_users`
    ADD IF NOT EXISTS `position` VARCHAR(255) NOT NULL DEFAULT '' AFTER `firstname`;

ALTER TABLE `glpi_plugin_dlteams_accountkeys_items`
    ADD IF NOT EXISTS `users_id_tech` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `comment`,
    ADD IF NOT EXISTS `groups_id_tech` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `users_id_tech`,
    ADD IF NOT EXISTS `suppliers_id_tech` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `groups_id_tech`,
    ADD IF NOT EXISTS `contacts_id_tech` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `suppliers_id_tech`;

ALTER TABLE `glpi_users`
    ADD IF NOT EXISTS `print_logo` TINYINT NOT NULL DEFAULT '0' AFTER `comment`,
    ADD IF NOT EXISTS `links_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `print_logo`,
    ADD IF NOT EXISTS `print_comments` TINYINT NOT NULL DEFAULT '0' AFTER `links_id`,
    ADD IF NOT EXISTS `edition_comment` TEXT NOT NULL DEFAULT '' AFTER `print_comments`;

ALTER TABLE `glpi_knowbaseitems_items`
    ADD IF NOT EXISTS `comment` TEXT NOT NULL DEFAULT '' AFTER `items_id`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_auditcategories_items` (
     `id` int unsigned NOT NULL AUTO_INCREMENT,
     `auditcategories_id` int unsigned NOT NULL,
     `items_id` INT unsigned NOT NULL,
     `itemtype` VARCHAR(255) NOT NULL,
     `comment` TEXT NOT NULL DEFAULT '',
     `date_mod` TIMESTAMP NULL,
     `date_creation` TIMESTAMP NULL,
     PRIMARY KEY (`id`)
) ENGINE = InnoDB;

ALTER TABLE `glpi_plugin_dlteams_datacatalogs`
    ADD IF NOT EXISTS `completename` MEDIUMTEXT NOT NULL DEFAULT '' AFTER `comment`;

ALTER TABLE `glpi_plugin_dlteams_policieforms_items` ADD IF NOT EXISTS `plugin_dlteams_storageendactions_id` int unsigned DEFAULT NULL AFTER `entities_id`;
ALTER TABLE `glpi_plugin_dlteams_policieforms_items` ADD IF NOT EXISTS `plugin_dlteams_storagetypes_id` int unsigned DEFAULT NULL AFTER `plugin_dlteams_storageendactions_id`;
ALTER TABLE `glpi_plugin_dlteams_policieforms` ADD IF NOT EXISTS `documents_id` int unsigned DEFAULT NULL AFTER `documentcategories_id`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_procedures` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `content` mediumtext DEFAULT NULL,
 `projecttasks_id` int unsigned NOT NULL DEFAULT 0,
 `comment` mediumtext DEFAULT NULL,
 `print_logo` TINYINT NOT NULL DEFAULT '0',
 `document_name` VARCHAR(255) NULL,
 `links_id` int unsigned NULL,
 `document_title` VARCHAR(255) NULL,
 `document_content` LONGTEXT NULL DEFAULT '',
 `document_comment` LONGTEXT NULL DEFAULT '',
 `publication_folder` VARCHAR(255) NULL,
 `is_firstpage` TINYINT NOT NULL DEFAULT '0',
 `is_comment` TINYINT NOT NULL DEFAULT '0',
 `object_notification` VARCHAR(255) NULL,
 `object_approval` VARCHAR(255) NULL,
 `text_notification` LONGTEXT NULL,
 `text_approval` LONGTEXT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='PluginDlteamsProcedure';
INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
('PluginDlteamsProcedure', 6, 5, 0),
('PluginDlteamsProcedure', 5, 2, 0),
('PluginDlteamsProcedure', 4, 1, 0),
('PluginDlteamsProcedure', 60, 3, 0),
('PluginDlteamsProcedure', 119, 4, 0) ON DUPLICATE KEY UPDATE `num` = `num`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_procedures_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `procedures_id` int unsigned NOT NULL DEFAULT 0,
  `items_id` int unsigned NOT NULL DEFAULT 0,
  `itemtype` varchar(100) DEFAULT NULL,
  `items_id1` int unsigned NOT NULL DEFAULT 0,
  `itemtype1` varchar(100) DEFAULT NULL,
  `comment` mediumtext DEFAULT NULL,
  `email` VARCHAR(255) NULL,
  `text_notification` LONGTEXT NULL,
  `text_approval` LONGTEXT NULL,
  `object_notification` VARCHAR(255) NULL,
  `object_approval` VARCHAR(255) NULL,
  `date_notification` TIMESTAMP NULL DEFAULT NULL,
  `date_approval` TIMESTAMP NULL DEFAULT NULL,
  `approval_token` VARCHAR(255) NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_procedures_sections` (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NULL,
    `tab_name` varchar(255) NULL,
    `comment` longtext NULL,
    `content` longtext NULL,
    `procedures_id` int UNSIGNED NOT NULL DEFAULT 0,
    `timeline_position` int UNSIGNED NOT NULL DEFAULT 0,
    `date_creation` timestamp NULL DEFAULT NULL,
    `date_mod` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`), KEY `procedures_id` (`procedures_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 31/05/2023 : 10:04 GMT
CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_procedures_contents` (
      `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
      `procedure_sections_id` INT UNSIGNED NOT NULL DEFAULT 0,
      `name` VARCHAR(255) NOT NULL,
      `comment` TEXT NOT NULL,
      `content` LONGTEXT NOT NULL,
      `timeline_position` int UNSIGNED NOT NULL DEFAULT 0,
      `date_creation` TIMESTAMP NULL DEFAULT NULL,
      `date_mod` TIMESTAMP NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `procedure_sections_id` (`procedure_sections_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_procedures_variables` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL ,
    `content` TEXT NOT NULL ,
    `comment` TEXT NOT NULL ,
    `date_mod` TIMESTAMP NULL ,
    `date_creation` TIMESTAMP NULL,
    `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
    `entities_id` int unsigned NOT NULL DEFAULT 0,
    `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_activitycategories_items` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `activitycategories_id` int unsigned NOT NULL,
    `itemtype` varchar(255) DEFAULT NULL,
    `items_id` int unsigned NOT NULL,
    `comment` varchar(255) DEFAULT NULL,
    `date_creation` timestamp,
    `date_mod` timestamp,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_procedures_variables_items` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `procedure_variables_id` int unsigned NOT NULL,
    `items_id` INT unsigned NOT NULL,
    `itemtype` VARCHAR(255) NOT NULL,
    `comment` TEXT NOT NULL DEFAULT '',
    `date_mod` TIMESTAMP NULL,
    `date_creation` TIMESTAMP NULL,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB;

ALTER TABLE `glpi_plugin_dlteams_policieforms_items`
    ADD IF NOT EXISTS `mandatory` TINYINT NOT NULL DEFAULT '0' AFTER `comment`;

ALTER TABLE `glpi_plugin_dlteams_users_items`
    ADD IF NOT EXISTS `name` VARCHAR(255) NULL AFTER `comment`;

ALTER TABLE `glpi_plugin_dlteams_users_items`
    ADD IF NOT EXISTS `entities_id` int unsigned NOT NULL DEFAULT 0;

ALTER TABLE `glpi_plugin_dlteams_groups_items`
    ADD IF NOT EXISTS `name` VARCHAR(255) NULL AFTER `comment`;

ALTER TABLE `glpi_plugin_dlteams_groups_items`
    ADD IF NOT EXISTS `entities_id` int unsigned NOT NULL DEFAULT 0;

-- RENAME TABLE `glpi_plugin_dlteams_recordcategories` TO `glpi_plugin_dlteams_activitycategories`;
-- CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_activitycategories` LIKE `glpi_plugin_dlteams_recordcategories`;
-- INSERT INTO `glpi_plugin_dlteams_activitycategories` SELECT * FROM `glpi_plugin_dlteams_recordcategories`;

ALTER TABLE `glpi_plugin_dlteams_records_items`
    ADD IF NOT EXISTS `users_id_tech` INT UNSIGNED NULL AFTER `users_id`,
	ADD IF NOT EXISTS `groups_id` INT UNSIGNED NULL AFTER `users_id_tech`,
	ADD IF NOT EXISTS `groups_id_tech` INT UNSIGNED NULL AFTER `groups_id`,
	ADD IF NOT EXISTS `users_id_actor` varchar(255) NULL AFTER `groups_id_tech`,
	ADD IF NOT EXISTS `name` varchar(255) NULL AFTER `users_id_actor`;

ALTER TABLE `glpi_plugin_dlteams_datacatalogs`
    ADD IF NOT EXISTS `use_other_directory` TINYINT NOT NULL DEFAULT '0' AFTER `comment`,
	ADD IF NOT EXISTS `default_keytype` INT UNSIGNED NULL AFTER `directory_name`;

DROP TABLE IF EXISTS `glpi_plugin_dlteams_storageunittype`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_storageunittypes` (
   `id` int unsigned NOT NULL AUTO_INCREMENT,
   `name` varchar(255) DEFAULT NULL,
   `comment` mediumtext DEFAULT NULL,
   `entities_id` int unsigned NOT NULL DEFAULT 0,
   `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
   `date_mod` timestamp NULL DEFAULT NULL,
   `date_creation` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE = InnoDB;
INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
('PluginDlteamsStorageUnitType', 3, 1, 0),
('PluginDlteamsStorageUnitType', 4, 2, 0),
('PluginDlteamsStorageUnitType', 5, 3, 0) ON DUPLICATE KEY UPDATE `num` = `num`;

ALTER TABLE `glpi_plugin_dlteams_policieforms`
	ADD UNIQUE IF NOT EXISTS `name-entities` (`entities_id`, `name`);

ALTER TABLE `glpi_plugin_dlteams_physicalstorages`
    ADD IF NOT EXISTS `locations_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `comment`,
    ADD IF NOT EXISTS `plugin_dlteams_storageunittypes_id` INT UNSIGNED NOT NULL DEFAULT '0' AFTER `locations_id`;



