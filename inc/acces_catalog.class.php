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

class PluginDlteamsAcces_Catalog extends CommonDBTM
{
    static public $itemtype_2 = 'PluginDlteamsDataCatalog';
    static public $itemtype_1;
    public static $items_id_1;
    public static $title;
    public static $sub_title;
    public static $table_match_str = [];

    public function __construct()
    {
        static::$itemtype_1 = str_replace("_Item", "", __CLASS__); // $itemtype_1 ---> PluginDlteamsDataCatalog
        static::$items_id_1 = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", __CLASS__))) . "s_id";
        static::$title = __("Catalogues en relation avec cet objet", 'dlteams');
        static::$sub_title = __("Choisir un catalogue à lier à cet élément", 'dlteams');
        static::$table_match_str = [
            [
                'head_text' => __("Name"),
                'column_name' => 'name',
                'show_as_link' => true
            ],
            [
                'head_text' => __("Content"),
                'column_name' => 'content',
            ],
            [
                'head_text' => __("Comment"),
                'column_name' => 'comment',
            ]
        ];
        parent::__construct();
        parent::forceTable(PluginDlteamsDataCatalog_Item::getTable());
    }

    static function canCreate() {return true;}
    static function canView() {return true;}
    static function canUpdate() {return true;}
    static function canDelete() {return true;}
    static function canPurge() {return true;}
    function canCreateItem() {return true;}
    function canViewItem() {return true;}
    function canUpdateItem() {return true;}
    function canDeleteItem() {return true;}
    function canPurgeItem() {return true;}

 static function getTypeName($nb = 0)
    {
        return __("Data catalog", 'dlteams');
    }

    static function getTypeNameForClass($nb = 0)
    {
        return __("Utilisateurs effectifs", 'dlteams');
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
                            $ong[] = static::createTabEntry(static::getTypeNameForClass(), count(static::getUtilisateursEffectifRequest($item)));
                        } else
                            $ong = static::getTypeNameForClass();
					// tab name traitements effectifs
                        $ong[] = static::createTabEntry(__("Traitements effectifs", 'dlteams'), count(static::getTraitementsEffectifRequest($item)));
                    }
                }

                return $ong;
                break;
            case Contact::class:
            case Supplier::class:
                if (!$withtemplate) {
                    if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            return static::createTabEntry(__("Catalogues accessibles", "dlteams"), count(self::getCataloguesAccessibleRequest($item)));
                        }
                        return static::getTypeName(2);
                    }
                }
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
//            si onglet objet datacatalog, on affiche tous les utilisateurs et groupes et contacts et suppliers qui on acces a ce catalogue
            case static::$itemtype_2:
//                utilisateurs effectifs
                if ($tabnum == 0)
                    self::showUtilisateursEffectifs($item);

//            traitements effectifs
                if ($tabnum == 1)
                    self::showTraitementsEffectifs($item);
                break;
            case Contact::class:
            case Supplier::class:
                self::showAccessibleCataloguesItems($item);
                break;
        }
    }

    /**Show items links to a document
     * @param $doc Document object
     * @return void*/
    public static function showUtilisateursEffectifs(PluginDlteamsDataCatalog $object_item)
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

        $items = self::getUtilisateursEffectifRequest($object_item);
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
            $header .= "<th>" . __("Intitulé") . "</th>";
			$header .= "<th>" . __("Nom") . "</th>";
            $header .= "<th>" . __("Prénom") . "</th>";
			$header .= "<th>" . __("Type") . "</th>";
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

				$nom = "";
				$prenom = "";
				if ($row["itemtype"]::getTypeName() == __("User")) {
					$nom = $item->getField('realname');
					$prenom = $item->getField('firstname');
				}
				if ($row["itemtype"]::getTypeName() == __("Contact")) {
					$nom = $item->getField('name');
					$prenom = $item->getField('firstname');
				}
	            echo "<td>" . $nom . "</td>";
				echo "<td>" . $prenom . "</td>";
                echo "<td>" . $row["itemtype"]::getTypeName() . "</td>";
                echo "<td>" . $row['comment'] . "</td>";
                echo "</tr>";
            }
            echo $header;
            echo "</table>";

        }
    }

    /**Show items links to a document
     * @param $doc Document object
     * @return void*/
    public static function showTraitementsEffectifs(PluginDlteamsDataCatalog $object_item)
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
        $items = self::getTraitementsEffectifRequest($object_item);
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
            //$header .= "<th>" . __(PluginDlteamsRecord::getTypeName()) . "</th>";
			$header .= "<th>" . __("Traitement") . "</th>";
            $header .= "<th>" . __("Type") . "</th>";
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

    public static function showAccessibleCataloguesItems(CommonDBTM $object_item)
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
        $items = self::getCataloguesAccessibleRequest($object_item);
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
            $header .= "<th>" . __("Catalogue") . "</th>";
//            $header .= "<th>" . __("Objet") . "</th>";
            $header .= "<th>" . __("Comment") . "</th>";
            $header .= "</tr>";
            echo $header;

            foreach ($items as $row) {
                /*                highlight_string("<?php\n\$data =\n" . var_export($row, true) . ";\n?>");*/
//                die();
                $item = new PluginDlteamsDataCatalog(); //plante si itemtype is null
                $item->getFromDB($row['datacatalogs_id']);
                $name = "<a target='_blank' href=\"" . $item::getFormURLWithID($item->fields['id']) . "\">" . $item->fields['name'] . "</a>";
                echo "<tr lass='tab_bg_1'>";
//                if ($canedit) {
//                    echo "<td>";
//                    Html::showMassiveActionCheckBox(__CLASS__, $row["id"]);
//                    echo "</td>";
//                }
                echo "<td>" . $name . "</td>";
//                echo "<td>" . PluginDlteamsDataCatalog::getTypeName() . "</td>";
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

    public static function getUtilisateursEffectifRequest(CommonDBTM $item)
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
                $table_item_name . '.itemtype' => ['LIKE', PluginDlteamsAccountKey::class],
                $table_item_name . '.' . 'datacatalogs_id' => $item->fields['id'],
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
                    PluginDlteamsAccountKey_Item::getTable() . ".id as linkid",
                    PluginDlteamsAccountKey_Item::getTable() . ".*",
                ],
                'FROM' => PluginDlteamsAccountKey_Item::getTable(),
                'OR' => [
                    [
                        'itemtype' => User::class,
                        'accountkeys_id' => $datacatalog_item["items_id"]
                    ],
                    [
                        'itemtype' => Group::class,
                        'accountkeys_id' => $datacatalog_item["items_id"]
                    ],
                    [
                        'itemtype' => Supplier::class,
                        'accountkeys_id' => $datacatalog_item["items_id"]
                    ],
                    [
                        'itemtype' => Contact::class,
                        'accountkeys_id' => $datacatalog_item["items_id"]
                    ],
                ],
            ];

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


    public static function getTraitementsEffectifRequest(CommonDBTM $item)
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
                $table_item_name . '.itemtype' => ['LIKE', PluginDlteamsPolicieForm::class],
                $table_item_name . '.' . 'datacatalogs_id' => $item->fields['id'],
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


        $records = [];
//      get records that have acces to this catalog through Policiefrom
        foreach ($temp as $policieform_item) {
            $request2 = [
                'SELECT' => [
                    PluginDlteamsPolicieForm_Item::getTable() . ".id as linkid",
                    PluginDlteamsPolicieForm_Item::getTable() . ".*",
                ],
                'FROM' => PluginDlteamsPolicieForm_Item::getTable(),
                'WHERE' => [
                    'itemtype' => PluginDlteamsRecord::class,
                    'policieforms_id' => $policieform_item["items_id"]
                ],
            ];
            $iterator = $DB->request($request2);
            $temp = [];

            foreach ($iterator as $id => $data) {
                if ($data["itemtype"]) {
                    array_push($records, $data);
                }
            }
        }
        return $records;
    }

    public static function getCataloguesAccessibleRequest(CommonDBTM $item)
    {
        global $DB;        //var_dump ($table_name, $columnid_name);


        $accountkeys = [];
//      get users that have acces to this catalog through account or keys

        $request2 = [
            'SELECT' => [
                PluginDlteamsAccountKey_Item::getTable() . ".id as linkid",
                PluginDlteamsAccountKey_Item::getTable() . ".*",
            ],
            'FROM' => PluginDlteamsAccountKey_Item::getTable(),
            'WHERE' => [
                'itemtype' => $item->getType(),
                'items_id' => $item->fields["id"]
            ]
        ];

        $iterator = $DB->request($request2);

        foreach ($iterator as $id => $data) {
            if ($data["itemtype"]) {
                array_push($accountkeys, $data);
            }
        }

        $temp = [];
        foreach ($accountkeys as $accountkey_item) {
            $query = [
                'SELECT' => [
                    PluginDlteamsDataCatalog_Item::getTable() . '.id AS linkid',
                    PluginDlteamsDataCatalog_Item::getTable() . '.itemtype AS itemtype',
                    PluginDlteamsDataCatalog_Item::getTable() . '.items_id AS items_id',
                    PluginDlteamsDataCatalog_Item::getTable() . '.*',
                ],
                'FROM' => PluginDlteamsDataCatalog_Item::getTable(),
                'WHERE' => [
                    PluginDlteamsDataCatalog_Item::getTable() . '.itemtype' => ['LIKE', PluginDlteamsAccountKey::class],
                    PluginDlteamsDataCatalog_Item::getTable() . '.' . 'items_id' => $accountkey_item["accountkeys_id"],
                ],
            ];

            if ($DB->fieldExists(PluginDlteamsDataCatalog_Item::getTable(), 'comment')) {
                $query['SELECT'][] = PluginDlteamsDataCatalog_Item::getTable() . '.comment AS comment';
            }


            $iterator = $DB->request($query);

            foreach ($iterator as $id => $data) {

                if ($data["itemtype"]) {
                    array_push($temp, $data);

                }

            }
        }

        return $temp;
    }
}
