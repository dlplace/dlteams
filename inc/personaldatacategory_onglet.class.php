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

class PluginDlteamsPersonalDataCategory_onglet extends CommonDBTM {

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
         case PluginDlteamsConcernedPerson::class :
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

      // Displays form
      //if ($canedit) {
         /*echo "<div class='firstbloc'>";
         echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";
			$iden = $item->fields['id'];
         echo "<input type='hidden' name='plugin_dlteams_records_id' value='$iden' />";
		 
		 
		 if ($canedit) {
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th colspan='4'>" . __("Add Category of data subjects linked to a personal data type", 'dlteams') . 
			
			"<br><i style='font-weight: normal'>" .
			__("Art 4.1, 5.1, 30.1(f) concernant les données relatives au traitement", 'dlteams') .
			"</i></th>";
		 echo "</tr>";
			

            echo "<tr class='tab_bg_1'><td width='' class=''>";
            echo _n("Category of data subjectssss", "Categories of data subjects", 0, 'dlteams');
            echo "<br/><br/>";
			PluginDlteamsConcernedPerson::dropdown([
              
               'name' => "plugin_dlteams_concernedpersons_id",
               'width' => '',
            ]);
			
            echo "</td>";
            echo "<td width='' class=''>";
            echo _n("Personal Data Category", "Personal Data Categories", 0, 'dlteams');
            echo "<br/><br/>";
			PluginDlteamsProcessedData::dropdown([
               'addicon'  => PluginDlteamsProcessedData::canCreate(),
               'name' => 'plugin_dlteams_processeddatas_id',
               'width' => ''
            ]);
			
            echo "</td>";

            echo "<td class=''>";
            echo __("Mandatory", 'dlteams');
            echo "<br/><br/>";
            Dropdown::showYesNo("mandatory", 1);
            echo "</td></tr>";

            echo "<tr><td colspan='4' class='center'>";
            echo "<input type='submit' name='add' value=\"" . __('Add') . "\" class='submit' style='margin-top:10px'>";
            echo "</td></tr>";
            echo "</table>";
         }
		
         Html::closeForm();
         echo "</div>";*/
      //}

      // Displays the table
      if ($iterator) {

         echo "<div class='spaced'>";
         if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . PluginDlteamsRecord_PersonalAndDataCategory::class . $rand);
            $massive_action_params = ['container' => 'mass' . PluginDlteamsRecord_PersonalAndDataCategory::class . $rand,
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
            $header_top     .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsRecord_PersonalAndDataCategory::class . $rand);
            $header_bottom  .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsRecord_PersonalAndDataCategory::class . $rand);
            $header_end     .= "</th>";
         }

         $header_end .= "<th class='left'>" . __("Nom", 'dlteams') . "</th>";
         $header_end .= "<th class='left'>" . __("Type", 'dlteams') . "</th>";
		 $header_end .= "<th class='left'>" . __("Categorie de donnée", 'dlteams') . "</th>";
         $header_end .= "<th class='left'>" . __("Obligatoire", 'dlteams') . "</th>";
         // To Do
         /*$header_end .= "<th width='15%' class='center'>" . __("GDPR Sensitive", 'dlteams') . "</th>";*/
         //To Do
         $header_end .= "</tr>";

         echo $header_begin . $header_top . $header_end;
         // Prints the content
         foreach ($items_list as $data) {
            if($data['gpddname'] || $data['gpdpname']){
            echo "<tr class='tab_bg_1'>";

            if ($canedit && $number) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(PluginDlteamsRecord_PersonalAndDataCategory::class, $data['linkid']);
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
			echo "<td class='left'>" . ($data['gpddname']) . "</td>";
            echo "<td class='left'>" . ($data['mandatory']? __("Yes") : __("No")) . "</td>";
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
   
   static function showTab1(CommonDBTM $item) {
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

      $iterator = $DB->request(self::getRequest1($item));
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
            Html::openMassiveActionsForm('mass' . PluginDlteamsRecord_External::class . $rand);
            $massive_action_params = ['container' => 'mass' . PluginDlteamsRecord_External::class . $rand,
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
            $header_top     .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsRecord_External::class . $rand);
            $header_bottom  .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsRecord_External::class . $rand);
            $header_end     .= "</th>";
         }

         $header_end .= "<th class='left'>" . __("Nom", 'dlteams') . "</th>";
         $header_end .= "<th class='left'>" . __("Type", 'dlteams') . "</th>";
		 $header_end .= "<th class='left'>" . __("Motif d'envoi d'informations", 'dlteams') . "</th>";
        // $header_end .= "<th class='left'>" . __("Obligatoire", 'dlteams') . "</th>";
         // To Do
         /*$header_end .= "<th width='15%' class='center'>" . __("GDPR Sensitive", 'dlteams') . "</th>";*/
         //To Do
         $header_end .= "</tr>";

         echo $header_begin . $header_top . $header_end;
         // Prints the content
         foreach ($items_list as $data) {
            if($data['gppcname']){
            echo "<tr class='tab_bg_1'>";

            if ($canedit && $number) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(PluginDlteamsRecord_External::class, $data['linkid']);
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
            'glpi_plugin_dlteams_records_personalanddatacategories.id AS linkid',
			'glpi_plugin_dlteams_records.name AS nameRegistre',
			'glpi_plugin_dlteams_records.id AS idRecord',
            'glpi_plugin_dlteams_concernedpersons.id AS gpdpid',
            'glpi_plugin_dlteams_concernedpersons.name AS gpdpname',
            'glpi_plugin_dlteams_processeddatas.id AS gpddid',
            'glpi_plugin_dlteams_processeddatas.name AS gpddname',
            //'glpi_plugin_dlteams_personaldatacategories.is_special_category AS gpdpsensible',
            'glpi_plugin_dlteams_records_personalanddatacategories.mandatory AS mandatory',
            
         ],
         'FROM' => 'glpi_plugin_dlteams_records_personalanddatacategories',
         'LEFT JOIN' => [
            'glpi_plugin_dlteams_concernedpersons' => [         
               'FKEY' => [
                  'glpi_plugin_dlteams_records_personalanddatacategories' => "plugin_dlteams_concernedpersons_id",
                  'glpi_plugin_dlteams_concernedpersons' => "id",                 
               ]
         ], 
            'glpi_plugin_dlteams_processeddatas' => [                                                            
               'FKEY' => [
                  'glpi_plugin_dlteams_records_personalanddatacategories' => "plugin_dlteams_processeddatas_id",
                  'glpi_plugin_dlteams_processeddatas' => "id",
               
               ],
            ], 
            'glpi_plugin_dlteams_records' => [
               'FKEY' => [
                  'glpi_plugin_dlteams_records_personalanddatacategories' => "plugin_dlteams_records_id",
                  'glpi_plugin_dlteams_records' => "id",
               
               ],
            ]
         ],
		 'ORDER' => [                                         
		    'gpdpname ASC',                                               
            'gpddname ASC'
         ],
         /*'ORDER' => [
            'gpddname ASC',
            'gpdpname ASC'
         ],*/
         'WHERE' => [
            'glpi_plugin_dlteams_records_personalanddatacategories.plugin_dlteams_concernedpersons_id' => $record->fields['id']
         ]
      ];
   }
   
   static function getRequest1($record) {
     return [
         'SELECT' => [
            'glpi_plugin_dlteams_records_externals.id AS linkid',
			'glpi_plugin_dlteams_records.name AS nameRegistre',
			'glpi_plugin_dlteams_records.id AS idRecord',
            /*'glpi_plugin_dlteams_records_externals.recipient_reason AS recipient_reason',*/
			/*add by me**/
			'glpi_plugin_dlteams_sendingreasons.id AS id1',
            'glpi_plugin_dlteams_sendingreasons.name AS name1',
			/**add by me**/
            'glpi_plugin_dlteams_records_externals.recipient_comment AS recipient_comment',
            'glpi_plugin_dlteams_concernedpersons.id AS gppcid',
            'glpi_plugin_dlteams_concernedpersons.name AS gppcname',
            'glpi_plugin_dlteams_thirdpartycategories.id AS gptcid',
            'glpi_plugin_dlteams_thirdpartycategories.name AS gptcname',
            'glpi_suppliers.id AS gpsid',
            'glpi_suppliers.name AS gpsname',
         ],
         'FROM' => 'glpi_plugin_dlteams_records_externals',
         'LEFT JOIN' => [
            'glpi_plugin_dlteams_concernedpersons' => [
               'FKEY' => [
                  'glpi_plugin_dlteams_records_externals' => "plugin_dlteams_concernedpersons_id",
                  'glpi_plugin_dlteams_concernedpersons' => "id",
               ]
            ],
            'glpi_plugin_dlteams_thirdpartycategories' => [
               'FKEY' => [
                  'glpi_plugin_dlteams_records_externals' => "plugin_dlteams_thirdpartycategories_id",
                  'glpi_plugin_dlteams_thirdpartycategories' => "id",
               ],
            ],
			/**add by me**/
			'glpi_plugin_dlteams_sendingreasons' => [
               'FKEY' => [
                  'glpi_plugin_dlteams_records_externals' => "plugin_dlteams_sendingreasons_id",
                  'glpi_plugin_dlteams_sendingreasons' => "id",
               ],
            ],
			/**add by me**/
            'glpi_suppliers' => [
               'FKEY' => [
                  'glpi_plugin_dlteams_records_externals' => "suppliers_id",
                  'glpi_suppliers' => "id",
               ],
            ],
			'glpi_plugin_dlteams_records' => [
               'FKEY' => [
                  'glpi_plugin_dlteams_records_externals' => "plugin_dlteams_records_id",
                  'glpi_plugin_dlteams_records' => "id",
               
               ],
            ]
         ],
         'WHERE' => [
            'glpi_plugin_dlteams_records_externals.plugin_dlteams_concernedpersons_id' => $record->fields['id']
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
         $count += $dbu->countElementsInTable('glpi_plugin_dlteams_records_personalanddatacategories', ['plugin_dlteams_concernedpersons_id' => $item->getID()]);
      return $count;
   }

}