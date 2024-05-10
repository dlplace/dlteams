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

class PluginDlteamsRecord_Storage extends CommonDBRelation implements
PluginDlteamsExportableInterface {
   static public $itemtype_1 = 'PluginDlteamsRecord';
   static public $items_id_1 = 'plugin_dlteams_records_id';
   static public $itemtype_2 = 'PluginDlteamsStorageType';
   static public $items_id_2 = 'plugin_dlteams_storagetypes_id';

   static function getTypeName($nb = 0) {

      return _n("Durée de conservation2", "Durée de conservation5", $nb, 'dlteams');
   }

   static function countForItem(CommonDBTM $item) {
      /*$dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_dlteams_allitems', ['items_id1' => $item->getID(), 'itemtype1' => 'PluginDlteamsRecord','itemtype2' => 'PluginDlteamsLegalbasi']);*/
	  $dbu = new DbUtils();
      return $dbu->countElementsInTable('glpi_plugin_dlteams_records_storages', ['plugin_dlteams_records_id' => $item->getID()]);
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (!$item->canView()) {
         return false;
      }

      switch ($item->getType()) {
         case PluginDlteamsRecord_Storage::class :
			$nb=0;
			//$dbu = new DbUtils();
			//$nb = $dbu->countElementsInTable('glpi_plugin_dlteams_allitems', ['items_id1' => '593', 'itemtype1' => 'PluginDlteamsRecord','itemtype2' => 'PluginDlteamsLegalbasi']);
               $nb = self::countForItem($item);
            return static::createTabEntry(PluginDlteamsRecord_Storage::getTypeName($nb), $nb);
      }

      return '';
   }

   //Edit Massive Actions
   // === Massive actions stuff ===

   function getSpecificMassiveActions($checkitem=NULL) {
      $actions = parent::getSpecificMassiveActions($checkitem);

      // add a single massive action
      /*$class        = __CLASS__;
      $action_key   = "name";
      $action_label = __("Modifier Stockage", 'dlteams');
      $actions[$class.MassiveAction::CLASS_ACTION_SEPARATOR.$action_key] = $action_label;

      $action_key   = "gpdpname";
      $action_label = __("Modifier Conservation Time", 'dlteams');
      $actions[$class.MassiveAction::CLASS_ACTION_SEPARATOR.$action_key] = $action_label;

      $action_key   = "storage_comment";
      $action_label = __("Modifier Commentaire", 'dlteams');
      $actions[$class.MassiveAction::CLASS_ACTION_SEPARATOR.$action_key] = $action_label;

      $action_key   = "storage_action";
      $action_label = __("Modifier Action en fn de période", 'dlteams');
      $actions[$class.MassiveAction::CLASS_ACTION_SEPARATOR.$action_key] = $action_label;*/
      return $actions;
   }

   static function showMassiveActionsSubForm(MassiveAction $ma) {
      /*switch ($ma->getAction()) {
         case 'name':
             echo "<table class='tab_cadre_fixe'><tr><td width='20%' class='right'>";
            echo _n("Name", "Name", 0, 'dlteams');
            echo "</td><td width='30%' class='left'>";
            PluginDlteamsStorage::dropdown([
               'addicon'  => PluginDlteamsStorage::canCreate(),
               'name' => 'plugin_dlteams_storagetypes_id',
               'width' => '60%'
            ]);
            echo "</td></tr></table>";
            break;
         case 'gpdpname':
            echo "<table class='tab_cadre_fixe'><tr><td width='20%' class='right'>";
            echo _n("Duree de conservation", "Duree de conservation", 0, 'dlteams');
            echo "</td><td width='30%' class='left'>";
            PluginGenericobjectRgpdconservation::dropdown([
            'addicon'  => PluginGenericobjectRgpdconservation::canCreate(),
            'name' => 'plugin_genericobject_rgpdconservations_id',
            'width' => '60%'
            ]);
            echo "</td></tr></table>";
            break;

         case 'storage_comment':
            echo "<table class='tab_cadre_fixe'><tr><td width='20%' class='right'>";
            echo _n("Commentaire", "Commentaire", 0, 'dlteams');
            echo "</td><td width='30%' class='left'>";
            echo "<textarea type='text' style='width:75%' maxlength=1000 rows='3' name='storage_comment'></textarea>";
            echo "</td></tr></table>";
            break;
         case 'storage_action':
		 /**add by me**/
			/*echo "<table class='tab_cadre_fixe'><tr><td width='20%' class='right'>";
            echo _n("Action Fin Periode", "Action en fin de periode", 0, 'dlteams');
            echo "</td><td width='30%' class='left'>";
            PluginDlteamsActionFinPeriode::dropdown([
               'addicon'  => PluginDlteamsActionFinPeriode::canCreate(),
               'name' => 'plugin_dlteams_storageendactions_id',
               'width' => '60%'
            ]);
            echo "</td></tr></table>";*/
		 /**add by me**/
         /*$choices = [
            __("Suppression", 'dlteams'),
            __("Archiving", 'dlteams'),
            __("Anonymisation", 'dlteams'),
            __("Restitution", 'dlteams'),
            "<input type='text' onfocus='checkRadio()' placeholder='" . __("Other") .
               "' name='storage_action_other' value=''>",
         ];
         // Display radio checkboxes
         foreach ($choices as $id => $choice) {
            if ($id == 4)
               echo "<input type='radio' id='$id' name='storage_action_other' value='other'><label for='$id'>  $choice</label><br>";
            else
               echo "<input type='radio' id='$id' name='storage_action' value='$choice'><label for='$id'>  $choice</label><br>";
         }

         // JS for auto check when click on "other" text input
         echo "<script>function checkRadio() {document.getElementById($id).checked = true;}</script>";*/


      //}

      return parent::showMassiveActionsSubForm($ma);
   }

   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      /*switch ($ma->getAction()) {
         case 'name':
            $input = $ma->getInput();

            foreach ($ids as $id) {

               if ($item->getFromDB($id)
                  && $item->toggleName($input)) {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                  $ma->addMessage(__("Something went wrong"));
               }
            }
            return;
         case 'gpdpname':
            $input = $ma->getInput();

            foreach ($ids as $id) {

               if ($item->getFromDB($id) && $item->toggleDureeConservation($input)) {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                  $ma->addMessage(__("Something went wrong"));
               }
            }
            return;
            case 'storage_comment':
            $input = $ma->getInput();

            foreach ($ids as $id) {

               if ($item->getFromDB($id) && $item->toggleComment($input)) {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                  $ma->addMessage(__("Something went wrong"));
               }
            }
            return;
            case 'storage_action':
            $input = $ma->getInput();

            foreach ($ids as $id) {

               if ($item->getFromDB($id) && $item->toggleAction($input)) {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
               } else {
                  $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                  $ma->addMessage(__("Something went wrong"));
               }
            }

            return;
      }*/

      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }

   /*protected function toggleName($input) {
      $this->fields['plugin_dlteams_storagetypes_id'] = $input['plugin_dlteams_storagetypes_id'];
      return $this->updateInDB(['plugin_dlteams_storagetypes_id']);
   }

   protected function toggleDureeConservation($input) {
      $this->fields['plugin_genericobject_rgpdconservations_id'] = $input['plugin_genericobject_rgpdconservations_id'];
      return $this->updateInDB([
         'plugin_genericobject_rgpdconservations_id',
      ]);
   }
   protected function toggleComment($input) {
      $this->fields['storage_comment'] = $input['storage_comment'];
      return $this->updateInDB([
         'storage_comment',
      ]);
   }*/
  /* protected function toggleAction($input) {
	   /**add by me**/

	    /*global $DB;

		$req=$DB->request(['SELECT' => 'name', 'FROM' => 'glpi_plugin_dlteams_storageendactions', 'WHERE' => ['id' => $input['plugin_dlteams_storageendactions_id']]]);
		if ($row = $req->next()) {
				$storageAction=$row['name'];

		}
		$this->fields['storage_action']=$storageAction;
		$this->updateInDB(['storage_action']);

	   /**add by me**/
	   /*$this->fields['plugin_dlteams_storageendactions_id'] = $input['plugin_dlteams_storageendactions_id'];
       return $this->updateInDB(['plugin_dlteams_storageendactions_id']);
      /*if(array_key_exists('storage_action',$input)){
      $this->fields['storage_action'] = $input['storage_action'];
      return $this->updateInDB([
         'storage_action',
      ]);
   }
      else if(array_key_exists('storage_action_other',$input)){
      $this->fields['storage_action'] = $input['storage_action_other'];
      return $this->updateInDB([
         'storage_action',
      ]);
      }*/
  // }


   //Edit Massive Actions
   /**
    * Export in an array all the data of the current instanciated PluginDlteamsRecord_PersonalAndDataCategory
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
    public function exportToDB($remove_uuid = false, $subItems = []) {
      if ($this->isNewItem()) {
         return false;
      }

      $storage = $this->fields;
      return $storage;
   }

   public static function importToDB(PluginDlteamsLinker $linker, $input = [], $record_id = 0, $subItems = []) {
      global $DB;

      $recordFk = PluginDlteamsRecord::getForeignKeyField();
      $input[$recordFk] = $record_id;

      $item = new self();
      $originalId = $input['id'];
      unset($input['id']);
      foreach (['storage_comment'] as $key) {
         $input[$key] = $DB->escape($input[$key]);
      }

      $itemId = $item->add($input);
      if ($itemId === false) {
         $typeName = strtolower(self::getTypeName());
         throw new ImportFailureException(sprintf(__('failed to copy the %1$s record', 'dlteams'), $originalId));
      }
      $linker->addObject($originalId, $item);
      return $itemId;
   }

   public function deleteObsoleteItems(CommonDBTM $container, array $exclude) {}

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      switch ($item->getType()) {
         case PluginDlteamsRecord::class :
            self::showForRecord($item, $withtemplate);
            break;
      }

      return true;
   }

   static function showForRecord(PluginDlteamsRecord $record, $withtemplate = 0) {
      global $DB;
      $id = $record->fields['id'];
      if (!$record->can($id, READ)) {
         return false;
      }

      $canedit = $record->can($id, UPDATE);
      $rand = mt_rand(1, mt_getrandmax());

      // Request that joins 3 table (not possible to do with CommonDBRelation methods)
      // Result is used lower to display a table

      $iterator = $DB->request(self::getRequest($record));

//	  var_dump($iterator );
/*       highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//	  die();
      $number = count($iterator);

      $items_list = [];
      $used = [];
	   foreach ($iterator as $id => $data){
      //while ($data = $iterator->next()) {
         $items_list[$data['linkid']] = $data;
         $used[$data['linkid']] = $data['linkid'];
      }


	  /***form new**/
	  if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";
		$iden=$record->fields['id'];
         echo "<input type='hidden' name='plugin_dlteams_records_id' value='$iden' />";
          echo "<input type='hidden' name='itemtype1' value='".$record->getType()."' />";
          echo "<input type='hidden' name='items_id1' value='".$record->getID()."' />";
          echo "<input type='hidden' name='itemtype' value='".PluginDlteamsStoragePeriod::getType()."' />";

         echo "<table class='tab_cadre_fixe'>";
			echo "<tr class=''><th colspan='3'>" . __("Conservation time", 'dlteams') .
			"<br><i style='font-weight: normal'>" .
			__("In application of legal basis, what are conservation times of the data retained", 'dlteams') .
			"</i></th>";
			echo "</tr>";
         /*echo "<th colspan='2'></th></tr>";*/
		 /**add by me**/
			echo "<tr class='tab_bg_1'><td class='' colspan='1'>";
			echo __("Specify the retention periods and the future of the personal data concerned by this processing", 'dlteams');
			echo "<br/><br/>";
				PluginDlteamsStoragePeriod::dropdown([
				'addicon'  => PluginDlteamsStoragePeriod::canCreate(),
				'name' => 'plugin_dlteams_storageperiods_id',
				'width' => '200px'
			]);
			echo "</td>";

			echo "<td width='' colspan='1'>";
			echo "<span style='display:none' id='td1'>";
			echo __("Stockage", 'dlteams');
			echo "<br/><br/>";
				PluginDlteamsStorageType::dropdown([
				'addicon'  => PluginDlteamsStorageType::canCreate(),
				'name' => 'plugin_dlteams_storagetypes_id',
				'width' => '250px',
			]);
			echo "</span>";
			echo "</td>";

			echo "<td width='' colspan='2'>";
			echo "<span style='display:none;float:left' id='td2'>";
			echo __("Action Fin Periode", 'dlteams');
			echo "<br/><br/>";
				PluginDlteamsStorageEndAction::dropdown([
				'addicon'  => PluginDlteamsStorageEndAction::canCreate(),
				'name' => 'plugin_dlteams_storageendactions_id',
				'width' => '200px',
			]);
			echo "</span>";
			echo "<span style='display:none;float:left;margin-left:10px;margin-top:5px' id='td4'>";
			echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin-top:35px'>";
			echo "</span>";
			echo "</td>";
		 /**add by me**/
		 /*echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
         echo __("Action at the end of the period", 'dlteams');
         echo "</td><td width='50%' class='left'>";

         $choices = [
            __("Suppression", 'dlteams'),
            __("Archiving", 'dlteams'),
            __("Anonymisation", 'dlteams'),
            __("Restitution", 'dlteams'),
            "<input type='text' onfocus='checkRadio()' placeholder='" . __("Other") .
               "' name='storage_action_other' value=''>",
         ];*/

         // Display radio checkboxes
         /*foreach ($choices as $id => $choice) {
            if ($id == 4)
               echo "<input type='radio' id='$id' name='storage_action' value='other'><label for='$id'>  $choice</label><br>";
            else
               echo "<input type='radio' id='$id' name='storage_action' value='$choice'><label for='$id'>  $choice</label><br>";
         }*/

         // JS for auto check when click on "other" text input
         //echo "<script>function checkRadio() {document.getElementById($id).checked = true;}</script>";

		 /**add by me**/
         /*echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
         echo __("Stockage", 'dlteams');
         echo "</td><td width='50%' class='left'>";
         PluginDlteamsStorage::dropdown([
            'addicon'  => PluginDlteamsStorage::canCreate(),
            'name' => 'plugin_dlteams_storagetypes_id',
            'width' => '40%',
         ]);
         echo "</td></tr>";
         echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
         echo __("Conservation time", 'dlteams');
         echo "</td><td width='50%' class='left'>";
         PluginGenericobjectRgpdconservation::dropdown([
            'addicon'  => PluginGenericobjectRgpdconservation::canCreate(),
            'name' => 'plugin_genericobject_rgpdconservations_id',
            'width' => '40%',
         ]);
         echo "</td></tr>";
         echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
         echo __("Comment");
         echo "</td><td width='50%' class='left'>";
         echo "<textarea type='text' style='width:75%' maxlength=1000 rows='3' name='storage_comment'></textarea>";
         echo "</td></tr>";
         echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
         echo __("Action at the end of the period", 'dlteams');
         echo "</td><td width='50%' class='left'>";

         $choices = [
            __("Suppression", 'dlteams'),
            __("Archiving", 'dlteams'),
            __("Anonymisation", 'dlteams'),
            __("Restitution", 'dlteams'),
            "<input type='text' onfocus='checkRadio()' placeholder='" . __("Other") .
               "' name='storage_action_other' value=''>",
         ];

         // Display radio checkboxes
         foreach ($choices as $id => $choice) {
            if ($id == 4)
               echo "<input type='radio' id='$id' name='storage_action' value='other'><label for='$id'>  $choice</label><br>";
            else
               echo "<input type='radio' id='$id' name='storage_action' value='$choice'><label for='$id'>  $choice</label><br>";
         }

         // JS for auto check when click on "other" text input
         echo "<script>function checkRadio() {document.getElementById($id).checked = true;}</script>";
         echo "</td></tr>";*/

			/*echo "<td colspan='20%' class='center'>";
			echo "<span style='display:none' id='td4'>";
			echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin-top:35px'>";
			echo "</span>";
			echo "</td></tr>";*/



			/*echo "<td colspan='20%' class='center'>";
			echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin-top:20px'>";
			echo "</td></tr>";*/
         echo "</table>";
		 echo "<span style='display:none;float:right;width:100%;' id='td3'>";
			//echo __("Comment");
			echo "<br/><br/>";
			echo "<textarea type='text' style='width:400px;float:right;margin-right:24.5%' maxlength=1000 rows='3' name='storage_comment' class='storage_comment1' placeholder='commentaire'></textarea>";
			echo "</span>";
         Html::closeForm();
         echo "</div>";
      }

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

      $header_end .= "<th>" . __("Durée de conservation") . "</th>";
      $header_end .= "<th>" . __("Stockage") . "</th>";

	  $header_end .= "<th>" . __("En fin de période") . "</th>";
	  $header_end .= "<th>" . __("Comment") . "</th>";

      /*$header_end .= "<th>" . __("Comment") . "</th>";
      $header_end .= "<th>" . __("En fin de période") . "</th>";*/
      $header_end .= "</tr>";

      echo $header_begin . $header_top . $header_end;


      foreach ($items_list as $data) {
/*          highlight_string("<?php\n\$data =\n" . var_export($data, true) . ";\n?>");*/
//          die();
         echo "<tr class='tab_bg_1'>";

         if ($canedit && $number) {
            echo "<td width='10'>";
//var_dump ($data['linkid']);
            Html::showMassiveActionCheckBox(__class__, $data['linkid']);
            echo "</td>";
         }
         foreach ([
               "" => 'PluginDlteamsStoragePeriod',
               ] as $table => $class) {
               $link = $data[$table.'gpdpname'];

               if ($_SESSION['glpiis_ids_visible'] || empty($data[$table.'gpdpname'])) {
                  $link = sprintf(__("%1\$s (%2\$s)"), $link, $data[$table.'id']);
               }

            }
         $name = "<a target='_blank' href=\"" . $class::getFormURLWithID($data[$table.'gpdpid']) . "\">" . $link . "</a>";

         $link2 = $data['name'];
         if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
            $link2 = sprintf(__("%1\$s (%2\$s)"), $link2, $data['id']);
         }

		 $link3 = $data['name1'];
         if ($_SESSION['glpiis_ids_visible'] || empty($data['name1'])) {
            $link3 = sprintf(__("%1\$s (%2\$s)"), $link3, $data['id1']);
         }
		 $name3 = "<a target='_blank' href=\"" . PluginDlteamsStorageEndAction::getFormURLWithID($data['id1']) . "\">" . $link3 . "</a>";
         $name2 = "<a target='_blank' href=\"" . PluginDlteamsStorageType::getFormURLWithID($data['id']) . "\">" . $link2 . "</a>";
         echo "<td class='left'>" . $name . "</td>";
         echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
         echo ">" . $name2 . "</td>";
         /*echo "<td class='left'>" . ($data['storage_comment']?? "") . "</td>";
         echo "<td class='left'>" . $data['storage_action'] . "</td>";*/
		 echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
         echo ">" . $name3 . "</td>";
		 //echo "<td class='left'>" . $data['storage_action'] . "</td>";
		 echo "<td class='left'>" . ($data['storage_comment']?? "") . "</td>";
         echo "</tr>";
      }

      if ($iterator->count() > 25) {
         echo $header_begin . $header_bottom . $header_end;
      }
      echo "</table>";

      if ($canedit && $number) {
         $massive_action_params['ontop'] = false;
               if ($iterator->count() > 25) { Html::showMassiveActions($massive_action_params);}
         Html::closeForm();
      }
      echo "</div>";
   }

   static function getRequest($record) {
//      return [
//         'SELECT' => [
//            'glpi_plugin_dlteams_records_storages.id AS linkid',
//            'glpi_plugin_dlteams_records_storages.storage_comment AS storage_comment',
//            'glpi_plugin_dlteams_records_storages.storage_action AS storage_action',
//            'glpi_plugin_dlteams_storagetypes.id AS id',
//            'glpi_plugin_dlteams_storagetypes.name AS name',
//			   'glpi_plugin_dlteams_storageendactions.id AS id1',
//            'glpi_plugin_dlteams_storageendactions.name AS name1',
//            'glpi_plugin_dlteams_storageperiods.id AS gpdpid',
//            'glpi_plugin_dlteams_storageperiods.name AS gpdpname',
//         ],
//         'FROM' => 'glpi_plugin_dlteams_records_storages',
//         'INNER JOIN' => [
//            'glpi_plugin_dlteams_storagetypes' => [
//               'FKEY' => [
//                  'glpi_plugin_dlteams_records_storages' => "plugin_dlteams_storagetypes_id",
//                  'glpi_plugin_dlteams_storagetypes' => "id",
//               ]
//            ],
//			'glpi_plugin_dlteams_storageendactions' => [
//               'FKEY' => [
//                  'glpi_plugin_dlteams_records_storages' => "plugin_dlteams_storageendactions_id",
//                  'glpi_plugin_dlteams_storageendactions' => "id",
//               ]
//            ]
//         ],
//
//         'JOIN' => [
//            'glpi_plugin_dlteams_storageperiods' => [
//               'FKEY' => [
//                  'glpi_plugin_dlteams_records_storages' => "plugin_dlteams_storageperiods_id",
//                  'glpi_plugin_dlteams_storageperiods' => "id",
//               ]
//            ]
//         ],
//         'ORDER' => [
//            'name ASC',
//            'gpdpname ASC'
//         ],
//         'WHERE' => [
//            'glpi_plugin_dlteams_records_storages.plugin_dlteams_records_id' => $record->fields['id']
//         ]
//      ];

       return [
           'SELECT' => [
               'glpi_plugin_dlteams_storageperiods_items.id AS linkid',
               'glpi_plugin_dlteams_storageperiods.id AS id',
               'glpi_plugin_dlteams_storageperiods.name AS name',
               'glpi_plugin_dlteams_storageperiods.content AS content',
               'glpi_plugin_dlteams_storageperiods_items.comment AS comment',
           ],
           'FROM' => 'glpi_plugin_dlteams_storageperiods_items',
           'LEFT JOIN' => [
               'glpi_plugin_dlteams_storageperiods' => [
                   'FKEY' => [
                       'glpi_plugin_dlteams_storageperiods_items' => "storageperiods_id",
                       'glpi_plugin_dlteams_storageperiods' => "id",
                   ]
               ],
           ],
           //'ORDER' => [ 'typename DESC', 'name ASC'],
           'ORDER' => ['glpi_plugin_dlteams_storageperiods_items.id ASC', 'name ASC'],
           'WHERE' => [
               'glpi_plugin_dlteams_storageperiods_items.items_id' => $record->fields['id'],
               'glpi_plugin_dlteams_storageperiods_items.itemtype' => 'PluginDlteamsRecord'
               //'glpi_plugin_dlteams_allitems.plugin_dlteams_legalbasis_id' => "PluginDlteamsLegalbasi"
               /*'glpi_plugin_dlteams_allitems.items_id2' => $record->fields['id'],
               'glpi_plugin_dlteams_allitems.itemtype2' => $record->getType(),
               'glpi_plugin_dlteams_allitems.itemtype1' => PluginDlteamsLegalbasi::getType(),*/
           ]
       ];
   }

   static function getListForItem(CommonDBTM $item) {
      global $DB;
      $params = static::getListForItemParams($item, true);
      $params['SELECT'][] = "glpi_plugin_dlteams_records_storages.storage_action AS storage_action";
      $params['SELECT'][] = "glpi_plugin_dlteams_records_storages.storage_comment AS storage_comment";
      $iterator = $DB->request($params);
      return $iterator;
   }

   /*function getForbiddenStandardMassiveAction() {
      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
   }*/

   /*static function rawSearchOptionsToAdd() {
   }*/

   function rawSearchOptions() {

      $tab = [];

      /*$tab[] = [
         'id' => 'datasubjectscategory',
         'name' => PluginGenericobjectPersonnesconcernee::getTypeName(0)
      ];*/

      $tab[] = [
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
      ];

	  $tab[] = [
         'id' => '43',
         'table' => PluginDlteamsStorageType::getTable(),
         'field' => 'name',
         'name' => __("Type de stockage"),
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
         'id' => '42',
         'table' => PluginDlteamsStorageEndAction::getTable(),
         'field' => 'name',
         'name' => __("Action fin periode"),
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
		   'id'            => '44',
		   'table'         => PluginDlteamsRecord_Storage::getTable(),
		   'field'         => 'storage_comment',
		   'name'          => __('Commentaire'),
		   'datatype'      => 'text',
		   'massiveaction' => true // <- NO MASSIVE ACTION
		];

      return $tab;
   }
}

?>
<style>
#td1{
	display:none;
}
</style>
<script>
$(document).ready(function(){
    $('select[name=plugin_dlteams_storageperiods_id]').on('change',function(){
		//alert($(this).val());
		//
		if($(this).val()!='0'){
			document.getElementById("td1").style.display = "block";
			document.getElementById("td2").style.display = "block";
			document.getElementById("td3").style.display = "block";
			document.getElementById("td4").style.display = "block";
			var content = $(this).val();
				// $.ajax({
				// 	url : "getComment.php",
				// 	type: 'POST',
				// 	async : false,
				// 	data : { content : content},
				// 	success : function(data) {
				// 		//alert(data);
				// 		//userData = json.parse(data);
				// 		//alert(json.parse(data));
				// 	  $('.storage_comment1').val(data);
				// 	  //alert($("#email").val());
				// 	}
				// });


            $.ajax({
                url: '/marketplace/dlteams/ajax/get_object_specific_field.php',
                type: 'POST',
                data: {
                    id: $(this).val(),
                    object: 'PluginDlteamsStoragePeriod',
                    field: 'content'
                },
                success: function (data) {
                    // Handle the returned data here
                    console.log(data);
                    // ne marche que si on a 1 seul editeur tinyMCE sur la page
                    tinyMCE.activeEditor.setContent(data);
                    tinyMCE.triggerSave();
                }
            });
		}else{
			document.getElementById("td1").style.display = "none";
			document.getElementById("td2").style.display = "none";
			document.getElementById("td3").style.display = "none";
			document.getElementById("td4").style.display = "none";
		}
		//
    });
});
</script>

<!--script>
$(document).ready(function(){
    $('select[name=plugin_dlteams_storageperiods_id]').on('change',function(){
		//alert($(this).val());
        var content = $(this).val();
        $.ajax({
            url : "getComment.php",
            type: 'POST',
            async : false,
            data : { content : content},
            success : function(data) {
				//alert(data);
                //userData = json.parse(data);
				//alert(json.parse(data));
              $('.storage_comment1').val(data);
			  //alert($("#email").val());
            }
        });
    });
});
</script-->
