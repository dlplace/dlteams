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

class PluginDlteamsAcces_PolicieForm extends CommonDBTM
{
    static public $itemtype_2 = 'PluginDlteamsPolicieForm';
    static public $itemtype_1;
    public static $items_id_1;
    public static $title;
    public static $sub_title;
    public static $table_match_str = [];

    public function __construct()
    {
        static::$itemtype_1 = str_replace("_Item", "", __CLASS__); // $itemtype_1 ---> PluginDlteamsDataCatalog
        static::$items_id_1 = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", __CLASS__))) . "s_id";
        parent::__construct();
//        parent::forceTable(PluginDlteamsPolicieForm_Item::getTable());
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


    static function getTypeNameForClass($nb = 0)
    {
        return __("Stockages effectifs", 'dlteams');
    }

    static function getActeursTypeNameForClass($nb = 0)
    {
        return __("Acteurs effectifs", 'dlteams');
    }


    // affichage de l'onglet et de son nom
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case static::$itemtype_2:
                $ong = [];
                if (!$withtemplate) {
                    if (Session::haveRight($item::$rightname, READ)) {
                        // tab name utilisateurs effectifs
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            $ong[] = static::createTabEntry(static::getTypeNameForClass(), count(static::getStockageEffectifRequest($item)));
                        } else
                            $ong[] = static::getTypeNameForClass();

                        $ong[] = static::createTabEntry(static::getActeursTypeNameForClass(), count(static::getActeursEffectifRequest($item)));
                    }
                }

                return $ong;
                break;
        }

        return '';
    }

    // comptage du nombre de liaison entre les 2 objets dans la table de l'objet courant
    static function countForItem(CommonDBTM $item)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable(static::getTable(), ['items_id' => $item->getID(), 'itemtype' => $item->getType()]);
    }


    public static function getItemsRequest(CommonDBTM $object_item)
    {
        global $DB;
        $link_table = str_replace("_Item", "", __CLASS__);
        $temp = new $link_table();

        $items = $DB->request([
            'FROM' => self::getTable(),
            'SELECT' => [
                self::getTable() . '.id',
                self::getTable() . '.id as linkid',
                self::getTable() . '.comment',
                self::getTable() . '.itemtype as itemtype',
                self::getTable() . '.items_id as items_id',
            ],
            'WHERE' => [
                static::getTable() . '.' . static::$items_id_1 => $object_item->fields['id']
            ],
            'LEFT JOIN' => [
                $temp->getTable() => [
                    'FKEY' => [
                        static::getTable() => static::$items_id_1,
                        $temp->getTable() => 'id'
                    ]
                ]
            ],
//            'ORDER' => self::getTable() . '.id DESC',
            'ORDER' => [$temp->getTable() . '.name ASC', self::getTable() . '.itemtype ASC'],
        ]);

        return iterator_to_array($items);
    }


    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addImpactTab($ong, $options);
        return $ong;
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
//            si onglet objet policiform, on affiche tous les stockages qui on acces a ce policiform
            case static::$itemtype_2:
//                utilisateurs effectifs
                if ($tabnum == 0)
                    self::showStockageEffectifs($item);
//                end utilisateurs effectifs

                if ($tabnum == 1)
                    self::showActeursEffectifs($item);

                break;
        }
    }

    /**
     * Show items links to a document
     *
     * @param $doc Document object
     *
     * @return void
     **@since 0.84
     *
     */
    public static function showStockageEffectifs(PluginDlteamsPolicieForm $object_item)
    {

        global $DB;
        $instID = $object_item->fields['id'];
        if (!$object_item->can($instID, READ)) {
            return false;
        }
        $canedit = $object_item->can($instID, UPDATE);

        $types_iterator = [];
        $types = PluginDlteamsItemType::getTypes();
//        Enlève le choix de L'objet LegalBasi dans la dropdown qui affiche la liste des objets
        $key = array_search("PluginDlteamsDataCatalog", $types);
        unset($types[$key]);
        $rand = mt_rand();

        $items = self::getStockageEffectifRequest($object_item);
        if (!count($items)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
            echo "<table class='tab_cadre_fixehov'>";
            $header = "<tr>";
//            if ($canedit) {
//                $header .= "<th width='10'>";
//                $header .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
//                $header .= "</th>";
//            }
            $header .= "<th>" . __("Element") . "</th>";
            $header .= "<th>" . __("Objet") . "</th>";
            $header .= "<th>" . __("Comment") . "</th>";
            $header .= "</tr>";
            echo $header;

            foreach ($items as $row) {
                $item = new $row['itemtype'](); //plante si itemtype is null
                $item->getFromDB($row['items_id']);
                $name = "<a target='_blank' href=\"" . $item::getFormURLWithID($item->getField('id')) . "\">" . $item->getField('name') . "</a>";
                echo "<tr lass='tab_bg_1'>";
//                if ($canedit) {
//                    echo "<td>";
//                    Html::showMassiveActionCheckBox(__CLASS__, $row["id"]);
//                    echo "</td>";
//                }
                echo "<td>" . $name . "</td>";
                echo "<td>" . $row["itemtype"]::getTypeName() . "</td>";
                echo "<td>" . $row['comment'] . "</td>";
                echo "</tr>";
            }
            echo $header;
            echo "</table>";

        }
    }


    public static function showActeursEffectifs(PluginDlteamsPolicieForm $object_item)
    {

        global $DB;
        $instID = $object_item->fields['id'];
        if (!$object_item->can($instID, READ)) {
            return false;
        }
        $canedit = $object_item->can($instID, UPDATE);

        $types_iterator = [];
        $types = PluginDlteamsItemType::getTypes();
//        Enlève le choix de L'objet LegalBasi dans la dropdown qui affiche la liste des objets
        $key = array_search("PluginDlteamsDataCatalog", $types);
        unset($types[$key]);
        $rand = mt_rand();

        $items = self::getActeursEffectifRequest($object_item);
        if (!count($items)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
            echo "<table class='tab_cadre_fixehov'>";
            $header = "<tr>";
//            if ($canedit) {
//                $header .= "<th width='10'>";
//                $header .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
//                $header .= "</th>";
//            }
            $header .= "<th>" . __("Element") . "</th>";
            $header .= "<th>" . __("Objet") . "</th>";
            $header .= "<th>" . __("Comment") . "</th>";
            $header .= "</tr>";
            echo $header;

            foreach ($items as $row) {
                $item = new $row['itemtype'](); //plante si itemtype is null
                $item->getFromDB($row['items_id']);
                $name = "<a target='_blank' href=\"" . $item::getFormURLWithID($item->getField('id')) . "\">" . $item->getField('name') . "</a>";
                echo "<tr lass='tab_bg_1'>";
//                if ($canedit) {
//                    echo "<td>";
//                    Html::showMassiveActionCheckBox(__CLASS__, $row["id"]);
//                    echo "</td>";
//                }
                echo "<td>" . $name . "</td>";
                echo "<td>" . $row["itemtype"]::getTypeName() . "</td>";
                echo "<td>" . $row['comment'] . "</td>";
                echo "</tr>";
            }
            echo $header;
            echo "</table>";

        }
    }


    public function post_purgeItem()
    {
        global $DB;
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


        $DB->delete($relation_item->getTable(), $criteria);

//        $relation_item->deleteByCriteria($criteria);
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
            "comment" => $this->oldvalues["comment"]
        ];


        $relation_item->deleteByCriteria($criteria);
        $relation_item->add([
            ...$criteria,
            "comment" => $this->fields["comment"]
        ]);
    }

    function rawSearchOptions()
    {
        $tab[] = [
            'id' => '44',
            'table' => static::getTable(),
            'field' => 'comment',
            'datatype' => 'text',
            'name' => __("Commentaire"),
            'forcegroupby' => true,
            'massiveaction' => true,
        ];

        return $tab;
    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }

    public static function getStockageEffectifRequest(CommonDBTM $item)
    {
        global $DB;        //var_dump ($table_name, $columnid_name);
        $table_item_name = getTableForItemType(static::$itemtype_2 . "_Item");

        $query = [
            'SELECT' => [
                $table_item_name . '.id AS linkid',
                $table_item_name . '.itemtype AS itemtype',
                $table_item_name . '.items_id AS items_id',
                $table_item_name . '.*',
            ],
            'FROM' => $table_item_name,
            'WHERE' => [
                $table_item_name . '.itemtype' => PluginDlteamsDataCatalog::class,
                $table_item_name . '.' . 'policieforms_id' => $item->fields['id'],
            ],
        ];

        if ($DB->fieldExists($table_item_name, 'comment')) {
            $query['SELECT'][] = $table_item_name . '.comment AS comment';
        }


        $iterator = $DB->request($query);
        $temp = [];

        foreach ($iterator as $id => $data) {

            if ($data["itemtype"]) {
                $item_object = null;
                $item_str = $data["itemtype"];
                $item_object = new $item_str();
                $item_object->getFromDB($data["items_id"]);


                if (isset($item_object->fields["entities_id"])) {
                    array_push($temp, $data);
                }

            }

        }

        $users = [];
//      get users that have acces to this catalog through account or keys
        foreach ($temp as $datacatalog_item) {

            $request2 = [
                'SELECT' => [
                    PluginDlteamsDataCatalog_Item::getTable() . ".id as linkid",
                    PluginDlteamsDataCatalog_Item::getTable() . ".*",
                ],
                'FROM' => PluginDlteamsDataCatalog_Item::getTable(),
//                'OR' => [
//                    [
//                        'itemtype' => Storage::class,
//                        'datacatalogs_id' => $datacatalog_item["items_id"]
//                    ],
//                    [
//                        'itemtype' => Group::class,
//                        'accountkeys_id' => $datacatalog_item["items_id"]
//                    ],
//                    [
//                        'itemtype' => Supplier::class,
//                        'accountkeys_id' => $datacatalog_item["items_id"]
//                    ],
//                    [
//                        'itemtype' => Contact::class,
//                        'accountkeys_id' => $datacatalog_item["items_id"]
//                    ],
//                ],
            ];

            foreach (PluginDlteamsDataCatalogStorage_Item::$itemtypes_list as $itemtype){
                $request2['OR'][] = [
                    'itemtype' => $itemtype,
                    'datacatalogs_id' => $datacatalog_item["items_id"]
                ];
            }


            $iterator = $DB->request($request2);
            $temp = [];

            foreach ($iterator as $id => $data) {

                if ($data["itemtype"]) {
                    array_push($users, $data);
                }

            }
        }

        return $users;
    }



    public static function getActeursEffectifRequest(CommonDBTM $item)
    {
        global $DB;        //var_dump ($table_name, $columnid_name);
        $table_item_name = getTableForItemType(static::$itemtype_2 . "_Item");

        $query = [
            'SELECT' => [
                $table_item_name . '.id AS linkid',
                $table_item_name . '.itemtype AS itemtype',
                $table_item_name . '.items_id AS items_id',
                $table_item_name . '.*',
            ],
            'FROM' => $table_item_name,
            'WHERE' => [
                $table_item_name . '.itemtype' => PluginDlteamsRecord::class,
                $table_item_name . '.' . 'policieforms_id' => $item->fields['id'],
            ],
        ];

        if ($DB->fieldExists($table_item_name, 'comment')) {
            $query['SELECT'][] = $table_item_name . '.comment AS comment';
        }


        $iterator = $DB->request($query);
        $temp = [];

        foreach ($iterator as $id => $data) {

            if ($data["itemtype"]) {
                $item_object = null;
                $item_str = $data["itemtype"];
                $item_object = new $item_str();
                $item_object->getFromDB($data["items_id"]);


                if (isset($item_object->fields["entities_id"])) {
                    array_push($temp, $data);
                }

            }

        }

        $users = [];
//      get users that have acces to this catalog through account or keys
        foreach ($temp as $datacatalog_item) {

            $request2 = [
                'SELECT' => [
                    PluginDlteamsRecord_Item::getTable() . ".id as linkid",
                    PluginDlteamsRecord_Item::getTable() . ".*",
                ],
                'FROM' => PluginDlteamsRecord_Item::getTable(),
                'OR' => [
                    [
                        'itemtype' => User::class,
                        'records_id' => $datacatalog_item["items_id"]
                    ],
                    [
                        'itemtype' => Group::class,
                        'records_id' => $datacatalog_item["items_id"]
                    ],
                    [
                        'itemtype' => Supplier::class,
                        'records_id' => $datacatalog_item["items_id"]
                    ],
                    [
                        'itemtype' => Contact::class,
                        'records_id' => $datacatalog_item["items_id"]
                    ],
                    [
                        'itemtype' => PluginDlteamsThirdPartyCategory::class,
                        'itemtype1' => null,
                        'records_id' => $datacatalog_item["items_id"]
                    ],
                ],
            ];

//            foreach (PluginDlteamsDataCatalogStorage_Item::$itemtypes_list as $itemtype){
//                $request2['OR'][] = [
//                    'itemtype' => $itemtype,
//                    'datacatalogs_id' => $datacatalog_item["items_id"]
//                ];
//            }


            $iterator = $DB->request($request2);
            $temp = [];

            foreach ($iterator as $id => $data) {

                if ($data["itemtype"]) {
                    array_push($users, $data);
                }

            }
        }

        return $users;
    }
}
