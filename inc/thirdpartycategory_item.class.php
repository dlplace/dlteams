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

class PluginDlteamsThirdPartyCategory_Item extends CommonDBRelation
{

    public static $itemtype_1 = 'PluginDlteamsThirdPartyCategory';
    public static $items_id_1 = 'thirdpartycategories_id';
    public static $take_entity_1 = false;

    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';
    public static $take_entity_2 = true;

    public static $column1_id = "31";
    public static $column2_id = "32";
    public static $column4_id = "34";

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
        return __("Third party categories", 'dlteams');
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $nbdoc = $nbitem = 0;
        switch ($item->getType()) {
            case 'PluginDlteamsThirdPartyCategory':
                $ong = self::createTabEntry(_n(
                    'Associated item',
                    'Associated items',
                    Session::getPluralNumber()
                ), $nbitem);
                return $ong;

            default:

                if ($_SESSION['glpishow_count_on_tabs']) {
                    $nbitem = self::countForItem($item);
                }
                return static::createTabEntry(static::getTypeName(Session::getPluralNumber()), $nbitem);
        }
    }



    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'PluginDlteamsThirdPartyCategory':
                self::showItems($item);
                break;
            default:
//                self::showForitem($item, $withtemplate);
                self::showItemsForItemType($item);
                break;
        }
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
    public static function showItems(PluginDlteamsThirdPartyCategory $object_item)
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
        $key = array_search("PluginDlteamsLegalBasi", $types);
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

            echo "<tr class='tab_bg_1'><td class='left' width='40%'>". __($entitled, 'dlteams');
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

    public static function getItemsRequest(CommonDBTM $object_item)
    {
        global $DB;
        $link_table = str_replace("_Item", "", __CLASS__);
        $temp = new $link_table();

//        var_dump($object_item->fields['id']);
//        die();
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
//            'JOIN' => [
//                'glpi_plugin_dlteams_protectivetypes' => [
//                    'FKEY' => [
//                        'glpi_plugin_dlteams_protectivemeasures' => "plugin_dlteams_protectivetypes_id",
//                        'glpi_plugin_dlteams_protectivetypes' => "id"
//                    ]
//                ],
//                'glpi_plugin_dlteams_protectivecategories' => [
//                    'FKEY' => [
//                        'glpi_plugin_dlteams_protectivemeasures' => "plugin_dlteams_protectivecategories_id",
//                        'glpi_plugin_dlteams_protectivecategories' => "id"
//                    ]
//                ],
//            ],
            'ORDER' => self::getTable() . '.id DESC'
        ]);

        return iterator_to_array($items);
    }

    public static function getRequest(CommonDBTM $object_item){
        $request = [
            'SELECT' => [
                'glpi_plugin_dlteams_records_items.id AS linkid',
                'glpi_plugin_dlteams_records_items.itemtype AS itemtype',
                'glpi_plugin_dlteams_records_items.items_id AS items_id',
                'glpi_plugin_dlteams_records_items.comment AS comment',
            ],
            'FROM' => 'glpi_plugin_dlteams_records_items',
            'ORDER' => ['glpi_plugin_dlteams_records_items.id ASC'],
            'OR' => [
                [
                    'glpi_plugin_dlteams_records_items.records_id' => $object_item->fields['id'],
                    'glpi_plugin_dlteams_records_items.itemtype' => 'Group',
                    'glpi_plugin_dlteams_records_items.itemtype1' => null,
                ],
                [
                    'glpi_plugin_dlteams_records_items.records_id' => $object_item->fields['id'],
                    'glpi_plugin_dlteams_records_items.itemtype' => 'User',
                    'glpi_plugin_dlteams_records_items.itemtype1' => null,
                ],
                [
                    'glpi_plugin_dlteams_records_items.records_id' => $object_item->fields['id'],
                    'glpi_plugin_dlteams_records_items.itemtype' => 'PluginDlteamsThirdPartyCategory',
                    'glpi_plugin_dlteams_records_items.itemtype1' => null,
                ],
                [
                    'glpi_plugin_dlteams_records_items.records_id' => $object_item->fields['id'],
                    'glpi_plugin_dlteams_records_items.itemtype' => 'Supplier',
                    'glpi_plugin_dlteams_records_items.itemtype1' => null,
                ],
            ]
        ];

        return $request;
    }

    static function showItemsForItemType(CommonDBTM $object_item, $withtemplate = 0) {
        global $DB;
        $id = $object_item->fields['id'];
        if (!$object_item->can($id, READ)) {
            return false;
        }

        $canedit = $object_item->can($id, UPDATE);
        $rand = mt_rand(1, mt_getrandmax());

        $iterator=$DB->request(self::getRequest($object_item));


//        $iterator=$DB->request([]);
        $number = count($iterator);

        $items_list = [];
        $used = [];
        foreach ($iterator as $id => $data){
            // while ($data = $iterator->next()) {
            $items_list[$data['linkid']] = $data;

            $used[$data['linkid']] = $data['linkid'];

        }
        //print_r($items_list);die;
        if ($canedit) {
            echo "<div class='firstbloc'>";
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";
            $iden=$object_item->fields['id'];
            echo "<input type='hidden' name='plugin_dlteams_records_id' value='$iden' />";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th colspan='3'>" . __("Actors, subcontractors, recipients", 'dlteams') . "</th></tr>";

            echo "<tr class='tab_bg_1'>";
            //echo __("Group");
            echo "<td width='' class='left'>";
            echo "<p style='font-size:13px'><i>RGPD, Art 4 et 28.10 : indiquer ici les responsables internes de traitement ainsi que les sous-traitants</i></p>";
            /* Test Field */
            global $CFG_GLPI;

            $id = $object_item->fields['id'];
            if (!$object_item->can($id, READ)) {
                return false;
            }

            $canedit = PluginDlteamsRecord::canUpdate();
            $rand = mt_rand(1, mt_getrandmax());

            $options['canedit'] = $canedit;
            $options['formtitle'] = __("Right exercice", 'dlteams');

            $rand = Dropdown::showFromArray("consent_type", [
                __("------", 'dlteams'),
                __("Groupe", 'dlteams'),
                __("Utilisateur", 'dlteams'),
                __("Tiers Categories", 'dlteams'),
                __("Tiers", 'dlteams'),
            ], [
                'value' => $item->fields['consent_type1'] ?? 0,
                'width' => '130px'
            ]);
            $params = [
                'consent_type1' => '__VALUE__',
                'plugin_dlteams_records_id' => $id
            ];
            Ajax::updateItemOnSelectEvent(
                "dropdown_consent_type$rand",
                'consent_row2',
                $CFG_GLPI['root_doc'] . '/marketplace/dlteams/ajax/record_external_dropdown.php',
                $params
            );
            echo "<span id='consent_row2' style='margin-left:40px!important'>";
            static::showConsent1($object_item, $object_item->fields);

            echo "</span>";
            echo "</td></tr>";

            //echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
            //echo __("Supplier", 'dlteams');
            //echo "</td><td width='50%' class='left' id='consent_row2'>";
            // self::showConsent1($item, $item->fields);
            //echo "</td></tr>";

            /*echo "<tr><td colspan='20%' class='center' style='padding-left:100px'>";
            echo "<input type='submit' name='add1' value=\"" . _sx('button', 'Save') . "\" class='submit' style='margin-left:0px'>";
            echo "</td></tr>";*/


            /**add y me**/
            /*echo "<tr class='tab_bg_2'><th colspan='3'>" . __("Process", 'dlteams') . "</th></tr>";
            echo "<tr class='tab_bg_1'><td width='10%' class=''>";
          // echo __("Process", 'dlteams') .
              echo "<br><i>" . __("The UE law says that user should give and informed consent if data is treated outside of UE", 'dlteams') . "</i>";
           echo "</td><td width='90%' class=''>";
           $checked = json_decode($item->fields['external_process'] ?? '{}', true);
           $choices = [
              __("France", 'dlteams'),
              __("European Union", 'dlteams'),
              __("World", 'dlteams'),
              "<input type='text' disabled=true placeholder='" . __("Other") .
              "' name='process_other' id='process_other' value='" . ($checked['other'] ?? '') . "'>",
           ];
           echo PluginDlteamsUtils::displayCheckboxes($checked, $choices, 'process');
           echo "</td></tr>";
           echo "<tr><td colspan='20%' class='center' style='padding-left:100px'>";
           echo "<input type='submit' name='add2' value=\"" . _sx('button', 'Save') . "\" class='submit' style='margin-left:0px'>";
           echo "</td></tr>";*/
            /**add by me**/
            echo "</table>";
            Html::closeForm();
            echo "</div>";

        }

        // Display recipients
        echo "<div class='spaced'>";
        if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massive_action_params = ['container' => 'mass' .__CLASS__ . $rand,
                'num_displayed' => min($_SESSION['glpilist_limit'], $number)];
            Html::showMassiveActions($massive_action_params);
        }
        echo "<table class='tab_cadre_fixehov'>";

        $header_begin = "<tr>";
        $header_top = '';
        $header_bottom = '';
        $header_end = '';

        if ($canedit && $number) {

            $header_begin   .= "<th width='10'>";
            $header_top     .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            $header_bottom  .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            $header_end     .= "</th>";
        }

        $header_end .= "<th>" . __("Acteur") . "</th>";
        $header_end .= "<th>" . __("Type", 'dlteams') . "</th>";
        $header_end .= "<th>" . __("Comment") . "</th>";
        $header_end .= "</tr>";

        echo $header_begin . $header_top . $header_end;
        //var_dump($items_list);

        foreach ($items_list as $data) {
            echo "<tr class='tab_bg_1'>";

            if ($canedit && $number) {
                echo "<td width='10'>";
                Html::showMassiveActionCheckBox(__CLASS__, $data['linkid']);
                echo "</td>";
            }

//            $link = $data['name'];

//            if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
//                $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
//            }
            $itemtype_str = $data['itemtype'];
            $itemtype_object = new $itemtype_str();
            $itemtype_object->getFromDB($data['items_id']);

            $name = "<a target='_blank' href=\"" . $data['itemtype']::getFormURLWithID($data['items_id']) . "\">" . $itemtype_object->fields['name'] . "</a>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $name . "</td>";
            /*add by me**/
            /*$link1 = $data['name1'];
            if ($_SESSION['glpiis_ids_visible'] || empty($data['name1'])) {
               $link1 = sprintf(__("%1\$s (%2\$s)"), $link1, $data['id']);
            }
            $name1 = "<a target='_blank' href='../front/sendingreason.form.php?id=".$data['id1']."'>" . $link1 . "</a>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $name1 . "</td>";*/
            /**add by me**/

            echo "<td class='left' width='30%'>" . $data['itemtype']::getTypeName() . "</td>";
            echo "<td class='left' width='40%'>" . ($data['comment']?? "") . "</td>";
            echo "</tr>";
        }

        if ($iterator->count() > 10) {
            echo $header_begin . $header_bottom . $header_end;
        }
        echo "</table>";

        if ($canedit && $number) {
            $massive_action_params['ontop'] = false;
            Html::showMassiveActions($massive_action_params);
            Html::closeForm();
        }


        echo "</div>";
    }

    static function countItems(CommonDBTM $object_item) {
        $counter = 0;
        global $DB;
        $id = $object_item->fields['id'];
        if (!$object_item->can($id, READ)) {
            return false;
        }


        $iterator=$DB->request(self::getRequest($object_item));

        $items_list = [];
        $used = [];
        foreach ($iterator as $id => $data){
            // while ($data = $iterator->next()) {
            $items_list[$data['linkid']] = $data;

            $used[$data['linkid']] = $data['linkid'];

        }


        foreach ($items_list as $data) {
            $counter++;
        }
        return $counter;
    }




    static function showConsent1(PluginDlteamsRecord $record, $data = []) {
        if ($data['consent_type1'] == 0) {
        }else if ($data['consent_type1'] == 1) {
            //echo __("Groupe <i class='fas fa-dolly'></i>&nbsp;", 'dlteams');
            Group::dropdown([
                'addicon'  => Group::canCreate(),
                'name' => "groups_id",
                'display_emptychoice' => false,
                'width' => "200px"
            ]);
            echo "<textarea type='text' rows='1' name='comment' placeholder='Commentaire'  style='margin-bottom:-15px;margin-left:90px;width:45%'></textarea>";
            echo "<input type='submit' name='add1' value=\"" . _sx('button', 'Add') . "\" class='submit' style='float:right;margin-right:7.5%'>";

        } else if ($data['consent_type1'] == 2) {

            //echo __("Utilisateur <i class='fas fa-dolly'></i>&nbsp;", 'dlteams');

            /*User::dropdown([
            'addicon'  => User::canCreate(),
            'name' => "users_id",
            'display_emptychoice' => false,
            'width' => "200px"

         ]); */
            $randDropdown = mt_rand();
            User::dropdown(['value'  => $record->fields["users_id"],
                'entity' => $record->fields["entities_id"],
                'right'  => 'all',
                'width' => '200px',
                'rand'   => $randDropdown]);
            echo "<textarea type='text' maxlength=600 rows='1' name='comment' placeholder='Commentaire' style='margin-bottom:-15px;margin-left:40px;width:45%'></textarea>";
            echo "<input type='submit' name='add1' value=\"" . _sx('button', 'Add') . "\" class='submit' style='float:right;margin-right:7.5%'>";


        }
        else if ($data['consent_type1'] == 3) {
            // Display explicit consentecho "<td><br>" . "</td><td>";

            //echo __("Tiers Categories <i class='fas fa-dolly'></i>&nbsp;", 'dlteams');

            PluginDlteamsThirdPartyCategory::dropdown([
                'addicon'  => PluginDlteamsThirdPartyCategory::canCreate(),
                'name' => "plugin_dlteams_thirdpartycategories_id1",
                'width' => "200px"
            ]);
            echo "<textarea type='text' maxlength=600 rows='1' name='comment' placeholder='Commentaire' style='margin-bottom:-15px;margin-left:90px;width:45%'></textarea>";
            echo "<input type='submit' name='add1' value=\"" . _sx('button', 'Add') . "\" class='submit' style='float:right;margin-right:7.5%'>";

        }

        else if ($data['consent_type1'] == 4) {
            // Display explicit consentecho "<td><br>" . "</td><td>";

            //echo __("Tiers <i class='fas fa-dolly'></i>&nbsp;", 'dlteams');

            Supplier::dropdown([
                'addicon'  => Supplier::canCreate(),
                'name' => "suppliers_id1",
                'display_emptychoice' => false,
                'width' => "200px"
            ]);
            echo "<textarea type='text' maxlength=600 rows='1' name='comment' placeholder='Commentaire' style='margin-bottom:-15px;margin-left:90px;width:45%'></textarea>";

            echo "<input type='submit' name='add1' value=\"" . _sx('button', 'Add') . "\" class='submit' style='float:right;margin-right:7.5%'>";
        }

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
        $action_label = __("Delete dlteams relations");
        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;

        return $actions;
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
                    $record = new PluginDlteamsRecord_Item();

                    if($record->getFromDB($id)){


                        global $DB;
                        switch ($record->getField('itemtype')){
                            case 'User':
                                $relationTable = 'glpi_plugin_dlteams_users_items';
                                break;
                            default:
                                $relationTable = CommonDBRelation::getTable(
                                    $record->getField('itemtype')
                                );

                                $relationTable .='_items';
                                break;
                        }


                        $DB->delete(
                            $relationTable,
                            array(
                                'foreign_id' => $id,
                            )
                        );

                        $record->delete(array('id' => $id));
                        $ma->itemDone(__CLASS__, $id, MassiveAction::ACTION_OK);
                    }
                    else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                    }
                }
                break;

            case 'update_dlteams_action':
                $temp = new PluginDlteamsThirdPartyCategory_Item();
                $updatable_columns = $temp->rawSearchOptions();
                $parts = explode(':', $ma->POST['id_field']);
                $id_modif = $parts[1];

                switch ($id_modif) {
                    case static::$column4_id:
                        foreach ($ids as $id) {
                            $base_item = new PluginDlteamsRecord_Item();
                            if ($base_item->getFromDB($id)) {
//                                var_dump($base_item->fields);
                                $array_values = array_values($ma->POST);

//                            suppression dans storage period item
                                if($base_item->fields["itemtype"] == 'Group'){

                                    $itemtype_item = new PluginDlteamsGroup_Item();
                                    $itemtype_item_id_column = "groups_id";

                                    $t = $itemtype_item->deleteByCriteria([
                                        $itemtype_item_id_column => $base_item->fields["items_id"],
                                        "itemtype" => "PluginDlteamsRecord",
                                        "items_id" => $base_item->fields["records_id"],
                                        "foreign_id" => $base_item->fields["id"]
                                    ]);

//                                ajout de la nouvelle ligne
                                    $ee = $itemtype_item->add([
                                        $itemtype_item_id_column => $base_item->fields["items_id"],
                                        "itemtype" => "PluginDlteamsRecord",
                                        "items_id" => $base_item->fields["records_id"],
                                        "foreign_id" => $base_item->fields["id"],
                                        "comment" => $array_values[1]
                                    ]);


                                    $t = $base_item->update([
                                        "comment" => $array_values[1],
                                        'id' => $id
                                    ]);
                                }

                                if($base_item->fields["itemtype"] == 'User'){

                                    $itemtype_item = new PluginDlteamsUser_Item();
                                    $itemtype_item_id_column = "users_id";


                                    $t = $itemtype_item->deleteByCriteria([
                                        $itemtype_item_id_column => $base_item->fields["items_id"],
                                        "itemtype" => "PluginDlteamsRecord",
                                        "items_id" => $base_item->fields["records_id"],
                                        "foreign_id" => $base_item->fields["id"]
                                    ]);

//                                ajout de la nouvelle ligne
                                    $ee = $itemtype_item->add([
                                        $itemtype_item_id_column => $base_item->fields["items_id"],
                                        "itemtype" => "PluginDlteamsRecord",
                                        "items_id" => $base_item->fields["records_id"],
                                        "foreign_id" => $base_item->fields["id"],
                                        "comment" => $array_values[1]
                                    ]);


                                    $t = $base_item->update([
                                        "comment" => $array_values[1],
                                        'id' => $id
                                    ]);
                                }

                                if($base_item->fields["itemtype"] == 'PluginDlteamsThirdPartyCategory'){

                                    $itemtype_item = new PluginDlteamsThirdPartyCategory_Item();
                                    $itemtype_item_id_column = "thirdpartycategories_id";


                                    $t = $itemtype_item->deleteByCriteria([
                                        $itemtype_item_id_column => $base_item->fields["items_id"],
                                        "itemtype" => "PluginDlteamsRecord",
                                        "items_id" => $base_item->fields["records_id"],
                                        "foreign_id" => $base_item->fields["id"]
                                    ]);

//                                ajout de la nouvelle ligne
                                    $ee = $itemtype_item->add([
                                        $itemtype_item_id_column => $base_item->fields["items_id"],
                                        "itemtype" => "PluginDlteamsRecord",
                                        "items_id" => $base_item->fields["records_id"],
                                        "foreign_id" => $base_item->fields["id"],
                                        "comment" => $array_values[1]
                                    ]);


                                    $t = $base_item->update([
                                        "comment" => $array_values[1],
                                        'id' => $id
                                    ]);
                                }

                                if($base_item->fields["itemtype"] == 'Supplier'){

                                    $itemtype_item = new PluginDlteamsSupplier_Item();
                                    $itemtype_item_id_column = "suppliers_id";


                                    $t = $itemtype_item->deleteByCriteria([
                                        $itemtype_item_id_column => $base_item->fields["items_id"],
                                        "itemtype" => "PluginDlteamsRecord",
                                        "items_id" => $base_item->fields["records_id"],
                                        "foreign_id" => $base_item->fields["id"]
                                    ]);

//                                ajout de la nouvelle ligne
                                    $ee = $itemtype_item->add([
                                        $itemtype_item_id_column => $base_item->fields["items_id"],
                                        "itemtype" => "PluginDlteamsRecord",
                                        "items_id" => $base_item->fields["records_id"],
                                        "foreign_id" => $base_item->fields["id"],
                                        "comment" => $array_values[1]
                                    ]);


                                    $t = $base_item->update([
                                        "comment" => $array_values[1],
                                        'id' => $id
                                    ]);
                                }
                            }
                        }
                        break;

                }
                $ma->__set("remainings", ["PluginDlteamsRecord_Item" => [$id => $id]]);
                $ma->itemDone('PluginDlteamsRecord_Item', $id, MassiveAction::ACTION_OK);
                break;
        }


        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    function rawSearchOptions()
    {

        $tab = [];


//        $tab[] = [
//            'id' => static::$column2_id,
//            'table' => PluginDlteamsStorageType::getTable(),
//            'field' => 'name',
//            'name' => __("Stockage"),
//            'forcegroupby' => true,
//            'massiveaction' => true,
//            'datatype' => 'dropdown',
//            'searchtype' => ['equals', 'notequals'],
//            'joinparams' => [
//                'beforejoin' => [
//                    'table' => self::getTable(),
//                    'joinparams' => [
//                        'jointype' => 'child'
//                    ]
//                ]
//            ]
//        ];
//
//        $tab[] = [
//            'id' => static::$column3_id,
//            'table' => PluginDlteamsStorageEndAction::getTable(),
//            'field' => 'name',
//            'name' => __("En fin de période"),
//            'forcegroupby' => true,
//            'massiveaction' => true,
//            'datatype' => 'dropdown',
//            'searchtype' => ['equals', 'notequals'],
//            'joinparams' => [
//                'beforejoin' => [
//                    'table' => self::getTable(),
//                    'joinparams' => [
//                        'jointype' => 'child'
//                    ]
//                ]
//            ]
//        ];
//
        $tab[] = [
            'id' => static::$column4_id,
            'table' => PluginDlteamsRecord_Item::getTable(),
            'field' => 'comment',
            'name' => __("Commentaire"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'text',
            'searchtype' => ['equals', 'notequals'],
        ];


        return $tab;
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


}
