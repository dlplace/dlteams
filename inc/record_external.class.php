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

require_once('record_item.class.php');
require_once('record_juridique.class.php');

class PluginDlteamsRecord_External extends CommonDBTM
{

    static public $itemtype_1 = 'PluginDlteamsRecord';
    static public $items_id_1 = 'plugin_dlteams_records_id';
    static public $itemtype_2 = PluginDlteamsConcernedPerson::class;
    static public $items_id_2 = 'plugin_dlteams_concernedpersons_id';

    static public $column1_id = '50';
    static public $column2_id = '51';


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
    function canEdit($id) {return true;}

    public function __construct()
    {
        self::forceTable("glpi_plugin_dlteams_records_items");
    }

    static function getTypeName($nb = 0)
    {

        return _n("Actor, subcontractor, recipient", "Actors, subcontractors, recipients", $nb, 'dlteams');
    }


    static function countForItem(CommonDBTM $item)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable('glpi_plugin_dlteams_records_externals', ['plugin_dlteams_records_id' => $item->getID()]);
    }


    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$item->canView()) {
            return false;
        }

            global $DB;
        switch ($item->getType()) {
            case PluginDlteamsRecord::class :
//                var_dump(count(PluginDlteamsRecord_External::getListForItem($item)));
//                die();
                $nb = count(PluginDlteamsRecord_External::getListForItem($item)) + count($DB->request(PluginDlteamsActeur_Item::getRequest($item)));
                return self::createTabEntry(PluginDlteamsRecord_External::getTypeName($nb), $nb);
        }

        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case PluginDlteamsRecord::class :
                PluginDlteamsActeur_Item::showItemsForItemType($item, $withtemplate);

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

        $canedit = $record->can($id, UPDATE);
        $rand = mt_rand(1, mt_getrandmax());

        $iterator = PluginDlteamsRecord_External::getListForItem($record);

        $number = count($iterator);

        $items_list = [];
        $used = [];
        foreach ($iterator as $id => $data) {
            // while ($data = $iterator->next()) {
            $items_list[$data['linkid']] = $data;

            $used[$data['linkid']] = $data['linkid'];
        }
        //print_r($items_list);die;
        if ($canedit) {
            echo "<div class='firstbloc'>";
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";
            $iden = $record->fields['id'];

            echo "<input type='hidden' name='plugin_dlteams_records_id' value='$iden' />";
            echo "<table class='tab_cadre_fixe'>";


            /**add y me**/
            echo "<tr class='tab_bg_2'><th colspan='3'>" . __("Process", 'dlteams') . "</th></tr>";
            echo "<tr class='tab_bg_1'><td>";
            // echo __("Process", 'dlteams') .
            echo "<br><i>" . __("The UE law says that user should give and informed consent if data is treated outside of UE", 'dlteams') . "</i>";
            echo "</td><td>";
            $checked = json_decode($record->fields['external_process'] ?? '{}', true);
            $choices = [
                __("France", 'dlteams'),
                __("European Union", 'dlteams'),
                __("World", 'dlteams'),
                "<input type='text' disabled=true placeholder='" . __("Other") .
                "' name='process_other' id='process_other' value='" . ($checked['other'] ?? '') . "'>",
            ];
            echo PluginDlteamsUtils::displayCheckboxes($checked, $choices, 'process');
            echo "</td>";
            echo "<td class='left' style='padding-left:0px'>";
            echo "<input type='submit' name='add2' value=\"" . _sx('button', 'Save') . "\" class='submit' style='margin-left:0px'>";
            echo "</td></tr>";
            /**add by me**/

            echo "<tr class='tab_bg_2'><th colspan='3'>" . __("Recipient : to which organism or persons data are communicated", 'dlteams') . "</th></tr>";

            echo "<tr class='tab_bg_1'><td th colspan='3'>";
            // echo __("Organismes ou Personnes ", 'dlteams');
            //  echo "<br/><br/>";

            /* Test Field */
            global $CFG_GLPI;

            $id = $record->fields['id'];
            if (!$record->can($id, READ)) {
                return false;
            }

            $canedit = PluginDlteamsRecord::canUpdate();
            $rand = mt_rand(1, mt_getrandmax());

            $options['canedit'] = $canedit;
            $options['formtitle'] = __("Right exercice", 'dlteams');
            $record->fields['consent_type'] = 0;
//-------------------------------------------------------------------------------------------
            $rand = Dropdown::showFromArray("consent_type", [
                __("------", 'dlteams'),
                __("Personnes Concernees", 'dlteams'),
                __("Tiers Categories", 'dlteams'),
                __("Tiers", 'dlteams'),

            ], [
                'value' => $record->fields['consent_type'],
                'width' => '150px',
            ]);
            $params = [
                'consent_type' => '__VALUE__',
                'plugin_dlteams_records_id' => $id,

            ];
            Ajax::updateItemOnSelectEvent(
                "dropdown_consent_type$rand",
                'consent_row1',
                $CFG_GLPI['root_doc'] . '/marketplace/dlteams/ajax/record_external_dropdown.php',
                $params
            );
            // echo "</td>";
            // echo "<td id='consent_row1' colspan='1' style='float:left'>";
            echo "<span id='consent_row1' style='margin-left:10px!important'>";
            self::showConsent($record, $record->fields);

            echo "</td>";

            echo "</tr>";

            echo "</table>";
            Html::closeForm();
            echo "</div>";

        }

        // Display recipients
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

        $header_end .= "<th>" . __("Recipient") . "</th>";
        $header_end .= "<th>" . __("Type") . "</th>";
        $header_end .= "<th>" . __("Reason of the sending", 'dlteams') . "</th>";
        $header_end .= "<th>" . __("Comment") . "</th>";
        $header_end .= "</tr>";

        echo $header_begin . $header_top . $header_end;
        //var_dump($items_list);

        foreach ($items_list as $data) {
            echo "<tr class='tab_bg_1'>";

            if ($canedit && $number) {
                echo "<td width='10'>";
                Html::showMassiveActionCheckBox(__class__, $data['linkid']);
                echo "</td>";
            }

            $itemtype_str = $data["itemtype"];
            $itemtype_object = new $itemtype_str();
            $itemtype_object->getFromDB($data['items_id']);
            $link = $itemtype_object->fields['name'];

            if ($_SESSION['glpiis_ids_visible'] || empty($link)) {
                $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
            }
            $name = "<a target='_blank' href=\"" . $data['itemtype']::getFormURLWithID($data['items_id']) . "\">" . $link . "</a>";

//            Recipient
            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $name . "</td>";
            /*add by me**/


//            Type
            echo "<td class='left'>" . ($data['itemtype']::getTypeName()) . "</td>";


            $itemtype_str = $data["itemtype1"];
            $itemtype_object = new $itemtype_str();
            $itemtype_object->getFromDB($data['items_id1']);
            $link1 = $itemtype_object->fields['name'];
            if ($_SESSION['glpiis_ids_visible'] || empty($data['name1'])) {
                $link1 = sprintf(__("%1\$s"), $link1);
            }
            $name1 = "<a target='_blank' href='../front/sendingreason.form.php?id=" . $data['items_id1'] . "'>" . $link1 . "</a>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $name1 . "</td>";
            /**add by me**/

            /*echo "<td class='left'>" . $data['recipient_reason'] . "</td>";*/
            echo "<td class='left'>" . ($data['comment'] ?? "") . "</td>";
            echo "</tr>";
        }

        if ($iterator->count() > 10) {
            echo $header_begin . $header_bottom . $header_end;
        }
        echo "</table>";

        if ($canedit && $number) {
            $massive_action_params['ontop'] = false;
            // Html::showMassiveActions($massive_action_params);
            Html::closeForm();
        }

        echo "</div>";
    }

    static function getListForItem(CommonDBTM $item)
    {

        global $DB;
//      $params = static::getListForItemParams($item, true);
//      $params['SELECT'][] = "glpi_plugin_dlteams_records_externals.recipient_reason AS recipient_reason";
//      $params['SELECT'][] = "glpi_plugin_dlteams_records_externals.recipient_comment AS recipient_comment";

        $request = [
            'SELECT' => [
                'glpi_plugin_dlteams_records_items.id AS linkid',
                'glpi_plugin_dlteams_records_items.id AS id',
                'glpi_plugin_dlteams_records_items.itemtype AS itemtype',
                'glpi_plugin_dlteams_records_items.items_id AS items_id',
                'glpi_plugin_dlteams_records_items.itemtype1 AS itemtype1',
                'glpi_plugin_dlteams_records_items.items_id1 AS items_id1',
                'glpi_plugin_dlteams_records_items.comment AS comment',
            ],
            'FROM' => 'glpi_plugin_dlteams_records_items',
            'ORDER' => ['glpi_plugin_dlteams_records_items.id ASC'],
            'OR' => [
                [
                    'glpi_plugin_dlteams_records_items.records_id' => $item->fields['id'],
                    'glpi_plugin_dlteams_records_items.itemtype' => 'PluginDlteamsConcernedPerson',
                    'glpi_plugin_dlteams_records_items.itemtype1' => 'PluginDlteamsSendingReason',
                ],
                [
                    'glpi_plugin_dlteams_records_items.records_id' => $item->fields['id'],
                    'glpi_plugin_dlteams_records_items.itemtype' => 'PluginDlteamsThirdPartyCategory',
                    'glpi_plugin_dlteams_records_items.itemtype1' => 'PluginDlteamsSendingReason',
                ],
                [
                    'glpi_plugin_dlteams_records_items.records_id' => $item->fields['id'],
                    'glpi_plugin_dlteams_records_items.itemtype' => 'Supplier',
                    'glpi_plugin_dlteams_records_items.itemtype1' => 'PluginDlteamsSendingReason',
                ],
            ]
        ];

        //print_r($params);die;
        $iterator = $DB->request($request);
        return $iterator;
        //$iterator = $DB->request($params);
    }


    /**add by me**/
    function rawSearchOptions()
    {

        $tab = [];



        $tab[] = [
            'id' => "17",
            'table' => PluginDlteamsConcernedPerson::getTable(),
            'field' => 'name',
            'name' => __("Personnes concernées"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'dropdown',
            'searchtype' => ['equals', 'notequals'],
        ];

        $tab[] = [
            'id' => "18",
            'table' => PluginDlteamsThirdPartyCategory::getTable(),
            'field' => 'name',
            'name' => __("Tiers Categories"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'dropdown',
            'searchtype' => ['equals', 'notequals'],
        ];

        $tab[] = [
            'id' => "19",
            'table' => Supplier::getTable(),
            'field' => 'name',
            'name' => __("tiers"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'dropdown',
            'searchtype' => ['equals', 'notequals'],
        ];

        $tab[] = [
            'id' => "23",
            'table' => PluginDlteamsSendingReason::getTable(),
            'field' => 'name',
            'name' => __("Motif Envoi"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'dropdown',
            'searchtype' => ['equals', 'notequals'],
        ];

        $tab[] = [
            'id' => "24",
            'table' => PluginDlteamsRecord_External::getTable(),
            'field' => 'recipient_comment',
            'name' => __("Commentaire"),
            'massiveaction' => true,
            'datatype' => 'text'
        ];

        return $tab;
    }

    public function update(array $input, $history = 1, $options = [])
    {
        $record_item = new PluginDlteamsRecord_Item();
        $record_item->getFromDB($input["id"]);
        $record_item_oldfields = $record_item->fields;

//
        global $DB;
        if(isset($input["plugin_dlteams_concernedpersons_id"])){
            $DB->update(
                $record_item->getTable(),
                [
                    "items_id" => $input["plugin_dlteams_concernedpersons_id"],
                    "itemtype" => PluginDlteamsConcernedPerson::class,
                ],
                [
                    "id" => $input["id"]
                ]
            );


//            mis a jour de record
            $relation_item_str = $this->fields["itemtype"] . "_Item";
            if (!class_exists($relation_item_str))
                $relation_item_str = "PluginDlteams" . $relation_item_str;
            $relation_item = new $relation_item_str();

            $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";
            $criteria = [
                $relation_column_id => $record_item_oldfields["items_id"],
                "itemtype" => $record_item_oldfields["itemtype"],
                "items_id" => $record_item_oldfields["records_id"],
                "comment" => $record_item_oldfields["comment"],
            ];


            if($DB->delete($relation_item->getTable(), $criteria)){
                $relation_item = new PluginDlteamsConcernedPerson_Item();
                $relation_item->add([
                    "itemtype" => PluginDlteamsRecord::class,
                    "concerndedpersons_id" => $input["plugin_dlteams_concernedpersons_id"],
                    "items_id" => $record_item_oldfields["records_id"],
                    "comment" => $record_item_oldfields["comment"],
                ]);

                Session::addMessageAfterRedirect("Relation ".PluginDlteamsConcernedPerson::getTypeName()." mis a jour avec succès");
            }

        }

        if(isset($input["recipient_comment"])){
            $DB->update(
                $record_item->getTable(),
                [
                    "comment" => $input["recipient_comment"],
                ],
                [
                    "id" => $input["id"]
                ]
            );


//            mis a jour de record
            $relation_item_str = $record_item->fields["itemtype"] . "_Item";

            if (!class_exists($relation_item_str))
                $relation_item_str = "PluginDlteams" . $relation_item_str;
            $relation_item = new $relation_item_str();

            $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";
            $criteria = [
                $relation_column_id => $record_item_oldfields["items_id"],
                "itemtype" => $record_item_oldfields["itemtype"],
                "items_id" => $record_item_oldfields["records_id"],
                "comment" => $record_item_oldfields["recipient_comment"],
            ];


            if($DB->delete($relation_item->getTable(), $criteria)){
                $relation_item = new $relation_item_str();
                $relation_item->add([
                    "itemtype" => PluginDlteamsRecord::class,
                    $relation_column_id => $record_item_oldfields["items_id"],
                    "items_id" => $record_item_oldfields["records_id"],
                    "comment" => $record_item_oldfields["recipient_comment"],
                ]);

                Session::addMessageAfterRedirect("Relation ".$relation_item_str::getTypeName()." mis a jour avec succès");
            }

        }

        Session::addMessageAfterRedirect("Traitement modifié avec succès");
        return true;
    }


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

    static function showConsent(PluginDlteamsRecord $record, $data = [])
    {
        if ($data['consent_type'] == 0) {
        } else if ($data['consent_type'] == 3) {
            echo "<span style='margin-left:10px;margin-top:20px;width:170px'>";
            //echo __("Tiers ", 'dlteams');
            //echo "<br/><br/>";

            Supplier::dropdown([
                'addicon' => Supplier::canCreate(),
                'name' => "suppliers_id",
                'display_emptychoice' => false,
                'width' => '150px'
            ]);
            echo "</span>";

//
            echo "<span style='margin-left:90px;margin-top:20px;width:280px'>";
            echo __("Motif d'envoi ", 'dlteams');
            //echo "<br/><br/>";
            PluginDlteamsSendingReason::dropdown([
                'addicon' => PluginDlteamsSendingReason::canCreate(),
                'name' => 'plugin_dlteams_sendingreasons_id',
                'width' => '250px'
            ]);
            //echo "<br/><br/>";
            echo "</span>";
            echo "<span style='margin-left:100px;margin-top:20px;width:30%'>";
            // echo __("Commentaire ");
            //echo "<br/><br/>";
            echo "<textarea type='text' maxlength=600 rows='1' name='recipient_comment' style='width:30%;margin-bottom:-13px;margin-top:20px' placeholder='Commentaire'></textarea>";
            echo "</span>";

//echo "<br/><br/>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin-top:18px;float:right;margin-right:7%'>";
//

        } else if ($data['consent_type'] == 1) {
            echo "<span style='margin-left:10px;margin-top:20px;width:170px'>";
            // echo __("Personnes Concernees ", 'dlteams');
            //echo "<br/><br/>";
            PluginDlteamsConcernedPerson::dropdown([
                'addicon' => PluginDlteamsConcernedPerson::canCreate(),
                'name' => "plugin_dlteams_concernedpersons_id",
                'width' => '150px'
            ]);
            echo "</span>";
            //
            echo "<span style='margin-left:90px;margin-top:20px;width:280px'>";
            echo __("Motif d'envoi ", 'dlteams');
            //echo "<br/><br/>";
            PluginDlteamsSendingReason::dropdown([
                'addicon' => PluginDlteamsSendingReason::canCreate(),
                'name' => 'plugin_dlteams_sendingreasons_id',
                'width' => '250px'
            ]);
            //echo "<br/><br/>";
            echo "</span>";
            echo "<span style='margin-left:100px;margin-top:20px;width:30%'>";
            // echo __("Commentaire ");
            //echo "<br/><br/>";
            echo "<textarea type='text' maxlength=600 rows='1' name='recipient_comment' style='width:30%;margin-bottom:-13px;margin-top:20px' placeholder='Commentaire'></textarea>";
            echo "</span>";

//echo "<br/><br/>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin-top:18px;float:right;margin-right:7%'>";
//

        } else if ($data['consent_type'] == 2) {
            // Display explicit consentecho "<td><br>" . "</td><td>";
            echo "<span style='margin-left:10px;margin-top:20px;width:170px'>";
            //echo __("Tiers Categories ", 'dlteams');
            //echo "<br/><br/>";
            PluginDlteamsThirdPartyCategory::dropdown([
                'addicon' => PluginDlteamsThirdPartyCategory::canCreate(),
                'name' => "plugin_dlteams_thirdpartycategories_id",
                'width' => '150px',
            ]);
            echo "</span>";
            //
            echo "<span style='margin-left:90px;margin-top:20px;width:280px'>";
            echo __("Motif d'envoi ", 'dlteams');
            //echo "<br/><br/>";
            PluginDlteamsSendingReason::dropdown([
                'addicon' => PluginDlteamsSendingReason::canCreate(),
                'name' => 'plugin_dlteams_sendingreasons_id',
                'width' => '250px',
            ]);
            //echo "<br/><br/>";
            echo "</span>";
            echo "<span style='margin-left:100px;margin-top:20px;width:30%'>";
            // echo __("Commentaire ");
            //echo "<br/><br/>";
            echo "<textarea type='text' maxlength=600 rows='1' name='recipient_comment' style='width:30%;margin-bottom:-13px;margin-top:20px' placeholder='Commentaire'></textarea>";
            echo "</span>";

//echo "<br/><br/>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin-top:18px;float:right;margin-right:7%'>";
//


        }

    }


}
