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

//class PluginDlteamsConcernedPerson extends CommonDBTM implements
class PluginDlteamsConcernedPerson extends CommonDropdown implements
   PluginDlteamsExportableInterface{
   use PluginDlteamsExportable;
   static $rightname = 'plugin_dlteams_concernedperson';
   public $dohistory = true;
   protected $usenotepad = true;

   static function getTypeName($nb = 0) {
      return _n("Concerned person", "Concerned persons", $nb, 'dlteams');
   }

    function showForm($id, $options = [])
   {
      global $CFG_GLPI;

      $this->initForm($id, $options);
      $this->showFormHeader($options);

	echo "<table, th, td width='100%'>";
	  echo "<tr>";
		echo "<td width='15%' style='text-align:right'>". " " . "</td>";
		echo "<td width='15%' style='text-align:right' >". __("Name", 'dlteams') . "</td>";
		echo "<td>";
		$name = Html::cleanInputText($this->fields['name']);
		echo "<input type='text' style='width:98%' name='name' required value='" . $name. "'>" . "</td>";
		echo "<td width='15%' style='text-align:right'>". " " . "</td>";
	  echo "</tr>" ;

	  echo "<tr>";
      echo "<td width='15%' style='text-align:right'>". " " . "</td>";
	  echo "<td width='15%' style='text-align:right'>" . __("Content", 'dlteams') . "</td>";
	  echo "<td>";
      $content = Html::cleanInputText($this->fields['content']);
      echo "<textarea style='width: 98%;' name='content' rows='3'>" . $content . "</textarea>";
      echo "</td></tr>";

      echo "<tr>";
      echo "<td width='15%' style='text-align:right'>". " " . "</td>";
	  echo "<td width='15%' style='text-align:right'>" . __("Comment", 'dlteams') . "</td>";
      echo "<td>";
	  $comment = Html::cleanInputText($this->fields['comment']);
      echo "<textarea style='width: 98%;' name='comment' rows='3'>" . $comment . "</textarea>";
      echo "</td></tr>";
    echo "</table>";

   $this->showFormButtons($options);

   }


   function prepareInputForAdd($input) {
      $input['users_id_creator'] = Session::getLoginUserID();
      return parent::prepareInputForAdd($input);
   }

   function prepareInputForUpdate($input) {
      $input['users_id_lastupdater'] = Session::getLoginUserID();
      return parent::prepareInputForUpdate($input);
   }


   public function getSpecificMassiveActions($checkitem = null)
    {
        global $DB;

        $actions = [];
		$actions['Appliance' . MassiveAction::CLASS_ACTION_SEPARATOR . 'add_item'] =
                "<i class='fa-fw " . Appliance::getIcon() . "'></i>" . _x('button', 'Associate to an appliance');

        return $actions;
    }


   function cleanDBonPurge() {
      /*$rel = new PluginDlteamsRecord_MotifEnvoi();
      $rel->deleteByCriteria(['plugin_dlteams_concernedpersons_id' => $this->fields['id']]);*/
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
         'field'              => 'comment',
         'name'               => __("Comments"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __("Entity"),
         'datatype'           => 'dropdown',
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __("Child entities"),
         'datatype'           => 'bool',
         'massiveaction'      => false,
      ];

	  $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'content',
         'name'               => __("Content"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

      return $tab;
   }

   public function defineTabs($options = [])
   {
      $ong = [];
      $ong = array();
      //add main tab for current object
      $this->addDefaultFormTab($ong)
		->addImpactTab($ong, $options)
		->addStandardTab('PluginDlteamsRecord_Item', $ong, $options)
		->addStandardTab('PluginDlteamsConcernedPerson_Item', $ong, $options)
		->addStandardTab('PluginDlteamsObject_document', $ong, $options)
        ->addStandardTab('ManualLink', $ong, $options)
		->addStandardTab(PluginDlteamsTicket_Item::class, $ong, $options)
		->addStandardTab('KnowbaseItem_Item', $ong, $options)
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
            if ($item->getType() == 'PluginDlteamsConcernedPerson') {
               /** @var PluginDlteamsConcernedPerson $item */
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
	  $name=str_replace('"', '', addslashes($item->fields['name']));
	  $entities_ori=$item->fields['entities_id'];
	  $id_ori=$item->fields['id'];

	  //var_dump($name);
	  //var_dump($entities_ori);
	  //var_dump($id_ori);

      $nb=$dbu->countElementsInTable(static::getTable(), ['name' => addslashes($name), 'entities_id' => $entity]);
	  //var_dump($nb);

	  if($nb<=0){
		  $DB->request("INSERT INTO ".static::getTable()." (is_template, template_name, is_deleted, entities_id, is_recursive, date_mod, date_creation, id_model, name, content, comment) SELECT is_template, template_name, is_deleted, '$entity', is_recursive, date_mod, date_creation, id, name, content, comment FROM ".static::getTable()." WHERE id='$id_ori'");

			/***********add in allitem table****/
			/*$reqid=$DB->request("SELECT id FROM ".static::getTable()." WHERE entities_id='$entity' and name='$name' and copy_id='$id_ori'");
			if ($row = $reqid->next()) {
				$idRecord=$row['id']; //get id of copied concernperson record
			}

			/*SELECT * FROM `glpi_plugin_dlteams_allitems` WHERE (`items_id1`=159 AND `itemtype1`='PluginDlteamsConcernedPerson') OR ( `items_id2`=159 AND `itemtype2`='PluginDlteamsConcernedPerson');*/

			//first step
			/*$iduser=Session::getLoginUserID();
			$class=__class__;
			/*$reqpc=$DB->request("SELECT * FROM glpi_plugin_dlteams_allitems WHERE items_id2='$id_ori' AND itemtype2='$class' AND itemtype1='PluginDlteamsRecord'");
			//var_dump(count($reqpc));
			if (count($reqpc)) {

				foreach ($reqpc as $id => $row) {
						$val0_ori=$row['items_id1'];
						$val1_ori=$row['itemtype1'];
						$val3_ori=$row['itemtype2'];
						$val4_ori=str_replace('"', '', addslashes($row['comment']));
						$DB->request("INSERT INTO glpi_plugin_dlteams_allitems (items_id1,itemtype1,items_id2,itemtype2,comment) SELECT '$val0_ori', '$val1_ori', '$idRecord', '$val3_ori','$val4_ori'");
				}

			}else{

			}*/
			//first step

			//second step
			/*$reqpc1=$DB->request("SELECT * FROM glpi_plugin_dlteams_allitems WHERE items_id1='$id_ori' AND itemtype1='$class'");
			//var_dump(count($reqpc1));
			if (count($reqpc1)) {
				//var_dump(count($reqpersonalcategory));
				foreach ($reqpc1 as $id => $row) {
						$val0_ori1=$row['items_id1'];
						$val1_ori1=$row['itemtype1'];
						$val2_ori1=$row['items_id2'];
						$val3_ori1=$row['itemtype2'];
						$val4_ori1=str_replace('"', '', addslashes($row['comment']));
						$DB->request("INSERT INTO glpi_plugin_dlteams_allitems (items_id1,itemtype1,items_id2,itemtype2,comment) SELECT '$idRecord', '$val1_ori1', '$val2_ori1', '$val3_ori1','$val4_ori1'");
				}

				//part1
				$req=$DB->request("SELECT * FROM glpi_plugin_dlteams_allitems WHERE items_id1='$idRecord' AND itemtype1='$class'");
				//var_dump(count($req));
				if (count($req)) {
					//var_dump(count($reqpersonalcategory));
					foreach ($req as $id => $row) {
							$val0=$row['items_id1'];
							$val1=$row['itemtype1'];
							$val2=$row['items_id2'];
							$val3=$row['itemtype2'];
							$val4=str_replace('"', '', addslashes($row['comment']));
							if($val3=='Document'){
								//document
								$test=$DB->request("SELECT name FROM glpi_documents WHERE id='$val2' AND entities_id='$entities_ori'");
								//var_dump(count($test));
								if (count($test)) {
									foreach ($test as $id => $row) {
											$valname=str_replace('"', '', addslashes($row['name']));
											$test1=$DB->request("SELECT * FROM glpi_documents WHERE name='$valname' AND entities_id='$entity'");
											//var_dump($valname);
											if (count($test1)) {
												foreach ($test1 as $id => $row) {
													$itemnew=$row['id'];
													$DB->request("UPDATE glpi_plugin_dlteams_allitems set items_id2='$itemnew' WHERE items_id1='$idRecord' AND itemtype1='$val1' AND items_id2='$val2' AND itemtype2='$val3'");

													//insert or no in glpi_document_item
													$testdoc=$DB->request("SELECT * FROM glpi_documents_items WHERE documents_id='$itemnew' and items_id='$idRecord' and itemtype='$class' and entities_id='$entities_ori'");
													var_dump(count($testdoc));
													if (count($testdoc)) {

													}else{

														$DB->request("INSERT INTO glpi_documents_items (documents_id,items_id,itemtype,entities_id,is_recursive,date_mod,users_id,timeline_position,date_creation,date) SELECT '$itemnew','$idRecord','$class','$entity',is_recursive,date_mod,'$iduser',timeline_position,date_creation,date FROM glpi_documents_items WHERE documents_id='$val2_ori1' and items_id='$val0_ori1' and itemtype='$class' and entities_id='$entities_ori'");
													}
													//insert or no in glpi_document_item
												}

											}else{

												$DB->request("INSERT INTO glpi_documents (entities_id,is_recursive,name,filename,filepath,documentcategories_id,mime,date_mod,comment,is_deleted,link,users_id,tickets_id,sha1sum,is_blacklisted,tag,date_creation) SELECT '$entity',is_recursive,name,filename,filepath,documentcategories_id,mime,date_mod,comment,is_deleted,link,'$iduser',tickets_id,sha1sum,is_blacklisted,tag,date_creation FROM glpi_documents WHERE name='$valname' and entities_id='$entities_ori'");
												//////////////
												$test2=$DB->request("SELECT id FROM glpi_documents WHERE name='$valname' AND entities_id='$entity'");
												foreach ($test2 as $id => $row1) {
													$iddocnew=$row1['id'];
												}
												$DB->request("UPDATE glpi_plugin_dlteams_allitems set items_id2='$iddocnew' WHERE items_id1='$idRecord' AND itemtype1='$val1' AND items_id2='$val2' AND itemtype2='$val3'");

												//insert into glpi_document_item table

												$DB->request("INSERT INTO glpi_documents_items (documents_id,items_id,itemtype,entities_id,is_recursive,date_mod,users_id,timeline_position,date_creation,date) SELECT '$iddocnew','$idRecord','$class','$entity',is_recursive,date_mod,'$iduser',timeline_position,date_creation,date FROM glpi_documents_items WHERE documents_id='$val2_ori1' and items_id='$val0_ori1' and itemtype='$class' and entities_id='$entities_ori'");

												//insert into glpi_document_item table
											}

									}
								}else{

								}
								//document

							}else{
								$test=$DB->request("SELECT name FROM ".$val3::getTable()." WHERE id='$val2' AND entities_id='$entities_ori'");
								foreach ($test as $id => $row1) {
									$idr=str_replace('"', '', addslashes( $row1['name']));
									$test1=$DB->request("SELECT * FROM ".$val3::getTable()." WHERE name='$idr' AND entities_id='$entity'");
									if (count($test1)>0) {
										foreach ($test1 as $id => $row1) {
										   $idrecup=$row1['id'];
										}
										$DB->request("UPDATE glpi_plugin_dlteams_allitems set items_id2='$idrecup' WHERE items_id1='$idRecord' AND itemtype1='$val1' AND items_id2='$val2' AND itemtype2='$val3'");
									}else{
										//

										//
									}
								}
							}

					}
				}else{
				}
				//part1

			}else{

			}
			//second step

			/*************add in all item table*******/
		  return true;
	  }else{



		  return false;
	  }
   }


}
