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

require_once ('record_storage.class.php');

class PluginDlteamsRecord_LegalBasi extends CommonDBRelation implements
PluginDlteamsExportableInterface {

   static public $itemtype_1 = 'PluginDlteamsRecord';
   static public $items_id_1 = 'plugin_dlteams_records_id';
   static public $itemtype_2 = PluginDlteamsLegalBasi::class;
   static public $items_id_2 = 'plugin_dlteams_legalbasis_id';

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

      return __("Legality and retention period", 'dlteams');
   }
   /**
    * Export in an array all the data of the current instanciated PluginDlteamsRecord_LegalBasisAct
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
    public function exportToDB($remove_uuid = false, $subitems = []) {
      if ($this->isNewItem()) {
         return false;
      }

      $Legal_basics_acts = $this->fields;
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

            return self::createTabEntry(PluginDlteamsRecord_LegalBasi::getTypeName($nb), $nb);
      }

      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      switch ($item->getType()) {
         case PluginDlteamsRecord::class :
            self::showForRecord($item, $withtemplate);
			   PluginDlteamsRecord_Storage::showForRecord($item, $withtemplate);
            break;
      }

      return true;
   }

   static function showForRecord(PluginDlteamsRecord $record, $withtemplate = 0) {

      
      $id = $record->fields['id'];
      if (!$record->can($id, READ)) {
         return false;
      }

      $canedit = PluginDlteamsRecord::canUpdate();
      $rand = mt_rand(1, mt_getrandmax());
      
      global $CFG_GLPI;
      global $DB;



      $iterator = $DB->request(self::getRequest($record));
	  //$iterator = $DB->request(PluginDlteamsAllItem::getRequestForItems($record,static::$itemtype_2,['id','name']));
      $number = count($iterator);
      $items_list = [];
      $used = [];
    
      while ($data = $iterator->next()) {
         $items_list[$data['linkid']] = $data;
         $used[$data['id']] = $data['id'];
      }
      // choose legalbasis for this processing
	  if ($canedit) {
         echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsAllItem::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='".$record->getType()."' />";
            echo "<input type='hidden' name='items_id1' value='".$record->getID()."' />";
            echo "<input type='hidden' name='itemtype' value='".PluginDlteamsLegalbasi::getType()."' />";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th colspan='3'>" . __("Add Legal Basis", 'dlteams') .
            "<br><i style='font-weight: normal'>" .
            __("Organism must be based on legal bases that allow this processing", 'dlteams') .
            "</i></th>";
		   echo "</tr>";

         echo "<tr class='tab_bg_1'><td class='' width='40%'>". __("Add Legal Basis to record", 'dlteams');
         echo "</td><td width='40%' class='left'>";
         /*PluginGenericobjectBaseslegale::dropdown([
            'addicon'  => PluginGenericobjectBaseslegale::canCreate(),
            'name' => 'plugin_genericobject_baseslegales_id',
            'width' => '60%'
         ]);*/
		   PluginDlteamsLegalbasi::dropdown([
            'addicon'  => PluginDlteamsLegalbasi::canCreate(),
            'name' => 'items_id',
            'width' => '60%',
            'used' => $used
         ]);
		 
		 echo "</td>";
		 echo "<td width='20%' class='left'>";
         /*echo "</td></tr><tr><td colspan='2' class='center'>";*/
         echo "<input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
      }

		// send data to form
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
         $header_end .= "<th>" . __("Type") . "</th>";
         $header_end .= "<th>" . __("Comment") . "</th>";
         $header_end .= "</tr>";

         echo $header_begin . $header_top . $header_end;
         foreach ($items_list as $data) {
            if($data['name']){
               echo "<tr class='tab_bg_1'>";

            if ($canedit && $number) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(PluginDlteamsAllItem::class, $data['linkid']);
               echo "</td>";
            }
            foreach ([
               "" => 'PluginDlteamsLegalBasi',
            ] as $table => $class) {
               $link = $data[$table.'name'];
               if ($_SESSION['glpiis_ids_visible'] || empty($data[$table.'name'])) {
                  $link = sprintf(__("%1\$s (%2\$s)"), $link, $data[$table.'id']);
               }
			   
			   

               $name = "<a target='_blank' href=\"" . $class::getFormURLWithID($data[$table.'id']) . "\">" . $link . "</a>";
               //$type = "<a href=\"" . $class::getFormURLWithID($data[$table.'id']) . "\">" . $link1 . "</a>";
               
			   // dysplay check box & name
			   echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
               echo ">" . $name . "</td>";

               // dysplay nametype
               echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
               echo ">" ;
               //if($type){ 	    // if($data['nametype']){
                  echo($data['typename']);  //   echo($data['nametype']);
              // }
                echo"</td>";
				
				// dysplay comment
               echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
               echo ">" ; 
               if($data['comment']){
                  echo $data['comment'];
               } 
               echo "</td>";
            
            }
            echo "</tr>";
         }
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

   /*function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';

      return $forbidden;
   }*/
   
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
   
   // show table list 
   static function getRequest($record) {
      $sub1 = new QuerySubQuery([
         'SELECT' => [
            'glpi_plugin_dlteams_allitems.id AS linkid',
            'glpi_plugin_dlteams_legalbasis.id AS id',
            'glpi_plugin_dlteams_legalbasis.name AS name', 
			'glpi_plugin_dlteams_legalbasis.plugin_dlteams_legalbasistypes_id AS type', 
			'glpi_plugin_dlteams_legalbasistypes.name AS typename',
            //'glpi_plugin_dlteams_legalbasis.content AS content',
		    'glpi_plugin_dlteams_allitems.comment AS comment',
         ],
         'FROM' => 'glpi_plugin_dlteams_allitems',
         'LEFT JOIN' => [
            'glpi_plugin_dlteams_legalbasis' => [
               'FKEY' => [
                  'glpi_plugin_dlteams_allitems' => "items_id1",
                  'glpi_plugin_dlteams_legalbasis' => "id",
               ]
            ],
         ],
		     'JOIN' => [
            'glpi_plugin_dlteams_legalbasistypes' => [
               'FKEY' => [
                  'glpi_plugin_dlteams_legalbasis' => "plugin_dlteams_legalbasistypes_id",
                  'glpi_plugin_dlteams_legalbasistypes' => "id"
               ]
         ],
         ],
         'ORDER' => [
            'glpi_plugin_dlteams_legalbasis.name ASC'
         ],
         'WHERE' => [
            'glpi_plugin_dlteams_allitems.items_id2' => $record->fields['id'],
            'glpi_plugin_dlteams_allitems.itemtype2' => $record->getType(),
            'glpi_plugin_dlteams_allitems.itemtype1' => PluginDlteamsLegalbasi::getType(),
         ]
      ]);

      $sub2 = new QuerySubQuery([
         'SELECT' => [
            'glpi_plugin_dlteams_allitems.id AS linkid',
            'glpi_plugin_dlteams_legalbasis.id AS id',
            'glpi_plugin_dlteams_legalbasis.name AS name', 
			'glpi_plugin_dlteams_legalbasis.plugin_dlteams_legalbasistypes_id AS type', 
			'glpi_plugin_dlteams_legalbasistypes.name AS typename',			
            //'glpi_plugin_dlteams_legalbasis.content AS content',
		    'glpi_plugin_dlteams_allitems.comment AS comment',
         ],
         'FROM' => 'glpi_plugin_dlteams_allitems',
         'LEFT JOIN' => [
            'glpi_plugin_dlteams_legalbasis' => [
               'FKEY' => [
                  'glpi_plugin_dlteams_allitems' => "items_id2",
                  'glpi_plugin_dlteams_legalbasis' => "id",
               ]
            ],
         ],
		     'JOIN' => [
            'glpi_plugin_dlteams_legalbasistypes' => [
               'FKEY' => [
                  'glpi_plugin_dlteams_legalbasis' => "plugin_dlteams_legalbasistypes_id",
                  'glpi_plugin_dlteams_legalbasistypes' => "id"
               ]
         ],
         ],
         'ORDER' => [
            'glpi_plugin_dlteams_legalbasis.name ASC'
         ],
         'WHERE' => [
            'glpi_plugin_dlteams_allitems.items_id1' => $record->fields['id'],
            'glpi_plugin_dlteams_allitems.itemtype1' => $record->getType(),
            'glpi_plugin_dlteams_allitems.itemtype2' => PluginDlteamsLegalbasi::getType(),
         ]
      ]);

      $union = new QueryUnion([$sub1,$sub2]);
      return ['FROM' => $union];
   } 


   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id' => 'legalbasi',
         'name' => PluginDlteamsLegalbasi::getTypeName(0)
      ];

      $tab[] = [
         'id' => '31',
         'table' => PluginDlteamsLegalbasi::getTable(),
         'field' => 'name',
         'name' => __("Name"),
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
