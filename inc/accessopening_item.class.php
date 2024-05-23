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

class PluginDlteamsAccessOpening_Item extends CommonDBRelation
{
//    public static $itemtype_1 = 'PluginDlteamsAccessOpening';
//    public static $items_id_1 = 'accessopenings_id';
//    public static $take_entity_1 = false;
//
//    public static $itemtype_2 = 'itemtype';
//    public static $items_id_2 = 'items_id';
//    public static $take_entity_2 = true;
//
//    public static $column1_id = "31";
//    public static $column2_id = "32";

    static public $itemtype_2 = PluginDlteamsAccessOpening::class;
    static public $itemtype_1;
    public static $items_id_1;
    public static $title;
    public static $sub_title;
    public static $table_match_str = [];

    public function __construct()
    {
        static::$itemtype_1 = str_replace("_Item", "", __CLASS__); // $itemtype_1 ---> PluginDlteamsProtectiveMeasure
        static::$items_id_1 = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", __CLASS__))) . "s_id";
        static::$title = __("Lieux ou se trouvent cet élément", 'dlteams');
        static::$sub_title = __("Choisir un lieu", 'dlteams');
        static::$table_match_str = [
            [
                'head_text' => __("Name"),
                'column_name' => 'name',
                'show_as_link' => true
            ],
            [
                'head_text' => __("Type"),
                'column_name' => 'typename',
            ],
            [
                'head_text' => __("Categorie"),
                'column_name' => 'namecat',
            ],
//            [
//                'head_text' => __("Content"),
//                'column_name' => 'content',
//            ],
            [
                'head_text' => __("Comment"),
                'column_name' => 'comment',
            ]
        ];
//        self::forceTable('glpi_plugin_dlteams_locations_items');
        parent::__construct();
    }


    public function post_purgeItem()
    {
//        purge relations
        $relation_item_str = $this->fields["itemtype"] . "_Item";
        if($relation_item_str == "Location_Item")
            $relation_item_str = PluginDlteamsLocation_Item::class;
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
        $relation_item_str = $this->fields["itemtype"] . "_Item";
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
}
