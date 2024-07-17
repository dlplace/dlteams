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

class PluginDlteamsProject_Ma extends CommonDBTM {

    static public $itemtype_2 = 'PluginDlteamsDataCatalog';

    static function canCreate()
    {
        return true;
    }

    static function canView()
    {
        return true;
    }
//
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


    public static function getTable($classname = null)
    {
        return PluginDlteamsDataCatalog::getTable(); // TODO: Change the autogenerated stub
    }

    public function rawSearchOptions()
    {
        return parent::rawSearchOptions(); // TODO: Change the autogenerated stub
    }


    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        $forbidden[] = 'MassiveAction:update';
        return $forbidden;
    }

    public function getSpecificMassiveActions($checkitem = NULL)
    {
        $actions = parent::getSpecificMassiveActions($checkitem);
        // add a single massive action
        $class = __CLASS__;
        $action_key = "delete_dlteams_action";
        $action_label = _n("Supprimer", "Supprimer", 0, "dlteams");

        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;

        return $actions;
    }

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



    public static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {

        switch ($ma->getAction()){
            case 'copyTo':
/*                highlight_string("<?php\n\$data =\n" . var_export($ma->POST, true) . ";\n?>");*/
//                die();
                foreach ($ids as $id){
                    if ($item->getFromDB($id)) {
                        if (static::copy1($ma->POST['entities_id'], $id, $item)) {

                            Session::addMessageAfterRedirect(sprintf(__('%s copied: %s', 'dlteams'), $item->getTypeName(), $item->getName()));
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                        }
                    } else {
                        // Example of ko count
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                    }
                }
                $ma->itemDone(__CLASS__, $id, MassiveAction::ACTION_OK);
                return true;
                break;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids); // TODO: Change the autogenerated stub
    }

    public static function copy1($entity, $id, $item)
    {
        global $DB;
        $DB->beginTransaction();
        $dbu = new DbUtils();
        $name = $item->fields['name'];

        $nb = $dbu->countElementsInTable($item->getTable(), ['name' => addslashes($name), 'entities_id' => $entity]);

        $dt = new Project();
        $dt->getFromDB($id);

        if ($nb <= 0) {
//            $DB->request("INSERT INTO " . static::getTable() . " (entities_id, is_recursive, date_mod, date_creation, name, content, comment, plugin_dlteams_rightmeasurecategories_id) SELECT '$entity', is_recursive, date_mod, date_creation, name, content, comment, 0 FROM " . static::getTable() . " WHERE id='$id'");
            $datacatalog = new Project();
            $datacatalog->add([
                "name" => addslashes($dt->fields["name"]),
                "content" => addslashes($dt->fields["content"]),
                "comment" => addslashes($dt->fields["comment"]),
                "entities_id" => $entity,
            ]);

            $newid = $datacatalog->fields["id"];

//            $relations_to_copy = [
//                PluginDlteamsPolicieForm::class,
//                PluginDlteamsAppliance::class,
//                PluginDlteamsProtectiveMeasure::class
//            ];


            $uuid = plugin_dlteams_getUuid();
//            copie des projects task
//            $nb = $dbu->countElementsInTable(ProjectTask::getTable(), ['name' => addslashes($name), 'entities_id' => $entity]);
            $DB->request("INSERT INTO " . ProjectTask::getTable() . " (uuid, name, content, comment, completename, entities_id, projects_id, projecttasks_id) SELECT '$uuid', name, content, comment, completename, $entity, projects_id, projecttasks_id FROM " . ProjectTask::getTable() . " WHERE projects_id='$id'");

            $oldproject_task_idx_result = $DB->query("SELECT id FROM `glpi_projecttasks` WHERE projects_id='$id'");
            $oldproject_task_idx = $DB->fetchAssoc($oldproject_task_idx_result);
            $result = $DB->query('SELECT LAST_INSERT_ID() as last_id FROM `glpi_projecttasks`');
            $data = $DB->fetchAssoc($result);
            $newprojecttask_id = $data["last_id"];
            foreach ($oldproject_task_idx as $opt_id){
                $DB->request("INSERT INTO " . ProjectTask_Ticket::getTable() . " (tickets_id, projecttasks_id) SELECT projects_id, '$newprojecttask_id' FROM " . ProjectTask_Ticket::getTable() . " WHERE projecttasks_id='$opt_id')");
            }
            highlight_string("<?php\n\$data =\n" . var_export($data, true) . ";\n?>");
            die();
//            copie des projecttask ticket


            highlight_string("<?php\n\$data =\n" . var_export($data, true) . ";\n?>");
            die();



            $DB->request("INSERT INTO " . PluginDlteamsDataCatalog_Item::getTable() . " (datacatalogs_id, items_id, itemtype, comment) SELECT '$newid', items_id, itemtype, comment FROM " . PluginDlteamsDataCatalog_Item::getTable() . " WHERE datacatalogs_id='$id' AND (itemtype='PluginDlteamsPolicieForm' OR itemtype='PluginDlteamsAppliance' OR itemtype='PluginDlteamsProtectiveMeasure')");
            $DB->request("INSERT INTO " . PluginDlteamsPolicieForm_Item::getTable() . " (policieforms_id, items_id, itemtype, comment) SELECT policieforms_id, '$newid', itemtype, comment FROM " . PluginDlteamsPolicieForm_Item::getTable() . " WHERE itemtype='PluginDlteamsDataCatalog' AND items_id='$id'");
            $DB->request("INSERT INTO " . Appliance_Item::getTable() . " (appliances_id, items_id, itemtype, comment) SELECT appliances_id, '$newid', itemtype, comment FROM " . Appliance_Item::getTable() . " WHERE itemtype='PluginDlteamsDataCatalog' AND items_id='$id'");
            $DB->request("INSERT INTO " . PluginDlteamsProtectiveMeasure_Item::getTable() . " (protectivemeasures_id, items_id, itemtype, comment) SELECT protectivemeasures_id, '$newid', itemtype, comment FROM " . PluginDlteamsProtectiveMeasure_Item::getTable() . " WHERE itemtype='PluginDlteamsDataCatalog' AND items_id='$id'");

            return true;
        } else {
            return false;
        }
    }


}