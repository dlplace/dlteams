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

class PluginDlteamsItemType extends CommonDBTM
{
    static private $types = [];
    static private $isInit = false;

    static public function registerType($type)
    {
        if (!in_array($type, self::$types)) {
            self::$types[] = $type;
        }
    }

    static public function init()
    {
        if (!self::$isInit) {
            self::$isInit = true;
            $types = [
                'Computer',
                'Datacenter',
                'Database',
                'Document',
				'NetworkEquipment',
				'Phone',
				'PluginDlteamsAccountKey',
				'PluginDlteamsAppliance',
                'PluginDlteamsAudit',
                'PluginDlteamsConcernedPerson',
                'PluginDlteamsDataCatalog',
                'PluginDlteamsDeliverable',
                'PluginDlteamsKnowBaseItem',
                'PluginDlteamsLegalBasi',
				'PluginDlteamsNetworkPort',
                'PluginDlteamsPhysicalStorage',
                'PluginDlteamsPolicieForm',
                'PluginDlteamsProcedure',
                'PluginDlteamsProcessedData',
				'PluginDlteamsProjectTask_Deliverable',
                'PluginDlteamsProtectiveMeasure',
                'PluginDlteamsRecord',
                'PluginDlteamsRightMeasure',
                'PluginDlteamsRiskAssessment',
				'PluginDlteamsStep',
                'PluginDlteamsStoragePeriod',
                'PluginDlteamsThirdPartyCategory',
                'PluginDlteamsTrainingCertification',
				'PluginDlteamsTrainingSession',
				'Rack',
				'Supplier',
                'PluginDlteamsDeliverable_Variable',
				'PluginDlteamsProcedure_Variable',
                'PluginDlteamsProcess',
                'PluginDlteamsProcedure',
                'PluginDlteamsStorageUnitType',
                'PluginDlteamsTicketTask',
                'PluginDlteamsActivityCategory',
                'Project',
                PluginDlteamsStep::class,
                TicketTask::class,
                PluginDlteamsTicketTask::class,
                KnowbaseItem::class,
                PluginDlteamsITILFollowup::class

            ];

            $plugin = new Plugin();

            if ($plugin->isActivated('formcreator')) {
                $types [] = 'PluginFormcreatorFormanswer';
                $types [] = 'PluginFormcreatorForm';
            }

            $types = array_merge($types, self::$types);

            foreach ($types as $type) {
                PluginDlteamsItemType::registerType($type);
            }
        }
    }

    static public function getItemsTypes() // permet de déclarer les classe d'objet
    {
        $types = [
            Appliance_Item::class,
			Computer_Item::class,
            Database_Item::class,
			Datacenter_Item::class,
            Document_Item::class,
			NetworkEquipment_Item::class,
			Phone_Item::class,
			PluginAccountKey_Item::class,
			PluginDlteamsAppliance_Item::class,
            //PluginDlteamsDataCarrier_Item::class,
            PluginDlteamsDataCatalog_Item::class,
            PluginDlteamsGroup_Item::class,
            PluginDlteamsSupplier_Item::class,
            PluginDlteamsRecord_Item::class,
            PluginDlteamsConcernedPerson_Item::class,
            PluginDlteamsPhysicalStorage_Item::class,
            PluginDlteamsProcessedData_Item::class,
            PluginDlteamsLegalBasi_Item::class,
            PluginDlteamsStoragePeriod_Item::class,
            PluginDlteamsThirdPartyCategory_Item::class,
            PluginDlteamsRightMeasure_Item::class,
            PluginDlteamsProtectiveMeasure_Item::class,
			PluginDlteamsProjectTask2_Item::class,
            PluginDlteamsStep::class,
            PluginDlteamsITILFollowup::class,
            // 'PluginDlteamsStorageUnitType'
        ];
        return $types;
    }

    static public function getItemsTypesExceptRecordItem()		// ??
    {
        $types = [
            Document_Item::class,
			Computer_Item::class,
			PluginDlteamsAppliance_Item::class,
            PluginDlteamsConcernedPerson_Item::class,
            PluginDlteamsDataCatalog_Item::class,
            PluginDlteamsGroup_Item::class,
            PluginDlteamsLegalBasi_Item::class,
            PluginDlteamsProtectiveMeasure_Item::class,
			PluginDlteamsProcessedData_Item::class,
            PluginDlteamsRightMeasure_Item::class,
            PluginDlteamsSupplier_Item::class,
            PluginDlteamsStoragePeriod_Item::class,
            PluginDlteamsThirdPartyCategory_Item::class,
            PluginDlteamsITILFollowup::class,
            // PluginDlteamsDataCarrier_Item::class,
        ];

        return $types;
    }

    static public function getTypes($all = false)
    {
        if (empty(self::$types)) {
            self::init();
        }

        if ($all) {
            return self::$types;
        }

        // Only allowed types
        $types = self::$types;

        foreach ($types as $key => $type) {
            if (!class_exists($type)) {
                unset($types[$key]);
            } else {
                $item = new $type();
                if (!$item->canView()) {
                    unset($types[$key]);
                }
            }
        }
        return $types;
    }

    static public function getGlpiTypesForMenu($all = false)
    {
        return [
            Project::class
        ];
    }

    static public function objetsReliablesDlregister() // objets pouvant être reliés
    {
        return [
            'Appliance' => ['PluginDlteamsLegalbasi',],
            'Contact' => ['PluginDlteamsLegalbasi',],
            'Contract' => ['PluginDlteamsLegalbasi',],
            'Computer' => ['PluginDlteamsProtectiveMeasure', 'PluginDlteamsDataCatalog'],
			'Datacenter' => ['PluginDlteamsProtectiveMeasure'],
			// 'DataCarrier' => ['PluginDlteamsProtectiveMeasure'],
            'Database' => ['PluginDlteamsProtectiveMeasure',],
            'Document' => ['PluginDlteamsProtectiveMeasure',],
            'NetworkEquipment' => ['PluginDlteamsProtectiveMeasure',],
            'Peripheral' => ['PluginDlteamsProtectiveMeasure',],
			'Phone' => ['PluginDlteamsProtectiveMeasure',],
			'PhysicalStorage' => ['PluginDlteamsProtectiveMeasure'],
			'Printer' => ['PluginDlteamsProtectiveMeasure',],
			'Rack' => ['PluginDlteamsProtectiveMeasure',],
            'Supplier' => ['PluginDlteamsProtectiveMeasure',],
            'User' => ['PluginDlteamsProtectiveMeasure'],
			'PluginDlteamsAccountKey' => ['PluginDlteamsProtectiveMeasure'],
        ];
    }

    static public function getSpecificsTypes($typesToShow = [])
    {
        foreach ($typesToShow as $key => $type) {
            if (!class_exists($type)) {
                unset($typesToShow[$key]);
            } else {
                $item = new $type();
                if (!$item->canView()) {
                    unset($typesToShow[$key]);
                }
            }
        }
        return $typesToShow;
    }
}
