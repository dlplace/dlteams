-- mise à jour des droits sur les tables crées par empty
UPDATE `glpi_profilerights` SET rights = 127 WHERE `name` LIKE 'plugin_dlteams%' ;

-- suppression de l'enregistrement si existe
-- DELETE FROM `glpi_entities` WHERE `glpi_entities`.`name` = 'model-rgpd';
-- SET @modelrgpd_id = (SELECT id FROM `glpi_entities` WHERE name = 'model-rgpd');

-- Création de l'entité model rgpd
-- le N° d'entité est le plus grand numéro disponible
SET @modelrgpd_id = (SELECT MAX(`id`) FROM `glpi_entities`) + 1; 
SET @rootname = (SELECT completename FROM `glpi_entities` WHERE id = 0);
SET @rootname =  CONCAT (@rootname, ' ', CHAR(62), ' model-rgpd');
INSERT INTO `glpi_entities` (`id`, `name`, `entities_id`, `completename`, `comment`, `level`, `sons_cache`, `ancestors_cache`, `registration_number`, `address`, `postcode`, `town`, `state`, `country`, `website`, `phonenumber`, `fax`, `email`, `admin_email`, `admin_email_name`, `from_email`, `from_email_name`, `noreply_email`, `noreply_email_name`, `replyto_email`, `replyto_email_name`, `notification_subject_tag`, `ldap_dn`, `tag`, `authldaps_id`, `mail_domain`, `entity_ldapfilter`, `mailing_signature`, `cartridges_alert_repeat`, `consumables_alert_repeat`, `use_licenses_alert`, `send_licenses_alert_before_delay`, `use_certificates_alert`, `send_certificates_alert_before_delay`, `certificates_alert_repeat_interval`, `use_contracts_alert`, `send_contracts_alert_before_delay`, `use_infocoms_alert`, `send_infocoms_alert_before_delay`, `use_reservations_alert`, `use_domains_alert`, `send_domains_alert_close_expiries_delay`, `send_domains_alert_expired_delay`, `autoclose_delay`, `autopurge_delay`, `notclosed_delay`, `calendars_strategy`, `calendars_id`, `auto_assign_mode`, `tickettype`, `max_closedate`, `inquest_config`, `inquest_rate`, `inquest_delay`, `inquest_URL`, `autofill_warranty_date`, `autofill_use_date`, `autofill_buy_date`, `autofill_delivery_date`, `autofill_order_date`, `tickettemplates_strategy`, `tickettemplates_id`, `changetemplates_strategy`, `changetemplates_id`, `problemtemplates_strategy`, `problemtemplates_id`, `entities_strategy_software`, `entities_id_software`, `default_contract_alert`, `default_infocom_alert`, `default_cartridges_alarm_threshold`, `default_consumables_alarm_threshold`, `delay_send_emails`, `is_notif_enable_default`, `inquest_duration`, `date_mod`, `date_creation`, `autofill_decommission_date`, `suppliers_as_private`, `anonymize_support_agents`, `display_users_initials`, `contracts_strategy_default`, `contracts_id_default`, `enable_custom_css`, `custom_css_code`, `latitude`, `longitude`, `altitude`, `transfers_strategy`, `transfers_id`, `agent_base_url`) VALUES (@modelrgpd_id, 'model-rgpd', '0', @rootname, '', '2', NULL, '[0]', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0', NULL, NULL, NULL, '-2', '-2', '-2', '-2', '-2', '-2', '-2', '-2', '-2', '-2', '-2', '-2', '-2', '-2', '-2', '-2', '-10', '-2', '-2', '0', '-2', '-2', '2023-01-11 10:55:30', '-2', '0', '-10', NULL, '-2', '-2', '-2', '-2', '-2', '-2', '0', '-2', '0', '-2', '0', '-2', '0', '-2', '-2', '-2', '-2', '-2', '-2', '0', '2023-01-11 10:55:30', '2023-01-11 10:55:30', '-2', '-2', '-2', '-2', '-2', '0', '-2', NULL, NULL, NULL, NULL, '-2', '0', NULL);

-- Création de l'admin rgpd password = Temporaire.32
-- DELETE FROM `glpi_users` WHERE `glpi_users`.`name` = 'admin-rgpd';
INSERT INTO `glpi_users` (`name`, `password`, `password_last_update`, `phone`, `phone2`, `mobile`, `realname`, `firstname`, `locations_id`, `language`, `use_mode`, `list_limit`, `is_active`, `comment`, `auths_id`, `authtype`, `last_login`, `date_mod`, `date_sync`, `is_deleted`, `profiles_id`, `entities_id`, `usertitles_id`, `usercategories_id`, `date_format`, `number_format`, `names_format`, `csv_delimiter`, `is_ids_visible`, `use_flat_dropdowntree`, `show_jobs_at_login`, `priority_1`, `priority_2`, `priority_3`, `priority_4`, `priority_5`, `priority_6`, `followup_private`, `task_private`, `default_requesttypes_id`, `password_forget_token`, `password_forget_token_date`, `user_dn`, `registration_number`, `show_count_on_tabs`, `refresh_views`, `set_default_tech`, `personal_token`, `personal_token_date`, `api_token`, `api_token_date`, `cookie_token`, `cookie_token_date`, `display_count_on_home`, `notification_to_myself`, `duedateok_color`, `duedatewarning_color`, `duedatecritical_color`, `duedatewarning_less`, `duedatecritical_less`, `duedatewarning_unit`, `duedatecritical_unit`, `display_options`, `is_deleted_ldap`, `pdffont`, `picture`, `begin_date`, `end_date`, `keep_devices_when_purging_item`, `privatebookmarkorder`, `backcreated`, `task_state`, `palette`, `page_layout`, `fold_menu`, `fold_search`, `savedsearches_pinned`, `timeline_order`, `itil_layout`, `richtext_layout`, `set_default_requester`, `lock_autolock_mode`, `lock_directunlock_notification`, `date_creation`, `highcontrast_css`, `plannings`, `sync_field`, `groups_id`, `users_id_supervisor`, `timezone`, `default_dashboard_central`, `default_dashboard_assets`, `default_dashboard_helpdesk`, `default_dashboard_mini_ticket`, `default_central_tab`, `nickname`) VALUES ('admin-rgpd', '$2y$10$s3jFA120iBZ9tsCnn4BniuutxaUA5FUZFrfVCoJzg0bCW3fNvxLFm', '2023-01-04 11:14:05', NULL, NULL, NULL, 'admin-rgpd', NULL, 0, NULL, 0, NULL, 1, NULL, 0, 1, '2019-12-31 23:00:00', '2023-01-04 11:14:05', NULL, 0, 0, 0, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Administrateur de l\'entité modèle \"Conformité RGPD\"', NULL, NULL, NULL, 'AOFAPdTK1JHShr4w5K158ZkAyIQgYwwX74a7G6rN', '2021-04-19 07:47:48', 'dTLeLRg9DtgJVTZvBXcuF7pMgniZ3f00EZQ6gXeG', '2022-01-14 08:09:49', '$2y$10$eg0dO31jLgcG.UVAQmnLYe8ZUYauQmJVPj1jQtFwjcHNMptbUsozm', '2023-01-04 09:52:47', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, '[61,62]', NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '{\"filters\":{\"ChangeTask\":{\"color\":\"#E94A31\",\"display\":true,\"type\":\"event_filter\"},\"ProblemTask\":{\"color\":\"#5174F2\",\"display\":true,\"type\":\"event_filter\"},\"Reminder\":{\"color\":\"#51C9F2\",\"display\":true,\"type\":\"event_filter\"},\"TicketTask\":{\"color\":\"#FFCC29\",\"display\":true,\"type\":\"event_filter\"},\"ProjectTask\":{\"color\":\"#20C646\",\"display\":true,\"type\":\"event_filter\"},\"PlanningExternalEvent\":{\"color\":\"#364959\",\"display\":true,\"type\":\"event_filter\"},\"NotPlanned\":{\"color\":\"#8C5344\",\"display\":false,\"type\":\"event_filter\"},\"OnlyBgEvents\":{\"color\":\"#FF8100\",\"display\":false,\"type\":\"event_filter\"}},\"plannings\":{\"user_2\":{\"color\":\"#FFEEC4\",\"display\":true,\"type\":\"user\"}},\"lastview\":\"timeGridWeek\"}', NULL, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- ajout des droits super-admin pour l'entité rgpd-model au user admin-rgpd + le user courant + le user id = 2 (glpi)
SET @adminrgpd_id = (SELECT id FROM `glpi_users` WHERE name = 'admin-rgpd');
-- L'administrateur général, le user actuel et user admin-rgpd ont les droits d'admin sur la nouvelle entité
-- DELETE FROM `glpi_profiles_users` WHERE `glpi_profiles_users`.`entities_id` = @modelrgpd_id;
INSERT INTO `glpi_profiles_users` (`users_id`, `profiles_id`, `entities_id`, `is_recursive`, `is_dynamic`, `is_default_profile`) VALUES
(@currentuser_id, 3, @modelrgpd_id, 0, 0, 0),
(@adminrgpd_id, 3, @modelrgpd_id, 0, 0, 0),
(2, 4, @modelrgpd_id, 0, 0, 0);
-- remettre profil du defaut glpi sur root
UPDATE `glpi_users` SET `profiles_id` = 4 WHERE id = 2;

-- Création d'un nouveau profil "Vue-Modele"
-- DELETE FROM `glpi_profiles` WHERE `glpi_profiles`.`name` = 'Vue-Modele';
INSERT INTO `glpi_profiles` (`name`, `interface`, `is_default`, `helpdesk_hardware`, `helpdesk_item_type`, `ticket_status`, `date_mod`, `comment`, `problem_status`, `create_ticket_on_login`, `tickettemplates_id`, `changetemplates_id`, `problemtemplates_id`, `change_status`, `managed_domainrecordtypes`, `date_creation`)
VALUES ('Vue-Modele', 'central', '0', '3', '[]', '[]', NULL, '', '[]', '0', '0', '0', '0', NULL, '[-1]', NULL);
-- récupération de l'id du profil
SET @vuemodele_profile_id = (select id from `glpi_profiles` where `name` = 'Vue-Modele');

-- ajout du profil "Vue-Modele" pour le user courant + l'admin-rgpd de modelrgpd (accès simultané aux 2 entités pour la copie
INSERT INTO `glpi_profiles_users` (`users_id`, `profiles_id`, `entities_id`, `is_recursive`, `is_dynamic`, `is_default_profile`)
VALUES
(@currentuser_id, @vuemodele_profile_id, @modelrgpd_id, 0, 0, 0),
(@adminrgpd_id, @vuemodele_profile_id, @modelrgpd_id, 0, 0, 0);

-- ajout des traitements
INSERT INTO `glpi_profilerights` (profiles_id, name, rights) VALUES (@vuemodele_profile_id, 'plugin_dlteams_record', 127);
-- DELETE FROM `glpi_plugin_dlteams_records` where id < 1000;

-- ajout des personnes concernées
INSERT INTO `glpi_profilerights` (profiles_id, name, rights) VALUES (@vuemodele_profile_id, 'plugin_dlteams_concernedperson', 127);
-- DELETE FROM `glpi_plugin_dlteams_concernedpersons` WHERE id <1000;

-- ajout des DCP
INSERT INTO `glpi_profilerights` (profiles_id, name, rights) VALUES (@vuemodele_profile_id, 'plugin_dlteams_processeddata', 127);
-- DELETE FROM `glpi_plugin_dlteams_processeddatas` WHERE id <1000;

INSERT INTO `glpi_profilerights` (profiles_id, name, rights) VALUES (@vuemodele_profile_id, 'plugin_dlteams_legalbasi', 127);
-- DELETE FROM `glpi_plugin_dlteams_legalbasis` WHERE id <1000;

INSERT INTO `glpi_profilerights` (profiles_id, name, rights) VALUES (@vuemodele_profile_id, 'plugin_dlteams_storageperiod', 127);
-- DELETE FROM `glpi_plugin_dlteams_storageperiods` WHERE id <1000;

INSERT INTO `glpi_profilerights` (profiles_id, name, rights) VALUES (@vuemodele_profile_id, 'plugin_dlteams_thirdpartycategory', 127);
-- DELETE FROM `glpi_plugin_dlteams_thirdpartycategories` WHERE id <1000;

INSERT INTO `glpi_profilerights` (profiles_id, name, rights) VALUES (@vuemodele_profile_id, 'plugin_dlteams_rightmeasure', 127);
-- DELETE FROM `glpi_plugin_dlteams_rightmeasures` WHERE id <1000;

INSERT INTO `glpi_profilerights` (profiles_id, name, rights) VALUES (@vuemodele_profile_id, 'plugin_dlteams_policieform', 127);
-- DELETE FROM `glpi_plugin_dlteams_policieforms` WHERE id <1000;

INSERT INTO `glpi_profilerights` (profiles_id, name, rights) VALUES (@vuemodele_profile_id, 'plugin_dlteams_protectivemeasure', 127);
-- DELETE FROM `glpi_plugin_dlteams_protectivemeasures` WHERE id <1000;

INSERT INTO `glpi_profilerights` (profiles_id, name, rights) VALUES (@vuemodele_profile_id, 'plugin_dlteams_deliverable', 127);
-- DELETE FROM `glpi_plugin_dlteams_deliverables` WHERE id <1000;

INSERT INTO `glpi_profilerights` (profiles_id, name, rights) VALUES (@vuemodele_profile_id, 'plugin_dlteams_audit', 127);
-- DELETE FROM `glpi_plugin_dlteams_audits` WHERE id <1000;

INSERT INTO `glpi_profilerights` (profiles_id, name, rights) VALUES (@vuemodele_profile_id, 'plugin_dlteams_riskassessment', 127);
-- DELETE FROM `glpi_plugin_dlteams_riskassessments` WHERE id <1000;
