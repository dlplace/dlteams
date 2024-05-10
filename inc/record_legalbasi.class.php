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

require_once('record_storage.class.php');

class PluginDlteamsRecord_LegalBasi extends CommonDBRelation implements
    PluginDlteamsExportableInterface
{
    static public $itemtype_1 = 'PluginDlteamsRecord';
    static public $items_id_1 = 'plugin_dlteams_records_id';
    static public $itemtype_2 = 'pluginDlregisterLegalbasi';
    static public $items_id_2 = 'plugin_dlteams_legalbasis_id';
    static public $column1_id = '40';
    static public $column2_id = '41';
    static public $column3_id = '42';
    static public $column4_id = '43';


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
        return __("Legality and retention period", 'dlteams');
    }

    static function getTypeNameForClass($nb = 0)
    {
        return __("Eléments rattachés", 'dlteams');
    }

    /**
     * Export in an array all the data of the current instanciated PluginDlteamsRecord_LegalBasisAct
     * @param boolean $remove_uuid remove the uuid key
     *
     * @return array the array with all data (with sub tables)
     */
    public function exportToDB($remove_uuid = false, $subitems = [])
    {
        if ($this->isNewItem()) {
            return false;
        }

        $Legal_basics_acts = $this->fields;
        return $Legal_basics_acts;
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

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$item->canView()) {
            return false;
        }

        switch ($item->getType()) {
            case PluginDlteamsRecord::class :
                $nbitem = countElementsInTable('glpi_plugin_dlteams_allitems', ['itemtype1' => 'PluginDlteamsRecord', 'items_id1' => $item->fields['id'], 'itemtype2' => 'PluginDlteamsLegalbasi']);
                $nbitem = $nbitem + countElementsInTable('glpi_plugin_dlteams_records_storages', ['plugin_dlteams_records_id' => $item->fields['id']]);
                return self::createTabEntry(PluginDlteamsConservation_Element::getTypeName($nbitem), $nbitem);
            default:
                $nbitem = 0;
                return self::createTabEntry(static::getTypeNameForClass($nbitem), $nbitem);
        }
        return '';
    }


    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {

            case PluginDlteamsRecord::class :
//                PluginDlteamsRecord_Storage::showForRecord($item, $withtemplate);
                self::showForRecord($item, $withtemplate);
//                $n = new PluginDlteamsRecord_LegalBasi_ShowForRecord();
                self::showRecordStorageForRecord($item, $withtemplate);
//            PluginDlteamsStoragePeriod_Item::showForRecords($item);
                break;
            default:
                self::showItems($item, $withtemplate);
        }
        return true;
    }

    static function showItems(CommonGLPI $item, $withtemplate = 0)
    {


        global $DB;
        $id = $item->fields['id'];
        if (!$item->can($id, READ)) {
            return false;
        }


        $canedit = $item->can($id, UPDATE);
        $rand = mt_rand(1, mt_getrandmax());

        // Request that joins 3 table (not possible to do with CommonDBRelation methods)
        // Result is used lower to display a table
        $request = [
            'SELECT' => [
                'glpi_plugin_dlteams_storageperiods_items.id AS linkid',
                'glpi_plugin_dlteams_storageperiods_items.itemtype AS itemtype',
                'glpi_plugin_dlteams_storageperiods_items.items_id AS items_id',
                'glpi_plugin_dlteams_storageperiods.id AS glpi_plugin_dlteams_storageperiods_id',
                'glpi_plugin_dlteams_storageperiods.name AS duree',
                'glpi_plugin_dlteams_storageperiods.content AS content',
                'glpi_plugin_dlteams_storageperiods_items.comment AS comment',
//                'glpi_plugin_dlteams_storagetypes.plugin_dlteams_storagetypes_id AS stockage_id',
//                'glpi_plugin_dlteams_storagetypes.name AS stockage',
//                'glpi_plugin_dlteams_storageendactions.name AS enfinperiode',
            ],
            'FROM' => 'glpi_plugin_dlteams_storageperiods_items',
            'LEFT JOIN' => [
                'glpi_plugin_dlteams_storageperiods' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_storageperiods_items' => "storageperiods_id",
                        'glpi_plugin_dlteams_storageperiods' => "id",
                    ]
                ],
                'glpi_plugin_dlteams_storagetypes' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_storageperiods' => "plugin_dlteams_storagetypes_id",
                        'glpi_plugin_dlteams_storagetypes' => "id",
                    ]
                ],
//                'glpi_plugin_dlteams_storageendactions' => [ // Nouvelle jointure
//                    'FKEY' => [
//                        'glpi_plugin_dlteams_storageperiods_items' => "plugin_dlteams_storageendactions_id",
//                        'glpi_plugin_dlteams_storageendactions' => "id",
//                    ]
//                ]
            ],
            'ORDER' => ['glpi_plugin_dlteams_storageperiods_items.id ASC'],
//            'WHERE' => [
//                'glpi_plugin_dlteams_storageperiods_items.items_id' => $item->fields['id'],
//            ]
        ];

        $iterator = $DB->request($request);

//	  var_dump($iterator );
        /*               highlight_string("<?php\n\$data =\n" . var_export($iterator, true) . ";\n?>");*/
//	  die();
        $number = count($iterator);

        $items_list = [];
        $used = [];
        foreach ($iterator as $id => $data) {
            //while ($data = $iterator->next()) {
            $items_list[$data['linkid']] = $data;
            $used[$data['linkid']] = $data['linkid'];
        }


        /***form new**/
        if ($canedit) {
            echo "<div class='firstbloc'>";
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";
            $iden = $item->fields['id'];
            echo "<input type='hidden' name='plugin_dlteams_records_id' value='$iden' />";
            echo "<input type='hidden' name='itemtype1' value='" . $item->getType() . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $item->getID() . "' />";
            echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsStoragePeriod::getType() . "' />";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class=''><th colspan='3'>" . __("Conservation time", 'dlteams') .
                "<br><i style='font-weight: normal'>" .
                __("In application of legal basis, what are conservation times of the data retained", 'dlteams') .
                "</i></th>";
            echo "</tr>";
            /*echo "<th colspan='2'></th></tr>";*/
            /**add by me**/

            echo "<tr class='tab_bg_1'><td class='' colspan='1'>";
            echo __("Object", 'dlteams');
            echo "<br/><br/>";
            $types = PluginDlteamsItemType::getTypes();
            Dropdown::showSelectItemFromItemtypes(['itemtypes' => $types,
                'entity_restrict' => $item->fields['entities_id'],
                'checkright' => true,
                'used' => $used
            ]);
//            PluginDlteamsStoragePeriod::dropdown([
//                'addicon' => PluginDlteamsStoragePeriod::canCreate(),
//                'name' => 'plugin_dlteams_storageperiods_id',
//                'width' => '200px'
//            ]);
            echo "</td>";

            echo "<td width='' colspan='1'>";
            echo "<span>";
            echo __("Stockage", 'dlteams');
            echo "<br/><br/>";
            PluginDlteamsStorageType::dropdown([
                'addicon' => PluginDlteamsStorageType::canCreate(),
                'name' => 'plugin_dlteams_storagetypes_id',
                'width' => '250px',
            ]);
            echo "</span>";
            echo "</td>";

            echo "<td width='' colspan='2'>";
            echo "<span style='float:left' id='td2'>";
            echo __("Action Fin Periode", 'dlteams');
            echo "<br/><br/>";
            PluginDlteamsStorageEndAction::dropdown([
                'addicon' => PluginDlteamsStorageEndAction::canCreate(),
                'name' => 'plugin_dlteams_storageendactions_id',
                'width' => '200px',
            ]);
            echo "</span>";
            echo "<span style='float:left;margin-left:10px;margin-top:5px' id='td4'>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin-top:35px'>";
            echo "</span>";
            echo "</td>";

            echo "</table>";
            echo "<span style='float:right;width:100%;' id='td3'>";
            //echo __("Comment");
            echo "<br/><br/>";
            echo "<textarea type='text' style='width:400px;float:right;margin-right:24.5%' maxlength=1000 rows='3' name='storage_comment' class='storage_comment1' placeholder='commentaire'></textarea>";
            echo "</span>";
            Html::closeForm();
            echo "</div>";
        }

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

        $header_end .= "<th>" . __("Name") . "</th>";
        $header_end .= "<th>" . __("Type") . "</th>";
        $header_end .= "<th>" . __("Stockage") . "</th>";

        $header_end .= "<th>" . __("En fin de période") . "</th>";
        $header_end .= "<th>" . __("Comment") . "</th>";

        /*$header_end .= "<th>" . __("Comment") . "</th>";
        $header_end .= "<th>" . __("En fin de période") . "</th>";*/
        $header_end .= "</tr>";

        echo $header_begin . $header_top . $header_end;


        foreach ($items_list as $data) {
            /*            highlight_string("<?php\n\$data =\n" . var_export($data, true) . ";\n?>");*/
//            die();
            echo "<tr class='tab_bg_1'>";

            if ($canedit && $number) {
                echo "<td width='10'>";
//var_dump ($data['linkid']);
                Html::showMassiveActionCheckBox(__class__, $data['linkid']);
                echo "</td>";
            }
            foreach (["" => 'PluginDlteamsStoragePeriod'] as $table => $class) {
//                $link = $data[$table . 'gpdpname'];

//                if ($_SESSION['glpiis_ids_visible'] || empty($data[$table.'gpdpname'])) {
//                    $link = sprintf(__("%1\$s (%2\$s)"), $link, $data[$table.'id']);
//                }

            }
            $temp_item = new $data['itemtype']();
            $temp_item->getFromDB($data['items_id']);
            $name = "<a target='_blank' href=\"" . $temp_item::getFormURLWithID($temp_item->getField('id')) . "\">" . $temp_item->getField('name') . "</a>";

            $endaction = new PluginDlteamsStorageEndAction();
            $storagetype = new PluginDlteamsStorageType();
            $other_table_item_str = $data["itemtype"] . "_Item";
            $other_table_item = new $other_table_item_str();
            $i1_itemsid_column = strtolower(str_replace("PluginDlteams", "", $data["itemtype"])) . "s_id";
            $search_criteria = [
                'items_id' => $item->fields['id'],
                'itemtype' => 'PluginDlteamsStoragePeriod',
                $i1_itemsid_column => $data["items_id"],
            ];
            $items = $other_table_item->find($search_criteria);
            if ($items) {
                // Récupérer le premier enregistrement trouvé
                $temp_record = reset($items);
                $plugin_dlteams_storageendactions_id = $temp_record['plugin_dlteams_storageendactions_id'];
                $plugin_dlteams_storagetypes_id = $temp_record['plugin_dlteams_storagetypes_id'];
            }

            if (
                isset($plugin_dlteams_storageendactions_id)
                && isset($plugin_dlteams_storagetypes_id)
                && $endaction->getFromDB($plugin_dlteams_storageendactions_id)
                && $storagetype->getFromDB($plugin_dlteams_storagetypes_id)
                && isset($endaction->fields["name"])
            ) {
                $endactionname = $endaction->fields["name"];
                $endactionid = $endaction->fields["id"];
                $typeid = $storagetype->fields["id"];
                $typename = $storagetype->fields["name"];
                $endaction_link = "<a target='_blank' href=\"" . PluginDlteamsStorageEndAction::getFormURLWithID($endactionid) . "\">" . $endactionname . "</a>";
                $stockage = "<a target='_blank' href=\"" . PluginDlteamsStorageType::getFormURLWithID($typeid) . "\">" . $typename . "</a>";
            } else {
                $endaction_link = null;
                $stockage = null;
            }
            echo "<td class='left'>" . $name . "</td>";
            echo "<td class='left'>" . $data["itemtype"]::getTypeName() . "</td>";
            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $stockage . "</td>";
            /*echo "<td class='left'>" . ($data['storage_comment']?? "") . "</td>";
            echo "<td class='left'>" . $data['storage_action'] . "</td>";*/
            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">"
                . $endaction_link . "</td>";
            echo "<td class='left'>" . ($data['comment'] ?? "") . "</td>";
            echo "</tr>";
        }

        if ($iterator->count() > 25) {
            echo $header_begin . $header_bottom . $header_end;
        }
        echo "</table>";

        if ($canedit && $number) {
            $massive_action_params['ontop'] = false;
            if ($iterator->count() > 25) {
                Html::showMassiveActions($massive_action_params);
            }
            Html::closeForm();
        }
        echo "</div>";
    }

    static function showRecordStorageForRecord(PluginDlteamsRecord $record, $withtemplate = 0)
    {

        global $DB;
        $id = $record->fields['id'];
        if (!$record->can($id, READ)) {
            return false;
        }

        $canedit = $record->can($id, UPDATE);
        $rand = mt_rand(1, mt_getrandmax());

        // Request that joins 3 table (not possible to do with CommonDBRelation methods)
        // Result is used lower to display a table
        $request = [
            'SELECT' => [
                'glpi_plugin_dlteams_storageperiods_items.foreign_id AS linkid',
                'glpi_plugin_dlteams_storageperiods_items.itemtype AS itemtype',
                'glpi_plugin_dlteams_storageperiods_items.items_id AS items_id',
                'glpi_plugin_dlteams_storageperiods.id AS glpi_plugin_dlteams_storageperiods_id',
                'glpi_plugin_dlteams_storageperiods.name AS duree',
                'glpi_plugin_dlteams_storageperiods.content AS content',
                'glpi_plugin_dlteams_storageperiods_items.comment AS comment',
//                'glpi_plugin_dlteams_storagetypes.plugin_dlteams_storagetypes_id AS stockage_id',
//                'glpi_plugin_dlteams_storagetypes.name AS stockage',
//                'glpi_plugin_dlteams_storageendactions.name AS enfinperiode',
            ],
            'FROM' => 'glpi_plugin_dlteams_storageperiods_items',
            'LEFT JOIN' => [
                'glpi_plugin_dlteams_storageperiods' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_storageperiods_items' => "storageperiods_id",
                        'glpi_plugin_dlteams_storageperiods' => "id",
                    ]
                ],
                'glpi_plugin_dlteams_storagetypes' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_storageperiods' => "plugin_dlteams_storagetypes_id",
                        'glpi_plugin_dlteams_storagetypes' => "id",
                    ]
                ],
            ],
            'ORDER' => ['glpi_plugin_dlteams_storageperiods_items.id ASC'],
            'WHERE' => [
                'glpi_plugin_dlteams_storageperiods_items.items_id' => $record->fields['id'],
                'glpi_plugin_dlteams_storageperiods_items.itemtype' => 'PluginDlteamsRecord',
            ]
        ];

        $iterator = $DB->request($request);

//	  var_dump($iterator );
        /*               highlight_string("<?php\n\$data =\n" . var_export($iterator, true) . ";\n?>");*/
//	  die();
        $number = count($iterator);

        $items_list = [];
        $used = [];
        foreach ($iterator as $id => $data) {
            //while ($data = $iterator->next()) {
            $items_list[$data['linkid']] = $data;
            $used[$data['linkid']] = $data['linkid'];
        }


        /***form new**/
        if ($canedit) {
            echo "<div class='firstbloc'>";
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";
            $iden = $record->fields['id'];
            echo "<input type='hidden' name='plugin_dlteams_records_id' value='$iden' />";
            echo "<input type='hidden' name='itemtype1' value='" . $record->getType() . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $record->getID() . "' />";
            echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsStoragePeriod::getType() . "' />";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class=''><th colspan='3'>" . __("Conservation time", 'dlteams') .
                "<br><i style='font-weight: normal'>" .
                __("In application of legal basis, what are conservation times of the data retained", 'dlteams') .
                "</i></th>";
            echo "</tr>";
            /*echo "<th colspan='2'></th></tr>";*/
            /**add by me**/
            echo "<tr class='tab_bg_1'><td class='' colspan='1'>";
            echo __("Specify the retention periods and the future of the personal data concerned by this processing", 'dlteams');
            echo "<br/><br/>";
            PluginDlteamsStoragePeriod::dropdown([
                'addicon' => PluginDlteamsStoragePeriod::canCreate(),
                'name' => 'plugin_dlteams_storageperiods_id',
                'width' => '200px'
            ]);
            echo "</td>";

            echo "<td width='' colspan='1'>";
            echo "<span style='display:none' id='td1'>";
            echo __("Stockage", 'dlteams');
            echo "<br/><br/>";
            PluginDlteamsStorageType::dropdown([
                'addicon' => PluginDlteamsStorageType::canCreate(),
                'name' => 'plugin_dlteams_storagetypes_id',
                'width' => '250px',
            ]);
            echo "</span>";
            echo "</td>";

            echo "<td width='' colspan='2'>";
            echo "<span style='display:none;float:left' id='td2'>";
            echo __("Action Fin Periode", 'dlteams');
            echo "<br/><br/>";
            PluginDlteamsStorageEndAction::dropdown([
                'addicon' => PluginDlteamsStorageEndAction::canCreate(),
                'name' => 'plugin_dlteams_storageendactions_id',
                'width' => '200px',
            ]);
            echo "</span>";
            echo "<span style='display:none;float:left;margin-left:10px;margin-top:5px' id='td4'>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin-top:35px'>";
            echo "</span>";
            echo "</td>";
            /**add by me**/
            /*echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
            echo __("Action at the end of the period", 'dlteams');
            echo "</td><td width='50%' class='left'>";

            $choices = [
               __("Suppression", 'dlteams'),
               __("Archiving", 'dlteams'),
               __("Anonymisation", 'dlteams'),
               __("Restitution", 'dlteams'),
               "<input type='text' onfocus='checkRadio()' placeholder='" . __("Other") .
                  "' name='storage_action_other' value=''>",
            ];*/

            // Display radio checkboxes
            /*foreach ($choices as $id => $choice) {
               if ($id == 4)
                  echo "<input type='radio' id='$id' name='storage_action' value='other'><label for='$id'>  $choice</label><br>";
               else
                  echo "<input type='radio' id='$id' name='storage_action' value='$choice'><label for='$id'>  $choice</label><br>";
            }*/

            // JS for auto check when click on "other" text input
            //echo "<script>function checkRadio() {document.getElementById($id).checked = true;}</script>";

            /**add by me**/
            /*echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
            echo __("Stockage", 'dlteams');
            echo "</td><td width='50%' class='left'>";
            PluginDlteamsStorage::dropdown([
               'addicon'  => PluginDlteamsStorage::canCreate(),
               'name' => 'plugin_dlteams_storagetypes_id',
               'width' => '40%',
            ]);
            echo "</td></tr>";
            echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
            echo __("Conservation time", 'dlteams');
            echo "</td><td width='50%' class='left'>";
            PluginGenericobjectRgpdconservation::dropdown([
               'addicon'  => PluginGenericobjectRgpdconservation::canCreate(),
               'name' => 'plugin_genericobject_rgpdconservations_id',
               'width' => '40%',
            ]);
            echo "</td></tr>";
            echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
            echo __("Comment");
            echo "</td><td width='50%' class='left'>";
            echo "<textarea type='text' style='width:75%' maxlength=1000 rows='3' name='storage_comment'></textarea>";
            echo "</td></tr>";
            echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
            echo __("Action at the end of the period", 'dlteams');
            echo "</td><td width='50%' class='left'>";

            $choices = [
               __("Suppression", 'dlteams'),
               __("Archiving", 'dlteams'),
               __("Anonymisation", 'dlteams'),
               __("Restitution", 'dlteams'),
               "<input type='text' onfocus='checkRadio()' placeholder='" . __("Other") .
                  "' name='storage_action_other' value=''>",
            ];

            // Display radio checkboxes
            foreach ($choices as $id => $choice) {
               if ($id == 4)
                  echo "<input type='radio' id='$id' name='storage_action' value='other'><label for='$id'>  $choice</label><br>";
               else
                  echo "<input type='radio' id='$id' name='storage_action' value='$choice'><label for='$id'>  $choice</label><br>";
            }

            // JS for auto check when click on "other" text input
            echo "<script>function checkRadio() {document.getElementById($id).checked = true;}</script>";
            echo "</td></tr>";*/

            /*echo "<td colspan='20%' class='center'>";
            echo "<span style='display:none' id='td4'>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin-top:35px'>";
            echo "</span>";
            echo "</td></tr>";*/


            /*echo "<td colspan='20%' class='center'>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin-top:20px'>";
            echo "</td></tr>";*/
            echo "</table>";
            echo "<span style='display:none;float:right;width:100%;' id='td3'>";
            //echo __("Comment");
            echo "<br/><br/>";
            echo "<textarea type='text' style='width:400px;float:right;margin-right:24.5%' maxlength=1000 rows='3' name='storage_comment' class='storage_comment1' placeholder='commentaire'></textarea>";
            echo "</span>";
            Html::closeForm();
            echo "</div>";
        }

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

        $header_end .= "<th>" . __("Durée de conservation") . "</th>";
        $header_end .= "<th>" . __("Stockage") . "</th>";

        $header_end .= "<th>" . __("En fin de période") . "</th>";
        $header_end .= "<th>" . __("Comment") . "</th>";

        /*$header_end .= "<th>" . __("Comment") . "</th>";
        $header_end .= "<th>" . __("En fin de période") . "</th>";*/
        $header_end .= "</tr>";

        echo $header_begin . $header_top . $header_end;


        foreach ($items_list as $data) {
            /*            highlight_string("<?php\n\$data =\n" . var_export($data, true) . ";\n?>");*/
//            die();
            echo "<tr class='tab_bg_1'>";

            if ($canedit && $number) {
                echo "<td width='10'>";
//var_dump ($data['linkid']);
                Html::showMassiveActionCheckBox(__class__, $data['linkid']);
                echo "</td>";
            }
            foreach (["" => 'PluginDlteamsStoragePeriod'] as $table => $class) {
//                $link = $data[$table . 'gpdpname'];

//                if ($_SESSION['glpiis_ids_visible'] || empty($data[$table.'gpdpname'])) {
//                    $link = sprintf(__("%1\$s (%2\$s)"), $link, $data[$table.'id']);
//                }

            }
            $name = "<a target='_blank' href=\"" . $class::getFormURLWithID($data['glpi_plugin_dlteams_storageperiods_id']) . "\">" . $data["duree"] . "</a>";

//            $link2 = $data['name'];
//            if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
//                $link2 = sprintf(__("%1\$s (%2\$s)"), $link2, $data['id']);
//            }
//
//            $link3 = $data['name1'];
//            if ($_SESSION['glpiis_ids_visible'] || empty($data['name1'])) {
//                $link3 = sprintf(__("%1\$s (%2\$s)"), $link3, $data['id1']);
//            }
//            $name3 = "<a target='_blank' href=\"" . PluginDlteamsStorageEndAction::getFormURLWithID($data['id1']) . "\">" . $link3 . "</a>";
//            $name2 = "<a target='_blank' href=\"" . PluginDlteamsStorageType::getFormURLWithID($data['id']) . "\">" . $link2 . "</a>";
//            $record_item = new PluginDlteamsRecord_Item();
//            $search_criteria = [
//                'records_id' => $record->fields['id'],
//                'itemtype' => 'PluginDlteamsStoragePeriod',
//                'items_id' => $data["glpi_plugin_dlteams_storageperiods_id"],
//            ];
//            $records = $record_item->find($search_criteria);
//            $temp_record = null;
//            $plugin_dlteams_storageendactions_id = null;
//            if ($records) {
//                // Récupérer le premier enregistrement trouvé
//                $temp_record = reset($records);
//                $plugin_dlteams_storageendactions_id = $temp_record['plugin_dlteams_storageendactions_id'];
//            }

            $endaction = new PluginDlteamsStorageEndAction();
            $storagetype = new PluginDlteamsStorageType();
            $other_table_item_str = $data["itemtype"] . "_Item";
            $other_table_item = new $other_table_item_str();
            $i1_itemsid_column = strtolower(str_replace("PluginDlteams", "", $data["itemtype"])) . "s_id";
            $search_criteria = [
                'items_id' => $data["glpi_plugin_dlteams_storageperiods_id"],
                'itemtype' => 'PluginDlteamsStoragePeriod',
                $i1_itemsid_column => $data["items_id"],
            ];
            $items = $other_table_item->find($search_criteria);
            if ($items) {
                // Récupérer le premier enregistrement trouvé
                $temp_record = reset($items);
                $plugin_dlteams_storageendactions_id = $temp_record['plugin_dlteams_storageendactions_id'];
                $plugin_dlteams_storagetypes_id = $temp_record['plugin_dlteams_storagetypes_id'];
            }
//            var_dump($plugin_dlteams_storageendactions_id);
//            die();

            if (
                isset($plugin_dlteams_storageendactions_id)
                && isset($plugin_dlteams_storagetypes_id)
                && $endaction->getFromDB($plugin_dlteams_storageendactions_id)
                && $storagetype->getFromDB($plugin_dlteams_storagetypes_id)
                && isset($endaction->fields["name"])
            ) {
                $endactionname = $endaction->fields["name"];
                $endactionid = $endaction->fields["id"];
                $typeid = $storagetype->fields["id"];
                $typename = $storagetype->fields["name"];
                $endaction_link = "<a target='_blank' href=\"" . PluginDlteamsStorageEndAction::getFormURLWithID($endactionid) . "\">" . $endactionname . "</a>";
                $stockage = "<a target='_blank' href=\"" . PluginDlteamsStorageType::getFormURLWithID($typeid) . "\">" . $typename . "</a>";
            } else {
                $endaction_link = null;
                $stockage = null;
            }
            echo "<td class='left'>" . $name . "</td>";
            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $stockage . "</td>";
            /*echo "<td class='left'>" . ($data['storage_comment']?? "") . "</td>";
            echo "<td class='left'>" . $data['storage_action'] . "</td>";*/
            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">"
                . $endaction_link . "</td>";
            echo "<td class='left'>" . ($data['comment'] ?? "") . "</td>";
            echo "</tr>";
        }

        if ($iterator->count() > 25) {
            echo $header_begin . $header_bottom . $header_end;
        }
        echo "</table>";

        if ($canedit && $number) {
            $massive_action_params['ontop'] = false;
            if ($iterator->count() > 25) {
                Html::showMassiveActions($massive_action_params);
            }
            Html::closeForm();
        }
        echo "</div>";
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
        $iterator = $DB->request(self::getRequest($record));
        $number = count($iterator);
        //var_dump ($number) ;
        $items_list = [];
        $used = [];

        foreach ($iterator as $id => $data) {
            //while ($data = $iterator->next()) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }
        // choose legalbasis for this processing
        if ($canedit) {
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsLegalBasi_Item::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . $record->getType() . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $record->getID() . "' />";
            echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsLegalbasi::getType() . "' />";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th colspan='3'>" . __("Add Legal Basis", 'dlteams') .
                "<br><i style='font-weight: normal'>" .
                __("Organism must be based on legal bases that allow this processing", 'dlteams') .
                "</i></th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'><td class='' width='20%'>" . __("Add Legal Basis to record", 'dlteams');
            echo "</td><td width='30%' class='left'>";
            PluginDlteamsLegalbasi::dropdown([
                'addicon' => PluginDlteamsLegalbasi::canCreate(),
                'name' => 'items_id',
                'width' => '250px',
                //'used' => $used
            ]);

            echo "</td>";
            echo "<td width='30%' class='left'>";

            echo "<textarea type='text' maxlength=600 rows='3' name='comment' class='storage_comment2' style='display:none;width:85%;margin-top:4px' placeholder='Commentaire' id='update_comment_textarea'></textarea>";
            echo "</td>";
            echo "<td width='' class='left'>";
            echo "<input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin-left:-63px;display:none' id='btnLegalbasi'>";
            echo "</td></tr>";
            echo "</table>";
            Html::closeForm();
        }

        // send data to form
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
                $header_top .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsConservation_Element::class . $rand);

                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsConservation_Element::class . $rand);
                $header_end .= "</th>";
            }

            $header_end .= "<th>" . __("Name") . "</th>";
            $header_end .= "<th>" . __("Type") . "</th>";
            $header_end .= "<th>" . __("Content") . "</th>";
            $header_end .= "<th>" . __("Comment") . "</th>";
            $header_end .= "</tr>";

            echo $header_begin . $header_top . $header_end; // affiche la ligne d'entete

            //foreach ($items_list as $data) {
            foreach ($iterator as $data) {
                if ($data['name']) {
                    echo "<tr class='tab_bg_1'>";

                    if ($canedit && $number) {
                        echo "<td width='10'>";
//var_dump ($data['linkid']);
                        Html::showMassiveActionCheckBox(PluginDlteamsConservation_Element::class, $data['linkid']);
                        echo "</td>";
                    }
                    foreach ([
                                 "" => 'PluginDlteamsLegalBasi',
                             ] as $table => $class) {
                        $link = $data[$table . 'name'];
                        if ($_SESSION['glpiis_ids_visible'] || empty($data[$table . 'name'])) {
                            $link = sprintf(__("%1\$s (%2\$s)"), $link, $data[$table . 'id']);
                        }

                        $name = "<a target='_blank' href=\"" . $class::getFormURLWithID($data[$table . 'id']) . "\">" . $link . "</a>";
                        //$type = "<a href=\"" . $class::getFormURLWithID($data[$table.'id']) . "\">" . $link1 . "</a>";

                        // dysplay check box & name
                        echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                        echo " style='width:350px'>" . $name . "</td>";

                        // dysplay nametype
                        echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                        echo " style='width:150px'>";
                        //if($type){ 	    // if($data['nametype']){
                        echo($data['typename']);  //   echo($data['nametype']);
                        // }
                        echo "</td>";

                        // dysplay content
                        echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                        echo " style='width:420px'>";
                        if ($data['content']) {
                            echo $data['content'];
                        }
                        echo "</td>";

                        // dysplay comment
                        echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                        echo ">";
                        if ($data['comment']) {
                            echo $data['comment'];
                        }
                        echo "</td>";

                    }
                    echo "</tr>";
                }
            }

            if ($iterator->count() > 25) {
                echo $header_begin . $header_bottom . $header_end;
            }
            echo "</table>";

            if ($canedit && $number) {
                $massive_action_params['ontop'] = false;
                if ($iterator->count() > 25) {
                    Html::showMassiveActions($massive_action_params);
                }
                Html::closeForm();
            }

            echo "</div>";
        }


//        show record storage now
    }

    /*function getForbiddenStandardMassiveAction() {
       $forbidden = parent::getForbiddenStandardMassiveAction();
       $forbidden[] = 'update';
       return $forbidden;
    }*/

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

    function getSpecificMassiveActions($checkitem = NULL)
    {
        $actions = [];

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

    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        switch ($ma->getAction()) {
            case 'delete_dlteams_action':
                foreach ($ids as $id) {
                    $item = new PluginDlteamsStoragePeriod_Item();
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
                        $ma->__set("remainings", ["PluginDlteamsStoragePeriod_Item" => [$id => $id]]);
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    }
                }
                break;
            case 'update_dlteams_action':
                $temp = new PluginDlteamsConservation_Element();
                $updatable_columns = $temp->rawSearchOptions();
                $parts = explode(':', $ma->POST['id_field']);
                $id_modif = $parts[1];

                switch ($id_modif) {
                    case static::$column1_id:
                        foreach ($ids as $id) {
                            $base_item = new PluginDlteamsRecord_Item();
                            if ($base_item->getFromDB($id)) {
//                                var_dump($base_item->fields);
                                $array_values = array_values($ma->POST);

//                            suppression dans storage period item
                                $itemtype_item_str = $ma->POST["itemtype"] . "_Item";
                                $itemtype_item = new $itemtype_item_str();
                                $itemtype_item_id_column = strtolower(str_replace("PluginDlteams", "", $base_item->fields["itemtype"])) . "s_id";
                                $t = $itemtype_item->deleteByCriteria([
                                    $itemtype_item_id_column => $base_item->fields["items_id"],
                                    "itemtype" => "PluginDlteamsRecord",
                                    "items_id" => $base_item->fields[strtolower(str_replace("PluginDlteams", "", "PluginDlteamsRecord")) . "s_id"],
                                    "foreign_id" => $base_item->fields["id"]
                                ]);

//                                ajout de la nouvelle ligne
                                $ee = $itemtype_item->add([
                                    $itemtype_item_id_column => $array_values[1],
                                    "itemtype" => "PluginDlteamsRecord",
                                    "items_id" => $base_item->fields[strtolower(str_replace("PluginDlteams", "", "PluginDlteamsRecord")) . "s_id"],
                                    "foreign_id" => $base_item->fields["id"]
                                ]);


                                $t = $base_item->update([
                                    'items_id' => $array_values[1], // 'items_id' parce que case case static::$column1_id ou static::$column1_id est 49 ce qui correspond a l'id de la fonction rawSearchOptions
                                    'id' => $id
                                ]);
                            }
                        }
                        break;
                    case static::$column2_id:
                        foreach ($ids as $id) {
                            $base_item = new PluginDlteamsRecord_Item();
                            if ($base_item->getFromDB($id)) {
                                $array_values = array_values($ma->POST);


                                $t = $base_item->update([
                                    'plugin_dlteams_storagetypes_id' => $array_values[1], // 'items_id' parce que case case static::$column1_id ou static::$column1_id est 49 ce qui correspond a l'id de la fonction rawSearchOptions
                                    'id' => $id
                                ]);
                            }
                        }
                        break;
                    case static::$column3_id:
                        foreach ($ids as $id) {
                            $base_item = new PluginDlteamsRecord_Item();
                            if ($base_item->getFromDB($id)) {
                                $array_values = array_values($ma->POST);


                                $t = $base_item->update([
                                    'plugin_dlteams_storageendactions_id' => $array_values[1], // 'items_id' parce que case case static::$column1_id ou static::$column1_id est 49 ce qui correspond a l'id de la fonction rawSearchOptions
                                    'id' => $id
                                ]);
                            }
                        }
                        break;
                    case static::$column4_id:
                        foreach ($ids as $id) {
                            $base_item = new PluginDlteamsRecord_Item();
                            if ($base_item->getFromDB($id)) {
//                                var_dump($base_item->fields);
                                $array_values = array_values($ma->POST);

//                            suppression dans storage period item
                                $itemtype_item_str = $ma->POST["itemtype"] . "_Item";
                                $itemtype_item = new $itemtype_item_str();
                                $itemtype_item_id_column = strtolower(str_replace("PluginDlteams", "", $base_item->fields["itemtype"])) . "s_id";
                                $t = $itemtype_item->deleteByCriteria([
                                    $itemtype_item_id_column => $base_item->fields["items_id"],
                                    "itemtype" => "PluginDlteamsRecord",
                                    "items_id" => $base_item->fields[strtolower(str_replace("PluginDlteams", "", "PluginDlteamsRecord")) . "s_id"],
                                    "foreign_id" => $base_item->fields["id"]
                                ]);

//                                ajout de la nouvelle ligne
                                $ee = $itemtype_item->add([
                                    $itemtype_item_id_column => $base_item->fields["items_id"],
                                    "itemtype" => "PluginDlteamsRecord",
                                    "items_id" => $base_item->fields[strtolower(str_replace("PluginDlteams", "", "PluginDlteamsRecord")) . "s_id"],
                                    "foreign_id" => $base_item->fields["id"],
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
                $ma->__set("remainings", ["PluginDlteamsRecord_Item" => [$id => $id]]);
                $ma->itemDone('PluginDlteamsRecord_Item', $id, MassiveAction::ACTION_OK);
                break;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    // show table list
    static function getRequest($record)
    {
        /*$sub1 = new QuerySubQuery([
           'SELECT' => [
              'glpi_plugin_dlteams_allitems.id AS linkid',
              'glpi_plugin_dlteams_legalbasis.id AS id',
              'glpi_plugin_dlteams_legalbasis.name AS name',
              'glpi_plugin_dlteams_legalbasis.plugin_dlteams_legalbasistypes_id AS type',
              'glpi_plugin_dlteams_legalbasistypes.name AS typename',
              'glpi_plugin_dlteams_legalbasis.content AS content',
              //'glpi_plugin_dlteams_allitems.comment AS comment',
           ],
           'FROM' => 'glpi_plugin_dlteams_allitems',
           'LEFT JOIN' => [
              'glpi_plugin_dlteams_legalbasis' => [
                 'FKEY' => [
                    'glpi_plugin_dlteams_allitems' => "items_id2",
                    'glpi_plugin_dlteams_legalbasis' => "id",
                 ]
              ],
           ],
               'JOIN' => [
              'glpi_plugin_dlteams_legalbasistypes' => [
                 'FKEY' => [
                    'glpi_plugin_dlteams_legalbasis' => "plugin_dlteams_legalbasistypes_id",
                    'glpi_plugin_dlteams_legalbasistypes' => "id"
                 ]
           ],
           ],
           'ORDER' => [ 'typename ASC', 'name ASC'],
           'WHERE' => [
              'glpi_plugin_dlteams_allitems.items_id2' => $record->fields['id'],
              'glpi_plugin_dlteams_allitems.itemtype2' => $record->getType(),
              'glpi_plugin_dlteams_allitems.itemtype1' => PluginDlteamsLegalbasi::getType(),
           ]
        ]);

        $sub2 = new QuerySubQuery([
           'SELECT' => [
              'glpi_plugin_dlteams_allitems.id AS linkid',
              'glpi_plugin_dlteams_legalbasis.id AS id',
              'glpi_plugin_dlteams_legalbasis.name AS name',
              'glpi_plugin_dlteams_legalbasis.plugin_dlteams_legalbasistypes_id AS type',
              'glpi_plugin_dlteams_legalbasistypes.name AS typename',
              'glpi_plugin_dlteams_legalbasis.content AS content',
              //'glpi_plugin_dlteams_allitems.comment AS comment',
           ],
           'FROM' => 'glpi_plugin_dlteams_allitems',
           'LEFT JOIN' => [
              'glpi_plugin_dlteams_legalbasis' => [
                 'FKEY' => [
                    'glpi_plugin_dlteams_allitems' => "items_id2",
                    'glpi_plugin_dlteams_legalbasis' => "id",
                 ]
              ],
           ],
               'JOIN' => [
              'glpi_plugin_dlteams_legalbasistypes' => [
                 'FKEY' => [
                    'glpi_plugin_dlteams_legalbasis' => "plugin_dlteams_legalbasistypes_id",
                    'glpi_plugin_dlteams_legalbasistypes' => "id"
                 ]
           ],
           ],
           'ORDER' => [ 'typename ASC', 'name ASC'],
           'WHERE' => [
              'glpi_plugin_dlteams_allitems.items_id1' => $record->fields['id'],
              'glpi_plugin_dlteams_allitems.itemtype1' => $record->getType(),
              'glpi_plugin_dlteams_allitems.itemtype2' => PluginDlteamsLegalbasi::getType(),
           ]
        ]);

        $union = new QueryUnion([$sub1,$sub2]);
        return ['FROM' => $union];*/

        return [
            'SELECT' => [
                'glpi_plugin_dlteams_legalbasis_items.id AS linkid',
                'glpi_plugin_dlteams_legalbasis.id AS id',
                'glpi_plugin_dlteams_legalbasis.name AS name',
                'glpi_plugin_dlteams_legalbasis.plugin_dlteams_legalbasistypes_id AS type',
                'glpi_plugin_dlteams_legalbasistypes.name AS typename',
                'glpi_plugin_dlteams_legalbasis.content AS content',
                'glpi_plugin_dlteams_legalbasis_items.comment AS comment',
            ],
            'FROM' => 'glpi_plugin_dlteams_legalbasis_items',
            'LEFT JOIN' => [
                'glpi_plugin_dlteams_legalbasis' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_legalbasis_items' => "legalbasis_id",
                        'glpi_plugin_dlteams_legalbasis' => "id",
                    ]
                ],
            ],
            'JOIN' => [
                'glpi_plugin_dlteams_legalbasistypes' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_legalbasis' => "plugin_dlteams_legalbasistypes_id",
                        'glpi_plugin_dlteams_legalbasistypes' => "id"
                    ]
                ],
            ],
            //'ORDER' => [ 'typename DESC', 'name ASC'],
            'ORDER' => ['glpi_plugin_dlteams_legalbasistypes.id ASC', 'name ASC'],
            'WHERE' => [
                'glpi_plugin_dlteams_legalbasis_items.items_id' => $record->fields['id'],
                'glpi_plugin_dlteams_legalbasis_items.itemtype' => 'PluginDlteamsRecord'
                //'glpi_plugin_dlteams_allitems.plugin_dlteams_legalbasis_id' => "PluginDlteamsLegalbasi"
                /*'glpi_plugin_dlteams_allitems.items_id2' => $record->fields['id'],
                'glpi_plugin_dlteams_allitems.itemtype2' => $record->getType(),
                'glpi_plugin_dlteams_allitems.itemtype1' => PluginDlteamsLegalbasi::getType(),*/
            ]
        ];
    }

    function rawSearchOptions()
    {

        $tab = [];

        /*$tab[] = [
           'id' => 'datasubjectscategory',
           'name' => PluginGenericobjectPersonnesconcernee::getTypeName(0)
        ];*/

        $tab[] = [
            'id' => static::$column1_id,
            'table' => PluginDlteamsStoragePeriod::getTable(),
            'field' => 'name',
            'name' => __("Durée de conservation"),
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

        $tab[] = [
            'id' => static::$column2_id,
            'table' => PluginDlteamsStorageType::getTable(),
            'field' => 'name',
            'name' => __("Stockage"),
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

        $tab[] = [
            'id' => static::$column3_id,
            'table' => PluginDlteamsStorageEndAction::getTable(),
            'field' => 'name',
            'name' => __("En fin de période"),
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

    public function deleteObsoleteItems(CommonDBTM $container, array $exclude)
    {
    }
}

?>

<script>
    $(document).ready(function () {
        $('select[name=items_id]').on('change', function () {
            //
            if ($(this).val() != '0') {
                document.getElementById("btnLegalbasi")?.style.display = "block";
                // document.getElementById("inputLegalbasi")?.style.display = "block";
                var content = $(this).val();
                $.ajax({
                    url: "getCommentLegalBasi.php",
                    type: 'POST',
                    async: false,
                    data: {content: content},
                    success: function (data) {
                        //alert(data);
                        //userData = json.parse(data);
                        //alert(json.parse(data));
                        $('.storage_comment2').val(data);
                        //alert($("#email").val());
                    }
                });
            } else {
                document.getElementById("btnLegalbasi")?.style.display = "none";
                // document.getElementById("inputLegalbasi")?.style.display = "none";
            }
            //
        });
    });
</script>
