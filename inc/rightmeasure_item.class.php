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

class PluginDlteamsRightMeasure_Item extends CommonDBRelation
{

    public static $itemtype_1 = 'PluginDlteamsRightMeasure';
    public static $items_id_1 = 'rightmeasures_id';
    public static $take_entity_1 = false;

    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';
    public static $take_entity_2 = true;

    public static $column2_id = "34";

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
        return __("Eléments reliés", 'dlteams');
    }


    // affichage de l'onglet et de son nom
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        global $DB;
//        return static::createTabEntry(static::getTypeName(Session::getPluralNumber()), 0);
        switch ($item->getType()) {
            case 'PluginDlteamsRightMeasure':
                $nbitem = count($DB->request(static::getRequest($item->fields['id'])));

                $ong = [];
                $ong[1] = static::createTabEntry(static::getTypeNameForClass(Session::getPluralNumber()), $nbitem);


                return $ong;
                break;

        }
    }

    static function getTypeName($nb = 0)
    {
        return __("Concerned persons", 'dlteams');
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case 'PluginDlteamsRightMeasure':
                switch ($tabnum) {
                    case 1:
                        self::showForRightMeasures($item);
                        break;
                }
                break;
            default:
//                self::showForitem($item, $withtemplate);
                break;
        }

        return true;

    }

    public static function showForRightMeasures(PluginDlteamsRightMeasure $item)
    {
        global $DB;
        $instID = $item->fields['id'];
        if (!$item->can($instID, READ)) {
            return false;
        }
        $canedit = $item->can($instID, UPDATE);
        // for a measure,
        // don't show here others protective measures associated to this one,
        // it's done for both directions in self::showAssociated
        $types_iterator = [];
        $number = count($types_iterator);

        $used = [];
        $types = PluginDlteamsItemType::getTypes();
        $key = array_search(get_class($item), $types);
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
            echo "<tr class='tab_bg_2'><th>" . __($title, 'dlteams') .
                "<br><i style='font-weight: normal'>" .
                "</i></th>";
            echo "<th colspan='2'></th></tr>";

            echo "<tr class='tab_bg_1'><td class='left' width='40%'>" . __($entitled, 'dlteams');
            echo "</td><td width='40%' class='left'>";
            $types = PluginDlteamsItemType::getTypes();
            $key = array_search("PluginDlteamsConcernedPerson", $types);
            unset($types[$key]);
            Dropdown::showSelectItemFromItemtypes(['itemtypes' => $types,
                'entity_restrict' => ($item->fields['is_recursive'] ? getSonsOf('glpi_entities', $item->fields['entities_id'])
                    : $item->fields['entities_id']),
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
        $items = $DB->request(static::getRequest($instID));
        $items = iterator_to_array($items);
        $items_to_list = [];
        foreach ($items as $itemdata) {
            $temp = array_filter($items_to_list, function ($k) use ($itemdata) {
                return $k['items_id'] === $itemdata['items_id'] && $k['itemtype'] === $itemdata['itemtype'];
            });
// Decommenter si on doit afficher les redondance
            if (count($temp) === 0) {
                array_push($items_to_list, $itemdata);
            }
        }


        if (!count($items_to_list)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
            if ($canedit) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = [
                    'num_displayed' => min($_SESSION['glpilist_limit'], count($items)),
                    'container' => 'mass' . __CLASS__ . $rand,
                    'confirm' => true
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
            $header .= "<th>" . __("Object") . "</th>";
            $header .= "<th>" . __("Type") . "</th>";
            $header .= "<th>" . __("Comment") . "</th>";
            $header .= "</tr>";
            echo $header;

            foreach ($items_to_list as $row) {
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


    function rawSearchOptions()
    {

        $tab = [];


        $tab[] = [
            'id' => static::$column2_id,
            'table' => PluginDlteamsConcernedPerson_Item::getTable(),
            'field' => 'comment',
            'name' => __("Commentaire"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'text',
            'searchtype' => ['equals', 'notequals'],
            'joinparams' => [
                'beforejoin' => [
                    'table' => self::getTable(),
                    'joinparams' => [
                        'jointype' => 'child'
                    ]
                ]
            ]
        ];

        return $tab;
    }

    static function getRequest($instID)
    {
        $link_table = str_replace("_Item", "", __CLASS__);
        $temp = new $link_table();
        return [
            'FROM' => self::getTable(),
            'SELECT' => [
                self::getTable() . '.id',
                self::getTable() . '.id as linkid',
                self::getTable() . '.comment',
                self::getTable() . '.itemtype as itemtype',
                self::getTable() . '.items_id as items_id',
            ],
            'WHERE' => [
                static::getTable() . '.rightmeasures_id' => $instID
            ],

            'LEFT JOIN' => [
                $temp->getTable() => [
                    'FKEY' => [
                        static::getTable() => 'rightmeasures_id',
                        $temp->getTable() => 'id'
                    ]
                ]
            ],
            'ORDER' => self::getTable() . '.id DESC'
        ];
    }


    static function showMassiveActionsSubForm(MassiveAction $ma)
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

    function getSpecificMassiveActions($checkitem = NULL)
    {
        $actions = parent::getSpecificMassiveActions($checkitem);

        // add a single massive action
        $class = __CLASS__;

        $action_key = "update_dlteams_action";
        $action_label = __("Update dlteams relations", "dlteams");
        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;

        $action_key = "delete_dlteams_action";
        $action_label = _n("Delete dlteams relation", "Delete dlteams relations", 0, "dlteams");
        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;

        return $actions;
    }

    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {

        switch ($ma->getAction()) {
            case 'delete_dlteams_action':
                foreach ($ids as $id) {
                    if ($item->getFromDB($id)) {
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
                $temp = new self();
                $updatable_columns = $temp->rawSearchOptions();
                $parts = explode(':', $ma->POST['id_field']);
                $id_modif = $parts[1];

                switch ($id_modif) {
                    case static::$column2_id:

                        foreach ($ids as $id) {
                            $base_item = new self();
                            if ($base_item->getFromDB($id)) {
//                                var_dump($base_item->fields);
                                $array_values = array_values($ma->POST);

                                $itemtype_str = $base_item->fields['itemtype'] . "_Item";
                                $itemtype_item = new $itemtype_str();
                                $itemtype_item_id_column = strtolower(str_replace("PluginDlteams", "", $base_item->fields["itemtype"])) . "s_id";

                                $t = $itemtype_item->deleteByCriteria([
                                    $itemtype_item_id_column => $base_item->fields["items_id"],
                                    "itemtype" => "PluginDlteamsRightMeasure",
                                    "items_id" => $base_item->fields["rightmeasures_id"],
                                ]);

                                //                                ajout de la nouvelle ligne
                                $ee = $itemtype_item->add([
                                    $itemtype_item_id_column => $base_item->fields["items_id"],
                                    "itemtype" => "PluginDlteamsRightMeasure",
                                    "items_id" => $base_item->fields["rightmeasures_id"],
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

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:purge';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        $forbidden[] = 'MassiveAction:update';
        return $forbidden;
    }
}

?>

<script>
    $(document).ready(function () {

        var form = document.querySelector('[id^="massaction_"][id$="' + dynamicID + '"]');

        form.addEventListener('submit', function (event) {
            console.log(event);
            if (!confirm('Voulez-vous vraiment soumettre ce formulaire ?')) {
                event.preventDefault();
            }
        });

        $('select[name=items_id]').on('change', function () {
            // alert($(this).val());
            if ($(this).val() != '0') {
                document.getElementById('update_comment_textarea').style.display = 'block';
                // $.ajax({
                //     url: '/marketplace/dlteams/ajax/get_object_specific_field.php',
                //     type: 'POST',
                //     data: {
                //         id: $(this).val(),
                //         object: 'PluginDlteamsProtectiveMeasure',
                //         field: 'content'
                //     },
                //     success: function (data) {
                //         // Handle the returned data here
                //         console.log(data);
                //         $("#update_comment_textarea").val(data);
                //     }
                // });
            } else {
                document.getElementById('update_comment_textarea').style.display = 'none';
            }
            //
        });


        $('select[name=itemtype]').on('change', function () {
            if ($(this).val() != '0') {
                document.getElementById('update_comment_textarea').style.display = 'block';
                // $.ajax({
                //     url: '/marketplace/dlteams/ajax/get_object_specific_field.php',
                //     type: 'POST',
                //     data: {
                //         id: $(this).val(),
                //         object: 'PluginDlteamsProtectiveMeasure',
                //         field: 'content'
                //     },
                //     success: function (data) {
                //         // Handle the returned data here
                //         console.log(data);
                //         $("#update_comment_textarea").val(data);
                //     }
                // });
            } else {
                document.getElementById('update_comment_textarea').style.display = 'none';
            }
            //
        });

    });


</script>
