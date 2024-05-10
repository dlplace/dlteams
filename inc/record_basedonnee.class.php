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

class PluginDlteamsRecord_BaseDonnee extends CommonDBRelation implements
PluginDlteamsExportableInterface {

   static public $itemtype_1 = 'PluginDlteamsRecord';
   static public $items_id_1 = 'plugin_dlteams_records_id';
   static public $itemtype_2 = 'PluginDlteamsDataCarrier';
   static public $items_id_2 = 'plugin_dlteams_datacarriers_id';

    /**
       * @since 0.84
      **/
   static function canCreate() {

      return true;
   }


   /**
    * @since 0.84
   **/
   static function canView() {
      return true;
   }


   /**
    * @since 0.84
   **/
   static function canUpdate() {
      return true;
   }


   /**
    * @since 0.84
   **/
   static function canDelete() {
      return true;
   }


   /**
    * @since 0.85
    **/
   static function canPurge() {
      return true;
   }


   /**
    * @since 0.84
   **/
   function canCreateItem() {

      return true;
   }


   /**
    * @since 0.84
   **/
   function canViewItem() {
      return true;
   }


   /**
    * @since 0.84
   **/
   function canUpdateItem() {

      return true;
   }


   /**
    * @since 0.84
   **/
   function canDeleteItem() {

      return true;
   }


   /**
    * @since 9.3.2
    */
   function canPurgeItem() {

      return true;
   }

   static function getTypeName($nb = 0) {
      return __("Support de donnée", 'dlteams');
   }
   /**
    * Export in an array all the data of the current instanciated PluginDlteamsRecord_LegalBasisAct
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
    public function exportToDB($remove_uuid = false, $subItems = []) {
      if ($this->isNewItem()) {
         return false;
      }

      $Legal_basics_acts = $this->fields;
      // remove fk
      return $Legal_basics_acts;
   }

   public static function importToDB(PluginDlteamsLinker $linker, $input = [], $record_id = 0, $subItems = []) {
      $recordFk = PluginDlteamsRecord::getForeignKeyField();
      $input[$recordFk] = $record_id;

      $item = new self();
      $originalId = $input['id'];
      unset($input['id']);
      $itemId = $item->add($input);
      if ($itemId === false) {
         $typeName = strtolower(self::getTypeName());
         throw new ImportFailureException(sprintf(__('failed to copy the %1$s record', 'dlteams'), $input['name']));
      }
      $linker->addObject($originalId, $item);
      return $itemId;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$item->canView()) {
         return false;
      }

      switch ($item->getType()) {
         case PluginDlteamsRecord::class :

            $nb = 0;
            if ($_SESSION['glpishow_count_on_tabs']) {
               $nb = self::countForItem($item);
            }

            return self::createTabEntry(PluginDlteamsRecord_BaseDonnee::getTypeName($nb), $nb);
      }

      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      switch ($item->getType()) {
         case PluginDlteamsRecord::class :
            self::showForItem($item, $withtemplate);
            break;
      }

      return true;
   }

    static function countItems(CommonDBTM $item) {

        $counter = 0;

        $id = $item->fields['id'];
        if (!$item->can($id, READ)) {
            return false;
        }

        $canedit = $item::canUpdate();
        $rand = mt_rand(1, mt_getrandmax());

        global $DB;



        // $iterator = $DB->request(PluginDlteamsAllItem::getRequestForItems($item,static::$itemtype_2,['id','name']));
        $iterator=$DB->request(self::getRequest($item));
        $number = count($iterator);
        $items_list = [];
        $used = [];

        // while ($data = $iterator->next()) {
        foreach ($iterator as $id => $data){
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($iterator) {

            //var_dump($items_list);
            foreach ($items_list as $data) {
                if ($data['name']) {
                    $counter++;
                }
            }

        }

        return $counter;

    }

   static function showForItem(CommonDBTM $item, $withtemplate = 0) {


      $id = $item->fields['id'];
      if (!$item->can($id, READ)) {
         return false;
      }

      $canedit = $item::canUpdate();
      $rand = mt_rand(1, mt_getrandmax());

      global $CFG_GLPI;
      global $DB;



     // $iterator = $DB->request(PluginDlteamsAllItem::getRequestForItems($item,static::$itemtype_2,['id','name']));
	  $iterator=$DB->request(self::getRequest($item));
      $number = count($iterator);
      $items_list = [];
      $used = [];

     // while ($data = $iterator->next()) {
	  foreach ($iterator as $id => $data){
         $items_list[$data['linkid']] = $data;
         $used[$data['id']] = $data['id'];
      }

       if ($canedit) {
           echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post' action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";
           echo "<input type='hidden' name='plugin_dlteams_records_id' value='$id' />";
//		 echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
//            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsAllItem::class) . "'>";
           echo "<input type='hidden' name='itemtype1' value='".$item->getType()."' />";
           echo "<input type='hidden' name='items_id1' value='".$item->getID()."' />";
           echo "<input type='hidden' name='itemtype' value='PluginDlteamsDataCarrier' />";
           /*echo "<input type='hidden' name='plugin_genericobject_baseslegaletypes_id'/>";*/

           echo "<table class='tab_cadre_fixe'>";
           echo "<tr class='tab_bg_2'><th>" . __("Show data carriers", 'dlteams') .
               "<br><i style='font-weight: normal'>" .
               /*__("Organism must be based on legal bases that allow this processing", 'dlteams') .*/
               "</i></th>";
           echo "<th colspan='2'></th></tr>";

           echo "<tr class='tab_bg_1'><td class='left' width='40%'>". __("Add the data carriers where the informations are stored", 'dlteams');
           echo "</td><td width='10%' class='left'>";
           PluginDlteamsDataCarrier::dropdown([
               'addicon'  => PluginDlteamsDataCarrier::canCreate(),
               'name' => 'datacarrier_items_id',
               'width' => '270px',
               'used' => $used,
           ]);

           echo "</td>";
           echo "<td width='40%'>";
           echo "<textarea type='text' style='width:100%;margin-right:5%; margin-bottom: 10px; display:none;' maxlength=1000 rows='3' id='datacarrier_textarea' name='comment' class='storage_comment1' placeholder='commentaire'></textarea>";
           echo "</td>";
           echo "<td width='20%' class='right'><input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
           /*echo "</td></tr><tr><td colspan='2' class='center'>";
           echo "<input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";*/
           echo "</td></tr>";
           echo "</table>";
           Html::closeForm();
       }

      if ($iterator) {
         echo "<div class='spaced'>";
         if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . PluginDlteamsAllItem::class . $rand);
            $massive_action_params = ['container' => 'mass' . PluginDlteamsAllItem::class . $rand,
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
            $header_top     .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsAllItem::class . $rand);
            $header_bottom  .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsAllItem::class . $rand);
            $header_end     .= "</th>";
         }

         $header_end .= "<th>" . __("Name") . "</th>";
         $header_end .= "<th>" . __("Comment") . "</th>";
         $header_end .= "</tr>";

         echo $header_begin . $header_top . $header_end;
		 //var_dump($items_list);
         foreach ($items_list as $data) {
            if ($data['name']) {
               echo "<tr class='tab_bg_1'>";

               if ($canedit && $number) {
                  echo "<td width='10'>";
                  Html::showMassiveActionCheckBox(PluginDlteamsAllItem::class, $data['linkid']);
                  echo "</td>";
               }
               $link = $data['name'];
               if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
                  $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
               }

               $name = "<a target='_blank' href=\"" . PluginDlteamsDataCarrier::getFormURLWithID($data['id']) . "\">" . $link . "</a>";
               echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
               echo ">" . $name . "</td>";

                echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                echo ">" . $data["comment"] . "</td>";

               echo "</tr>";
            }
         }


         /*if ($iterator->count() > 10) {
            echo $header_begin . $header_bottom . $header_end;
         }*/
         echo "</table>";

//         if ($canedit && $number) {
//            $massive_action_params['ontop'] = false;
//            Html::showMassiveActions($massive_action_params);
//            Html::closeForm();
//         }

         echo "</div>";
      }

   }

   /*function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';

      return $forbidden;
   }*/

    /*add by me**/
   function getSpecificMassiveActions($checkitem=NULL) {
      $actions = parent::getSpecificMassiveActions($checkitem);
      return $actions;
   }

   static function showMassiveActionsSubForm(MassiveAction $ma) {
      return parent::showMassiveActionsSubForm($ma);
   }

   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }
   /**add by me**/

        // show table list
   static function getRequest($item) {

       return [
           'SELECT' => [
               'glpi_plugin_dlteams_records_items.id AS linkid',
               'glpi_plugin_dlteams_records_items.items_id AS id',
               'glpi_plugin_dlteams_records_items.comment AS comment',
               'glpi_plugin_dlteams_datacarriers.id AS datacarriers_id',
               'glpi_plugin_dlteams_datacarriers.name AS name',
               'glpi_plugin_dlteams_datacarriers.content AS content',
           ],
           'FROM' => 'glpi_plugin_dlteams_records_items',
           'LEFT JOIN' => [
               'glpi_plugin_dlteams_datacarriers' => [
                   'FKEY' => [
                       'glpi_plugin_dlteams_records_items' => "items_id",
                       'glpi_plugin_dlteams_datacarriers' => "id",
                   ]
               ],
           ],
           'ORDER' => [ /*'glpi_plugin_dlteams_legalbasistypes.id ASC',*/ 'name ASC'],
           'WHERE' => [
               'glpi_plugin_dlteams_records_items.records_id' => $item->getID(),
               'glpi_plugin_dlteams_records_items.itemtype' => 'PluginDlteamsDataCarrier',
           ]
       ];
   }

   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id' => '37',
         'table' => PluginDlteamsDataCarrier::getTable(),
         'field' => 'name',
         'name' => __("Support de données"),
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

      return $tab;
   }

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude) {}
}

?>
