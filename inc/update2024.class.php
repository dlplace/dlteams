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


use GlpiPlugin\dlteams\Exception\ImportFailureException;

class PluginDlteamsUpdate
{
    public static function update2024(){
        global $DB;
        $update24 = "ALTER TABLE `glpi_plugin_dlteams_records_items`
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

ALTER TABLE `glpi_plugin_dlteams_policieforms`
	ADD UNIQUE IF NOT EXISTS `name-entities` (`entities_id`, `name`);

ALTER TABLE `glpi_plugin_dlteams_physicalstorages`
    ADD IF NOT EXISTS `locations_id` INT NOT NULL DEFAULT '0' AFTER `comment`,
    ADD IF NOT EXISTS `plugin_dlteams_storageunittypes_id` INT NOT NULL DEFAULT '0' AFTER `locations_id`;

ALTER TABLE `glpi_plugin_dlteams_datacarriertypes`
    ADD IF NOT EXISTS `is_deleted` tinyint(1) NOT NULL DEFAULT 0 AFTER `comment`,
    ADD IF NOT EXISTS `date_creation` timestamp NULL DEFAULT NULL AFTER `is_deleted`,
    ADD IF NOT EXISTS `date_mod` timestamp NULL DEFAULT NULL AFTER `date_creation`;

ALTER TABLE `glpi_plugin_dlteams_policieforms`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `documents_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_plugin_dlteams_deliverables`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_plugin_dlteams_procedures`
    ADD IF NOT EXISTS `projects_id` int(11) unsigned DEFAULT NULL AFTER `content`;

-- ALTER TABLE `glpi_tickettasks` ADD IF NOT EXISTS `entities_id` INT NOT NULL DEFAULT '0' AFTER `id`;
-- ALTER TABLE `glpi_tickettasks` DROP IF EXISTS `entities_id`;

ALTER TABLE `glpi_plugin_dlteams_datacatalogs_items` DROP INDEX IF EXISTS `unicity`;
ALTER TABLE `glpi_appliances_items` DROP INDEX IF EXISTS `unicity`;

ALTER TABLE `glpi_plugin_dlteams_records`
	RENAME COLUMN IF EXISTS `copy_id` TO `id_model`,
	RENAME COLUMN IF EXISTS `copy_entityid` TO `entity_model`,
	RENAME COLUMN IF EXISTS `copy_date` TO `date_majmodel`,
	RENAME COLUMN IF EXISTS `copy_update` TO `type_majmodel`;
ALTER TABLE `glpi_plugin_dlteams_records`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_plugin_dlteams_concernedpersons`
	RENAME COLUMN IF EXISTS `copy_id` TO `id_model`,
	RENAME COLUMN IF EXISTS `copy_entityid` TO `entity_model`,
	RENAME COLUMN IF EXISTS `copy_date` TO `date_majmodel`,
	RENAME COLUMN IF EXISTS `copy_update` TO `type_majmodel`;
ALTER TABLE `glpi_plugin_dlteams_concernedpersons`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_plugin_dlteams_processeddatas`
	RENAME COLUMN IF EXISTS `copy_id` TO `id_model`,
	RENAME COLUMN IF EXISTS `copy_entityid` TO `entity_model`,
	RENAME COLUMN IF EXISTS `copy_date` TO `date_majmodel`,
	RENAME COLUMN IF EXISTS `copy_update` TO `type_majmodel`;
ALTER TABLE `glpi_plugin_dlteams_processeddatas`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_plugin_dlteams_legalbasis`
	RENAME COLUMN IF EXISTS `copy_id` TO `id_model`,
	RENAME COLUMN IF EXISTS `copy_entityid` TO `entity_model`,
	RENAME COLUMN IF EXISTS `copy_date` TO `date_majmodel`,
	RENAME COLUMN IF EXISTS `copy_update` TO `type_majmodel`;
ALTER TABLE `glpi_plugin_dlteams_legalbasis`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_plugin_dlteams_storageperiods`
	RENAME COLUMN IF EXISTS `copy_id` TO `id_model`,
	RENAME COLUMN IF EXISTS `copy_entityid` TO `entity_model`,
	RENAME COLUMN IF EXISTS `copy_date` TO `date_majmodel`,
	RENAME COLUMN IF EXISTS `copy_update` TO `type_majmodel`;
ALTER TABLE `glpi_plugin_dlteams_storageperiods`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_plugin_dlteams_thirdpartycategories`
	RENAME COLUMN IF EXISTS `copy_id` TO `id_model`,
	RENAME COLUMN IF EXISTS `copy_entityid` TO `entity_model`,
	RENAME COLUMN IF EXISTS `copy_date` TO `date_majmodel`,
	RENAME COLUMN IF EXISTS `copy_update` TO `type_majmodel`;
ALTER TABLE `glpi_plugin_dlteams_thirdpartycategories`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_plugin_dlteams_rightmeasures`
	RENAME COLUMN IF EXISTS `copy_id` TO `id_model`,
	RENAME COLUMN IF EXISTS `copy_entityid` TO `entity_model`,
	RENAME COLUMN IF EXISTS `copy_date` TO `date_majmodel`,
	RENAME COLUMN IF EXISTS `copy_update` TO `type_majmodel`;
ALTER TABLE `glpi_plugin_dlteams_rightmeasures`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `users_id`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_plugin_dlteams_datacatalogs`
	RENAME COLUMN IF EXISTS `copy_id` TO `id_model`,
	RENAME COLUMN IF EXISTS `copy_entityid` TO `entity_model`,
	RENAME COLUMN IF EXISTS `copy_date` TO `date_majmodel`,
	RENAME COLUMN IF EXISTS `copy_update` TO `type_majmodel`;
ALTER TABLE `glpi_plugin_dlteams_datacatalogs`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `date_mod`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_plugin_dlteams_riskassessments`
	RENAME COLUMN IF EXISTS `copy_id` TO `id_model`,
	RENAME COLUMN IF EXISTS `copy_entityid` TO `entity_model`,
	RENAME COLUMN IF EXISTS `copy_date` TO `date_majmodel`,
	RENAME COLUMN IF EXISTS `copy_update` TO `type_majmodel`;
ALTER TABLE `glpi_plugin_dlteams_riskassessments`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `date_mod`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_plugin_dlteams_audits`
	RENAME COLUMN IF EXISTS `copy_id` TO `id_model`,
	RENAME COLUMN IF EXISTS `copy_entityid` TO `entity_model`,
	RENAME COLUMN IF EXISTS `copy_date` TO `date_majmodel`,
	RENAME COLUMN IF EXISTS `copy_update` TO `type_majmodel`;
ALTER TABLE `glpi_plugin_dlteams_audits`	
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `date_mod`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_plugin_dlteams_protectivemeasures`
	RENAME COLUMN IF EXISTS `copy_id` TO `id_model`,
	RENAME COLUMN IF EXISTS `copy_entityid` TO `entity_model`,
	RENAME COLUMN IF EXISTS `copy_date` TO `date_majmodel`,
	RENAME COLUMN IF EXISTS `copy_update` TO `type_majmodel`;
ALTER TABLE `glpi_plugin_dlteams_protectivemeasures`	
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `date_mod`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_plugin_dlteams_datacarriertypes`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `date_mod`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_appliances`
	RENAME COLUMN IF EXISTS `copy_id` TO `id_model`,
	RENAME COLUMN IF EXISTS `copy_entityid` TO `entity_model`,
	RENAME COLUMN IF EXISTS `copy_date` TO `date_majmodel`,
	RENAME COLUMN IF EXISTS `copy_update` TO `type_majmodel`;
ALTER TABLE `glpi_appliances`
    ADD IF NOT EXISTS `entity_model` int(11) unsigned DEFAULT NULL AFTER `date_mod`,
    ADD IF NOT EXISTS `id_model` int(11) unsigned DEFAULT NULL AFTER `entity_model`,
    ADD IF NOT EXISTS `date_majmodel` timestamp NULL DEFAULT NULL AFTER `id_model`,
    ADD IF NOT EXISTS `type_majmodel` tinyint(1) unsigned DEFAULT NULL AFTER `date_majmodel`;

ALTER TABLE `glpi_plugin_dlteams_policieforms`
    DROP INDEX IF EXISTS `name-entities`,
    ADD UNIQUE INDEX IF NOT EXISTS `name-entities-is_deleted` (`entities_id`, `name`, `is_deleted`) USING BTREE;

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

ALTER TABLE `glpi_projecttasks`
    ADD IF NOT EXISTS `timeline_position` tinyint(1) NOT NULL DEFAULT '0' AFTER `date_mod`;

ALTER TABLE `glpi_tickets`
    ADD IF NOT EXISTS `timeline_position` tinyint(1) NOT NULL DEFAULT '0' AFTER `date_mod`;
";
        $DB->beginTransaction();
        $test = $DB->query($update24);
        var_dump($test);
        die();
        $DB->commit();
    }

}
