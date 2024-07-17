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

class PluginDlteamsTicketTask_Plannification extends CommonDBTM
{
    static public $itemtype_2 = 'TicketTask';
    static public $itemtype_1;
    public static $items_id_1;
    public static $title;
    public static $sub_title;
    public static $table_match_str = [];

    public function __construct()
    {
        static::$itemtype_1 = str_replace("_Item", "", __CLASS__); // $itemtype_1 ---> TicketTask
        static::$items_id_1 = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", __CLASS__))) . "s_id";
        static::$title = __("Evenement en relation avec cette tâche", 'dlteams');
        static::$sub_title = __("Choisir un événement à lier à cette tâche", 'dlteams');
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
        self::forceTable(TicketTask::getTable());
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
        return _n("Planification", "Planifications", $nb, 'dlteams');
    }


    static function getTypeNameForClass($nb = 0)
    {
        return __("Eléments rattachés", 'dlteams');
    }

    public static function getFormURLWithID($id = 0, $full = true)
    {
        $itemtype = TicketTask::class;
        $itemtype_item = new PluginDlteamsTicketTask_Plannification();
        $itemtype_item->getFromDB($id);
        $link = $itemtype::getFormURL($full);
        $link .= (strpos($link, '?') ? '&' : '?') . 'id=' . $itemtype_item->fields["records_id"];
        return $link;
    }

    // affichage de l'onglet et de son nom
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case 'TicketTask':
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
                    if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            return static::createTabEntry(static::getTypeName(2), count(static::getRequest($item)));
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
        //var_dump ($link_table, static::getTable(), $temp->getTable());
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
            case static::$itemtype_2:
                self::showItems($item);
                break;
            default:
                self::showForItem($item);
                break;
        }
    }

    // Liste des tâches
    public static function showItems(TicketTask $object_item)
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
		// Enlève le choix de L'objet LegalBasi dans la dropdown qui affiche la liste des objets
        $key = array_search("TicketTask", $types);
        unset($types[$key]);
        /*        highlight_string("<?php\n\$data =\n" . var_export($types, true) . ";\n?>");*/
        $rand = mt_rand();

        if ($canedit) {
            echo "<form name='recorditem_form$rand' id='recorditem_form$rand' method='post'
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
            echo "<input for='recorditem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
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
                echo "<td>" . htmlspecialchars_decode($row['comment'] ?? "") . "</td>";
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

    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        switch ($ma->getAction()) {
            case 'assign_as_record_acteur':
                echo "<tr class='tab_bg_1'><td width='24%' style='display: flex; align-items: center; gap: 5px'>";
                TicketTask::dropdown([
                    "name" => "records_id",
                    "addicon" => TicketTask::canCreate(),
                    "width" => "150px",
                    "used" => [],
                ]);
                echo "</td>";

                echo '<br /><br />' . Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                return true;
                break;
        }
        return parent::showMassiveActionsSubForm($ma); // TODO: Change the autogenerated stub
    }

    public static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        global $DB;
        $DB->beginTransaction();
        switch ($ma->getAction()) {
            case 'assign_as_record_acteur':
                foreach ($ids as $id) {
                    $user_item = new PluginDlteamsUser_Item();
                    $array1 = [
                        "users_id" => $id,
                        "itemtype" => TicketTask::class,
                        "items_id" => $ma->POST["records_id"],
                    ];
                    $user_item->add($array1);

                    $record_item = new PluginDlteamsTicketTask_Plannification();
                    $array_2 = [
                        "records_id" => $ma->POST["records_id"],
                        "itemtype" => User::class,
                        "items_id" => $id,
                        "users_id_actor" => $id
                    ];
                    if (!$record_item->getFromDBByCrit($array_2)) {

                        $record_item->add($array_2);
                        Session::addMessageAfterRedirect("Opération éffectué avec succès");
                        $DB->commit();
                    } else {
                        Session::addMessageAfterRedirect("Cet utilisateur est déjà acteur de ce traitement", true, ERROR);
                        $DB->rollback();
                    }
                }
                break;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids); // TODO: Change the autogenerated stub
    }

    public static function getTable($classname = null)
    {
        return TicketTask::getTable($classname); // TODO: Change the autogenerated stub
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

//        if ($canedit) {
//            echo "<form name='recorditem_form$rand' id='recorditem_form$rand' method='post'
//             action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
//            echo "<input type='hidden' name='itemtype1' value='" . $item->getType() . "' />";
//            echo "<input type='hidden' name='itemtype' value='" . static::$itemtype_2 . "' />";
//            echo "<input type='hidden' name='items_id1' value='" . $item->getID() . "' />";
//            echo "<input type='hidden' name='entities_id' value='" . $item->fields['entities_id'] . "' />";
////
//            echo "<table class='tab_cadre_fixe'>";
//
//            echo "<tr class='tab_bg_2'><th colspan='3'>" . static::$title .
//                "</th>";
//            echo "</tr>";
//
//            echo "<tr class='tab_bg_1'>";
//            echo "<td class='right' style='text-wrap: nowrap;' width='20%'>";
//            echo static::$sub_title;
//            echo "</td>";
//            echo "<td style='display: flex;' class='left'>";
//
////            Dropdown::showFromArray('test', [
////                ['name' => 'element1'],
////                ['name' => 'element2'],
////            ], ['width'   => '30%',
////                'rand'    => $rand,
////                'display' => false]);
//            global $CFG_GLPI;
//            Ticket::dropdown([
//                'addicon' => true,
//                'name' => 'items_id',
//                'used' => $used,
//                'value' => "", //$responsible,
//                'right' => 'all',
//                'width' => "250px",
//                'url' => $CFG_GLPI['root_doc'] . "/marketplace/dlteams/ajax/getDropdownValue.php"
//            ]);
//
//            echo "</td>";
//            echo "<td class='left'>";
//            echo "</td>";
//            echo "</tr>";
//
//            echo "<tr class='tab_bg_1' style='display: none;' id='field-createlink'>";
//            echo "<td class='right' width='20%'>";
//            echo __("Comment");
//            echo "</td>";
//            echo "<td style='display: flex;' class='left'>";
//            echo "<div>";
//            echo "<textarea type='text' style='width:100%;' maxlength=1000 rows='2' name='comment' class='comment'></textarea>";
//            if ($item::getType() == PluginDlteamsDataCatalog::class) {
//                echo "<div style='display: flex; align-items: center; gap: 3px;'>";
//                Html::showCheckbox([
//                    'name' => 'apply_on_childs',
//                    'checked' => true
//                ]);
//
//                echo "<label>les catalogues enfants héritent automatiquement</label>";
//                echo "</div>";
//            }
//
//            echo "</div>";
//            echo "</td>";
//            echo "<td class='left'>";
//            echo "</td>";
//            echo "</tr>";
//
//            echo "<tr>";
//            echo "<td>";
//            echo "</td>";
//            echo "<td colspan='2' class='left'>";
//            echo "<button name='link_element' style='display: none;' id='btn-createlink' class='btn btn-primary'>Relier cet élément</button>";
//            echo "</td>";
//            echo "</tr>";
//
//
//            echo "</table>";
//            Html::closeForm();
//        }
//
//        echo "<script>
//                $(document).ready(function(e){
//
//                $('select[name=items_id]').on('change', function () {
//                    if($(this).val() != '0'){
//                        document.getElementById('btn-createlink').style.display = 'block';
//                        document.getElementById('field-createlink').style.display = 'table-row';
//
//                        $.ajax({
//                                url: '/marketplace/dlteams/ajax/get_object_specific_field.php',
//                                type: 'POST',
//                                data: {
//                                    id: $(this).val(),
//                                    object: '" . static::$itemtype_2 . "',
//                                    field: 'content'
//                                },
//                                success: function (data) {
//                                    // Handle the returned data here
////                                    let comm_field = $('textarea[name=comment]');
////                                    comm_field.val(data);
////                                    comm_field.val(comm_field.val().replace(/^\s+/, ''));
//                                }
//                            });
//
//
//                    }
//                    else{
//                        document.getElementById('btn-createlink').style.display = 'none';
//                        document.getElementById('field-createlink').style.display = 'none';
//                    }
//
//                    });
//                });
//        </script>";


        $massiveaction_processor = __CLASS__;

//        if (in_array($item::getType(), $itemtype1_list_in_records) || in_array($item::getType(), $exceptions_itemtype_in_records)) {
//            $massiveaction_processor = $item::getType() . "_Item";
//            static::$items_id_1 = strtolower(str_replace("PluginDlteams", "", $item::getType())) . "s_id";
//        }

//        if ($iterator) {
        echo "<div class='spaced'>";
        if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . $massiveaction_processor . $rand);
            $massive_action_params = [
                'container' => 'mass' . $massiveaction_processor . $rand,
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
            $header_top .= Html::getCheckAllAsCheckbox('mass' . $massiveaction_processor . $rand);
//                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            $header_end .= "</th>";
        }

//        foreach (static::$table_match_str as $column) {
            $header_end .= "<th>" . __("Id") . "</th>";
            $header_end .= "<th>" . __("Content") . "</th>";
            $header_end .= "<th>" . __("Gabarit") . "</th>";
            $header_end .= "<th>" . __("Statut") . "</th>";
            $header_end .= "<th>" . __("Num") . "</th>";
            $header_end .= "<th>" . __("Acteur") . "</th>";
            $header_end .= "<th>" . __("Groupe") . "</th>";
            $header_end .= "<th>" . __("Echéance") . "</th>";
            $header_end .= "<th>" . __("Durée") . "</th>";
//            $header_end .= "<th>" . __("Planification") . "</th>";
            $header_end .= "<th>" . __("Date Maj") . "</th>";
//        }
        $header_end .= "</tr>";

        echo $header_begin . $header_top . $header_end;

        foreach ($items_list as $data) {
            echo "<tr class='tab_bg_1'>";
            if ($canedit && $number) {
                echo "<td width='10'>";
//                        Computer_Item::class;
                Html::showMassiveActionCheckBox($massiveaction_processor, $data['linkid']);
                echo "</td>";
                $id = $data['linkid'];
            }

            $name = "<a target='_blank' href=\"" . TicketTask::getFormURLWithID($data["id"]) . "\">" . sprintf("%s", isset($data["linkid"])?$data["linkid"]:"") . "</a>";

            echo "<td style='width: 10%' class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">";
            $linkid = $data["linkid"];
            echo "<span style='border: none; background-color: transparent; cursor: pointer; margin-left: 10px' class='btn-updatetask' data-row-id='$linkid'>";
            echo "<i class='fas fa-edit me-2'></i>";
            echo "</span>";
            echo $name . "</td>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . \Glpi\RichText\RichText::getSafeHtml($data["content"], false) . "</td>";


            echo "
            <script>
                $(document).ready(function () {
//                                $('#opentask".$data["linkid"]."').qtip({
//                                    content: ".html_entity_decode($data["content"]).",
//                                    style: {classes: 'qtip-shadow qtip-bootstrap'}
//                                });


                });
            </script>
            ";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $data["templatename"]??"--" . "</td>";
//            echo "</tr>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . Planning::getStatusIcon($data['state']) . "</td>";
//            echo "</tr>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $data["timeline_position"] . "</td>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . sprintf("%s %s", $data["user_firstname"], $data["user_realaname"]) . "</td>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $data["group_completename"]??"--" . "</td>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . date('d-m-Y H:i', strtotime($data["date"]??"")). "</td>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . PluginDlteamsTicketTask_Planning::convertirTemps($data['actiontime']). "</td>";
//            echo "</tr>";

//            colonne pllnifications
//            $tache_plannification_query = [
//              "FROM" => TicketTask::getTable(),
//                "WHERE" => [
//                    "tickettasks_id" => $data["linkid"]
//                ]
//            ];
//            $tache_plannification_itertor = $DB->request($tache_plannification_query);
//            $outstr = "";
//            foreach ($tache_plannification_itertor as $tache_planifictaion){
//                $outstr.= sprintf("%s => %s <br/>", $tache_planifictaion["begin"]?date('d-m-Y H:i', strtotime($tache_planifictaion["begin"])):"--", $tache_planifictaion["end"]?date('d-m-Y H:i', strtotime($tache_planifictaion["end"]??"")):"--");
//            }
//            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
//            echo ">" . $outstr. "</td>";


            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . date('d-m-Y H:i', strtotime($data["date_mod"]??"")). "</td>";
            echo "</tr>";

        }
        echo "</table>";
        Html::closeForm();

        $script = "<script>
    $(document).ready(function () {
        $('.btn-updatetask').on('click', function () {
            
            var link_id = $(this).attr('data-row-id');


            glpi_ajax_dialog({
                dialogclass: 'modal-xl',
                bs_focus: false,
                url: '/marketplace/dlteams/ajax/tickettask_plannif.php?itemtype=TicketTask&items_id='+link_id+'&edittickettask=true',
                params: {";

        $script .= "linkid: link_id
                },
                title: i18n.textdomain('dlteams').__('Action', 'dlteams'),
                close: function () {

                },
                fail: function () {
                    displayAjaxMessageAfterRedirect();
                }
            });});});</script>";

        echo $script;

        echo "</div>";
//        }

    }

    public function post_purgeItem()
    {
        $exception_list = PluginDlteamsUtils::itemtypeExceptionList();
        if (in_array($this->fields["itemtype"], $exception_list)) {
            $relation_item_str = "Item_" . $this->fields["itemtype"];
        } else
            $relation_item_str = $this->fields["itemtype"] . "_Item";

//        purge relations
        if (!class_exists($relation_item_str))
            $relation_item_str = "PluginDlteams" . $relation_item_str;

        if ($relation_item_str == Document_Item::class)
            $relation_item_str = PluginDlteamsDocument_Item::class;


        $relation_item = new $relation_item_str();

        $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

        $criteria = [
            "itemtype" => static::$itemtype_2,
            "items_id" => $this->fields[static::$items_id_1],
            $relation_column_id => $this->fields["items_id"],
//            "comment" => $this->fields["comment"]
        ];
        global $DB;
        if ($DB->fieldExists($relation_item->getTable(), 'comment')) {
            $criteria["comment"] = $this->fields["comment"];
        }

        $relation_item->deleteByCriteria($criteria);
    }


    public function post_updateItem($history = 1)
    {
        $exception_list = PluginDlteamsUtils::itemtypeExceptionList();
        if (in_array($this->fields["itemtype"], $exception_list)) {
            $relation_item_str = "Item_" . $this->fields["itemtype"];
        } else
            $relation_item_str = $this->fields["itemtype"] . "_Item";

        if (!class_exists($relation_item_str))
            $relation_item_str = "PluginDlteams" . $relation_item_str;

        if ($relation_item_str == Document_Item::class)
            $relation_item_str = PluginDlteamsDocument_Item::class;

        $relation_item = new $relation_item_str();
        $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

        $criteria = [
            "itemtype" => static::$itemtype_2,
            "items_id" => $this->fields[static::$items_id_1],
            $relation_column_id => $this->fields["items_id"],
//            "comment" => $this->oldvalues["comment"]
        ];

        global $DB;
        if ($DB->fieldExists($relation_item->getTable(), 'comment')) {
            $criteria["comment"] = addslashes($this->fields["comment"]);
        }

        $relation_item->deleteByCriteria($criteria);

        global $DB;
        if (isset($this->fields["plugin_dlteams_storagetypes_id"]) && $DB->fieldExists($relation_item->getTable(), 'plugin_dlteams_storagetypes_id'))
            $criteria["plugin_dlteams_storageendactions_id"] = $this->fields["plugin_dlteams_storageendactions_id"];

        if (isset($this->fields["plugin_dlteams_storagetypes_id"]) && $DB->fieldExists($relation_item->getTable(), 'plugin_dlteams_storagetypes_id'))
            $criteria["plugin_dlteams_storagetypes_id"] = $this->fields["plugin_dlteams_storagetypes_id"];

        $criteria2 = [
            ...$criteria,
//            "comment" => $this->fields["comment"]
        ];

        if ($DB->fieldExists($relation_item->getTable(), 'comment')) {
            $criteria2["comment"] = addslashes($this->fields["comment"]);
        }
        $relation_item->add($criteria2);

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
        $forbidden[] = 'Document_Item:remove';
        $forbidden[] = 'Document_Item:add';
        return $forbidden;
    }

    public static function getRequest(CommonDBTM $item)
    {
//        $itemtype1_list_in_records = [
//            PluginDlteamsProcessedData::class,
//        ];
//
//        $exceptions_itemtype_in_records = [
//            PluginDlteamsConcernedPerson::class
//        ];

        global $DB;
//        if (in_array($item::getType(), $itemtype1_list_in_records) || in_array($item::getType(), $exceptions_itemtype_in_records)) {
//            $table_item_name = getTableForItemType($item::getType() . "_Item");
//            $table_name = $item::getTable(); // si $item = DataCatalog, $table_name contiendra data_catalogs
//            $columnid_name = strtolower(str_replace("PluginDlteams", "", $item::getType())) . "s_id"; // $columnid_name contiendra users_id si $item = User
//        } else {
//            $table_item_name = getTableForItemType(static::$itemtype_2 . "_Item");
//            $table_name = static::$itemtype_2::getTable(); // si $item = DataCatalog, $table_name contiendra data_catalogs
//            $columnid_name = strtolower(str_replace("PluginDlteams", "", static::$itemtype_2::getType())) . "s_id"; // $columnid_name contiendra users_id si $item = User
//        }



        $query = [
            'SELECT' => [
                TicketTask::getTable() . '.id AS linkid',
                Ticket::getTable() . '.id AS id',
                TicketTask::getTable() . '.content AS content',
                TicketTask::getTable() . '.*',
                Ticket::getTable() . '.name AS name',
                TaskTemplate::getTable() . '.name AS templatename',
                User::getTable().'.firstname as user_firstname',
                User::getTable().'.realname as user_realaname',
                Group::getTable().'.completename as group_completename',
            ],
            'FROM' => TicketTask::getTable(),
            'LEFT JOIN' => [
                Ticket::getTable() => [
                    'ON' => [
                        TicketTask::getTable() => "tickets_id",
                        Ticket::getTable() => 'id'
                    ]
                ],
                TaskTemplate::getTable() => [
                    'ON' => [
                        TicketTask::getTable() => "tasktemplates_id",
                        TaskTemplate::getTable() => 'id'
                    ]
                ],
                User::getTable() => [
                    'ON' => [
                        TicketTask::getTable() => "users_id_tech",
                        User::getTable() => 'id'
                    ]
                ],
                Group::getTable() => [
                    'ON' => [
                        TicketTask::getTable() => "groups_id_tech",
                        Group::getTable() => 'id'
                    ]
                ],
            ],
            'WHERE' => [
                TicketTask::getTable().'.tickets_id' => $item->fields["id"],
                TicketTask::getTable().'.tickettasks_id' => ['>', 1]
            ],
            'ORDER'  => [
                TicketTask::getTable().'.date_creation ASC',
//                'id DESC'
            ],
        ];

//        if ($table_name == TicketTask::getTable()){
//            $query["SELECT"][] = $table_name . '.number AS number';
//            $query["ORDERBY"][] = $table_name . '.number ASC';
//        }


//        if (in_array($item::getType(), $itemtype1_list_in_records) || in_array($item::getType(), $exceptions_itemtype_in_records)) {
//            $query["WHERE"] = [
//                $table_item_name . '.itemtype' => ['LIKE', static::$itemtype_1],
//                $table_item_name . '.' . $columnid_name => $item->fields['id'],
//            ];
//        } else {
//            $query["WHERE"] = [
//                $table_item_name . '.itemtype' => ['LIKE', $item::getType()],
//                $table_item_name . '.' . 'items_id' => $item->fields['id'],
//            ];
//        }
//
//        if ($DB->fieldExists($table_item_name, 'comment')) {
//            $query['SELECT'][] = $table_item_name . '.comment AS comment';
//        }
//
//        if ($DB->fieldExists($table_name, 'content')) {
//            $query['SELECT'][] = $table_name . '.content AS content';
//        }

        $iterator = $DB->request($query);
        $temp = [];

        foreach ($iterator as $id => $data) {
            if ($data["name"]) {
//                $item_object = null;
//                $item_str = $data["itemtype"];
//                $item_object = new $item_str();
//                $item_object->getFromDB($data["items_id"]);
//                if (isset($item_object->fields["entities_id"])) {
                    array_push($temp, $data);
//                }
            }
        }
        return $temp;
    }
}