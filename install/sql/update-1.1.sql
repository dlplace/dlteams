
CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_groups_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `groups_id` int unsigned NOT NULL DEFAULT 0,
  `itemtype` varchar(255) DEFAULT NULL,
  `items_id` int unsigned NOT NULL DEFAULT 0,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_users_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `users_id` int unsigned NOT NULL,
  `itemtype` varchar(255) DEFAULT NULL,
  `items_id` int unsigned NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `glpi_plugin_dlteams_concernedpersons_items` ADD IF NOT EXISTS `items_id1` int unsigned NOT NULL DEFAULT 0;
ALTER TABLE `glpi_plugin_dlteams_concernedpersons_items` ADD IF NOT EXISTS `itemtype1` varchar(255) DEFAULT NULL;
ALTER TABLE `glpi_plugin_dlteams_concernedpersons_items` ADD IF NOT EXISTS `json` tinytext DEFAULT NULL;

ALTER TABLE `glpi_plugin_dlteams_processeddatas_items` ADD IF NOT EXISTS `items_id1` int unsigned NOT NULL DEFAULT 0 AFTER `itemtype`;
ALTER TABLE `glpi_plugin_dlteams_processeddatas_items` ADD IF NOT EXISTS `itemtype1` varchar(255) DEFAULT NULL AFTER `items_id1`;

ALTER TABLE `glpi_plugin_dlteams_records_items` ADD IF NOT EXISTS `entities_id` int unsigned NOT NULL DEFAULT 0 AFTER `users_id`;
ALTER TABLE `glpi_plugin_dlteams_records_items` ADD IF NOT EXISTS `itemtype1` varchar(255) DEFAULT NULL AFTER `items_id`;
ALTER TABLE `glpi_plugin_dlteams_records_items` ADD IF NOT EXISTS `items_id1` int unsigned DEFAULT NULL AFTER `itemtype1`;
ALTER TABLE `glpi_plugin_dlteams_records_items` ADD IF NOT EXISTS `json` tinytext DEFAULT NULL AFTER `comment`;
ALTER TABLE `glpi_plugin_dlteams_records_items` ADD IF NOT EXISTS `timeline_position` tinyint(1) DEFAULT NULL AFTER `json`;
ALTER TABLE `glpi_plugin_dlteams_records_items` ADD IF NOT EXISTS `plugin_dlteams_storageendactions_id` int unsigned DEFAULT NULL AFTER `entities_id`;
ALTER TABLE `glpi_plugin_dlteams_records_items` ADD IF NOT EXISTS `plugin_dlteams_storagetypes_id` int unsigned DEFAULT NULL;
ALTER TABLE `glpi_plugin_dlteams_records_items` ADD IF NOT EXISTS `mandatory` tinyint(4) NOT NULL DEFAULT 0;
ALTER TABLE `glpi_plugin_dlteams_records_items` DROP IF EXISTS `groups_id` ;
ALTER TABLE `glpi_plugin_dlteams_records_items` DROP IF EXISTS `suppliers_id`;
ALTER TABLE `glpi_plugin_dlteams_records_items` DROP IF EXISTS `plugin_dlteams_thirdpartycategories_id`;

-- 31/05/2023 : 09:20 GMT
CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_deliverables_sections` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NULL,
    `tab_name` varchar(255) NOT NULL DEFAULT '',
    `comment` longtext NULL,
    `content` longtext NULL,
    `deliverables_id` int unsigned NOT NULL DEFAULT 0,
    `timeline_position` int unsigned NOT NULL DEFAULT 0,
    `date_creation` timestamp NULL DEFAULT NULL,
    `date_mod` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`), KEY `deliverables_id` (`deliverables_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 31/05/2023 : 10:04 GMT
CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_deliverables_contents` (
      `id` int unsigned NOT NULL AUTO_INCREMENT,
      `deliverable_sections_id` INT unsigned NOT NULL DEFAULT 0,
      `name` VARCHAR(255) NOT NULL DEFAULT '',
      `comment` TEXT NOT NULL,
      `content` LONGTEXT NOT NULL,
      `timeline_position` int unsigned NOT NULL DEFAULT 0,
      `date_creation` TIMESTAMP NULL DEFAULT NULL,
      `date_mod` TIMESTAMP NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `deliverable_sections_id` (`deliverable_sections_id`),
      CONSTRAINT `fk_deliverable_sections`
          FOREIGN KEY (`deliverable_sections_id`)
              REFERENCES `glpi_plugin_dlteams_deliverables_sections` (`id`)
) ENGINE = InnoDB;

-- 17/06 adding comment field on documents
ALTER TABLE `glpi_documents_items` 
	ADD COLUMN IF NOT EXISTS `comment` varchar(255) NULL ;

ALTER TABLE `glpi_plugin_dlteams_deliverables_items`
    ADD COLUMN IF NOT EXISTS `email` VARCHAR(255) NULL AFTER `comment`,
	ADD COLUMN IF NOT EXISTS `approval_token` VARCHAR(255) NULL AFTER `comment`,
    ADD COLUMN IF NOT EXISTS `text_notification` LONGTEXT NULL AFTER `email`,
    ADD COLUMN IF NOT EXISTS `text_approval` LONGTEXT NULL AFTER `text_notification`,
    ADD COLUMN IF NOT EXISTS `object_notification` VARCHAR(255) NOT NULL AFTER `text_approval`,
    ADD COLUMN IF NOT EXISTS `object_approval` VARCHAR(255) NOT NULL AFTER `object_notification`,
    ADD COLUMN IF NOT EXISTS `date_notification` TIMESTAMP NULL DEFAULT NULL AFTER `object_approval`,
    ADD COLUMN IF NOT EXISTS `date_approval` TIMESTAMP NULL DEFAULT NULL ;

ALTER TABLE `glpi_plugin_dlteams_deliverables_items`
    DROP IF EXISTS `approval_request`;

ALTER TABLE `glpi_plugin_dlteams_storageperiods` CHANGE `url` `url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;
ALTER TABLE `glpi_plugin_dlteams_storageperiods` CHANGE `url2` `url2` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

ALTER TABLE `glpi_plugin_dlteams_deliverables`
    ADD COLUMN IF NOT EXISTS `object_notification` VARCHAR(255) NULL AFTER `comment`,
    ADD COLUMN IF NOT EXISTS `object_approval` VARCHAR(255) NULL AFTER `object_notification`,
    ADD COLUMN IF NOT EXISTS `text_notification` LONGTEXT NULL AFTER `object_approval`,
    ADD COLUMN IF NOT EXISTS `text_approval` LONGTEXT NULL AFTER `text_notification`;

-- 11/07 adding field to make categorie for  records documents & deliverables documents
ALTER TABLE `glpi_plugin_dlteams_policieforms`
	ADD COLUMN IF NOT EXISTS `documentcategories_id` int unsigned NOT NULL DEFAULT 0;


-- 31/05/2023 : 09:20 GMT
CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_deliverables_sections` (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NULL,
    `tab_name` varchar(255) NOT NULL,
    `comment` longtext NULL,
    `content` longtext NULL,
    `deliverables_id` int UNSIGNED NOT NULL DEFAULT 0,
    `timeline_position` int UNSIGNED NOT NULL DEFAULT 0,
    `date_creation` timestamp NULL DEFAULT NULL,
    `date_mod` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`), KEY `deliverables_id` (`deliverables_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 31/05/2023 : 10:04 GMT
CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_deliverables_contents` (
      `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
      `deliverable_sections_id` INT UNSIGNED NOT NULL DEFAULT 0,
      `name` VARCHAR(255) NOT NULL,
      `comment` TEXT NOT NULL,
      `content` LONGTEXT NOT NULL,
      `timeline_position` int UNSIGNED NOT NULL DEFAULT 0,
      `date_creation` TIMESTAMP NULL DEFAULT NULL,
      `date_mod` TIMESTAMP NULL DEFAULT NULL,
      PRIMARY KEY (`id`),
      KEY `deliverable_sections_id` (`deliverable_sections_id`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

 -- 21/06/2023 : 12:18 GMT
-- ALTER TABLE `glpi_plugin_dlteams_thirdpartycategories_items` DROP FOREIGN KEY IF EXISTS `foreign_id`;
-- ALTER TABLE `glpi_plugin_dlteams_thirdpartycategories_items` DROP COLUMN IF EXISTS `foreign_id`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_links_items` (
    `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `links_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
    `items_id` int UNSIGNED NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
    `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
    `comment` mediumtext DEFAULT NULL,
    `json` tinytext DEFAULT NULL,
    `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
    `date_creation` timestamp NULL DEFAULT NULL,
    `date` timestamp NULL DEFAULT NULL,
    `entities_id` int UNSIGNED NOT NULL DEFAULT 0,
    `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
    `date_mod` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `glpi_plugin_dlteams_deliverables_items`
    ADD IF NOT EXISTS `approval_request` TINYINT NOT NULL DEFAULT 0 AFTER `comment`,
    ADD IF NOT EXISTS `email` VARCHAR(255) NULL AFTER `approval_request`;

-- 11/07 adding categories for records documents & deliverables documents
ALTER TABLE `glpi_plugin_dlteams_policieforms`
ADD COLUMN IF NOT EXISTS `documentcategories_id` int unsigned NOT NULL DEFAULT 0;

ALTER TABLE `glpi_plugin_dlteams_deliverables_items`
    ADD COLUMN IF NOT EXISTS `approval_token` VARCHAR(255) NULL AFTER `comment`;

ALTER TABLE `glpi_knowbaseitems`
ADD COLUMN IF NOT EXISTS `entities_id` int UNSIGNED NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS `is_recursive` tinyint(1) NOT NULL DEFAULT 0;

-- renommage des tables database en datacarrier + changement de class
-- si on rename 1/on doit aller dans les update ou se trouve le CREATE 2/placer le rename avant le create 3/ placer le create 
RENAME TABLE IF EXISTS `glpi_plugin_dlteams_databases` TO `glpi_plugin_dlteams_datacarriers`;
UPDATE `glpi_plugin_dlteams_records_items` SET `itemtype` = 'PluginDlteamsDataCarrier' WHERE `itemtype` = 'PluginDlteamsDatabase';
RENAME TABLE IF EXISTS `glpi_plugin_dlteams_databasecategories` TO `glpi_plugin_dlteams_datacarriercategories`;
UPDATE `glpi_displaypreferences` SET `itemtype` = 'PluginDlteamsDataCarrierCategory' WHERE `itemtype` = 'PluginDlteamsDatabaseCategory';
RENAME TABLE IF EXISTS `glpi_plugin_dlteams_databasehostings` TO `glpi_plugin_dlteams_datacarrierhostings`;
UPDATE `glpi_displaypreferences` SET `itemtype` = 'PluginDlteamsDataCarrierHosting' WHERE `itemtype` = 'PluginDlteamsDatabaseHosting';
RENAME TABLE IF EXISTS `glpi_plugin_dlteams_databasemanagements` TO `glpi_plugin_dlteams_datacarriermanagements`;
UPDATE `glpi_displaypreferences` SET `itemtype` = 'PluginDlteamsDataCarrierManagement' WHERE `itemtype` = 'PluginDlteamsDatabaseManagement';
RENAME TABLE IF EXISTS `glpi_plugin_dlteams_databasetypes` TO `glpi_plugin_dlteams_datacarriertypes`;
UPDATE `glpi_displaypreferences` SET `itemtype` = 'PluginDlteamsDataCarrierType' WHERE `itemtype` = 'PluginDlteamsDatabaseType';
UPDATE `glpi_plugin_dlteams_datacatalogs_items` SET `itemtype` = 'PluginDlteamsDataCarrierType' WHERE `itemtype` = 'PluginDlteamsDatabaseType';
RENAME TABLE IF EXISTS `glpi_plugin_dlteams_databases_items` TO `glpi_plugin_dlteams_datacarriers_items`;

ALTER TABLE `glpi_plugin_dlteams_storageperiods` 
	CHANGE IF EXISTS `url` `url` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;
ALTER TABLE `glpi_plugin_dlteams_storageperiods` 
	CHANGE IF EXISTS `url2` `url2` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

ALTER TABLE `glpi_plugin_dlteams_deliverables`
    ADD IF NOT EXISTS `text_notification` VARCHAR(255) NULL AFTER `comment`,
    ADD IF NOT EXISTS `text_approval` VARCHAR(255) NULL AFTER `text_notification`;

ALTER TABLE `glpi_plugin_dlteams_deliverables`
    ADD IF NOT EXISTS `object_notification` VARCHAR(255) NULL AFTER `comment`,
    ADD IF NOT EXISTS `object_approval` VARCHAR(255) NULL AFTER `object_notification`;

ALTER TABLE `glpi_plugin_dlteams_deliverables_items`
    DROP IF EXISTS `approval_request`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_contacts_items` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `contacts_id` int unsigned NOT NULL,
    `itemtype` varchar(255) DEFAULT NULL,
    `items_id` int unsigned NOT NULL,
    `comment` varchar(255) DEFAULT NULL,
    `date_creation` TIMESTAMP NULL DEFAULT NULL,
    `date_mod` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `glpi_plugin_dlteams_deliverables`
    CHANGE IF EXISTS `text_notification` `text_notification` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

ALTER TABLE `glpi_plugin_dlteams_deliverables`
    CHANGE IF EXISTS `text_approval` `text_approval` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

ALTER TABLE `glpi_plugin_dlteams_deliverables`
    ADD IF NOT EXISTS `is_firstpage` TINYINT NOT NULL DEFAULT '0' AFTER `comment`,
    ADD IF NOT EXISTS `is_comment` TINYINT NOT NULL DEFAULT '0' AFTER `is_firstpage`;

ALTER TABLE `glpi_plugin_dlteams_deliverables`
    ADD IF NOT EXISTS `document_title` VARCHAR(255) NULL AFTER `comment`,
    ADD IF NOT EXISTS `document_name` VARCHAR(255) NULL AFTER `comment`, # added
    ADD IF NOT EXISTS `publication_folder` VARCHAR(255) NULL AFTER `document_title`;

ALTER TABLE `glpi_plugin_dlteams_deliverables`
    ADD IF NOT EXISTS `links_id` INT UNSIGNED NULL AFTER `comment`;

ALTER TABLE `glpi_appliances_items`
    ADD IF NOT EXISTS `comment` varchar(255) DEFAULT NULL AFTER `itemtype`;

ALTER TABLE IF EXISTS `glpi_plugin_accounts_accounts_items`
    ADD IF NOT EXISTS `comment` varchar(255) DEFAULT NULL AFTER `itemtype`;

ALTER TABLE IF EXISTS `glpi_databases`
    ADD IF NOT EXISTS `comment` varchar(255) DEFAULT NULL AFTER `name`;

CREATE TABLE IF NOT EXISTS `glpi_databases_items` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `databases_id` int unsigned NOT NULL DEFAULT 0,
    `itemtype` varchar(255) DEFAULT NULL,
    `items_id` int unsigned NOT NULL DEFAULT 0,
    `comment` varchar(255) DEFAULT NULL,
    `date_creation` timestamp NULL DEFAULT NULL,
    `date_mod` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_accountkeys` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) DEFAULT NULL,
 `plugin_dlteams_keytypes_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_datacatalogs_id` int unsigned NOT NULL DEFAULT 0,
 `comment` mediumtext DEFAULT NULL,
 `date_mod` timestamp,
 `date_creation` timestamp,
 `users_id` INT UNSIGNED NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='PluginDlteamsAccountKey';
INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
('PluginDlteamsAccountKey', 6, 2, 0),
('PluginDlteamsAccountKey', 7, 1, 0),
('PluginDlteamsAccountKey', 3, 3, 0) ON DUPLICATE KEY UPDATE `num` = `num`;


CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_accountkeys_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `accountkeys_id` int unsigned NOT NULL DEFAULT 0,
  `items_id` int unsigned NOT NULL DEFAULT 0,
  `itemtype` varchar(100) DEFAULT NULL,
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NULL,
  `date_creation` timestamp,
  `date` timestamp,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp,
   PRIMARY KEY (`id`),
   KEY `accountkeys_id` (`accountkeys_id`),
   KEY `date_mod` (`items_id`),
   KEY `date_creation` (`itemtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_dlteams_copies`;

ALTER TABLE `glpi_plugin_dlteams_deliverables`
    ADD IF NOT EXISTS `document_content` LONGTEXT NULL DEFAULT '' AFTER `document_title`,
    ADD IF NOT EXISTS `document_comment` LONGTEXT NULL DEFAULT '' AFTER `document_content`;

# 28/07/2023 08:25 GMT
ALTER TABLE `glpi_plugin_dlteams_deliverables_items`
    CHANGE `comment` `comment` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL;

ALTER TABLE `glpi_plugin_dlteams_users_items`
    CHANGE `comment` `comment` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;
ALTER TABLE `glpi_plugin_dlteams_contacts_items`
    CHANGE `comment` `comment` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL;

#01/08/2023 10:11GMT
ALTER TABLE `glpi_plugin_dlteams_deliverables_items`
    CHANGE `object_notification` `object_notification` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;
ALTER TABLE `glpi_plugin_dlteams_deliverables_items`
    CHANGE `object_approval` `object_approval` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

ALTER TABLE `glpi_plugin_dlteams_deliverables`
    ADD IF NOT EXISTS `print_logo` TINYINT NOT NULL DEFAULT '0' AFTER `comment`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_networkequipments_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `networkequipments_id` int unsigned NOT NULL,
  `itemtype` varchar(255) DEFAULT NULL,
  `items_id` int unsigned NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_trainingsessions` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `date_creation` timestamp NULL DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `reference` varchar(255) DEFAULT NULL,
 `public` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `begin_date` timestamp NULL DEFAULT NULL,
 `end_date` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='PluginDlteamsTrainingSession';
INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
('PluginDlteamsTrainingSession', 7, 1, 0),
('PluginDlteamsTrainingSession', 3, 2, 0),
('PluginDlteamsTrainingSession', 6, 3, 0),
('PluginDlteamsTrainingSession', 8, 4, 0),
('PluginDlteamsTrainingSession', 9, 5, 0),
('PluginDlteamsTrainingSession', 10, 6, 0),
('PluginDlteamsTrainingSession', 11, 7, 0),
('PluginDlteamsTrainingSession', 4, 8, 0) ON DUPLICATE KEY UPDATE `num` = `num`;
 -- `reference` varchar(255) DEFAULT 'présentielle site/distant, elearning téléréunion/libre',

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_trainingsessions_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `trainingsessions_id` int unsigned NOT NULL,
  `itemtype` varchar(255) DEFAULT NULL,
  `items_id` int unsigned NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_printers_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `printers_id` int unsigned NOT NULL,
  `itemtype` varchar(255) DEFAULT NULL,
  `items_id` int unsigned NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_peripherals_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `peripherals_id` int unsigned NOT NULL,
  `itemtype` varchar(255) DEFAULT NULL,
  `items_id` int unsigned NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_peripherals_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `peripherals_id` int unsigned NOT NULL,
  `itemtype` varchar(255) DEFAULT NULL,
  `items_id` int unsigned NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_phones_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `phones_id` int unsigned NOT NULL,
  `itemtype` varchar(255) DEFAULT NULL,
  `items_id` int unsigned NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_racks_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `racks_id` int unsigned NOT NULL,
  `itemtype` varchar(255) DEFAULT NULL,
  `items_id` int unsigned NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_pdus_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `pdus_id` int unsigned NOT NULL,
  `itemtype` varchar(255) DEFAULT NULL,
  `items_id` int unsigned NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_datacenters_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `datacenters_id` int unsigned NOT NULL,
  `itemtype` varchar(255) DEFAULT NULL,
  `items_id` int unsigned NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `glpi_computers_items`
    ADD COLUMN IF NOT EXISTS `comment` varchar(255) DEFAULT NULL AFTER `itemtype`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_datacarriertypes_items` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `datacarriertypes_id` int unsigned NOT NULL,
    `itemtype` varchar(255) DEFAULT NULL,
    `items_id` int unsigned NOT NULL,
    `comment` varchar(255) DEFAULT NULL,
    `date_creation` TIMESTAMP NULL DEFAULT NULL,
    `date_mod` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `glpi_plugin_dlteams_deliverables_items` DROP INDEX IF EXISTS `unicity`;
ALTER TABLE `glpi_plugin_dlteams_legalbasis_items` DROP INDEX IF EXISTS `unicity`;
ALTER TABLE `glpi_plugin_dlteams_protectivemeasures_items` DROP INDEX IF EXISTS `unicity`;

-- ajouter la gestion des entités et l'héritage pour la base de connaissances
-- ALTER TABLE `glpi_knowbaseitems`
--	ADD IF NOT EXISTS `entities_id` int unsigned NOT NULL DEFAULT 0 AFTER `id`,
--	ADD IF NOT EXISTS `is_recursive` tinyint(1) NOT NULL DEFAULT 0 AFTER `entities_id`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_physicalstorages` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='PluginDlteamsPhysicalStorage';
INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
('PluginDlteamsPhysicalStorage', 6, 2, 0),
('PluginDlteamsPhysicalStorage', 7, 1, 0),
('PluginDlteamsPhysicalStorage', 3, 3, 0) ON DUPLICATE KEY UPDATE `num` = `num`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_physicalstorages_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `physicalstorages_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `physicalstorages_id` (`physicalstorages_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ;

ALTER TABLE `glpi_plugin_dlteams_storageperiods_items` ADD IF NOT EXISTS `plugin_dlteams_storageendactions_id` int unsigned DEFAULT NULL AFTER `entities_id`;
ALTER TABLE `glpi_plugin_dlteams_storageperiods_items` ADD IF NOT EXISTS `plugin_dlteams_storagetypes_id` int unsigned DEFAULT NULL;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_deliverablevariables` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='PluginDlteamsDeliverableVariable';
INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
('PluginDlteamsDeliverableVariable', 6, 2, 0),
('PluginDlteamsDeliverableVariable', 7, 1, 0),
('PluginDlteamsDeliverableVariable', 3, 3, 0) ON DUPLICATE KEY UPDATE `num` = `num`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_documents_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `documents_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `documents_id` (`documents_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_mediasupports` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='PluginDlteamsMediaSupport';
INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
('PluginDlteamsMediaSupport', 7, 1, 0),
('PluginDlteamsMediaSupport', 3, 3, 0),
('PluginDlteamsMediaSupport', 4, 4, 0),
('PluginDlteamsMediaSupport', 5, 5, 0) ON DUPLICATE KEY UPDATE `num` = `num`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_siintegrations` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='PluginDlteamsSIIntegration';
INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
('PluginDlteamsSIIntegration', 7, 1, 0),
('PluginDlteamsSIIntegration', 3, 3, 0),
('PluginDlteamsSIIntegration', 4, 4, 0),
('PluginDlteamsSIIntegration', 5, 5, 0) ON DUPLICATE KEY UPDATE `num` = `num`;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_transmissionmethods` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='PluginDlteamsTransmissionMethod';
INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
('PluginDlteamsTransmissionMethod', 7, 1, 0),
('PluginDlteamsTransmissionMethod', 3, 3, 0),
('PluginDlteamsTransmissionMethod', 4, 4, 0),
('PluginDlteamsTransmissionMethod', 5, 5, 0) ON DUPLICATE KEY UPDATE `num` = `num`;

ALTER TABLE `glpi_plugin_dlteams_records`
    ADD IF NOT EXISTS `mediasupport` VARCHAR(1000) NULL AFTER `profiling_auto`,
    ADD IF NOT EXISTS `siintegration` VARCHAR(1000) NULL AFTER `mediasupport`,
	ADD IF NOT EXISTS `transmissionmethod` VARCHAR(1000) NULL AFTER `siintegration`;

ALTER TABLE `glpi_plugin_dlteams_records_items`
    ADD IF NOT EXISTS `document_mandatory` TINYINT(1) NOT NULL DEFAULT '0';

ALTER TABLE `glpi_plugin_dlteams_records`
    ADD IF NOT EXISTS `collect_comment` VARCHAR(1000) NULL DEFAULT '';

ALTER TABLE `glpi_plugin_dlteams_datacatalogs`
    ADD IF NOT EXISTS `datacatalogs_id` INT UNSIGNED NULL DEFAULT NULL,
    ADD INDEX IF NOT EXISTS (`datacatalogs_id`),
    ADD IF NOT EXISTS `completename` mediumtext DEFAULT NULL AFTER `plugin_dlteams_catalogclassifications_id`,
	DROP IF EXISTS `zzz_plugin_databases_databases_id_db1`,
	DROP IF EXISTS `zzz_plugin_databases_databases_id_db2`,
	DROP IF EXISTS `zzz_plugin_databases_databases_id_db3`;

ALTER TABLE `glpi_plugin_dlteams_records`
    ADD IF NOT EXISTS `print_logo` TINYINT(1) NOT NULL DEFAULT '1',
    ADD IF NOT EXISTS `print_comments` TINYINT(1) NOT NULL DEFAULT '0';

ALTER TABLE `glpi_plugin_dlteams_records`
    ADD IF NOT EXISTS `links_id` INT UNSIGNED NULL;

ALTER TABLE `glpi_plugin_dlteams_deliverables_contents` DROP FOREIGN KEY IF EXISTS `fk_deliverable_sections`;

ALTER TABLE `glpi_plugin_dlteams_datacatalogs`
    ADD IF NOT EXISTS `plugin_dlteams_datacatalogs_id` INT UNSIGNED NULL,
    ADD IF NOT EXISTS `completename` VARCHAR(255) NULL;

ALTER TABLE `glpi_plugin_dlteams_datacatalogs`
    DROP IF EXISTS `glpi_plugin_dlteams_datacatalogs_id`;

ALTER TABLE `glpi_plugin_dlteams_thirdpartycategories_items`
	ADD IF NOT EXISTS `items_id1` int unsigned NOT NULL DEFAULT 0,
	ADD IF NOT EXISTS `itemtype1` varchar(255) DEFAULT NULL,
    CHANGE IF EXISTS `items_id1` `items_id1` int unsigned NULL DEFAULT 0;

ALTER TABLE `glpi_plugin_dlteams_datacatalogs`
    ADD IF NOT EXISTS `level` INT(11) NOT NULL DEFAULT '0',
    ADD IF NOT EXISTS `ancestors_cache` LONGTEXT NULL,
    ADD IF NOT EXISTS `sons_cache` LONGTEXT NULL;
