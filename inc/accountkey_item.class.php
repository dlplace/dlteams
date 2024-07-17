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

class PluginDlteamsAccountKey_Item extends CommonDBTM
{
    static public $itemtype_2 = PluginDlteamsAccountKey::class;
    static public $itemtype_1;
    public static $items_id_1 = 'accountkeys_id';
    private static string $title;
    public static int $PROFILE_ADMIN = 2;

    private static function assignAccountToUser(mixed $id, array $POST, &$errormessage)
    {
        global $DB;
        $DB->beginTransaction();
        $accountkey = new PluginDlteamsAccountKey();

        $user = new User();
        $user->getFromDB($id);

        $name = $POST["name"];
        static::processNameAndTransform($name, $id);

        //if (!$accountkey->getFromDBByCrit(["name" => strtolower($name)])) {
        $catalog = new PluginDlteamsDataCatalog();
        $catalog->getFromDB($POST["datacatalogs_id"]);
        $keyid = $accountkey->add([
            "name" => strtolower($name),
            "plugin_dlteams_keytypes_id" => $catalog->fields["default_keytype"]??0,
            "entities_id" => $_SESSION['glpiactive_entity'],
            "plugin_dlteams_datacatalogs_id" => $POST["datacatalogs_id"]
        ]);


        if ($keyid) {
            $accountkey_item = new PluginDlteamsAccountKey_Item();
            $user_item = new PluginDlteamsUser_Item();
            $elements_geres_utilises_fields = [];
            if ($accountkey && $accountkey->fields["name"] && str_contains($accountkey->fields["name"], "Administrateur") && static::canAccessAnnuaireCatalog($ma->POST['accountkeys_id'])) {
                $array2["users_id_tech"] = $id;
            } else {
                $array2["users_id"] = $id;
            }

            if ($accountkey_item->add([
                    "itemtype" => User::class,
                    "items_id" => $id,
                    "accountkeys_id" => $keyid,
                    "users_id" => $id,
                    "name" => strtolower($name)
                ]) && $user_item->add([
                    "itemtype" => PluginDlteamsAccountKey::class,
                    "items_id" => $keyid,
                    "users_id" => $id
                ])) {
                Session::addMessageAfterRedirect(sprintf("Compte <a href='%s'>%s</a> créé et attribué avec succès à %s ", PluginDlteamsAccountKey::getFormURLWithID($keyid), $POST["name"], $user->fields["name"]));
                $DB->commit();
            } else {
                $errormessage .= sprintf("erreur d'attribution de %s à %s <br/>", $POST["name"], $user->fields["name"]);
                // Example of ko count
                $DB->rollback();
                //$ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
            }
            return true;
        }
        else {
            Session::addMessageAfterRedirect(sprintf("Une erreur s'est produite, le compte n'a été créé"), 0, ERROR);
            return false;
        }


    }


    private static function processNameAndTransform(mixed &$name, $id = null)
    {
        preg_match_all('/##(.*?)##/', $name, $matches);
        foreach ($matches[1] as $key => $match) {
            $var = str_replace("user.", "", $match);

            $user = new User();
            $user->getFromDB($id);

            if ($var == "mail") {
                $name = str_replace($matches[0][$key], UserEmail::getDefaultForUser($id), $name);
            } elseif (str_contains($var, '.premiercaractere')) {
                $attribute = str_replace(".premiercaractere", "", $var);
                if($attribute == "name") // on utilise realname au lieu de name
                    $attribute = "realname";
                if (isset($user->fields[$attribute])) {
                    $name = str_replace($matches[0][$key], $user->fields[$attribute][0], $name);
                } else {
                    Session::addMessageAfterRedirect(sprintf("%s n'a pas pu été traité correctement - $attribute", $matches[0][$key]), 0, WARNING);
                }
            } elseif (isset($user->fields[$var])) {
                $name = str_replace($matches[0][$key], $user->fields[$var], $name);
            } else {
                $name = str_replace($matches[0][$key], "", $name);
                Session::addMessageAfterRedirect(sprintf("%s n'a pas pu été traité correctement - $attribute", $matches[0][$key]), 0, WARNING);
            }
        }
    }

    function canCreateItem()
    {
        return true;
    }

    function canViewItem()
    {
        return true;
    }

    function canUpdateItem()
    {
        return true;
    }

    function canDeleteItem()
    {
        return true;
    }

    function canPurgeItem()
    {
        return true;
    }

    static function canCreate()
    {
        return true;
    }

    static function canView()
    {
        return true;
    }

    static function canUpdate()
    {
        return true;
    }

    static function canDelete()
    {
        return true;
    }

    static function canPurge()
    {
        return true;
    }

    public static function getFormURLWithID($id = 0, $full = true)
    {
        $itemtype = PluginDlteamsAccountKey::class;
        $itemtype_item = new PluginDlteamsAccountKey_Item();
        $itemtype_item->getFromDB($id);
        $link = $itemtype::getFormURL($full);
        $link .= (strpos($link, '?') ? '&' : '?') . 'id=' . $itemtype_item->fields["accountkeys_id"];
        return $link;
    }

    // Return the localized name of the current Type

    public static function getTypeName($nb = 0)
    {
        return _n('Compte ou clé', 'Compte ou clé', $nb, 'dlteams');
    }

    private static function showForItemgetRequest(CommonDBTM $item, $isdirectory = false)
    {
        global $DB;
        $idx = [$item->fields["id"]];

        if (!$isdirectory && $item->getType() == PluginDlteamsDataCatalog::class) {
            $iterator_catalogs = $DB->request([
                'SELECT' => [
                    'glpi_plugin_dlteams_datacatalogs.id AS linkid',
                    'glpi_plugin_dlteams_datacatalogs.*',
                ],
                'FROM' => 'glpi_plugin_dlteams_datacatalogs',
                'OR' => [
                    [
                        'glpi_plugin_dlteams_datacatalogs.plugin_dlteams_datacatalogs_id' => $item->fields["id"],
                        'glpi_plugin_dlteams_datacatalogs.is_directoryservice' => 1,
                        'glpi_plugin_dlteams_datacatalogs.entities_id' => $_SESSION['glpiactiveentities'],
                        'glpi_plugin_dlteams_datacatalogs.is_deleted' => 0,

                    ],
                    [
                        'glpi_plugin_dlteams_datacatalogs.plugin_dlteams_datacatalogs_id' => $item->fields["plugin_dlteams_datacatalogs_id"],
                        'glpi_plugin_dlteams_datacatalogs.is_directoryservice' => 1,
                        'glpi_plugin_dlteams_datacatalogs.entities_id' => $_SESSION['glpiactiveentities'],
                        'glpi_plugin_dlteams_datacatalogs.is_deleted' => 0,
                    ]
                ],
                'ORDER' => ['name ASC'],
            ], "", true);

            foreach ($iterator_catalogs as $data) {
                $idx[] = $data["id"];
            }
        }

        if ($isdirectory && $item->getType() == PluginDlteamsDataCatalog::class)
            $query = [
                "SELECT" => [
                    PluginDlteamsDataCatalog_Item::getTable() . ".id as linkid",
                    PluginDlteamsDataCatalog_Item::getTable() . ".*",
                    PluginDlteamsAccountKey::getTable() . ".name as name",
                    'glpi_plugin_dlteams_keytypes' . '.name AS keytypename',
                    'glpi_plugin_dlteams_keytypes' . '.id AS keytypeid',
                ],
                "FROM" => PluginDlteamsDataCatalog_Item::getTable(),
                "LEFT JOIN" => [
                    PluginDlteamsAccountKey::getTable() => [
                        'FKEY' => [
                            PluginDlteamsAccountKey::getTable() => "id",
                            PluginDlteamsDataCatalog_Item::getTable() => "items_id"
                        ]
                    ],
                    'glpi_plugin_dlteams_keytypes' => [
                        'ON' => [
                            PluginDlteamsAccountKey::getTable() => 'plugin_dlteams_keytypes_id',
                            'glpi_plugin_dlteams_keytypes' => 'id'
                        ]
                    ]
                ],
                "WHERE" => [
                    "itemtype" => PluginDlteamsAccountKey::class,
                    "datacatalogs_id" => $item->fields["id"],
                    "is_directory" => true
                ],
//                "ORDER" => [
//
//                ]
            ];
        else
            $query = [
                "SELECT" => [
                    PluginDlteamsAccountKey_Item::getTable() . ".id as linkid",
                    PluginDlteamsAccountKey_Item::getTable() . ".*",
                    PluginDlteamsDataCatalog::getTable() . ".name as datacataloguename",
                    PluginDlteamsAccountKey::getTable() . ".name as name",
                    'glpi_plugin_dlteams_keytypes' . '.name AS keytypename',
                    'glpi_plugin_dlteams_keytypes' . '.id AS keytypeid',
                ],
                "FROM" => PluginDlteamsAccountKey_Item::getTable(),
                "LEFT JOIN" => [
                    PluginDlteamsAccountKey::getTable() => [
                        'FKEY' => [
                            PluginDlteamsAccountKey::getTable() => "id",
                            PluginDlteamsAccountKey_Item::getTable() => "accountkeys_id"
                        ]
                    ],
                    'glpi_plugin_dlteams_keytypes' => [
                        'ON' => [
                            PluginDlteamsAccountKey::getTable() => 'plugin_dlteams_keytypes_id',
                            'glpi_plugin_dlteams_keytypes' => 'id'
                        ]
                    ],
                    PluginDlteamsDataCatalog::getTable() => [
                        'FKEY' => [
                            PluginDlteamsDataCatalog::getTable() => "id",
                            PluginDlteamsAccountKey_Item::getTable() => "items_id"
                        ]
                    ],
                ],
                "WHERE" => [
                    "itemtype" => $item->getType(),
                    "items_id" => $idx,
                    PluginDlteamsAccountKey::getTable() . ".is_deleted" => 0
                ],
                "ORDER" => ["datacataloguename ASC", PluginDlteamsAccountKey::getTable().".name ASC"]
            ];


        $iterator = $DB->request($query);

        $result = [];
        foreach ($iterator as $data) {
            $result[] = $data;
        }

        return $result;
    }

    public static function getUserComptes(User $user)
    {
        global $DB;
        $query = "SELECT `glpi_plugin_dlteams_users_items`.`id` AS `linkid`, 
                            `glpi_plugin_dlteams_users_items`.*, 
                            `glpi_plugin_dlteams_accountkeys`.`name` AS `name`, 
                            `glpi_plugin_dlteams_keytypes`.`name` AS `keytypename`, 
                            `glpi_plugin_dlteams_keytypes`.`id` AS `keytypeid`,
                            `glpi_plugin_dlteams_datacatalogs`.`name` as `annuaire_name`
                    FROM `glpi_plugin_dlteams_users_items` 
                    LEFT JOIN `glpi_plugin_dlteams_accountkeys` 
                        ON (`glpi_plugin_dlteams_accountkeys`.`id` = `glpi_plugin_dlteams_users_items`.`items_id`) 
                    LEFT JOIN `glpi_plugin_dlteams_keytypes` 
                        ON (`glpi_plugin_dlteams_accountkeys`.`plugin_dlteams_keytypes_id` = `glpi_plugin_dlteams_keytypes`.`id`)
                    LEFT JOIN `glpi_plugin_dlteams_datacatalogs` 
                        ON (`glpi_plugin_dlteams_accountkeys`.`plugin_dlteams_datacatalogs_id` = `glpi_plugin_dlteams_datacatalogs`.`id`)
                    WHERE `glpi_plugin_dlteams_users_items`.`itemtype` = 'PluginDlteamsAccountKey' 
                    AND `glpi_plugin_dlteams_users_items`.`users_id` = '" . $user->fields["id"] . "' 
                    AND `glpi_plugin_dlteams_keytypes`.`id` <> '4'";


        $iterator = $DB->query($query);

        $result = [];
        foreach ($iterator as $data) {
            $result[] = $data;
        }

        return $result;
    }

    public static function getUserComptesPhysique(User $item)
    {
        global $DB;
        $query = [
            "SELECT" => [
                PluginDlteamsUser_Item::getTable() . ".id as linkid",
                PluginDlteamsUser_Item::getTable() . ".*",
                PluginDlteamsAccountKey::getTable() . ".name as name",
                'glpi_plugin_dlteams_keytypes' . '.name AS keytypename',
                'glpi_plugin_dlteams_keytypes' . '.id AS keytypeid',
            ],
            "FROM" => PluginDlteamsUser_Item::getTable(),
            "LEFT JOIN" => [
                PluginDlteamsAccountKey::getTable() => [
                    'FKEY' => [
                        PluginDlteamsAccountKey::getTable() => "id",
                        PluginDlteamsUser_Item::getTable() => "items_id"
                    ]
                ],
                'glpi_plugin_dlteams_keytypes' => [
                    'FKEY' => [
                        PluginDlteamsAccountKey::getTable() => 'plugin_dlteams_keytypes_id',
                        'glpi_plugin_dlteams_keytypes' => 'id'
                    ]
                ]
            ],
            "WHERE" => [
                PluginDlteamsUser_Item::getTable() . ".itemtype" => PluginDlteamsAccountKey::class,
                PluginDlteamsUser_Item::getTable() . ".users_id" => $item->fields["id"],
                "glpi_plugin_dlteams_keytypes.id" => 4 // 4 = compte physique
            ]
        ];

        $iterator = $DB->request($query);

        $result = [];
        foreach ($iterator as $data) {
            $result[] = $data;
        }

        return $result;
    }

    public static function showItemsGetRequest(PluginDlteamsAccountKey $item)
    {
        global $DB;
        $query = [
            "SELECT" => [
                PluginDlteamsAccountKey_Item::getTable() . ".id as linkid",
                PluginDlteamsAccountKey_Item::getTable() . ".*",
                'glpi_plugin_dlteams_keytypes' . '.name AS keytypename',
                'glpi_plugin_dlteams_keytypes' . '.id AS keytypeid',
            ],
            "FROM" => PluginDlteamsAccountKey_Item::getTable(),
            "LEFT JOIN" => [
                PluginDlteamsAccountKey::getTable() => [
                    'FKEY' => [
                        PluginDlteamsAccountKey::getTable() => "id",
                        PluginDlteamsAccountKey_Item::getTable() => "accountkeys_id"
                    ]
                ],
                'glpi_plugin_dlteams_keytypes' => [
                    'ON' => [
                        PluginDlteamsAccountKey::getTable() => 'plugin_dlteams_keytypes_id',
                        'glpi_plugin_dlteams_keytypes' => 'id'
                    ]
                ]
            ],
            "OR" => [
                [
                    PluginDlteamsAccountKey_Item::getTable() . ".itemtype" => User::class,
                    PluginDlteamsAccountKey_Item::getTable() . ".accountkeys_id" => $item->fields["id"],
                ],
                [
                    PluginDlteamsAccountKey_Item::getTable() . ".itemtype" => Supplier::class,
                    PluginDlteamsAccountKey_Item::getTable() . ".accountkeys_id" => $item->fields["id"],
                ],
                [
                    PluginDlteamsAccountKey_Item::getTable() . ".itemtype" => Group::class,
                    PluginDlteamsAccountKey_Item::getTable() . ".accountkeys_id" => $item->fields["id"],
                ],
                [
                    PluginDlteamsAccountKey_Item::getTable() . ".itemtype" => Contact::class,
                    PluginDlteamsAccountKey_Item::getTable() . ".accountkeys_id" => $item->fields["id"],
                ]
            ]
        ];

        $iterator = $DB->request($query);
        $result = [];
        foreach ($iterator as $data) {
            $result[] = $data;
        }
        return $result;
    }

    static function getTypeNameForClass($nb = 0)
    {
        return __("Attributions", 'dlteams');
    }

    public static function getUserPhones($user_id)
    {

        $user = new User();
        $user->getFromDB($user_id);
        global $DB;

        $user_groups = Group_User::getUserGroups($user->getID());
        $groupsIds = array_column($user_groups, 'groups_id');

        $query = "SELECT `glpi_phones`.*, `glpi_phonemodels`.`name` as `phonemodel_name` FROM `glpi_phones` 
                  LEFT JOIN `glpi_phonemodels`
                    ON (`glpi_phonemodels`.`id` = `glpi_phones`.`phonemodels_id`)
                    WHERE ((`glpi_phones`.`users_id_tech` = '" . $user_id . "') 
                    OR (`glpi_phones`.`users_id` = '" . $user_id . "'))
                    ";

        $iterator = $DB->request($query);
        $phones = [];
        foreach ($iterator as $phone) {
            $phones[] = $phone;
        }
        return $phones;
    }

    public static function getUserComputers($user_id)
    {
        $user = new User();
        $user->getFromDB($user_id);
        global $DB;

        $user_groups = Group_User::getUserGroups($user->getID());
        $groupsIds = array_column($user_groups, 'groups_id');

        $query = "SELECT `glpi_computers`.*  FROM `glpi_computers` 
                    WHERE ((`glpi_computers`.`users_id_tech` = '" . $user_id . "') 
                    OR (`glpi_computers`.`users_id` = '" . $user_id . "'))
                    ";

        $iterator = $DB->request($query);

        $computers = [];
        foreach ($iterator as $phone) {
            $computers[] = $phone;
        }

        return $computers;
    }

    public static function getUserCatalogs($user_id)
    {
        global $DB;
//        recuperer les clés de l'utilisateur
        $query = [
            "SELECT" => [
                PluginDlteamsAccountKey_Item::getTable() . '.*',
            ],
            "FROM" => PluginDlteamsAccountKey_Item::getTable(),
            "WHERE" => [
                "itemtype" => User::class,
                "items_id" => $user_id
            ]
        ];

        $iterator = $DB->request($query);

        $result = [];
//        pour chaque clé, récuperer les catalogues liés
        foreach ($iterator as $data) {
            $accountkeys_temp = [];
            $datacatalog_request = [
                "SELECT" => [
                    PluginDlteamsDataCatalog_Item::getTable() . '.*'
                ],
                "FROM" => PluginDlteamsDataCatalog_Item::getTable(),
                "WHERE" => [
                    "itemtype" => PluginDlteamsAccountKey::class,
                    "items_id" => $data["accountkeys_id"]
                ]
            ];

            $iterator1 = $DB->request($datacatalog_request);

            foreach ($iterator1 as $catalog_item) {
                $datacatalog_id = $catalog_item["datacatalogs_id"];
                $datacatalog = new PluginDlteamsDataCatalog();
                $datacatalog->getFromDB($datacatalog_id);

                $temp = $datacatalog->fields;


//                contact catalogue
                $user_contact = new User();
                $user_contact->getFromDB($datacatalog->fields["users_id_contact"]);
                $temp["user_contact"] = $user_contact->fields;
                $temp["user_contact"]["email"] = $user_contact->getDefaultEmail();

                $user_contact->getFromDB($datacatalog->fields["suppliers_id_contact"]);
                $temp["supplier_contact"] = $user_contact->fields;
                $temp["supplier_contact"]["email"] = $user_contact->getDefaultEmail();


                $user_contact->getFromDB($datacatalog->fields["contacts_id_contact"]);
                $temp["contact_contact"] = $user_contact->fields;
                $temp["contact_contact"]["email"] = $user_contact->getDefaultEmail();


                /*                highlight_string("<?php\n\$data =\n" . var_export($temp["user_contact"], true) . ";\n?>");*/
//                die();


//                stockage
                $storage_query = [
                    'FROM' => PluginDlteamsDataCatalogStorage_Item::getTable(),
                    'SELECT' => [
                        PluginDlteamsDataCatalogStorage_Item::getTable() . '.id',
                        PluginDlteamsDataCatalogStorage_Item::getTable() . '.id as linkid',
                        PluginDlteamsDataCatalogStorage_Item::getTable() . '.comment',
                        PluginDlteamsDataCatalogStorage_Item::getTable() . '.itemtype as itemtype',
                        PluginDlteamsDataCatalogStorage_Item::getTable() . '.items_id as items_id',
                    ],
//            'WHERE' => [
//                static::getTable() . '.' . static::$items_id_1 => $object_item->fields['id']
//            ],
                    'LEFT JOIN' => [
                        PluginDlteamsDataCatalog::getTable() => [
                            'FKEY' => [
                                PluginDlteamsDataCatalogStorage_Item::getTable() => 'datacatalogs_id',
                                PluginDlteamsDataCatalog::getTable() => 'id'
                            ]
                        ]
                    ],
                    'ORDER' => [PluginDlteamsDataCatalog::getTable() . '.name ASC'],
                ];

                $itemtype_list = [
                    'Datacenter',
                    'Computer',
                    'NetworkEquipment',
                    'Peripheral',
                    'Printer',
                    'Phone',
                    'PluginDlteamsPhysicalStorage'
                ];
                foreach ($itemtype_list as $itemtype) {
                    $storage_query['WHERE']['OR'][] = [
                        'itemtype' => $itemtype,
                        PluginDlteamsDataCatalogStorage_Item::getTable() . '.datacatalogs_id' => $datacatalog->fields['id']
                    ];
                }

                $iterator = $DB->request($storage_query);
                $storages = [];
                foreach ($iterator as $storage) {
                    $storage_itemtype_str = $storage["itemtype"];
                    $storage_itemtype = new $storage_itemtype_str();
                    $storage_itemtype->getFromDB($storage["items_id"]);
                    $storage["name"] = $storage_itemtype->fields["name"];

                    $storages[] = $storage;
                }


                $storage_names = array_column($storages, 'name');
                $temp["storages"] = implode(', ', $storage_names);

//                traitements et usage
                $record_query = [
                    "SELECT" => [
                        PluginDlteamsRecord_Item::getTable() . ".*",
                        PluginDlteamsRecord::getTable() . ".name as name"
                    ],
                    "FROM" => PluginDlteamsRecord_Item::getTable(),
                    "LEFT JOIN" => [
                        PluginDlteamsRecord::getTable() => [
                            'FKEY' => [
                                PluginDlteamsRecord::getTable() => 'id',
                                PluginDlteamsRecord_Item::getTable() => 'records_id'
                            ]
                        ]
                    ],
                    "WHERE" => [
                        "itemtype" => PluginDlteamsDataCatalog::class,
                        "items_id" => $datacatalog_id
                    ]
                ];
                $record_iterator = $DB->request($record_query);


                $records_items = [];
                foreach ($record_iterator as $records_item) {
                    $records_items[] = $records_item;
                }
                $names = array_column($records_items, 'name');
                $temp["records"] = implode(', ', $names);


//                type de données contenus par le catalogue
                $datacarrier_query = [
                    "SELECT" => [
                        PluginDlteamsDataCarrierType_Item::getTable() . ".*",
                        PluginDlteamsDataCarrierType::getTable() . ".name as name"
                    ],
                    "FROM" => PluginDlteamsDataCarrierType_Item::getTable(),
                    "LEFT JOIN" => [
                        PluginDlteamsDataCarrierType::getTable() => [
                            'FKEY' => [
                                PluginDlteamsDataCarrierType::getTable() => 'id',
                                PluginDlteamsDataCarrierType_Item::getTable() => 'datacarriertypes_id'
                            ]
                        ]
                    ],
                    "WHERE" => [
                        PluginDlteamsDataCarrierType_Item::getTable() . ".itemtype" => PluginDlteamsDataCatalog::class,
                        PluginDlteamsDataCarrierType_Item::getTable() . ".items_id" => $datacatalog_id
                    ]
                ];
                $datacarrietype_iterator = $DB->request($datacarrier_query);

                $datacarriertype_items = [];
                foreach ($datacarrietype_iterator as $datacarriertype_item) {
                    $datacarriertype_items[] = $datacarriertype_item;
                }

                $names = array_column($datacarriertype_items, 'name');
                $temp["datacarriertypes"] = implode(', ', $names);


//                appliances
                $appliance_query = [
                    "SELECT" => [
                        Appliance_Item::getTable() . ".*",
                        Appliance::getTable() . ".name as name"
                    ],
                    "FROM" => Appliance_Item::getTable(),
                    "LEFT JOIN" => [
                        Appliance::getTable() => [
                            'FKEY' => [
                                Appliance::getTable() => 'id',
                                Appliance_Item::getTable() => 'appliances_id'
                            ]
                        ]
                    ],
                    "WHERE" => [
                        "itemtype" => PluginDlteamsDataCatalog::class,
                        "items_id" => $datacatalog_id
                    ]
                ];
                $appliance_iterator = $DB->request($appliance_query);

                $appliance_items = [];
                foreach ($appliance_iterator as $appliance_item) {
                    $appliance_items[] = $appliance_item;
                }

                $names = array_column($appliance_items, 'name');
                $temp["appliances"] = implode(', ', $names);
//                end appliances

//                accountkeys
                $accountkeys_query = [
                    "SELECT" => [
                        PluginDlteamsAccountKey_Item::getTable() . ".*",
                        PluginDlteamsAccountKey::getTable() . ".name as name"
                    ],
                    "FROM" => PluginDlteamsAccountKey_Item::getTable(),
                    "LEFT JOIN" => [
                        PluginDlteamsAccountKey::getTable() => [
                            'FKEY' => [
                                PluginDlteamsAccountKey::getTable() => 'id',
                                PluginDlteamsAccountKey_Item::getTable() => 'accountkeys_id'
                            ]
                        ]
                    ],
                    "WHERE" => [
                        "itemtype" => PluginDlteamsDataCatalog::class,
                        "items_id" => $datacatalog_id
                    ]
                ];
                $accountkeys_iterator = $DB->request($accountkeys_query);

                $accountkeys_items = [];
                foreach ($accountkeys_iterator as $accountkeys_item) {
                    $accountkeys_items[] = $accountkeys_item;
                    $accountkeys_temp[] = $accountkeys_item["accountkeys_id"];
                }

                $names = array_column($accountkeys_items, 'name');
                $temp["accountkeys"] = implode(', ', $names);
//                end accountkey


//                catalog users
                $a_keys = PluginDlteamsAcces_Catalog::getCataloguesAccessibleRequest($datacatalog);
                $admin_keys = [];

                $users = [];
                $admin_users = [];
                foreach ($a_keys as $user_key) {
                    $item = new $user_key['itemtype']();
                    $item->getFromDB($user_key['items_id']);

                    $users[] = $item->fields["name"];
                }

//                $names = array_column($users, 'name');
                $temp["catalog_users_formatted"] = implode(', ', $users);

                foreach ($a_keys as $keyfields) {
                    if ($keyfields["profiles_json"]) {
                        $profiles = json_decode($keyfields["profiles_json"]);
                        if (array_search("2", $profiles)) {
                            $admin_keys[] = $keyfields;
                        }
                    }
                }
                if (count($admin_keys) > 0) {
                    foreach ($admin_keys as $user_key) {
                        $item = new $user_key['itemtype']();
                        $item->getFromDB($user_key['items_id']);

                        $admin_users[] = $item->fields["name"];
                    }
                }

                $temp["catalog_admin_formatted"] = implode(', ', $admin_users);


//

                $result[] = $temp;
            }
        }


        return $result;

    }

    function rawSearchOptions()
    {
        $tab[] = [
            'id' => '45',
            'table' => static::getTable(),
            'field' => 'comment',
            'datatype' => 'text',
            'name' => __("Commentaire"),
            'forcegroupby' => true,
            'massiveaction' => true,
        ];

        return $tab;
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case static::$itemtype_2:
                $ong = [];
                if (!$withtemplate) {
                    if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            $iterator = self::showItemsGetRequest($item);
                            $ong[] = static::createTabEntry(static::getTypeNameForClass(), count($iterator));
                        } else
                            $ong[] = static::getTypeNameForClass();
                    }
                }


                return $ong;
                break;
            default:
                if (!$withtemplate) {
                    $ong = [];
                    if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            $iterator = static::showForItemgetRequest($item, false);
                            $ong_name = "Clés d'accès";
                            if($item->getType() == PluginDlteamsDataCatalog::getType() && $item->fields["plugin_dlteams_datacarriercategories_id"] != 2)
                                $ong_name = "Comptes d'accès";
                            $ong[1] = static::createTabEntry($ong_name, count($iterator));
                        } else
                            $ong[1] = static::getTypeName(2);
                    }

//                     on a ajoute l'onglet "Comptes de l'annuaire" si l'objet est un catalogue
//                    if ($item::getType() == PluginDlteamsDataCatalog::class && $item->fields["is_directoryservice"]) {
//                        $iterator = static::showForItemgetRequest($item, true);
//                        $ong[2] = static::createTabEntry(__("Comptes de l'annuaire", "dlteams"), count($iterator));
//                    }

                    return $ong;
                }
                break;
        }

        return '';
    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        $forbidden[] = 'Document_Item:add';
        $forbidden[] = 'Document_Item:remove';
        return $forbidden;
    }

    static function showItems(CommonDBTM $item, $withtemplate = 0)
    {
        global $DB;
        $id = $item->fields['id'];
        if (!$item->can($id, READ)) {
            return false;
        }

        $canedit = $item->can($id, UPDATE);
        $rand = mt_rand(1, mt_getrandmax());

        $iterator = self::showItemsGetRequest($item);


        $number = count($iterator);

        $items_list = [];
        $used = [];
        foreach ($iterator as $id => $data) {
            // while ($data = $iterator->next()) {
            $items_list[$data['linkid']] = $data;

            $used[$data['linkid']] = $data['linkid'];

        }
        /*        highlight_string("<?php\n\$data =\n" . var_export($items_list, true) . ";\n?>");*/
//        die();
        //print_r($items_list);die;
        if ($canedit) {
            echo "<form name='recorditem_form$rand' id='recorditem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . PluginDlteamsAccountKey::class . "'>";
            echo "<input type='hidden' name='items_id1' value='" . $item->fields["id"] . "'>";

            echo "<table class='tab_cadre_fixe'>";
            $title = "Attributions à ce compte";
            $entitled = "Type";
            echo "<tr class='tab_bg_2'><th colspan='3'>" . __($title, 'dlteams') .
                "</th>";
            echo "</tr>";


            echo "<tr class='tab_bg_1'><td class='right' style='text-wrap: nowrap;' width='40%'>" . __($entitled, 'dlteams');
            echo "</td><td width='40%' class='left'>";
            $types = [
                User::class,
                Group::class,
                Contact::class,
                Supplier::class
            ];


            echo "<div style='display: flex; gap: 4px;'>";
            Dropdown::showSelectItemFromItemtypes(['itemtypes' => $types,
                'entity_restrict' => ($item->fields['is_recursive'] ? getSonsOf('glpi_entities', $item->fields['entities_id'])
                    : $item->fields['entities_id']),
                'checkright' => true,
                'used' => $used,
                'ajax_page' => "/marketplace/dlteams/ajax/dlteamsDropdownAllItem.php"
            ]);
            echo "</div>";

            echo "</td><td width='20%' class='left'>";
            echo "</td></tr>";

//            echo "<tr class='tab_bg_1' style='display: none' id='field_permissions'><td class='right' width='40%'>" . __("Permissions");
//            echo "</td><td width='40%' class='left comment-td'>";
//            echo "<div style='display: flex; gap: 4px;'>";
//            PluginDlteamsUserProfile::dropdown([
//                "name" => "profiles_idx[]",
//                "width" => "150px",
//                'value' => [],
//                'multiple' => true
//            ]);
//            echo "</div>";
//            echo "</td><td width='20%' class='left'>";
//            echo "</td></tr>";


            echo "<tr class='tab_bg_1' style='display: none' id='field_comment'><td class='right' width='40%'>" . __("Comment");
            echo "</td><td width='40%' class='left comment-td'>";
            echo "<div style='display: flex; gap: 4px;'>";
            echo "<textarea type='text' style='width:100%' maxlength=1000 rows='3' name='comment' class='comment'></textarea>";
            echo "</div>";
            echo "</td><td width='20%' class='left'>";
            echo "</td></tr>";


            echo "<style>
                .comment-td {width: 40%;}
                @media (max-width: 767px) {.comment-td {width: 100%;}}
              </style>";

            echo "<tr class='tab_bg_1' style='display: none' id='field_submit'><td class='right' width='40%'>";
            echo "</td><td width='40%' class='left'>";

            echo "<div style='display: flex; gap: 4px;'>";
            echo "<input for='recorditem_form$rand' type='submit' name='link_element' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</div>";
            echo "</td><td width='20%' class='left'>";
            echo "</td></tr>";

            echo "</table>";
            Html::closeForm();

            echo "<script>
                $(document).ready(function(e){
                    $(document).on('change', 'select[name=items_id]', function () {
                        if($(this).val() != '0'){
                            $('#field_submit').css('display', 'revert');
//                            $('#field_permissions').css('display', 'revert');
                            $('#field_comment').css('display', 'revert');
                            
                            $.ajax({
                                url: '/marketplace/dlteams/ajax/get_object_specific_field.php',
                                type: 'POST',
                                data: {
                                    id: $(this).val(),
                                    object: $('select[name=itemtype]').val(),
                                    field: 'content'
                                },
                                success: function (data) {
                                    // Handle the returned data here
                                    let comm_field = $('textarea[name=comment]');
                                    comm_field.val(data);
                                    comm_field.val(comm_field.val().replace(/^\s+/, ''));
                                }
                            });  
                        } else {
                            $('textarea[name=comment]').val('');
                            $('#field_submit').css('display', 'none');
                            $('#field_comment').css('display', 'none');   
//                            $('#field_permissions').css('display', 'none');   
                        }
                    });
                });
        </script>";
        }

        // Display recipients
        echo "<div class='spaced'>";
        if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massive_action_params = ['container' => 'mass' . __CLASS__ . $rand,
                'num_displayed' => min($_SESSION['glpilist_limit'], $number)];
            Html::showMassiveActions($massive_action_params);
        }
        echo "<table class='tab_cadre_fixehov'>";

        $header_begin = "<tr>";
        $header_top = '';
        $header_bottom = '';
        $header_end = '';

        if ($canedit && $number) {
            $header_begin .= "<th width='10'>";
            $header_top .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            $header_bottom .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            $header_end .= "</th>";
        }
        $header_end .= "<th>" . __("Intitulé") . "</th>";
        $header_end .= "<th>" . __("Type", 'dlteams') . "</th>";
        $header_end .= "<th>" . __("Nom") . "</th>";
        $header_end .= "<th>" . __("Prénom") . "</th>";
        $header_end .= "<th>" . __("Comment") . "</th>";
        $header_end .= "</tr>";

        echo $header_begin . $header_top . $header_end;
        //var_dump($items_list);

        foreach ($items_list as $data) {
            echo "<tr class='tab_bg_1'>";

            if ($canedit && $number) {
                echo "<td width='10'>";
                Html::showMassiveActionCheckBox(__CLASS__, $data['linkid']);
                echo "</td>";
            }
            $itemtype_str = $data['itemtype'];
            $itemtype_object = new $itemtype_str();
            $itemtype_object->getFromDB($data['items_id']);
            $name = "<a target='_blank' href=\"" . $data['itemtype']::getFormURLWithID($data['items_id']) . "\">" . $itemtype_object->fields['name'] . "</a>";
            echo "<td width='20%' class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $name . "</td>";
            echo "<td class='left' width='20%'>" . $data['itemtype']::getTypeName() . "</td>";

            $nom = "";
            $prenom = "";
            if ($itemtype_str == User::class) {
                $nom = $itemtype_object->fields["realname"];
                $prenom = $itemtype_object->fields["firstname"];
            }

            if ($itemtype_str == Contact::class) {
                $nom = $itemtype_object->fields["name"];
                $prenom = $itemtype_object->fields["firstname"];
            }
            echo "<td class='left' width='20%'>" . ($nom ?? "") . "</td>";
            echo "<td class='left' width='20%'>" . ($prenom ?? "") . "</td>";
            echo "<td class='left' width='20%'>" . ($data['comment'] ?? "") . "</td>";
            echo "</tr>";
        }

        if (count($iterator) > 10) {
            echo $header_begin . $header_bottom . $header_end;
        }
        echo "</table>";

        if ($canedit && $number > 10) {
            $massive_action_params['ontop'] = false;
            Html::showMassiveActions($massive_action_params);
        }
        Html::closeForm();


        echo "</div>";

        return true;
    }


    public static function showHelpTextReplace()
    {
        echo "<blockquote style='text-align: justify;background-color: #ebebeb;padding: 1rem;width: 100%;margin-top: 1rem;'>";

        echo "Les balises suivantes seront remplacées par leurs valeurs lors de la création :  <br/>
            <span style='color: #0a6aa1'>##firstname##</span> prénom | <span style='color: #0a6aa1'>##firstname.premiercaractere##</span> : 1ère lettre du prénom | <span style='color: #0a6aa1'>##name## :</span> nom | <span style='color: #0a6aa1'>##name.premiercaractere##</span> : 1ère lettre du nom | <span style='color: #0a6aa1'>##mail##</span> : mail principal de l'utilisateur 
            <br/> <b>exemple :</b> <span style='color: #b10610'>##firstname.premiercaractere##.##name##@organisme.fr</span> générera <span style='color: #b10610'>p.dupont@organisme.fr</span> pour l'utilisateur <span style='color: #b10610'>Patrick Dupont</span>";
        echo "</blockquote>";
    }

    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {

        global $DB;
        $query = [
            "FROM" => PluginDlteamsDataCatalog::getTable(),
            "OR" => [
                [
                    "is_directoryservice" => 0,
                    "entities_id" => $_SESSION['glpiactiveentities'],
                ],
                [
                    "is_directoryservice" => 1,
                    "entities_id" => $_SESSION['glpiactiveentities'],
                    "plugin_dlteams_datacatalogs_id" => ['>', 0]
                ]
            ]
        ];

        $iterator = $DB->request($query);
        foreach ($iterator as $cat) {
            $used[$cat["id"]] = $cat["id"];
        }
        switch ($ma->getAction()) {
            case 'assign_key_to_user':
                echo "<tr class='tab_bg_1'><td width='24%' style='display: flex; align-items: center; gap: 5px'>";
                PluginDlteamsAccountKey::dropdown([
                    "name" => "accountkeys_id",
                    "addicon" => PluginDlteamsAccountKey::canCreate(),
                    "width" => "150px",
                    "used" => [],
                ]);

                echo "</td>";

                echo '<br /><br />' . Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                return true;
                break;
            case 'create_account_and_assign_to_user':
                echo "<table style='display: inline;' class='tab_cadre_fixe'>";

                echo "<tr class='tab_bg_1'>";
                echo "<td class='right'>Dans quel annuaire ?</td>";

                echo "<td>";
                $field_rand = PluginDlteamsDataCatalog::dropdown([
                    "name" => "datacatalogs_id",
                    "addicon" => PluginDlteamsDataCatalog::canCreate(),
                    "width" => "250px",
                    "used" => $used,
                ]);
                echo "</td>";
                echo "</tr>";

                $next_step_rand = mt_rand();
                echo "<tr id='update_next_step$next_step_rand' class='tab_bg_1'>";
                echo "</tr>";
                    $params = $ma->POST;
                    $params['id_field'] = '__VALUE__';
                    global $CFG_GLPI;
                    Ajax::updateItemOnSelectEvent(
                        "dropdown_datacatalogs_id$field_rand",
                        "update_next_step$next_step_rand",
                        $CFG_GLPI['root_doc'] . '/marketplace/dlteams/ajax/get_catalogue_format_field.php',
                        $params
                    );

                /*
                echo "<tr class='tab_bg_1'>";
                echo "<td class='right'>Type de compte</td>";
                echo "<td>";
                PluginDlteamsKeyType::dropdown([
                    "name" => "keytypes_id",
                    "addicon" => PluginDlteamsKeyType::canCreate(),
                    "width" => "250px",
                    "used" => [],
                ]);
                echo "</td>";
                echo "</tr>";
                */

                echo "</table>";

                echo '<br /><br />' . Html::submit(_x('button', "Créer puis attribuer à l'utilisateur"), ['name' => 'massiveaction', 'class' => 'btn btn-sm btn-primary',]);

                echo "<br/>";
                static::showHelpTextReplace();
                return true;
                break;
            case 'create_account_and_assign_to_group':
            case 'create_account_and_assign_to_each_user_of_group':
                echo "<table style='display: inline;' class='tab_cadre_fixe'>";

                echo "<tr class='tab_bg_1'>";
                echo "<td class='right'>Comptes ou clé</td>";
                echo "<td>";
                echo "<input type='text' style='width:100%' maxlength=250 name='name' required value='" . "" . "'>";
                echo "</td>";
                echo "</tr>";


                echo "<tr class='tab_bg_1'>";
                echo "<td class='right'>Comptes ou lieu de stockage</td>";

                echo "<td>";
                PluginDlteamsDataCatalog::dropdown([
                    "name" => "datacatalogs_id",
                    "addicon" => PluginDlteamsDataCatalog::canCreate(),
                    "width" => "250px",
                    "used" => $used,
                ]);
                echo "</td>";
                echo "</tr>";


                /*
                echo "<tr class='tab_bg_1'>";
                echo "<td class='right'>Type de clé</td>";
                echo "<td>";
                PluginDlteamsKeyType::dropdown([
                    "name" => "keytypes_id",
                    "addicon" => PluginDlteamsKeyType::canCreate(),
                    "width" => "250px",
                    "used" => [],
                ]);
                echo "</td>";
                echo "</tr>";
                */

                echo "</table>";


                echo '<br /><br />' . Html::submit(_x('button', "Créer puis attribuer au groupe"), ['name' => 'massiveaction', 'class' => 'btn btn-sm btn-primary',]);

                echo "<br/>";
                static::showHelpTextReplace();
                return true;
                break;
        }
        return parent::showMassiveActionsSubForm($ma); // TODO: Change the autogenerated stub
    }

    static function showForItem(CommonDBTM $item, $is_directory = false)
    {

        $id = $item->fields['id'];
        $canedit = $item->can($id, UPDATE); // canedit booleen = true
        $rand = mt_rand(1, mt_getrandmax());
        global $DB;

        $iterator = static::showForItemgetRequest($item, $is_directory);
        $number = count($iterator);


        $used = [];
        foreach ($iterator as $it) {
            $used[$it["linkid"]] = $it["linkid"];
        }

        /***form new**/
        if ($canedit) {
            static::showForItemForm($item, null, $is_directory);
        }

        if ($is_directory)
            $ma_processor = PluginDlteamsDataCatalog_Item::class;
        else
            $ma_processor = __CLASS__;

        echo "<div class='spaced'>";
        if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . $ma_processor . $rand);
            $massive_action_params = [
                'container' => 'mass' . $ma_processor . $rand,
                'num_displayed' => min($_SESSION['glpilist_limit'], $number)
            ];
            Html::showMassiveActions($massive_action_params);
        }
        echo "<table class='tab_cadre_fixehov'>";

        $header_begin = "<tr>";
        $header_top = '';
        $header_bottom = '';
        $header_end = '';

        if ($canedit && $number) {
            $header_begin .= "<th width='10'>";
            $header_top .= Html::getCheckAllAsCheckbox('mass' . $ma_processor . $rand);
            $header_end .= "</th>";
        }

        $header_end .= "<th>";
        echo "</th>";

        $header_end .= "<th>" . __("Intitulé", "dlteams") . "</th>";
        $header_end .= "<th>" . __("Annuaire", "dlteams") . "</th>"; // ajout annuaire
        $header_end .= "<th>" . __("Type", "dlteams") . "</th>";
        if (!$is_directory && $item->getType() == PluginDlteamsDataCatalog::class)
            $header_end .= "<th>" . __("Permissions", "dlteams") . "</th>";
        $header_end .= "<th>" . __("Comment", "dlteams") . "</th>";

        $header_end .= "<th>" . __("Groupe / Utilisateur / Contact / Tiers", "dlteams") . "</th>";
//        $header_end .= "<th></th>";
        $header_end .= "</tr>";
        echo $header_begin . $header_top . $header_end;

        foreach ($iterator as $data) {
            echo "<tr class='tab_bg_1'>";
            if ($canedit && $number) {
                echo "<td width='10'>";
                Html::showMassiveActionCheckBox($ma_processor, $data['linkid']);
                echo "</td>";
            }

            echo "<td width='10'>";
            $linkid = $data['linkid'];
            echo "<span style='border: none; background-color: transparent; cursor: pointer; margin-left: 10px' class='btn-updatepermissions' data-row-id='$linkid'>";
            echo "<i class='fas fa-edit me-2'></i>";
            echo "</span>";
            echo "</td>";

            $name = $data["name"];
            if ($is_directory)
                $accountkeys_id = $data['items_id'];
            else
                $accountkeys_id = $data['accountkeys_id'];
            $name = "<a target='_blank' href=\"" . PluginDlteamsAccountKey::getFormURLWithID($accountkeys_id) . "\">" . $name . "</a>";
            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $name . "</td>";

            // Annuaire
            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");

            $accountkey = new PluginDlteamsAccountKey();
            $accountkey->getFromDB($accountkeys_id);
            $datacatalog = new PluginDlteamsDataCatalog();
            $datacatalog->getFromDB($accountkey->fields["plugin_dlteams_datacatalogs_id"]);
            $name = $datacatalog->fields["name"];
            echo ">" . $name . "</td>";

//            if ($item::getType() != PluginDlteamsDataCatalog::class) {
            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $data["keytypename"] ?? "--" . "</td>";
//            }


            if (!$is_directory && $item->getType() == PluginDlteamsDataCatalog::class) {
                $profiles_json = $data["profiles_json"] ? json_decode($data["profiles_json"]) : [];
                $profile_names = "";
                foreach ($profiles_json as $key => $profile_id) {
                    $profile = new PluginDlteamsUserProfile();
                    $profile->getFromDB($profile_id);
                    $profile_names .= "<a target='_blank' href=\"" . PluginDlteamsUserProfile::getFormURLWithID($profile_id) . "\">" . $profile->fields["name"] . "</a>";
                    if (count($profiles_json) != $key + 1)
                        $profile_names .= ",&nbsp;";
                }
                echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                echo ">";
                echo $profile_names;
                echo "</td>";
            }

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">";
            echo $data["comment"];
            echo "</td>";

            echo "<td class='left relations_list" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">";
            $accountkey = new PluginDlteamsAccountKey();
            $accountkey->getFromDB($accountkeys_id);

            $users_data = PluginDlteamsAccountKey_Attribution::getCountAndUsersName($accountkey);
            echo "<div style='display: flex; flex-direction: row'>";
            echo "<span class='pointer add_users' data-accountkey-id='$accountkeys_id' data-datacatalog-id='$id'>
            <i class='fa fa-plus' title='" . __('Attribuer à un utilisateur') . "'></i>
            <span class='sr-only'>" . __('Attribuer un compte à un utilsateur') . "</span>
         </span>";
            echo "<ul>";
            foreach ($users_data["names"] as $linkid => $li_row) {
                echo "<li>";
                echo $li_row . "&nbsp; <i class='fa fa-close delete-user-from-account' data-linkid='$linkid' style='color: red; cursor: pointer'></i>";
                echo "</li>";
            }
            echo "</ul>";
            echo "</div>";
            echo "</td>";

            echo "</tr>";
        }

//        begin script
        $script = "<script>
    $(document).ready(function () {
        
        
        $('.delete-user-from-account').click(function(e) {
          e.preventDefault();
           $.ajax({
                        url: '/marketplace/dlteams/ajax/delete_user_from_account.php',
                        type: 'POST',
                        data: {
                            id: $(this).attr('data-linkid'),
                        },
                        success: function (data) {
                            location.reload();
                        },
                        
            });
        });
        
        $('.add_users').click(function () {
             glpi_ajax_dialog({
                dialogclass: 'modal-md',
                bs_focus: false,
                params: {
                  accountkeys_id: $(this).attr('data-accountkey-id'),
                  datacatalogs_id: $(this).attr('data-datacatalog-id')
                },
                url: '/marketplace/dlteams/ajax/account_to_user.php',
                title: i18n.textdomain('dlteams').__('Action', 'dlteams'),
                close: function () {

                },
                fail: function () {
                    displayAjaxMessageAfterRedirect();
                }
                
             });
        });
        
        
        $('.btn-updatepermissions').on('click', function () {
            var link_id = $(this).attr('data-row-id');
            
            
            // ajout d'utilisateur
           


            glpi_ajax_dialog({
                dialogclass: 'modal-xl',
                bs_focus: false,
                url: '/marketplace/dlteams/ajax/object_item_single_update_action.php',
                params: {
                    object: '" . PluginDlteamsAccountKey_Item::class . "',";


//        if ($tabnum != 'directory')
//            $script .= "field: 'profiles_json',";
//
//        if ($tabnum == 'directory')
//            $script .= "is_directory: true,";

        $script .= "linkid: link_id,
                    itemtype: '" . $item->getType() . "',
                    items_id: '" . $item->fields["id"] . "'
                },
                title: i18n.textdomain('dlteams').__('Action', 'dlteams'),
                close: function () {

                },
                fail: function () {
                    displayAjaxMessageAfterRedirect();
                }
            });});});</script>";

        echo $script;
//        end script

    }

    static function canAccessAnnuaireCatalog(PluginDlteamsAccountKey $item)
    {
        $accountkey_item_query = [
            "FROM" => PluginDlteamsAccountKey_Item::getTable(),
            "WHERE" => [
                "accountkeys_id" => $item->fields["id"],
                "itemtype" => PluginDlteamsDataCatalog::class,
                "items_id" => PluginDlteamsDataCatalog::getAnnuairescatalogues()
            ]
        ];
        global $DB;

        return count($DB->request($accountkey_item_query)) > 0;
    }

    public static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        switch ($ma->getAction()) {
            case 'assign_key_to_user':
                foreach ($ids as $id) {
                    $user_item = new PluginDlteamsUser_Item();
                    $array1 = [
                        'users_id' => $id,
                        'itemtype' => PluginDlteamsAccountKey::class,
                        'items_id' => $ma->POST['accountkeys_id']
                    ];
                    $user_item->add($array1);


                    $accountkey_item = new PluginDlteamsAccountKey_Item();
                    $array2 = [
                        'accountkeys_id' => $ma->POST['accountkeys_id'],
                        'itemtype' => User::class,
                        'items_id' => $id,
                    ];
                    $accountkey = new PluginDlteamsAccountKey();
                    $accountkey->getFromDB($ma->POST['accountkeys_id']);
                    if ($accountkey && $accountkey->fields["name"] && str_contains($accountkey->fields["name"], "Administrateur") && static::canAccessAnnuaireCatalog($ma->POST['accountkeys_id'])) {
                        $array2["users_id_tech"] = $id;
                    } else {
                        $array2["users_id"] = $id;
                    }
                    $accountkey_item->add($array2);

                    Session::addMessageAfterRedirect("Clé assigné avec succès");
                }
                break;


            case 'create_account_and_assign_to_group':
                global $DB;

                // pour chaque groupe sélectionné
                foreach ($ids as $id) {
                    // recuperer le nom
                    $name = $ma->POST["name"];

                    // transformer le nom et interpreter les balises
                    static::processNameAndTransform($name);


                    $accountkey = new PluginDlteamsAccountKey();

                    // si un compte du meme name n'existe pas
                    if (!$accountkey->getFromDBByCrit(["name" => strtolower($name)])) {

                        // ajouter le compte
                        $catalog = new PluginDlteamsDataCatalog();
                        $catalog->getFromDB($ma->POST["datacatalogs_id"]);
                        $keyid = $accountkey->add([
                            "name" => strtolower($name),
                            "plugin_dlteams_keytypes_id" => $catalog->fields["default_keytype"],
                            "entities_id" => $_SESSION['glpiactive_entity'],
                            "plugin_dlteams_datacatalogs_id" => $ma->POST["datacatalogs_id"]
                        ]);

                        // si le compte a été bien ajouté
                        if ($keyid) {
                            // ajouter group_item puis accountkey_item
                            $group_item = new PluginDlteamsGroup_Item();
                            $array1 = [
                                'groups_id' => $id,
                                'itemtype' => PluginDlteamsAccountKey::class,
                                'items_id' => $keyid,
                                "entities_id" => $_SESSION['glpiactive_entity'],
                            ];

                            $group_item->add($array1);

                            $accountkey_item = new PluginDlteamsAccountKey_Item();
                            $array2 = [
                                'accountkeys_id' => $keyid,
                                'itemtype' => Group::class,
                                'items_id' => $id,
                                "entities_id" => $_SESSION['glpiactive_entity'],
                            ];
                            $accountkey = new PluginDlteamsAccountKey();
                            $accountkey->getFromDB($keyid);
                            if ($accountkey && $accountkey->fields["name"] && str_contains($accountkey->fields["name"], "Administrateur") && static::canAccessAnnuaireCatalog($keyid)) {
                                $array2["groups_id_tech"] = $id;
                            } else {
                                $array2["groups_id"] = $id;
                            }
                            $array2["name"] = $accountkey->fields["name"];
                            $accountkey_item->add($array2);

                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                            Session::addMessageAfterRedirect("Clé assigné avec succès");
                            return true;
                        } // si le compte n'a pas pu été ajouté
                        else {
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                            return false;
                        }

                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        return false;
                    }
                }
                break;
            case 'create_account_and_assign_to_user':
                global $DB;
                $errormessage = "";
                try {
                    foreach ($ids as $id) {
                        if(!static::assignAccountToUser($id, $ma->POST, $errormessage)){
                            throw new Exception("Le compte n'a pas été créé");
                        }
                    }
                    if (strlen($errormessage) > 0)
                        Session::addMessageAfterRedirect($errormessage, 0, 2);

                    $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    return true;

                } catch (Exception $e) {
                    $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                    return false;
                }

                break;
            case 'create_account_and_assign_to_each_user_of_group':
                global $DB;
                $errormessage = "";
                foreach ($ids as $id) {
                    // get group users

                    $users = Group_User::getGroupUsers($id);
                    try {
                        foreach ($users as $user) {
                            // foreach user, assign account
                            static::assignAccountToUser($user["id"], $ma->POST, $errormessage);
                        }

                        if (strlen($errormessage) > 0)
                            Session::addMessageAfterRedirect($errormessage, 0, 2);

                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                        return true;
                    } catch (Exception $e) {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        return false;
                    }
                }

                break;
        }

        return true;
    }

    public static function showForItemForm(CommonDBTM $item = null, $linkid = null, $is_directory = false)
    {
        $row = null;

        if ($linkid) {
            $row = new PluginDlteamsAccountKey_Item();
            $row->getFromDB($linkid);
        }

//        if($item::getType() == PluginDlteamsDataCatalog::class){
//            $row = new PluginDlteamsDataCatalog_Item();
//            $row->getFromDB($linkid);
//        }


        $rand = mt_rand();
        echo "<div class='firstbloc'>";
        echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsAccountKey_Item::class) . "'>";
//            $iden = $item->fields['id'];

        echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsAccountKey::class . "' />";

        if ($linkid)
            echo "<input type='hidden' name='linkid' value='" . $linkid . "' />";

        if ($item) {
            echo "<input type='hidden' name='items_id1' value='" . $item->getID() . "' />";
            echo "<input type='hidden' name='entities_id' value='" . $item->fields['entities_id'] . "' />";
            echo "<input type='hidden' name='itemtype1' value='" . $item->getType() . "' />";
        }

        if ($is_directory)
            echo "<input type='hidden' name='is_directory' value='" . true . "' />";

        //echo $item->getType();
        echo "<table class='tab_cadre_fixe'>";
        if($item){
            if (isset($item->fields["plugin_dlteams_datacarriercategories_id"]) && $item->fields["plugin_dlteams_datacarriercategories_id"] === 2) {
                PluginDlteamsAccountKey_Item::$title = __("Clés ou badges permettant l'accès aux données de ce catalogue", 'dlteams');
                $choice = __("Choisir une clé, un badge", 'dlteams');
            } elseif ($item->getType() == Contact::class || $item->getType() == Supplier::class) {
                PluginDlteamsAccountKey_Item::$title = __("Comptes permettant l'accès aux données", 'dlteams');
                $choice = __("Choisir un compte", 'dlteams');
            }  /*elseif ($item->getType() == PluginDlteamsDataCatalog::class && $item->fields["is_directoryservice"]) {
            PluginDlteamsAccountKey_Item::$title = __("Indiquez les comptes ou clés gérés par cet annuaire", 'dlteams');
            $choice = __("Choisir un compte", 'dlteams');
        }*/ elseif ($item->getType() == PluginDlteamsDataCatalog::class) {
                PluginDlteamsAccountKey_Item::$title = __("Indiquez les comptes ou clés utilisées permettant l'accès à ce catalogue", 'dlteams');
                $choice = __("Choisir un compte", 'dlteams');
            } else {
                PluginDlteamsAccountKey_Item::$title = __("Comptes permettant l'accès aux données", 'dlteams');
                $choice = __("Choisir un compte", 'dlteams');
            }
        }
        else{
            PluginDlteamsAccountKey_Item::$title = __("Comptes permettant l'accès a ce catalogue", 'dlteams');
            $choice = __("Choisir un compte", 'dlteams');
        }



        echo "<tr class=''><th colspan='3'>" . PluginDlteamsAccountKey_Item::$title . "<br><i style='font-weight: normal'>" . "</i></th>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'><td width='24%' style='display: flex; align-items: center; gap: 5px'>";
        echo "<span style='white-space: nowrap'>" . $choice . "</span>";


        $value = "0";

        $datacatalog_item = new PluginDlteamsDataCatalog_Item();
        $datacatalog_item->getFromDB($linkid);


        if ($item && $item::getType() == PluginDlteamsDataCatalog::class && isset($datacatalog_item->fields["items_id"]) && !isset($row->fields["accountkeys_id"])) {
            $value = $datacatalog_item->fields["items_id"];
        } elseif (isset($row->fields["accountkeys_id"]) && $row->fields["accountkeys_id"])
            $value = $row->fields["accountkeys_id"];

//        $used = [];
//        global $DB;
//        $accountkeys_query = [
//            "SELECT" => [
//                "*",
//            ],
//            "FROM" => PluginDlteamsAccountKey::getTable(),
//            "WHERE" => ["entities_id" => $_SESSION['glpiactive_entity']]
//        ];


        global $CFG_GLPI;

//        les comptes de son parent
        $parent = new PluginDlteamsDataCatalog();
        $parentaccounts_idx = [];
        $parentannuaires_accounts_idx = [];
//        var_dump($item->fields["plugin_dlteams_datacatalogs_id"]);
//        die();
        if($parent->getFromDB($item->fields["plugin_dlteams_datacatalogs_id"])){
//
            foreach (PluginDlteamsAccountKey_Directory::showForItemgetRequest($parent, false) as $parentaccount)
                $parentaccounts_idx[] = $parentaccount["id"];

//            quels sont les annuaires tiers de ce catalogue parent? pour chaque annuaire, récuperer les clés
            if($parent->fields["use_other_directory"]){
                foreach (PluginDlteamsDataCatalog::annuaireTiersRequest($parent) as $annuairetier_of_parent){
                    $annuaire_tier = new PluginDlteamsDataCatalog();
                    $annuaire_tier->getFromDB($annuairetier_of_parent["id"]);

//                    recuperer les clés de cet annuaire tier du parent
                    foreach (PluginDlteamsAccountKey_Directory::showForItemgetRequest($annuaire_tier, false) as $parentannuaire_account)
                        $parentannuaires_accounts_idx[] = $parentannuaire_account["id"];

                }
            }
        }
        if($item->fields["use_other_directory"]){
            foreach (PluginDlteamsDataCatalog::annuaireTiersRequest($item) as $annuairetier_of_item){
                $annuaire_tier = new PluginDlteamsDataCatalog();
                $annuaire_tier->getFromDB($annuairetier_of_item["id"]);

//                    recuperer les clés de cet annuaire tier du parent
                foreach (PluginDlteamsAccountKey_Directory::showForItemgetRequest($annuaire_tier, false) as $annuaire_account)
                    $parentannuaires_accounts_idx[] = $annuaire_account["id"];

            }
        }
        $condition = [];

        $authorized_key_idx = [...$parentaccounts_idx,...$parentannuaires_accounts_idx];
        foreach (PluginDlteamsAccountKey_Directory::showForItemgetRequest($item, true) as $annuaire_account)
            $authorized_key_idx[] = $annuaire_account["id"];

        if(count($authorized_key_idx) > 0)
        $condition[PluginDlteamsAccountKey::getTable().'.id'] = $authorized_key_idx;

//        if (empty($condition))
//            $condition["glpi_plugin_dlteams_accountkeys.id"] = null;

        if (empty($condition))
            $condition["glpi_plugin_dlteams_accountkeys.id"] = null;

/*        highlight_string("<?php\n\$data =\n" . var_export($condition, true) . ";\n?>");*/
//        die();
        PluginDlteamsAccountKey::dropdown([
            "name" => "accountkeys_id",
            "addicon" => PluginDlteamsAccountKey::canCreate(),
            "width" => "150px",
//            "used" => $used,
            "value" => $value,
            "condition" => $condition,
            'url' => $CFG_GLPI['root_doc'] . "/marketplace/dlteams/ajax/getDropdownValue.php"
        ]);

        echo "</td>";

//        $displaycss = " display: none;";
//        if ($linkid)
        $displaycss = " display: block;";

        echo "<td>";
        if (!$is_directory && $item && $item->getType() == PluginDlteamsDataCatalog::class) {
            echo "<span style='float:right;width:100%; $displaycss' id='td1'>";

            echo __("Accès en tant que", "dlteams");
            echo "&nbsp";

            $profile = new Profile();
            $default_profiles = [];
            if($profile->getFromDBByCrit(["name" => "Utilisateur"]))
                $default_profiles[] = $profile->fields["id"];


//            var_dump($profile->fields);
//            die();
//            if($profile->getFromDBByCrit(["name" => "Administrateur"]))
            PluginDlteamsUserProfile::dropdown([
                "name" => "profiles_idx[]",
                "width" => "150px",
                'value' => $row ? json_decode($row->fields["profiles_json"] ?? "[]")[0] : 1,
//                'multiple' => true
            ]);


            echo "</span>";
        }
        echo "</td>";


        if($item && $item->getType() == PluginDlteamsDataCatalog::class && !$value){
            echo "<td>";
            echo "<span style='float:right;width:100%; $displaycss' id='td1'>";

            echo __("Affecté à", "dlteams");
            echo "&nbsp";

            $profile = new Profile();
            $default_profiles = [];
//            if($profile->getFromDBByCrit(["name" => "Utilisateur"]))
//                $default_profiles[] = $profile->fields["id"];

//            var_dump($profile->fields);
//            die();
//            if($profile->getFromDBByCrit(["name" => "Administrateur"]))
            User::dropdown([
                "name" => "users_idx[]",
                "width" => "150px",
                'value' => [],
                'right' => 'all',
                'multiple' => true
            ]);

            echo "</span>";
            echo "</td>";
        }



        echo "<td style='$displaycss align-items: center;' id='td3'>";
        echo "<span style='float:right;width:100%;'>";


        echo "<textarea type='text' rows='1' name='comment' placeholder='Commentaire'  style='margin-bottom:-15px;width:90%'>";
        if ($row)
            echo $row->fields["comment"];
        echo "</textarea>";

        echo "</span>";
        echo "</td>";

        echo "<td>";
        echo "<span style='$displaycss float:left;margin-left:10px;' id='td4'>";
        if ($row)
            echo "<input type='submit' name='update' value=\"" . _sx('button', 'Update') . "\" class='submit'>";
        else
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
        echo "</span>";
        echo "</td>";
        echo "</table>";
        Html::closeForm();
        echo "</div>";


        $script = "
            <script>
$(document).ready(function(){
    $('select[name=accountkeys_id]').on('change',function(){

		if($(this).val()!='0'){";
        if ($item && $item::getType() != PluginDlteamsDataCatalog::class)
            $script .= "document.getElementById('td1').style.display = 'block';";
        $script .= "document.getElementById('td3').style.display = 'table-cell';
			document.getElementById('td4').style.display = 'block';
			var content = $(this).val();
				$.ajax({
					url : 'getComment.php',
					type: 'POST',
					async : false,
					data : { content : content},
					success : function(data) {
						//alert(data);
						//userData = json.parse(data);
						//alert(json.parse(data));
					  $('.comment1').val(data);
					}
});
}else{
    document.getElementById('td1').style.display = 'none';
    document.getElementById('td2').style.display = 'none';
    document.getElementById('td3').style.display = 'none';
    document.getElementById('td4').style.display = 'none';
}
});});</script>";

//        echo $script;
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case static::$itemtype_2:
                self::showItems($item);
                break;
            default:
                self::showForItem($item, $tabnum == 2);
                break;
        }
    }


    public function post_purgeItem()
    {
//        purge relations
        $relation_item_str = $this->fields["itemtype"] . "_Item";
        if (!class_exists($relation_item_str))
            $relation_item_str = "PluginDlteams" . $relation_item_str;
        $relation_item = new $relation_item_str();

        $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

        $criteria = [
            "itemtype" => static::$itemtype_2,
            "items_id" => $this->fields[static::$items_id_1],
            $relation_column_id => $this->fields["items_id"],
            "comment" => $this->fields["comment"]
        ];

        $relation_item->deleteByCriteria($criteria);
    }

    public function post_updateItem($history = 1)
    {
        $relation_item_str = $this->fields["itemtype"] . "_Item";
        if (!class_exists($relation_item_str))
            $relation_item_str = "PluginDlteams" . $relation_item_str;
        $relation_item = new $relation_item_str();
        $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

        $criteria = [
            "itemtype" => static::$itemtype_2,
            "items_id" => $this->fields[static::$items_id_1],
            $relation_column_id => $this->fields["items_id"],
        ];
        if (isset($this->oldvalues["comment"]))
            $criteria["comment"] = $this->oldvalues["comment"];


        global $DB;
        if ($DB->fieldExists($relation_item->getTable(), 'plugin_accounts_accounts_id')) {
            $criteria["plugin_accounts_accounts_id"] = $this->fields["plugin_dlteams_userprofiles_id"];
        }

        if ($relation_item->deleteByCriteria($criteria)) {
            if (isset($_POST["plugin_dlteams_userprofiles_id"]))
                $criteria["plugin_dlteams_userprofiles_id"] = $_POST["plugin_dlteams_userprofiles_id"];

            $relation_item->add([
                ...$criteria,
                "comment" => $this->fields["comment"]
            ]);
        }
    }
}
