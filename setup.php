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

define('plugin_dlteams_version', '24.05.05');
define('plugin_dlteams_root', __DIR__);
// include_once __DIR__ . '/dlteams.php';
function plugin_init_dlteams()
{
    global $PLUGIN_HOOKS;
    global $CFG_GLPI;
    $GLOBALS["DLTEAMS_FILES_TO_RENAME"] = [
        "/src/CommonDBTM.php",
        "/src/Plugin.php",
        "/front/commonitiltask.form.php",
        "/src/Search.php",
    ];
    // Register types
    // Register types for Associated items
    PluginDlteamsItemType::init();

    // ajout d'un onglet sur les class citées
    Plugin::registerClass('ManualLink', ['addtabon' => 'KnowbaseItem']);
    // Plugin::registerClass('PluginPdfConfig', ['addtabon' => 'Config']);
    //$PLUGIN_HOOKS['config_page']['pdf'] = 'front/config.form.php';
    /*
        $types = PluginDlteamsItemType::getTypes();
        // Register our classes
        foreach ($types as $type) {
            //TODO use preg_match to register more classes
            if (preg_match('/^PluginDlteams([a-zA-Z]+)/', $type) == 1) {
                Plugin::registerClass($type, [
                    "document_types" => true,
                    "helpdesk_visible_types" => true,
                    "linkgroup_types" => true,
                    "linkuser_types" => true,
                    "linkgroup_tech_types" => true,
                    "linkuser_tech_types" => true,
                    "ticket_types" => true,
                    "infocom_types" => true,
                    "networkport_types" => true,
                    "reservation_types" => true,
                    "contract_types" => true,
                    "unicity_types" => true,
                    "location_types" => true,
                    "itemdevices_types" => true,
                ]);
            }
        }
    */


//    $PLUGIN_HOOKS['menu_entry']['dlteams'] = 'front/tickettask.php';
//    $PLUGIN_HOOKS['menu_toadd']['dlteams']['management'] = PluginDlteamsTicketTask::class;
    $PLUGIN_HOOKS['assign_to_ticket']['dlteams'] = true;

    Plugin::registerClass(ManualLink::class, ['addtabon' => [Ticket::class, 'KnowbaseItem']]);
    Plugin::registerClass(PluginDlteamsLocation_Item::class, ['addtabon' => [Location::class, 'KnowbaseItem']]);
//    Plugin::registerClass(PluginDlteamsTicketTask::class, ['addtabon' => [Ticket::class]]);
    Plugin::registerClass(PluginDlteamsObject_document::class, ['addtabon' => [Ticket::class]]);

    Plugin::registerClass(PluginDlteamsDataCatalog::class,
        ['linkgroup_types' => true,
//            'linkuser_types' => true,
//            'linkgroup_tech_types' => true,
//            'linkuser_tech_types' => true,
            'document_types' => true,
            'ticket_types' => true,
            'helpdesk_visible_types' => true,
            'notificationtemplates_types' => true,
        ]
    );

//    var_dump("hhh");
//    die();
    Plugin::registerClass(PluginDlteamsRecord::class,
        ['linkgroup_types' => true,
            'linkuser_types' => true,
            'linkgroup_tech_types' => true,
            'linkuser_tech_types' => true,
            'document_types' => true,
            'ticket_types' => true,
            'helpdesk_visible_types' => true,
            'notificationtemplates_types' => true,
        ]
    );

    Plugin::registerClass(PluginDlteamsAccountKey_Item::class,
        [
            'linkgroup_types' => true,
            'linkuser_types' => true,
            'linkgroup_tech_types' => true,
            'linkuser_tech_types' => true,
            'document_types' => true,
            'ticket_types' => true,
            'helpdesk_visible_types' => true,
            'notificationtemplates_types' => true,
        ]
    );

    Plugin::registerClass(PluginDlteamsRecord_Item::class,
        [
            'linkgroup_types' => true,
            'linkuser_types' => true,
            'linkgroup_tech_types' => true,
            'linkuser_tech_types' => true,
            'document_types' => true,
            'ticket_types' => true,
            'helpdesk_visible_types' => true,
            'notificationtemplates_types' => true,
        ]
    );

    Html::requireJs('tinymce');
    $PLUGIN_HOOKS['csrf_compliant']['dlteams'] = true;
    $PLUGIN_HOOKS['use_massive_action']['dlteams'] = 1;


    if (Session::getLoginUserID()) {
        //Plugin::registerClass('PluginDlteamsProfile', ['addtabon' => ['Profile']]);
        $CFG_GLPI['impact_asset_types']['PluginDlteamsLegalBasi'] = "marketplace/dlteams/legalbasi.png";
        $PLUGIN_HOOKS['change_profile']['dlteams'] = ['PluginDlteamsProfile', 'initProfile'];

        if (Session::haveRight('plugin_dlteams_record', UPDATE)
            || Session::haveRight('config', UPDATE)) {
            $PLUGIN_HOOKS['config_page']['dlteams'] = 'front/config.form.php';
        }
        //Plugin::registerClass('PluginDlteamsControllerInfo', ['addtabon' => ['Entity']]);
        $PLUGIN_HOOKS['post_init']['dlteams'] = 'plugin_post_init_dlteams';
        $PLUGIN_HOOKS['add_css']['dlteams'] = 'plugin.css';
        $PLUGIN_HOOKS['add_css']['dlteams'] = 'css/libraries/jquery-ui.css';

//        if (Session::haveRight("plugin_dlteams_controllerinfo", READ)) {
//            $PLUGIN_HOOKS['menu_toadd']['dlteams'] = ['assets' => 'PluginDlteamsPhysicalStorage'];
//        }
        if (Session::haveRight("plugin_dlteams_controllerinfo", READ)) {
            $PLUGIN_HOOKS['helpdesk_menu_entry']['physicalstorages'] = plugin_dlteams_root . '/front/physicalstorage.form.php';
            $PLUGIN_HOOKS['helpdesk_menu_entry_icon']['physicalstorages'] = PluginDlteamsPhysicalStorage::getIcon();
        }
        if (Session::haveRight("plugin_dlteams_controllerinfo", READ)) {
            $PLUGIN_HOOKS['helpdesk_menu_entry']['vehicles'] = plugin_dlteams_root . '/front/vehicle.form.php';
            $PLUGIN_HOOKS['helpdesk_menu_entry_icon']['vehicles'] = PluginDlteamsVehicle::getIcon();
        }
    }

    $PLUGIN_HOOKS['process_massive_actions_plan']['dlteams'] = ['PluginDlteamsDatabase_Item' => 'processMassiveActionsForOneItemtypetest'];
    $PLUGIN_HOOKS['display_login']['dlteams'] = 'dlteams_display_login';
    $PLUGIN_HOOKS['item_add']['dlteams'] = ['Supplier' => 'dlteams_additem_called'];


    $PLUGIN_HOOKS['add_javascript']['dlteams'] = 'js/plugin.js';
    $PLUGIN_HOOKS['add_javascript']['dlteams'] = 'js/libraries/jquery-ui.js';
    $PLUGIN_HOOKS['redefine_menus']['dlteams'] = 'plugin_dlteams_redefine_menus';

    $PLUGIN_HOOKS['pre_item_action_massive']['dlteams'] = 'dlteams_pre_item_action_massive';
    $PLUGIN_HOOKS['item_purge']['dlteams'] = [
        'Link' => 'dlteams_purge_called',
    ];

    // permet l'ajout d'entrées de menus
    $PLUGIN_HOOKS['menu_toadd']['dlteams']['assets'][] = PluginDlteamsNetworkPort::class;
//    $PLUGIN_HOOKS['menu_toadd']['dlteams']['assets'][] = PluginDlteamsPhysicalStorage::class;
    $PLUGIN_HOOKS['menu_toadd']['dlteams']['helpdesk'][] = PluginDlteamsTicketTask::class;
    $PLUGIN_HOOKS['menu_toadd']['dlteams']['helpdesk'][] = PluginDlteamsProjectTask::class;
//	$PLUGIN_HOOKS['menu_toadd']['dlteams']['assets'][] = PluginDlteamsVehicle::class;
//	$PLUGIN_HOOKS['menu_toadd']['dlteams']['assets'][] = PluginDlteamsVehicle::class;

    global $CFG_GLPI;
    $CFG_GLPI['appliance_types'][] = 'PluginDlteamsDataCatalog';
    $CFG_GLPI["ticket_types"] = array_merge($CFG_GLPI["ticket_types"], PluginDlteamsItemType::getTypes(true));

//    activer whitelabel
}
// Autoriser les événements pour les tickets
	function plugin_dlteams_AssignToTicket($types) {
		$types[PluginDlteamsAudit::class] = PluginDlteamsAudit::getTypeName(2);
		$types[PluginDlteamsAccountKey::class] = PluginDlteamsAccountKey::getTypeName(2);
		$types[PluginDlteamsConcernedPerson::class] = PluginDlteamsConcernedPerson::getTypeName(2);
		$types[PluginDlteamsDataCatalog::class] = PluginDlteamsDataCatalog::getTypeName(2);
		$types[PluginDlteamsDeliverable::class] = PluginDlteamsDeliverable::getTypeName(2);
		$types[PluginDlteamsLegaBasi::class] = PluginDlteamsLegalBasi::getTypeName(2);
		$types[PluginDlteamsPolicieForm::class] = PluginDlteamsPolicieForm::getTypeName(2);
		$types[PluginDlteamsProcedure::class] = PluginDlteamsProcedure::getTypeName(2);
		$types[PluginDlteamsProtectiveMeasure::class] = PluginDlteamsProtectiveMeasure::getTypeName(2);
		$types[PluginDlteamsRecord::class] = PluginDlteamsRecord::getTypeName(2);
		$types[PluginDlteamsRiskAssessment::class] = PluginDlteamsRiskAssessment::getTypeName(2);
		$types[PluginDlteamsRightMeasure::class] = PluginDlteamsRightMeasure::getTypeName(2);
		$types[PluginDlteamsStoragePeriod::class] = PluginDlteamsStoragePeriod::getTypeName(2);
		$types[PluginDlteamsThirdpartyCategory::class] = PluginDlteamsThirdpartyCategory::getTypeName(2);
		$types[PluginDlteamsTrainingSession::class] = PluginDlteamsTrainingSession::getTypeName(2);
		return $types;
	}

function plugin_post_init_dlteams() {
    // ajout d'onglets dans certaines classe de GLPI
    Plugin::registerClass(Location::class, ['addtabon' => [PluginDlteamsPhysicalStorage::class]]);
    Plugin::registerClass(ManualLink::class, ['addtabon' => ['Ticket', 'KnowbaseItem']]);
//    Plugin::registerClass('PluginDlteamsTicketTask_Item', ['addtabon' => 'Ticket']);
    Plugin::registerClass('KnowbaseItem_Comment', ['addtabon' => ['Ticket']]);
    
	// ajout d'onglets dans certaines classe de dlteams
    Plugin::registerClass(PluginDlteamsAccountKey_Item::class, ['addtabon' => ['User', 'Group', 'Contact', 'Supplier']]);
    Plugin::registerClass(PluginDlteamsAcces_Catalog::class, ['addtabon' => ['Contact', 'Supplier']]);
    Plugin::registerClass('PluginDlteamsControllerInfo', ['addtabon' => ['Entity']]);
    Plugin::registerClass('PluginDlteamsNotification', ['notificationtemplates_types' => true]);
    Plugin::registerClass('PluginDlteamsProfile', ['addtabon' => ['Profile']]);
    // Plugin::registerClass(PluginDlteamsAudit_Item::class, ['addtabon' => [Datacenter::class]]);
    Plugin::registerClass(PluginDlteamsUserCreatePDF::class, ['addtabon' => ['User']]);
    // ajout de l'onglet Elements DLTeams -> n'a aps d'effet pour le moment

    Plugin::registerClass(
        'PluginDlteamsRecord_Item', [
        'addtabon' => [
		//  'Group',
		//  'User',
            'Supplier',
            'Document',
            'Ticket',
        ]]);
    Plugin::registerClass(
        'PluginDlteamsProtectiveMeasure_Item', [
        'addtabon' => [
            'Computer',
            'Datacenter',
            'NetworkEquipment',
            'Peripheral',
            'Printer',
            'Phone',
            //'PluginAccountsAccount',
            'PluginDlteamstAccount'
        ]]);

    Plugin::registerClass(
        'PluginDlteamsDataCatalog_Item', [
        'addtabon' => [
            // 'Appliance', // ne marche pas à cause de l'onglet "éléments" de appliance
            // 'PluginDlteamsAppliance',
            'Computer', // ok pas d'onglet "éléments"
            'Datacenter', // idem
            'NetworkEquipment',  // idem
            'Peripheral',  // idem
            'Printer',   // idem
            'PluginDlteamsPhysicalStorage',   // ok objet dlplace
            'Phone', // ok
        ]]);

//    Plugin::registerClass(
//        PluginDlteamsTicket_Item::class, [
//        'addtabon' => [
//            Ticket::class,
//        ]]);
}

// dossier pour les fichiers export
function FolderExport() {
	$glpiRoot = str_replace('\\', '/', GLPI_ROOT);
	if (!file_exists($glpiRoot . "/files/_plugins/" . "dlteams" . "/")) {
		mkdir($glpiRoot . "/files/_plugins/" . "dlteams" . "/");
		chmod($glpiRoot . "/files/_plugins/" . "dlteams" . "/", 0777);
	}
}

function plugin_version_dlteams() {
    return [
        'name' => "DLTeams",
        'version' => plugin_dlteams_version,
        'author' => "DLPlace developers",
        'license' => "GPLv3+",
        'homepage' => "https://dlplace.fr",
        'minGlpiVersion' => '9.4',
        'requirements' => [
            'glpi' => [
                'min' => '9.4',
                'dev' => false
            ]
        ],
    ];
}

function plugin_dlteams_check_prerequisites() {

    global $DB;
    if (version_compare(GLPI_VERSION, '10.0', 'lt')
        || version_compare(GLPI_VERSION, '10.1', 'ge')) {
        if (method_exists('Plugin', 'messageIncompatible')) {
            echo Plugin::messageIncompatible('core', '10.0');
        }
        return false;
    } else {
        $query = "select * from glpi_plugins where directory = 'formcreator' and state = 1";
        $result_query = $DB->query($query);
        if ($DB->numRows($result_query) == 1) {
            return true;
        } else {
            echo "the plugin 'FormCreator' must be installed before using 'DLTeams'";
            return false;
        }
    }
}

function plugin_dlteams_check_config($verbose = false) {
    if (true) {
        return true;
    }
    if ($verbose) {
        echo __("Installed / not configured");
    }
    return false;
}

// Generate unique id for form based on server name, glpi directory and basetime
function plugin_dlteams_getUuid() {
    //encode uname -a, ex Linux localhost 2.4.21-0.13mdk #1 Fri Mar 14 15:08:06 EST 2003 i686
    $serverSubSha1 = substr(sha1(php_uname('a')), 0, 8);
    // encode script current dir, ex : /var/www/glpi_X
    $dirSubSha1 = substr(sha1(__FILE__), 0, 8);
    return uniqid("$serverSubSha1-$dirSubSha1-", true);
}
