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

include("../../../inc/includes.php");
global $DB;

$DB->request("SET @currententity_id := ".Session::getActiveEntity());
$DB->request("SET @currentuser_id := ".Session::getLoginUserID());
$message = "Import des jeux de données et éléments reliés" ;

// model-rgpd & user & profils & rights creation
	$result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
    $modelrgpd_id = 0;
    if ($result && $DB->numrows($result) > 0) {
        $data = $DB->fetchAssoc($result);
        $modelrgpd_id = $data['id'];
    }

// liste des class liées
 $objects = ['processeddatas','datacarriertypes','legalbasis','storageperiods'];

// STEP 1 : on supprime les traitements et les liaisons dépendances de model-rgpd
$DB->request("SET @modelrgpd_id := ".$modelrgpd_id);
		$query = "SELECT COUNT(*) FROM `glpi_plugin_dlteams_policieforms` WHERE `entities_id` = '$modelrgpd_id'";
		$row = $DB->query($query)->fetch_assoc();
		$message .= strval($row["COUNT(*)"]) . " jeux de données précédents supprimés <br>" . "Ajouts : " . nl2br("\n");
		$master_table = "glpi_plugin_dlteams_policieforms"; $item_type = "PluginDlteamsPolicieForm"
$querys = [
// suppression des liens puis des policiesform
"DELETE t1 FROM `glpi_plugin_dlteams_processeddatas_items` as t1 INNER JOIN ".$master_table." as t2 ON t2.`id` = t1.`items_id` WHERE t2.`entities_id` =  @modelrgpd_id AND t1.`itemtype` = ".$item_type."" ,
"DELETE t1 FROM `glpi_plugin_dlteams_datacarriertypes_items` as t1 INNER JOIN ".$master_table." as t2 ON t2.`id` = t1.`items_id` WHERE t2.`entities_id` =  @modelrgpd_id AND t1.`itemtype` = ".$item_type."" ,
"DELETE t1 FROM `glpi_plugin_dlteams_legalbasis_items` as t1 INNER JOIN ".$master_table." as t2 ON t2.`id` = t1.`items_id` WHERE t2.`entities_id` =  @modelrgpd_id AND t1.`itemtype` = ".$item_type."" ,
"DELETE t1 FROM `glpi_plugin_dlteams_storageperiods_items` as t1 INNER JOIN ".$master_table." as t2 ON t2.`id` = t1.`items_id` WHERE t2.`entities_id` =  @modelrgpd_id AND t1.`itemtype` = ".$item_type."" ,
"DELETE t1 FROM `glpi_plugin_dlteams_policieforms_items` as t1 INNER JOIN ".$master_table." as t2 ON t2.`id` = t1.`policieforms_id` WHERE t2.`entities_id` =  @modelrgpd_id ",

// "DELETE FROM `glpi_plugin_dlteams_policieforms` WHERE `entities_id` =  @modelrgpd_id",
// "DELETE FROM `glpi_plugin_dlteams_processeddatas` WHERE `entities_id` =  @modelrgpd_id",
// "DELETE FROM `glpi_plugin_dlteams_datacarriertypes` WHERE `entities_id` =  @modelrgpd_id",
// "DELETE FROM `glpi_plugin_dlteams_legalbasis_items` WHERE `entities_id` =  @modelrgpd_id",
"DELETE FROM `glpi_plugin_dlteams_policieforms` WHERE t2.`entities_id` =  @modelrgpd_id "
];

	foreach ($querys as $query) {
//var_dump ($query); die;
		$DB->queryOrDie($query, $DB->error());
    }

// STEP 2 : adding oid
//records : on ajoute les oid
	$query = "ALTER TABLE `glpi_plugin_dlteams_policieforms` ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
    $DB->queryOrDie($query, $DB->error());
	$query = "ALTER TABLE `glpi_plugin_dlteams_policieforms_items` ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid1` INT UNSIGNED NULL";
	$DB->queryOrDie($query, $DB->error());

	foreach ($objects as $object) {
	// 	$object & $object_items : ajout des oid
		$table = "glpi_plugin_dlteams_" . $object;
		$query = "ALTER TABLE ".$table." ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
		$DB->queryOrDie($query, $DB->error());
		$query = "ALTER TABLE ".$table."_items ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL";
		/*$DB->queryOrDie($query, $DB->error());
			$query = "SELECT COUNT(*) FROM " . $table. " WHERE `entities_id` = '$modelrgpd_id'";
			$row = $DB->query($query)->fetch_assoc();
			$message .= strval($row["COUNT(*)"]) . $table . " importés <br>";*/
   }

// STEP 3 : import en ajout des datas avec creation des id
   $fields_exports = [
	['glpi_plugin_dlteams_policieforms','`is_recursive`, `is_deleted`, `date_creation`, `name`, `content`, `documentcategories_id`, `oid`'],
	['glpi_plugin_dlteams_policieforms_items','`itemtype`, `comment`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
	['glpi_plugin_dlteams_processeddatas', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
	['glpi_plugin_dlteams_processeddatas_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
	['glpi_plugin_dlteams_datacarriertypes', '`is_recursive`, `name`, `comment`, `date_creation`, `oid`'],
	['glpi_plugin_dlteams_datacarriertypes_items', '`itemtype`, `comment`, `date_creation`, `oid`, `items_oid`'],
	['glpi_plugin_dlteams_legalbasis', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
	['glpi_plugin_dlteams_legalbasis_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
	['glpi_plugin_dlteams_storageperiods', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
	['glpi_plugin_dlteams_storageperiods_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
];

   foreach ($fields_exports as list($table, $fields_export)) {
		// si model dossier install/datas ; sinon dossier files/_plugins/dlteams/
			//$glpiRoot=str_replace('\\', '/', GLPI_ROOT);
			//$file_pointer = $glpiRoot. "/marketplace/dlteams/install/datas/" . $table . ".dat";
		$file_pointer = plugin_dlteams_root . "/install/datas/" . $table . ".dat";
		// pour export d'une entité, on envoie dans files/plugins
			// $file_pointer = $glpiRoot. "/files/_plugins/" . "dlteams"."/" . $table . ".dat";
		// pour tests, on prend le dossier de prod
			//$file_pointer = "/var/www/dlteams_app/marketplace/dlteams/install/datas/" . $table . ".dat";
		$query = "LOAD DATA INFILE '".$file_pointer."' INTO TABLE ".$table." FIELDS TERMINATED BY '\t' ($fields_export)";
		//var_dump($query); die;
		$DB->queryOrDie($query, $DB->error());
			$query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0";
			$row = $DB->query($query)->fetch_assoc();
			$message .= $table . " : " . strval($row["COUNT(*)"]) . nl2br("\n") ;
   }

// STEP 4 : update relation id
	// pour les tables objets, on met à jour entities_id = origin + date + copy_id
	$entities_id_target = $modelrgpd_id;
	$date = date('Y-m-d H:i:s');
    foreach ($fields_exports as list($table, $fields_export)) {
		$endoftable = substr($table, -6);
		if ($endoftable <> "_items") {
		$query = "UPDATE ".$table." SET `entities_id` = " . $entities_id_target . ", `date_mod` = " . '"' . $date . '"' . ", `copy_id` =  `oid` WHERE `oid` is not null" ;
		$DB->queryOrDie($query, $DB->error());
		}
		// $query = "UPDATE ".$table." SET `date_creation` =  WHERE `oid` is not null" ;
		// $DB->queryOrDie($query, $DB->error());
	}

	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.oid = t2.oid 
	SET t1.`records_id` = t2.`id`, t1.`date_mod` = " . '"' . $date . '"';
    $DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_concernedpersons` as t3 ON t1.`items_oid` = t3.`oid` AND t1.`itemtype` = ". '"PluginDlteamsConcernedPerson"' .
	" SET t1.`items_id` = t3.`id`";
    $DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_processeddatas` as t3 ON t1.`items_oid1` = t3.`oid` AND t1.`itemtype1` = ". '"PluginDlteamsProcessedData"' .
	"SET t1.`items_id1` = t3.`id`";
    $DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_legalbasis` as t3 ON t1.`items_oid` = t3.`oid` AND t1.`itemtype` = " . '"PluginDlteamsLegalbasi"' .
	"SET t1.`items_id` = t3.`id`";
	$DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_storageperiods` as t3 ON t1.`items_oid` = t3.`oid` AND t1.`itemtype` = " . '"PluginDlteamsStoragePeriod"' .
	"SET t1.`items_id` = t3.`id`";
	$DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_rightmeasures` as t3 ON t1.`items_oid` = t3.`oid` AND t1.`itemtype` = " . '"PluginDlteamsRightMeasure"'.
	"SET t1.`items_id` = t3.`id`";
	$DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_thirdpartycategories` as t3 ON t1.`items_oid` = t3.`oid` AND t1.`itemtype` = " . '"PluginDlteamsThirdPartyCategory"'.
	"SET t1.`items_id` = t3.`id`";
	$DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_protectivemeasures` as t3 ON t1.`items_oid` = t3.`oid` AND t1.`itemtype` = " . '"PluginDlteamsProtectiveMeasure"'.
	"SET t1.`items_id` = t3.`id`";
	$DB->queryOrDie($query, $DB->error());

	$query = "UPDATE `glpi_plugin_dlteams_concernedpersons_items` as t1 
	INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t2.`oid` = t1.`items_oid`  
	INNER JOIN `glpi_plugin_dlteams_concernedpersons` as t3 ON t3.`oid` = t1.`oid`
	INNER JOIN `glpi_plugin_dlteams_processeddatas` as t4 ON t4.`oid` = t1.`items_oid1`
	SET t1.`concernedpersons_id` = t3.`id`, t1.`items_id` = t2.`id`, t1.`items_id1` = t4.`id`";
    $DB->queryOrDie($query, $DB->error());

	$query = "UPDATE `glpi_plugin_dlteams_processeddatas_items` as t1 
	INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.items_oid = t2.oid 
	INNER JOIN `glpi_plugin_dlteams_processeddatas` as t3 ON t1.oid = t3.oid
	INNER JOIN `glpi_plugin_dlteams_concernedpersons` as t4 ON t1.items_oid1 = t4.oid
	SET t1.`processeddatas_id` = t3.`id`, t1.`items_id` = t2.`id`, t1.`items_id1` = t4.`id`";
    $DB->queryOrDie($query, $DB->error());

	$query = "UPDATE `glpi_plugin_dlteams_legalbasis_items` as t1 
	INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_oid` = t2.`oid` 
	INNER JOIN `glpi_plugin_dlteams_legalbasis` as t3 ON t1.`oid` = t3.`oid` 
	SET t1.`legalbasis_id` = t3.`id`, t1.`items_id` = t2.`id`";
	$DB->queryOrDie($query, $DB->error());

	//$result = $DB->query('SELECT ROW_COUNT() AS nombre_enregistrements');
	$query = "UPDATE `glpi_plugin_dlteams_storageperiods_items` as t1 
	INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_oid` = t2.`oid` 
	INNER JOIN `glpi_plugin_dlteams_storageperiods` as t3 ON t1.`oid` = t3.`oid` 
	SET t1.`storageperiods_id` = t3.`id`, t1.`items_id` = t2.`id`";
	$DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_thirdpartycategories_items` as t1 
	INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_oid` = t2.`oid` 
	INNER JOIN `glpi_plugin_dlteams_thirdpartycategories` as t3 ON t1.`oid` = t3.`oid` 
	SET t1.`thirdpartycategories_id` = t3.`id`, t1.`items_id` = t2.`id`";
	$DB->queryOrDie($query, $DB->error());
	$query = "UPDATE `glpi_plugin_dlteams_protectivemeasures_items` as t1 
	INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.`items_oid` = t2.`oid` 
	INNER JOIN `glpi_plugin_dlteams_protectivemeasures` as t3 ON t1.`oid` = t3.`oid` 
	SET t1.`protectivemeasures_id` = t3.`id`, t1.`items_id` = t2.`id`";
	$DB->queryOrDie($query, $DB->error());

// STEP 5 Delete oid
   foreach ($fields_exports as list($table, $fields_export)) {
		$query = "ALTER TABLE $table DROP IF EXISTS `oid`, DROP IF EXISTS `items_oid`, DROP IF EXISTS `items_oid1`";
		$DB->queryOrDie($query, $DB->error());
		}

Session::addMessageAfterRedirect($message);
header("Refresh:0; url=config.form.php");

////////////////// OLD
/*	$tables = ['glpi_plugin_dlteams_allitems',
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
*/
/*
on avait initialement
policieforms, id = 250 + policieforms_items, itemtype = ProcesseDatas, items_id = 350, itemtype = Datacarriertype, itemsid = 21, itemtype = LegalBasi, itemsid = 88, itemtype = StoragePeriod, itemsid = 61
ProcessedDatas_items  + Daccarriertype_items + Legalbasis_items + Storageperiod_items : itemtype = PolicieForm, items_id = 250

Step 1 : on supprime les traitements et les liaisons dépendances de model-rgpd
delete policieform where entities_id = 250 + suppression des %tablesliées_items where itemtype = PolicieForm & items_id = 250 + suppressions de policieform_items where policiforms_id = 250
après STEP1 : id = 250 n'existe plus + toutes ses %_items avec items_id = 250 supprimées

Step 2 : ajout des champs oid sur les tables

Step 3 : ajout des enregistrement venant du fichier texte 
policieforms, id = 417 + ProcesseDatas = 700 + Datacarriertype = 52 + LegalBasi = 233+ StoragePeriod = 547

Step 4 : reconstitution des id à partir des oid
ProcessedDatas_items  + Daccarriertype_items + Legalbasis_items + Storageperiod_items : itemtype = PolicieForm, items_id = 417
Puis policieforms_items, itemtype = ProcesseDatas, items_id = 700, itemtype = Datacarriertype, itemsid = 52, itemtype = LegalBasi, itemsid = 233, itemtype = StoragePeriod, itemsid = 547

-> à partir de là on a plein d'enregistrements en double
SI ON VEUT UPDATER

Le policieform existe-t-til ? 
si il existe entity_model + id_model identique (250 & 417) alors 
si date_majmodel du policieform avec oid = 0 plus ancien alor son met à jour le 250

-- update des tables par rapport aux modeles
update policieform as t1
innerjoin policieform_model as t2
on t1.entity_model = t1.entity_model, t1.id_model = t1.id_model
set t1.name, t1.content = t2.name, t2.content
where t1.date_majmodel < t2.date_majmodel and t1.type_majmodel = 1

-- mise à jour des contenu des tables liées
update ProcessedDatas + Datacarriertype + LegalBasi + StoragePeriod

-- ajout de liaisons manquantes
supposons que l'on ait policieform 250 (id model_) relié avec ProcesseDatas = 350 + Datacarriertype = 21
supposons que l'ont ait policieform_model (417) relié avec ProcesseDatas (700) + Datacarriertype (52) + LegalBasi(233) + StoragePeriod(547)
ce sont les id model qui relient 


Si on fait l'export avec les id originaux + l'import dans une table _model

-->>>comment faire l'import avec l'id  ??? 

Alors 
-- update des tables par rapport aux modeles
update policieform as t1
innerjoin policieform_model as t2
on t1.entity_model = t2.entity_id, t1.id_model = t2.id
set t1.name = t2.name, t1.content = t2.content
where t1.date_majmodel < t2.date_modif and t1.type_majmodel = 1

-- ajout des enregistrements manquants
insert into legalbasi as t1 (entities_id, name, content, comment, documentcategories_id, id_model)
SELECT entities_id, name, content, comment, documentcategories_id, date_creation
FROM legalbasi_model as t2
WHERE (select t1.id_model where t1.id_model <> t2.id)


-- update des liaisons
si on a policieform 250 (id model_417) relié avec ProcesseDatas = 350 (id model_700) + Datacarriertype = 21 (id model_52)
et 		policieform_model (417) relié avec ProcesseDatas (700) + Datacarriertype (52) + LegalBasi(233) + StoragePeriod(547)

1/ ajouter les dépendances manquantes
insert into legalbasi (entities_id, name, content, comment, documentcategories_id, id_model)
SELECT entities_id, name, content, comment, documentcategories_id, date_creation
FROM legalbasi_model



2/ ajouter les liaisons
insert into policieform_items (entities_id, itemtype, items_id)
SELECT entities_id, name, content, comment, documentcategories_id, date_creation
FROM `glpi`.`glpi_plugin_dlteams_policieforms`
WHERE `glpi`.`glpi_plugin_dlteams_policieforms`.id = 56; 




1/ import des bases modeles : 
delete from `legalbasis_model`; -> Ainsi on a toujours les mêmes id + entities_id qui peuvent alors servir de id_model 
INSERT INTO `legalbasis_model` (`id`, `entities_id`, `is_recursive`, `date_mod`, `is_deleted`, `date_creation`, `is_helpdesk_visible`, `users_id`, `copy_id`, `copy_entityid`, `copy_date`, `copy_update`, `name`, `plugin_dlteams_legalbasistypes_id`, `content`, `comment`, `url`, `url2`, `entity_model`, `id_model`, `date_majmodel`, `type_majmodel`) VALUES
(1, 1, 0, '2022-01-14 11:37:19', 0, '2022-01-14 11:37:19', 0, 0, 0, 0, NULL, 0, 'Article 6-1a (consentement)', 1, 'la personne concernée a consenti au traitement de ses données à caractère personnel pour une ou plusieurs finalités spécifiques;', '', '', '', 0, 0, '2022-03-28 22:00:00', 0),
(10, 11, 0, '2022-01-14 11:37:19', 0, '2022-01-14 11:37:19', 0, 0, 0, 0, NULL, 0, 'Loi Hamon relative à la e-consommation', 4, 'Loi no 2014-344 du 17 mars 2014 : meilleure information produits, droit de rétractation, de remboursement avec formulaires et délais, code de conduite du marchand', '', '', '', 0, 0, '2022-03-28 22:00:00', 0);

Mise à jour des bases actives : 
2/ on update l'existant grâce à id_model
update policieform as t1
innerjoin policieform_model as t2
on t1.entity_model = t2.entity_id, t1.id_model = t2.id //on met à jour tous les enregistrements de toutes les entités
set t1.name = t2.name, t1.content = t2.content
where t1.date_majmodel < t2.date_modif and t1.type_majmodel = 1

3/ ajout des enregistrements manquants dans rgpd_model
insert into legalbasi as t1 ($rgpd_model, name, content, comment, documentcategories_id, id_model)
SELECT entities_id, name, content, comment, documentcategories_id, date_creation
FROM legalbasi_model as t2
WHERE (select t1.id where t1.id <> t2.id_model)

4/ ajout les liaisons manquantes pour policieform
on a policieform_model 25 relié avec ProcesseDatas 231 + Datacarriertype 52 + LegalBasi 233 + StoragePeriod 547
soit policieform_items_model 
		policieforms_id = 28, itemtype = ProcesseDatas, items_id = 231
		policieforms_id = 28, itemtype = Datacarriertype, items_id = 52
		policieforms_id = 28, itemtype = legalbasi, items_id = 233
		policieforms_id = 28, itemtype = StoragePeriod, items_id = 547

supposons 
		policieform 250, id_model = 28 
et policieform_items
		policieforms_id = 250, itemtype = ProcessDatas, items_id = 350
		policieforms_id = 250, itemtype = Datacarriertype, items_id = 21
	
	ajout des id_model des tables liées (pour voir les manquants)
		policieforms_id = 250, itemtype = ProcesseDatas, items_id = 350, id_model_main = 28, id_model_linked = 231, 
		policieforms_id = 250, itemtype = Datacarriertype, items_id = 21, id_model_main = 28, id_model_linked = 52
	
	ajout des éléments manquants : 
		INSERT INTO policieform_items as t2 (id_model_main, itemtype, id_model_linked...)
		SELECT t1.policieforms_id, t1.itemtype, t1.items_id, ...
		FROM policieform_items_model as t1
		FULL OUTER JOIN t2 ON t1.itemtype = t2.itemtype, t1.items_id = t2.id_model_linked
		WHERE t2.itemtype IS NULL;
	
	ensuite on va chercher les items_id depuis les modèles
		update policieform_items as t1
		inner join Datacarriertype as t2
		on t1.id_model_linked = t2.id_model
		set t1.items_id = t2.id
		where t1.itemtype = Datacarriertype and t1.items_id is null
	
	enfin, on va chercher les policies_forms
		update policieform_items as t1
		inner join policieform as t2
		on t1.id_model_main = t2.id_model
		set t1.policieforms_id = t2.id
		where t1.policieforms_id is null