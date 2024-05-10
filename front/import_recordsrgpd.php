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


// STEP 1 : adding oid
$entities_id_origin = $_POST["entities_id_target"] ?? 30;
//records : on ajoute les oid
$query = "ALTER TABLE `glpi_plugin_dlteams_records` ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
$DB->queryOrDie($query, $DB->error());
$query = "ALTER TABLE `glpi_plugin_dlteams_records_items` ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid1` INT UNSIGNED NULL";
$DB->queryOrDie($query, $DB->error());
// 	concerned_persons : ajout des oid
$query = "ALTER TABLE `glpi_plugin_dlteams_concernedpersons` ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
$DB->queryOrDie($query, $DB->error());
$query = "ALTER TABLE `glpi_plugin_dlteams_concernedpersons_items` ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid1` INT UNSIGNED NULL";
$DB->queryOrDie($query, $DB->error());
$result = $DB->query('SELECT ROW_COUNT() AS nombre_enregistrements');
$message = "";
$message .= $_POST["entities_id_target"] . " Catégories de personnes copiées" . "<br>";
// 	processeddatas & processeddatas_items : ajout des oid
$table = "glpi_plugin_dlteams_processeddatas";
$query = "ALTER TABLE " . $table . " ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
$DB->queryOrDie($query, $DB->error());
$query = "ALTER TABLE " . $table . "_items ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid1` INT UNSIGNED NULL";
$DB->queryOrDie($query, $DB->error());
$result = $DB->query('SELECT ROW_COUNT() AS nombre_enregistrements');
$message .= $_POST["entities_id_target"] . " Catégories de données copiées" . "<br>";

// autres class
$objects = [
    'legalbasis',
    'storageperiods',
    'rightmeasures',
    'thirdpartycategories',
    'protectivemeasures'
];
foreach ($objects as $object) {
    // 	$object & $object_items : ajout des oid
    $table = "glpi_plugin_dlteams_" . $object;
    $query = "ALTER TABLE " . $table . " ADD IF NOT EXISTS `oid` INT UNSIGNED NULL";
    $DB->queryOrDie($query, $DB->error());
    $query = "ALTER TABLE " . $table . "_items ADD IF NOT EXISTS `oid` INT UNSIGNED NULL, ADD IF NOT EXISTS `items_oid` INT UNSIGNED NULL";
    $DB->queryOrDie($query, $DB->error());
}

// STEP 2 : import datas without id
$fields_exports = [
    ['glpi_plugin_dlteams_records', '`is_recursive`, `is_deleted`, `date_creation`, `number`, `name`, `content`, `additional_info`, `states_id`, `first_entry_date`, `consent_json`, `consent_type`, `consent_type1`, `consent_explicit`, 
	`plugin_dlteams_recordcategories_id`, `diffusion`, `conservation_time`, `archive_time`, `archive_required`, `right_information`, `right_correction`, `right_opposition`, `right_portability`, `sensitive`, `external_process`, `impact_person`, `impact_organism`, `specific_security_measures`, `oid`'],
    ['glpi_plugin_dlteams_records_items', '`itemtype`, `itemtype1`, `comment`, `json`, `timeline_position`, `date_creation`, `plugin_dlteams_storageendactions_id`, `plugin_dlteams_storagetypes_id`,
	`mandatory`, `oid`, `items_oid`, `items_oid1`'],
    ['glpi_plugin_dlteams_concernedpersons', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
    ['glpi_plugin_dlteams_concernedpersons_items', '`itemtype`, `itemtype1`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`, `items_oid1`'],
    ['glpi_plugin_dlteams_processeddatas', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
    ['glpi_plugin_dlteams_processeddatas_items', '`itemtype`, `itemtype1`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`, `items_oid1`'],
    ['glpi_plugin_dlteams_legalbasis', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
    ['glpi_plugin_dlteams_legalbasis_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
    ['glpi_plugin_dlteams_storageperiods', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
    ['glpi_plugin_dlteams_storageperiods_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
    ['glpi_plugin_dlteams_rightmeasures', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
    ['glpi_plugin_dlteams_rightmeasures_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
    ['glpi_plugin_dlteams_thirdpartycategories', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
    ['glpi_plugin_dlteams_thirdpartycategories_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
    ['glpi_plugin_dlteams_protectivemeasures', '`is_recursive`, `name`, `content`, `comment`, `date_creation`, `oid`'],
    ['glpi_plugin_dlteams_protectivemeasures_items', '`itemtype`, `comment`, `json`, `timeline_position`, `date_creation`, `oid`, `items_oid`'],
];

//foreach ($fields_exports as list($table, $fields_export)) {
$glpiRoot = str_replace('\\', '/', GLPI_ROOT); // /var/www/dev_dlteams_app


$temprandom = bin2hex(openssl_random_pseudo_bytes(16));

$files_data = [];

foreach ($_FILES["files_to_import"]["tmp_name"] as $key => $file_path) {
    $file_name = basename($_FILES["files_to_import"]["name"][$key]);
//        $destination = $storageDirectory . $file_name;
    $destination = $glpiRoot . "/files/_plugins/dlteams/temp/" . $temprandom . "/";
    $table_name = str_replace(".dat", "", $file_name);

    array_push($files_data, [
        "full_path" => $destination . $file_name,
        "destination_folder" => $destination,
        "table_name" => $table_name
    ]);

    // Créer les répertoires parents si nécessaire
    if (!file_exists($destination) && !is_dir($destination)) {
        mkdir($destination, 0755, true);
        chmod($destination, 0755);
    }

    move_uploaded_file($file_path, $destination . $file_name);
    chmod($destination . $file_name, 0755);
}


$DB->beginTransaction();
foreach ($fields_exports as list($table, $fields_export)) {
    $exist_in_uploaded_files = false;
    $current_file_data = null;

//    check if $table exist in uploaded files
    foreach ($files_data as $fd) {
        if ($fd["table_name"] == $table) {
            $exist_in_uploaded_files = true;
            $current_file_data = $fd;
        }
    }

//    if exist proceed to the importation
    if ($exist_in_uploaded_files) {
//        echo "$table<br/>";
        $query = "LOAD DATA INFILE '" . $current_file_data["full_path"] . "' INTO TABLE " . $current_file_data["table_name"] . " FIELDS TERMINATED BY '\t' (" . $fields_export . ")";
        try {
            $DB->queryOrDie($query, $DB->error());
        } catch (Exception $e) {
            $DB->rollBack();
        }
    }
}
$DB->commit();
/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();

//}

// STEP 3 : update relation id
// on approvisionne records pour entities_id = origin
$entities_id_target = $_POST["entities_id_target"] ?? 0;
foreach ($fields_exports as list($table, $fields_export)) {
    $query = "UPDATE " . $table . " SET `entities_id` = " . $entities_id_target . " WHERE `oid` is not null";

    $DB->query($query);
    if ($DB->error){
        Session::addMessageAfterRedirect($DB->error(), false, WARNING);
    }
}
//	$result = $DB->query('SELECT ROW_COUNT() AS nombre_enregistrements');
// if ($result && $DB->numrows($result) > 0) { $data = $DB->fetchAssoc($result); $modelrgpd_id = $data['id']; }
// on approvisionne les _items pour entities_id = origin
/*
select t1.id, t2.id, t3.id, t1.oid, t2.oid, t3.oid  from `glpi_plugin_dlteams_records_items` as t1
INNER JOIN `glpi_plugin_dlteams_concernedpersons` as t3
ON t1.`items_id` = t3.`id` and t3.`oid` is not null
INNER JOIN `glpi_plugin_dlteams_records` as t2
ON  t1.`records_id` = t2.`id`;

select t1.id, t1.`records_id`, t1.oid, t1.`items_id`, t1.`itemtype`, t1.`items_oid`, t1.`items_id1`, t1.`items_oid1`, t1.`itemtype1`
from `glpi_plugin_dlteams_records_items` as t1
where t1.`oid` is not null AND t1.`itemtype` = "PluginDlteamsConcernedPerson"
order by t1.`items_oid`;

select t1.id, t2.id, t3.id from `glpi_plugin_dlteams_records_items` as t1
INNER JOIN `glpi_plugin_dlteams_concernedpersons` as t3
ON t1.`items_oid` = t3.`oid`
INNER JOIN `glpi_plugin_dlteams_records` as t2
ON  t1.`oid` = t2.`oid` ;

UPDATE `glpi_plugin_dlteams_records_items` as t1
INNER JOIN `glpi_plugin_dlteams_records` as t2
ON t1.oid = t2.oid and `itemtype` = "PluginDlteamsConcernedPerson"
SET t1.`records_id` = t2.'id'

*/


//	$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 INNER JOIN `glpi_plugin_dlteams_concernedpersons` as t3
//  ON t1.oid = t2.oid AND t1.items_oid = t3.oid SET t1.`records_id` = t2.`id`, t1.`items_id` = t3.`id`";
$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.oid = t2.oid SET t1.`records_id` = t2.`id`";
$DB->queryOrDie($query, $DB->error());
$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_concernedpersons` as t3 ON t1.`items_oid` = t3.`oid` and t1.`itemtype` = 'PluginDlteamsConcernedPerson'
	SET t1.`items_id` = t3.`id`";
$DB->queryOrDie($query, $DB->error());
$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_processeddatas` as t3 ON t1.`items_oid1` = t3.`oid` and t1.`itemtype1` = 'PluginDlteamsProcessedData' 
	SET t1.`items_id1` = t3.`id`";
$DB->queryOrDie($query, $DB->error());
$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_legalbasis` as t3 ON t1.`items_oid` = t3.`oid` and t1.`itemtype` = 'PluginDlteamsLegalbasi' 
	SET t1.`items_id` = t3.`id`";
$DB->queryOrDie($query, $DB->error());
$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_storageperiods` as t3 ON t1.`items_oid` = t3.`oid` and t1.`itemtype` = 'PluginDlteamsStoragePeriod' 
	SET t1.`items_id` = t3.`id`";
$DB->queryOrDie($query, $DB->error());
$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_rightmeasures` as t3 ON t1.`items_oid` = t3.`oid` and t1.`itemtype` = 'PluginDlteamsRightMeasure' 
	SET t1.`items_id` = t3.`id`";
$DB->queryOrDie($query, $DB->error());
$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_thirdpartycategories` as t3 ON t1.`items_oid` = t3.`oid` and t1.`itemtype` = 'PluginDlteamsThirdPartyCategory' 
	SET t1.`items_id` = t3.`id`";
$DB->queryOrDie($query, $DB->error());
$query = "UPDATE `glpi_plugin_dlteams_records_items` as t1 INNER JOIN `glpi_plugin_dlteams_protectivemeasures` as t3 ON t1.`items_oid` = t3.`oid` and t1.`itemtype` = 'PluginDlteamsProtectiveMeasure' 
	SET t1.`items_id` = t3.`id`";
$DB->queryOrDie($query, $DB->error());


$query = "UPDATE `glpi_plugin_dlteams_concernedpersons_items` as t1 INNER JOIN `glpi_plugin_dlteams_records_items` as t2 INNER JOIN `glpi_plugin_dlteams_concernedpersons` as t3 INNER JOIN `glpi_plugin_dlteams_processeddatas_items` as t4 
	ON t1.items_oid = t2.oid AND t1.items_oid1 = t3.oid SET t1.`concernedpersons_id` = t3.`id`, t1.`items_id` = t2.`items_id1`";
$DB->queryOrDie($query, $DB->error());
$query = "UPDATE `glpi_plugin_dlteams_processeddatas_items` as t1 INNER JOIN `glpi_plugin_dlteams_records_items` as t2 INNER JOIN `glpi_plugin_dlteams_processeddatas` as t3 INNER JOIN `glpi_plugin_dlteams_concernedpersons_items` as t4
	ON t1.items_oid = t2.oid AND t1.items_oid1 = t3.oid AND t4.items_oid = t2.oid SET t1.`processeddatas_id` = t3.`id`, t1.`items_id` = t2.`items_id1`, t1.`items_id1` = t3.`id`";
$DB->queryOrDie($query, $DB->error());

$DB->queryOrDie($query, $DB->error());
$query = "UPDATE `glpi_plugin_dlteams_legalbasis_items` as t1 INNER JOIN `glpi_plugin_dlteams_records` as t2 INNER JOIN `glpi_plugin_dlteams_legalbasis` as t3 
	ON t1.items_oid = t2.oid AND t1.items_oid = t3.oid SET t1.`legalbasis_id` = t3.`id`, t1.`items_id` = t2.`id`";
$DB->queryOrDie($query, $DB->error());
//$result = $DB->query('SELECT ROW_COUNT() AS nombre_enregistrements');
/*
// STEP 4 Delete oid
   foreach ($fields_exports as list($table, $fields_export)) {
		$query = "ALTER TABLE $table DROP IF EXISTS `oid`, DROP IF EXISTS `items_oid`, DROP IF EXISTS `items_oid1`";
		$DB->queryOrDie($query, $DB->error());
		}
*/

Session::addMessageAfterRedirect("xxx traitements importés");
header("Refresh:0; url=config.form.php");


/*
SELECT t1.id, t2.id, t1.oid, t2.oid
FROM `glpi_plugin_dlteams_records_items`as t1
INNER JOIN `glpi_plugin_dlteams_records` as t2 ON t1.oid = t2.oid  AND t1.itemtype = "PluginDlteamsConcernedperson"
INNER JOIN `glpi_plugin_dlteams_concernedpersons` as t3 ON t1.`items_oid` = t3.`oid`

*/
