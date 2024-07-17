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

class PluginDlteamsPolicieForm_Item extends CommonDropdown
{
    static public $itemtype_2 = 'PluginDlteamsPolicieForm';
    static public $itemtype_1;
    public static $items_id_1;
    public static $title;
    public static $sub_title;
    public static $table_match_str = [];

    public function __construct()
    {
        static::$itemtype_1 = str_replace("_Item", "", __CLASS__); // $itemtype_1 ---> PluginDlteamsPolicieForm
        static::$items_id_1 = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", __CLASS__))) . "s_id";
        static::$title = __("Jeux de données et types de documents contenus dans ce catalogue", 'dlteams');
        static::$sub_title = __("Ajoutez des jeux de données et commentez si besoin ", 'dlteams');
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
        return __("Jeux de données", 'dlteams');
    }

    static function getTypeNameForClass($nb = 0)
    {
        return __("Eléments reliés", 'dlteams');
    }


    // affichage de l'onglet et de son nom
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case 'PluginDlteamsPolicieForm':
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
//            'ORDER' => self::getTable() . '.id DESC',
            'ORDER' => [$temp->getTable().'.name ASC', self::getTable().'.itemtype ASC'],
        ]);

/*        highlight_string("<?php\n\$data =\n" . var_export(iterator_to_array($items), true) . ";\n?>");*/
//        die();
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
            case Document::class:
                self::showForDocument($item);
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
    public static function showItems(PluginDlteamsPolicieForm $object_item)
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
        $key = array_search("PluginDlteamsPolicieForm", $types);
        unset($types[$key]);
        $rand = mt_rand();

        if ($canedit) {
            echo "<form name='audititem_form$rand' id='audititem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
            echo "<input type='hidden' name='" . static::$items_id_1 . "' value='$instID'>";
            echo "<input type='hidden' name='itemtype1' value='" . str_replace("_Item", "", __CLASS__) . "'>";
            echo "<input type='hidden' name='items_id1' value='" . $instID . "'>";
            echo "<input type='hidden' name='link_element' value='" . true . "'>";

            echo "<table class='tab_cadre_fixe'>";
            $title = "Related objects";
            $entitled = "Indicate the objects related to this element";
            echo "<tr class='tab_bg_2'><th colspan='3'>" . __(static::$title, 'dlteams') .
                "</th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'><td class='right' style='text-wrap: nowrap;' width='40%'>" . __(static::$sub_title, 'dlteams');
            echo "</td><td width='40%' class='left'>";
            $types = PluginDlteamsItemType::getTypes();
            $key = array_search(PluginDlteamsPolicieForm::class, $types);
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
            echo "<input for='audititem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
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

    static function showForDocument(CommonDBTM $item){
        $id = $item->fields['id'];
        $canedit = $item->can($id, UPDATE); // canedit booleen = true
        $rand = mt_rand(1, mt_getrandmax());
        global $DB;

        $iterator = [];
//        $iterator = static::getRequest($item);
//        $number = count($iterator);
        $items_list = [];
        $used = [];

        //while ($data = $iterator->next()) {
//        foreach ($iterator as $id => $data) {
//            $items_list[$data['linkid']] = $data;
//            $used[$data['id']] = $data['id'];
//        }

        if ($canedit) {
            echo "<form name='audititem_form$rand' id='audititem_form$rand' method='post'
             action='" . Toolbox::getItemTypeFormURL(PluginDlteamsPolicieForm::class) . "'>";
            echo "<input type='hidden' name='update_document_modele' value='" . $item->getType() . "' />";
//            echo "<input type='hidden' name='itemtype' value='" . static::$itemtype_2 . "' />";
            echo "<input type='hidden' name='documents_id' value='" . $item->getID() . "' />";
//            echo "<input type='hidden' name='entities_id' value='" . $item->fields['entities_id'] . "' />";
//
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr class='tab_bg_2'><th colspan='3'>" . "Ce document est modèle de " .
                "</th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td class='right' style='text-wrap: nowrap;' width='20%'>";
            echo "Document modèle";
            echo "</td>";

            echo "<td style='display: flex;' class='left'>";


            PluginDlteamsPolicieForm::dropdown([
                'addicon' => true,
                'name' => 'policieforms_id',
                'value' => $item->fields["policieforms_id"], //$responsible,
                // 'entity' => $this->fields["entities_id"],
                'right' => 'all',
                'width' => "250px",
                'used' => $used
            ]);


            echo "</td>";
            echo "<td class='left'>";
            echo "</td>";
            echo "</tr>";


            echo "<tr class='tab_bg_2'><th colspan='3'>" . "Fichiers liés" .
                "</th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td class='right' style='text-wrap: nowrap;' width='20%'>";
            echo "Fichier html";
            echo "</td>";

            echo "<td style='display: flex;' class='left'>";


            PluginDlteamsDeliverable::dropdown([
                'addicon' => true,
                'name' => 'deliverables_id',
                'value' => $item->fields["deliverables_id"], //$responsible,
                // 'entity' => $this->fields["entities_id"],
                'right' => 'all',
                'width' => "250px",
                'used' => $used
            ]);


            echo "</table>";

            $item->showFormButtons([
                "candel" => false
            ]);

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
            echo "<form name='audititem_form$rand' id='audititem_form$rand' method='post'
             action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . $item->getType() . "' />";
            echo "<input type='hidden' name='itemtype' value='" . static::$itemtype_2 . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $item->getID() . "' />";
            echo "<input type='hidden' name='entities_id' value='" . $item->fields['entities_id'] . "' />";
//
            echo "<table class='tab_cadre_fixe'>";

            switch ($item::getType()){
                case PluginDlteamsDataCatalog::class:
                    static::$title = "Ce jeu de données se trouve classé dans les catalogues suivants";
                    break;
                case Document::class:
                    static::$title = "Ce jeu de données se trouve dans les documents suivants";
                    break;
            }
            echo "<tr class='tab_bg_2'><th colspan='3'>" . static::$title .
                "</th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td class='right' style='text-wrap: nowrap;' width='20%'>";
            echo static::$sub_title;
            echo "</td>";

            echo "<td style='display: flex;' class='left'>";


            static::$itemtype_2::dropdown([
                'addicon' => true,
                'name' => 'items_id',
                'value' => "", //$responsible,
                // 'entity' => $this->fields["entities_id"],
                'right' => 'all',
                'width' => "250px",
                'used' => $used
            ]);
//            $field_id = Html::cleanId("dropdown_" . 'items_' . mt_rand());
//            $item_link = getItemForItemtype(static::$itemtype_2);
//
//            echo '<div class="btn btn-outline-secondary"
//                           title="' . __s('Add') . '" data-bs-toggle="modal" data-bs-target="#add_' . $field_id . '">'
//                .Ajax::createIframeModalWindow('add_' . $field_id, $item_link->getFormURL(), ['display' => false])
//                ."<span data-bs-toggle='tooltip'>
//              <i class='fa-fw ti ti-plus'></i>
//              <span class='sr-only'>" . __s('Add') . "</span>
//                </span>"
//                .'</div>';


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
            echo "<button name='link_element' style='display: none;' id='btn-createlink' class='btn btn-primary'>Relier cet élément</button>";
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


        if ($iterator) {

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

            $header_end.="<th>"."Traitements"."</th>";
            $header_end .= "</tr>";

            echo $header_begin . $header_top . $header_end;
            //var_dump($items_list);
            foreach ($items_list as $data) {
                echo "<tr class='tab_bg_1'>";

                if ($canedit && $number) {
                    echo "<td width='10'>";

                    $item_str = $item::class . "_Item";
//                        Computer_Item::class;
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

                echo "<td class='left relations_list" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                echo ">";

                $policieform_id = $data["policieforms_id"];
                $policieform = new PluginDlteamsPolicieForm();
                $policieform->getFromDB($policieform_id);


                $users_data = PluginDlteamsRecord_Item::getRequest($policieform);
/*                highlight_string("<?php\n\$data =\n" . var_export($users_data, true) . ";\n?>");*/
//                die();
                echo "<ul>";
                foreach ($users_data as $li_row) {
                    echo "<li>";
                    $linkid = $li_row["linkid"];
                    echo $li_row["name"] . "&nbsp; <i class='fa fa-close delete-record-pf' data-linkid='$linkid' style='color: red; cursor: pointer'></i>";
                    echo "</li>";
                }
                echo "</ul>";

                echo "<span class='pointer add_record' data-policieform-id='$policieform_id' data-datacatalog-id='$id'>
                    <i class='fa fa-plus' title='" . __('Attribuer un traitement') . "'></i>
                    <span class='sr-only'>" . __('Attribuer un traitement à ce jeu de données') . "</span>
                 </span>";
                echo "</td>";

                echo "</tr>";
            }

            echo "</table>";

            echo "
            <script>
                $('.add_record').click(function () {
             glpi_ajax_dialog({
                dialogclass: 'modal-md',
                bs_focus: false,
                params: {
                  policieforms_id: $(this).attr('data-policieform-id'),
                  datacatalogs_id: $(this).attr('data-datacatalog-id')
                },
                url: '/marketplace/dlteams/ajax/policiform_record.php',
                title: i18n.textdomain('dlteams').__('Action', 'dlteams'),
                close: function () {

                },
                fail: function () {
                    displayAjaxMessageAfterRedirect();
                }
                
             });
        });
                
                
                
                 $('.delete-record-pf').click(function(e) {
          e.preventDefault();
           $.ajax({
                        url: '/marketplace/dlteams/ajax/policiform_record.php',
                        type: 'POST',
                        data: {
                            delete: true,
                            linkid: $(this).attr('data-linkid'),
                        },
                        success: function (data) {
                            location.reload();
                        },
                        
            });
        });
                </script>
            ";

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
/*        highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//        die();
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
        $relation_item->add([
            ...$criteria,
            "comment" => $this->fields["comment"]
        ]);
    }

    public function update(array $input, $history = 1, $options = [])
    {
        global $DB;
        $pi = new PluginDlteamsPolicieForm_Item();
        $pi->getFromDB($input["id"]);

//        get relation
        $record_item = new PluginDlteamsRecord_Item();
        $record_item->getFromDBByCrit([
            "itemtype" => PluginDlteamsPolicieForm::class,
            "items_id" => $pi->fields["policieforms_id"],
            "records_id" => $pi->fields["items_id"],
            "comment" => $pi->fields["comment"],
        ]);



        if (isset($input["comment"])) {
            $DB->update(
                $pi->getTable(),
                [
                    "comment" => $input["comment"],
                ],
                ['id' => $input["id"]]
            );
//        update record_item relation
            $DB->update(
                $record_item->getTable(),
                [
                    "comment" => $input["comment"],
                ],
                ['id' => $record_item->fields["id"]]
            );
            Session::addMessageAfterRedirect("Relation ".PluginDlteamsRecord::getTypeName()." mis a jour avec succès");
        }

        if(isset($input["mandatory"])){
            $DB->update(
                $record_item->getTable(),
                [
                    "mandatory" => $input["mandatory"],
                ],
                ['id' => $record_item->fields["id"]]
            );

            Session::addMessageAfterRedirect("Relation ".PluginDlteamsRecord::getTypeName()." mis a jour avec succès");
        }

        return true;

    }

    function rawSearchOptions()
    {
        $tab[] = [
            'id' => '43',
            'table' => static::getTable(),
            'field' => 'mandatory',
            'datatype' => 'bool',
            'name' => __("Obligatoire"),
            'forcegroupby' => true,
            'massiveaction' => true,
        ];

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
        $forbidden[] = 'MassiveAction:purge_but_item_linked';
        $forbidden[] = 'MassiveAction:add_note';
        return $forbidden;
    }

    public static function getRequest(CommonDBTM $item)
    {
        $table_name = static::$itemtype_2::getTable(); // si $item = DataCatalog, $table_name contiendra data_carriers
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
