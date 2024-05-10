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

class PluginDlteamsAllItem extends CommonDBTM {

   static function canCreate() {
      return true;
   }
   static function canView() {
      return true;
   }
   static function canUpdate() {
      return true;
   }
   static function canDelete() {
      return true;
   }
   static function canPurge() {
      return true;
   }
   function canCreateItem() {
      return true;
   }
   function canViewItem() {
      return true;
   }
   function canUpdateItem() {
      return true;
   }
   function canDeleteItem() {
      return true;
   }
   function canPurgeItem() {
      return true;
   }
   static function getTypeName($nb = 0) {
      return _n('Relation', 'Relations', $nb);
   }
   public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (!$withtemplate) {
         if (Session::haveRight($item::$rightname, READ)) {
            if ($_SESSION['glpishow_count_on_tabs']) {
               return static::createTabEntry(static::getTypeName(2), static::countForItem($item));
            }
            return static::getTypeName(2);
         }
      }
      return '';
   }

   /**
    * @param CommonDBTM $item
    *
    * @return int
   **/
   static function countForItem(CommonDBTM $item) {
      $dbu = new DbUtils();
      return $dbu->countElementsInTable(static::getTable(), ['items_id1' => $item->getID(), 'itemtype1' => $item->getType()])
      + $dbu->countElementsInTable(static::getTable(), ['items_id2' => $item->getID(), 'itemtype2' => $item->getType()]);
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         default :
            static::showTab($item, $withtemplate);
            break;
      }
      return true;
   }

   /** Give the list of Types that have a specific view - @return array */
   static public function getTypesWithSpecificView() {
      return [];
   }

   /** Give the list of names of the Header according to the type of item shown - @param string $itemtype - @return array */
   static public function getHeaderListByType($itemtype) {
      switch ($itemtype) {
         default:
            $res = [];
            $res[] =  __('Name');
			$res[] =  __('Type');
            $res[] =  __('Comment');
            if (Session::isMultiEntitiesMode()) {
               $res[] =  __('Entities');
            }
            break;
      }
      return $res;
   }

   /** Show a row according to the type of item shown
    * @param CommonDBTM $item item we show this tab for - @param array $data data from DB we want to show
    * @param object $otherItem item we want to show*/
   static public function showRowByType(CommonDBTM $item, $data, $otherItem) {
      $itemtype = $otherItem->getType();

      switch ($itemtype) {
         default:
            $ID = "";
            if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
               $ID = " (" . $data["id"] . ")";
            }
            $link = Toolbox::getItemTypeFormURL($itemtype);
            $name = "<a href=\"" . $link . "?id=" . $data["id"] . "\" target='_blank'>"
                     . $data["name"] . "$ID</a>";

            if ($item->can($item->fields['id'], UPDATE)) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["linkid"]);
               echo "</td>";
            }
			echo "<td " . (isset($data['is_deleted']) && $data['is_deleted'] ? "class='tab_bg_2_2'" : "") .
               ">" . $name . "</td>";
            echo "<td>" . $otherItem::getTypeName(1) . "</td>";
            /*echo "<td " . (isset($data['is_deleted']) && $data['is_deleted'] ? "class='tab_bg_2_2'" : "") .
               ">" . $name . "</td>";*/
            echo "<td>" . ($data['items_comment']?? "") . "</td>";

			if (Session::isMultiEntitiesMode()) {
               echo "<td>" . Dropdown::getDropdownName("glpi_entities", $data['entity']) . "</td>";
            }
            break;
      }
   }

   /* Show the list of the items of type inside $itemtypes linked to $item
    * @param CommonDBTM $item item we show this list for -     * @param array $itemtypes array of the Types that we want to show
    * @param int $rand id for forms - @param array $item_list data from DB */
   static public function viewList(CommonDBTM $item, $itemtypes, $rand, $item_list) {
      global $DB;
      $dbu  = new DbUtils();
      $instID = $item->fields['id'];
      $canedit = $item->can($instID, UPDATE);
      $number = count($itemtypes);

      if ($number == 1) {
         $str = $itemtypes[0]::getTypeName(2);
      } else {
         $str = __('Others');
      }

      echo "<div class='spaced'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_2'><th>" . $str .
         "<br><i style='font-weight: normal'>" .
         "</i></th>";
      echo "<th colspan='2'></th></tr>";
      echo "</table>";

      if ($canedit && $number) {
         Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
         $massiveactionparams = [];
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr>";

      if ($canedit && $number) {
         echo "<th width='10'>" . Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
      }

      foreach(static::getHeaderListByType($itemtypes[0]) as $str) {
         echo "<th>" . $str . "</th>";
      }
      echo "</tr>";

      foreach ($itemtypes as $itemtype) {

         if (!($otherItem = $dbu->getItemForItemtype($itemtype))) {
            continue;
         }

         if ($itemtype::canView() && isset($item_list[$itemtype])) {
            Session::initNavigateListItems($itemtype, $item::getTypeName(2) . " = " . $itemtype);
            foreach ($item_list[$itemtype] as $data) {
               $otherItem->getFromDB($data["id"]);
               Session::addToNavigateListItems($itemtype, $data["id"]);
               echo "<tr class='tab_bg_1'>";
               static::showRowByType($item, $data, $otherItem);
               echo "</tr>";
            }
         }
      }
      echo "</table>";

      if ($canedit && $number) {
         $paramsma['ontop'] = false;
         Html::showMassiveActions($paramsma);
         Html::closeForm();
      }
      echo "</div>";
   }

   static function showTab(CommonDBTM $item) {
      global $DB;
      $dbu  = new DbUtils();
      $instID = $item->fields['id'];
      $instType = $item->getType();
      if (!$item->can($instID, READ)) {
         return false;
      }
      $rand = mt_rand();
      $canedit = $item->can($instID, UPDATE);
      $query = "SELECT `t1`.`itemtype1` AS `itemtype` 
            FROM `glpi_plugin_dlteams_allitems` AS `t1`
            WHERE `t1`.`items_id2` = '$instID'
               AND  `t1`.`itemtype2` = '$instType'
         UNION 
            SELECT `t2`.`itemtype2` AS `itemtype` 
            FROM `glpi_plugin_dlteams_allitems` AS `t2`
            WHERE `t2`.`items_id1` = '$instID'
               AND  `t2`.`itemtype1` = '$instType'
         LIMIT " . count(PluginDlteamsItemType::getTypes(true));

      $result = $DB->query($query);
      $number = $DB->numrows($result);
      $itemtypes = [];
      $others = [];
      $items_list = [];
      $used = [];

      for ($i = 0; $i < $number; $i++) {
         $itemtype = $DB->result($result, $i, "itemtype");
         $itemtypes[] = $itemtype;
         $iterator = $DB->request(static::getRequest($item, $itemtype));

		foreach ($iterator as $id => $data){
         //while ($data = $iterator->next()) {
            $items_list[$itemtype][$data['linkid']] = $data;
            $used[$itemtype][$data['id']] = $data['id'];
         }
      }

      if ($canedit) {
         echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(static::class) . "'>";
         echo "<input type='hidden' name='itemtype1' value='".$item->getType()."' />";
         echo "<input type='hidden' name='items_id1' value='".$instID."' />";

         echo "<table class='tab_cadre_fixe'>";

         echo "<tr class='tab_bg_2'><th>" . __("Add Documents", 'dlteams') .
            "<br><i style='font-weight: normal'>" .
            "</i></th>";
         echo "<th colspan='2'></th></tr>";

         echo "<tr class='tab_bg_1'><td class='left' width='40%'>". __("Related objects", 'dlteams');
         echo "</td><td width='80%' class='left'>";
         $types = PluginDlteamsItemType::getTypes();
         $key = array_search(get_class($item), $types);
         unset($types[$key]);
         Dropdown::showSelectItemFromItemtypes(['itemtypes'=> $types,
                                                'entity_restrict' => ($item->fields['is_recursive'] ? getSonsOf('glpi_entities', $item->fields['entities_id'])
                                                   : $item->fields['entities_id']),
                                                'checkright' => true,
                                                'used' => $used,
												'width' => '300px',
                                             ]);
         unset($types);
         echo "</td><td width='20%' class='right'><input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
         echo "</td></tr>";
         echo "<tr class='tab_bg_1'><td width='35%' class=''>";
            echo __("Comment");
		      echo "<br/><br/>";
            echo "<textarea type='text' style='width:100%' maxlength=1000 rows='3' name='storage_comment' class='storage_comment1'></textarea>";
         echo "</td>";
         echo "</table>";
         Html::closeForm();
      }

      foreach ($itemtypes as $itemtype) {
         if (in_array($itemtype, static::getTypesWithSpecificView())) {
            static::viewList($item, [$itemtype], $rand, $items_list);
         } else {
            $others[] = $itemtype;
         }
      }

      if ($number != 0) {
         static::viewList($item, $others, $rand, $items_list);
      }
   }

   static function getRequest($item1, $itemtype2) {
      $i = 1;
      $j = 2;
      $tab_query = [];
      while($i != 3) {
         $temp = new QuerySubQuery([
            'SELECT' => ['`ext_table`.*',
               static::getTable().'.`id` AS linkid',
               static::getTable().'.`comment` AS items_comment',
               '`glpi_entities`.`id` AS entity',
               '`glpi_entities`.`completename` AS entityname'],
            'FROM' => static::getTable(),
            'LEFT JOIN' => [
               $itemtype2::getTable().' AS ext_table' => [
                  'FKEY' => [
                     static::getTable() => "items_id".$i,
                     'ext_table' => "id",
                  ]
               ],
               'glpi_entities' => [
                  'FKEY' => [
                     'ext_table' => "entities_id",
                     'glpi_entities' => "id",
                  ]
               ],
            ],
            'ORDER' => [
               'entityname',
               'name ASC'
            ],
            'WHERE' => [
               static::getTable().'.itemtype'.$i => $itemtype2,
               static::getTable().'.itemtype'.$j => $item1->getType(),
               static::getTable().'.items_id'.$j => $item1->fields['id']
            ]
         ]);
         $tab_query[] = $temp;
         $i++;
         $j--;
      }

      $union = new QueryUnion($tab_query);
      return ['FROM' => $union];
   }

   static function getRequestForItems($item1, $itemtype2, $rows) {
      $select = [static::getTable().'.id AS linkid'];
      foreach($rows as $row) {
         $select[] = 'ext_table.'.$row.' AS '.$row;
      }

      $i = 1;
      $j = 2;
      $tab_query = [];
      while($i != 3) {
         $temp = new QuerySubQuery([
            'SELECT' => $select,
            'FROM' => static::getTable(),
            'LEFT JOIN' => [
               $itemtype2::getTable().' AS ext_table' => [
                  'FKEY' => [
                     static::getTable() => "items_id".$i,
                     'ext_table' => "id",
                  ]
               ],
            ],
            'ORDER' => [
               'name ASC'
            ],
            'WHERE' => [
               static::getTable().'.itemtype'.$i => $itemtype2,
               static::getTable().'.itemtype'.$j => $item1->getType(),
               static::getTable().'.items_id'.$j => $item1->fields['id']
            ]
         ]);
         $tab_query[] = $temp;
         $i++;
         $j--;
      }

      $union = new QueryUnion($tab_query);
      return ['FROM' => $union];
   }

   public static function countSpecificItems($item, $itemtypes = []) {
      $dbu = new DbUtils();
      $count = 0;
      foreach ($itemtypes as $itemtype) {
         $count += $dbu->countElementsInTable(static::getTable(), ['items_id1' => $item->getID(), 'itemtype1' => $item->getType(), 'itemtype2' => $itemtype]);
         $count += $dbu->countElementsInTable(static::getTable(), ['items_id2' => $item->getID(), 'itemtype2' => $item->getType(), 'itemtype1' => $itemtype]);
      }
      return $count;
   }

   public static function countSpecificItems1($item, $itemtypes = []) {
      $dbu = new DbUtils();
      $count = 0;
      foreach ($itemtypes as $itemtype) {
         $count += $dbu->countElementsInTable('glpi_documents_items', ['items_id' => $item->getID(), 'itemtype' => 'PluginDlteamsRecord']);
      }
      return $count;
   }

   /*function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';

      return $forbidden;
   }*/

   function rawSearchOptions() {

      $tab = [];

      /*$tab[] = [
         'id' => '32',
         'table' => PluginDlteamsLegalbasi::getTable(),
         'field' => 'name',
         'name' => __("Bases légales"),
         'forcegroupby' => true,
         'massiveaction' => true,
         'datatype' => 'dropdown',
         'searchtype' => ['equals', 'notequals'],
         'joinparams' => [
            'beforejoin' => [
               'table' => static::getTable(),
               'joinparams' => [
                  'jointype' => 'child'
               ]
            ]
         ]
      ];

	//  if()

	  $tab[] = [
         'id' => '33',
         'table' => PluginDlteamsProtectiveMeasure::getTable(),
         'field' => 'name',
         'name' => __("Mesures de protection"),
         'forcegroupby' => true,
         'massiveaction' => true,
         'datatype' => 'dropdown',
         'searchtype' => ['equals', 'notequals'],
         'joinparams' => [
            'beforejoin' => [
               'table' => static::getTable(),
               'joinparams' => [
                  'jointype' => 'child'
               ]
            ]
         ]
      ];

	  $tab[] = [
         'id' => '34',
         'table' => PluginDlteamsDatabase::getTable(),
         'field' => 'name',
         'name' => __("Bases de données"),
         'forcegroupby' => true,
         'massiveaction' => true,
         'datatype' => 'dropdown',
         'searchtype' => ['equals', 'notequals'],
         'joinparams' => [
            'beforejoin' => [
               'table' => static::getTable(),
               'joinparams' => [
                  'jointype' => 'child'
               ]
            ]
         ]
      ];*/

	   $tab[] = [
		   'id'            => '44',
		   'table'         => static::getTable(),
		   'field'         => 'comment',
		   'name'          => __('Commentaire'),
		   'datatype'      => 'text',
		   'massiveaction' => true // <- NO MASSIVE ACTION
		];

      /*$tab[] = [
         'id' => '41',
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
      ];*/



 return $tab;
   }

}
