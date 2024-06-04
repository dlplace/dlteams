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

class PluginDlteamsDatacatalog_Protectivemeasure extends CommonDBTM
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
        static::$items_id_1 = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", PluginDlteamsProtectiveMeasure::class))) . "s_id";
        static::$title = __("Mesures prises pour assurer la sécurité et la confidentialité de ce catalogue ", 'dlteams');
        static::$sub_title = __("Choisir les mesures de protection", 'dlteams');
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
//            [
//                'head_text' => __("Content"),
//                'column_name' => 'content',
//            ],
            [
                'head_text' => __("Comment"),
                'column_name' => 'comment',
            ]
        ];
        parent::__construct();
        self::forceTable(PluginDlteamsProtectiveMeasure_Item::getTable());
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
        switch ($item->getType()) {
            case static::$itemtype_2:
                if (!$withtemplate) {
                    if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            $count = static::countForItem($item);
                            return static::createTabEntry(static::getTypeNameForClass(), $count);
                        }
                        return static::getTypeNameForClass();
                    }
                }
                break;

            default:
                if (!$withtemplate) {
                    if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            $count = static::countForItem($item);
                            if($item::getType() == PluginDlteamsDataCatalog::class && $item->fields["plugin_dlteams_datacatalogs_id"])
                                $count+= static::countForItem($item, true);
                            return static::createTabEntry(static::getTypeName(2), $count);
                        }
                        return static::getTypeName(2);
                    }
                }
                break;
        }

        return '';
    }

    // comptage du nombre de liaison entre les 2 objets dans la table de l'objet courant
    static function countForItem(CommonDBTM $item, $showcatalogparent_pm = false)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable(static::getTable(), ['items_id' => $showcatalogparent_pm?$item->fields['plugin_dlteams_datacatalogs_id']:$item->getID(), 'itemtype' => $item->getType()]);
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

    public function update(array $input, $history = 1, $options = [])
    {
        if(isset($input["plugin_dlteams_protectivemeasures_id"]))
            $input["protectivemeasures_id"] = $input["plugin_dlteams_protectivemeasures_id"];

/*        highlight_string("<?php\n\$data =\n" . var_export($input, true) . ";\n?>");*/
//        die();

        parent::update($input, $history, $options);
        return true;
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
                if($item::getType() == PluginDlteamsDataCatalog::class && $item->fields["plugin_dlteams_datacatalogs_id"])
                    self::showForItem($item, 0, true);
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

    static function showForItem(CommonDBTM $item, $withtemplate = 0, $showcatalogparent_pm = false)
    {
        $id = $item->fields['id'];
        $canedit = $item->can($id, UPDATE); // canedit booleen = true
        $rand = mt_rand(1, mt_getrandmax());
        global $DB;

        $iterator = static::getRequest($item, $showcatalogparent_pm);
        $number = count($iterator);
        $items_list = [];

        $used = [];

        //while ($data = $iterator->next()) {
        foreach ($iterator as $id => $data) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }


        if ($canedit && !$showcatalogparent_pm) {
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
             action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . $item->getType() . "' />";
            echo "<input type='hidden' name='itemtype' value='" . static::$itemtype_2 . "' />";
            echo "<input type='hidden' name='datacatalogs_idx[]' value='" . $item->getID() . "' />";
            echo "<input type='hidden' name='entities_id' value='" . $item->fields['entities_id'] . "' />";
        }
//
        $title = static::$title;
        if($showcatalogparent_pm === true)
            $title = "Mesures effectives (appliquées au catalogue parent)";
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr class='tab_bg_2'><th colspan='3'>" . $title .
                "</i></th>";
            echo "</tr>";

        if ($canedit && !$showcatalogparent_pm) {
            echo "<tr class='tab_bg_1'>";
            echo "<td class='left'>";
            echo "<div style='display: flex; flex-direction: column; gap: 10px;'>";
            echo "<span>";
            echo static::$sub_title;
            echo "</span>";

            global $CFG_GLPI;
            static::$itemtype_2::dropdown([
                'addicon' => true,
                'name' => 'protectivemeasures_id',
                'value' => "", //$responsible,
                //'entity' => $this->fields["entities_id"],
                'right' => 'all',
                'width' => "250px",
                'url' => $CFG_GLPI['root_doc'] . "/marketplace/dlteams/ajax/getDropdownValue.php",
                'used' => $used
            ]);
            echo "</div>";
            echo "</td>";


            echo "<td class='left'>";
            echo "<div style='display: flex; flex-direction: column; gap: 10px;'>";
            echo "<span>";
            echo "Catalogues liés";
            echo "</span>";

            $used = [];
            PluginDlteamsDataCatalog::dropdown([
                'addicon' => PluginDlteamsDataCatalog::canCreate(),
                'name' => 'datacatalogs_idx[]',
                'value' => [], //$responsible,
                //'entity' => $this->fields["entities_id"],
                'right' => 'all',
                'width' => "250px",
                'used' => $used,
                'multiple' => true
            ]);
            echo "</div>";
            echo "</td>";


//            echo "<td class='left'>";
//            echo "<div style='display: flex; flex-direction: column; gap: 10px;'>";
//            echo "<span>";
//            echo "Livrables et rapports";
//            echo "</span>";
//
//            $used = [];
//
//            PluginDlteamsDeliverable::dropdown([
//                'addicon' => true,
//                'name' => 'deliverables_id',
//                'value' => "", //$responsible,
//                //'entity' => $this->fields["entities_id"],
//                'right' => 'all',
//                'width' => "250px",
////                'used' => $used
//            ]);
//            echo "</div>";
//            echo "</td>";

            echo "<td class='' width='30%'>";
//            echo __("Comment");
//            echo "</td>";
//            echo "<td style='display: flex;' class='left'>";
            echo "<div style='display: flex; flex-direction: column; gap: 10px;'>";
            echo "<span style='text-align: start'>";
            echo __("Comment");
            echo "</span>";
            echo "<textarea type='text' style='width:100%;' maxlength=1000 rows='2' name='comment' class='comment'></textarea>";
            echo "<div>";
            echo "</td>";
//            echo "<td class='left'>";
//            echo "</td>";
            echo "</tr>";


//
//
//            echo "<tr class='tab_bg_1' style='display: none;' id='field-createlink'>";
//            echo "<td class='right' width='20%'>";
//            echo __("Comment");
//            echo "</td>";
//            echo "<td style='display: flex;' class='left'>";
//            echo "<textarea type='text' style='width:100%;' maxlength=1000 rows='2' name='comment' class='comment'></textarea>";
//            echo "</td>";
//            echo "<td class='left'>";
//            echo "</td>";
//            echo "</tr>";

            echo "<tr>";
            echo "<td>";
            echo "</td>";
            echo "<td colspan='2' class='left'>";
            echo "<button name='add' id='btn-createlink' class='btn btn-primary'>Ajouter</button>";
            echo "</td>";
            echo "</tr>";

        }

            echo "</table>";
        if ($canedit && !$showcatalogparent_pm) {
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
        if ($canedit && $number && !$showcatalogparent_pm) {
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


        if ($canedit && $number && !$showcatalogparent_pm) {

            $header_begin .= "<th width='10'>";
            $header_top .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
//                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            $header_end .= "</th>";
        }


//        foreach (static::$table_match_str as $column) {
//            $header_end .= "<th>" . $column["head_text"] . "</th>";
//        }

        $header_end .= "<th>" . __("Name") . "</th>";
        $header_end .= "<th>" . __("Type"). "</th>";
        $header_end .= "<th>" . __("Categorie") . "</th>";
//        $header_end .= "<th>" . __("Livrable ou rapport") . "</th>";
        $header_end .= "<th>" . __("Comment") . "</th>";
        $header_end .= "</tr>";

        echo $header_begin . $header_top . $header_end;
        //var_dump($items_list);
        foreach ($items_list as $data) {

            echo "<tr class='tab_bg_1'>";

            if ($canedit && $number && !$showcatalogparent_pm) {
                echo "<td width='10'>";

                $item_str = $item::class . "_Item";
                Html::showMassiveActionCheckBox(__CLASS__, $data['linkid']);
                echo "</td>";

                $id = $data['linkid'];
            }

/*            highlight_string("<?php\n\$data =\n" . var_export($data, true) . ";\n?>");*/
//            die();

            $protectivemeasure = new PluginDlteamsProtectiveMeasure();
            $protectivemeasure->getFromDB($data["protectivemeasures_id"]);

            $name = "<a target='_blank' href=\"" . PluginDlteamsProtectiveMeasure::getFormURLWithID($data["protectivemeasures_id"]) . "\">" . $protectivemeasure->fields["name"] . "</a>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $name . "</td>";


            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $data["typename"] . "</td>";


            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $data["namecat"] . "</td>";


            $deliverble = new PluginDlteamsDeliverable();
            $deliverble->getFromDB($data["items_id1"]);

//            $name = "<a target='_blank' href=\"" . PluginDlteamsDeliverable::getFormURLWithID($data["items_id1"]) . "\">" . $deliverble->fields["name"] . "</a>";
//
//            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
//            echo ">" . $name . "</td>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $data["comment"] . "</td>";


            echo "</tr>";
        }

        echo "</table>";

        Html::closeForm();

        echo "
                <script>
                    $(document).ready(function(e) {
                        var window.eventBinded = false;
                
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
        global $DB;
/*        highlight_string("<?php\n\$data =\n" . var_export($this->fields, true) . ";\n?>");*/
//        die();
//        purge relations
       $datacatalog_item = new PluginDlteamsDataCatalog_Item();

        $criteria = [
            "itemtype" => PluginDlteamsProtectiveMeasure::class,
            "items_id" => $this->fields["protectivemeasures_id"],
//            "itemtype1" => PluginDlteamsDeliverable::class,
//            "items_id1" => $this->fields["items_id1"],
            "datacatalogs_id" => $this->fields["items_id"],
            "comment" => $this->fields["comment"]
        ];

        $DB->delete($datacatalog_item->getTable(), $criteria);
//        $relation_item->deleteByCriteria($criteria);

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
            "comment" => isset($this->oldvalues["comment"])?$this->oldvalues["comment"]:$this->fields["comment"]
        ];

        if(isset($this->oldvalues["protectivemeasures_id"]))
            $criteria["items_id"] = $this->oldvalues["protectivemeasures_id"];


        if($relation_item->deleteByCriteria($criteria)) {
            if(isset($_POST["plugin_dlteams_protectivemeasures_id"]))
                $criteria["items_id"] = $_POST["plugin_dlteams_protectivemeasures_id"];
            $relation_item->add([
                ...$criteria,
                "comment" => $this->fields["comment"]
            ]);
        }
    }




    function rawSearchOptions()
    {

        $tab[] = [
            'id' => '45',
            'table' => PluginDlteamsProtectiveMeasure::getTable(),
            'field' => 'protectivemeasures_id',
            'datatype' => 'dropdown',
            'name' => __("Mesure de protection"),
            'forcegroupby' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '48',
            'table' => PluginDlteamsProtectiveMeasure_Item::getTable(),
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
//        $forbidden[] = 'MassiveAction:update';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }

    public static function getRequest(CommonDBTM $item, $showcatalogparent_pm = false)
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
                $table_item_name . '.' . 'items_id' => $showcatalogparent_pm?$item->fields['plugin_dlteams_datacatalogs_id']:$item->fields['id'],
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
