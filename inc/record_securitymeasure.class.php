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


class PluginDlteamsRecord_SecurityMeasure extends CommonDBTM
{
    static public $itemtype_1 = 'PluginDlteamsRecord';
    static public $items_id_1 = 'records_id';
    static public $itemtype_2 = PluginDlteamsRecord::class;

    static public $column1_id = '42';
    static public $column2_id = '43';
//    static public $tables_of = ['PluginDlteamsRecord_SecurityMeasure' => 'glpi_plugin_dlteams_records_items'];
//    protected static $tables_of = array(
//        'PluginDlteamsRecord_SecurityMeasure' => 'glpi_plugin_dlteams_records_items'
//    );

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
        return __("Data security and confidentiality", 'dlteams');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$item->canView()) {
            return false;
        }

        switch ($item->getType()) {
            case PluginDlteamsRecord::class :

                $nb = 0;
                /*if ($_SESSION['glpishow_count_on_tabs']) {
                   $nb = self::countForItem($item);
                }*/
                $nb = self::countSpecificItems($item, [PluginDlteamsProtectiveMeasure::getType()]);
                return self::createTabEntry(__("Protection tab", 'dlteams'), $nb);
        }

        return '';
    }

    public static function countSpecificItems($item, $itemtypes = [])
    {
        $dbu = new DbUtils();
        $count = 0;
        foreach ($itemtypes as $itemtype) {
            $count += $dbu->countElementsInTable('glpi_plugin_dlteams_records_items', ['records_id' => $item->getID(), 'itemtype' => 'PluginDlteamsProtectiveMeasure']);
        }
        return $count;
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case PluginDlteamsRecord::class :
                self::showForRecord($item, $withtemplate);
                break;
        }

        return true;
    }



    public function __construct()
    {
        static::forceTable('glpi_plugin_dlteams_records_items');
    }


    static function showForRecord(PluginDlteamsRecord $record, $withtemplate = 0)
    {
        $id = $record->fields['id'];
        if (!$record->can($id, READ)) {
            return false;
        }
        $canedit = $record->can($id, UPDATE);
        $rand = mt_rand(1, mt_getrandmax());
        global $CFG_GLPI;
        global $DB;


        // $iterator = $DB->request(PluginDlteamsAllItem::getRequestForItems($record,static::$itemtype_2,['id','name','content']));
        $iterator = $DB->request(self::getRequest($record));

        $results = $DB->request(static::getRequestForImpacts($record));
        $resultsimpactorganisms = $DB->request(static::getRequestForImpacts($record, true));
        $used_result = [];
        $used_result_impactorganism = [];
        // while ($data = $results->next()) {
        foreach ($results as $id => $data) {
            $used_result[$data['id']] = $data['id'];
        }
        //while ($dataimpactorganism = $resultsimpactorganisms->next()) {
        foreach ($resultsimpactorganisms as $id => $dataimpactorganism) {
            $used_result_impactorganism[$dataimpactorganism['id']] = $dataimpactorganism['id'];
        }
        //print_r($used_result);die;


        $number = count($iterator);

        $items_list = [];
        $used = [];

        foreach ($iterator as $id => $data) {
            //while ($data = $iterator->next()) {
            if ($data['id']) {
                $items_list[$data['linkid']] = $data;
                $used[$data['id']] = $data['id'];
            }
        }
        //var_dump($used);die();


        //record personal datacategory begin
        echo "<div class='firstbloc'>";
        // echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
        // action='" . Toolbox::getItemTypeFormURL(PluginDlteamsRecord::class) . "'>";
        echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
      action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";

        // echo "<input type='hidden' name='update' value='1' />";
        $iden = $record->fields['id'];
        echo "<input type='hidden' name='plugin_dlteams_records_id' value='$iden' />";
        echo "<input type='hidden' name='plugin_dlteams_records_id' value='$iden' />";
        echo "<input type='hidden' name='id' value='$iden' />";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr><th colspan='3'>" . __("General questions", 'dlteams') . "</th></tr>";
        echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
        echo __("Personally sensitive", 'dlteams') .
            "<br><i>" . __("Could this processing be seen sensitive by persons", 'dlteams') . "</i>";
        echo "</td><td width='50%' class='left'>";
        echo "<input type='radio' name='sensitive' value='1' " .
            ($record->fields['sensitive'] ? "checked" : "") . "> " . __("Yes") . "<br>";
        echo "<input type='radio' name='sensitive' value='0' " .
            (!$record->fields['sensitive'] ? "checked" : "") . "> " . __("No") . "<br>";
        echo "</td></tr>";
        echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
        echo __("Permit profiling", 'dlteams') .
            "<br><i>" . __("Using personal data to analyse/predict behaviour in order to plan automated actions", 'dlteams') . "</i>";
        echo "</td><td width='50%' class='left'>";
        $rand = Dropdown::showYesNo("profiling", $record->fields['profiling']);
        // if yes, display automated profiling question
        $params = [
            'profiling' => '__VALUE__',
            'profiling_auto' => $record->fields['profiling_auto']??false
        ];
        Ajax::updateItemOnSelectEvent(
            "dropdown_profiling$rand",
            'profiling_auto_row',
            $CFG_GLPI['root_doc'] . '/marketplace/dlteams/ajax/record_profiling_auto_dropdown.php',
            $params
        );
        echo "</td></tr>";
        echo "<tr class='tab_bg_1' id='profiling_auto_row'>";
        self::showProfilingAuto($record->fields);
        echo "</tr>";
        if ($canedit) {
            echo "<tr><td colspan='2' class='center'>";
            echo "</td></tr>";
        }
        //record personal datacategory end
        //if ($canedit) {

        echo "<tr class='tab_bg_2'><th colspan='3'>" . self::getTypeName() . "</th></tr>";

        echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
        echo __("In case of a data leak, what would be the impact on people ?", 'dlteams') .
            "<br><i>" . __("Avoiding data leaks is an important objective of GDPR", 'dlteams') . "</i>";
        echo "</td>";
        echo "<td width='50%' class='left'>";


        PluginDlteamsImpact::dropdown([
            'addicon' => PluginDlteamsImpact::canCreate(),
            'name' => 'impact_person',
            'width' => '40%',
            'value' => empty($used_result) ? '0' : reset($used_result),
        ]);


        // new DropDdown to implement

        echo "</td></tr>";

        echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
        echo __("Which impact level could lead to a data violation", 'dlteams') .
            "<br><i>" . __("Protect data permit to reduce risks", 'dlteams') . "</i>";
        echo "</td>";
        echo "<td width='50%' class='left'>";
        PluginDlteamsImpact::dropdown([
            'addicon' => PluginDlteamsImpact::canCreate(),
            'name' => 'impact_organism',
            'width' => '40%',
            'value' => empty($used_result_impactorganism) ? '0' : reset($used_result_impactorganism),
        ]);
        /*       $checked = json_decode($record->fields['violation_impact_level'] ?? "{}", true);
              $choices [] = "<input type='text' placeholder='" . __("Other") .
                 "' name='violation_impact_level_other' id='violation_impact_level_other' value='" . ($checked['other'] ?? '') . "'>";
              echo PluginDlteamsUtils::displayCheckboxes($checked, $choices, "violation_impact_level", 'radio'); */
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
        echo __("Specific security measures", 'dlteams') .
            "<br><i>" . __("Detail the potential impacts retained concerning people and the organization", 'dlteams') . "</i>";
        echo "</td>";
        echo "<td width='50%' class='left'>";
        echo "<textarea name='specific_security_measures' cols='60' rows='4w' maxlength='1000' >" .
            ($record->fields['specific_security_measures'] ?? "") . "</textarea>";
        echo "</td></tr>";
        if ($canedit) {
            echo "<tr><td colspan='20%' class='center'>";
            echo "<input type='submit' name='update' value=\"" . _sx('button', 'Save') . "\" class='submit'>";
            echo "</td></tr>";
        }
        echo "</table>";
        Html::closeForm();

        echo "</div>";

        echo "<script>
                $(document).ready(function () {
                    $('select[name=items_id_m]').on('change', function () {
                        //alert($(this).val());
                        //
                        if ($(this).val() != '0') {
                            document.getElementById('btnProtective').style.display = 'block';
                            document.getElementById('inputProtective').style.display = 'block';
                            var content = $(this).val();
                            $.ajax({
                                url: 'getCommentProtectiveMeasure.php',
                                type: 'POST',
                                async: false,
                                data: {content: content},
                                success: function (data) {
                                    //alert(data);
                                    //userData = json.parse(data);
                                    //alert(json.parse(data));
                                    $('.storage_comment1').val(data);
                        
                    }
                    });
                    } else {
                        document.getElementById('btnProtective').style.display = 'none';
                        document.getElementById('inputProtective').style.display = 'none';
                    }
                    
                    });

                    $('select[name=items_id]').on('change', function () {
                        // alert($(this).val());
                        if ($(this).val() != '0') {
                            document.getElementById('update_comment_textarea').style.display = 'block';
                    
                        } else {
                            document.getElementById('update_comment_textarea').style.display = 'none';
                        }
                        //
                    });
                    });
                    </script>";


//        PluginDlteamsProtectiveMeasure_Item::showForRecords($record, true);
        global $DB;
        $instID = $record->fields['id'];
        if (!$record->can($instID, READ)) {
            return false;
        }
        $canedit = $record->can($instID, UPDATE);
        // for a measure,
        // don't show here others protective measures associated to this one,
        // it's done for both directions in self::showAssociated
        $types_iterator = [];
        $number = count($types_iterator);

        $used = [];
        $types = PluginDlteamsItemType::getTypes();
        $key = array_search(get_class($record), $types);
        unset($types[$key]);
        $rand = mt_rand();

        $items = $DB->request([
            'FROM' => 'glpi_plugin_dlteams_records_items',
            'SELECT' => [
                'glpi_plugin_dlteams_records_items.id',
                'glpi_plugin_dlteams_records_items.id as linkid',
                'glpi_plugin_dlteams_records_items.comment',
                'glpi_plugin_dlteams_records_items.itemtype as itemtype',
                'glpi_plugin_dlteams_records_items.items_id as items_id',
                'glpi_plugin_dlteams_protectivetypes.name AS typename',
                'glpi_plugin_dlteams_protectivecategories.name as nameCat',
                'glpi_plugin_dlteams_protectivemeasures.name as name',
                'glpi_plugin_dlteams_protectivemeasures.content as content',
                'glpi_plugin_dlteams_protectivemeasures.id as pm_id',
            ],
            'WHERE' => [
                'glpi_plugin_dlteams_records_items.itemtype' => PluginDlteamsProtectiveMeasure::class,
                'glpi_plugin_dlteams_records_items.records_id' => $instID,
            ],
            'LEFT JOIN' => [
                'glpi_plugin_dlteams_protectivemeasures' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_records_items' => 'items_id',
                        'glpi_plugin_dlteams_protectivemeasures' => 'id'
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
            'ORDER' => ['typename ASC', 'nameCat ASC', 'nameCat ASC'],
        ]);

        $items = iterator_to_array($items);
        foreach ($items as $data) {
            $used[$data['pm_id']] = $data['pm_id'];
        }

/*        highlight_string("<?php\n\$data =\n" . var_export($used, true) . ";\n?>");*/
//        die();

        if ($canedit) {
            echo "<div class='firstbloc'>";
            echo "<form name='protectivemeasureitem_form$rand' id='protectivemeasureitem_form$rand' method='post'
               action='" . Toolbox::getItemTypeFormURL(PluginDlteamsProtectiveMeasure_Item::class) . "'>";

            echo "<table class='tab_cadre_fixe'>";
			// In addition to the general measures, what specifics measures have been taken ?
            echo "<tr class='tab_bg_2'><th colspan='4'>" . __("In addition to the general measures, specifics measures taken", 'dlteams') . "</th></tr>";
            echo "<tr class='tab_bg_1'>";
            echo "<td width='15%;'>";
            echo "<label>" . __("Add security measures", 'dlteams') . "</label>";
            echo "</td>";
            echo "<td width='25px;' class='right'>";

            Dropdown::show(PluginDlteamsProtectiveMeasure::class, [
                'addicon' => PluginDlteamsProtectiveMeasure::canCreate(),
                'name' => 'items_id',
                'entity'   => $_SESSION['glpiactive_entity'],
                'used' => $used,
                'url' => $CFG_GLPI['root_doc'] . "/marketplace/dlteams/ajax/getDropdownValue.php"
            ]);
            echo "</td>";
            echo "<td>";
            echo "<textarea type='text' style='width:100%;margin-right:5%; display:none;margin-bottom: 10px;' maxlength=1000 rows='3' id='update_comment_textarea' name='comment' class='storage_comment1' placeholder='commentaire'></textarea>";
            echo "</td>";
            echo "<td class='center'>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='btn btn-primary'>";
            echo "<input type='hidden' name='itemtype' value='" . str_replace("_Item", "", PluginDlteamsProtectiveMeasure_Item::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . PluginDlteamsRecord::class . "'>";
            echo "<input type='hidden' name='items_id1' value='" . $instID . "'>";
            echo "</td>";
            echo "</tr>";
            echo "</table>";
            Html::closeForm();
            echo "</div>";
        }
//        $link_table = str_replace("_Item", "", PluginDlteamsProtectiveMeasure_Item::class);


        if (!count($items)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        }
        else {
            if ($canedit) {
                Html::openMassiveActionsForm('mass' . PluginDlteamsRecord_SecurityMeasure::class . $rand);
                $massiveactionparams = [
                    'num_displayed' => min($_SESSION['glpilist_limit'], count($items)),
                    'container' => 'mass' . PluginDlteamsRecord_SecurityMeasure::class . $rand
                ];
                Html::showMassiveActions($massiveactionparams);
            }

            echo "<table class='tab_cadre_fixehov'>";
            $header = "<tr>";
            if ($canedit) {
                $header .= "<th width='10'>";
                $header .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsRecord_SecurityMeasure::class . $rand);
                $header .= "</th>";
            }
            $header .= "<th>" . __("Name") . "</th>";
            $header .= "<th>" . __("Content") . "</th>";
            $header .= "<th>" . __("Type") . "</th>";
            $header .= "<th>" . __("Category") . "</th>";
            $header .= "<th>" . __("Comment") . "</th>";
            $header .= "</tr>";
            echo $header;

            foreach ($items as $row) {
/*                highlight_string("<?php\n\$data =\n" . var_export($row, true) . ";\n?>");*/

                $item = new PluginDlteamsProtectiveMeasure();
                $item->getFromDB($row['pm_id']);
                $name = "<a target='_blank' href=\"" . $item::getFormURLWithID($item->getField('id')) . "\">" . $item->getField('name') . "</a>";
                echo "<tr lass='tab_bg_1'>";
                if ($canedit) {
                    echo "<td>";
                    Html::showMassiveActionCheckBox(PluginDlteamsRecord_SecurityMeasure::class, $row["id"]);
                    echo "</td>";
                }
                echo "<td>" . $name . "</td>";
                echo "<td>" . $row['content'] . "</td>";
                echo "<td>" . $row['typename'] . "</td>";
                echo "<td>" . $row['nameCat'] . "</td>";
                echo "<td>" . $row['comment'] . "</td>";
                echo "</tr>";
            }
//            echo $header;
            echo "</table>";

            if ($canedit) {
                Html::closeForm();
            }

        }
    }

    static function getListForItem(CommonDBTM $item)
    {

        global $DB;

        $params = static::getListForItemParams($item, true);
        $iterator = $DB->request($params);

        return $iterator;
    }

    static function getSelectedValue($record)
    {
        global $DB;
        $results = $DB->request('glpi_plugin_dlteams_records_impacts', ['FIELDS' => 'id']);
        return $results;
    }

    /*   public function getForbiddenStandardMassiveAction()
        {
            $forbidden   = parent::getForbiddenStandardMassiveAction();
            $forbidden[] = 'update';
            $forbidden[] = 'purge';
            $forbidden[] = 'delete';
            $forbidden[] = 'add_transfer_list';
            $forbidden[] = 'add_note';
            $forbidden[] = 'Document_Item:add';
            $forbidden[] = 'Document_Item:remove';
            $forbidden[] = 'Contract_Item:add';
            $forbidden[] = 'Contract_Item:remove';
            $forbidden[] = 'Infocom:activate';
            $forbidden[] = 'MassiveAction:amend_comment';
            return $forbidden;
        }*/


    /**
     * Export in an array all the data of the current instanciated PluginDlteamsRecord_SecurityMeasure
     * @param CommonDBTM $record record linked to impacts
     * @param boolean $impactorganism impact or impactorganism ?
     *
     * @return array the array with all data (with sub tables)
     */
    static function getRequestForImpacts($record, bool $immpactorganism = false)
    {
        return [
            'SELECT' => [
                'glpi_plugin_dlteams_records.id AS linkid',
                'glpi_plugin_dlteams_impacts.id AS id',

            ],
            'FROM' => 'glpi_plugin_dlteams_records',
            'LEFT JOIN' => [
                'glpi_plugin_dlteams_impacts' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_records' => $immpactorganism ? "impact_organism" : "impact_person",
                        'glpi_plugin_dlteams_impacts' => "id",
                    ]
                ],
            ],
            'WHERE' => [
                'glpi_plugin_dlteams_records.id' => $record->fields['id']
            ]
        ];
    }

    function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id' => 'protective',
            'name' => PluginDlteamsProtectiveMeasure::getTypeName(0)
        ];

        $tab[] = [
            'id' => static::$column1_id,
            'table' => PluginDlteamsProtectiveMeasure::getTable(),
            'field' => 'name',
            'name' => __("Name"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'dropdown',
            'searchtype' => ['equals', 'notequals'],
            'joinparams' => [
                'beforejoin' => [
                    'table' => PluginDlteamsRecord_Item::getTable(),
                    'joinparams' => [
                        'jointype' => 'left',
                        'alias' => 'items',
                        'joincondition' => [
                            [
                                'fieldleft' => 'items.items_id',
                                'fieldright' => PluginDlteamsProtectiveMeasure::getTable() . '.id',
                            ]
                        ]
                    ]
                ],
                'afterjoin' => [
                    'table' => 'glpi_plugin_dlteams_records_items',
                    'joinparams' => [
                        'jointype' => 'left',
                        'alias' => 'record_items',
                        'joincondition' => [
                            [
                                'fieldleft' => 'record_items.id',
                                'fieldright' => 'items.itemtype_id',
                            ]
                        ],
                        'update' => [
                            'items_id' => [
                                'datatype' => 'itemlink',
                                'massiveaction' => true,
                                'itemlink_type' => 'PluginDlteamsRecord_Item',
                                'itemlink_field' => 'id',
                                'itemlink_foreignkey' => 'items_id',
                            ],
                        ],
                    ]
                ]
            ],
            'addwhere' => [
                [
                    'link' => 'AND',
                    'field' => 'record_items.id',
                    'searchtype' => 'isnotempty',
                ]
            ],
        ];

        $tab[] = [
            'id' => static::$column2_id,
            'table' => PluginDlteamsRecord_Item::getTable(),
            'field' => 'comment',
            'name' => __("Comment"),
            'datatype' => 'text',
            'forcegroupby' => true,
            'massiveaction' => true,
        ];

        return $tab;
    }

    /*add by me**/
//    function getSpecificMassiveActions($checkitem = NULL)
//    {
//
//        $actions['Contact_Suppliervv' . MassiveAction::CLASS_ACTION_SEPARATOR . 'add']
//            = _x('button', 'Add a supplier test');
//        $class = __CLASS__;
//        $action_key = "delete_dlteams_action";
//        $action_label = __("Delete dlteams relations");
//        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;
//        $actions = parent::getSpecificMassiveActions($checkitem);
//        return $actions;
//    }

//    function getSpecificMassiveActions($checkitem = NULL)
//    {
//        // add a single massive action
//        $class = __CLASS__;
//
//        $action_key = "update_dlteams_action";
//        $action_label = __("Update dlteams relations", "dlteams");
//        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;
//
//
//        $action_key = "delete_dlteams_action";
//        $action_label = _n("Delete dlteams relation", "Delete dlteams relations", 0, "dlteams");
//        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;
//
//        return $actions;
//    }

//    static function showMassiveActionsSubForm(MassiveAction $ma)
//    {
//        switch ($ma->getAction()) {
//            case 'update_dlteams_action':
////                var_dump($ma->POST);
//                if (!isset($ma->POST['id_field'])) {
//                    $itemtypes = array_keys($ma->items);
//                    $options_per_type = [];
//                    $options_counts = [];
//                    foreach ($itemtypes as $itemtype) {
//                        $options_per_type[$itemtype] = [];
//                        $group = '';
//                        $show_all = true;
//                        $show_infocoms = true;
//                        $itemtable = getTableForItemType($itemtype);
//
//                        if (
//                            Infocom::canApplyOn($itemtype)
//                            && (!$itemtype::canUpdate()
//                                || !Infocom::canUpdate())
//                        ) {
//                            $show_all = false;
//                            $show_infocoms = Infocom::canUpdate();
//                        }
//                        foreach (Search::getCleanedOptions($itemtype, UPDATE) as $index => $option) {
//                            if (!is_array($option) || count($option) == 1) {
//                                $group = !is_array($option) ? $option : $option['name'];
//                                $options_per_type[$itemtype][$group] = [];
//                            } else {
//                                if (
//                                    ($option['field'] != 'id')
//                                    && ($index != 1)
//                                    // Permit entities_id is explicitly activate
//                                    && (($option["linkfield"] != 'entities_id')
//                                        || (isset($option['massiveaction']) && $option['massiveaction']))
//                                ) {
//                                    if (!isset($option['massiveaction']) || $option['massiveaction']) {
//                                        if (
//                                            ($show_all)
//                                            || (($show_infocoms
//                                                    && Search::isInfocomOption($itemtype, $index))
//                                                || (!$show_infocoms
//                                                    && !Search::isInfocomOption($itemtype, $index)))
//                                        ) {
//                                            $options_per_type[$itemtype][$group][$itemtype . ':' . $index]
//                                                = $option['name'];
//                                            if ($itemtable == $option['table']) {
//                                                $field_key = 'MAIN:' . $option['field'] . ':' . $index;
//                                            } else {
//                                                $field_key = $option['table'] . ':' . $option['field'] . ':' . $index;
//                                            }
//                                            if (!isset($options_count[$field_key])) {
//                                                $options_count[$field_key] = [];
//                                            }
//                                            $options_count[$field_key][] = $itemtype . ':' . $index . ':' . $group;
//                                            if (isset($option['MA_common_field'])) {
//                                                if (!isset($options_count[$option['MA_common_field']])) {
//                                                    $options_count[$option['MA_common_field']] = [];
//                                                }
//                                                $options_count[$option['MA_common_field']][]
//                                                    = $itemtype . ':' . $index . ':' . $group;
//                                            }
//                                        }
//                                    }
//                                }
//                            }
//                        }
//                    }
//
//                    if (count($itemtypes) > 1) {
//                        $common_options = [];
//                        foreach ($options_count as $field => $users) {
//                            if (count($users) > 1) {
//                                $labels = [];
//                                foreach ($users as $user) {
//                                    $user = explode(':', $user);
//                                    $itemtype = $user[0];
//                                    $index = $itemtype . ':' . $user[1];
//                                    $group = implode(':', array_slice($user, 2));
//                                    if (isset($options_per_type[$itemtype][$group][$index])) {
//                                        if (
//                                        !in_array(
//                                            $options_per_type[$itemtype][$group][$index],
//                                            $labels
//                                        )
//                                        ) {
//                                            $labels[] = $options_per_type[$itemtype][$group][$index];
//                                        }
//                                    }
//                                    $common_options[$field][] = $index;
//                                }
//                                $options[$group][$field] = implode('/', $labels);
//                            }
//                        }
//                        $choose_itemtype = true;
//                        $itemtype_choices = [-1 => Dropdown::EMPTY_VALUE];
//                        foreach ($itemtypes as $itemtype) {
//                            $itemtype_choices[$itemtype] = $itemtype::getTypeName(Session::getPluralNumber());
//                        }
//                    } else {
//                        $options = $options_per_type[$itemtypes[0]];
//                        $common_options = false;
//                        $choose_itemtype = false;
//                    }
//                    $choose_field = is_countable($options) ? (count($options) >= 1) : false;
//
//                    // Beware: "class='tab_cadre_fixe'" induce side effects ...
//                    echo "<table width='100%'><tr>";
//
//                    $colspan = 0;
//                    if ($choose_field) {
//                        $colspan++;
//                        echo "<td>";
//                        if ($common_options) {
//                            echo __('Select the common field that you want to update');
//                        } else {
//                            echo __('Select the field that you want to update');
//                        }
//                        echo "</td>";
//                        if ($choose_itemtype) {
//                            $colspan++;
//                            echo "<td rowspan='2'>" . __('or') . "</td>";
//                        }
//                    }
//
//                    if ($choose_itemtype) {
//                        $colspan++;
//                        echo "<td>" . __('Select the type of the item on which applying this action') . "</td>";
//                    }
//
//                    echo "</tr><tr>";
//                    // Remove empty option groups
//                    $options = array_filter($options, static function ($v) {
//                        return !is_array($v) || count($v) > 0;
//                    });
//                    if ($choose_field) {
//                        echo "<td>";
//                        $field_rand = Dropdown::showFromArray(
//                            'id_field',
//                            $options,
//                            ['display_emptychoice' => true]
//                        );
//                        echo "</td>";
//                    }
//                    if ($choose_itemtype) {
//                        echo "<td>";
//                        $itemtype_rand = Dropdown::showFromArray(
//                            'specialize_itemtype',
//                            $itemtype_choices
//                        );
//                        echo "</td>";
//                    }
//
//                    $next_step_rand = mt_rand();
//
//                    echo "</tr></table>";
//                    echo "<span id='update_next_step$next_step_rand'>&nbsp;</span>";
//
//                    if ($choose_field) {
//                        $params = $ma->POST;
//                        $params['id_field'] = '__VALUE__';
//                        $params['common_options'] = $common_options;
//                        Ajax::updateItemOnSelectEvent(
//                            "dropdown_id_field$field_rand",
//                            "update_next_step$next_step_rand",
//                            $_SERVER['REQUEST_URI'],
//                            $params
//                        );
//                    }
//
//                    if ($choose_itemtype) {
//                        $params = $ma->POST;
//                        $params['specialize_itemtype'] = '__VALUE__';
//                        $params['common_options'] = $common_options;
//                        Ajax::updateItemOnSelectEvent(
//                            "dropdown_specialize_itemtype$itemtype_rand",
//                            "update_next_step$next_step_rand",
//                            $_SERVER['REQUEST_URI'],
//                            $params
//                        );
//                    }
//                    // Only display the form for this stage
//                    exit();
//                }
//
//                if (!isset($ma->POST['common_options'])) {
//                    echo "<div class='center'><img src='" . $CFG_GLPI["root_doc"] . "/pics/warning.png' alt='" .
//                        __s('Warning') . "'><br><br>";
//                    echo "<span class='b'>" . __('Implementation error!') . "</span><br>";
//                    echo "</div>";
//                    exit();
//                }
//
//                if ($ma->POST['common_options'] == 'false') {
//                    $search_options = [$ma->POST['id_field']];
//                } else if (isset($ma->POST['common_options'][$ma->POST['id_field']])) {
//                    $search_options = $ma->POST['common_options'][$ma->POST['id_field']];
//                } else {
//                    $search_options = [];
//                }
//
//                // TODO: ensure that all items are equivalent ...
//                $item = null;
//                $search = null;
//                foreach ($search_options as $search_option) {
//                    $search_option = explode(':', $search_option);
//                    $so_itemtype = $search_option[0];
//                    $so_index = $search_option[1];
//
//                    if (!$so_item = getItemForItemtype($so_itemtype)) {
//                        continue;
//                    }
//
//                    if (Infocom::canApplyOn($so_itemtype)) {
//                        Session::checkSeveralRightsOr([$so_itemtype => UPDATE,
//                            "infocom" => UPDATE
//                        ]);
//                    } else {
//                        $so_item->checkGlobal(UPDATE);
//                    }
//
//                    $itemtype_search_options = Search::getOptions($so_itemtype);
//                    if (!isset($itemtype_search_options[$so_index])) {
//                        exit();
//                    }
//
//                    $item = $so_item;
//                    $search = $itemtype_search_options[$so_index];
//                    break; // No need to process all items a corresponding item/searchoption has been found
//                }
//
//                if ($item === null) {
//                    exit();
//                }
//
//                $plugdisplay = false;
//                if (
//                    ($plug = isPluginItemType($item->getType()))
//                    // Specific for plugin which add link to core object
//                    || ($plug = isPluginItemType(getItemTypeForTable($search['table'])))
//                ) {
////                    $plugdisplay = Plugin::doOneHook(
////                        $plug['plugin'],
////                        'MassiveActionsFieldsDisplay',
////                        ['itemtype' => $item->getType(),
////                            'options'  => $search
////                        ]
////                    );
//                }
//
//                if (
//                    empty($search["linkfield"])
//                    || ($search['table'] == 'glpi_infocoms')
//                ) {
//                    $fieldname = $search["field"];
//                } else {
//                    $fieldname = $search["linkfield"];
//                }
//
//                if (!$plugdisplay) {
//                    $options = [];
//                    $values = [];
//                    // For ticket template or aditional options of massive actions
//                    if (isset($ma->POST['options'])) {
//                        $options = $ma->POST['options'];
//                    }
//                    switch ($item->getType()) {
//                        case 'Change':
//                            $search['condition'][] = 'is_change';
//                            break;
//                        case 'Problem':
//                            $search['condition'][] = 'is_problem';
//                            break;
//                        case 'Ticket':
//                            if ($DB->fieldExists($search['table'], 'is_incident') || $DB->fieldExists($search['table'], 'is_request')) {
//                                $search['condition'][] = [
//                                    'OR' => [
//                                        'is_incident',
//                                        'is_request'
//                                    ]
//                                ];
//                            }
//                            break;
//                    }
//                    if (isset($ma->POST['additionalvalues'])) {
//                        $values = $ma->POST['additionalvalues'];
//                    }
//                    $values[$search["field"]] = '';
//                    echo $item->getValueToSelect($search, $fieldname, $values, $options);
//                }
//
//                $items_index = [];
//                foreach ($search_options as $search_option) {
//                    $search_option = explode(':', $search_option);
//                    $items_index[$search_option[0]] = $search_option[1];
//                }
////                New lines added for dlteams
////            baseitem est l'item de la table de base. glpi_plugin_dlteams_records_items pour le cas present
//                echo Html::hidden('baseitem', ['value' => PluginDlteamsRecord_Item::class]);
//                echo Html::hidden('itemtype', ['value' => 'PluginDlteamsStoragePeriod']);
////            end of new lines addes for dlegister
//                echo Html::hidden('search_options', ['value' => $items_index]);
//                echo Html::hidden('field', ['value' => $fieldname]);
//                echo "<br>\n";
//
//                $submitname = "<i class='fas fa-save'></i><span>" . _sx('button', 'Post') . "</span>";
//                if (isset($ma->POST['submitname']) && $ma->POST['submitname']) {
//                    $submitname = stripslashes($ma->POST['submitname']);
//                }
//                echo Html::submit($submitname, [
//                    'name' => 'massiveaction',
//                    'class' => 'btn btn-sm btn-primary',
//                ]);
//
//
//                return true;
//        }
//        return parent::showMassiveActionsSubForm($ma);
//    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
//        $forbidden[] = 'MassiveAction:purge';
//        $forbidden[] = 'MassiveAction:update';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
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
            "comment" => $this->oldvalues["comment"]
        ];


        $relation_item->deleteByCriteria($criteria);
        $relation_item->add([
            ...$criteria,
            "comment" => $this->fields["comment"]
        ]);
    }

    public function post_purgeItem()
    {
        global $DB;
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


        $DB->delete($relation_item->getTable(), $criteria);

//        $relation_item->deleteByCriteria($criteria);
    }

    /**
     * @param MassiveAction $ma
     * @param CommonDBTM $item
     * @param array $ids
     * @return false|void
     */
//    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
//    {
//
//        switch ($ma->getAction()) {
//            case 'delete_dlteams_action':
//                foreach ($ids as $id) {
////                    $item = new Rec()
//                    $item = new PluginDlteamsRecord_Item();
//                    if ($item->getFromDB($id)) {
//                        $plural_item_name = strtolower(str_replace("PluginDlteams", "", $item->getField('itemtype'))) . "s_id";
//                        $relationTable = CommonDBRelation::getTable(
//                            $item->getField('itemtype')
//                        );
//
//
//                        // suppression universelle de l'item lié
//                        global $DB;
//
//                        $DB->delete(
//                            $relationTable . '_items',
//                            array(
//                                'itemtype' => str_replace("_Item", "", $item->getType()),
//                                $plural_item_name => $item->getField('items_id'),
//                                'items_id' => $item->getField(strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $item->getType()))) . "s_id")
//                            )
//                        );
//
//                        // suppression de l'item
//                        $item->delete(array('id' => $id));
//                        $ma->__set("remainings", ["PluginDlteamsRecord_Item" => [$id => $id]]);
//                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
//                    } else {
//                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
//                        $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
//                    }
//                }
//                break;
//
//            case 'update_dlteams_action':
//                $temp = new PluginDlteamsThirdPartyCategory_Item();
//                $updatable_columns = $temp->rawSearchOptions();
//                $parts = explode(':', $ma->POST['id_field']);
//                $id_modif = $parts[1];
//
//
//                switch ($id_modif) {
//                    case static::$column1_id:
//                        foreach ($ids as $id) {
//                            $base_item = new PluginDlteamsRecord_Item();
//                            if ($base_item->getFromDB($id)) {
////                                var_dump($base_item->fields);
//                                $array_values = array_values($ma->POST);
//
//                                $itemtype_item = new PluginDlteamsProtectiveMeasure_Item();
//                                $itemtype_item_id_column = "protectivemeasures_id";
//
//                                $t = $itemtype_item->deleteByCriteria([
//                                    $itemtype_item_id_column => $base_item->fields["items_id"],
//                                    "itemtype" => "PluginDlteamsRecord",
//                                    "items_id" => $base_item->fields["records_id"],
//                                ]);
//
//                                //                                ajout de la nouvelle ligne
//                                $ee = $itemtype_item->add([
//                                    $itemtype_item_id_column => $array_values[1],
//                                    "itemtype" => "PluginDlteamsRecord",
//                                    "items_id" => $base_item->fields["records_id"],
//                                    "comment" => htmlspecialchars($base_item->fields["comment"])
//                                ]);
//
//                                global $DB;
//
//                                $t = $DB->update(
//                                    $base_item->getTable(),
//                                    [
//                                            "items_id" => $array_values[1],
//                                    ],
//                                    ['id' => $id],
//                                );
//                            }
//                        }
//                        break;
//                    case static::$column2_id:
//
//                        foreach ($ids as $id) {
//                            $base_item = new PluginDlteamsRecord_Item();
//                            if ($base_item->getFromDB($id)) {
//                                $array_values = array_values($ma->POST);
//
//                                $itemtype_item = new PluginDlteamsProtectiveMeasure_Item();
//                                $itemtype_item_id_column = "protectivemeasures_id";
//
//                                $t = $itemtype_item->deleteByCriteria([
//                                    $itemtype_item_id_column => $base_item->fields["items_id"],
//                                    "itemtype" => "PluginDlteamsRecord",
//                                    "items_id" => $base_item->fields["records_id"],
//                                ]);
//
//                                //                                ajout de la nouvelle ligne
//                                $ee = $itemtype_item->add([
//                                    $itemtype_item_id_column => $base_item->fields["items_id"],
//                                    "itemtype" => "PluginDlteamsRecord",
//                                    "items_id" => $base_item->fields["records_id"],
//                                    "comment" => $array_values[1]
//                                ]);
//
//                                global $DB;
//                                $t = $DB->update(
//                                    $base_item->getTable(),
//                                    [
//                                        "comment" => $array_values[1],
//                                    ],
//                                    ['id' => $id],
//                                );
//                            }
//                        }
//                        break;
//
//                }
//                $ma->__set("remainings", ["PluginDlteamsRecord_Item" => [$id => $id]]);
//                $ma->itemDone('PluginDlteamsRecord_Item', $id, MassiveAction::ACTION_OK);
//                break;
//        }
//
//
//        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
//    }
    /**add by me**/

    // show table list
    static function getRequest($record)
    {

        return [
            'SELECT' => [
                'glpi_plugin_dlteams_protectivemeasures_items.id AS linkid',
                'glpi_plugin_dlteams_protectivemeasures.id AS id',
                'glpi_plugin_dlteams_protectivemeasures.name AS name',
                'glpi_plugin_dlteams_protectivemeasures.plugin_dlteams_protectivetypes_id AS type',
                'glpi_plugin_dlteams_protectivetypes.name AS typename',
                'glpi_plugin_dlteams_protectivecategories.name as nameCat',
                //'glpi_plugin_dlteams_protectivemeasures.content AS content',
                'glpi_plugin_dlteams_protectivemeasures_items.comment AS comment',
            ],
            'FROM' => 'glpi_plugin_dlteams_protectivemeasures_items',
            'LEFT JOIN' => [
                'glpi_plugin_dlteams_protectivemeasures' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_protectivemeasures_items' => "protectivemeasures_id",
                        'glpi_plugin_dlteams_protectivemeasures' => "id",
                    ]
                ],
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
            'ORDER' => [ /*'glpi_plugin_dlteams_legalbasistypes.id ASC',*/ 'name ASC'],
            'WHERE' => [
                'glpi_plugin_dlteams_protectivemeasures_items.items_id' => $record->fields['id']
            ]
        ];
    }



    /*function rawSearchOptions(){

       $tab = [];

       /*$tab[] = [
          'id' => 'securitymeasure',
          'name' => PluginGenericobjectMesuressecurite::getTypeName(0)
       ];*/

    /*$tab[] = [
       'id' => '51',
       'table' => PluginGenericobjectMesuressecurite::getTable(),
       'field' => 'name',
       'name' => __("Mesure de protection"),
       'forcegroupby' => true,
       'massiveaction' => true,
       'datatype' => 'dropdown',
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
 }*/

    static function showProfilingAuto($data = [])
    {
        //var_dump($data['profiling']);
        if ($data['profiling'] == 1) {
            echo "<td class='right'>" .
                __("Is there a fully automated process involved (without human intervention)? Precise", 'dlteams') .
                "</td>";
            echo "<td colspan='2'>";
            $profiling_auto = Html::cleanInputText($data['profiling_auto']);
            echo "<textarea style='width: 98%;' name='profiling_auto' maxlength='1000' rows='3'>" . $profiling_auto . "</textarea>";
            echo "</td>";
        } else if ($data['profiling'] == 0) {

        }
    }

    /**
     * Export in an array all the data of the current instanciated PluginDlteamsRecord_SecurityMeasure
     * @param boolean $remove_uuid remove the uuid key
     *
     * @return array the array with all data (with sub tables)
     */
    public function exportToDB($remove_uuid = false, $subItems = [])
    {
        if ($this->isNewItem()) {
            return false;
        }

        $security_measures = $this->fields;
        return $security_measures;
    }

    public static function importToDB(PluginDlteamsLinker $linker, $input = [], $record_id = 0, $subItems = [])
    {
        $recordFk = PluginDlteamsRecord::getForeignKeyField();
        $input[$recordFk] = $record_id;

        $item = new self();
        $originalId = $input['id'];
        unset($input['id']);
        $itemId = $item->add($input);
        if ($itemId === false) {
            $typeName = strtolower(self::getTypeName());
            throw new ImportFailureException(sprintf(__('failed to copy the %1$s record', 'dlteams'), $input['name']));
        }
        $linker->addObject($originalId, $item);
        return $itemId;
    }

    public function cleanDBonPurge()
    {
        $this->deleteChildrenAndRelationsFromDb(
            [
                Appliance_Item_Relation::class,
            ]
        );
    }

    public function deleteObsoleteItems(CommonDBTM $container, array $exclude)
    {
    }


}

?>


