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

use Glpi\RichText\RichText;

class PluginDlteamsTrainingSession extends CommonDBTM {
   static $rightname = 'plugin_dlteams_trainingsession';
   public $dohistory = true;
   protected $usenotepad = true;
   
   static function getTypeName($nb = 0) {
      return _n("Training session", "Training sessions", $nb, 'dlteams');
   }

   function showForm($id, $options = []) {
      global $CFG_GLPI;
      $this->initForm($id, $options);
      $this->showFormHeader($options);

      echo "<tr class='tab_bg_1'>";
      echo "<td width='50%'>". __("Name", 'dlteams') . "</i></td>";
      echo "<td colspan='2'>";
		$name = Html::cleanInputText($this->fields['name']);
		echo "<input type='text' style='width:71%' maxlength=250 name='name' required value='" . $name . "'>";
      echo "</td></tr>";
	  
	  echo "<tr class='tab_bg_1'>";
      echo "<td width='50%'>". __("Référence", 'dlteams') . "</i></td>";
	  echo "<td colspan='1'>";
		echo "<input type='text' style='width:71%' maxlength=100 name='reference' value='" . Html::cleanInputText($this->fields['reference']) . "'>";
	  echo "</td></tr>";
	 
	  echo "<tr class='tab_bg_1'>";
      echo "<td width='50%'>". __("Public Cible", 'dlteams') . "</i></td>";
	  echo "<td colspan='3'>";
		echo "<input type='text' style='width:71%' maxlength=250 name='public' value='" . Html::cleanInputText($this->fields['public']) . "'>";
	  echo "</td></tr>";
	
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Comment", 'dlteams') . "</td>";
      echo "<td colspan='2'>";
		$comment = Html::cleanInputText($this->fields['comment']);
		echo "<textarea style='width: 70%;' name='comment' maxlength='1000' rows='3'>" . $comment . "</textarea>";
      echo "</td></tr>";

	  echo "<tr class='tab_bg_1'>";
      echo "<td width='50%'>" . __("Formateur", 'dlteams') . "</td>";
      echo "<td colspan='2'>";
		  User::dropdown(['name' => 'users_id','width'  => "73%",'value' => $this->fields['users_id']]);
      echo "</td></tr>";
	  
	  $this->showFormButtons($options);
      return true;
   }
   
   function prepareInputForAdd($input) {
      $input['users_id_creator'] = Session::getLoginUserID();
      return parent::prepareInputForAdd($input);
   }

   function prepareInputForUpdate($input) {
      $input['users_id_lastupdater'] = Session::getLoginUserID();
      return parent::prepareInputForUpdate($input);
   }

   function cleanDBonPurge() {
      //$rel = new PluginDlteamsRecord_MotifEnvoi();
      //$rel->deleteByCriteria(['plugin_dlteams_concernedpersons_id' => $this->fields['id']]);
   
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
         'field'              => 'reference',
         'name'               => __("Référence"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];
	  
	  $tab[] = [
         'id'                 => '7',
         'table'              => $this->getTable(),
         'field'              => 'begin_date',
         'name'               => __("Date début"),
         'datatype'           => 'timestamp',
         'toview'             => true,
         'massiveaction'      => true,
      ];
	  
	  $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'end_date',
         'name'               => __("Date fin"),
         'datatype'           => 'timestamp',
         'toview'             => true,
         'massiveaction'      => true,
      ];
	  
	  $tab[] = [
         'id'                 => '9',
         'table'              => $this->getTable(),
         'field'              => 'public',
         'name'               => __("Public"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];
	  
	  $tab[] = [
         'id'                 => '10',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'name'               => __("Formateur"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];
      return $tab;
   }
   
   public function defineTabs($options = []) {
      $ong = [];
      $ong = array();
      //add main tab for current object
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
/*   
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
*/
  
}
