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

class PluginDlteamsDatabase_Item extends CommonDBRelation
{
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

    // From CommonDBRelation
    public static $itemtype_1 = "PluginDlteamsDatabase";
    public static $items_id_1 = 'databases_id';
    public static $take_entity_1 = false;

    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';
    public static $take_entity_2 = true;

    public static $column2_id = '43';

    /**
     * @since 9.2
     *
     **/
    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        $forbidden[] = 'purge';
        $forbidden[] = 'delete';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }


    /**
     * @param CommonDBTM $item
     */
    public static function cleanForItem(CommonDBTM $item)
    {
        $temp = new self();
        $temp->deleteByCriteria(['itemtype' => $item->getType(),
            'items_id' => $item->getField('id')
        ]);
    }

    function rawSearchOptions()
    {

        $tab = [];


        $tab[] = [
            'id' => static::$column2_id,
            'table' => PluginDlteamsDatabase_Item::getTable(),
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

    /**
     * @param CommonGLPI $item
     * @param int $withtemplate
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            if (
                $item->getType() == 'PluginDlteamsDatabase'
            ) {
                if ($_SESSION['glpishow_count_on_tabs']) {
                    return self::createTabEntry(
                        _n('Associated item', 'Associated items', Session::getPluralNumber()),
                        self::countForMainItem($item)
                    );
                }
                return _n('Associated item', 'Associated items', Session::getPluralNumber());
            } else if (
                $item->getType()
                && PluginDlteamsDatabase::canView()
            ) {
                if ($_SESSION['glpishow_count_on_tabs']) {
                    return self::createTabEntry(
                        PluginDlteamsDatabase::getTypeName(2),
                        self::countForItem($item)
                    );
                }
                return PluginDlteamsDatabase::getTypeName(2);
            }
        }
        return '';
    }


    /**
     * @param CommonGLPI $item
     * @param int $tabnum
     * @param int $withtemplate
     * @return bool
     */
    public static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0
    )
    {

        if ($item->getType() == 'PluginDlteamsDatabase') {
            self::showForPluginDlteamsDatabase($item);
        } else if ($item->getType()) {
            self::showForItem($item);
        }
        return true;
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


    /**
     * @param $databases_id
     * @param $items_id
     * @param $itemtype
     * @return bool
     */
    public function getFromDBbyPluginDlteamsDatabasesAndItem($databases_id, $items_id, $itemtype)
    {

        $database = new self();
        $databases = $database->find([
            'databases_id' => $databases_id,
            'itemtype' => $itemtype,
            'items_id' => $items_id
        ]);
        if (count($databases) != 1) {
            return false;
        }

        $cert = current($databases);
        $this->fields = $cert;

        return true;
    }

    /**
     * Link a database to an item
     *
     * @param $values
     * @since 9.2
     */
    public function addItem($values)
    {
        $this->add(['databases_id' => $values["databases_id"],
            'items_id' => $values["items_id"],
            'itemtype' => $values["itemtype"]
        ]);
    }


    /**
     * Delete a database link to an item
     *
     * @param integer $databases_id the database ID
     * @param integer $items_id the item's id
     * @param string $itemtype the itemtype
     * @since 9.2
     *
     */
    public function deleteItemByPluginDlteamsDatabasesAndItem($databases_id, $items_id, $itemtype)
    {

        if (
        $this->getFromDBbyPluginDlteamsDatabasesAndItem(
            $databases_id,
            $items_id,
            $itemtype
        )
        ) {
            $this->delete(['id' => $this->fields["id"]]);
        }
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
            'ORDER' => self::getTable() . '.id DESC'
        ]);

        return iterator_to_array($items);
    }

    /**
     * Show items linked to a database
     *
     * @param PluginDlteamsDatabase $database PluginDlteamsDatabase object
     *
     * @return void|boolean (display) Returns false if there is a rights error.
     **@since 9.2
     *
     */
    public static function showForPluginDlteamsDatabase(PluginDlteamsDatabase $database)
    {

        $instID = $database->fields['id'];
        if (!$database->can($instID, READ)) {
            return false;
        }
        $canedit = $database->can($instID, UPDATE);
        $rand = mt_rand();

        $items = self::getItemsRequest($database);

        $number = count($items);

        if (Session::isMultiEntitiesMode()) {
            $colsup = 1;
        } else {
            $colsup = 0;
        }


        $used = [];
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
                'entity_restrict' => ($database->fields['is_recursive'] ? getSonsOf('glpi_entities', $database->fields['entities_id'])
                    : $database->fields['entities_id']),
                'checkright' => true,
                'used' => $used
            ]);
            unset($types);
            echo "</td><td width='20%' class='left'><input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td></tr>";
            echo "<tr class='tab_bg_1'><td width='35%' class=''>";
            echo __("Comment");
            echo "<br/><br/>";
            echo "<textarea type='text' style='width:100%' maxlength=1000 rows='3' name='comment' class='comment'></textarea>";
            echo "</td>";
            echo "</table>";
            Html::closeForm();
        }


        if (!count($items)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        }
        else {
            if ($canedit) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = [
                    'num_displayed'   => min($_SESSION['glpilist_limit'], count($items)),
                    'container'       => 'mass' . __CLASS__ . $rand
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
                echo "<td>" .  $name . "</td>";
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


//        echo "<div class='spaced'>";
//        if ($canedit && $number) {
//            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
//            $massiveactionparams = [];
//            Html::showMassiveActions($massiveactionparams);
//        }
//        echo "<table class='tab_cadre_fixe'>";
//        echo "<tr>";
//
//        if ($canedit && $number) {
//            echo "<th width='10'>" .
//                Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
//        }
//
//        echo "<th>" . __('Name') . "</th>";
//        echo "<th>" . _n('Type', 'Types', 1) . "</th>";
//        if (Session::isMultiEntitiesMode()) {
//            echo "<th>" . Entity::getTypeName(1) . "</th>";
//        }
//        echo "<th>" . __('Comment') . "</th>";
//        //echo "<th>" . __('Inventory number') . "</th>";
//        echo "</tr>";
//
//        foreach ($types_iterator as $type_row) {
//            $itemtype = $type_row['itemtype'];
//
//            var_dump($type_row['comment']);
//
//            if (!($item = getItemForItemtype($itemtype))) {
//                continue;
//            }
//
//            if ($item->canView()) {
//                $iterator = self::getTypeItems($instID, $itemtype);
//
//                if (count($iterator)) {
//                    Session::initNavigateListItems($itemtype, PluginDlteamsDatabase::getTypeName(2) . " = " . $database->fields['name']);
//                    foreach ($iterator as $data) {
//
//                        $item->getFromDB($data["id"]);
//                        Session::addToNavigateListItems($itemtype, $data["id"]);
//                        $ID = "";
//                        if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
//                            $ID = " (" . $data["id"] . ")";
//                        }
//
//                        $link = $itemtype::getFormURLWithID($data["id"]);
//                        $name = "<a href=\"" . $link . "\">" . $data["name"] . "$ID</a>";
//
//                        echo "<tr class='tab_bg_1'>";
//                        if ($canedit) {
//                            echo "<td width='10'>";
//                            Html::showMassiveActionCheckBox(__CLASS__, $data["linkid"]);
//                            echo "</td>";
//                        }
//                        echo "<td class='left' " . (isset($data['is_deleted']) && $data['is_deleted'] ? "class='tab_bg_2_2'" : "") .
//                            ">" . $name . "</td>";
//                        echo "<td class='left'>" . $item->getTypeName(1) . "</td>";
//                        if (Session::isMultiEntitiesMode()) {
//                            $entity = ($item->isEntityAssign() ?
//                                Dropdown::getDropdownName("glpi_entities", $data['entity']) :
//                                '-');
//                            echo "<td class='left'>" . $entity??"--" . "</td>";
//                        }
//
//
//                        echo "<td class='left'>" .  $type_row["comment"] . "</td>";
//                        //echo "<td class='center'>" . (isset($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-") . "</td>";
//                        echo "</tr>";
//                    }
//                }
//            }
//        }
//        echo "</table>";
//
//        if ($canedit && $number) {
//            $paramsma = [
//                'ontop' => false,
//            ];
//            Html::showMassiveActions($paramsma);
//            Html::closeForm();
//        }
//        echo "</div>";
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
                            $base_item = new PluginDlteamsDatabase_Item();
                            if ($base_item->getFromDB($id)) {
//                                var_dump($base_item->fields);
                                $array_values = array_values($ma->POST);

                                $itemtype_str = $base_item->fields['itemtype'] . "_Item";
                                $itemtype_item = new $itemtype_str();
                                $itemtype_item_id_column = strtolower(str_replace("PluginDlteams", "", $base_item->fields["itemtype"])) . "s_id";

                                $t = $itemtype_item->deleteByCriteria([
                                    $itemtype_item_id_column => $base_item->fields["items_id"],
                                    "itemtype" => "PluginDlteamsDatabase",
                                    "items_id" => $base_item->fields["databases_id"],
                                ]);

                                //                                ajout de la nouvelle ligne
                                $ee = $itemtype_item->add([
                                    $itemtype_item_id_column => $base_item->fields["items_id"],
                                    "itemtype" => "PluginDlteamsDatabase",
                                    "items_id" => $base_item->fields["databases_id"],
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
                $ma->__set("remainings", ["PluginDlteamsDatabase_Item" => [$id => $id]]);
                $ma->itemDone('PluginDlteamsDatabase_Item', $id, MassiveAction::ACTION_OK);
                break;
        }

        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    /**
     * Show databases associated to an item
     *
     * @param $item  CommonDBTM object for which associated databases must be displayed
     * @param $withtemplate (default 0)
     *
     * @return bool
     * @since 9.2
     *
     */
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {

        $ID = $item->getField('id');

        if (
            $item->isNewID($ID)
            || !PluginDlteamsDatabase::canView()
            || !$item->can($item->fields['id'], READ)
        ) {
            return false;
        }

        $database = new PluginDlteamsDatabase();

        if (empty($withtemplate)) {
            $withtemplate = 0;
        }

        $canedit = $item->canAddItem('PluginDlteamsDatabase');
        $rand = mt_rand();
        $is_recursive = $item->isRecursive();

        $iterator = self::getListForItem($item);
        $number = $iterator->numrows();
        $i = 0;

        $databases = [];
        $used = [];

        foreach ($iterator as $data) {
            $databases[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($canedit && $withtemplate < 2) {
            if ($item->maybeRecursive()) {
                $is_recursive = $item->fields['is_recursive'];
            } else {
                $is_recursive = false;
            }
            $entity_restrict = getEntitiesRestrictCriteria(
                "glpi_plugin_dlteams_databases",
                'entities_id',
                $item->fields['entities_id'],
                $is_recursive
            );

            $nb = countElementsInTable(
                'glpi_plugin_dlteams_databases',
                [
                    'is_deleted' => 0
                ] + $entity_restrict
            );

            echo "<div class='firstbloc'>";

            if (PluginDlteamsDatabase::canView() && (!$nb || ($nb > count($used)))) {
                echo "<form name='database_form$rand'
                        id='database_form$rand'
                        method='post'
                        action='" . Toolbox::getItemTypeFormURL('PluginDlteamsDatabase')
                    . "'>";
                echo "<table class='tab_cadre_fixe'>";
                echo "<tr class='tab_bg_1'>";
                echo "<td colspan='4' class='center'>";
                echo Html::hidden(
                    'entities_id',
                    ['value' => $item->fields['entities_id']]
                );
                echo Html::hidden(
                    'is_recursive',
                    ['value' => $is_recursive]
                );
                echo Html::hidden(
                    'itemtype',
                    ['value' => $item->getType()]
                );
                echo Html::hidden(
                    'items_id',
                    ['value' => $ID]
                );
                if ($item->getType() == 'Ticket') {
                    echo Html::hidden('tickets_id', ['value' => $ID]);
                }
                Dropdown::show('PluginDlteamsDatabase', ['entity' => $item->fields['entities_id'],
                    'is_recursive' => $is_recursive,
                    'used' => $used
                ]);

                echo "</td><td class='center' width='20%'>";
                echo Html::submit(_sx('button', 'Associate'), ['name' => 'add']);
                echo "</td>";
                echo "</tr>";
                echo "</table>";
                Html::closeForm();
            }

            echo "</div>";
        }

        echo "<div class='spaced table-responsive'>";
        if ($canedit && $number && ($withtemplate < 2)) {
            $massiveactionparams = ['num_displayed' => $number];
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            Html::showMassiveActions($massiveactionparams);
        }
        echo "<table class='tab_cadre_fixe'>";

        echo "<tr>";
        if ($canedit && $number && ($withtemplate < 2)) {
            echo "<th width='10'>";
            echo Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            echo "</th>";
        }
        echo "<th>" . __('Name') . "</th>";
        echo "<th>" . __('Comment') . "</th>";
        echo "<th>" . _n('Type', 'Types', 1) . "</th>";
        if (Session::isMultiEntitiesMode()) {
            echo "<th>" . Entity::getTypeName(1) . "</th>";
        }
        /*echo "<th>" . __('DNS suffix') . "</th>";
        echo "<th>" . __('Creation date') . "</th>";
        echo "<th>" . __('Expiration date') . "</th>";
        echo "<th>" . __('Status') . "</th>";*/
        echo "</tr>";

        $used = [];

        if ($number) {
            Session::initNavigateListItems(
                'PluginDlteamsDatabase',
                sprintf(
                    __('%1$s = %2$s'),
                    $item->getTypeName(1),
                    $item->getName()
                )
            );

            foreach ($databases as $data) {
                $databaseID = $data["id"];
                $link = NOT_AVAILABLE;

                if ($database->getFromDB($databaseID)) {
                    $link = $database->getLink();
                }

                Session::addToNavigateListItems('PluginDlteamsDatabase', $databaseID);

                $used[$databaseID] = $databaseID;

                echo "<tr class='tab_bg_1" . ($data["is_deleted"] ? "_2" : "") . "'>";
                if ($canedit && ($withtemplate < 2)) {
                    echo "<td width='10'>";
                    Html::showMassiveActionCheckBox(__CLASS__, $data["linkid"]);
                    echo "</td>";
                }
                echo "<td class='left'>$link</td>";
                echo "<td class='left'>";
                echo Dropdown::getDropdownName(
                    "glpi_plugin_dlteams_databasetypes",
                    $data["id"]
                );
                echo "</td>";
                echo "<td class='left'>" . $data["comment"] . "</td>";
                if (Session::isMultiEntitiesMode()) {
                    echo "<td class='left'>" . Dropdown::getDropdownName("glpi_entities", $data['entities_id']) .
                        "</td>";
                }
                /*echo "<td class='center'>" . $data["dns_suffix"] . "</td>";
                echo "<td class='center'>" . Html::convDate($data["date_creation"]) . "</td>";
               if (
                   $data["date_expiration"] <= date('Y-m-d')
                    && !empty($data["date_expiration"])
               ) {
                    echo "<td class='center'>";
                    echo "<div class='deleted'>" . Html::convDate($data["date_expiration"]) . "</div>";
                    echo "</td>";
               } else if (empty($data["date_expiration"])) {
                   echo "<td class='center'>" . __('Does not expire') . "</td>";
               } else {
                   echo "<td class='center'>" . Html::convDate($data["date_expiration"]) . "</td>";
               }
               echo "<td class='center'>";
               echo Dropdown::getDropdownName("glpi_states", $data["states_id"]);*/
                echo "</td>";
                echo "</tr>";
                $i++;
            }
        }

        echo "</table>";
        if ($canedit && $number && ($withtemplate < 2)) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
        }
        echo "</div>";
    }
}
