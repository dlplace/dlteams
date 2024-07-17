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

class PluginDlteamsDataCarrierType_Item extends CommonDBTM
{
    static public $itemtype_2 = 'PluginDlteamsDataCarrierType';
    static public $itemtype_1;
    public static $items_id_1;
    public static $title;
    public static $sub_title;
    public static $table_match_str = [];

    public function __construct()
    {
        static::$itemtype_1 = str_replace("_Item", "", __CLASS__); // $itemtype_1 ---> PluginDlteamsPolicieForm
        static::$items_id_1 = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", __CLASS__))) . "s_id";
        parent::__construct();
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
            "itemtype" => static::$itemtype_2,
            "items_id" => $this->fields[static::$items_id_1],
            $relation_column_id => $this->fields["items_id"],
            "comment" => $this->fields["comment"]
        ];

        $relation_item->deleteByCriteria($criteria);
    }


    public function post_updateItem($history = 1)
    {
        /*        highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//        die();
        $relation_item_str = $this->fields["itemtype"] . "_Item";
        if(!class_exists($relation_item_str))
            $relation_item_str = "PluginDlteams".$relation_item_str;
        $relation_item = new $relation_item_str();
        $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

        $criteria = [
            "itemtype" => static::$itemtype_2,
            "items_id" => $this->fields[static::$items_id_1],
            $relation_column_id => $this->fields["items_id"],
            "comment" => $this->oldvalues["comment"]
        ];

        $relation_item->deleteByCriteria($criteria);
        $relation_item->add([
            ...$criteria,
            "comment" => $this->fields["comment"]
        ]);
    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }

}
