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
 
class PluginDlteamsRecord_Software extends CommonDBRelation {

   static $rightname = 'plugin_dlteams_record';

   static public $itemtype_1 = 'PluginDlteamsRecord';
   static public $items_id_1 = 'plugin_dlteams_records_id';
   static public $itemtype_2 = 'Software';
   static public $items_id_2 = 'softwares_id';

   function canPurgeItem() {
        return true;
   }

   function canDeleteItem() {
        return true;
   }

   static function canPurge() {
        return true;
   }

   static function canDelete() {
        return true;
   }

   static function getTypeName($nb = 0) {

      return _n("Software", "Software", $nb, 'dlteams');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!Software::canView() || !$item->canView()) {
         return false;
      }

      switch ($item->getType()) {
         case PluginDlteamsRecord::class :

            if ($item->fields['storage_medium'] == PluginDlteamsRecord::STORAGE_MEDIUM_PAPER_ONLY) {
               return false;
            }

            $nb = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
               $nb = self::countForItem($item);
            }

            return self::createTabEntry(PluginDlteamsRecord_Software::getTypeName($nb), $nb);
      }

      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      switch ($item->getType()) {
         case PluginDlteamsRecord::class :
            self::showForRecord($item, $withtemplate);
            break;
      }

      return true;
   }

   static function showForRecord(PluginDlteamsRecord $record, $withtemplate = 0) {

      $id = $record->fields['id'];
      if (!Software::canView() || !$record->can($id, READ)) {
         return;
      }

      $canedit = $record->can($id, UPDATE);
      $rand = mt_rand(1, mt_getrandmax());

      $iterator = self::getListForItem($record);
      $number = count($iterator);

      $items_list = [];
      $used = [];
      while ($data = $iterator->next()) {
         $items_list[$data['id']] = $data;
         $used[$data['id']] = $data['id'];
      }

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";
         echo "<input type='hidden' name='plugin_dlteams_records_id' value='$id' />";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th>" . __("Add a software", 'dlteams') . "</th></tr>";
         echo "<tr class='tab_bg_1'><td width='80%' class='center'>";

         if (PluginDlteamsConfig::getConfig('system', 'allow_software_from_every_entity')) {
            $entity = 0;
            $entity_sons = true;
         } else {
            $entity = $record->fields['is_recursive'] ? getSonsOf('glpi_entities', $record->fields['entities_id']) : $record->fields['entities_id'];
            $entity_sons = $record->fields['is_recursive'];
         }

         Software::dropdown([
            'addicon'  => Software::canCreate(),
            'name' => 'softwares_id',
            'entity' => $entity,
            'entity_sons' => $entity_sons,
            'used' => $used,
         ]);
         echo "</td></tr><tr><td width='20%' class='center'>";
         echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }

      if ($iterator) {

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

            $header_begin   .= "<th width='10'>";
            $header_top     .= Html::getCheckAllAsCheckbox('mass' . __class__ . $rand);
            $header_bottom  .= Html::getCheckAllAsCheckbox('mass' . __class__ . $rand);
            $header_end     .= "</th>";
         }

         $header_end .= "<th>" . __("Name") . "</th>";
         $header_end .= "<th>" . __("Entity") . "</th>";
         $header_end .= "<th>" . __("Publisher") . "</th>";
         $header_end .= "<th>" . __("Category") . "</th>";
         $header_end .= "</tr>";

         echo $header_begin . $header_top . $header_end;

         foreach ($items_list as $data) {

            echo "<tr class='tab_bg_1'>";

            if ($canedit && $number) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__class__, $data['linkid']);
               echo "</td>";
            }

            $link = $data['name'];
            if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
               $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
            }
            $name = "<a href=\"" . Software::getFormURLWithID($data['id']) . "\">" . $link . "</a>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $name . "</td>";

            echo "<td class='left'>";
            echo Dropdown::getDropdownName(
               Entity::getTable(),
               $data['entities_id']) . "</td>";

            echo "<td class='center'>";
            echo Dropdown::getDropdownName(
               Manufacturer::getTable(),
               $data['manufacturers_id']) . "</td>";

            echo "<td class='center'>";
            echo Dropdown::getDropdownName(
                SoftwareCategory::getTable(),
                $data['softwarecategories_id']) . "</td>";

            echo "</tr>";
         }

         if ($iterator->count() > 10) {
            echo $header_begin . $header_bottom . $header_end;
         }
         echo "</table>";

         if ($canedit && $number) {
            $massive_action_params['ontop'] = false;
            Html::showMassiveActions($massive_action_params);
            Html::closeForm();
         }

         echo "</div>";
      }
   }

   static function countForItem(CommonDBTM $item) {

      return countElementsInTable(PluginDlteamsRecord_Software::getTable(),
                                  ['plugin_dlteams_records_id' => $item->getID()]);
   }

   static function cleanForItem(CommonDBTM $item) {

      $rel = new PluginDlteamsRecord_Software();
      $rel->deleteByCriteria([
         'itemtype' => $item->getType(),
         'softwares_id' => $item->fields['id']
      ]);

   }

   static function getListForItem(CommonDBTM $item) {

      global $DB;

      $params = static::getListForItemParams($item, true);
      $iterator = $DB->request($params);

      return $iterator;
   }

   function prepareInputForUpdate($input) {
      // override hack - there was a problem in CommonDBConnexity.checkAttachedItemChangesAllowed while purging item lined to master table - permissions errors
      return $input;
   }

   function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';

      return $forbidden;
   }

   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id' => 'software',
         'name' => Software::getTypeName(0)
      ];

      $tab[] = [
         'id' => '71',
         'table' => Software::getTable(),
         'field' => 'name',
         'name' => __("Name"),
         'forcegroupby' => true,
         'massiveaction' => false,
         'datatype' => 'dropdown',
         'joinparams' => [
            'beforejoin' => [
               'table' => self::getTable(),
               'joinparams' => [
                  'jointype' => 'child'
               ]
            ]
         ]
      ];

      return $tab;
   }

}
