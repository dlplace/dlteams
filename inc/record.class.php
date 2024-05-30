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

// Selectable elements for broadcast
define('broadcast_elements', [
//    null,
    1 => __("Third party politic", 'dlteams'),
    2 => __("Employees politic", 'dlteams'),
    3 => __("Internal politic", 'dlteams')
]);

class PluginDlteamsRecord extends CommonDropdown implements
    PluginDlteamsExportableInterface
{
    use PluginDlteamsExportable;
    use Glpi\Features\Clonable;

    static $rightname = 'plugin_dlteams_record';
    public $dohistory = true;
    protected $usenotepad = true;
    const broadcast_elements = broadcast_elements;
    /*const STORAGE_MEDIUM_UNDEFINED = 0;
    const STORAGE_MEDIUM_PAPER_ONLY = 1;
    const STORAGE_MEDIUM_MIXED = 4;
    const STORAGE_MEDIUM_ELECTRONIC = 8;
    const PIA_STATUS_UNDEFINED = 0;
    const PIA_STATUS_TODO = 1;
    const PIA_STATUS_QUALIFICATION = 2;
    const PIA_STATUS_APPROVAL = 4;
    const PIA_STATUS_PENDING = 8;
    const PIA_STATUS_CLOSED = 16;*/

    public function getCloneRelations(): array
    {
        return [
            Document_Item::class,
            Notepad::class,
            KnowbaseItem_Item::class,
        ];
    }

    static function getTypeName($nb = 0)
    {
        return _n("Processing Activity", "Processing Activities", $nb, 'dlteams');
    }

//    static function canView(){
//        return true;
//    }

    public function getName($options = [])
    {
        //return sprintf("%s (%s)", $this->fields["name"], $this->fields["number"]); // TODO: Change the autogenerated stub
        return sprintf("%s | %s", $this->fields["number"], $this->fields["name"]); // TODO: Change the autogenerated stub
    }

    function showForm($id, $options = [])
    {
        global $CFG_GLPI;
        $this->initForm($id, $options);
        $this->showFormHeader($options);
//        parent::showForm($id, $options);

        /* echo "<tr class='tab_bg_1'>";
        echo "<td style='text-align:right'>" .PluginDlteamsActivityCategory::getTypeName() . "</td>";
        $randDropdown = mt_rand();
        echo "<td colspan='2'>";
        PluginDlteamsActivityCategory::dropdown([
            'name' => 'plugin_dlteams_activitycategories_id',
            'value' => $this->fields["plugin_dlteams_activitycategories_id"] ?? "", //$responsible,
            'entity' => $this->fields["entities_id"],
            'right' => 'all',
            'width' => "250px",
            'rand' => $randDropdown
        ]);
        echo "</td></tr>"; */

        echo "<style>";
        echo "
            .form-table-text {
                text-align: right;
                width: 50%;
            }
            
            
            @media (max-width: 800px) {
                .form-table-text {
                    text-align: left;
                    width: 100%;
                }
            }
        ";

        echo "</style>";
        echo "<tr class='tab_bg_1'>";
        echo "<td class='form-table-text'>" . __("Number (order)", 'dlteams') . "</td>";
        echo "<td colspan='1'>";
        $number = Html::cleanInputText($this->fields['number']);
        echo "<input type='number' min='1' max='9999' name='number' size='8' required value='" . $number . "'>";
        $number = Html::cleanInputText($this->fields['parentnumber']);
        if($id == 0) $number = 1;
        echo "<input type='number' min='0' max='9999' id='parentnumber' name='parentnumber' size='8' required value='" . $number . "'>";
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='form-table-text'>" . __("Title", 'dlteams') .
            "<br><i>" . __("Record objective, must be unique, understandable", 'dlteams') . "</i></td>";
        echo "<td colspan='2'>";
        $title = Html::cleanInputText($this->fields['name']);

		// on affiche un warning (texte en rouge) en dessous de designation du traitement "En publication, seule la désignation sera affichée"
        echo "<input type='text' style='width:98%' maxlength=250 name='name' required value='" . $title . "'>";
        if($this->fields["parentnumber"] == 0)
            echo "<div id='label_parentnumber' style='color: #b10610;'>En publication, seule la désignation sera affichée</div>";
        echo "</td></tr>";
//
        echo "<tr class='tab_bg_1'>";
        echo "<td class='form-table-text'>" . __("Purpose (GDPR Article 30 1b)", 'dlteams') .
            "<br><i>" . __("Details of objective into sub-purposes (different stages/parts)", 'dlteams') . "</i></td>";

        echo "<td colspan='2' >";
        $purpose = Html::cleanInputText($this->fields['content']);
        echo "<textarea style='width:98%' name='content' required maxlength='1000' rows='5'>" . $purpose . "</textarea>";
        echo "</td></tr>";
//
        echo "<tr class='tab_bg_1'>";
        echo "<td class='form-table-text'>" . __("Broadcast", 'dlteams') .
            "<br><i>" . __("In which document it appears", 'dlteams') . "</i></td>";

        echo "<td colspan='2'>";
        // Options for dropdown select
        $options['value'] = $this->fields['states_id'] ?? 0;
        $options['width'] = "250px";

        Dropdown::showFromArray('states_id', self::broadcast_elements, $options);
        echo "</td></tr>";
//
        // Redacteurs
        if ($responsible = $this->fields["users_id_tech"]) ;
        // if empty, take legal representative of the entity
        else {
            global $DB;
            $iterator = $DB->request([
                'SELECT' => 'users_id_representative',
                'FROM' => 'glpi_plugin_dlteams_controllerinfos',
                'WHERE' => ['entities_id' => $this->getEntityID()]
            ]);

            if (count($iterator)) {
                $responsible = $iterator->next() ?? ['users_id_representative'];
            }
        }

        /*echo "<tr class='tab_bg_1'>";
        echo "<td style='text-align:right'>" . __("Catégorie de traitement", 'dlteams') . "</td>";
        //$randDropdown = mt_rand();
        echo "<td colspan='2'>";
        PluginDlteamsActivityCategory::dropdown([
            'addicon' => PluginDlteamsSendingReason::canCreate(),
            'name' => 'plugin_dlteams_activitycategories_id',
            'value' => $this->fields["plugin_dlteams_activitycategories_id"] ?? "", //$responsible,
            // 'entity' => $this->fields["entities_id"],
            'right' => 'all',
            'width' => "250px",
            // 'rand'   => $randDropdown
        ]);
        echo "</td></tr>";*/
//
        
        echo "<tr class='tab_bg_1'>";
        echo "<td class='form-table-text'>" . __("Process responsible", 'dlteams') . "</td>";
        $randDropdown = mt_rand();
        echo "<td colspan='2'>";
        User::dropdown([
            'name' => 'users_id_tech',
            'value' => $this->fields["users_id_tech"] ?? "", //$responsible,
            'entity' => $this->fields["entities_id"],
            'right' => 'all',
            'width' => "250px",
            'rand' => $randDropdown
        ]);
        echo "</td></tr>";
//
        echo "<tr class='tab_bg_1'>";
        echo "<td class='form-table-text'>" . __("Process auditor", 'dlteams') . "</td>";
        $randDropdown = mt_rand();
        echo "<td colspan='2'>";
        User::dropdown([
            'name' => 'users_id_auditor',
            'value' => $this->fields["users_id_auditor"] ?? "",
            'entity' => $this->fields["entities_id"],
            'right' => 'all',
            'width' => "250px",
            'rand' => $randDropdown
        ]);
        echo "</td></tr>";
//
        echo "<tr class='tab_bg_1'>";
        echo "<td class='form-table-text'>" . __("Process actor", 'dlteams') . "</td>";
        $randDropdown = mt_rand();
        echo "<td colspan='2'>";
        User::dropdown([
            'name' => 'users_id_actor',
            'value' => $this->fields["users_id_actor"] ?? "",
            'entity' => $this->fields["entities_id"],
            'right' => 'all',
            'width' => "250px",
            'rand' => $randDropdown
        ]);
        echo "</td></tr>";
//
        echo "<tr class='tab_bg_2'>";
        echo "<td class='form-table-text'>" . __("First entry date", 'dlteams') . "</td>";
        echo "<td colspan='2'>";
        // Test if date exists
        if ($this->fields['first_entry_date'] != '')
            $first_entry_date = $this->fields['first_entry_date'];
        // Else pre-add today's date
        else $first_entry_date = date('Y-m-d');
        echo "<div style='width: 30%'>";
        Html::showDateField('first_entry_date', ['value' => $first_entry_date, 'style' => "width: 100%"]);
        echo "</div>";
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='form-table-text'><label for='is_grouping'>" . __("Ce traitement est un regroupement", 'dlteams') . "</label></td>";
        echo "<td colspan='2'>";
        Html::showCheckbox(['name' => 'is_grouping', 'title' =>'Ce traitement est un regroupement', 'id' => 'is_grouping', 'checked' => $this->fields['is_grouping']]);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='form-table-text'>" . __("Additional information", 'dlteams') . "</td>";
        echo "<td colspan='2'>";
        $additional_info = Html::cleanInputText($this->fields['additional_info']);
        echo "<textarea style='width: 98%;' name='additional_info' maxlength='1000' rows='3'>" . $additional_info . "</textarea>";
        echo "</td></tr>";

//        var_dump(self::canUpdate());
//        die();

        $this->showFormButtons($options);
		
        /* echo "<div>";
        echo "<p style='margin-top: 10px; text-align: center '>";
        $user = new User();
        $user->getFromDB($this->fields["users_id"]);
        if (isset($user->fields["firstname"]) && $user->fields["realname"])
            echo sprintf("Créé le %s par %s %s | ", date('d-m-Y H:i', strtotime($this->fields["date_creation"])), $user->fields["firstname"], $user->fields["realname"]);

        // $user = new User();
        $user->getFromDB($this->fields["users_id"]);
        if (isset($user->fields["firstname"]) && $user->fields["realname"])
            echo sprintf("Mis à jour le %s par %s %s", date('d-m-Y H:i', strtotime($this->fields["date_mod"])), $user->fields["firstname"], $user->fields["realname"]);
        echo "</p>";
        echo "</div>"; */ 
		
        return true;
    }

    /* static function showPIAStatus($data = [])
    {
        if ($data['pia_required']) {
            echo "&nbsp;&nbsp;&nbsp;" . __("Status") . "&nbsp;&nbsp;";
            self::dropdownPiaStatus('pia_status', $data['pia_status']);
        }
    }

    static function showConsentRequired($data = [])
    {

        if ($data['consent_required']) {
            echo "<td>" . __("Consent storage", 'dlteams') . "</td>";
            echo "<td colspan='2'>";
            $consent_storage = Html::setSimpleTextContent($data['consent_storage']);
            echo "<textarea style='width: 98%;' name='consent_storage' maxlength='1000' rows='3'>" . $consent_storage . "</textarea>";
            echo "</td>";
        }
    } */ 

    // @see CommonDBTM::showMassiveActionsSubForm()
    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        switch ($ma->getAction()) {
            case 'copyTo':
                Entity::dropdown([
                    'name' => 'entities_id',
                ]);
                echo '<br /><br />' . Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                return true;
        }
        return parent::showMassiveActionsSubForm($ma);
    }

    public static function importToDB(PluginDlteamsLinker $linker, $input = [], $containerId = 0, $subItems = [])
    {
        global $DB;
        // New record
        $input['_skip_checks'] = true;
        $item = new self();
        // Set Record Fields
        $originalId = $input['id'];
        unset($input['id']);

        // Escape text fields
        foreach ([
                     'name', 'content', 'additional_info', 'consent_explicit',
                     'diffusion', 'conservation_time', 'archive_time', 'right_information',
                     'right_correction', 'right_opposition', 'right_portability', 'profiling_auto', 'specific_security_measures'
                 ] as $key) {
            $input[$key] = $DB->escape($input[$key]);
        }
        $itemId = $item->add($input, [], false);
        if ($itemId === false) {
            $typeName = strtolower(self::getTypeName());
            throw new ImportFailureException(sprintf(__('failed to copy the %1$s record', 'dlteams'), $input['name']));
        }
        // add the record to the linker
        $linker->addObject($originalId, $item);
        return $itemId;
    }

    function getSpecificMassiveActions($checkitem = NULL)
    {
        $actions = parent::getSpecificMassiveActions($checkitem);
        // add a single massive action
        $class = __CLASS__;
//        $action_key = "update_dlteams_action";
//        $action_label = __("Update dlteams relations", "dlteams");
//        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;

        $action_key = "delete_with_related_dlteams_action";
        $action_label = __("Supprimer définitivement avec les éléments liés", "dlteams");
        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;

        $action_key = "delete_without_related_dlteams_action";
        $action_label = __("Supprimer mais conserver les éléments liés", "dlteams");
        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;
        return $actions;
    }

    function exportToDB($subItems = [])
    {
        if ($this->isNewItem()) {
            return false;
        }
        $export = $this->fields;

        // Remove unused key
        unset(
            $export['users_id_creator'],
            $export['users_id_lastupdater'],
            $export['users_id_tech'],
            $export['users_id_auditor'],
            $export['users_id_actor'],
            $export['first_entry_date'],
            $export['date_creation'],
            $export['date_mod']
        );
        return $export;
    }

    // Execute massive action for dlteams Plugin
    // @see CommonDBTM::processMassiveActionsForOneItemtype()
    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        switch ($ma->getAction()) {
            case 'copyTo':
                if ($item->getType() == 'PluginDlteamsRecord') {
                    // @var PluginDlteamsRecord $item
                    foreach ($ids as $id) {
                        if ($item->getFromDB($id)) {
                            if ($item->copy($ma->POST['entities_id'], $id, $item)) {
                                //Session::addMessageAfterRedirect(sprintf(__('Record copied: %s', 'dlteams'), $item->getName()));
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                            }
                        } else {
                            // Example of ko count
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        }
                    }
                }
                return;
                break;
            case 'delete_with_related_dlteams_action':
                global $DB;
                $types = PluginDlteamsItemType::getItemsTypes();
                foreach ($ids as $id) {
                    foreach ($types as $type) {
                        if (class_exists($type)) {
                            $type = new $type();
                            $DB->delete(
                                $type->getTable(),
                                [
                                    'itemtype' => 'PluginDlteamsRecord',
                                    'items_id' => $id
                                ]
                            );
                        }
                    }

                    $record_item = new PluginDlteamsRecord_Item();
                    $DB->delete(
                        $record_item->getTable(),
                        [
                            "records_id" => $id
                        ]
                    );

                    $item->delete(['id' => $id]);
                }
                Session::addMessageAfterRedirect("Opération éffectué avec succés");
                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                return;
                break;


            case 'delete_without_related_dlteams_action':
//                global $DB;
//                $types = PluginDlteamsItemType::getItemsTypes();
                foreach ($ids as $id) {
                    $item->delete(['id' => $id]);
//                    foreach ($types as $type) {
//                    }
                }

                Session::addMessageAfterRedirect("Opération éffectué avec succés");
                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                return;
                break;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

//    public static function getTimelinePosition(){
//        return 0;
//    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this
            ->addDefaultFormTab($ong)
            // Section 1 : Description du traitement
            ->addStandardTab(__CLASS__, $ong, $options)
//            // Section 2 : Personnes concernées par ce traitement et catégories de données
            ->addStandardTab('PluginDlteamsRecord_PersonalAndDataCategory', $ong, $options)
//            // Section 3 : Bases légales & rétention
            ->addStandardTab(PluginDlteamsConservation_Element::class, $ong, $options)
//            // Section 4 : Consentement et Exercice des droits
            ->addStandardTab('PluginDlteamsRecord_Rights', $ong, $options)
//            // Section 5 : Acteurs, soustaitants, destinataires
            ->addStandardTab('PluginDlteamsRecord_External', $ong, $options)
            // Section 6  : Security and confidentiality
            ->addStandardTab('PluginDlteamsRecord_SecurityMeasure', $ong, $options)
            ->addStandardTab('PluginDlteamsRecord_Element', $ong, $options)
//            ->addStandardTab('PluginDlteamsObject_document', $ong, $options)
            ->addStandardTab('PluginDlteamsCreatePDF', $ong, $options)
            ->addStandardTab('PluginDlteamsRecord_Item', $ong, $options)
            ->addStandardTab('ManualLink', $ong, $options)
            ->addStandardTab(PluginDlteamsMessagerie::class, $ong, $options)
            ->addStandardTab('Notepad', $ong, $options)
            ->addStandardTab(PluginDlteamsTicket_Item::class, $ong, $options)
            ->addStandardTab('Log', $ong, $options);
        return $ong;
    }

//    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
//    {
//
//        if (!$withtemplate) {
//            $nb = 0;
//            switch ($item->getType()) {
//                case __CLASS__:
//                    $ong[1] = $this->getTypeName(1);
//                    if ($item->canUpdateItem()) {
//                        if ($_SESSION['glpishow_count_on_tabs']) {
////                            $nb = $item->countVisibilities();
//                            $nb = 0;
//                        }
//                        $ong[2] = self::createTabEntry(
//                            _n('Target', 'Targets', Session::getPluralNumber()),
//                            $nb
//                        );
//                        $ong[3] = __('Edit');
//                    }
//                    return $ong;
//            }
//        }
//        return '';
//    }

    function cleanDBonPurge()
    {
        $this->deleteChildrenAndRelationsFromDb(
            [
                PluginDlteamsRecord_PersonalAndDataCategory::class,
                PluginDlteamsRecord_Storage::class,
                PluginDlteamsRecord_PersonalDataCategory::class,
            ]
        );

        /*      $retention = new PluginDlteamsRecord_Retention();
              $retention->deleteByCriteria(['plugin_dlteams_records_id' => $this->fields['id']]);*/
    }

    static function getAllPiaStatusArray($withmetaforsearch = false)
    {
        $tab = [
            self::PIA_STATUS_UNDEFINED => __("Undefined", 'dlteams'),
            self::PIA_STATUS_TODO => __("To do"),
            self::PIA_STATUS_QUALIFICATION => __("Qualification"),
            self::PIA_STATUS_APPROVAL => __("Approval"),
            self::PIA_STATUS_PENDING => __("Pending"),
            self::PIA_STATUS_CLOSED => __("Closed")
        ];

        if ($withmetaforsearch) {
            $tab['all'] = __("All");
        }

        return $tab;
    }

    static function getAllStorageMediumArray($withmetaforsearch = false)
    {
        $tab = [
            self::STORAGE_MEDIUM_UNDEFINED => __("Undefined", 'dlteams'),
            self::STORAGE_MEDIUM_PAPER_ONLY => __("Paper only", 'dlteams'),
            self::STORAGE_MEDIUM_MIXED => __("Paper and electronic", 'dlteams'),
            self::STORAGE_MEDIUM_ELECTRONIC => __("Electronic only", 'dlteams'),
        ];

        if ($withmetaforsearch) {
            $tab['all'] = __("All");
        }
        return $tab;
    }

    static function dropdownStorageMedium($name, $value = 0, $display = true)
    {
        return Dropdown::showFromArray($name, self::getAllStorageMediumArray(), [
            'value' => $value, 'display' => $display
        ]);
    }

    static function dropdownPiaStatus($name, $value = 0, $display = true)
    {
        return Dropdown::showFromArray($name, self::getAllPiastatusArray(), [
            'value' => $value, 'display' => $display
        ]);
    }

    static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }

        switch ($field) {
            case 'states_id':
                if (isset(self::broadcast_elements[$values[$field]])) {
                    return self::broadcast_elements[$values[$field]];
                } else {
                    return '&nbsp;';
                }
            case 'pia_status':
                if (!$values[$field]) {
                    return '&nbsp;';
                }
                $pia_status = self::getAllPiastatusArray();

                return $pia_status[$values[$field]];
            case 'storage_medium':
                $storage_medium = self::getAllStorageMediumArray();

                return $storage_medium[$values[$field]];
        }
    }

    static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;

        switch ($field) {
            case 'pia_status':
                return self::dropdownPiaStatus($name, $values[$field], false);
            case 'storage_medium':
                return self::dropdownStorageMedium($name, $values[$field], false);
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }

    function prepareInputForAdd($input)
    {
        $input['users_id_creator'] = Session::getLoginUserID();
        if (array_key_exists('pia_required', $input) && $input['pia_required'] == 0) {
            $input['pia_status'] = PluginDlteamsRecord::PIA_STATUS_UNDEFINED;
        }

        if (array_key_exists('consent_required', $input) && $input['consent_required'] == 0) {
            $input['consent_storage'] = null;
        }
        return parent::prepareInputForAdd($input);
    }

    function prepareInputForUpdate($input)
    {
        $input['users_id_lastupdater'] = Session::getLoginUserID();
        if (array_key_exists('pia_required', $input) && $input['pia_required'] == 0) {
            $input['pia_status'] = PluginDlteamsRecord::PIA_STATUS_UNDEFINED;
        }
        if (array_key_exists('consent_required', $input) && $input['consent_required'] == 0) {
            $input['consent_storage'] = null;
        }
        return parent::prepareInputForUpdate($input);
    }

    function post_updateItem($history = 1)
    {
        if ((isset($this->fields['storage_medium']) && $this->fields['storage_medium'] == self::STORAGE_MEDIUM_PAPER_ONLY)
            && (PluginDlteamsConfig::getConfig('system', 'remove_software_when_paper_only'))
        ) {
            $del = new PluginDlteamsRecord_Software();
            $del->deleteByCriteria(['plugin_dlteams_records_id' => $this->fields['id']]);
        }


        global $DB;
        $record_item_query = [
            "FROM" => PluginDlteamsRecord_Item::getTable(),
            "WHERE" => [
                "records_id" => $this->fields["id"]
            ]
        ];


        $iterator = $DB->request($record_item_query);

//       foreach related item update the name if the original name changed
        foreach ($iterator as $data) {
            if ($this->fields["name"] != $data["name"]) {
                $record_item = new PluginDlteamsRecord_Item();

                $DB->update($record_item->getTable(), [
                    "name" => addslashes($this->fields["name"]),
                ], ["id" => $data["id"]]);
            }
        }
    }

    function rawSearchOptions()
    {
        $tab = [];
        $tab[] = [
            //'id' => 'common',
            'id' => '100',
            'table' => $this->getTable(),
            'field' => 'id',
            'name' => __("Id", 'dlteams'),
            'massiveaction' => false,
            'datatype' => 'number'
        ];

        /*$tab[] = [
            'id' => '1',
            'table' => $this->getTable(),
            'field' => 'number',
            'name' => __("Number (order)", 'dlteams'),
            'massiveaction' => false,
            'datatype' => 'number'
        ];*/
        $tab[] = [
            'id' => '1',
            'table' => $this->getTable(),
            'field' => 'completenumber',
            'name' => __("Number (order)", 'dlteams'),
            'massiveaction' => false,
            'datatype' => 'decimal'
        ];

        $tab[] = [
            'id' => '2',
            'table' => $this->getTable(),
            'field' => 'name',
            'name' => __("Name"),
            'datatype' => 'itemlink',
            'searchtype' => 'contains',
            'massiveaction' => true
        ];

        $tab[] = [
            'id' => '3',
            'table' => $this->getTable(),
            'field' => 'content',
            'name' => __("Purpose", 'dlteams'),
            'massiveaction' => true,
            'htmltext' => true
        ];

        $tab[] = [
            'id' => '12',
            'table' => $this->getTable(),
            'field' => 'additional_info',
            'name' => __("Additional information", 'dlteams'),
            'massiveaction' => true,
            'htmltext' => true
        ];

        $tab[] = [
            'id' => '4',
            'table' => Link::getTable(),
            'field' => 'name',
            'linkfield' => 'links_id',
            'name' => __("Publication folder", 'dlteams'),
            'massiveaction' => true,
            'datatype' => 'dropdown'
        ];

        $tab[] = [
            'id' => '5',
            'table' => $this->getTable(),
            'field' => 'states_id',
            'name' => __("Broadcast", 'dlteams'),
            'massiveaction' => true,
            'datatype' => 'specific',
        ];

        $tab[] = [
            'id' => '101',
            'table' => 'glpi_users',
            'field' => 'name',
            'linkfield' => 'users_id_tech',
            'name' => __("Responsable du traitement"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'toview' => true,
            'datatype' => 'dropdown',
            'searchtype' => ['equals', 'notequals'],
            'entity' => $this->getEntityID(),
            'right' => 'all',
        ];


        $tab[] = [
            'id' => '102',
            'table' => 'glpi_users',
            'field' => 'users_id_auditor',
            'name' => __("Auditeur"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'dropdown',
            'searchtype' => ['equals', 'notequals'],
            'entity' => $this->getEntityID(),
            'right' => 'all',
        ];

        $tab[] = [
            'id' => '103',
            'table' => 'glpi_users',
            'field' => 'users_id_actor',
            'name' => __("Rédacteur de la fiche"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'dropdown',
            'searchtype' => ['equals', 'notequals'],
            'entity' => $this->getEntityID(),
            'right' => 'all',
        ];

        $tab[] = [
            'id' => '6',
            'table' => 'glpi_entities',
            'field' => 'completename',
            'name' => __("Entity"),
            'massiveaction' => true,
            'datatype' => 'dropdown',
        ];
        $tab[] = [
            'id' => '7',
            'table' => $this->getTable(),
            'field' => 'is_recursive',
            'name' => __("Child entities"),
            'massiveaction' => false,
            'datatype' => 'bool'
        ];

        $tab[] = [
            'id' => '8',
            'table' => PluginDlteamsActivityCategory::getTable(),
            'field' => 'name',
            'name' => __("Activité", 'dlteams'),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'dropdown',
            'searchtype' => ['equals', 'notequals'],
        ];

//TODO: changer field ou table
//        $tab[] = [
//            'id' => '9',
//            'table' => PluginDlteamsActivityCategory::getTable(),
//            'field' => 'number',
//            'name' => __("Numéro Activité", 'dlteams'),
//            'forcegroupby' => true,
//            'massiveaction' => true,
//            'datatype' => 'dropdown',
//            'searchtype' => ['equals', 'notequals'],
//        ];




        /*
        $tab[] = [
            'id' => '12',
            'table' => PluginDlteamsActivitycategory::getTable(),
            'field' => 'name',
            'name' => "Ct",
            'forcegroupby' => true,
            'usehaving' => true,
            'datatype'  => 'itemlink',
            'massiveaction' => false,
            'joinparams' => [
                'jointype' => 'itemtype_item',
                'table' => PluginDlteamsActivitycategory_Item::getTable(),
            ]
        ];
        */


        $tab[] = [
            'id' => '10',
            'table' => $this->getTable(),
            'field' => 'date_creation',
            'name' => __("Creation date"),
            'massiveaction' => false,
            'datatype' => 'text',
        ];

        $tab[] = [
            'id' => '11',
            'table' => $this->getTable(),
            'field' => 'date_mod',
            'name' => __("Last update"),
            'massiveaction' => false,
            'datatype' => 'text',
        ];

//        $tab[] = [
//            'id'                 => '400',
//            'table'              => PluginDlteamsActivityCategory::getTable(),
//            'field'              => 'name',
//            'name'               => PluginDlteamsActivityCategory::getTypeName(1),
//            'massiveaction'      => false,
//            'searchtype'         => ['equals', 'notequals'],
//            'datatype'           => 'dropdown',
//            'linkfield'         => 'activitycategories_id',
//            'joinparams'         => [
//                'jointype'           => 'items_id',
//                'beforejoin'         => [
//                    'table'              => PluginDlteamsActivitycategory_Item::getTable(),
//                    'joinparams'         => [
//                        'jointype'           => 'itemtype_item',
//                    ]
//                ]
//            ]
//        ];

        // $tab = array_merge(
        //    $tab,
        //    PluginDlteamsRecord_Contract::rawSearchOptions()
        // );

        // $tab = array_merge(
        //    $tab,
        //    PluginDlteamsRecord_LegalBasisAct::rawSearchOptions()
        // );

        // $tab = array_merge(
        //    $tab,
        //    PluginDlteamsRecord_PersonalAndDataCategory::rawSearchOptions()
        // );

        // $tab = array_merge(
        //    $tab,
        //    PluginDlteamsRecord_SecurityMeasure::rawSearchOptions()
        // );

        // $tab = array_merge(
        //    $tab,
        //    PluginDlteamsRecord_Software::rawSearchOptions()
        // );

        // $tab = array_merge(
        //    $tab,
        //    PluginDlteamsRecord_Element::rawSearchOptions()
        // );

        // $tab = array_merge(
        //    $tab,
        //    PluginDlteamsRecord_BaseDonnee::rawSearchOptions()
        // );

        return $tab;
    }

    public function deleteObsoleteItems(CommonDBTM $container, array $exclude)
    {
    }
}
