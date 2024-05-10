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

class PluginDlteamsElementsRGPD extends CommonDBChild
{

    public static $column1_id = '12';
    private $objet;

    public function __construct()
    {
//        var_dump($this);
//        static::forceTable('glpi_plugin_dlteams_users_items');
        parent::__construct();
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

// Objets faisant l'objet de l'ajout de l'onglet "éléments DLTeams"
    /*	$objects = [
            'Computer',
            'Networkequipment',
            'Peripheral',
            'Supplier',
            'Contact',
            'Contract',
            'Document',
            'Appliance',
            'Database',
            'User',
            'Group',
        ];
       foreach ($fields_exports as list($object, $fields_export)) {
            $file_pointer = "/var/www/dlteams_app/marketplace/dlteams/install/datas/" . $table . ".dat";
            $query = "LOAD DATA INFILE '".$file_pointer."' INTO TABLE ".$table." FIELDS TERMINATED BY '\t' ($fields_export)";
            $DB->queryOrDie($query, $DB->error());
            $query = "SELECT COUNT(*) FROM $table WHERE `oid` <> 0";
            $row = $DB->query($query)->fetch_assoc();
            $message .= $table . " : " . strval($row["COUNT(*)"]) . nl2br("\n") ; // strval($result); // . $table .  . "exportés";
       }
    */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $iterator = self::getRequest($item);
        $nbitem = count($iterator);
        return static::createTabEntry(__('Éléments DLTeams', 'dlteams'), $nbitem);
//        switch ($item::getType()) {
//            case Document::getType():
//                $iterator = PluginDlteamsDocumentsRGPD::getRequest($item);
//                $nbitem = count($iterator);
//                return static::createTabEntry(__('Éléments DLTeams', 'dlteams'), $nbitem);
//                break;
//            case Group::getType():
//                $iterator = PluginDlteamsGroupsRGPD::getRequest($item);
//                $nbitem = count($iterator);
//                return static::createTabEntry(__('Éléments DLTeams', 'dlteams'), $nbitem);
//                break;
//            case User::getType():
//                $iterator = PluginDlteamsUsersRGPD::getRequest($item);
//                $nbitem = count($iterator);
//                return static::createTabEntry(__('Éléments DLTeams', 'dlteams'), $nbitem);
//                break;
//            case Supplier::getType():
//                $iterator = PluginDlteamsSuppliersRGPD::getRequest($item);
//                $nbitem = count($iterator);
//                return static::createTabEntry(__('Éléments DLTeams', 'dlteams'), $nbitem);
//                break;
//        }
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $class_rgpd = "PluginDlteams" . $item::class . "sRGPD";
        if (!class_exists($class_rgpd)) {
            throw new Exception("La classe " . $class_rgpd . " n'existe pas");
            return true;
        }
        $class_rgpd::showItems($item);
        return true;
//        switch ($item::getType()) {
//            /*case Computer::getType():
//                PluginDlteamsComputersRGPD::showItems($item);
//                break;
//            case Networkequipment::getType():
//                PluginDlteamsNetworkequipementsRGPD::showItems($item);
//                break;*/
//            case Document::getType():
//                PluginDlteamsDocumentsRGPD::showItems($item);
//                break;
//            case Group::getType():
//                PluginDlteamsGroupsRGPD::showItems($item);
//                break;
//            case User::getType():
//                PluginDlteamsUsersRGPD::showItems($item);
//                break;
//            case Supplier::getType():
//                PluginDlteamsSuppliersRGPD::showItems($item);
//                break;
//        }
//        return true;
    }



//    add

    /**
     * Show items links to a document
     *
     * @param $doc Document object
     *
     * @return void
     **@since 0.84
     *
     */
    public static function showItems(CommonDBTM $item)
    {
//        static::forceTable($item->getTable());

        $type = $item->getType();
        $id = $item->fields['id'];
        if (!$item->can($id, READ)) {
            return false;
        }

        $canedit = $item::canUpdate();
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

//        new

        if ($canedit) {
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
             action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . $item->getType() . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $item->getID() . "' />";
            echo "<input type='hidden' name='entities_id' value='" . $item->fields['entities_id'] . "' />";
//
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr class='tab_bg_1'>";
            echo "<td class='right' width='20%'>";
            echo __("Objets à relier", 'dlteams');
            echo "</td>";
            echo "<td style='display: flex;' class='left'>";
            $types = PluginDlteamsItemType::objetsReliablesDlregister()[$item::class];
            Dropdown::showSelectItemFromItemtypes(['itemtypes' => $types,
                'entity_restrict' => (isset($item->fields['is_recursive']) && $item->fields['is_recursive'] ? getSonsOf('glpi_entities', $item->fields['entities_id'])
                    : $item->fields['entities_id']),
                'checkright' => true,
                'used' => $used,
                'width' => '100%',
                'itemtype_name' => 'itemtype',
                'ajax_page' => "/marketplace/dlteams/ajax/dlteamsDropdownAllItem.php"
            ]);
            echo "</td>";
            echo "<td class='left'>";
            echo "</td>";
            echo "</tr>";
//
//
            echo "<tr class='tab_bg_1'>";
            echo "<td class='right' width='20%'>";
            echo __("Comment");
            echo "</td>";
            echo "<td style='display: flex;' class='left'>";
            echo "<textarea type='text' style='width:100%' maxlength=1000 rows='3' name='comment' class='comment'></textarea>";
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

                $('select[name=itemtype]').on('change', function () {
                    if($(this).val() != '0'){
                        document.getElementById('btn-createlink').style.display = 'block';
                    }
                    else
                        document.getElementById('btn-createlink').style.display = 'none';
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


            $header_end .= "<th>" . __("Name") . "</th>";
            $header_end .= "<th>" . __("Type") . "</th>";
            if (isset($data['comment'])) {
                $header_end .= "<th>" . __("Comment") . "</th>";
            }
            $header_end .= "</tr>";

            echo $header_begin . $header_top . $header_end;
            //var_dump($items_list);
            foreach ($items_list as $data) {
                if ($data['name']) {
                    echo "<tr class='tab_bg_1'>";

                    if ($canedit && $number) {
                        echo "<td width='10'>";

                        $item_str = $item::class . "_Item";
//                        Computer_Item::class;
                        Html::showMassiveActionCheckBox($item_str, $data['linkid']);
                        echo "</td>";
                    }

                    $item_object = null;
                    $item_str = $data["itemtype"];
                    $item_object = new $item_str();
                    $item_object->getFromDB($data["items_id"]);

                    $name = "<a target='_blank' href=\"" . $data["itemtype"]::getFormURLWithID($data['items_id']) . "\">" . $item_object->fields["name"] . "</a>";
                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo ">" . $name . "</td>";

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo ">" . $data["itemtype"]::getTypeName() . "</td>";

                    if (isset($data['comment'])) {
                        echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                        echo " width='40%'>";
                        if ($data['comment']) {
                            echo $data['comment'];
                        } else {
                            echo "---";
                        }
                        echo "</td>";
                    }

                    echo "</tr>";
                }
            }


            /*if ($iterator->count() > 10) {
               echo $header_begin . $header_bottom . $header_end;
            }*/
            echo "</table>";

            Html::closeForm();

            echo "</div>";
        }

    }

    public static function getRequest(CommonDBTM $item)
    {
        $table_name = $item->getTable(); // si $item = User, $table_name contiendra glpi_users
        $columnid_name = strtolower($item->getType()) . "s_id"; // $columnid_name contiendra users_id si $item = User

//
        global $DB;
        if ($DB->tableExists(getTableForItemType($item::class . "_Item"))) {
            $table_item_name = getTableForItemType($item::class . "_Item");
        } else {
            $table_item_name = str_replace("glpi_", "", $table_name);
            $table_item_name = "glpi_plugin_dlteams_" . $table_item_name . "_items"; // si $item = User, $table_item_name contiendra glpi_plugin_dlteams_users_items
        }

        $query = [
            'SELECT' => [
                $table_item_name . '.id AS linkid',
                $table_item_name . '.itemtype AS itemtype',
                $table_item_name . '.items_id AS items_id',
                $table_name . '.id AS id',
                $table_name . '.name AS name',
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
                $table_item_name . '.itemtype' => ['LIKE', 'PluginDlteams%'],
                $table_item_name . '.' . $columnid_name => $item->fields['id'],
//                'glpi_plugin_dlteams_users_items.entities_id' => $_SESSION['glpiactive_entity']
            ],
            'ORDERBY' => ['name ASC']
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
//                $items_list[$data['linkid']] = $data;
//                $used[$data['id']] = $data['id'];
//                    unset($iterator[$id]);
                    array_push($temp, $data);
                }

            }

        }

//        die();
        return $temp;
    }

    function getSpecificMassiveActions($checkitem = NULL)
    {
        $actions = parent::getSpecificMassiveActions($checkitem);

        // add a single massive action
        $class = __CLASS__;

        $action_key = "update_dlteams_action";
        $action_label = __("Update dlteams relations", 'dlteams');
        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;

        $action_key = "delete_dlteams_action";
        $action_label = _n("Delete dlteams relation", "Delete dlteams relations", 0, "dlteams");

        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;

        return $actions;
    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:purge';
        $forbidden[] = 'MassiveAction:update';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }

    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {


        switch ($ma->getAction()) {
            case 'update_dlteams_action':
//                var_dump($ma->POST);
                if (!isset($ma->POST['id_field'])) {
                    $itemtypes = array_keys($ma->items);
                    $options_per_type = [];
                    $options_counts = [];
                    foreach ($itemtypes as $itemtype) {
                        $options_per_type[$itemtype] = [];
                        $group = '';
                        $show_all = true;
                        $show_infocoms = true;
                        $itemtable = getTableForItemType($itemtype);

                        if (
                            Infocom::canApplyOn($itemtype)
                            && (!$itemtype::canUpdate()
                                || !Infocom::canUpdate())
                        ) {
                            $show_all = false;
                            $show_infocoms = Infocom::canUpdate();
                        }
                        foreach (Search::getCleanedOptions($itemtype, UPDATE) as $index => $option) {
                            if (!is_array($option) || count($option) == 1) {
                                $group = !is_array($option) ? $option : $option['name'];
                                $options_per_type[$itemtype][$group] = [];
                            } else {
                                if (
                                    ($option['field'] != 'id')
                                    && ($index != 1)
                                    // Permit entities_id is explicitly activate
                                    && (($option["linkfield"] != 'entities_id')
                                        || (isset($option['massiveaction']) && $option['massiveaction']))
                                ) {
                                    if (!isset($option['massiveaction']) || $option['massiveaction']) {
                                        if (
                                            ($show_all)
                                            || (($show_infocoms
                                                    && Search::isInfocomOption($itemtype, $index))
                                                || (!$show_infocoms
                                                    && !Search::isInfocomOption($itemtype, $index)))
                                        ) {
                                            $options_per_type[$itemtype][$group][$itemtype . ':' . $index]
                                                = $option['name'];
                                            if ($itemtable == $option['table']) {
                                                $field_key = 'MAIN:' . $option['field'] . ':' . $index;
                                            } else {
                                                $field_key = $option['table'] . ':' . $option['field'] . ':' . $index;
                                            }
                                            if (!isset($options_count[$field_key])) {
                                                $options_count[$field_key] = [];
                                            }
                                            $options_count[$field_key][] = $itemtype . ':' . $index . ':' . $group;
                                            if (isset($option['MA_common_field'])) {
                                                if (!isset($options_count[$option['MA_common_field']])) {
                                                    $options_count[$option['MA_common_field']] = [];
                                                }
                                                $options_count[$option['MA_common_field']][]
                                                    = $itemtype . ':' . $index . ':' . $group;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (count($itemtypes) > 1) {
                        $common_options = [];
                        foreach ($options_count as $field => $users) {
                            if (count($users) > 1) {
                                $labels = [];
                                foreach ($users as $user) {
                                    $user = explode(':', $user);
                                    $itemtype = $user[0];
                                    $index = $itemtype . ':' . $user[1];
                                    $group = implode(':', array_slice($user, 2));
                                    if (isset($options_per_type[$itemtype][$group][$index])) {
                                        if (
                                        !in_array(
                                            $options_per_type[$itemtype][$group][$index],
                                            $labels
                                        )
                                        ) {
                                            $labels[] = $options_per_type[$itemtype][$group][$index];
                                        }
                                    }
                                    $common_options[$field][] = $index;
                                }
                                $options[$group][$field] = implode('/', $labels);
                            }
                        }
                        $choose_itemtype = true;
                        $itemtype_choices = [-1 => Dropdown::EMPTY_VALUE];
                        foreach ($itemtypes as $itemtype) {
                            $itemtype_choices[$itemtype] = $itemtype::getTypeName(Session::getPluralNumber());
                        }
                    } else {
                        $options = $options_per_type[$itemtypes[0]];
                        $common_options = false;
                        $choose_itemtype = false;
                    }
                    $choose_field = is_countable($options) ? (count($options) >= 1) : false;

                    // Beware: "class='tab_cadre_fixe'" induce side effects ...
                    echo "<table width='100%'><tr>";

                    $colspan = 0;
                    if ($choose_field) {
                        $colspan++;
                        echo "<td>";
                        if ($common_options) {
                            echo __('Select the common field that you want to update');
                        } else {
                            echo __('Select the field that you want to update');
                        }
                        echo "</td>";
                        if ($choose_itemtype) {
                            $colspan++;
                            echo "<td rowspan='2'>" . __('or') . "</td>";
                        }
                    }

                    if ($choose_itemtype) {
                        $colspan++;
                        echo "<td>" . __('Select the type of the item on which applying this action') . "</td>";
                    }

                    echo "</tr><tr>";
                    // Remove empty option groups
                    $options = array_filter($options, static function ($v) {
                        return !is_array($v) || count($v) > 0;
                    });
                    if ($choose_field) {
                        echo "<td>";
                        $field_rand = Dropdown::showFromArray(
                            'id_field',
                            $options,
                            ['display_emptychoice' => true]
                        );
                        echo "</td>";
                    }
                    if ($choose_itemtype) {
                        echo "<td>";
                        $itemtype_rand = Dropdown::showFromArray(
                            'specialize_itemtype',
                            $itemtype_choices
                        );
                        echo "</td>";
                    }

                    $next_step_rand = mt_rand();

                    echo "</tr></table>";
                    echo "<span id='update_next_step$next_step_rand'>&nbsp;</span>";

                    if ($choose_field) {
                        $params = $ma->POST;
                        $params['id_field'] = '__VALUE__';
                        $params['common_options'] = $common_options;
                        Ajax::updateItemOnSelectEvent(
                            "dropdown_id_field$field_rand",
                            "update_next_step$next_step_rand",
                            $_SERVER['REQUEST_URI'],
                            $params
                        );
                    }

                    if ($choose_itemtype) {
                        $params = $ma->POST;
                        $params['specialize_itemtype'] = '__VALUE__';
                        $params['common_options'] = $common_options;
                        Ajax::updateItemOnSelectEvent(
                            "dropdown_specialize_itemtype$itemtype_rand",
                            "update_next_step$next_step_rand",
                            $_SERVER['REQUEST_URI'],
                            $params
                        );
                    }
                    // Only display the form for this stage
                    exit();
                }

                if (!isset($ma->POST['common_options'])) {
                    echo "<div class='center'><img src='" . $CFG_GLPI["root_doc"] . "/pics/warning.png' alt='" .
                        __s('Warning') . "'><br><br>";
                    echo "<span class='b'>" . __('Implementation error!') . "</span><br>";
                    echo "</div>";
                    exit();
                }

                if ($ma->POST['common_options'] == 'false') {
                    $search_options = [$ma->POST['id_field']];
                } else if (isset($ma->POST['common_options'][$ma->POST['id_field']])) {
                    $search_options = $ma->POST['common_options'][$ma->POST['id_field']];
                } else {
                    $search_options = [];
                }

                // TODO: ensure that all items are equivalent ...
                $item = null;
                $search = null;
                foreach ($search_options as $search_option) {
                    $search_option = explode(':', $search_option);
                    $so_itemtype = $search_option[0];
                    $so_index = $search_option[1];

                    if (!$so_item = getItemForItemtype($so_itemtype)) {
                        continue;
                    }

                    if (Infocom::canApplyOn($so_itemtype)) {
                        Session::checkSeveralRightsOr([$so_itemtype => UPDATE,
                            "infocom" => UPDATE
                        ]);
                    } else {
                        $so_item->checkGlobal(UPDATE);
                    }

                    $itemtype_search_options = Search::getOptions($so_itemtype);
                    if (!isset($itemtype_search_options[$so_index])) {
                        exit();
                    }

                    $item = $so_item;
                    $search = $itemtype_search_options[$so_index];
                    break; // No need to process all items a corresponding item/searchoption has been found
                }

                if ($item === null) {
                    exit();
                }

                $plugdisplay = false;
                if (
                    ($plug = isPluginItemType($item->getType()))
                    // Specific for plugin which add link to core object
                    || ($plug = isPluginItemType(getItemTypeForTable($search['table'])))
                ) {
//                    $plugdisplay = Plugin::doOneHook(
//                        $plug['plugin'],
//                        'MassiveActionsFieldsDisplay',
//                        ['itemtype' => $item->getType(),
//                            'options'  => $search
//                        ]
//                    );
                }

                if (
                    empty($search["linkfield"])
                    || ($search['table'] == 'glpi_infocoms')
                ) {
                    $fieldname = $search["field"];
                } else {
                    $fieldname = $search["linkfield"];
                }

                if (!$plugdisplay) {
                    $options = [];
                    $values = [];
                    // For ticket template or aditional options of massive actions
                    if (isset($ma->POST['options'])) {
                        $options = $ma->POST['options'];
                    }
                    switch ($item->getType()) {
                        case 'Change':
                            $search['condition'][] = 'is_change';
                            break;
                        case 'Problem':
                            $search['condition'][] = 'is_problem';
                            break;
                        case 'Ticket':
                            if ($DB->fieldExists($search['table'], 'is_incident') || $DB->fieldExists($search['table'], 'is_request')) {
                                $search['condition'][] = [
                                    'OR' => [
                                        'is_incident',
                                        'is_request'
                                    ]
                                ];
                            }
                            break;
                    }
                    if (isset($ma->POST['additionalvalues'])) {
                        $values = $ma->POST['additionalvalues'];
                    }
                    $values[$search["field"]] = '';
                    echo $item->getValueToSelect($search, $fieldname, $values, $options);
                }

                $items_index = [];
                foreach ($search_options as $search_option) {
                    $search_option = explode(':', $search_option);
                    $items_index[$search_option[0]] = $search_option[1];
                }
//                New lines added for dlteams
//            baseitem est l'item de la table de base. glpi_plugin_dlteams_records_items pour le cas present
                echo Html::hidden('baseitem', ['value' => PluginDlteamsRecord_Item::class]);
                echo Html::hidden('itemtype', ['value' => 'PluginDlteamsStoragePeriod']);
//            end of new lines addes for dlegister
                echo Html::hidden('search_options', ['value' => $items_index]);
                echo Html::hidden('field', ['value' => $fieldname]);
                echo "<br>\n";

                $submitname = "<i class='fas fa-save'></i><span>" . _sx('button', 'Post') . "</span>";
                if (isset($ma->POST['submitname']) && $ma->POST['submitname']) {
                    $submitname = stripslashes($ma->POST['submitname']);
                }
                echo Html::submit($submitname, [
                    'name' => 'massiveaction',
                    'class' => 'btn btn-sm btn-primary',
                ]);


                return true;
        }
        return parent::showMassiveActionsSubForm($ma);
    }


    public function delete(array $input, $force = 0, $history = 1)
    {
        var_dump("delete");
        die();
    }


    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
//        var_dump(static::$itemtype);
//        die();

        /*        highlight_string("<?php\n\$data =\n" . var_export($ma->POST, true) . ";\n?>");*/
//        die();

        switch ($ma->getAction()) {
            case 'delete_dlteams_action':
                foreach ($ids as $id) {
//                    var_dump($item->getType());
//                    die();
                    if ($item->getFromDB($id)) {
                        var_dump($item->getType());
                        die();
                        $plural_item_name = strtolower(str_replace("PluginDlteams", "", $item->getField('itemtype'))) . "s_id";
                        $relationTable = CommonDBRelation::getTable(
                            $item->getField('itemtype')
                        );

                        // suppression universelle de l'item lié
                        global $DB;

                        $DB->delete(
                            $relationTable . '_items',
                            array(
                                'itemtype' => str_replace("_Item", "", $item->getType()),
                                $plural_item_name => $item->getField('items_id'),
                                'items_id' => $item->getField(strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $item->getType()))) . "s_id")
                            )
                        );

                        // suppression de l'item
                        $item->delete(array('id' => $id));
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                    }
                }
                break;

            case 'update_dlteams_action':
                $temp = new PluginDlteamsLegalBasi_Item();
                $updatable_columns = $temp->rawSearchOptions();
                $parts = explode(':', $ma->POST['id_field']);
                $id_modif = $parts[1];

                switch ($id_modif) {
                    case static::$column1_id:
                        foreach ($ids as $id) {
                            $class_str = __CLASS__;
                            $base_item = new $class_str();
                            if ($base_item->getFromDB($id)) {
//                                var_dump($base_item->fields);
                                $array_values = array_values($ma->POST);
                                /*                                highlight_string("<?php\n\$data =\n" . var_export($ma->POST, true) . ";\n?>");*/
//                                die();

//                            suppression dans storage period item
                                $itemtype_item_str = $ma->POST["itemtype"] . "_Item";
                                $itemtype_item = new $itemtype_item_str();
                                $itemtype_item_id_column = strtolower(str_replace("PluginDlteams", "", $base_item->fields["itemtype"])) . "s_id";
                                $base_items_id_column = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", __CLASS__))) . "s_id";
                                $t = $itemtype_item->deleteByCriteria([
                                    $itemtype_item_id_column => $base_item->fields["items_id"],
                                    "itemtype" => $ma->POST["base"],
                                    "items_id" => $base_item->fields[strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", __CLASS__))) . "s_id"],
//                                    "foreign_id" => $base_item->fields["id"]
                                ]);

//                                ajout de la nouvelle ligne
                                $ee = $itemtype_item->add([
                                    $itemtype_item_id_column => $base_item->fields["items_id"],
                                    "itemtype" => $ma->POST["base"],
                                    "items_id" => $array_values[1],
//                                    "foreign_id" => $base_item->fields["id"]
                                ]);


                                $t = $base_item->update([
                                    $base_items_id_column => $array_values[1], // 'items_id' parce que case case static::$column1_id ou static::$column1_id est 49 ce qui correspond a l'id de la fonction rawSearchOptions
                                    'id' => $id
                                ]);
                            }
                        }
                        break;
                    case static::$column2_id:
                        foreach ($ids as $id) {
                            $base_item = new self();
                            if ($base_item->getFromDB($id)) {
                                $array_values = array_values($ma->POST);
//                            suppression dans storage period item
                                $itemtype_item_str = $base_item->fields["itemtype"] . "_Item";
                                $itemtype_item = new $itemtype_item_str();
                                $itemtype_item_id_column = strtolower(str_replace("PluginDlteams", "", $base_item->fields["itemtype"])) . "s_id";
                                $t = $itemtype_item->deleteByCriteria([
                                    $itemtype_item_id_column => $base_item->fields["items_id"],
                                    "itemtype" => "PluginDlteamsLegalbasi",
                                    "items_id" => $base_item->fields[strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", __CLASS__))) . "s_id"],
//                                    "foreign_id" => $base_item->fields["id"]
                                ]);

//                                ajout de la nouvelle ligne
                                $ee = $itemtype_item->add([
                                    $itemtype_item_id_column => $base_item->fields["items_id"],
                                    "itemtype" => $base_item->fields["itemtype"],
                                    "items_id" => $base_item->fields[strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", __CLASS__))) . "s_id"],
//                                    "foreign_id" => $base_item->fields["id"],
                                    "comment" => $array_values[1]
                                ]);


                                $t = $base_item->update([
                                    "comment" => $array_values[1],
                                    'id' => $id
                                ]);
                            }
                        }
                        break;
                }
                $ma->__set("remainings", [__CLASS__ => [$id => $id]]);
                $ma->itemDone(__CLASS__, $id, MassiveAction::ACTION_OK);
                break;
        }


        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }


    function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id' => static::$column1_id,
            'table' => PluginDlteamsGroup_Item::getTable(),
            'field' => 'comment',
            'name' => __("Commentaire"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'text',
            'searchtype' => ['equals', 'notequals'],
        ];


        return $tab;
    }
}
