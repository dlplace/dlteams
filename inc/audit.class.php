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

class PluginDlteamsAudit extends CommonDropdown
{
    static $rightname = 'plugin_dlteams_audit';
    public $dohistory = true;
    protected $usenotepad = true;

    static function getTypeName($nb = 0)
    {
        return _n("Audit", "Audits", $nb, 'dlteams');
    }

    function showForm($id, $options = [])
    {
        global $CFG_GLPI;
        $this->initForm($id, $options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'><td width='50%'>" . __('Name') . "</td>";
        echo "<td colspan='2'>";
        Html::autocompletionTextField($this, "name");
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='50%'>" . __('Content') . "</td>";
        echo "<td colspan='2'>
               <textarea cols='50' rows='4' name='content' >" . $this->fields["content"];
        echo "</textarea></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='50%'>" . __("Audit catégorie", 'dlteams') . "</i></td>";
        echo "<td colspan='2'>";
        PluginDlteamsAuditCategory::dropdown([
            'addicon' => PluginDlteamsAuditCategory::canCreate(),
            'name' => 'plugin_dlteams_auditcategories_id',
            'width' => '76%',
            'value' => $this->fields['plugin_dlteams_auditcategories_id']
        ]);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'><td width='50%'>" . __('Comments') . "</td>";
        echo "<td colspan='2'>
               <textarea cols='50' rows='4' name='comment' >" . $this->fields["comment"];
        echo "</textarea></td>";
        echo "</tr>";


        $this->showFormButtons($options);


        $script = "<script>";
        $script .= "$(document).ready(function(e){";
        $script .= "
        var confirmed = false;
        $('button[name=update]').click(function(e){
        if(!confirmed){
            e.preventDefault();
            
            glpi_confirm({
            message: 'La précedente ressource attachée va être supprimée, êtes vous d\'accord ?',
            confirm_label: 'Supprimer et continuer',
            cancel_label: 'Conserver',
            confirm_callback: function() {
               confirmed = true;

               //trigger click on the same element (to return true value)
               e.target.click();

               // re-init confirmed (to permit usage of 'confirm' function again in the page)
               // maybe timeout is not essential ...
               setTimeout(function() {
                  confirmed = false;
               }, 100);
            }
         });
        }
           
        });
        ";
        $script .= "});";

        echo $script;

        return true;
    }


    function rawSearchOptions()
    {

//       $trace = debug_backtrace();
//       echo 'Fichier : ' . $trace[0]['file'] . ' Ligne : ' . $trace[0]['line'];
//       die();

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
            'table' => 'glpi_plugin_dlteams_auditcategories',
            'field' => 'name',
            'name' => __("Type", 'dlteams'),
            'massiveaction' => true,
            'datatype' => 'dropdown',
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


//        $tab[] = [
//            'id' => '6',
//            'table' => Document_Item::getTable(),
//            'field' => 'items_id',
//            'name' => _x('quantity', 'Nb éléments'),
//            'datatype' => 'count',
//            'massiveaction' => false,
//            'joinparams' => [
//                'jointype' => 'itemtype_item'
//            ]
////            'joinparams' => [
////                'beforejoin' => [
////                    'table' => PluginDlteamsRecord_Item::getTable(),
////                    'joinparams' => [
////                        'jointype' => 'left',
////                        'alias' => 'items',
////                        'joincondition' => [
////                            [
////                                'fieldleft' => 'items.items_id',
////                                'fieldright' => PluginDlteamsProtectiveMeasure::getTable() . '.id',
////                            ]
////                        ]
////                    ]
////                ],
////                'afterjoin' => [
////                    'table' => 'glpi_plugin_dlteams_records_items',
////                    'joinparams' => [
////                        'jointype' => 'left',
////                        'alias' => 'record_items',
////                        'joincondition' => [
////                            [
////                                'fieldleft' => 'record_items.id',
////                                'fieldright' => 'items.itemtype_id',
////                            ]
////                        ],
////                        'update' => [
////                            'items_id' => [
////                                'datatype' => 'itemlink',
////                                'massiveaction' => true,
////                                'itemlink_type' => 'PluginDlteamsRecord_Item',
////                                'itemlink_field' => 'id',
////                                'itemlink_foreignkey' => 'items_id',
////                            ],
////                        ],
////                    ]
////                ]
////            ],
////            'addwhere' => [
////                [
////                    'link' => 'AND',
////                    'field' => 'record_items.id',
////                    'searchtype' => 'isnotempty',
////                ]
////            ],
//        ];

        return $tab;
    }

    /**
     * @see CommonDBTM::showMassiveActionsSubForm()
     */
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


    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        switch ($ma->getAction()) {
            case 'copyTo':
                if ($item->getType() == 'PluginDlteamsAudit') {
                    /** @var PluginDlteamsAudit $item */
                    foreach ($ids as $id) {
                        if ($item->getFromDB($id)) {

                            if ($item->copy1($ma->POST['entities_id'], $id, $item)) {

                                Session::addMessageAfterRedirect(sprintf(__('Audit copied: %s', 'dlteams'), $item->getName()));
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

    public function copy1($entity, $id, $item)
    {
        global $DB;
        $dbu = new DbUtils();
        $name = str_replace('"', '', addslashes($item->fields['name']));
        $entities_ori = $item->fields['entities_id'];
        $id_ori = $item->fields['id'];

        $plugin_dlteams_auditcategories_id = $item->fields['plugin_dlteams_auditcategories_id'];

        $nb = $dbu->countElementsInTable(static::getTable(), ['name' => $name, 'entities_id' => $entity]);
        //var_dump($nb);

        if ($nb <= 0) {
            $date = date('Y-m-d H:i:s');
            $user_id = $_SESSION['glpiID'];
            $categories = $DB->request("SELECT name, id FROM " . static::getCategoryTable() . " WHERE id='$plugin_dlteams_auditcategories_id'");
            foreach ($categories as $row) {
                $category_name = $row["name"];
            }
            $DB->request("INSERT INTO " . static::getCategoryTable() . " (name, comment, entities_id, is_recursive, date_mod, date_creation) SELECT name, comment, '$entity', is_recursive, '$date', '$date' FROM " . static::getCategoryTable() . " WHERE id='$plugin_dlteams_auditcategories_id'");

            $newcategories = $DB->request("SELECT name, id FROM " . static::getCategoryTable() . " WHERE entities_id='$entity' AND name='$category_name'");
            foreach ($newcategories as $row) {
                $newcategory_id = $row["id"];
            }
            $DB->request("INSERT INTO " . static::getTable() . " (entities_id, is_recursive, states_id, name, plugin_dlteams_auditcategories_id, content, comment, notepad, date_mod, date_creation, is_helpdesk_visible, users_id, use_tickets) 
                                    SELECT '$entity', is_recursive, states_id, name, '$newcategory_id', content, comment, notepad, '$date', '$date', is_helpdesk_visible, '$user_id', use_tickets FROM " . static::getTable() . " WHERE id='$id_ori'");

            return true;
        } else {


            return false;
        }
    }


    /**
     * Return the table used to store this object
     *
     * @param string $classname Force class (to avoid late_binding on inheritance)
     *
     * @return string
     **/
    public static function getCategoryTable($classname = null)
    {
        return 'glpi_plugin_dlteams_auditcategories';
    }

    public function defineTabs($options = [])
    {
        $ong = [];

        $ong = array();
        //add main tab for current object

        $this->addDefaultFormTab($ong)
            ->addStandardTab(PluginDlteamsAudit_Item::class, $ong, $options)
            ->addStandardTab(PluginDlteamsTicket::class, $ong, $options)
//            ->addStandardTab(Ticket::class, $ong, $options)
//            ->addStandardTab(ProjectTask_Ticket::class, $ong, $options)
            ->addStandardTab('PluginDlteamsObject_document', $ong, $options)
            ->addStandardTab('ManualLink', $ong, $options)
//	  ->addStandardTab('PluginDlteamsObject_allitem', $ong, $options)
            ->addStandardTab(PluginDlteamsKnowbaseItem_Item::class, $ong, $options)
            ->addImpactTab($ong, $options)
            ->addStandardTab('Notepad', $ong, $options)
            ->addStandardTab('Log', $ong, $options);
        return $ong;
    }
}
