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

class PluginDlteamsStoragePeriod_onglet extends CommonDBTM {

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
      return _n('Traitement', 'Traitement', $nb);
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

  

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case PluginDlteamsStoragePeriod::class :
            static::showTab($item, $withtemplate);
            break;
      }
        return true;
   }
   
   static function showTab(CommonDBTM $item) {
	//$record = new PluginDlteamsRecord(); 
 $id = $item->fields['id'];
 //var_dump($id);
      if (!$item->can($id, READ)) {
         return false;
      }

      $canedit = PluginDlteamsRecord::canUpdate();
      $rand = mt_rand(1, mt_getrandmax());

      global $CFG_GLPI;
      global $DB;
      // Request that joins 3 table (not possible to do with CommonDBRelation methods)
      // Result is used lower to display a table

      $iterator = $DB->request(self::getRequest($item));
	  //var_dump($iterator);
      $number = count($iterator);

      $items_list = [];
      $used = [];
	   foreach ($iterator as $id => $data){
      //while ($data = $iterator->next()) {
         $items_list[$data['linkid']] = $data;
         $used[$data['linkid']] = $data['linkid'];
      }

      // Displays the table
      if ($iterator) {

         echo "<div class='spaced'>";
         if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . PluginDlteamsRecord_Storage::class . $rand);
            $massive_action_params = ['container' => 'mass' . PluginDlteamsRecord_Storage::class . $rand,
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
            $header_top     .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsRecord_Storage::class . $rand);
            $header_bottom  .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsRecord_Storage::class . $rand);
            $header_end     .= "</th>";
         }

         $header_end .= "<th class='left'>" . __("Nom", 'dlteams') . "</th>";
         $header_end .= "<th class='left'>" . __("Type", 'dlteams') . "</th>";
		 $header_end .= "<th class='left'>" . __("Stockage", 'dlteams') . "</th>";
		 $header_end .= "<th class='left'>" . __("Action fin période", 'dlteams') . "</th>";
        // $header_end .= "<th class='left'>" . __("Obligatoire", 'dlteams') . "</th>";
         // To Do
         /*$header_end .= "<th width='15%' class='center'>" . __("GDPR Sensitive", 'dlteams') . "</th>";*/
         //To Do
         $header_end .= "</tr>";

         echo $header_begin . $header_top . $header_end;                                
         // Prints the content
         foreach ($items_list as $data) {
            if($data['name']){
            echo "<tr class='tab_bg_1'>";

            if ($canedit && $number) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(PluginDlteamsRecord_Storage::class, $data['linkid']);                    
               echo "</td>";
            }
            // Loop to displays columns of 2 items
            //foreach ([
               //"gpdp" => 'PluginDlteamsConcernedPerson',
               //"gpdd" => 'PluginDlteamsProcessedData'
               //] as $table => $class) {
               $link = $data['nameRegistre'];
               if ($_SESSION['glpiis_ids_visible'] || empty($data['nameRegistre'])) {
                 $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['idRecord']);
				   
               }
			   $linkURL="../front/record.form.php";
               $name = "<a target='_blank' href=\"" . $linkURL ."?id=" . $data["idRecord"] . "\">" . $link . "</a>";
               
               echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
               echo ">" . $name . "</td>";
            //}
			echo "<td class='left'>Registre des traitements</td>";
			echo "<td class='left'>" . ($data['name']) . "</td>";
			echo "<td class='left'>" . ($data['name1']) . "</td>";
            //echo "<td class='left'>" . ($data['mandatory']? __("Yes") : __("No")) . "</td>";
            //To Do
           /* echo "<td class='center'>" . ($data['gpdpsensible']? __("Yes") : __("No")) . "</td>";*/
           //To Do
            echo "</tr>";
         }
         }

         if ($iterator->count() > 10) {
            echo $header_begin . $header_bottom . $header_end;
         }
         echo "</table>";

         if ($canedit && $number > 10) {
            $massive_action_params['ontop'] = false;
            Html::showMassiveActions($massive_action_params);
            Html::closeForm();
         }

         echo "</div>";
      }
   }

   static function getRequest($record) {
		return [
			 'SELECT' => [
				'glpi_plugin_dlteams_records_storages.id AS linkid',
				'glpi_plugin_dlteams_records_storages.storage_comment AS storage_comment',
				'glpi_plugin_dlteams_records_storages.storage_action AS storage_action',
				'glpi_plugin_dlteams_records.name AS nameRegistre',
				'glpi_plugin_dlteams_records.id AS idRecord',
				'glpi_plugin_dlteams_storagetypes.id AS id',
				'glpi_plugin_dlteams_storagetypes.name AS name',
				   'glpi_plugin_dlteams_storageendactions.id AS id1',
				'glpi_plugin_dlteams_storageendactions.name AS name1',
				'glpi_plugin_dlteams_storageperiods.id AS gpdpid',
				'glpi_plugin_dlteams_storageperiods.name AS gpdpname',
			 ],
			 'FROM' => 'glpi_plugin_dlteams_records_storages',
			 'INNER JOIN' => [
				'glpi_plugin_dlteams_storagetypes' => [
				   'FKEY' => [
					  'glpi_plugin_dlteams_records_storages' => "plugin_dlteams_storagetypes_id",
					  'glpi_plugin_dlteams_storagetypes' => "id",
				   ]
				],
				'glpi_plugin_dlteams_storageendactions' => [
				   'FKEY' => [
					  'glpi_plugin_dlteams_records_storages' => "plugin_dlteams_storageendactions_id",
					  'glpi_plugin_dlteams_storageendactions' => "id",
				   ]
				],
				'glpi_plugin_dlteams_records' => [
					'FKEY' => [
						'glpi_plugin_dlteams_records_storages' => "plugin_dlteams_records_id",
						'glpi_plugin_dlteams_records' => "id",
               
                ],
            ]
			 ],
			 
			 'JOIN' => [
				'glpi_plugin_dlteams_storageperiods' => [
				   'FKEY' => [
					  'glpi_plugin_dlteams_records_storages' => "plugin_dlteams_storageperiods_id",
					  'glpi_plugin_dlteams_storageperiods' => "id",
				   ]
				]
			 ],
			 'ORDER' => [
				'name ASC',
				'gpdpname ASC'
			 ],
			 'WHERE' => [
				'glpi_plugin_dlteams_records_storages.plugin_dlteams_storageperiods_id' => $record->fields['id']
			 ]
		  ];
   }
   
   
   function getForbiddenStandardMassiveAction() {

      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';

      return $forbidden;
   }
  

 public static function countForItem($item) {
      $dbu = new DbUtils();
      $count = 0;
         $count += $dbu->countElementsInTable('glpi_plugin_dlteams_records_storages', ['plugin_dlteams_storageperiods_id' => $item->getID()]);
      return $count;
   }

}