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

class PluginDlteamsRecord_PersonalAndDataCategory extends CommonDBTM implements
    PluginDlteamsExportableInterface
{

    static public $itemtype_1 = 'PluginDlteamsRecord';
    static public $items_id_1 = 'records_id';
    static public $itemtype_2 = 'PluginDlteamsConcernedPerson';
    static public $items_id_2 = 'plugin_dlteams_concernedpersons_id';
    static public $column1_id = '49'; // for dlteams massiveupdate purpose
    static public $column2_id = '42'; // for dlteams massiveupdate purpose
    static public $column3_id = '39'; // for dlteams massiveupdate purpose

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

    public function canEdit($ID)
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
        return __("Personnes concernées par ce traitement et catégories de données", 'dlteams');
    }

    public function __construct()
    {
        self::forceTable('glpi_plugin_dlteams_records_items');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$item->canView()) {
            return false;
        }

        switch ($item->getType()) {
            case PluginDlteamsRecord::class :

                $nb = 0;
                if ($_SESSION['glpishow_count_on_tabs']) {
                    $nb = self::countForRecord($item);
                }

                return self::createTabEntry(PluginDlteamsRecord_PersonalAndDataCategory::getTypeName($nb), $nb);
        }

        return '';
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

    static function showForRecord(PluginDlteamsRecord $record, $withtemplate = 0)
    {

        $id = $record->fields['id'];
        if (!$record->can($id, READ)) {
            return false;
        }

        $canedit = PluginDlteamsRecord::canUpdate();
        $rand = mt_rand(1, mt_getrandmax());

        global $CFG_GLPI;
        global $DB;
        // Request that joins 3 table (not possible to do with CommonDBRelation methods)
        // Result is used lower to display a table

        $iterator = $DB->request(self::getRequest($record));
        $number = count($iterator);

        $items_list = [];
        $used = [];
        foreach ($iterator as $id => $data) {
            //while ($data = $iterator->next()) {
            $items_list[$data['linkid']] = $data;
            $used[$data['linkid']] = $data['linkid'];
        }

        // Displays form
        //if ($canedit) {
        echo "<div class='firstbloc'>";
        echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";
        $iden = $record->fields['id'];
        echo "<input type='hidden' name='base_id' value='$iden' />"; // records_id in this case
        echo "<input type='hidden' name='baseitem' value='PluginDlteamsRecord' />";
        echo "<input type='hidden' name='itemtype' value='PluginDlteamsConcernedPerson' />";
        echo "<input type='hidden' name='itemtype1' value='PluginDlteamsProcessedData' />";

        if ($canedit) {
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th colspan='4'>" . __("Add Category of data subjects linked to a personal data type", 'dlteams') .
                /**add by me**/
                "<br><i style='font-weight: normal'>" .
                __("Art 4.1, 5.1, 30.1(f) concernant les données relatives au traitement", 'dlteams') .
                "</i></th>";
            echo "</tr>";
            /**add by me**/


            echo "<tr class='tab_bg_1'><td width='' class=''>";
            echo _n("Category of data subjects", "Categories of data subjects", 0, 'dlteams');
            echo "<br/><br/>";

            PluginDlteamsConcernedPerson::dropdown([
                //'addicon'  => PluginDlteamsConcernedPerson::canCreate(),
                'name' => "items_id",
                'width' => '300px',
            ]);

            echo "</td>";
            echo "<td width='' class=''>";
            echo _n("Personal Data Category", "Personal Data Categories", 0, 'dlteams');
            echo "<br/><br/>";

            PluginDlteamsProcessedData::dropdown([
                //'addicon' => PluginDlteamsProcessedData::canCreate(),
                'name' => 'items_id1',
                'width' => '300px',
                'value' => $_SESSION["dlteams_recent_processeddata"]??""
            ]);

            echo "</td>";

            echo "<td class=''>";
            echo __("Mandatory", 'dlteams');
            echo "<br/><br/>";
            Dropdown::showYesNo("mandatory", 1);
            echo "</td>";

            echo "<td class='center'>";
            echo "<input type='submit' name='add' value=\"" . __('Add') . "\" class='submit' style='margin-top:35px'>";
            echo "</td></tr>";
            echo "</table>";
        }
        /**new form**/
        Html::closeForm();
        echo "</div>";
        //}

        // Displays the table
        if ($iterator) {

            echo "<div class='spaced'>";
            if ($canedit && $number) {
                Html::openMassiveActionsForm('mass' . __class__ . $rand);
                $massive_action_params = ['container' => 'mass' . __class__ . $rand,
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
                $header_top .= Html::getCheckAllAsCheckbox('mass' . __class__ . $rand);
                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . __class__ . $rand);
                $header_end .= "</th>";
            }

            $header_end .= "<th class='left'>" . _n("Category of data subjects", "Categories of data subjects", 0, 'dlteams') . "</th>";
            $header_end .= "<th class='left'>" . _n("Personal Data Category", "Personal Data Categories", 0, 'dlteams') . "</th>";
            $header_end .= "<th width='10%' class='left'>" . __("Mandatory", 'dlteams') . "</th>";


            $header_end .= "</tr>";

            echo $header_begin . $header_top . $header_end;

            foreach ($items_list as $data) {
                echo "<tr class='tab_bg_1'>";

                if ($canedit && $number) {
                    echo "<td width='10'>";
                    Html::showMassiveActionCheckBox(__class__, $data['linkid']);
                    echo "</td>";
                }

                if ($data["itemtype"] && $data["items_id"]) {
                    $item_object = new $data["itemtype"]();
                    $item_object->getFromDB($data["items_id"]);
                    $name = $item_object->fields["name"];

                    $name = "<a target='_blank' href=\"" . $data["itemtype"]::getFormURLWithID($data["items_id"]) . "\">" . $name . "</a>";
                } else {
                    $name = "<a href='#'>----</a>";
                }

                echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                echo ">" . $name . "</td>";


                if ($data["itemtype1"] && $data["items_id1"]) {
                    $item_object = new $data["itemtype1"]();
                    $item_object->getFromDB($data["items_id1"]);
                    $name = $item_object->fields["name"];

                    $name = "<a target='_blank' href=\"" . $data["itemtype1"]::getFormURLWithID($data["items_id1"]) . "\">" . $name . "</a>";
                } else {
                    $name = "<a href='#'>----</a>";
                }

                echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                echo ">" . $name . "</td>";

                echo "<td class='left'>" . ($data['mandatory'] ? __("Yes") : __("No")) . "</td>";

                echo "</tr>";
            }

            if ($iterator->count() > 10) {
                echo $header_begin . $header_bottom . $header_end;
            }
            echo "</table>";

            if ($canedit && $number > 10) {
                $massive_action_params['ontop'] = false;
                Html::showMassiveActions($massive_action_params);
                Html::closeForm();
            }

            echo "</div>";
        }
    }

    function getForbiddenStandardMassiveAction()
    {

        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'MassiveAction:amend_comment';
        $forbidden[] = 'MassiveAction:add_transfer_list';

        return $forbidden;
    }

    function rawSearchOptions()
    {

        $tab = [];

        /*$tab[] = [
           'id' => 'datasubjectscategory',
           'name' => PluginGenericobjectPersonnesconcernee::getTypeName(0)
        ];*/

        $tab[] = [
            'id' => "410",
            'table' => PluginDlteamsConcernedPerson::getTable(),
            'field' => 'items_id',
            'name' => __("Catégorie de personnes"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'dropdown',
        ];

        $tab[] = [
            'id' => "411",
            'table' => PluginDlteamsProcessedData::getTable(),
            'field' => 'items_id1',
            'name' => __("Catégories de données"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'dropdown',
        ];

        $tab[] = [
            'id' => "412",
            'table' => 'mandatory',
            'field' => 'mandatory',
            'name' => __("Obligatoire"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'bool',
        ];


        return $tab;
    }

    public function update(array $input, $history = 1, $options = [])
    {

        $record_item = new PluginDlteamsRecord_Item();
        $record_item->getFromDB($input["id"]);
        $record_oldfields = $record_item->fields;

        global $DB;
        if(isset($input["plugin_dlteams_concernedpersons_id"])){
            $DB->update(
                $record_item->getTable(),
                [
                    "items_id" => $input["plugin_dlteams_concernedpersons_id"]
                ],
                [
                    "id" => $input["id"]
                ]
            );


//            mis a jour de concerned person
            $concernedperson_item = new PluginDlteamsConcernedPerson_Item();
            $concernedperson_item->getFromDBByCrit([
                "concernedpersons_id" => $record_oldfields["items_id"],
                "itemtype" => PluginDlteamsRecord::class,
                "items_id" => $record_oldfields["records_id"],
                "itemtype1" => $record_oldfields["itemtype1"],
                "items_id1" => $record_oldfields["items_id1"],
                "comment" => $record_oldfields["comment"],
            ]);
            if($concernedperson_item){
                $DB->update(
                    $concernedperson_item->getTable(),
                    [
                        "concernedpersons_id" => $input["plugin_dlteams_concernedpersons_id"]
                    ],
                    [
                        "id" => $concernedperson_item->fields["id"]
                    ]
                );

                Session::addMessageAfterRedirect("Relation ".PluginDlteamsConcernedPerson::getTypeName()." mis a jour avec succès");
            }

//            mise a jour de processed data item
            $processeddata_item = new PluginDlteamsProcessedData_Item();
            $processeddata_item->getFromDBByCrit([
                "processeddatas_id" => $record_oldfields["items_id1"],
                "itemtype" => PluginDlteamsRecord::class,
                "items_id" => $record_oldfields["records_id"],
                "itemtype1" => $record_oldfields["itemtype"],
                "items_id1" => $record_oldfields["items_id"],
                "comment" => $record_oldfields["comment"],
            ]);
            if($processeddata_item){
                $DB->update(
                    $processeddata_item->getTable(),
                    [
                        "items_id1" => $input["plugin_dlteams_concernedpersons_id"]
                    ],
                    [
                        "id" => $processeddata_item->fields["id"]
                    ]
                );

                Session::addMessageAfterRedirect("Relation ".PluginDlteamsProcessedData::getTypeName()." mis a jour avec succès");
            }
        }

        if(isset($input["plugin_dlteams_processeddatas_id"])){
            $DB->update(
                $record_item->getTable(),
                [
                    "items_id1" => $input["plugin_dlteams_processeddatas_id"]
                ],
                [
                    "id" => $input["id"]
                ]
            );
            Session::addMessageAfterRedirect("Traitement mis à jour avec succès");


            $processeddata_item = new PluginDlteamsProcessedData_Item();
            $processeddata_item->getFromDBByCrit([
                "processeddatas_id" => $record_oldfields["items_id1"],
                "itemtype" => PluginDlteamsRecord::class,
                "items_id" => $record_oldfields["records_id"],
                "itemtype1" => $record_oldfields["itemtype"],
                "items_id1" => $record_oldfields["items_id"],
                "comment" => $record_oldfields["comment"],
            ]);
            if($processeddata_item){
                $DB->update(
                    $processeddata_item->getTable(),
                    [
                        "processeddatas_id" => $input["plugin_dlteams_processeddatas_id"]
                    ],
                    [
                        "id" => $processeddata_item->fields["id"]
                    ]
                );

                Session::addMessageAfterRedirect("Relation ".PluginDlteamsProcessedData::getTypeName()." mis a jour avec succès");
            }


            //            mis a jour de concerned person
            $concernedperson_item = new PluginDlteamsConcernedPerson_Item();
            $concernedperson_item->getFromDBByCrit([
                "concernedpersons_id" => $record_oldfields["items_id"],
                "itemtype" => PluginDlteamsRecord::class,
                "items_id" => $record_oldfields["records_id"],
                "itemtype1" => $record_oldfields["itemtype1"],
                "items_id1" => $record_oldfields["items_id1"],
                "comment" => $record_oldfields["comment"],
            ]);
            if($concernedperson_item){
                $DB->update(
                    $concernedperson_item->getTable(),
                    [
                        "items_id1" => $input["plugin_dlteams_processeddatas_id"]
                    ],
                    [
                        "id" => $concernedperson_item->fields["id"]
                    ]
                );

                Session::addMessageAfterRedirect("Relation ".PluginDlteamsConcernedPerson::getTypeName()." mis a jour avec succès");
            }
        }


        if(isset($input["mandatory"])){
            $DB->update(
                $record_item->getTable(),
                [
                    "mandatory" => $input["mandatory"]
                ],
                [
                    "id" => $input["id"]
                ]
            );
        }

        Session::addMessageAfterRedirect("Traitement modifié avec succès");
        return true; // TODO: Change the autogenerated stub
    }

    static function countForRecord($record)
    {
        global $DB;
        $items_list = [];
        $iterator = $DB->request(self::getRequest($record));
        foreach ($iterator as $id => $data) {
            //while ($data = $iterator->next()) {
//            if (($data['itemtype'] === 'PluginDlteamsConcernedPerson' && $data['itemtype1'] == null)
//                || ($data['itemtype1'] === 'PluginDlteamsProcessedData' && $data['itemtype'] == null )
//                ) {
//                $items_list[$data['linkid']] = $data;
//                $used[$data['linkid']] = $data['linkid'];
//            }

            array_push($items_list, $data);
        }
//        var_dump(count($items_list));
        /*        highlight_string("<?php\n\$data =\n" . var_export($items_list, true) . ";\n?>");*/
//        die();

        return count($items_list);
    }

    static function getRequest($record)
    {
        return [
            'SELECT' => [
                'glpi_plugin_dlteams_records_items.id AS linkid',
                'glpi_plugin_dlteams_records_items.*',
//                'glpi_plugin_dlteams_concernedpersons.id AS gpdpid',
                'glpi_plugin_dlteams_concernedpersons.name AS gpdpname',
//                'glpi_plugin_dlteams_processeddatas.id AS gpddid',
                'glpi_plugin_dlteams_processeddatas.name AS gpddname',
                'glpi_plugin_dlteams_records_items.comment AS comment',
                //'glpi_plugin_dlteams_personaldatacategories.is_special_category AS gpdpsensible',
                'glpi_plugin_dlteams_records_items.mandatory AS mandatory',

            ],
            'FROM' => 'glpi_plugin_dlteams_records_items',
            'LEFT JOIN' => [
                'glpi_plugin_dlteams_concernedpersons' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_records_items' => "items_id",
                        'glpi_plugin_dlteams_concernedpersons' => "id",
                    ]
                ],
                'glpi_plugin_dlteams_processeddatas' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_records_items' => "items_id1",
                        'glpi_plugin_dlteams_processeddatas' => "id",

                    ],
                ],
            ],
            'ORDER' => [
                'glpi_plugin_dlteams_concernedpersons.name ASC',
                'gpddname  ASC'
            ],
//            'ORDER' => [
//                'gpdpname ASC',
//                'gpddname ASC'
//            ],
            /*'ORDER' => [
               'gpddname ASC',
               'gpdpname ASC'
            ],*/
            'WHERE' => [
                'OR' => [
                    [
                        'glpi_plugin_dlteams_records_items.records_id' => $record->fields['id'],
                        'glpi_plugin_dlteams_records_items.itemtype' => 'PluginDlteamsConcernedPerson',
                        'glpi_plugin_dlteams_records_items.itemtype1' => null,
                        'glpi_plugin_dlteams_concernedpersons.is_deleted' => 0,
                    ],
                    [
                        'glpi_plugin_dlteams_records_items.records_id' => $record->fields['id'],
                        'glpi_plugin_dlteams_records_items.itemtype' => null,
                        'glpi_plugin_dlteams_records_items.itemtype1' => 'PluginDlteamsProcessedData',
                        'glpi_plugin_dlteams_processeddatas.is_deleted' => 0,
                    ],
                    [
                        'glpi_plugin_dlteams_records_items.records_id' => $record->fields['id'],
                        'glpi_plugin_dlteams_records_items.itemtype' => 'PluginDlteamsConcernedPerson',
                        'glpi_plugin_dlteams_records_items.itemtype1' => 'PluginDlteamsProcessedData',
                        'glpi_plugin_dlteams_concernedpersons.is_deleted' => 0,
                        'glpi_plugin_dlteams_processeddatas.is_deleted' => 0,
//                    'glpi_plugin_dlteams_records_items.records_id' => $record->fields['id'],
                    ]
                ],
            ]
        ];
    }


    protected function toggleMandatory($input)
    {
        $this->fields['mandatory'] = $input['mandatory'];
        return $this->updateInDB(['mandatory']);
    }

    /**
     * Export in an array all the data of the current instanciated PluginDlteamsRecord_PersonalAndDataCategory
     * @param boolean $remove_uuid remove the uuid key
     *
     * @return array the array with all data (with sub tables)
     */
    public function exportToDB($remove_uuid = false, $subItems = [])
    {
        if ($this->isNewItem()) {
            return false;
        }

        $personal_data_catagory = $this->fields;
        return $personal_data_catagory;
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

    public function deleteObsoleteItems(CommonDBTM $container, array $exclude)
    {
    }


    public function post_purgeItem()
    {
//        purge relation 1
        if ($this->fields["itemtype"] && $this->fields["items_id"]) {
            $relation_item_str = $this->fields["itemtype"] . "_Item";
            if (!class_exists($relation_item_str))
                $relation_item_str = "PluginDlteams" . $relation_item_str;
            $relation_item = new $relation_item_str();

            $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

            $criteria = [
                "itemtype" => "PluginDlteamsRecord",
                "items_id" => $this->fields["records_id"],
                $relation_column_id => $this->fields["items_id"],
                "itemtype1" => $this->fields["itemtype1"],
                "items_id1" => $this->fields["items_id1"],
                "comment" => $this->fields["comment"]
            ];

            if ($relation_item->deleteByCriteria($criteria))
                Session::addMessageAfterRedirect("Relation " . $relation_item::getTypeName() . " supprimé avec succès");
        }


        //        purge relation 2
        if ($this->fields["itemtype1"] && $this->fields["items_id1"]) {

            $relation_item_str = $this->fields["itemtype1"] . "_Item";
            if (!class_exists($relation_item_str))
                $relation_item_str = "PluginDlteams" . $relation_item_str;
            $relation_item = new $relation_item_str();

            $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype1"]))) . "s_id";

            $criteria = [
                "itemtype" => "PluginDlteamsRecord",
                "items_id" => $this->fields["records_id"],
                $relation_column_id => $this->fields["items_id1"],
                "itemtype1" => $this->fields["itemtype"],
                "items_id1" => $this->fields["items_id"],
                "comment" => $this->fields["comment"]
            ];

            if ($relation_item->deleteByCriteria($criteria))
                Session::addMessageAfterRedirect("Relation " . $relation_item::getTypeName() . " supprimé avec succès");
        }
    }
}
