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

class PluginDlteamsAccountKey_Attribution extends CommonDBTM
{
    static public $itemtype_2 = PluginDlteamsAccountKey::class;
    static public $itemtype_1;
    public static $items_id_1 = 'accountkeys_id';
    public static $title;
    public static $sub_title;
    public static $table_match_str = [];

    public function __construct()
    {

        static::$itemtype_1 = str_replace("_Item", "", __CLASS__); // $itemtype_1 ---> PluginDlteamsDataCatalog
        static::$title = __("Indiquez les comptes pouvant accéder à ce catalogue et leur rôle", 'dlteams');
        static::$sub_title = __("Choisir un catalogue à lier à cet élément", 'dlteams');
        static::$table_match_str = [
            [
                'head_text' => PluginDlteamsAccountKey::getTypeName(),
                'column_name' => 'name',
                'show_as_link' => true
            ],
            [
                'head_text' => __("Rôle"),
                'column_name' => 'content',
            ],
            [
                'head_text' => __("Comment"),
                'column_name' => 'comment',
            ]

        ];
        self::forceTable(PluginDlteamsAccountKey_Item::getTable());
        parent::__construct();
    }

    public static function getTable($classname = null)
    {
        return PluginDlteamsAccountKey_Item::getTable();
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

    static function getTypeName($nb = 0)
    {
        return __("Clés", 'dlteams');
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

    static function getTypeNameForClass($nb = 0)
    {
        return __("Attributions", 'dlteams');
    }


    // affichage de l'onglet et de son nom
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case static::$itemtype_2:
                if (!$withtemplate) {
                    if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            return static::createTabEntry(static::getTypeNameForClass(), count(static::getRequest($item)));
                        }
                        return static::getTypeNameForClass();
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
        return $dbu->countElementsInTable(PluginDlteamsAccountKey_Item::getTable(), ['items_id' => $item->getID(), 'itemtype' => $item->getType()]);
    }

    public static function getRequest(CommonDBTM $item)
    {

        $table_name = PluginDlteamsAccountKey::getTable();

        $columnid_name = "accountkeys_id";
        global $DB;        //var_dump ($table_name, $columnid_name);
        $table_item_name = getTableForItemType(PluginDlteamsAccountKey_Item::class);


        $query = [
            'SELECT' => [
                $table_item_name . '.id AS linkid',
                $table_item_name . '.itemtype AS itemtype',
                $table_item_name . '.items_id AS items_id',
                $table_item_name . '.*',
                $table_name . '.id AS id',
                $table_name . '.name AS name',
                'glpi_plugin_dlteams_keytypes' . '.name AS keytypename',
//                $table_name . '.content AS content',
            ],
            'FROM' => $table_item_name,
            'LEFT JOIN' => [
                $table_name => [
                    'ON' => [
                        $table_item_name => $columnid_name,
                        $table_name => 'id'
                    ]
                ],
                'glpi_plugin_dlteams_keytypes' => [
                    'ON' => [
                        PluginDlteamsAccountKey::getTable() => 'plugin_dlteams_keytypes_id',
                        'glpi_plugin_dlteams_keytypes' => 'id'
                    ]
                ]
            ],
            'OR' => [
                [
                    $table_item_name . '.itemtype' => Group::class,
                    $table_item_name . '.' . 'accountkeys_id' => $item->fields['id'],
                ],
                [
                    $table_item_name . '.itemtype' => Supplier::class,
                    $table_item_name . '.' . 'accountkeys_id' => $item->fields['id'],
                ],
                [
                    $table_item_name . '.itemtype' => Contact::class,
                    $table_item_name . '.' . 'accountkeys_id' => $item->fields['id'],
                ],
                [
                    $table_item_name . '.itemtype' => User::class,
                    $table_item_name . '.' . 'accountkeys_id' => $item->fields['id'],
                ]
            ],
            'ORDERBY' => [$table_name.'.name ASC']
        ];


        /*        highlight_string("<?php\n\$data =\n" . var_export($query, true) . ";\n?>");*/
//        die();
        if ($DB->fieldExists($table_item_name, 'comment')) {
            $query['SELECT'][] = $table_item_name . '.comment AS comment';
        }

        if ($DB->fieldExists($table_name, 'content')) {
            $query['SELECT'][] = $table_name . '.content AS content';
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
        return $temp;
    }


    public static function getCountAndUsersName(CommonDBTM $item)
    {

        $table_name = PluginDlteamsAccountKey::getTable();

        $columnid_name = "accountkeys_id";
        global $DB;        //var_dump ($table_name, $columnid_name);
        $table_item_name = getTableForItemType(PluginDlteamsAccountKey_Item::class);


        $query = [
            'SELECT' => [
                $table_item_name . '.id AS linkid',
                $table_item_name . '.itemtype AS itemtype',
                $table_item_name . '.items_id AS items_id',
                $table_item_name . '.*',
                $table_name . '.id AS id',
                $table_name . '.name AS name',
                'glpi_plugin_dlteams_keytypes' . '.name AS keytypename',
//                $table_name . '.content AS content',
            ],
            'FROM' => $table_item_name,
            'LEFT JOIN' => [
                $table_name => [
                    'ON' => [
                        $table_item_name => $columnid_name,
                        $table_name => 'id'
                    ]
                ],
                'glpi_plugin_dlteams_keytypes' => [
                    'ON' => [
                        PluginDlteamsAccountKey::getTable() => 'plugin_dlteams_keytypes_id',
                        'glpi_plugin_dlteams_keytypes' => 'id'
                    ]
                ]
            ],
            'OR' => [
                [
                    $table_item_name . '.itemtype' => Group::class,
                    $table_item_name . '.' . 'accountkeys_id' => $item->fields['id'],
                ],
                [
                    $table_item_name . '.itemtype' => Supplier::class,
                    $table_item_name . '.' . 'accountkeys_id' => $item->fields['id'],
                ],
                [
                    $table_item_name . '.itemtype' => Contact::class,
                    $table_item_name . '.' . 'accountkeys_id' => $item->fields['id'],
                ],
                [
                    $table_item_name . '.itemtype' => User::class,
                    $table_item_name . '.' . 'accountkeys_id' => $item->fields['id'],
                ]
            ],
            'ORDERBY' => [$table_name.'.name ASC']
        ];


        /*        highlight_string("<?php\n\$data =\n" . var_export($query, true) . ";\n?>");*/
//        die();
        if ($DB->fieldExists($table_item_name, 'comment')) {
            $query['SELECT'][] = $table_item_name . '.comment AS comment';
        }

        if ($DB->fieldExists($table_name, 'content')) {
            $query['SELECT'][] = $table_name . '.content AS content';
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

        $names = [];
        foreach ($temp as $row){


            $tipename = $row["itemtype"]::getTypeName();
            $nom = "";
            $prenom = "";
            $itemtype_str = $row["itemtype"];
            $itemtype_object = new $itemtype_str();
            $itemtype_object->getFromDB($row["items_id"]);
            if ($itemtype_str == User::class) {
                $nom = $itemtype_object->fields["realname"];
                $prenom = $itemtype_object->fields["firstname"];
            }

            if ($itemtype_str == Contact::class) {
                $nom = $itemtype_object->fields["name"];
                $prenom = $itemtype_object->fields["firstname"];
            }

            if($itemtype_str == Group::class){
                $nom = $itemtype_object->fields["completename"];
            }
            $names[$row["linkid"]] = sprintf("<i class='%s'></i> %s %s", $row["itemtype"]::getIcon(), $nom??"", $prenom??"");
        }

        return [
            "count" => count($temp),
            "names" => $names
        ];
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
            case static::$itemtype_2:
                self::showItems($item);
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
    public static function showItems(PluginDlteamsAccountKey $object_item)
    {
        global $DB;

        $instID = $object_item->fields['id'];
        if (!$object_item->can($instID, READ)) {
            return false;
        }
        $canedit = $object_item->can($instID, UPDATE);
        // for a measure,
        // don't show here others protective measures associated to this one,
        // it's done for both directions in self::showAssociated
        $types_iterator = [];
        $number = count($types_iterator);

        $used = [];
        $types = PluginDlteamsItemType::getTypes();
//        Enlève le choix de L'objet LegalBasi dans la dropdown qui affiche la liste des objets
        $key = array_search("PluginDlteamsDataCatalog", $types);
        unset($types[$key]);
        $rand = mt_rand();

        if ($canedit) {
            echo "<form name='attribution_form$rand' id='attribution_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
            echo "<input type='hidden' name='" . static::$items_id_1 . "' value='$instID'>";
            echo "<input type='hidden' name='itemtype1' value='" . str_replace("_Item", "", PluginDlteamsAccountKey::class) . "'>";
            echo "<input type='hidden' name='items_id1' value='" . $instID . "'>";

            echo "<table class='tab_cadre_fixe'>";
            $title = "Attributions à ce compte";
            $entitled = "";
            echo "<tr class='tab_bg_2'><th colspan='3'>" . __($title, 'dlteams') .
                "</th>";

            echo "</tr>";

            echo "<tr class='tab_bg_1'>";
//            echo "<td class='right' style='text-wrap: nowrap;'>" ;
//            echo "</td>";
            echo "<td width='30%' class='left'>";
            //            echo "<td style='display: none' id='td2'>";
            echo "<span style='float:left;'>";
            global $CFG_GLPI;

            $id = $object_item->fields['id'];
            if (!$object_item->can($id, READ)) {
                return false;
            }

            $canedit = PluginDlteamsDataCatalog::canUpdate();
            $rand = mt_rand(1, mt_getrandmax());

//            echo __("Type d'usager", "dlteams");
//            echo "&nbsp";
            $rand = Dropdown::showFromArray("itemtype_ref", [
                __("------", 'dlteams'),
                __("Groupe", 'dlteams'),
                __("Utilisateur", 'dlteams'),
                __(Contact::getTypeName(), 'dlteams'),
                __(Supplier::getTypeName(), 'dlteams'),
            ], [
                'value' => 0,
                'width' => '100%'
            ]);


            $params = [
                'itemtype_ref' => '__VALUE__',
                'datacatalogs_id' => $id,
            ];
            Ajax::updateItemOnSelectEvent(
                "dropdown_itemtype_ref$rand",
                'itemtype_row',
                $CFG_GLPI['root_doc'] . '/marketplace/dlteams/ajax/account_item_dropdown.php',
                $params
            );
            echo "</span>";
            echo "<span id='itemtype_row' style='margin-left:5px!important'>";
            echo "</span>";
//
//            echo "</td>";
            echo "</td>";
            echo "<td class='right' id='field_comment_label' style='display: none' >" . __("Comment");
            echo "</td><td width='10%' class='left comment-td' style='display: none' id='field_comment'>";
            echo "<div style='display: flex; gap: 4px;'>";
            echo "<textarea type='text' style='width:100%' maxlength=1000 rows='1' name='comment' class='comment'></textarea>";
            echo "</div>";
            echo "</td>";

            echo "<td width='40%' style='display: none;' class='left' id='field_submit'>";

            echo "<div>";
            echo "<input for='attribution_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</div>";
            echo "</td>";

            echo "</tr>";


            echo "<style>
                .comment-td {width: 40%;}
                @media (max-width: 767px) {.comment-td {width: 100%;}}
              </style>";

            echo "</table>";
            Html::closeForm();
        }

        echo "<script>
                $(document).ready(function(e){
                    $(document).on('change', 'select[name=items_id]', function () {
                        if($(this).val() != '0'){
                            $('#field_submit').css('display', 'revert');
                            $('#field_comment').css('display', 'revert');
                            $('#field_comment_label').css('display', 'revert');
                            var objet;
                            switch ($('select[name=itemtype_ref]').val()) {
                              case '0':
                                  break;
                              case '1':
                                   objet = 'Group';
                                  break;                                    
                              case '2': 
                                   objet = 'User';
                                break;
                              case '3':
                                    objet = 'Contact';
                                break;
                              case '4':
                                    objet = 'Supplier';
                                break;
                            }
                            $.ajax({
                                url: '/marketplace/dlteams/ajax/get_object_specific_field.php',
                                type: 'POST',
                                data: {
                                    id: $(this).val(),
                                    object: objet??null,
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
                            $('#field_comment_label').css('display', 'none');   
                        }
                    });
                    
                    $(document).on('change', 'select[name=itemtype_ref]', function () {
                         $('textarea[name=comment]').val('');
                            $('#field_submit').css('display', 'none');
                            $('#field_comment').css('display', 'none');   
                            $('#field_comment_label').css('display', 'none');   
                    });
                });
        </script>";

//        var_dump(self::getTable());
        $items = self::getRequest($object_item);

        if (!count($items)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {

            if ($canedit) {
                Html::openMassiveActionsForm('mass' . PluginDlteamsAccountKey_Item::class . $rand);
                $massiveactionparams = [
                    'num_displayed' => min($_SESSION['glpilist_limit'], count($items)),
                    'container' => 'mass' . PluginDlteamsAccountKey_Item::class . $rand
                ];
                Html::showMassiveActions($massiveactionparams);
            }

            echo "<table class='tab_cadre_fixehov'>";
            $header = "<tr>";
            if ($canedit) {
                $header .= "<th width='10'>";
                $header .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsAccountKey_Item::class . $rand);
                $header .= "</th>";
            }
            $header .= "<th>" . __("Utilisateur") . "</th>";
            $header .= "<th>" . __("Type") . "</th>";
            $header .= "<th>" . __("Nom") . "</th>";
            $header .= "<th>" . __("Prénom") . "</th>";
            $header .= "<th>" . __("Comment") . "</th>";
            $header .= "</tr>";
            echo $header;

            foreach ($items as $row) {
                $item = new $row['itemtype'](); //plante si itemtype is null
                $item->getFromDB($row['items_id']);

                if(isset($item->fields["realname"]))
                    $name = $item->fields["realname"];
                else
                    $name = $item->getField('name');
                $name = "<a target='_blank' href=\"" . $item::getFormURLWithID($item->getField('id')) . "\">" . $name . "</a>";
                echo "<tr lass='tab_bg_1'>";
                if ($canedit) {
                    echo "<td>";
                    Html::showMassiveActionCheckBox(PluginDlteamsAccountKey_Item::class, $row["id"]);
                    echo "</td>";
                }
                echo "<td>" . $name . "</td>";
                echo "<td>" . $row["itemtype"]::getTypeName() . "</td>";

                $nom = "";
                $prenom = "";
                $itemtype_str = $row["itemtype"];
                $itemtype_object = new $itemtype_str();
                if ($itemtype_str == User::class) {
                    $nom = $item->fields["realname"];
                    $prenom = $item->fields["firstname"];
                }

                if ($itemtype_str == Contact::class) {
                    $nom = $item->fields["name"];
                    $prenom = $item->fields["firstname"];
                }

                if($itemtype_str == Group::class){
                    $nom = $item->fields["completename"];
                }
                echo "<td class='left' width='20%'>" . ($nom ?? "") . "</td>";
                echo "<td class='left' width='20%'>" . ($prenom ?? "") . "</td>";


                echo "<td>" . $row['comment'] . "</td>";
                echo "</tr>";
            }
//            echo $header;
            echo "</table>";

            if ($canedit && count($items)) {
                $massiveactionparams['ontop'] = false;
                Html::showMassiveActions($massiveactionparams);
            }
            if ($canedit) {
                Html::closeForm();
            }
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

    function rawSearchOptions()
    {
        $tab[] = [
            'id' => '44',
            'table' => PluginDlteamsUserProfile::getTable(),
            'field' => 'items_id',
            'datatype' => 'dropdown',
            'name' => PluginDlteamsUserProfile::getTypeName(),
            'forcegroupby' => true,
            'massiveaction' => true,
        ];

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

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }

}
