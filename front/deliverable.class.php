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

class PluginDlteamsDeliverable extends CommonDBTM                                                                  
   implements PluginDlteamsExportableInterface{
   static $rightname = 'plugin_dlteams_deliverable';
   public $dohistory = true;
   protected $usenotepad = true;

   static function getTypeName($nb = 0) {
      return _n("Delivrable", "Delivrables", $nb, 'dlteams');
   }

   function showForm($id, $options = []) {

      global $CFG_GLPI;

      $this->initForm($id, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'><td>".__('Name')."</td>";
         echo "<td>";
            Html::autocompletionTextField($this, "name");
         echo "</td>";

         echo "<td rowspan='1'>". __('Comments')."</td>";
         echo "<td rowspan='1'>
               <textarea cols='45' rows='2' name='comment' >".$this->fields["comment"];
         echo "</textarea></td>";

      echo "</tr>\n";

      $this->showFormButtons($options);

      return true;
   }


   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => __("Characteristics")
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __("Name"),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __("ID"),
         'massiveaction'      => false,
         'datatype'           => 'number',
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => $this->getTable(),
         'field'              => 'type',
         'name'               => __("Type", 'dlteams'),
         'searchtype'         => 'equals',
         'massiveaction'      => true,
         'datatype'           => 'specific'
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'content',
         'name'               => __("Content"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __("Comments"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __("Entity"),
         'massiveaction'      => true,
         'datatype'           => 'dropdown',
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __("Child entities"),
         'massiveaction'      => false,
         'datatype'           => 'bool',
      ];

      return $tab;
   }

   public function defineTabs($options = [])
   {
      $ong = [];
      $ong = array();
      $this->addDefaultFormTab($ong)
      ->addStandardTab('PluginDlteamsObject_document', $ong, $options)
      ->addStandardTab('ManualLink', $ong, $options)	  
	  ->addStandardTab('PluginDlteamsObject_allitem', $ong, $options)
          ->addStandardTab(PluginDlteamsTicket_Item::class, $ong, $options)
	  ->addStandardTab('KnowbaseItem_Item', $ong, $options)
      ->addImpactTab($ong, $options)
      ->addStandardTab('Notepad', $ong, $options)
      ->addStandardTab('Log', $ong, $options);
      return $ong;
   }
   
   function exportToDB($subItems = [])
   {
      if ($this->isNewItem()) {
         return false;
      }

      $export = $this->fields;
      return $export;
   }
   
    public static function importToDB(PluginDlteamsLinker $linker, $input = [], $containerId = 0, $subItems = [])
   {
      $item = new self();
      $originalId = $input['id'];
      unset($input['id']);
	  $input['entities_id']= $_POST['entities_id'];;
	  $input['comment']=str_replace(['\'', '"'], "", $input['comment']);
	  $input['name']=str_replace(['\'', '"'], "", $input['name']);
	  $input['content']=str_replace(['\'', '"'], "", $input['content']);
      $itemId = $item->add($input);
      if ($itemId === false) {
         $typeName = strtolower(self::getTypeName());
         throw new ImportFailureException(sprintf(__('failed to copy the %1$s record', 'dlteams'), $input['name']));
      }
      return $itemId;
   }
   
    public function deleteObsoleteItems(CommonDBTM $container, array $exclude)
   {
   }

   public static function showMassiveActionsSubForm(MassiveAction $ma)
   {
      switch ($ma->getAction()) {
         case 'copyTo':
            Entity::dropdown([
               'name' => 'entities_id',
            ]);
            echo '<br /><br />' . Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
            return true;
      }
      return parent::showMassiveActionsSubForm($ma);
   }
   
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
   {
      switch ($ma->getAction()) {
         case 'copyTo':
            if ($item->getType() == 'PluginDlteamsDeliverable') {
               foreach ($ids as $id) {
                  if ($item->getFromDB($id)) {
                     if ($item->copy1($ma->POST['entities_id'], $id, $item)) {
						Session::addMessageAfterRedirect(sprintf(__('Record copied: %s', 'dlteams'), $item->getName()));
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                     }
                  } else {
                     // Example of ko count
                     $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                  }
               }
            }
            return;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids); 
   }
   
   public function copy1($entity, $id, $item){                                                        
	  global $DB;
	  $dbu = new DbUtils();
	  $name=addslashes(str_replace('"', '',$item->fields['name']));
	  $entities_ori=$item->fields['entities_id'];
	  $id_ori=$item->fields['id'];
      $nb=$dbu->countElementsInTable(static::getTable(), ['name' => addslashes($name), 'entities_id' => $entity]);
	 // var_dump($nb);
	  
	  if($nb<=0){
		 // var_dump($id_ori);
		  $DB->request("INSERT INTO ".static::getTable()." (is_deleted, entities_id, is_recursive, name, content, comment, date_mod, date_creation) SELECT is_deleted, '$entity', is_recursive, '$name', content, comment, date_mod, date_creation FROM ".static::getTable()." WHERE id='$id_ori'");
		  //get Id of copy record
		  $reqC=$DB->request("SELECT id FROM glpi_plugin_dlteams_deliverables WHERE name = '$name' AND entities_id = '$entity'");
			foreach ($reqC as $id => $row) {				
				$idRecord=$row['id']; //get id of copied record
				//var_dump($idRecord);
				echo "<br/>";
			}
			
			// var_dump($id_ori);
		  //get Id of copy record
		  /**copy the document**/
		  $req=$DB->request("SELECT * FROM glpi_documents_items WHERE items_id='$id_ori' AND itemtype='PluginDlteamsDeliverable' and entities_id='$entities_ori'"); 
		  //var_dump(count($req));
			if (count($req)) {
				
				foreach ($req as $id => $row) { 
				//
					$val0=$row['documents_id']; //get documents_id
					//var_dump($val0);
					echo "<br/>";
				//
				
				/**insert in document table***/
				$DB->request("INSERT INTO glpi_documents (entities_id,is_recursive,name,filename,filepath,documentcategories_id,mime,date_mod,comment,is_deleted,link,users_id,tickets_id,sha1sum,is_blacklisted,tag,date_creation) SELECT '$entity',is_recursive,name,filename,filepath,documentcategories_id,mime,date_mod,comment,is_deleted,link,users_id,tickets_id,sha1sum,is_blacklisted,tag,date_creation FROM glpi_documents WHERE id='$val0' and entities_id='$entities_ori'");
				/**insert in document table***/
				
				/**get ID of record copied***/
				$reqD=$DB->request("SELECT * FROM glpi_documents WHERE id='$val0'");
				foreach ($reqD as $id => $row) {				
					$val1=addslashes(str_replace('"', '', $row['name'])); 
					//var_dump($val1);
					echo "<br/>";
					
				}
				/**get ID of record copied***/
				
				$reqF=$DB->request("SELECT * FROM glpi_documents WHERE name='$val1' and entities_id='$entity'");
				foreach ($reqF as $id => $row) {				
					$val2=$row['id']; 
					//var_dump($val2);
					echo "<br/>";
					
				}
				
				
						/**insert in document_items table***/
						$DB->request("INSERT INTO glpi_documents_items (documents_id,items_id,itemtype,entities_id) SELECT '$val2','$idRecord','PluginDlteamsDeliverable','$entity' FROM glpi_documents_items WHERE documents_id='$val0' AND items_id='$id_ori' and itemtype='PluginDlteamsDeliverable' and entities_id='$entities_ori'");
						/**insert in document_items table***/
					
				
				}
			}else{
				
			}
		  /**copy the document**/
		  return true;
	  }else{
		  return false;
	  }
   }   
   
   
}
