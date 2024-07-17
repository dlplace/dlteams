-- pour les  tables d'objets, les 1000 premiers records sont réservés aux modèles
-- pour les tablés intitulés, les 100 premiers éléments sont réservés aux modèles

-- ajout du colonnage : "INSERT IGNORE INTO `glpi_displaypreferences` (itemtype, num, rank, users_id) VALUES PluginDlteams%, 2, 2, 1"*/

-- on ajoute les intitulés : !!!!! on reserve une plage de 100 fields pour les intitulés et une plage de 1000 fields pour l'entité modèle
-- Pour cela on change l'increment de la table à l'issue du 1er import : cela permet d'ajouter des enregistrements modèle lors de futures mises à jour */

-- ajout des droits du profil dans la table `glpi_profilerights` : "INSERT IGNORE INTO `glpi_profilerights` (profiles_id, name, rights) VALUES 4, %table, 127"
-- profiles_id : super-admin = 4 ; admin = 3 ;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_allitems` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `items_id1` int(11) DEFAULT 0 COMMENT 'RELATION to various tables, according to itemtype1 (id)RELATION to various tables, according to itemtype1 (id)',
 `itemtype1` varchar(100) DEFAULT NULL COMMENT 'see .class.php file',
 `items_id2` int(11) DEFAULT 0 COMMENT 'RELATION to various tables, according to itemtype2 (id)',
 `itemtype2` varchar(100) DEFAULT NULL COMMENT 'see .class.php file',
 `comment` mediumtext DEFAULT NULL,
 `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
 `date_creation` timestamp NULL DEFAULT NULL,
 `date` timestamp NULL DEFAULT NULL,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `date_mod` timestamp NULL DEFAULT NULL,
 `plugin_dlteams_records_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_concernedpersons_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_processeddatas_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_legalbasis_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_storageperiods_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_thirdpartycategories_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_rightmeasures_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_policieforms_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_datacarriers_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_datacatalogs_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_accountkeys_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_protectivemeasures_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_riskassessments_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_audits_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_trainingcertifications_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_databreachs_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_deliverables_id` int unsigned NOT NULL DEFAULT 0,
 `users_id` int unsigned NOT NULL DEFAULT 0,
 `groups_id` int unsigned NOT NULL DEFAULT 0,
 `suppliers_id` int unsigned NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDplacedpoAllitem';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_auditcategories` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`),
 KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsAuditcategory';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_audits` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `states_id` int unsigned NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `plugin_dlteams_auditcategories_id` int unsigned NOT NULL DEFAULT 0,
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `notepad` mediumtext DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsAudit';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_audits_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `audits_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `audits_id` (`audits_id`),
   KEY `date_mod` (`items_id`),
   KEY `date_creation` (`itemtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_catalogclassifications` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`),
 KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsCatalogClassification';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_concernedpersons` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_template` tinyint(1) NOT NULL DEFAULT 0,
 `template_name` varchar(255) NOT NULL DEFAULT '',
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) DEFAULT NULL,
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsConcernedPerson';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_concernedpersons_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `concernedpersons_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `concernedpersons_id` (`concernedpersons_id`),
   KEY `date_mod` (`items_id`),
   KEY `date_creation` (`itemtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_configs` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `entities_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'RELATION to glpi_entities (id)',
 `config` mediumtext NOT NULL DEFAULT '{}',
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL COMMENT 'RELATION to glpi_users (id)',
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id_lastupdater` int unsigned DEFAULT NULL COMMENT 'RELATION to glpi_users (id)',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_conservationcategory` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `entities_id` int unsigned NOT NULL,
 `is_recursive` tinyint(4) NOT NULL,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`),
 KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsConservationCategory';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_controllerinfos` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `entities_id` int unsigned DEFAULT NULL,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 1,
 `users_id_representative` int unsigned DEFAULT NULL,
 `users_id_dpo` int unsigned DEFAULT NULL,
 `logo_id` int unsigned DEFAULT NULL,
 `controllername` varchar(250) DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id_lastupdater` int unsigned DEFAULT NULL,
 `guid` mediumtext DEFAULT NULL,
 PRIMARY KEY (`id`),
 UNIQUE KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_datacarriers` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) DEFAULT NULL,
 `content` mediumtext DEFAULT NULL,
 `plugin_dlteams_datacarriercategories_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_datacarriertypes_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_servertypes_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_datacarrierhostings_id` int unsigned DEFAULT NULL,
 `plugin_dlteams_datacarriermanagements_id` int unsigned DEFAULT NULL,
 `locations_id` int unsigned NOT NULL DEFAULT 0,
 `users_id` int unsigned NOT NULL DEFAULT 0,
 `groups_id` int unsigned NOT NULL DEFAULT 0,
 `suppliers_id` int unsigned NOT NULL DEFAULT 0,
 `logs` mediumtext DEFAULT NULL,
 `meansofaccess` mediumtext DEFAULT NULL,
 `backupandversions` mediumtext DEFAULT NULL,
 `alertmonitoring` mediumtext DEFAULT NULL,
 `url` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `manufacturers_id` int unsigned NOT NULL DEFAULT 0,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
 KEY `name` (`name`)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsDataCarrier';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_datacarriers_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `datacarriers_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `datacarriers_id` (`datacarriers_id`),
   KEY `date_mod` (`items_id`),
   KEY `date_creation` (`itemtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_datacarriercategories` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `entities_id` int unsigned DEFAULT NULL,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
 KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsDataCarrierCategory';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_datacarrierhostings` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `completename` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `level` int(11) DEFAULT NULL,
 `ancestors_cache` mediumtext DEFAULT NULL,
 `sons_cache` mediumtext DEFAULT NULL,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsDataCarrierHosting';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_datacarriermanagements` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `completename` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `level` int(11) DEFAULT NULL,
 `ancestors_cache` mediumtext DEFAULT NULL,
 `sons_cache` mediumtext DEFAULT NULL,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
 KEY `entities_id` (`entities_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsDataCarrierManagement';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_datacarriertypes` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `name` varchar(255) DEFAULT NULL,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 1,
 `comment` mediumtext DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsDataCarrierType';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_datacatalogs` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL,
 `profil_name` varchar(255) DEFAULT NULL,
 `plugin_dlteams_catalogclassifications_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_datacarriertypes_id` int unsigned DEFAULT NULL COMMENT 'non utilisé',
 `content` text DEFAULT NULL COMMENT 'intended_use',
 `visible_datas` text NOT NULL,
 `profile_rights` text NOT NULL,
 `access_means` text NOT NULL,
 `comment` text DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsDataCatalog';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_datacatalogs_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `datacatalogs_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `datacatalogs_id` (`datacatalogs_id`),
   KEY `date_mod` (`items_id`),
   KEY `date_creation` (`itemtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_datacategories` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `entities_id` int unsigned NOT NULL,
 `is_recursive` tinyint(4) NOT NULL,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`),
 KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsDatacategory';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_deliverables` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsDeliverable';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_deliverables_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `deliverables_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_impactorganisms` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 1,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id_lastupdater` int unsigned DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_impacts` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 1,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id_lastupdater` int unsigned DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsImpact';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_keytypes` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsKeyType';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_legalbasis` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) DEFAULT NULL,
 `is_deleted` tinyint(1) unsigned DEFAULT 0,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 `name` varchar(255) DEFAULT NULL,
 `plugin_dlteams_legalbasistypes_id` int unsigned NOT NULL DEFAULT 0,
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `url` varchar(255) DEFAULT NULL,
 `url2` varchar(255) DEFAULT NULL,
 `entity_model` int(11) unsigned DEFAULT NULL,
 `id_model` int(11) unsigned DEFAULT NULL,
 `date_majmodel` timestamp NULL DEFAULT NULL,
 `type_majmodel` tinyint(1) unsigned DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsLegalbasi';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_legalbasis_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `legalbasis_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `legalbasis_id` (`legalbasis_id`),
   KEY `date_mod` (`items_id`),
   KEY `date_creation` (`itemtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_legalbasistypes` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `entities_id` int unsigned NOT NULL,
 `is_recursive` tinyint(1) NOT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsLegalBasisType';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_policieforms` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '''',
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsPolicieForm';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_policieforms_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `policieforms_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY (`policieforms_id`),
   KEY `date_mod` (`items_id`),
   KEY `date_creation` (`itemtype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_processeddatas` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `is_template` tinyint(1) NOT NULL DEFAULT 0,
 `template_name` varchar(255) NOT NULL DEFAULT '''',
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `rgpd_sensitive_data` tinyint(1) NOT NULL DEFAULT 0,
 `seen_sensitive_data` tinyint(1) NOT NULL DEFAULT 0,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0, PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsProcesseddata';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_processeddatas_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `processeddatas_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `processeddatas_id` (`processeddatas_id`),
   KEY `date_mod` (`date_mod`),
   KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_processes` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_template` tinyint(1) NOT NULL DEFAULT 0,
 `template_name` varchar(255) NOT NULL DEFAULT '',
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `plugin_dlteams_records_id_designation` int unsigned NOT NULL DEFAULT 0,
 `quand` varchar(255) NOT NULL DEFAULT '''fin du processus précédent''',
 `plugin_genericobject_traitementtypes_id` int unsigned NOT NULL DEFAULT 0,
 `groups_id` int unsigned NOT NULL DEFAULT 0,
 `users_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_genericobject_tierscategories_id_sstraitant1` int unsigned NOT NULL DEFAULT 0,
 `suppliers_id_sstraitant2` int unsigned NOT NULL DEFAULT 0,
 `description` mediumtext NOT NULL DEFAULT '\'NULL\'',
 `trigger-event` mediumtext DEFAULT NULL,
 `plugin_genericobject_personnesconcernees_id_percon1` int unsigned NOT NULL DEFAULT 0,
 `plugin_genericobject_personnesconcernees_id_percon2` int unsigned NOT NULL DEFAULT 0,
 `plugin_genericobject_personnesconcernees_id_percon3` int unsigned NOT NULL DEFAULT 0,
 `plugin_genericobject_personnesconcernees_id_percon4` int unsigned NOT NULL DEFAULT 0,
 `data-treat` mediumtext NOT NULL DEFAULT '',
 `dcp-treat` mediumtext NOT NULL DEFAULT '',
 `groups_id_emetdest3` int unsigned NOT NULL DEFAULT 0,
 `plugin_genericobject_personnesconcernees_id_emetdest5` int unsigned NOT NULL DEFAULT 0,
 `plugin_genericobject_tierscategories_id_emetdest1` int unsigned NOT NULL DEFAULT 0,
 `suppliers_id_emetdest2` int unsigned NOT NULL DEFAULT 0,
 `plugin_genericobject_supportsdedonnees_id_catalo1` int unsigned NOT NULL DEFAULT 0,
 `plugin_genericobject_supportsdedonnees_id_catalo2` int unsigned NOT NULL DEFAULT 0,
 `plugin_genericobject_supportsdedonnees_id_catalo3` int unsigned DEFAULT NULL,
 `plugin_genericobject_supportsdedonnees_id_catalo4` int unsigned DEFAULT NULL,
 `softwares_id_soft1` int unsigned NOT NULL DEFAULT 0,
 `softwares_id_soft3` int unsigned NOT NULL DEFAULT 0,
 `softwares_id_soft2` int unsigned NOT NULL DEFAULT 0,
 `softwares_id_soft4` int unsigned NOT NULL DEFAULT 0,
 `plugin_genericobject_documentsetcontrats_id_form1` int unsigned NOT NULL DEFAULT 0,
 `plugin_genericobject_documentsetcontrats_id_form2` int unsigned NOT NULL DEFAULT 0,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0, `plugin_genericobject_traiprocglobs_id` int unsigned NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsProcess';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_processes_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `processes_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `processes_id` (`processes_id`),
  KEY `date_mod` (`date_mod`),
  KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_protectivecategories` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsProtectiveCategory';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_protectivemeasures` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_template` tinyint(1) NOT NULL DEFAULT 0,
 `template_name` varchar(255) NOT NULL DEFAULT '',
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `name` varchar(255) NOT NULL DEFAULT '',
 `content` mediumtext NOT NULL DEFAULT '',
 `plugin_dlteams_protectivetypes_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_protectivecategories_id` int unsigned NOT NULL DEFAULT 0,
 `comment` mediumtext DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsProtectiveMeasure';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_protectivemeasures_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `protectivemeasures_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `protectivemeasures_id` (`protectivemeasures_id`),
  KEY `date_mod` (`date_mod`),
  KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_protectivetypes` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 1,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsProtectiveType';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_activitycategories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `comment` mediumtext DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 1,
  `date_creation` timestamp NULL DEFAULT NULL,
  `users_id_creator` int unsigned DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
  `users_id_lastupdater` int unsigned DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsActivityCategory';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_records` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 1,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `users_id_lastupdater` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 `number` int(11) DEFAULT 0,
 `name` varchar(255) DEFAULT NULL,
 `content` varchar(1000) DEFAULT NULL,
 `additional_info` varchar(1000) DEFAULT NULL,
 `states_id` int unsigned NOT NULL DEFAULT 1,
 `storage_medium` int(11) NOT NULL DEFAULT 0 COMMENT 'Default status to UNDEFINED',
 `pia_required` tinyint(1) NOT NULL DEFAULT 0,
 `pia_status` int(11) NOT NULL DEFAULT 0 COMMENT 'Default status to UNDEFINED',
 `first_entry_date` timestamp NULL DEFAULT NULL,
 `consent_json` longtext DEFAULT NULL,
 `consent_type` tinyint(1) DEFAULT NULL,
 `consent_type1` tinyint(1) DEFAULT NULL,
 `consent_explicit` varchar(1000) DEFAULT NULL,
 `plugin_dlteams_recordcategories_id` int unsigned DEFAULT NULL,
 `users_id_responsible` int unsigned DEFAULT NULL,
 `users_id_auditor` int unsigned DEFAULT NULL,
 `users_id_actor` int unsigned DEFAULT NULL,
 `diffusion` varchar(45) DEFAULT NULL,
 `conservation_time` varchar(1000) DEFAULT NULL COMMENT 'to display in legal bases tab',
 `archive_time` varchar(1000) DEFAULT NULL,
 `archive_required` tinyint(1) DEFAULT 0,
 `right_information` varchar(1000) DEFAULT NULL COMMENT 'section 5',
 `right_correction` varchar(1000) DEFAULT NULL COMMENT 'section 5',
 `right_opposition` varchar(1000) DEFAULT NULL COMMENT 'section 5',
 `right_portability` varchar(1000) DEFAULT NULL COMMENT 'section 5',
 `sensitive` tinyint(1) DEFAULT NULL,
 `profiling` tinyint(1) DEFAULT NULL,
 `profiling_auto` varchar(1000) DEFAULT NULL,
 `external_group` varchar(50) DEFAULT NULL,
 `external_supplier` varchar(50) DEFAULT NULL,
 `external_process` longtext DEFAULT NULL COMMENT 'Section 5',
 `impact_person` int unsigned DEFAULT NULL,
 `impact_organism` int unsigned DEFAULT NULL,
 `zzz_violation_impact` longtext DEFAULT NULL COMMENT 'Section 6',
 `zzz_violation_impact_level` longtext DEFAULT NULL COMMENT 'Section 6',
 `specific_security_measures` varchar(1000) DEFAULT NULL COMMENT 'Section 6',
 PRIMARY KEY (`id`),
 KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_records_items` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `records_id` int unsigned NOT NULL DEFAULT 0,
 `itemtype` varchar(100) DEFAULT NULL,
 `items_id` int unsigned DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `groups_id` int unsigned DEFAULT NULL,
 `suppliers_id` int unsigned DEFAULT NULL,
 `plugin_dlteams_thirdpartycategories_id` int unsigned DEFAULT NULL,
 `users_id` int unsigned DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `date_creation` (`date_creation`),
 KEY `date_mod` (`date_mod`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_records_externals` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `plugin_dlteams_records_id` int unsigned DEFAULT NULL,
 `suppliers_id` int unsigned DEFAULT NULL,
 `plugin_dlteams_concernedpersons_id` int unsigned DEFAULT NULL,
 `plugin_dlteams_thirdpartycategories_id` int unsigned DEFAULT NULL,
 `plugin_dlteams_sendingreasons_id` int unsigned DEFAULT NULL,
 `recipient_reason` varchar(100) DEFAULT NULL,
 `recipient_comment` varchar(1000) DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_records_legalbasis` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `plugin_dlteams_records_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_legalbasis_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_genericobject_baseslegales_id` int unsigned NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_records_personalanddatacategories` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `plugin_dlteams_records_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_concernedpersons_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_processeddatas_id` int unsigned NOT NULL DEFAULT 0,
 `mandatory` tinyint(1) DEFAULT 1,
 PRIMARY KEY (`id`),
 KEY `plugin_dlteams_records_id` (`plugin_dlteams_records_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_records_storages` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `plugin_dlteams_records_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_storageperiods_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_storagetypes_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_storageendactions_id` int unsigned NOT NULL DEFAULT 0,
 `storage_comment` longtext DEFAULT NULL,
 `storage_action` varchar(100) DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `plugin_dlteams_storages_id` (`plugin_dlteams_storagetypes_id`),
 KEY `plugin_genericobject_rgpdconservations_id` (`plugin_dlteams_storageperiods_id`),
 KEY `plugin_dlteams_records_id` (`plugin_dlteams_records_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_rightmeasures` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_template` tinyint(1) NOT NULL DEFAULT 0,
 `template_name` varchar(255) NOT NULL DEFAULT '',
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `plugin_dlteams_rightmeasurecategories_id` int unsigned NOT NULL DEFAULT 0,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsRightMeasure';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_rightmeasures_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `rightmeasures_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `rightmeasures_id` (`rightmeasures_id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_rightmeasurecategories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `comment` mediumtext DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 1,
  `date_creation` timestamp NULL DEFAULT NULL,
  `users_id_creator` int unsigned DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
  `users_id_lastupdater` int unsigned DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsRightMeasureCategory';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_riskassessments` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_template` tinyint(1) NOT NULL DEFAULT 0,
 `template_name` varchar(255) NOT NULL DEFAULT '',
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `serial` varchar(255) NOT NULL DEFAULT '',
 `otherserial` varchar(255) NOT NULL DEFAULT '',
 `locations_id` int unsigned NOT NULL DEFAULT 0,
 `content` mediumtext NOT NULL,
 `comment` mediumtext DEFAULT NULL,
 `states_id` int unsigned NOT NULL DEFAULT 0,
 `users_id` int unsigned NOT NULL DEFAULT 0,
 `groups_id` int unsigned NOT NULL DEFAULT 0,
 `manufacturers_id` int unsigned NOT NULL DEFAULT 0,
 `users_id_tech` int unsigned NOT NULL DEFAULT 0,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsRiskAssessment';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_riskassessments_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `riskassessments_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet à relier',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'id de l''objet de la classe reliée',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'nom de la classe reliée (see .class.php file)',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `riskassessments_id` (`riskassessments_id`),
  KEY `date_mod` (`date_mod`),
  KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_sendingreasons` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 1,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id_lastupdater` int unsigned DEFAULT NULL,
 `type` int NOT NULL,
 PRIMARY KEY (`id`),
 KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsSendingReason';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_servertypes` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 1,
 `users_id_creator` int unsigned DEFAULT NULL,
 `users_id_lastupdater` int unsigned DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsServerType';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_storageendactions` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 1,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id_lastupdater` int unsigned DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsStorageEndAction';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_storageperiods` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '''',
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `plugin_dlteams_storagetypes_id` int unsigned NOT NULL DEFAULT 0,
 `url` varchar(255) NOT NULL,
 `url2` varchar(255) NOT NULL,
 `plugin_dlteams_legalbasisacts_id_duree1s_id` int unsigned DEFAULT NULL,
 `plugin_dlteams_legalbasisacts_id_duree2s_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_dlteams_legalbasisacts_id_duree3s_id` int unsigned NOT NULL DEFAULT 0,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsStoragePeriod';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_storagetypes` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 1,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id_lastupdater` int unsigned DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsStorageType';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_thirdpartycategories` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsThirdpartycategory';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_thirdpartycategories_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `thirdpartycategories_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'RELATION to various tables, according to itemtype1 (id)RELATION to various tables, according to itemtype1 (id)',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'RELATION to various tables, according to itemtype2 (id)',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'see .class.php file',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `date_mod` (`date_mod`),
   KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_storageperiods_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `storageperiods_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'RELATION to various tables, according to itemtype1 (id)RELATION to various tables, according to itemtype1 (id)',
  `items_id` int unsigned NOT NULL DEFAULT 0 COMMENT 'RELATION to various tables, according to itemtype2 (id)',
  `itemtype` varchar(100) DEFAULT NULL COMMENT 'see .class.php file',
  `comment` mediumtext DEFAULT NULL,
  `json` tinytext DEFAULT NULL,
  `timeline_position` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`),
   KEY `date_mod` (`date_mod`),
   KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_trainingcategories` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `date_creation` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`),
 KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsTrainingCategory';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_trainingcertifications` (
 `id` int unsigned NOT NULL AUTO_INCREMENT,
 `is_template` tinyint(1) NOT NULL DEFAULT 0,
 `template_name` varchar(255) NOT NULL DEFAULT '',
 `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
 `entities_id` int unsigned NOT NULL DEFAULT 0,
 `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
 `date_creation` timestamp NULL DEFAULT NULL,
 `users_id_creator` int unsigned DEFAULT NULL,
 `date_mod` timestamp NULL DEFAULT NULL,
 `users_id` INT unsigned NOT NULL DEFAULT 0,
 `is_helpdesk_visible` TINYINT(1) NOT NULL DEFAULT 0,
 `name` varchar(255) NOT NULL DEFAULT '',
 `content` mediumtext DEFAULT NULL,
 `comment` mediumtext DEFAULT NULL,
 `public_cible` varchar(255) DEFAULT NULL,
 `groups_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_genericobject_rgpdformationcategories_id` int unsigned NOT NULL DEFAULT 0,
 `plugin_genericobject_etats_id` int unsigned NOT NULL DEFAULT 0,
 `url_formation` varchar(255) DEFAULT NULL,
 `url_examen` varchar(255) DEFAULT NULL,
 PRIMARY KEY (`id`),
 KEY `date_mod` (`date_mod`),
 KEY `date_creation` (`date_creation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsTrainingCertification';

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_userprofiles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '',
  `comment` mediumtext DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `entities_id` int unsigned NOT NULL DEFAULT 0,
  `is_recursive` tinyint(1) NOT NULL DEFAULT 0,
  `date_mod` timestamp NULL DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='PluginDlteamsUserProfile' ;

/*on met les droits tout sélectionner
-> l'install créé autant de ligne que de table et de profil dans glpi_profilerights : select * from glpi_profilerights where `name` like 'plugin_dlplacedpo%'
on passe les droits à 127 pour tous le monde*/
UPDATE `glpi_profilerights` SET rights = 127 WHERE `name` LIKE 'plugin_dlteams%' ;

/* ajout de l'héritage pour glpi_documentcategories
pour ne pas perdre ce que les utilisateurs ont fait on met par defaut is_recursive à 1
ALTER TABLE `glpi_documentcategories` ADD IF NOT EXISTS `entities_id` INT unsigned NOT NULL DEFAULT '0' AFTER `date_creation`;
ALTER TABLE `glpi_documentcategories` ADD IF NOT EXISTS `is_recursive` TINYINT NOT NULL DEFAULT '1' AFTER `date_creation`;
*/

-- 08/05/2023
CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_groups_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `groups_id` int unsigned NOT NULL DEFAULT 0,
  `itemtype` varchar(255) DEFAULT NULL,
  `items_id` int unsigned NOT NULL DEFAULT 0,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_users_items` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `users_id` int unsigned NOT NULL,
  `itemtype` varchar(255) DEFAULT NULL,
  `items_id` int unsigned NOT NULL,
  `comment` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NULL DEFAULT NULL,
  `date_mod` timestamp NULL DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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

-- 23/05/2023 : 14:42 GMT
/*CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_deliverabledocumentchapters` (
	`id` int unsigned NOT NULL AUTO_INCREMENT,
	`name` varchar(255) NOT NULL,
	`tab_name` varchar(255) NOT NULL,
    `content` longtext NOT NULL,
    `deliverables_id` int unsigned NOT NULL DEFAULT 0,
    `timeline_position` int unsigned NOT NULL DEFAULT 0,
    `date_creation` timestamp NULL DEFAULT NULL,
    `date_mod` timestamp NULL DEFAULT NULL,
	 PRIMARY KEY (`id`), KEY `deliverables_id` (`deliverables_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
*/

-- 31/05/2023 : 09:20 GMT
CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_deliverables_sections` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NULL,
    `tab_name` varchar(255) NOT NULL,
    `comment` longtext NULL,
    `content` longtext NULL,
    `deliverables_id` int unsigned NOT NULL DEFAULT 0,
    `timeline_position` int unsigned NOT NULL DEFAULT 0,
    `date_creation` timestamp NULL DEFAULT NULL,
    `date_mod` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`), KEY `deliverables_id` (`deliverables_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- 31/05/2023 : 10:04 GMT
CREATE TABLE IF NOT EXISTS `glpi_plugin_dlteams_deliverables_contents` (
      `id` int unsigned NOT NULL AUTO_INCREMENT,
      `deliverable_sections_id` INT unsigned NOT NULL DEFAULT 0,
      `name` VARCHAR(255) NOT NULL,
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
ALTER TABLE `glpi_documents_items` ADD COLUMN IF NOT EXISTS `comment` varchar(255) NULL ;

ALTER TABLE `glpi_plugin_dlteams_deliverables`
    ADD IF NOT EXISTS `is_firstpage` TINYINT NOT NULL DEFAULT '0' AFTER `comment`,
    ADD IF NOT EXISTS `is_comment` TINYINT NOT NULL DEFAULT '0' AFTER `is_firstpage`;

ALTER TABLE `glpi_plugin_dlteams_deliverables`
    ADD IF NOT EXISTS `document_title` VARCHAR(255) NULL AFTER `comment`,
    ADD IF NOT EXISTS `publication_folder` VARCHAR(255) NULL AFTER `document_title`;

ALTER TABLE `glpi_plugin_dlteams_deliverables`
    ADD IF NOT EXISTS `links_id` INT NULL AFTER `comment`;