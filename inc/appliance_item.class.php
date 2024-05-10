<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2023 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

class PluginDlteamsAppliance_Item extends CommonDBTM
{
    use Glpi\Features\Clonable;

//    public static $itemtype_1 = 'PluginDlteamsDataCatalog';
//    public static $items_id_1 = 'datacatalogs_id';
//    public static $take_entity_1 = false;
//
//    public static $itemtype_2 = 'itemtype';
//    public static $items_id_2 = 'items_id';
//    public static $take_entity_2 = true;

//    public function getCloneRelations(): array
//    {
//        return [
//            Appliance_Item_Relation::class
//        ];
//    }

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

    public static function getTypeName($nb = 0)
    {
        return _n('Elements rattachés', 'Elements rattachés', $nb);
    }


    static public $itemtype_2 = 'PluginDlteamsDataCatalog';
    static public $itemtype_1;
    public static $items_id_1;
    public static $title;
    public static $sub_title;
    public static $table_match_str = [];

    public function __construct()
    {
        static::$itemtype_1 = str_replace("_Item", "", __CLASS__); // $itemtype_1 ---> PluginDlteamsDataCatalog
        static::$items_id_1 = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", __CLASS__))) . "s_id";
        parent::__construct();
        self::forceTable(Appliance_Item::getTable());
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!Appliance::canView()) {
            return '';
        }

        $nb = 0;
        if ($item->getType() == Appliance::class) {
            if ($_SESSION['glpishow_count_on_tabs']) {
                if (!$item->isNewItem()) {
                    $nb = self::countForMainItem($item);
                }
            }
            return '';
            return self::createTabEntry(self::getTypeName(Session::getPluralNumber()), $nb);
        } else if (in_array($item->getType(), Appliance::getTypes(true))) {
            if ($_SESSION['glpishow_count_on_tabs']) {
                $nb = self::countForItem($item);
//                $nb = 0;
            }
            return self::createTabEntry(Appliance::getTypeName(Session::getPluralNumber()), $nb);
        }

        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case Appliance::class:
                self::showItems($item);
                break;
            default:
                if (in_array($item->getType(), Appliance::getTypes())) {
                    self::showForItem($item, $withtemplate);
                }
        }
        return true;
    }

    /**
     * Print enclosure items
     *
     * @param Appliance $appliance  Appliance object wanted
     *
     * @return void|boolean (display) Returns false if there is a rights error.
     **/
    public static function showItems(Appliance $appliance)
    {
        global $DB;

        $ID = $appliance->fields['id'];
        $rand = mt_rand();

        if (
            !$appliance->getFromDB($ID)
            || !$appliance->can($ID, READ)
        ) {
            return false;
        }
        $canedit = $appliance->canEdit($ID);

        $items = $DB->request([
            'FROM'   => self::getTable(),
            'WHERE'  => [
                self::$items_id_1 => $ID
            ]
        ]);

        Session::initNavigateListItems(
            self::getType(),
            //TRANS : %1$s is the itemtype name,
            //        %2$s is the name of the item (used for headings of a list)
            sprintf(
                __('%1$s = %2$s'),
                $appliance->getTypeName(1),
                $appliance->getName()
            )
        );

        if ($appliance->canAddItem('itemtype')) {
            echo "<div class='firstbloc'>";
            echo "<form method='post' name='appliances_form$rand'
                     id='appliances_form$rand'
                     action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'>";
            echo "<th colspan='2'>" .
               __('Add an item') . "</th></tr>";

            echo "<tr class='tab_bg_1'><td class='center'>";
//            Dropdown::showSelectItemFromItemtypes(
//                ['items_id_name'   => 'items_id',
//                    'itemtypes'       => Appliance::getTypes(true),
//                    'entity_restrict' => ($appliance->fields['is_recursive']
//                                      ? getSonsOf(
//                                          'glpi_entities',
//                                          $appliance->fields['entities_id']
//                                      )
//                                       : $appliance->fields['entities_id']),
//                    'checkright'      => true,
//                ]
//            );

            Appliance::dropdown([
                'name' => 'items_id',
                'addicon' => PluginDlteamsSendingReason::canCreate(),
                'name' => 'items_id',
                'entity_restrict' => ($appliance->fields['is_recursive']
                    ? getSonsOf(
                        'glpi_entities',
                        $appliance->fields['entities_id']
                    )
                    : $appliance->fields['entities_id']),
                'width' => "250px",
                'checkright'      => true,
            ]);
            echo "</td><td class='center' class='tab_bg_1'>";
            echo Html::hidden('appliances_id', ['value' => $ID]);
            echo Html::submit(_x('button', 'Add'), ['name' => 'add']);
            echo "</td></tr>";
            echo "</table>";
            Html::closeForm();
            echo "</div>";
        }

        $items = iterator_to_array($items);

        if (!count($items)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
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
            $header .= "<th>" . __('Itemtype') . "</th>";
            $header .= "<th>" . _n('Item', 'Items', 1) . "</th>";
            $header .= "<th>" . __("Serial") . "</th>";
            $header .= "<th>" . __("Inventory number") . "</th>";
            $header .= "<th>" . Appliance_Item_Relation::getTypeName(Session::getPluralNumber()) . "</th>";
            $header .= "</tr>";
            echo $header;

            foreach ($items as $row) {
                $item = new $row['itemtype']();
                $item->getFromDB($row['items_id']);
                echo "<tr lass='tab_bg_1'>";
                if ($canedit) {
                    echo "<td>";
                    Html::showMassiveActionCheckBox(__CLASS__, $row["id"]);
                    echo "</td>";
                }
                echo "<td>" . $item->getTypeName(1) . "</td>";
                echo "<td>" . $item->getLink() . "</td>";
                echo "<td>" . ($item->fields['serial'] ?? "") . "</td>";
                echo "<td>" . ($item->fields['otherserial'] ?? "") . "</td>";
                echo "<td class='relations_list'>";
                echo Appliance_Item_Relation::showListForApplianceItem($row["id"], $canedit);
                echo "</td>";
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

            echo Appliance_Item_Relation::getListJSForApplianceItem($appliance, $canedit);
        }
    }

    /**
     * Print an HTML array of appliances associated to an object
     *
     * @since 9.5.2
     *
     * @param CommonDBTM $item         CommonDBTM object wanted
     * @param boolean    $withtemplate not used (to be deleted)
     *
     * @return void
     **/
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {

        $itemtype = $item->getType();
        $ID       = $item->fields['id'];

        if (
            !Appliance::canView()
            || !$item->can($ID, READ)
        ) {
            return;
        }

        $canedit = $item->can($ID, UPDATE);
        $rand = mt_rand();


        $iterator = self::getRequest($item);
        $number = count($iterator);

        $appliances = [];
        $used      = [];
        foreach ($iterator as $data) {
            $appliances[$data['id']] = $data;
            $used[$data['id']]      = $data['id'];
        }
        if ($canedit && ($withtemplate != 2)) {
            echo "<div class='firstbloc'>";
            echo "<form name='applianceitem_form$rand' id='applianceitem_form$rand' method='post'
                action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
            echo "<input type='hidden' name='items_id1' value='$ID'>";
            echo "<input type='hidden' name='itemtype1' value='$itemtype'>";
            echo "<input type='hidden' name='itemtype' value='" . str_replace("_Item", "", __CLASS__) . "'>";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_1'>";
            echo "<td class='right' style='text-wrap: nowrap;' width='10%'>";
            echo __('Add to an appliance');
            echo "</td>";
            echo "<td style='display: flex;' class='left'>";
            Appliance::dropdown([
                'entity'  => $item->getEntityID(),
                'addicon' => Appliance::canCreate(),
                'used'    => $used,
                'name' => 'items_id',
                'right' => 'all'
            ]);
            echo "</td>";
            echo "<td class='left'>";
            echo "</td>";
            echo "</tr>";

            echo "<tr class='tab_bg_1' style='display: none;' id='field-createlink'>";
            echo "<td class='right' width='10%'>";
            echo __("Comment");
            echo "</td>";

            echo "<td style='display: flex;' class='left'>";
            echo "<div>";
            echo "<textarea type='text' style='width:90%;' rows='3' name='comment' class='comment'></textarea>";
            echo "<br/><br/>";
            if($item::getType() == PluginDlteamsDataCatalog::class) {
                echo "<div style='display: flex; align-items: center; gap: 3px;'>";
                Html::showCheckbox([
                    'name' => 'apply_on_childs',
                    'checked' => true
                ]);
                echo "<label>Ajouter cette application aux catalogues enfants</label>";
                echo "</div>";
            }
            echo "</div>";
            echo "</td>";
            echo "<td class='left'>";
            echo "</td>";
            echo "</tr>";

            echo "<tr>";
            echo "<td>";
            echo "</td>";
            echo "<td colspan='2' class='left'>";
            echo "<input type='submit' name='link_element' id='btn-createlink' value=\"" . _sx('button', 'Add') . "\" class='btn btn-primary'>";
            echo "</td>";
            echo "</tr>";



            echo "</table>";
            Html::closeForm();
            echo "</div>";

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
                                    object: '" . Appliance::class . "',
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
        }

        echo "<div class='spaced'>";
        if ($withtemplate != 2) {
            if ($canedit && $number) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = ['num_displayed' => min($_SESSION['glpilist_limit'], $number),
                    'container'     => 'mass' . __CLASS__ . $rand
                ];
                Html::showMassiveActions($massiveactionparams);
            }
        }
        echo "<table class='tab_cadre_fixehov'>";

        $header = "<tr>";
        if ($canedit && $number && ($withtemplate != 2)) {
            $header    .= "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            $header    .= "</th>";
        }

        $header .= "<th>" . __('Name') . "</th>";
//        $header .= "<th>" . Appliance_Item_Relation::getTypeName(Session::getPluralNumber()) . "</th>";
        $header .= "<th>" . __("Comment") . "</th>";
        $header .= "</tr>";

        if ($number > 0) {
            echo $header;
            Session::initNavigateListItems(
                __CLASS__,
                //TRANS : %1$s is the itemtype name,
                              //         %2$s is the name of the item (used for headings of a list)
                                        sprintf(
                                            __('%1$s = %2$s'),
                                            $item->getTypeName(1),
                                            $item->getName()
                                        )
            );
            foreach ($appliances as $data) {
                $cID         = $data["id"];
                Session::addToNavigateListItems(__CLASS__, $cID);
                $assocID     = $data["linkid"];
                $app         = new Appliance();
                $app->getFromResultSet($data);
                echo "<tr class='tab_bg_1" . (isset($app->fields["is_deleted"]) && $app->fields["is_deleted"] ? "_2" : "") . "'>";
                if ($canedit && ($withtemplate != 2)) {
                    echo "<td width='10'>";
                    Html::showMassiveActionCheckBox(__CLASS__, $assocID);
                    echo "</td>";
                }
                echo "<td class='b'>";
                $name = $app->fields["name"];
                if (
                    $_SESSION["glpiis_ids_visible"]
                    || empty($app->fields["name"])
                ) {
                    $name = sprintf(__('%1$s (%2$s)'), $name, $app->fields["id"]);
                }
                echo "<a href='" . Appliance::getFormURLWithID($cID) . "'>" . $name . "</a>";
                echo "</td>";
//                echo "<td class='relations_list'>";
//                echo Appliance_Item_Relation::showListForApplianceItem($assocID, $canedit);
//                echo "</td>";

                echo "<td class='relations_list'>";
                echo $data["comment"];

                echo "</td>";

                echo "</tr>";
            }
            echo $header;
            echo "</table>";
        } else {
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr><th>" . __('No item found') . "</th></tr></table>";
        }

        echo "</table>";
        if ($canedit && $number && ($withtemplate != 2) && $number>10) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
        }
            Html::closeForm();
        echo "</div>";

        echo Appliance_Item_Relation::getListJSForApplianceItem($item, $canedit);
    }

    static function countForItem(CommonDBTM $item)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable(static::getTable(), ['items_id' => $item->getID(), 'itemtype' => $item->getType()]);
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
            "itemtype" => PluginDlteamsDataCatalog::class,
            "items_id" => $this->fields["datacatalogs_id"],
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
            "itemtype" => PluginDlteamsAppliance::class,
            "items_id" => $this->fields["appliances_id"],
            $relation_column_id => $this->fields["items_id"],
            "comment" => $this->oldvalues["comment"]
        ];

        $relation_item->deleteByCriteria($criteria);
        $relation_item->add([
            ...$criteria,
            "comment" => $this->fields["comment"]
        ]);
    }

    public static function getRequest(CommonDBTM $item)
    {
        $query = [
            'SELECT' => [
                'glpi_appliances_items.id AS linkid',
                'glpi_appliances_items.itemtype AS itemtype',
                'glpi_appliances_items.items_id AS items_id',
                'glpi_appliances_items.*',
                Appliance::getTable().'.id AS id',
                Appliance::getTable().'.name AS name',
//                Appliance::getTable().'.content AS content',
            ],
            'FROM' => 'glpi_appliances_items',
            'LEFT JOIN' => [
                Appliance::getTable() => [
                    'ON' => [
                        'glpi_appliances_items' => 'appliances_id',
                        Appliance::getTable() => 'id'
                    ]
                ]
            ],
            'WHERE' => [
                'glpi_appliances_items' . '.itemtype' => ['LIKE', $item::getType()],
                'glpi_appliances_items' . '.' . 'items_id' => $item->fields['id'],
            ],
            'ORDERBY' => ['name ASC']
        ];


        global $DB;



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

    public function prepareInputForAdd($input)
    {
        return $this->prepareInput($input);
    }

    public function prepareInputForUpdate($input)
    {
        return $this->prepareInput($input);
    }

    /**
     * Prepares input (for update and add)
     *
     * @param array $input Input data
     *
     * @return array
     */
    private function prepareInput($input)
    {
        $error_detected = [];

       //check for requirements
        if (
            ($this->isNewItem() && (!isset($input['itemtype']) || empty($input['itemtype'])))
            || (isset($input['itemtype']) && empty($input['itemtype']))
        ) {
            $error_detected[] = __('An item type is required');
        }
        if (
            ($this->isNewItem() && (!isset($input['items_id']) || empty($input['items_id'])))
            || (isset($input['items_id']) && empty($input['items_id']))
        ) {
            $error_detected[] = __('An item is required');
        }
        if (
            ($this->isNewItem() && (!isset($input[self::$items_id_1]) || empty($input[self::$items_id_1])))
            || (isset($input[self::$items_id_1]) && empty($input[self::$items_id_1]))
        ) {
            $error_detected[] = __('An appliance is required');
        }

        if (count($error_detected)) {
            foreach ($error_detected as $error) {
                Session::addMessageAfterRedirect(
                    $error,
                    true,
                    ERROR
                );
            }
            return false;
        }

        return $input;
    }

    public static function countForMainItem(CommonDBTM $item, $extra_types_where = [])
    {
        $types = Appliance::getTypes();
        $clause = [];
        if (count($types)) {
            $clause = ['itemtype' => $types];
        } else {
            $clause = [new \QueryExpression('true = false')];
        }
        $extra_types_where = array_merge(
            $extra_types_where,
            $clause
        );
        return parent::countForMainItem($item, $extra_types_where);
    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }

    public static function getRelationMassiveActionsSpecificities()
    {
        global $CFG_GLPI;

        $specificities              = parent::getRelationMassiveActionsSpecificities();
        $specificities['itemtypes'] = Appliance::getTypes();

        return $specificities;
    }

    public function cleanDBonPurge()
    {
        $this->deleteChildrenAndRelationsFromDb(
            [
                Appliance_Item_Relation::class,
            ]
        );
    }

    public function getCloneRelations(): array
    {
        // TODO: Implement getCloneRelations() method.
    }
}

