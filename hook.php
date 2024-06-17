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

function plugin_dlteams_install()
{
    global $DB, $new_install;
    include_once(Plugin::getPhpDir("dlteams") . "/inc/profile.class.php");
// nouvelle installation ou mise à jour ?
    if (!$DB->TableExists("glpi_plugin_dlteams_records")) {
        $new_install = true; // no table so first install
        Session::addMessageAfterRedirect("Nouvelle installation effectuée");
    } else {
        $new_install = false;
        Session::addMessageAfterRedirect("Mise à jour du Plug'in effectuée");
    }

//    include_once(Plugin::getPhpDir("dlteams") . "/install/install.php");
//  création et mises à jour des tables
    error_reporting(E_ALL & ~E_WARNING);
    mysqli_report(MYSQLI_REPORT_OFF);
    $DB->runFile(plugin_dlteams_root . "/install/sql/update-1.0.sql");
    $DB->runFile(plugin_dlteams_root . "/install/sql/update-1.1.sql");
    $DB->runFile(plugin_dlteams_root . "/install/sql/update-1.2.sql");
    require_once(plugin_dlteams_root . "/install/sql/update-24.php");
	// $install = new PluginDlteamsInstall();
	// $install->install();

    PluginDlteamsProfile::initProfile();
    PluginDlteamsProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);

    /*global $DLTEAMS_FILES_TO_RENAME;
    foreach ($DLTEAMS_FILES_TO_RENAME as $file){
        copy(GLPI_ROOT."/marketplace/dlteams/install/fork/".GLPI_VERSION."/dlteams".$file, GLPI_ROOT . $file);
    }*/

    // function prepareFolderPub() {
    $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
    // var_dump ($glpiRoot . "/pub/") ; die ;
    if (!file_exists($glpiRoot . "/pub/")) {
        mkdir($glpiRoot . "/pub/");
        chmod($glpiRoot . "/pub/", 0310);
    }
    //return parent::prepareFolderPub();
    //}
    return true;
}

function plugin_dlteams_uninstall()
{
    global $DB;
    $DB->query('SET foreign_key_checks = 0');
    if (plugin_dlteams_root == "/var/www/dlteams_app/marketplace/dlteams" or plugin_dlteams_root == "/var/www/dev_dlteams_app/marketplace/dlteams" or plugin_dlteams_root == "/var/www/free_dlteams_app/marketplace/dlteams") {
        $mandatory_sites = 1;
    } else {
        $mandatory_sites = 0;
    }

//    disable dlteams fork
    $directory = GLPI_ROOT . "/marketplace/dlteams/install/fork/" . GLPI_VERSION . "/origin_glpi";
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $files = array();
    foreach ($rii as $file) {
        if ($file->isDir()) {
            continue;
        }
        $files[] = $file->getPathname();
    }
    foreach ($files as $path) {
        copy($path, GLPI_ROOT . str_replace($directory, "", $path));
    }
//    disable dlteams whitelabel
    $directory = GLPI_ROOT . "/marketplace/dlteams/install/whitelabel/" . GLPI_VERSION . "/origin_glpi";
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $files = array();
    foreach ($rii as $file) {
        if ($file->isDir()) {
            continue;
        }
        $files[] = $file->getPathname();
    }
    foreach ($files as $path) {
        copy($path, GLPI_ROOT . str_replace($directory, "", $path));
    }

    // deleting rgpd-model entitie + all dlteams database tables
	$requete = "SHOW TABLES LIKE 'glpi_plugin_dlteams_records'";
	$resultat = $DB->query($requete);
	if ($mandatory_sites == 0 && $resultat->num_rows > 0) {
			$DB->runFile(plugin_dlteams_root . "/install/sql/delete-modelrgpd.sql"); //delete model-rgpd + profile + user
			//$DB->runFile(plugin_dlteams_root . "/install/sql/delete-datas.sql"); //delete all dlteams database + delete displaypreference records + profilerights records
			drop_tables();
	}
	/* $options_tooltip['link_class'] = 'btn btn-outline-secondary';
    Html::showToolTip("Effacer toutes les données du plug'in ?", $options_tooltip);
	// Session::addMessageAfterRedirect("Effacer toutes les données du plug'in ?");
	// echo '<a href="#" onclick="confirmDelete()">Supprimer</a>';
	// var_dump ("effacement");
	function confirmDelete() {
		$confirmation = confirm("Effacer toutes les données du plug'in ?");
		if ($mandatory_sites == 0 && $confirmation) {
			$DB->runFile(plugin_dlteams_root . "/install/sql/delete-modelrgpd.sql"); //delete model-rgpd + profile + user
			//$DB->runFile(plugin_dlteams_root . "/install/sql/delete-datas.sql"); //delete all dlteams database + delete displaypreference records + profilerights records
			drop_tables();
		} else {
			alert("Suppression annulée.");
		}
	}*/
	return true;
}

function plugin_dlteams_getDropdown()
{
    return [
        PluginDlteamsActivityCategory::class => PluginDlteamsActivityCategory::getTypeName(2), // Catégories de traitement
        PluginDlteamsAuditCategory::class => PluginDlteamsAuditCategory::getTypeName(2), // Catégories d'audit
        PluginDlteamsCatalogClassification::class => PluginDlteamsCatalogClassification::getTypeName(2), // Classification de l'information
        PluginDlteamsDataCarrierCategory::class => PluginDlteamsDataCarrierCategory::getTypeName(2), // Catégories de base de données
        PluginDlteamsDataCarrierHosting::class => PluginDlteamsDataCarrierHosting::getTypeName(2), // Type d'hébergement
        PluginDlteamsDataCarrierType::class => PluginDlteamsDataCarrierType::getTypeName(2), // Type de base données
        PluginDlteamsDataCarrierManagement::class => PluginDlteamsDataCarrierManagement::getTypeName(2), // Type de gestion
        PluginDlteamsDataCategory::class => PluginDlteamsDataCategory::getTypeName(2), // Catégorie de données
        PluginDlteamsImpact::class => PluginDlteamsImpact::getTypeName(2), // Type d'impact
        PluginDlteamsKeyType::class => PluginDlteamsKeyType::getTypeName(2), // Type de clés
        PluginDlteamsLegalBasisType::class => PluginDlteamsLegalBasisType::getTypeName(2), // Type de bases légales
        PluginDlteamsMeansOfAcce::class => PluginDlteamsMeansOfAcce::getTypeName(2), // Moyens d'accès (aux données/catalogues)
        PluginDlteamsMediaSupport::class => PluginDlteamsMediaSupport::getTypeName(2), // Support, médias de collecte
        PluginDlteamsProtectiveCategory::class => PluginDlteamsProtectiveCategory::getTypeName(2), // Catégories des mesures de protection
        PluginDlteamsRightMeasureCategory::class => PluginDlteamsRightMeasureCategory::getTypeName(2), // Catégories de mesure
        PluginDlteamsProtectiveType::class => PluginDlteamsProtectiveType::getTypeName(2), // Types de mesures de protection
        PluginDlteamsSendingReason::class => PluginDlteamsSendingReason::getTypeName(2), // Motifs d'envoi d'informations
        PluginDlteamsServerType::class => PluginDlteamsServerType::getTypeName(2), //
        PluginDlteamsSIIntegration::class => PluginDlteamsSIIntegration::getTypeName(2), //Intégration dans le système d'information
        PluginDlteamsStorageType::class => PluginDlteamsStorageType::getTypeName(2), //Type de stockage
        PluginDlteamsStorageUnitType::class => PluginDlteamsStorageUnitType::getTypeName(2), // Catégories d'audit
        PluginDlteamsStorageEndAction::class => PluginDlteamsStorageEndAction::getTypeName(2), // Action en fin de stockage
        PluginDlteamsTransmissionMethod::class => PluginDlteamsTransmissionMethod::getTypeName(2), // Méthode de transmission
        PluginDlteamsUserProfile::class => PluginDlteamsUserProfile::getTypeName(2), // Rôle et droits pour les utilisateurs
		PluginDlteamsVehicleType::class => PluginDlteamsVehicleType::getTypeName(2), // Catégories de véhicule
    ];
}

function plugin_dlteams_getAddSearchOptions($itemtype)
{
    $options = [];
    if ($itemtype == 'Entity') {
        $options = PluginDlteamsControllerInfo::getSearchOptionsControllerInfo();
    }
    return $options;
}

//function add_default_where($temp, $test){
//
//    var_dump("kkk");
//    die();
//}

function plugin_dlteams_addSelect(...$args){
    switch ($args[0]){
        case "PluginDlteamsStep":
            break;
    }
}

function plugin_dlteams_getDatabaseRelations()
{
    $plugin = new Plugin();
    if ($plugin->isActivated('dlteams')) {
        return [
            'glpi_entities' => [
                'glpi_plugin_dlteams_accountkeys' => 'entities_id',
                'glpi_plugin_dlteams_configs' => 'entities_id',
                'glpi_plugin_dlteams_records' => 'entities_id',
                'glpi_plugin_dlteams_controllerinfos' => 'entities_id',
                'glpi_plugin_dlteams_impacts' => 'entities_id',
                'glpi_plugin_dlteams_storageendactions' => 'entities_id',
                'glpi_plugin_dlteams_sendingreasons' => 'entities_id',
                'glpi_plugin_dlteams_datacarriercategories' => 'entities_id',
                'glpi_plugin_dlteams_auditcategories' => 'entities_id',
                'glpi_plugin_dlteams_activitycategories' => 'entities_id',
                'glpi_plugin_dlteams_protectivecategories' => 'entities_id',
                'glpi_plugin_dlteams_datacarriertypes' => 'entities_id',
                'glpi_plugin_dlteams_datacarrierhostings' => 'entities_id',
                'glpi_plugin_dlteams_datacarriermanagements' => 'entities_id',
                'glpi_plugin_dlteams_catalogclassifications' => 'entities_id',
                'glpi_plugin_dlteams_legalbasis' => 'entities_id',
                'glpi_plugin_dlteams_legalbasistypes' => 'entities_id',
                'glpi_plugin_dlteams_physicalstorages' => 'entities_id',
                'glpi_plugin_dlteams_personaldatacategories' => 'entities_id',
                'glpi_plugin_dlteams_steps' => 'entities_id',
                'glpi_plugin_dlteams_securitymeasures' => 'entities_id',
                'glpi_plugin_dlteams_keytypes' => 'entities_id',
                'glpi_plugin_dlteams_protectivetypes' => 'entities_id',
                'glpi_plugin_dlteams_userprofiles' => 'entities_id',
				'glpi_plugin_dlteams_vehicles' => 'entities_id',
            ],
            'glpi_users' =>
                ['glpi_plugin_dlteams_controllerinfos' => ['users_id_representative', 'users_id_dpo',],
                    'glpi_plugin_dlteams_configs' => ['users_id_creator', 'users_id_lastupdater',],
                    'glpi_plugin_dlteams_activitycategories' => ['users_id_creator', 'users_id_lastupdater',],
                    'glpi_plugin_dlteams_impacts' => ['users_id_creator', 'users_id_lastupdater',],
                    'glpi_plugin_dlteams_storageendactions' => ['users_id_creator', 'users_id_lastupdater',],
                    'glpi_plugin_dlteams_sendingreasons' => ['users_id_creator', 'users_id_lastupdater',],
                    'glpi_plugin_dlteams_datacarriercategories' => ['users_id_creator', 'users_id_lastupdater',],
                    'glpi_plugin_dlteams_auditcategories' => ['users_id_creator', 'users_id_lastupdater',],
                    'glpi_plugin_dlteams_legalbasis' => ['users_id_creator', 'users_id_lastupdater',],
                    'glpi_plugin_dlteams_personaldatacategories' => ['users_id_creator', 'users_id_lastupdater',],
                    'glpi_plugin_dlteams_steps' => ['users_id_creator', 'users_id_lastupdater',],
                    'glpi_plugin_dlteams_records' => ['users_id_creator', 'users_id_lastupdater', 'users_id_owner',],
                    'glpi_plugin_dlteams_securitymeasures' => ['users_id_creator', 'users_id_lastupdater',],
					'glpi_plugin_dlteams_vehicles' => ['users_id_creator', 'users_id_lastupdater',],
                ],

            //'glpi_plugin_dlteams_storageendactions' => ['glpi_plugin_dlteams_records_actionfinperiodes' => 'plugin_dlteams_storageendactions_id',],
            //'glpi_plugin_dlteams_activitycategories' => ['glpi_plugin_dlteams_activitycategories' => 'glpi_plugin_dlteams_activitycategories',],
            //'glpi_plugin_dlteams_legalbasistypes' => ['glpi_plugin_dlteams_records_legalbasistypes' => 'plugin_dlteams_legalbasistypes_id',],
            //'glpi_plugin_dlteams_impactorganisms' => ['glpi_plugin_dlteams_records_impactorganisms' => 'plugin_dlteams_impactorganisms_id',],
            //'glpi_plugin_dlteams_legalbasisacts' => ['glpi_plugin_dlteams_records_legalbasis' => 'plugin_dlteams_legalbasisacts_id', 'glpi_plugin_dlteams_records_retentions' => 'plugin_dlteams_legalbasisacts_id',],
            // 'glpi_plugin_dlteams_personaldatacategories' => ['glpi_plugin_dlteams_records_personalanddatacategories' => 'plugin_dlteams_personaldatacategories_id',],
            //'glpi_plugin_dlteams_securitymeasures' => ['glpi_plugin_dlteams_records_securitymeasures' => 'plugin_dlteams_securitymeasures_id',],
            //'glpi_softwares' => ['glpi_plugin_dlteams_records_softwares' => 'softwares_id',],
        ];
    }
    return [];
}

function plugin_dlteams_postinit()
{
    global $PLUGIN_HOOKS;
    //$PLUGIN_HOOKS['item_purge']['dlteams'] = [];
    //$PLUGIN_HOOKS['item_purge']['dlteams']['Contract'] = ['PluginDlteamsRecord_Contract', 'cleanForItem'];
    //$PLUGIN_HOOKS['item_purge']['dlteams']['Software'] = ['PluginDlteamsRecord_Software', 'cleanForItem'];
    $PLUGIN_HOOKS['item_purge']['accounts']['KnowbaseItem_Item'] = ['ManualLink', 'cleanForItem'];
    $PLUGIN_HOOKS['item_purge']['accounts']['KnowbaseItem_Item'] = array('PluginAccountsAccount_Item', 'cleanForItem');
    CommonGLPI::registerStandardTab('KnowbaseItem_Item', 'PluginAccountsAccount_Item');
//    CommonGLPI::registerStandardTab('Computer_Item', 'PluginAccountsAccount_Item');


    /*foreach (PluginArchimapGraph::getTypes(true) as $type) {
       $PLUGIN_HOOKS['item_purge']['dlteams'][$type]
          = array('PluginDlteamsElementRGPD','cleanForItem');
       CommonGLPI::registerStandardTab($type, 'PluginDlteamsElementRGPD');
     }*/
}

/** Handle update item hook * @param CommonDBTM $item Item instance * @return void */
/*function dlteams_updateitem_called (CommonDBTM $item) {
   //do everything you want!
   //remember that $item is passed by reference (it is an abject)
   //so changes you will do here will be used by the core.
   if ($item::getType() === NetworkPort::getType()) {
      //we're working with a NetworkPort
   } elseif ($item::getType() === Computer::getType()) {
      //we're working with a Computer
   }
}*/

//function plugin_dlteams_addWhere($link, $nott, $itemtype, $ID, $val, $searchtype)
//{
//    if ($itemtype == PluginDlteamsTicketTask::class)
//        return " `glpi_tickets`.`entities_id` = " . Session::getActiveEntity();
//    return "";
//}

function plugin_dlteams_MassiveActions($itemtype)
{
    switch ($itemtype) {
//		 case Document_Item::class:
//		 return ['PluginDlteamsRecord' . MassiveAction::CLASS_ACTION_SEPARATOR . 'deleteRelation' => __('Supprimer la relation test', 'dlteams'),];
        case PluginDlteamsAccountKey::class:
            return ['PluginDlteamsAccountKey' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsRecord::class:
            return ['PluginDlteamsRecord' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsDataCatalog::class:
            return ['PluginDlteamsDataCatalog' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsRiskAssessment::class:
            return ['PluginDlteamsRiskAssessment' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsConcernedPerson::class:
            return ['PluginDlteamsConcernedPerson' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsProcessedData::class:
            return ['PluginDlteamsProcessedData' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsLegalbasi::class:
            return ['PluginDlteamsLegalbasi' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsStoragePeriod::class:
            return ['PluginDlteamsStoragePeriod' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsThirdPartyCategory::class:
            return ['PluginDlteamsThirdPartyCategory' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsPolicieForm::class:
            return ['PluginDlteamsPolicieForm' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsAudit::class:
            return ['PluginDlteamsAudit' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsProtectiveMeasure::class:
            return ['PluginDlteamsProtectiveMeasure' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case Project::class:
            return ['PluginDlteamsProject_Ma' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsProcedure::class:
            return ['PluginDlteamsProcedure' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsTrainingCertification::class:
            return ['PluginDlteamsTrainingCertification' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsTrainingSession::class:
            return ['PluginDlteamsTrainingSession' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsDeliverable::class:
            return ['PluginDlteamsDeliverable' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case Appliance::class:
            return ['PluginDlteamsAppliance' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case PluginDlteamsRightMeasure::class:
            return ['PluginDlteamsRightMeasure' . MassiveAction::CLASS_ACTION_SEPARATOR . 'copyTo' => __('Copy To', 'dlteams'),];
        case User::class:
            $action = [];
            $prefix = PluginDlteamsAccountKey_Item::class . MassiveAction::CLASS_ACTION_SEPARATOR;
            $action[$prefix . "assign_key_to_user"] = __('Assigner une clé à cet utilisateur', 'dlteams');


            $prefix = PluginDlteamsRecord_Item::class . MassiveAction::CLASS_ACTION_SEPARATOR;
            $action[$prefix . "assign_as_record_acteur"] = __('Assigner comme acteur d\'un traitement', 'dlteams');

            $prefix = PluginDlteamsUser_Item::class . MassiveAction::CLASS_ACTION_SEPARATOR;
            $action[$prefix . "assign_computer_to_user"] = __('Assigner un ordinateur', 'dlteams');

            $prefix = PluginDlteamsAccountKey_Item::class . MassiveAction::CLASS_ACTION_SEPARATOR;
            $action[$prefix . "create_account_and_assign_to_user"] = __('Créer des comptes et les attribuer', 'dlteams');

            return $action;


        case Group::class:
            $action = [];

            $prefix = PluginDlteamsAccountKey_Item::class . MassiveAction::CLASS_ACTION_SEPARATOR;
            $action[$prefix . "create_account_and_assign_to_group"] = __('Attribuer un compte à ce groupe', 'dlteams');

            $prefix = PluginDlteamsAccountKey_Item::class . MassiveAction::CLASS_ACTION_SEPARATOR;
            $action[$prefix . "create_account_and_assign_to_each_user_of_group"] = __(' Créer des comptes pour chaque utilisateur et les attribuer', 'dlteams');

            return $action;
        case Computer::class:
            $action = [];

            $prefix = PluginDlteamsProtectiveMeasure::class . MassiveAction::CLASS_ACTION_SEPARATOR;
            $action[$prefix . "add_protectivemeasure_to_computer"] = __('Ajouter une mesure de protection', 'dlteams');

            return $action;
    }
}

////// SPECIFIC MODIF MASSIVE FUNCTIONS ///////

function dlteams_additem_called(CommonDBTM $item)
{
    // On supplier creation : set it automaticaly to active
    if ($item::getType() === Supplier::getType()) {
        $item->fields['is_active'] = 1;
        $item->updateInDB(['is_active']);
        Session::addMessageAfterRedirect("Supplier auto set to active", true);
    }
}

// ajouter la même fonction pour rendre à oui l'ouverture d'une nouvelle fenetre dans item_link

function dlteams_display_login()
{
    global $CFG_GLPI;
    echo "
   <style>
      #logo_login img {
         display: none;
      }
      #logo_login {
          background-image: url('" . $CFG_GLPI['root_doc'] . "/marketplace/dlteams/images/banner.png');
          background-repeat: no-repeat;
          background-size: contain;
          width: 100%;
          height: 0;
          padding-top: 25%;
          padding-bottom: 0;
          margin: 0;
      }
      #text-login {
         display: none;
      }
      #firstboxlogin {
          background: #4F4E4E !important;
      }
      input.submit {
         background-color: #50E3C2 !important;
         border: #90F3E2 !important;
         color: #FFF !important;
      }
      #footer-login, a.copyright {
          color: #50E3C2 !important;
      }

      #footer-login:before {
          font-weight: bold;
          content: 'DL Place - ';
      }
   </style>";

    echo "<script>
// Change FAQ link on login page
let faq_link = document.querySelectorAll('#box-faq > a')[0];
faq_link.href = 'faq';

// SEO : add canonical link tag
let link = !!document.querySelector(\"link[rel='canonical']\") ? document.querySelector(\"link[rel='canonical']\") : document.createElement('link');
link.setAttribute('rel', 'canonical');
link.setAttribute('href', location.protocol + '//' + location.host + '/');
document.head.appendChild(link);
</script>";

    // Used to change title and favicon
    $path = $CFG_GLPI['root_doc'] . "/marketplace/dlteams";
    echo "<script src='$path/js/plugin.js'></script>";
}

/* Change the GLPI menu to modify "GRC" tab / @param $menu array / @return mixed */
function plugin_dlteams_redefine_menus(array $menu)
{
    // Not fully working : the headers sector isn't nicely displayed
    // plugins/genericobject/front/object.form.php:102 "assets" is not modifiable
    // todo modify generic object in order to input a custom "asset" field in header
    // $menu['grc']['content']['rgpd'] = $menu['assets']['content']['rgpd'];
    if (empty($menu)) {
        return $menu;
    }

//    $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
//    echo "Appelé par {$caller['function']} dans {$caller['file']} à la ligne {$caller['line']}";
//    die();
    if (array_key_exists('dlteams', $menu) === false) {
        $assets = [
            'default' => PluginDlteamsPhysicalStorage::getSearchURL(),
            'icon' => "fa-sharp fa-solid fa-th",
            'title' => 'Actifs',
            'content' => [
                'plugindlteamsphysicalstorage' => PluginDlteamsPhysicalStorage::getMenuContent(),
                'plugindlteamsvehicle' => PluginDlteamsVehicle::getMenuContent(),
                'plugindlteamsaccessopening' => PluginDlteamsAccessOpening::getMenuContent(),
            ],
//            'types' => 'PluginDlteamsMenu'
        ];



        $dlteams = [
            'default' => '/marketplace/dlteams/front/rgpd.php',
            'icon' => "fa-sharp fa-solid fa-circle-check",
            'title' => 'Conformité',
            'content' => [
                'plugindlteamsmenu' => PluginDlteamsMenu::getMenuContent(),
                'plugindlteamsiso27001' => PluginDlteamsIso27001::getMenuContent(),
//                'plugindlteamsvehicle' => PluginDlteamsVehicle::getMenuContent(),
            ],
//            'types' => 'PluginDlteamsMenu'

        ];

        $can_read_dashboard = Session::haveRight('dashboard', READ);


//        array_splice_assoc($menu, 2, 0, ['actifs' => $assets]);


            array_splice_assoc($menu, 2, 0, ['actifs' => $assets]);
            array_splice_assoc($menu, 3, 0, ['dlteams' => $dlteams]);

    }
/*    highlight_string("<?php\n\$data =\n" . var_export($menu, true) . ";\n?>");*/
//    die();
    return $menu;
}

// Handle update item hook - @param CommonDBTM $item Item instance - @return void
function dlteams_purge_called(CommonDBTM $item)
{
    switch ($item::getType()) {
        case Link::getType():
            PluginDlteamsDeliverable_Item::deleteItemAfterLinkDeletion($item);
            break;
    }
}

function array_splice_assoc(&$input, $offset, $length, $replacement = array())
{
    $replacement = (array)$replacement;
    $key_indices = array_flip(array_keys($input));
    if (isset($input[$offset]) && is_string($offset)) {
        $offset = $key_indices[$offset];
    }
    if (isset($input[$length]) && is_string($length)) {
        $length = $key_indices[$length] - $offset;
    }

    $input = array_slice($input, 0, $offset, TRUE)
        + $replacement
        + array_slice($input, $offset + $length, NULL, TRUE);
}

function drop_tables() {
    global $DB;
    $objects = [
        'accountkeys', 'accountkeys_items', 'activitycategories', 'activitycategories_items', 'allitems', 'auditcategories', 'auditcategories_items', 'audits', 'audits_items', 'catalogclassifications', 'concernedpersons', 'concernedpersons_items', 'configs', 'conservationcategory', 'consent', 'contacts_items', 'controllerinfos', 'databasehostings', 'databasemanagements',  'datacarriercategories', 'datacarrierhostings', 'datacarriermanagements', 'datacarriertypes', 'datacarriertypes_items', 'datacatalogs', 'datacatalogs_items', 'datacategories', 'datacenters_items', 'deliverables', 'deliverables_contents', 'deliverables_items', 'dlteams_deliverables_sections', 'deliverables_variables', 'deliverables_variables_items', 'documents_items', 'groups_items', 'impacts', 'impactorganisms', 'keytypes', 'legalbasis', 'legalbasistypes', 'legalbasis_items', 'links_items', 'meansofacces', 'meansofacces_items', 'mediasupports', 'networkequipments_items', 'pdus_items', 'peripherals_items', 'phones_items', 'physicalstorages', 'physicalstorages_items', 'policieforms', 'policieforms_items', 'processeddatas', 'processeddatas_items', 'processes', 'processes_items', 'protectivecategories', 'protectivemeasures', 'protectivemeasures_items', 'protectivetypes', 'records', 'records_actionfinperiodes', 'records_contracts', 'records_items', 'records_legalbasis', 'records_legalbasisacts', 'records_personalanddatacategories', 'records_retentions', 'records_securitymeasures', 'records_softwares', 'records_consent', 'records_externals', 'storages', 'records_storages', 'records_impacts', 'records_elements', 'records_basedonnees', 'records_impactorganisms', 'rightmeasurecategories', 'rightmeasures', 'rightmeasures_items', 'riskassessments', 'riskassessments_items', 'sendingreasons', 'servertypes', 'storageendactions', 'storageperiods', 'storageperiods_items', 'storagetypes', 'thirdpartycategories', 'thirdpartycategories_items', 'trainingcategories', 'trainingcertifications', 'trainings', 'userprofiles', 'users_items',  'printers_items', 'procedures', 'procedures_contents', 'procedures_items', 'procedures_sections', 'procedures_variables', 'procedures_variables_items', 'racks_items', 'siintegrations', 'trainingsessions', 'trainingsessions_items', 'transmissionmethods', 'steps_items', 'storageunittypes', 'vehicles', 'vehicletypes',
    ];
    foreach ($objects as $object) {
        $table = "glpi_plugin_dlteams_" . $object;
        $query = "DROP TABLE IF EXISTS " . $table . " ";
        $DB->queryOrDie($query, $DB->error());
    }
    // $query = "ALTER TABLE `glpi_computers_items` DROP IF EXISTS `comment`";
    // $DB->queryOrDie($query, $DB->error());
    // $query = "DELETE FROM `glpi_documentcategories` WHERE `completename` like 'Conformité RGPD%'"; //delete documentcategories records
    // $DB->queryOrDie($query, $DB->error());
}