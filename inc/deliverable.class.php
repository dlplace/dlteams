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

use Glpi\Application\View\TemplateRenderer;
use Glpi\RichText\RichText;

class PluginDlteamsDeliverable extends CommonDropdown
    implements PluginDlteamsExportableInterface
{
    static $rightname = 'plugin_dlteams_deliverable';
    public $dohistory = true;
    protected $usenotepad = true;

    static function getTypeName($nb = 0)
    {
        return _n("Fichier HTML", "Fichiers HTML", $nb, 'dlteams');
    }

    public function showTabsContent($options = [])
    {
        parent::showTabsContent($options); // TODO: Change the autogenerated stub
    }

    static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }

        $options['display'] = false;
        switch ($field) {
            case 'items_id':
                if (isset($values['itemtype']) && !empty($values['itemtype'])) {
                    $options['name'] = $name;
                    $options['value'] = $values[$field];
                    return Dropdown::show($values['itemtype'], $options);
                }
                break;
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }

    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {

        if(isset($options["searchopt"]["editbutton"]) && $options["searchopt"]["editbutton"]){
            $id = $options["raw_data"]["raw"]["id"];

            $output = "";

            $url = Toolbox::getItemTypeFormURL("PluginDlteamsDeliverable")
                ."?deliverable_id=".$id."&report_type="
                .PluginDlteamsCreatePDF::REPORT_SINGLE_RECORD
                ."&print_comments=true"
                ."&prevent_contextmenu=true"
                ."&print_first_page=true"
                ."&edit_pdf=true";

            $output.= "<a href='$url' target='_blank'>".__("Edit / Print PDF", 'dlteams')."</a>";

            $output.= Html::closeForm(false);
        return $output;
        }
        parent::getSpecificValueToDisplay($field, $values, $options);
    }

    function showForm($id, $options = [])
    {
        global $CFG_GLPI;

        $this->initForm($id, $options);
        $this->showFormHeader($options);

        echo "<table, th, td width='60%'>";
        echo "<tr>";
        echo "<input type='hidden' name='deliverable_id' value='" . $id . "'>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td width='15%' style='text-align:right' >" . __("Nom", 'dlteams') . "</td>";
        echo "<td>";
        $name = Html::cleanInputText($this->fields['name']);
        echo "<input type='text' style='width:98%' name='name' required value='" . $name . "'>" . "</td>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td width='15%' div style='text-align:right'>" . __("Rubrique", 'dlteams') . "</div></td>";
        echo "<td>";

//        Document::dropdown([
//            'name' => 'documents_id',
//            'entity' 	=> Session::getActiveEntity(),
//            //'entity_sons' => $entities_id->isRecursive(),
////            'width' => '250px',
//            'value' => $this->fields['documents_id']
//        ]);
        Dropdown::show(Document::class,[
            'addicon'  => Document::canCreate(),
            'name' => 'documents_id',
            'value' => $this->fields['documents_id']
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr>.";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td width='15%' style='text-align:right'>" . __("Content", 'dlteams') . "</td>";
        echo "<td>";
        $content = Html::cleanInputText($this->fields['content']);
        echo "<textarea style='width: 98%;' name='content' rows='4'>" . $content . "</textarea>";
        echo "</td></tr>";

        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td width='15%' style='text-align:right'>" . __("Comments") . "</td>";
        echo "<td>";
        $comment = Html::cleanInputText($this->fields['comment']);
        echo "<textarea style='width: 98%;' name='comment' rows='4'>" . $comment . "</textarea>";
        echo "</td></tr>";
        echo "</table>";

        $this->showFormButtons($options);
        Html::requireJs('tinymce');

        //$options['colspan'] = 2;
        //$this->showDates($options);
    }

    public static function showForProjectTask(ProjectTask $projecttask)
    {
        $ID = $projecttask->getField('id');
        if (!$projecttask->can($ID, READ)) {
            return false;
        }

        $canedit = $projecttask->canEdit($ID);
        $rand = mt_rand();

        $iterator = self::getListForItem($projecttask);
        $numrows = count($iterator);

        $tickets = [];
        $used = [];
        foreach ($iterator as $data) {
            $tickets[$data['id']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($canedit) {
            $condition = [
                'NOT' => [
                    'glpi_tickets.status' => array_merge(
                        Ticket::getSolvedStatusArray(),
                        Ticket::getClosedStatusArray()
                    )
                ]
            ];
            echo TemplateRenderer::getInstance()->render('components/form/link_existing_or_new.html.twig', [
                'rand' => $rand,
                'link_itemtype' => __CLASS__,
                'source_itemtype' => ProjectTask::class,
                'source_items_id' => $ID,
                'target_itemtype' => Ticket::class,
                'dropdown_options' => [
                    'entity' => $projecttask->getEntityID(),
                    'entity_sons' => $projecttask->isRecursive(),
                    'used' => $used,
                    'displaywith' => ['id'],
                    'condition' => $condition
                ],
                'create_link' => Session::haveRight(Ticket::$rightname, CREATE)
            ]);
        }

        echo "<div class='spaced'>";
        if ($canedit && $numrows) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = ['num_displayed' => min($_SESSION['glpilist_limit'], $numrows),
                'container' => 'mass' . __CLASS__ . $rand
            ];
            Html::showMassiveActions($massiveactionparams);
        }

        echo "<table class='tab_cadre_fixehov'>";
        echo "<tr><th colspan='12'>" . Ticket::getTypeName($numrows) . "</th>";
        echo "</tr>";
        if ($numrows) {
            Ticket::commonListHeader(Search::HTML_OUTPUT, 'mass' . __CLASS__ . $rand);
            Session::initNavigateListItems(
                'Ticket',
                //TRANS : %1$s is the itemtype name,
                //        %2$s is the name of the item (used for headings of a list)
                sprintf(
                    __('%1$s = %2$s'),
                    ProjectTask::getTypeName(1),
                    $projecttask->fields["name"]
                )
            );

            $i = 0;
            foreach ($tickets as $data) {
                Session::addToNavigateListItems('Ticket', $data["id"]);
                Ticket::showShort(
                    $data['id'],
                    [
                        'row_num' => $i,
                        'type_for_massiveaction' => __CLASS__,
                        'id_for_massiveaction' => $data['linkid']
                    ]
                );
                $i++;
            }
        }
        echo "</table>";
        if ($canedit && $numrows) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
        }
        echo "</div>";
    }

    function showFormqqq($id, $options = [])
    {

        global $CFG_GLPI;

        $this->initForm($id, $options);
        $this->showFormHeader($options);


        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Name') . "</td>";
        echo "</tr><td>";

        echo "<tr>";
        echo "<td>";

        echo "</td>";
        echo "</tr>";

        echo "<td rowspan='1'>" . __('Comments') . "</td>";
        $cols = 100;
        $rows = 60;
        echo "<td rowspan='1'>
               <textarea cols='45' rows='2' name='comment' id='field_name' >" . $this->fields["comment"];
        echo "</textarea></td>";


        echo "</tr>\n";
        echo "<tr>";
        echo "<td colspan='2'>";
        echo __('Content');
        echo "<br/>";
        echo "<br/>";
        Html::textarea(['name' => 'content',
            'value' => "", //\Glpi\RichText\RichText::getSafeHtml($this->fields['content'], true),
            'enable_fileupload' => false,
            'enable_richtext' => true,
            'cols' => $cols,
            'rows' => $rows
        ]);
        echo "</td>";
        echo "</tr>";

        return true;
    }

    function showDates($options = [])
    {

        $isNewID = ((isset($options['withtemplate']) && ($options['withtemplate'] == 2))
            || $this->isNewID($this->getID()));

        if ($isNewID) {
            return true;
        }

        $date_creation_exists = ($this->getField('date_creation') != NOT_AVAILABLE);
        $date_mod_exists = ($this->getField('date_mod') != NOT_AVAILABLE);

        $colspan = $options['colspan'];
        if ((!isset($options['withtemplate']) || ($options['withtemplate'] == 0))
            && !empty($this->fields['template_name'])) {
            $colspan = 1;
        }

        echo "<tr class='tab_bg_1 footerRow'>";
        //Display when it's not a new asset being created
        if ($date_creation_exists
            && $this->getID() > 0
            && (!isset($options['withtemplate']) || $options['withtemplate'] == 0 || $options['withtemplate'] == NULL)) {
            echo "<th colspan='$colspan'>";
            printf(__('Created on %s'), Html::convDateTime($this->fields["date_creation"]), " ;");
            echo "</th>";
        } else if (!isset($options['withtemplate']) || $options['withtemplate'] == 0 || !$date_creation_exists) {
            echo "<th colspan='$colspan'>";
            echo "</th>";
        }

        if (isset($options['withtemplate']) && $options['withtemplate']) {
            echo "<th colspan='$colspan'>";
            //TRANS: %s is the datetime of insertion
            printf(__('Created on %s'), Html::convDateTime($_SESSION["glpi_currenttime"]));
            echo "</th>";
        }
        echo "  -  ";
        if ($date_mod_exists) {
            echo "<th colspan='$colspan'>";
            //TRANS: %s is the datetime of update
            printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
            echo "</th>";
        } else {
            echo "<th colspan='$colspan'>";
            echo "</th>";
        }

        if ((!isset($options['withtemplate']) || ($options['withtemplate'] == 0))
            && !empty($this->fields['template_name'])) {
            echo "<th colspan='" . ($colspan * 2) . "'>";
            printf(__('Created from the template %s'), $this->fields['template_name']);
            echo "</th>";
        }

        echo "</tr>";
    }

    public static function getDefaultSearchRequest()
    {
        $search = [
            'criteria' => [
                0 => [
                    'field' => 0,
                    'searchtype' => 'contains',
                    'value' => ''
                ],
            ],
            'sort' => 9,
            'order' => 'DESC'
        ];
		return $search;
	}

    function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __("Characteristics")
        ];

        $tab[] = [
            'id' => '1',
            'table' => $this->getTable(),
            'field' => 'name',
            'name' => __("Name"),
            'datatype' => 'itemlink',
            'massiveaction' => false,
            'autocomplete' => true,
        ];

        $tab[] = [
            'id' => '2',
            'table' => $this->getTable(),
            'field' => 'id',
            'name' => __("ID"),
            'massiveaction' => false,
            'datatype' => 'number',
        ];

        $tab[] = [
            'id' => '3',
			//'table' => 'glpi_documentcategories',
            'table' => 'glpi_projecttasks',
            'field' => 'name',
            'name' => __("Rubrique"),
            'datatype' => 'dropdown',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '4',
            'table' => $this->getTable(),
            'field' => 'content',
            'name' => __("Content"),
            'datatype' => 'text',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '5',
            'table' => $this->getTable(),
            'field' => 'comment',
            'name' => __("Comments"),
            'datatype' => 'text',
            'toview' => true,
            'massiveaction' => true,
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
            'datatype' => 'bool',
        ];

        $tab[] = [
            'id' => '8',
            'table' => $this->getTable(),
            'field' => 'date_creation',
            'name' => __("Creation date"),
            'massiveaction' => false,
            'datatype' => 'text',
        ];

        $tab[] = [
            'id' => '9',
            'table' => $this->getTable(),
            'field' => 'date_mod',
            'name' => __("Last update"),
            'massiveaction' => false,
            'datatype' => 'text',
        ];

        $tab[] = [
            'id' => '10',
            'table' => $this->getTable(),
            'field' => 'id',
            'name' => __("Editer"),
            'massiveaction' => false,
            'datatype' => 'specific',
            'editbutton' => true
        ];

//        $tab[] = [
//            'id' => '10',
//            'table' => Deliverable_Item::getTable(),
//            'field' => 'date_mod',
//            'name' => __("Last update"),
//            'massiveaction' => false,
//            'datatype' => 'text',
//        ];

        return $tab;
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $ong = array();


        $this->addDefaultFormTab($ong)
            ->addStandardTab(PluginDlteamsDeliverable_Section::class, $ong, $options)
//            ->addStandardTab('PluginDlteamsProjectTask_Deliverable', $ong, $options)
            ->addStandardTab('PluginDlteamsCreatePDF', $ong, $options)
            ->addStandardTab(PluginDlteamsDeliverableNotification::class, $ong, $options)
            ->addStandardTab('PluginDlteamsObject_document', $ong, $options)
            ->addStandardTab('ManualLink', $ong, $options)
            ->addStandardTab('PluginDlteamsObject_allitem', $ong, $options)
            ->addStandardTab('Ticket', $ong, $options)
            ->addStandardTab('KnowbaseItem_Item', $ong, $options)
            ->addImpactTab($ong, $options)
            ->addStandardTab('Notepad', $ong, $options)
            ->addStandardTab('Log', $ong, $options);
        return $ong;
    }

//    public function getForbiddenStandardMassiveAction()
//    {
//        $forbidden = parent::getForbiddenStandardMassiveAction();
//        $forbidden[] = 'Infocom:activate';
//        $forbidden[] = 'MassiveAction:add_transfer_list';
//        $forbidden[] = 'Document_Item:add';
//        $forbidden[] = 'Contract_Item:add';
//        $forbidden[] = 'MassiveAction:amend_comment';
//        $forbidden[] = 'MassiveAction:add_note';
//        $forbidden[] = 'PluginDlteamsDeliverable:copyTo';
//        return $forbidden;
//    }

    function exportToDB($subItems = [])
    {
        if ($this->isNewItem()) {
            return false;
        }

        $export = $this->fields;
        return $export;
    }

    public static function importToDB(PluginDlteamsLinker $linker, $input = [], $containerId = 0, $subItems = [])
    {
        $item = new self();
        $originalId = $input['id'];
        unset($input['id']);
        $input['entities_id'] = $_POST['entities_id'];;
        $input['comment'] = str_replace(['\'', '"'], "", $input['comment']);
        $input['name'] = str_replace(['\'', '"'], "", $input['name']);
        $input['content'] = str_replace(['\'', '"'], "", $input['content']);
        $itemId = $item->add($input);
        if ($itemId === false) {
            $typeName = strtolower(self::getTypeName());
            throw new ImportFailureException(sprintf(__('failed to copy the %1$s record', 'dlteams'), $input['name']));
        }
        return $itemId;
    }

    public function deleteObsoleteItems(CommonDBTM $container, array $exclude)
    {
    }

    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
//        var_dump("jjsl");
//        die();
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

    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        switch ($ma->getAction()) {
            case 'copyTo':
                if ($item->getType() == 'PluginDlteamsDeliverable') {
                    foreach ($ids as $id) {
                        if ($item->getFromDB($id)) {
                            if ($item->copy1($ma->POST['entities_id'], $id, $item)) {
                                Session::addMessageAfterRedirect(sprintf(__('Deliverable copied: %s', 'dlteams'), $item->getName()));
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                            }
                        } else {
                            // Example of ko count
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        }
                    }
                }
                return;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }


    /**
     * @param $entity entité de destination vers laquelle sera éffectué la copie
     * @param $id id à copier
     * @param $item item à copier
     * @return bool
     */
    public function copy1($entity, $id, $item)
    {
        global $DB;
        $DB->beginTransaction();
        $dbu = new DbUtils();
        $name = addslashes($item->fields['name']);
        $entities_ori = $item->fields['entities_id'];
        $id_ori = $item->fields['id'];
        $nb = $dbu->countElementsInTable(static::getTable(), ['name' => $name, 'entities_id' => $entity, 'is_deleted' => 0]);

// code original
        try {
            if ($nb <= 0) {
                $deliverable_origin = new PluginDlteamsDeliverable();
                $new_deliverable = new PluginDlteamsDeliverable();
                $deliverable_origin->getFromDB($id_ori);


                // STEP 1 : on enleve les champs a ne pas copier
                unset($deliverable_origin->fields["date_creation"]);
                unset($deliverable_origin->fields["date_mod"]);
                unset($deliverable_origin->fields["links_id"]);
                unset($deliverable_origin->fields["users_id"]);
                unset($deliverable_origin->fields["id"]);// STEP 2 : creation des new deliverable
                ;
                $newid = $new_deliverable->add([
                    ...$deliverable_origin->fields,
                    "name" => addslashes($deliverable_origin->fields["name"]),
                    "content" => addslashes($deliverable_origin->fields["content"]),
                    "entities_id" => $entity
                ]);

                // creation new sections avec deliverable_id
                $query = [
                    "FROM" => PluginDlteamsDeliverable_Section::getTable(),
                    "WHERE" => [
                        "deliverables_id" => $id_ori
                    ]
                ];

                $desction_iterator = $DB->request($query);
                foreach ($desction_iterator as $dsection_ori) {
                    $dsection = new PluginDlteamsDeliverable_Section();

                    unset($dsection_ori["date_creation"]);
                    unset($dsection_ori["date_mod"]);
                    unset($dsection_ori["id"]);
                    $newdsection_id = $dsection->add([
                        ...$dsection_ori,
                        "deliverables_id" => $newid
                    ]);

//                creation des content
                    $query_content = [
                        "FROM" => PluginDlteamsDeliverable_Content::getTable(),
                        "WHERE" => [
                            "deliverable_sections_id" => $dsection_ori["deliverable_sections_id"]
                        ]
                    ];

                    $decontent_iterator = $DB->request($query_content);
                    foreach ($decontent_iterator as $decontent_ori) {
                        $decontent = new PluginDlteamsDeliverable_Content();

                        unset($decontent_ori["date_creation"]);
                        unset($decontent_ori["date_mod"]);
                        unset($decontent_ori["id"]);
                        $decontent->add([
                            ...$decontent_ori,
                            "deliverable_sections_id" => $newdsection_id
                        ]);
                    }

                }

                /**copy the document**/
                $document_query = [
                    "FROM" => Document_Item::getTable(),
                    "WHERE" => [
                        "items_id" => $id_ori,
                        "itemtype" => PluginDlteamsDeliverable::class,
                        "entities_id" => $entities_ori
                    ]
                ];
                $documentitem_iterator = $DB->request($document_query);
                foreach ($documentitem_iterator as $id => $documentitem_ori) {

//                        copy the origin document
                    $document = new Document();
                    $document_ori = new Document();
                    $document_ori->getFromDB($documentitem_ori['documents_id']);
                    unset($document_ori->fields["date_creation"]);
                    unset($document_ori->fields["id"]);
//                        add document if not exist
                    $crit_doc = [
//                        ...$document_ori->fields,
                        "name" => addslashes($document_ori->fields["name"]),
                        "entities_id" => $entity
                    ];

                    if (!$document->getFromDBByCrit($crit_doc)) {
                        $newiddoc = $document->add($crit_doc);
                    } else {
                        $document->getFromDBByCrit($crit_doc);
                        $newiddoc = $document->fields["id"];
                    }

                    $document = new PluginDlteamsDeliverable();
                    $documentdeli_ori = new PluginDlteamsDeliverable();
                    $documentdeli_ori->getFromDB($documentitem_ori['items_id']);
                    unset($documentdeli_ori->fields["date_creation"]);
                    unset($documentdeli_ori->fields["id"]);
                    $crit_doc_deliverable = [
//                        ...$document_ori->fields,
                        "name" => addslashes($documentdeli_ori->fields["name"]),
                        "is_deleted" => 0,
                        "entities_id" => $entity
                    ];

                    $doc_deliverable = new PluginDlteamsDeliverable();
                    if (!$doc_deliverable->getFromDBByCrit($crit_doc_deliverable)) {
                        $newiddeliverable_doc = $doc_deliverable->add($crit_doc_deliverable);
                    } else {
                        $doc_deliverable->getFromDBByCrit($crit_doc_deliverable);
                        $newiddeliverable_doc = $doc_deliverable->fields["id"];
                    }

//                        then add the document item
                    $documentitem = new Document_Item();
                    unset($documentitem_ori["documents_id"]);
                    $documentitem->add([
                        ...$documentitem_ori,
                        "documents_id" => $newiddoc,
                        "items_id" => $newiddeliverable_doc,
                        "entities_id" => $entity
                    ]);


                }


                /**copy the document**/

//            copie des variables et des notifications
                $variable_notification_query = [
                    "FROM" => PluginDlteamsDeliverable_Item::getTable(),
                    "WHERE" => [
                        "deliverables_id" => $id_ori,
                        "entities_id" => $entities_ori
                    ]

                ];
                $variable_notification_iterator = $DB->request($variable_notification_query);
                foreach ($variable_notification_iterator as $id => $variable_notification_ori) {
//                    $val0 = $row['deliverables_id']; //get documents_id

                    $deliverable_ori = new PluginDlteamsDeliverable();
                    $deliverable_temp = new PluginDlteamsDeliverable();
                    $deliverable_ori->getFromDB($variable_notification_ori["deliverables_id"]);

                    unset($deliverable_ori->fields["date_creation"]);
                    unset($deliverable_ori->fields["date_mod"]);
                    unset($deliverable_ori->fields["links_id"]);
                    unset($deliverable_ori->fields["users_id"]);
                    $exist_criteria = [
                        ...$deliverable_ori->fields,
                        "name" => addslashes($deliverable_ori["name"]),
                        "content" => addslashes($deliverable_ori["content"]),
                        "entities_id" => $entity
                    ];

                    if (!$deliverable_temp->getFromDBByCrit($exist_criteria)) {
                        $dtemp_id = $deliverable_temp->add([
                            ...$deliverable_ori->fields,
                            "entities_id" => $entity,
                        ]);
                    } else {
                        $deliverable_temp->getFromDBByCrit($exist_criteria);
                        $dtemp_id = $deliverable_temp->fields["id"];
                    }

                    $deliverable_item = new PluginDlteamsDeliverable_Item();
                    unset($variable_notification_ori["date_creation"]);
                    unset($variable_notification_ori["date_mod"]);
                    $deliverable_item->add([
                        ...$variable_notification_ori,
                        "deliverables_id" => $dtemp_id,
                        "entities_id" => $entity
                    ]);

                    /**get ID of record copied***/
//                    $reqF = $DB->request("SELECT * FROM glpi_plugin_dlteams_deliverables_items WHERE name='$val1' and entities_id='$entity'");
                }

                $DB->commit();
                return true;

            }
            else {
                $DB->rollback();
                return false;
            }
        } catch (Exception $e) {
            $DB->rollback();
            throw new Exception($e->getMessage());
        }

        return true;
    }
}

