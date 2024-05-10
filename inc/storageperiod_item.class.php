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

class PluginDlteamsStoragePeriod_Item extends CommonDBTM
{
    static public $itemtype_2 = 'PluginDlteamsStoragePeriod';
    static public $itemtype_1;
    public static $items_id_1;
    public static $title;
    public static $sub_title;
    public static $table_match_str = [];

    public function __construct()
    {
        static::$itemtype_1 = str_replace("_Item", "", __CLASS__); // $itemtype_1 ---> PluginDlteamsDataCatalog
        static::$items_id_1 = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", __CLASS__))) . "s_id";
        static::$title = __("Durées de conservations en relation avec cet objet", 'dlteams');
        static::$sub_title = __("Choisir une durée de conservation à lier à cet élément", 'dlteams');
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
        return __("Storage periods", 'dlteams');
    }

    static function getTypeNameForClass($nb = 0)
    {
        return __("Eléments rattachés", 'dlteams');
    }


    // affichage de l'onglet et de son nom
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case static::$itemtype_2:
                if (!$withtemplate) {
                    if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            return static::createTabEntry(static::getTypeNameForClass(), count(static::getItemsRequest($item)));
                        }
                        return static::getTypeNameForClass();
                    }
                }
                break;
            default:
                if (!$withtemplate) {
                    if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            return static::createTabEntry(static::getTypeName(2), static::countForItem($item));
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
            'ORDER' => [$temp->getTable().'.name ASC', self::getTable().'.itemtype ASC'],
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
                self::showItemsForItemType($item);
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
    public static function showItems(PluginDlteamsStoragePeriod $object_item)
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
            echo "<form name='storageperiod_form$rand' id='storageperiod_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
            echo "<input type='hidden' name='" . static::$items_id_1 . "' value='$instID'>";
            echo "<input type='hidden' name='itemtype1' value='" . str_replace("_Item", "", __CLASS__) . "'>";
            echo "<input type='hidden' name='items_id1' value='" . $instID . "'>";

            echo "<table class='tab_cadre_fixe'>";
            $title = "Related objects";
            $entitled = "Indicate the objects related to this element";
            echo "<tr class='tab_bg_2'><th colspan='3'>" . __($title, 'dlteams') .
                "</th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'><td class='right' style='text-wrap: nowrap;' width='40%'>" . __($entitled, 'dlteams');
            echo "</td><td width='40%' class='left'>";
            $types = PluginDlteamsItemType::getTypes();
            $key = array_search("PluginDlteamsLegalBasi", $types);
            unset($types[$key]);
            echo "<div style='display: flex; gap: 4px;'>";
            Dropdown::showSelectItemFromItemtypes(['itemtypes' => $types,
                'entity_restrict' => ($object_item->fields['is_recursive'] ? getSonsOf('glpi_entities', $object_item->fields['entities_id'])
                    : $object_item->fields['entities_id']),
                'checkright' => true,
                'used' => $used,
                'ajax_page' => "/marketplace/dlteams/ajax/dlteamsDropdownAllItem.php"
            ]);
            echo "</div>";
            unset($types);
            echo "</td><td width='20%' class='left'>";
            echo "</td></tr>";

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
            echo "<input for='storageperiod_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</div>";
            echo "</td><td width='20%' class='left'>";
            echo "</td></tr>";

            echo "</table>";
            Html::closeForm();
        }

        echo "<script>
                $(document).ready(function(e){
                    $(document).on('change', 'select[name=items_id]', function () {
                        if($(this).val() != '0'){
                            $('#field_submit').css('display', 'revert');
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
                        }
                    });
                });
        </script>";

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
                if ($canedit) {
                    echo "<td>";
                    Html::showMassiveActionCheckBox(__CLASS__, $row["id"]);
                    echo "</td>";
                }
                echo "<td>" . $name . "</td>";
                echo "<td>" . $row["itemtype"]::getTypeName() . "</td>";
                echo "<td>" . htmlspecialchars_decode($row['comment']??"") . "</td>";
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

    static function showItemsForItemType(CommonDBTM $object_item, $withtemplate = 0)
    {

        global $DB;
        $id = $object_item->fields['id'];
        if (!$object_item->can($id, READ)) {
            return false;
        }
        $canedit = $object_item->can($id, UPDATE);
        $rand = mt_rand(1, mt_getrandmax());

        // Request that joins 3 table (not possible to do with CommonDBRelation methods)
        // Result is used lower to display a table

        $request = [
            'SELECT' => [
                'glpi_plugin_dlteams_storageperiods_items.id AS linkid',
                'glpi_plugin_dlteams_storageperiods_items.itemtype AS itemtype',
                'glpi_plugin_dlteams_storageperiods_items.items_id AS items_id',
                'glpi_plugin_dlteams_storageperiods.id AS glpi_plugin_dlteams_storageperiods_id',
                'glpi_plugin_dlteams_storageperiods.name AS duree',
                'glpi_plugin_dlteams_storageperiods.content AS content',
                'glpi_plugin_dlteams_storageperiods_items.comment AS comment',
                'glpi_plugin_dlteams_storageperiods_items.plugin_dlteams_storageendactions_id AS storageendactions_id',
                'glpi_plugin_dlteams_storageperiods_items.plugin_dlteams_storagetypes_id AS storagetypes_id',
            ],
            'FROM' => 'glpi_plugin_dlteams_storageperiods_items',
            'LEFT JOIN' => [
                'glpi_plugin_dlteams_storageperiods' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_storageperiods_items' => "storageperiods_id",
                        'glpi_plugin_dlteams_storageperiods' => "id",
                    ]
                ],
                'glpi_plugin_dlteams_storagetypes' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_storageperiods' => "plugin_dlteams_storagetypes_id",
                        'glpi_plugin_dlteams_storagetypes' => "id",
                    ]
                ],
                'glpi_plugin_dlteams_storageendactions' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_storageendactions' => "id",
                        'glpi_plugin_dlteams_storageperiods_items' => "plugin_dlteams_storageendactions_id",
                    ]
                ],
            ],
            'ORDER' => [
                'glpi_plugin_dlteams_storagetypes.name ASC',
                'glpi_plugin_dlteams_storageendactions.name ASC',
                'glpi_plugin_dlteams_storageperiods.name ASC',
            ],
            'WHERE' => [
                'glpi_plugin_dlteams_storageperiods_items.items_id' => $object_item->fields['id'],
                'glpi_plugin_dlteams_storageperiods_items.itemtype' => $object_item->getType(),
            ]
        ];

        $iterator = $DB->request($request);
//        $iterator = [];


        $number = count($iterator);

        $items_list = [];
        $used = [];
        foreach ($iterator as $id => $data) {
            //while ($data = $iterator->next()) {
            $items_list[$data['linkid']] = $data;
            $used[$data['linkid']] = $data['linkid'];
        }


        /***form new**/
        if ($canedit) {
            echo "<div class='firstbloc'>";
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";
            $iden = $object_item->fields['id'];
            echo "<input type='hidden' name='plugin_dlteams_records_id' value='$iden' />";
            echo "<input type='hidden' name='itemtype1' value='" . $object_item->getType() . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $object_item->getID() . "' />";
            echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsStoragePeriod::getType() . "' />";
            echo "<input type='hidden' name='record_link' value='" . true . "' />";

            $title = __("Conservation time", 'dlteams');
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class=''><th colspan='3'>" . $title .
                "<br><i style='font-weight: normal'>" .
                __("In application of legal basis, what are conservation times of the data retained", 'dlteams') .
                "</i></th>";
            echo "</tr>";
            /*echo "<th colspan='2'></th></tr>";*/
            /**add by me**/
            echo "<tr class='tab_bg_1'><td class='' colspan='1'>";
            echo __("Specify the retention periods and the future of the personal data concerned by this processing", 'dlteams');
            echo "<br/><br/>";

            PluginDlteamsStoragePeriod::dropdown([
                'addicon' => PluginDlteamsStoragePeriod::canCreate(),
                'name' => 'plugin_dlteams_storageperiods_id',
                'width' => '200px'
            ]);
            echo "</td>";

            echo "<td width='' colspan='1'>";
            echo "<span style='display:none' id='td1'>";
            echo __("Stockage", 'dlteams');
            echo "<br/><br/>";
            PluginDlteamsStorageType::dropdown([
                'addicon' => PluginDlteamsStorageType::canCreate(),
                'name' => 'plugin_dlteams_storagetypes_id',
                'width' => '250px',
            ]);
            echo "</span>";
            echo "</td>";

            echo "<td width=''>";
            echo "<span style='display:none;float:left' id='td2'>";
            echo __("Action Fin Periode", 'dlteams');
            echo "<br/><br/>";
            PluginDlteamsStorageEndAction::dropdown([
                'addicon' => PluginDlteamsStorageEndAction::canCreate(),
                'name' => 'plugin_dlteams_storageendactions_id',
                'width' => '200px',
            ]);
            echo "</span>";
            echo "</td>";


            echo "<td style='width:30%'>";
            echo "<span style='display:none;float:right;width:100%;' id='td3'>";

            echo "<br/><br/>";
            Html::textarea(['name'            => 'storage_comment',
                'value'           => '',
                'class'           => 'storage_comment1',
                'cols'            => 60,
                'rows'            => 3,
                'enable_richtext' => true]);
//            echo "<textarea type='text' style='width:350px;float:right;' maxlength=1000 rows='3' name='storage_comment' class='storage_comment1' placeholder='commentaire'></textarea>";
            echo "</span>";
            echo "</td>";

            echo "<td>";
            echo "<span style='display:none;float:left;margin-left:10px;margin-top:5px' id='td4'>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin-top:35px'>";
            echo "</span>";
            echo "</td>";

            echo "</table>";
            Html::closeForm();
            echo "</div>";
        }

        echo "<div class='spaced'>";
        if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . PluginDlteamsStoragePeriod_Itemtype_Actions::class . $rand);
            $massive_action_params = ['container' => 'mass' . PluginDlteamsStoragePeriod_Itemtype_Actions::class . $rand,
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
            $header_top .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsStoragePeriod_Itemtype_Actions::class . $rand);
            $header_bottom .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsStoragePeriod_Itemtype_Actions::class . $rand);
            $header_end .= "</th>";
        }

        $header_end .= "<th>" . __("Durée de conservation") . "</th>";
        $header_end .= "<th>" . __("Stockage") . "</th>";

        $header_end .= "<th>" . __("En fin de période") . "</th>";
        $header_end .= "<th>" . __("Comment") . "</th>";


        $header_end .= "</tr>";

        echo $header_begin . $header_top . $header_end;


        foreach ($items_list as $data) {
            echo "<tr class='tab_bg_1'>";

            if ($canedit && $number) {
                echo "<td width='10'>";
                Html::showMassiveActionCheckBox(PluginDlteamsStoragePeriod_Itemtype_Actions::class, $data['linkid']);
                echo "</td>";
            }
            foreach (["" => 'PluginDlteamsStoragePeriod'] as $table => $class) {
//
            }
            $name = "<a target='_blank' href=\"" . $class::getFormURLWithID($data['glpi_plugin_dlteams_storageperiods_id']) . "\">" . $data["duree"] . "</a>";


            $endaction = new PluginDlteamsStorageEndAction();
            $storagetype = new PluginDlteamsStorageType();


            if (
                $endaction->getFromDB($data["storageendactions_id"])
                && isset($endaction->fields["name"])
            ) {

                $endactionname = $endaction->fields["name"];
                $endactionid = $endaction->fields["id"];
                $endaction_link = "<a target='_blank' href=\"" . PluginDlteamsStorageEndAction::getFormURLWithID($endactionid) . "\">" . $endactionname . "</a>";
            } else {
                $endaction_link = null;
            }

            if (
            $storagetype->getFromDB($data["storagetypes_id"])
            ) {

                $typeid = $storagetype->fields["id"];
                $typename = $storagetype->fields["name"];
                $stockage = "<a target='_blank' href=\"" . PluginDlteamsStorageType::getFormURLWithID($typeid) . "\">" . $typename . "</a>";
            } else {
                $stockage = null;
            }
            echo "<td class='left'>" . $name . "</td>";
            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $stockage . "</td>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">"
                . $endaction_link . "</td>";
            echo "<td class='left'>" . ($data['comment'] ? htmlspecialchars_decode($data['comment']): "") . "</td>";
            echo "</tr>";
        }

        if ($iterator && $iterator->count() > 25) {
            echo $header_begin . $header_bottom . $header_end;
        }
        echo "</table>";

        if ($canedit && $number) {
            $massive_action_params['ontop'] = false;
            if ($iterator->count() > 25) {
                Html::showMassiveActions($massive_action_params);
            }
            Html::closeForm();
        }
        echo "</div>";
    }


    public function post_purgeItem()
    {
//        purge relations
        $relation_item_str = $this->fields["itemtype"] . "_Item";
        if(!class_exists($relation_item_str))
            $relation_item_str = "PluginDlteams".$relation_item_str;
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
        if(!class_exists($relation_item_str))
            $relation_item_str = "PluginDlteams".$relation_item_str;
        $relation_item = new $relation_item_str();
        $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

        $criteria = [
            "itemtype" => static::$itemtype_2,
            "items_id" => $this->fields[static::$items_id_1],
            $relation_column_id => $this->fields["items_id"],
            "comment" => $this->oldvalues["comment"]
        ];

        $relation_item->deleteByCriteria($criteria);

        $criteria["plugin_dlteams_storageendactions_id"] = $this->fields["plugin_dlteams_storageendactions_id"];
        $criteria["plugin_dlteams_storagetypes_id"] = $this->fields["plugin_dlteams_storagetypes_id"];
        $criteria["comment"] = $this->fields["comment"];

        $relation_item->add([
            ...$criteria,
        ]);

        Session::addMessageAfterRedirect("Relation mis a jour avec succès");
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
        global $DB; 		//var_dump ($table_name, $columnid_name);
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
