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

class PluginDlteamsDocument_Item extends CommonDBTM
{
    public static $itemtype_1 = 'Document';
    public static $items_id_1 = 'documents_id';

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
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }

    public function update(array $input, $history = 1, $options = [])
    {
        global $DB;
        $document_item = new PluginDlteamsDocument_Item();
        $document_item->getFromDB($input["id"]);

//        get relation
        $record_item = new PluginDlteamsRecord_Item();
        $record_item->getFromDBByCrit([
            "itemtype" => Document::class,
            "items_id" => $document_item->fields["documents_id"],
            "records_id" => $document_item->fields["items_id"],
            "comment" => $document_item->fields["comment"],
        ]);



        if (isset($input["comment"])) {
            $DB->update(
                $document_item->getTable(),
                [
                    "comment" => $input["comment"],
                ],
                ['id' => $input["id"]]
            );
//        update record_item relation
            $DB->update(
                $record_item->getTable(),
                [
                    "comment" => $input["comment"],
                ],
                ['id' => $record_item->fields["id"]]
            );
            Session::addMessageAfterRedirect("Relation ".PluginDlteamsRecord::getTypeName()." mis a jour avec succès");
        }

        if(isset($input["mandatory"])){
            $DB->update(
                $record_item->getTable(),
                [
                    "document_mandatory" => $input["mandatory"],
                ],
                ['id' => $record_item->fields["id"]]
            );

            Session::addMessageAfterRedirect("Relation ".PluginDlteamsRecord::getTypeName()." mis a jour avec succès");
        }

        return true;

    }

    public function post_purgeItem()
    {
//        purge relations
        $relation_item_str = $this->fields["itemtype"] . "_Item";
        if (!class_exists($relation_item_str))
            $relation_item_str = "PluginDlteams" . $relation_item_str;

        if($relation_item_str == Document_Item::class)
            $relation_item_str = PluginDlteamsDocument_Item::class;

        $relation_item = new $relation_item_str();

        $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

        $criteria = [
            "itemtype" => static::$itemtype_1,
            "items_id" => $this->fields[static::$items_id_1],
            $relation_column_id => $this->fields["items_id"],
            "comment" => $this->fields["comment"]
        ];

        $relation_item->deleteByCriteria($criteria);
    }

    function rawSearchOptions()
    {
        $tab[] = [
            'id' => '43',
            'table' => static::getTable(),
            'field' => 'mandatory',
            'datatype' => 'bool',
            'name' => __("Obligatoire"),
            'forcegroupby' => true,
            'massiveaction' => true,
        ];

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


}
