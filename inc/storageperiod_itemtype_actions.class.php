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

class PluginDlteamsStoragePeriod_Itemtype_Actions extends CommonDBTM
{

    public function __construct()
    {
        self::forceTable(PluginDlteamsStoragePeriod_Item::getTable());
    }

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

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:add_transfer_list';
//        $forbidden[] = 'MassiveAction:update';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }

    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        switch ($ma->getAction()) {
            case 'update_html_comment':
                Html::textarea(['name' => 'comment',
                    'value' => "",
                    'cols' => 125,
                    'rows' => 3,
                    'enable_richtext' => true]);
                break;

        }
        return parent::showMassiveActionsSubForm($ma);
    }

    function getSpecificMassiveActions($checkitem = NULL)
    {
        $actions = parent::getSpecificMassiveActions($checkitem);

        // add a single massive action
        $class = __CLASS__;

        $action_key = "update_html_comment";
        $action_label = __("Mettre à jour le commentaire", "dlteams");
        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;


//        $action_key = "delete_dlteams_action";
//        $action_label = _n("Delete dlteams relation", "Delete dlteams relations", 0, "dlteams");
//        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;

        return $actions;
    }


    function rawSearchOptions()
    {
        $tab[] = [
            'id' => "43",
            'table' => PluginDlteamsStoragePeriod::getTable(),
            'field' => 'storageperiods_id',
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
            'id' => "44",
            'table' => PluginDlteamsStorageType::getTable(),
            'field' => 'plugin_dlteams_storagetypes_id',
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
            'id' => "42",
            'table' => PluginDlteamsStorageEndAction::getTable(),
            'field' => 'plugin_dlteams_storageendactions_id',
            'name' => __("En fin de période"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'dropdown',
        ];

//        $tab[] = [
//            'id' => '45',
//            'table' => static::getTable(),
//            'field' => 'comment',
//            'datatype' => 'text',
//            'name' => __("Commentaire"),
//            'forcegroupby' => true,
//            'massiveaction' => true,
//        ];

        return $tab;
    }

    public static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        global $DB;
        $DB->beginTransaction();
        switch ($ma->getAction()) {
            case 'update_html_comment':
                foreach ($ids as $key) {
                    if ($item->can($key, UPDATE)) {
                        if ($item->update([
                            "comment" => $ma->POST['comment'],
                            "id" => $key
                        ])) {
//                            $item->post_updateItem();
                            $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                        } else {
                            $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                            $ma->addMessage($item->getErrorMessage(ERROR_ON_ACTION));
                        }
                    } else {
                        $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_NORIGHT);
                        $ma->addMessage($item->getErrorMessage(ERROR_RIGHT));
                    }
                }
                break;
        }
        $DB->commit();
    }




    public function post_updateItem($history = 1)
    {
        $relation_item_str = $this->fields["itemtype"] . "_Item";
        if (!class_exists($relation_item_str))
            $relation_item_str = "PluginDlteams" . $relation_item_str;
        $relation_item = new $relation_item_str();
        $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

        $criteria = [
            "itemtype" => "PluginDlteamsStoragePeriod",
            $relation_column_id => $this->fields["items_id"],
        ];

        if (isset($this->updates["storageperiods_id"]))
            $criteria["items_id"] = $this->oldvalues["storageperiods_id"];

        if (isset($this->updates["plugin_dlteams_storageendactions_id"]))
            $criteria["plugin_dlteams_storageendactions_id"] = $this->oldvalues["plugin_dlteams_storageendactions_id"];

        if (isset($this->updates["plugin_dlteams_storagetypes_id"]))
            $criteria["plugin_dlteams_storagetypes_id"] = $this->oldvalues["plugin_dlteams_storagetypes_id"];

        if (isset($this->updates["comment"]))
            $criteria["comment"] = addslashes($this->oldvalues["comment"]);


        global $DB;
        $DB->delete(
            $relation_item->getTable(),
            [
                ...$criteria
            ]
        );
//        updaate new values

        $criteria["plugin_dlteams_storageendactions_id"] = $this->fields["plugin_dlteams_storageendactions_id"];
        $criteria["plugin_dlteams_storagetypes_id"] = $this->fields["plugin_dlteams_storagetypes_id"];
        $criteria["comment"] = addslashes($this->fields["comment"]);
        $criteria["items_id"] = $this->fields["storageperiods_id"];


        $DB->insert(
            $relation_item->getTable(),
            [
                ...$criteria,
            ]
        );


        Session::addMessageAfterRedirect("Relation mis a jour avec succès");
    }


    public function post_purgeItem()
    {
//        purge relations
        $relation_item_str = $this->fields["itemtype"] . "_Item";
        if (!class_exists($relation_item_str))
            $relation_item_str = "PluginDlteams" . $relation_item_str;
        $relation_item = new $relation_item_str();

        $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

        $criteria = [
            "itemtype" => "PluginDlteamsStoragePeriod",
            "items_id" => $this->fields["storageperiods_id"],
            $relation_column_id => $this->fields["items_id"],
            "comment" => $this->fields["comment"]
        ];

        $relation_item->deleteByCriteria($criteria);
    }
}
