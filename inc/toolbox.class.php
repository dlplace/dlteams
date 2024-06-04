<?php

class PluginDlteamsToolbox
{
    /**
     * Return a list of GLPI itemtypes.
     * These itemtypes will be available to attach fields containers on them,
     * and will be usable in dropdown / glpi_item fields.
     *
     * @return array
     */
    public static function getGlpiItemtypes(): array
    {
        global $CFG_GLPI, $PLUGIN_HOOKS;

        $assets_itemtypes = [
            Computer::class,
            Monitor::class,
            Software::class,
            NetworkEquipment::class,
            Peripheral::class,
            Printer::class,
            CartridgeItem::class,
            ConsumableItem::class,
            Phone::class,
            Rack::class,
            Enclosure::class,
            PDU::class,
            PassiveDCEquipment::class,
            Cable::class,
            Glpi\Socket::class,
        ];

        $actifs_itemtypes = [
            PluginDlteamsPhysicalStorage::class,
            PluginDlteamsAccessOpening::class,
        ];

        $rgpd_itemtypes = [
            PluginDlteamsRecord::class,
            PluginDlteamsPolicieForm::class,
            PluginDlteamsDataCatalog::class,
            PluginDlteamsAccountKey::class
        ];


        $management_itemtypes = [
            Datacenter::class,
            Appliance::class,
            Database::class,
            DatabaseInstance::class,
        ];


        $all_itemtypes = [
                _n('Asset', 'Assets', Session::getPluralNumber())         => $assets_itemtypes,
                __('Actifs')                                              => $actifs_itemtypes,
                __('Management')                                          => $management_itemtypes,
                __('RGPD')                                                => $rgpd_itemtypes,
            ];

        $plugin = new Plugin();
        if ($plugin->isActivated('genericobject') && method_exists('PluginGenericobjectType', 'getTypes')) {
            $go_itemtypes = [];
            foreach (array_keys(PluginGenericobjectType::getTypes()) as $go_itemtype) {
                if (!class_exists($go_itemtype)) {
                    continue;
                }
                $go_itemtypes[] = $go_itemtype;
            }
            if (count($go_itemtypes) > 0) {
                $all_itemtypes[$plugin->getInfo('genericobject', 'name')] = $go_itemtypes;
            }
        }

        $plugins_names = [];
        foreach ($all_itemtypes as $section => $itemtypes) {
            $named_itemtypes = [];
            foreach ($itemtypes as $itemtype) {
                $prefix = '';
                if ($itemtype_specs = isPluginItemType($itemtype)) {
                    $plugin_key = $itemtype_specs['plugin'];
                    if (!array_key_exists($plugin_key, $plugins_names)) {
                        $plugins_names[$plugin_key] = Plugin::getInfo($plugin_key, 'name');
                    }
                    $prefix = $plugins_names[$plugin_key] . ' - ';
                }

                $named_itemtypes[$itemtype] = $prefix . $itemtype::getTypeName(Session::getPluralNumber());
            }
            $all_itemtypes[$section] = $named_itemtypes;
        }

        // Remove empty lists (e.g. Plugin list).
        $all_itemtypes = array_filter($all_itemtypes);

        return $all_itemtypes;
    }
}