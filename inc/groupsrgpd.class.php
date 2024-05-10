<?php

class PluginDlteamsGroupsRGPD extends CommonDBChild
{

    public static $column1_id = '12';

    public function __construct()
    {
        static::forceTable('glpi_plugin_dlteams_groups_items');
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


    /**
     * Show items links to a document
     *
     * @param $doc Document object
     *
     * @return void
     **@since 0.84
     *
     */
    public static function showItems(Group $item)
    {


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

        if ($canedit) {
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . $item->getType() . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $item->getID() . "' />";
            echo "<input type='hidden' name='itemtype' value='" . Group::getType() . "' />";
            echo "<input type='hidden' name='entities_id' value='" . $item->fields['entities_id'] . "' />";

            /* il faudrait pouvoir faire un if fonction de l'objet pour adater la traduction de l'onglet Documents
            les traductions seront fonction de l'objet : alors $title = concatener(%classobjet,"_title"), $entitled = concatener (%classobjet; "_entitled")
                on remplace echo "<tr class='tab_bg_2'><th>" . __("Add Documents", 'dlteams') .
                par 		echo "<tr class='tab_bg_2'><th>" . __($titre, 'dlteams') .
                on remplace echo "<tr class='tab_bg_1'><td class='left' width='40%'>". __("Documents in which data may be stored", 'dlteams');
                par 	    echo "<tr class='tab_bg_1'><td class='left' width='40%'>". __($entitled, 'dlteams');
            Dans le fichier PO, il faudra ajouter les lignes record_element_title & record_element_entitled */

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th>" . __("Add related objects", 'dlteams') .
                "<br><i style='font-weight: normal'>" .
                "</i></th>";
            echo "<th colspan='2'></th></tr>";

            echo "<tr class='tab_bg_1'><td class='left' width='40%'>" . __("Related objects", 'dlteams');
            echo "</td><td width='80%' class='left'>";
            $types = PluginDlteamsItemType::getTypes();
            $key = array_search(get_class($item), $types);
            unset($types[$key]);
            Dropdown::showSelectItemFromItemtypes(['itemtypes' => $types,
                'entity_restrict' => ($item->fields['is_recursive'] ? getSonsOf('glpi_entities', $item->fields['entities_id'])
                    : $item->fields['entities_id']),
                'checkright' => true,
                'used' => $used,
                'width' => '300px',
            ]);
            unset($types);
            echo "</td><td width='20%' class='right'><input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td></tr>";

            echo "<tr class='tab_bg_1'><td width='35%' class=''>";
            echo __("Comment");
            echo "<br/><br/>";
            echo "<textarea type='text' style='width:100%' maxlength=1000 rows='3' name='comment' class='storage_comment1'></textarea>";
            echo "</td>";
            echo "</tr>";

            echo "</table>";
            Html::closeForm();
        }

        if ($iterator) {

            echo "<div class='spaced'>";
            if ($canedit && $number) {
                /*Html::openMassiveActionsForm('mass' . PluginDlteamsAllItem::class . $rand);
                $massive_action_params = ['container' => 'mass' . PluginDlteamsAllItem::class . $rand,
                   'num_displayed' => min($_SESSION['glpilist_limit'], $number)];
                Html::showMassiveActions($massive_action_params);*/
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massive_action_params = ['container' => 'mass' . __CLASS__ . $rand,
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
                /*$header_top     .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsAllItem::class . $rand);
                $header_bottom  .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsAllItem::class . $rand);*/
                $header_top .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                $header_end .= "</th>";
            }

            $header_end .= "<th>" . __("Name") . "</th>";
            $header_end .= "<th>" . __("Type") . "</th>";
            $header_end .= "<th>" . __("Comment") . "</th>";
            $header_end .= "</tr>";

            echo $header_begin . $header_top . $header_end;
            //var_dump($items_list);
            foreach ($items_list as $data) {
                if ($data['name']) {
                    echo "<tr class='tab_bg_1'>";

                    if ($canedit && $number) {
                        echo "<td width='10'>";
                        /*Html::showMassiveActionCheckBox(PluginDlteamsAllItem::class, $data['linkid']);*/
                        Html::showMassiveActionCheckBox(__CLASS__, $data['linkid']);
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

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo " width='40%'>";
                    if ($data['comment']) {
                        echo $data['comment'];
                    } else {
                        echo "---";
                    }
                    echo "</td>";

                    echo "</tr>";
                }
            }


            /*if ($iterator->count() > 10) {
               echo $header_begin . $header_bottom . $header_end;
            }*/
            echo "</table>";

            if ($canedit && $number) {
                $massive_action_params['ontop'] = false;
                Html::showMassiveActions($massive_action_params);
                Html::closeForm();
            }

            echo "</div>";
        }
    }

    public static function getRequest(Group $group)
    {
        $request = "SELECT `glpi_plugin_dlteams_groups_items`.`id` AS `linkid`, 
                            `glpi_plugin_dlteams_groups_items`.`itemtype` AS `itemtype`, 
                            `glpi_plugin_dlteams_groups_items`.`items_id` AS `items_id`, 
                            `glpi_groups`.`id` AS `id`, 
                            `glpi_groups`.`name` AS `name`, 
                            `glpi_plugin_dlteams_groups_items`.`comment` AS `comment` 
                    FROM `glpi_plugin_dlteams_groups_items` 
                    LEFT JOIN `glpi_groups` 
                        ON (`glpi_plugin_dlteams_groups_items`.`groups_id` = `glpi_groups`.`id`) 
                    WHERE `glpi_plugin_dlteams_groups_items`.`itemtype` LIKE 'PluginDlteams%' 
                    AND `glpi_plugin_dlteams_groups_items`.`groups_id` = " . $group->fields['id'] . "
                    ORDER BY `name` ASC;";

        global $DB;
        return $DB->request($request);
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
//        var_dump($ma->);

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

    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        switch ($ma->getAction()) {
            case 'delete_dlteams_action':
                foreach ($ids as $id) {
                    $object_item = new PluginDlteamsGroup_Item();

                    if ($object_item->getFromDB($id)) {

                        global $DB;
                        switch ($object_item->getField('itemtype')) {
                            case 'User':
                                $relationTable = 'glpi_plugin_dlteams_users_items';
                                break;
                            default:
                                $relationTable = CommonDBRelation::getTable(
                                    $object_item->getField('itemtype')
                                );

                                $relationTable .= '_items';
                                break;
                        }


                        switch ($object_item->getField('itemtype')) {
                            case 'PluginDlteamsThirdPartyCategory':
                                $itemtype_item_id_column = "thirdpartycategories_id";
                                break;
                            default:
                                $itemtype_item_id_column = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $object_item->getField('itemtype')))) . "s_id";
                                break;
                        }

                        $DB->delete(
                            $relationTable,
                            array(
                                'itemtype' => 'Group',
                                'items_id' => $object_item->fields['groups_id'],
                                $itemtype_item_id_column => $object_item->fields['items_id'],
                                'comment' => $object_item->fields['comment']
                            )
                        );

                        $object_item->delete(array('id' => $id));
                        $ma->itemDone(__CLASS__, $id, MassiveAction::ACTION_OK);
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                    }
                }
                break;

            case 'update_dlteams_action':
                $parts = explode(':', $ma->POST['id_field']);
                $id_modif = $parts[1];

                switch ($id_modif) {
                    case static::$column1_id:
                        foreach ($ids as $id) {
                            $base_item = new PluginDlteamsGroup_Item();
                            if ($base_item->getFromDB($id)) {
//                                var_dump($base_item->fields);
                                $array_values = array_values($ma->POST);


                                $itemtype_item_str = $base_item->fields["itemtype"]."_Item";
                                $itemtype_item = new $itemtype_item_str();
                                $itemtype_item_id_column = strtolower(str_replace("PluginDlteams", "", $base_item->getField('itemtype'))) . "s_id";

                                $t = $itemtype_item->deleteByCriteria([
                                    $itemtype_item_id_column => $base_item->fields["items_id"],
                                    "itemtype" => "Group",
                                    "items_id" => $base_item->fields["groups_id"],
                                    "comment" => $base_item->fields["comment"],
                                ]);

//                                ajout de la nouvelle ligne
                                $ee = $itemtype_item->add([
                                    $itemtype_item_id_column => $base_item->fields["items_id"],
                                    "itemtype" => "Group",
                                    "items_id" => $base_item->fields["groups_id"],
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
