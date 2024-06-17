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

class PluginDlteamsProtectiveMeasure_Item extends CommonDBRelation
{
    static public $itemtype_2 = 'PluginDlteamsProtectiveMeasure';
    static public $itemtype_1;
    public static $items_id_1;
    public static $title;
    public static $sub_title;
    public static $table_match_str = [];

    public function __construct()
    {
        static::$itemtype_1 = str_replace("_Item", "", __CLASS__); // $itemtype_1 ---> PluginDlteamsProtectiveMeasure
        static::$items_id_1 = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", __CLASS__))) . "s_id";
        static::$title = __("Mesures de protection en relation avec cet élément", 'dlteams');
        static::$sub_title = __("Choisir une nouvelle mesure de protection", 'dlteams');
        static::$table_match_str = [
            [
                'head_text' => __("Name"),
                'column_name' => 'name',
                'show_as_link' => true
            ],
            [
                'head_text' => __("Type"),
                'column_name' => 'typename',
            ],
            [
                'head_text' => __("Categorie"),
                'column_name' => 'namecat',
            ],
            [
                'head_text' => __("Comment"),
                'column_name' => 'comment',
            ]
        ];
        parent::__construct();
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
        return __("Mesures de protection", 'dlteams');
    }

    static function getTypeNameForClass($nb = 0)
    {
        return __("Eléments rattachés", 'dlteams');
    }


    // affichage de l'onglet et de son nom
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

//        var_dump(Session::haveRight($item::$rightname, READ));
//        die();
        switch ($item->getType()) {
            case static::$itemtype_2:
                if (!$withtemplate) {
                    if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            return static::createTabEntry(static::getTypeNameForClass(), static::countForItem($item));
                        }
                        return static::getTypeNameForClass();
                    }
                }
                break;

            default:
                if (!$withtemplate) {
//                    if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            return static::createTabEntry(static::getTypeName(2), static::countForItem($item));
                        }
                        return static::getTypeName(2);
//                    }
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
                self::getTable() . '.*',
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
            'ORDER' => self::getTable() . '.id DESC'
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
            case static::$itemtype_2:
                self::showItems($item);
                break;
            default:
                self::showForItem($item);
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
    public static function showItems(PluginDlteamsProtectiveMeasure $object_item)
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
        $key = array_search(static::$itemtype_2, $types);
        unset($types[$key]);
        $rand = mt_rand();


        if ($canedit) {
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
            echo "<input type='hidden' name='" . static::$items_id_1 . "' value='$instID'>";
            echo "<input type='hidden' name='itemtype1' value='" . str_replace("_Item", "", __CLASS__) . "'>";
            echo "<input type='hidden' name='items_id1' value='" . $instID . "'>";

            echo "<table class='tab_cadre_fixe'>";
            $title = "Related objects";
            $entitled = "Indicate the objects related to this element";
            echo "<tr class='tab_bg_2'><th colspan='2'>" . __($title, 'dlteams') .
                "<br><i style='font-weight: normal'>" .
                "</i></th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'><td class='left' width='40%'>" . __($entitled, 'dlteams');
            echo "</td><td width='40%' class='left'>";
            $types = PluginDlteamsItemType::getTypes();
            $key = array_search("PluginDlteamsLegalBasi", $types);
            unset($types[$key]);
            Dropdown::showSelectItemFromItemtypes(['itemtypes' => $types,
                'entity_restrict' => ($object_item->fields['is_recursive'] ? getSonsOf('glpi_entities', $object_item->fields['entities_id'])
                    : $object_item->fields['entities_id']),
                'checkright' => true,
                'used' => $used
            ]);
            unset($types);
            echo "</td><td width='20%' class='left'><input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td></tr>";
            echo "<tr class='tab_bg_1'><td width='35%' class=''>";
            echo __("Comment");
            echo "<br/><br/>";
            echo "<textarea type='text' style='width:100%' maxlength=1000 rows='3' name='comment' class='comment_legalbasi_item'></textarea>";
            echo "</td>";
            echo "</table>";
            Html::closeForm();
        }

//        var_dump(self::getTable());
        $items = self::getItemsRequest($object_item);

        if (!count($items)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
            if ($canedit) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = [
                    'num_displayed' => min($_SESSION['glpilist_limit'], count($items)),
                    'container' => 'mass' . __CLASS__ . $rand
                ];
                Html::showMassiveActions($massiveactionparams);
            }

            echo "<table class='tab_cadre_fixehov'>";
            $header = "<tr>";
            if ($canedit) {
                $header .= "<th width='10'>";
                $header .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                $header .= "</th>";
            }
            $header .= "<th>" . __("Nom") . "</th>";
            $header .= "<th>" . __("Type") . "</th>";

            // colonne non generique
            $header .= "<th>" . __("Categorie") . "</th>";
            $header .= "<th>" . __("Comment") . "</th>";
            $header .= "</tr>";
            echo $header;



            foreach ($items as $row) {
                $item = new $row['itemtype']();
                $item->getFromDB($row['items_id']);
                $name = "<a target='_blank' href=\"" . $item::getFormURLWithID($item->getField('id')) . "\">" . $item->getField('name') . "</a>";
                echo "<tr lass='tab_bg_1'>";
                if ($canedit) {
                    echo "<td>";
                    Html::showMassiveActionCheckBox(__CLASS__, $row["id"]);
                    echo "</td>";
                }
                echo "<td>" . $name . "</td>";
                echo "<td>" . $row["itemtype"]::getTypeName() . "</td>";

                // colonne non generique
                $item = new PluginDlteamsProtectiveCategory();
                $item->getFromDB($row['protectivemeasures_id']);
                $name = "<a target='_blank' href=\"" . $item::getFormURLWithID($row['protectivemeasures_id']) . "\">" . $item->getField('name') . "</a>";

                echo "<td>" . $name . "</td>";
                echo "<td>" . $row['comment'] . "</td>";
                echo "</tr>";

            }
            echo $header;
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

    static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {
        $id = $item->fields['id'];
        $canedit = $item->can($id, UPDATE); // canedit booleen = true
        $rand = mt_rand(1, mt_getrandmax());
        global $DB;

        $iterator = static::getRequest($item);
        $number = count($iterator);
        $items_list = [];

        $used = [];

        //while ($data = $iterator->next()) {
        foreach ($iterator as $id => $data) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }


        if ($canedit) {
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
             action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . $item->getType() . "' />";
            echo "<input type='hidden' name='itemtype' value='" . static::$itemtype_2 . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $item->getID() . "' />";
            echo "<input type='hidden' name='entities_id' value='" . $item->fields['entities_id'] . "' />";
//
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr class='tab_bg_2'><th colspan='3'>" . static::$title .
                "</i></th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td class='right' width='20%'>";
            echo static::$sub_title;
            echo "</td>";
            echo "<td style='display: flex;' class='left'>";

            $used = [];
            foreach ($iterator as $id => $data) {
                // while ($data = $iterator->next()) {
                $items_list[$data['linkid']] = $data;

                $used[$data['linkid']] = $data['linkid'];
            }

            $mesures_applicables_query = [
                "FROM" => PluginDlteamsProtectiveMeasure::getTable(),
                "WHERE" => [
                    "applicables" => [
                        'LIKE', "%".$item::getType()."%"
                    ]
                ]
            ];

            global $DB;

            $mesures_applicables_iterator = $DB->request($mesures_applicables_query);

            $applicables = [];
            foreach ($mesures_applicables_iterator as $applicable){
                $applicables[] = $applicable["id"];
            }


            global $CFG_GLPI;
            $params = [
                'addicon' => false,
                'name' => 'items_id',
                'value' => "", //$responsible,
                //'entity' => $this->fields["entities_id"],
                'right' => 'all',
                'width' => "250px",
                'url' => $CFG_GLPI['root_doc'] . "/marketplace/dlteams/ajax/getDropdownValue.php",
                'used' => $used,
            ];

            if(count($applicables) > 0) {
                $params['condition'] = [
                    'id' => $applicables
                ];
            }
            static::$itemtype_2::dropdown($params);
            echo "&nbsp;";


            $field_id = Html::cleanId("dropdown_" . 'items_' . mt_rand());
            $item_link = getItemForItemtype(static::$itemtype_2);

            echo '<div class="btn btn-outline-secondary"
                           title="' . __s('Add') . '" data-bs-toggle="modal" data-bs-target="#add_' . $field_id . '">'
                . Ajax::createIframeModalWindow('add_' . $field_id, $item_link->getFormURL(), ['display' => false])
                . "<span data-bs-toggle='tooltip'>
              <i class='fa-fw ti ti-plus'></i>
              <span class='sr-only'>" . __s('Add') . "</span>
                </span>"
                . '</div>';


            echo "</td>";
            echo "<td class='left'>";
            echo "</td>";
            echo "</tr>";


//
//
            echo "<tr class='tab_bg_1' style='display: none;' id='field-createlink'>";
            echo "<td class='right' width='20%'>";
            echo __("Comment");
            echo "</td>";
            echo "<td style='display: flex;' class='left'>";
            echo "<textarea type='text' style='width:100%;' maxlength=1000 rows='2' name='comment' class='comment'></textarea>";
            echo "</td>";
            echo "<td class='left'>";
            echo "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<td>";
            echo "</td>";
            echo "<td colspan='2' class='left'>";
            echo "<button name='link_element' style='display: none;' id='btn-createlink' class='btn btn-primary'>Ajouter</button>";
            echo "</td>";
            echo "</tr>";


            echo "</table>";
            Html::closeForm();
        }

        echo "<script>
                $(document).ready(function(e){

                $('select[name=items_id]').on('change', function () {
                    if($(this).val() != '0'){
                        document.getElementById('btn-createlink').style.display = 'block';
                        document.getElementById('field-createlink').style.display = 'table-row';
                        
                        $.ajax({
                                url: '/marketplace/dlteams/ajax/get_object_specific_field.php',
                                type: 'POST',
                                data: {
                                    id: $(this).val(),
                                    object: '" . static::$itemtype_2 . "',
                                    field: 'content'
                                },
                                success: function (data) {
                                    // Handle the returned data here
                                    let comm_field = $('textarea[name=comment]');
                                    comm_field.val(data);
                                    comm_field.val(comm_field.val().replace(/^\s+/, ''));
                                }
                            });                      
                        
                        
                    }
                    else{
                        document.getElementById('btn-createlink').style.display = 'none';
                        document.getElementById('field-createlink').style.display = 'none';
                    }
                       
                    });
                });
        </script>";




            echo "<div class='spaced'>";
            if ($canedit && $number) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massive_action_params = [
                    'container' => 'mass' . __CLASS__ . $rand,
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
                $header_top .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
//                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                $header_end .= "</th>";
            }


            foreach (static::$table_match_str as $column) {
                $header_end .= "<th>" . $column["head_text"] . "</th>";
            }
            $header_end .= "</tr>";

            echo $header_begin . $header_top . $header_end;
            //var_dump($items_list);
            foreach ($items_list as $data) {

                echo "<tr class='tab_bg_1'>";

                if ($canedit && $number) {
                    echo "<td width='10'>";

                    $item_str = $item::class . "_Item";
                    Html::showMassiveActionCheckBox(__CLASS__, $data['linkid']);
                    echo "</td>";

                    $id = $data['linkid'];
                }


                foreach (static::$table_match_str as $key => $column) {

                    if (isset($data[$column["column_name"]]))
                        $name = $data[$column["column_name"]];
                    if (!isset($data[$column["column_name"]]))
                        $name = "--";

                    if ($key === 0)
                        $name = "<a target='_blank' href=\"" . static::$itemtype_2::getFormURLWithID($data[static::$items_id_1]) . "\">" . $name . "</a>";

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo ">" . $name . "</td>";
                }

                echo "</tr>";
            }

            echo "</table>";

            Html::closeForm();

            echo "
                <script>
                    $(document).ready(function(e) {
                        //var window.eventBinded = false;
                
                        $(document).on('change', 'select[name=id_field]', function () {
                            console.log('shhhh');
                            
                            if (!alertShown) {
                                $.ajax({
                                        url: '/marketplace/dlteams/ajax/get_object_specific_field.php',
                                        type: 'POST',
                                        data: {
                                            id: " . $id . ",
                                            object: '" . static::$itemtype_2 . "_Item" . "',
                                            field: 'comment'
                                        },
                                        success: function (data) {
                                            // Handle the returned data here
                                            console.log(data);
                                            $('textarea[name=comment]').val(data);
                                        }
                            });
                                eventBinded = true;
                            }
                        });
                    });
                </script>
                ";

            echo "</div>";

    }

    public function post_purgeItem()
    {
//        purge relations
        $relation_item_str = $this->fields["itemtype"] . "_Item";
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

    public static function getRequest(CommonDBTM $item)
    {
        $table_name = static::$itemtype_2::getTable(); // si $item = DataCatalog, $table_name contiendra data_catalogs
        $columnid_name = strtolower(str_replace("PluginDlteams", "", static::$itemtype_2::getType())) . "s_id"; // $columnid_name contiendra users_id si $item = User

//
        global $DB;
        $table_item_name = getTableForItemType(static::$itemtype_2 . "_Item");

        $query = [
            'SELECT' => [
                $table_item_name . '.id AS linkid',
                $table_item_name . '.itemtype AS itemtype',
                $table_item_name . '.items_id AS items_id',
                $table_item_name . '.*',
                $table_name . '.id AS id',
                $table_name . '.name AS name',
                $table_name . '.content AS content',
                'glpi_plugin_dlteams_protectivetypes.name AS typename',
                'glpi_plugin_dlteams_protectivecategories.name as namecat',
            ],
            'FROM' => $table_item_name,
            'LEFT JOIN' => [
                $table_name => [
                    'ON' => [
                        $table_item_name => $columnid_name,
                        $table_name => 'id'
                    ]
                ]
            ],
            'JOIN' => [
                'glpi_plugin_dlteams_protectivetypes' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_protectivemeasures' => "plugin_dlteams_protectivetypes_id",
                        'glpi_plugin_dlteams_protectivetypes' => "id"
                    ]
                ],
                'glpi_plugin_dlteams_protectivecategories' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_protectivemeasures' => "plugin_dlteams_protectivecategories_id",
                        'glpi_plugin_dlteams_protectivecategories' => "id"
                    ]
                ],
            ],
            'WHERE' => [
                $table_item_name . '.itemtype' => ['LIKE', $item::getType()],
                $table_item_name . '.' . 'items_id' => $item->fields['id'],
            ],
            'ORDERBY' => ['name ASC']
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
}
