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

class PluginDlteamsAccountKeyCat extends CommonDBTM implements
   PluginDlteamsExportableInterface{
   use PluginDlteamsExportable;
   static $rightname = 'plugin_dlteams_record';
   public $dohistory = true;
   protected $usenotepad = true;
  
   static function getTypeName($nb = 0) {
      return _n("Accounts and Keys", "Accounts and Keys", $nb, 'dlteams');
   }
   
    function showForm($id, $options = [])
   {

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
      echo "<td width='50%'>". __("Annuaire de stockage", 'dlteams') . "</i></td>";
	  echo "<td colspan='2'>";
	  PluginDlteamsDatabase::dropdown([
            'addicon'  => PluginDlteamsDatabase::canCreate(),
            'name' => 'plugin_dlteams_databases_id_db0',
            'width' => '73%',
			'value' => $this->fields['plugin_dlteams_databases_id_db0']
      ]);
	 echo "</td></tr>";
	
	  echo "<tr class='tab_bg_1'>";
      echo "<td width='50%'>". __("Type de clé", 'dlteams') . "</i></td>";
	  echo "<td colspan='2'>";
	  PluginDlteamsKeyType::dropdown([
            'addicon'  => PluginDlteamsKeyType::canCreate(),
            'name' => 'plugin_dlteams_keytypes_id',
            'width' => '73%',
			'value' => $this->fields['plugin_dlteams_keytypes_id']
      ]);
	 echo "</td></tr>";
	 
	  echo "<tr class='tab_bg_1'>";
      echo "<td width='50%'>". __("Alternative", 'dlteams') . "</i></td>";
	  echo "<td colspan='2'>";
	  PluginDlteamsKeyType::dropdown([
            'addicon'  => PluginDlteamsKeyType::canCreate(),
            'name' => 'plugin_dlteams_keytypes2_id',
            'width' => '73%',
			'value' => $this->fields['plugin_dlteams_keytypes2_id']
      ]);
	 echo "</td></tr>";
	
	  echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Feature", 'dlteams') . "</td>";
      echo "<td colspan='2'>";
      $caracteristique= Html::cleanInputText($this->fields['key_features']);
      echo "<textarea style='width: 70%;' name='key_features' maxlength='1000' rows='3'>" . $caracteristique . "</textarea>";
      echo "</td></tr>";

      // Redacteurs
      /*if ($responsible = $this->fields["users_id_responsible"]);
      // if empty, take legal representative of the entity
      else {
         global $DB;
         $iterator = $DB->request([
            'SELECT' => 'users_id_representative',
            'FROM' => 'glpi_plugin_dlteams_controllerinfos',
            'WHERE' => ['entities_id' => $this->getEntityID()]
         ]);
         $responsible = $iterator->next()['users_id_representative'];
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Process responsible", 'dlteams') . "</td>";
      $randDropdown = mt_rand();
      echo "<td colspan='2'>";
      User::dropdown([
         'name'   => 'users_id_responsible',
         'value'  => $responsible,
         'entity' => $this->fields["entities_id"],
         'right'  => 'all',
         'width'  => "60%",
         'rand'   => $randDropdown
      ]);
      echo "</td></tr>";*/
      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Comment", 'dlteams') . "</td>";
      echo "<td colspan='2'>";
      $comment = Html::cleanInputText($this->fields['comment']);
      echo "<textarea style='width: 70%;' name='comment' maxlength='1000' rows='3'>" . $comment . "</textarea>";
      echo "</td></tr>";
	  echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Recovery method", 'dlteams') . "</td>";
      echo "<td colspan='2'>";
      $recupcle = Html::cleanInputText($this->fields['recovery_methode']);
      echo "<textarea style='width: 70%;' name='recovery_methode' maxlength='1000' rows='3'>" . $recupcle . "</textarea>";
      echo "</td></tr>";
	  
	  echo "<tr class='tab_bg_1'>";
      echo "<td width='50%'>" . __("Utilisateur", 'dlteams') . "</td>";
      echo "<td colspan='2'>";
      User::dropdown([
         'name'   => 'users_id',
         'width'  => "73%",
		 'value' => $this->fields['users_id']
      ]);
      echo "</td></tr>";
	  
	  echo "<tr class='tab_bg_1'>";
      echo "<td width='50%'>" . __("Groupe", 'dlteams') . "</td>";
      echo "<td colspan='2'>";
      Group::dropdown([
         'name'   => 'groups_id',
         'width'  => "73%",
		 'value' => $this->fields['groups_id']
      ]);
	  
	  
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
	  
	  /*$tab[] = [
         'id'                 => '6',
         'table'              => 'glpi_plugin_dlteams_databases',
         'field'              => 'name',
         'name'               => __("Nom"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];*/
	  
	  $tab[] = [
         'id'                 => '7',
         'table'              => 'glpi_plugin_dlteams_keytypes',
         'field'              => 'name',
         'name'               => __("Type de clé"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];
	  
	  $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'key_features',
         'name'               => __("Caracteristique"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];
	  
	  $tab[] = [
         'id'                 => '9',
         'table'              => $this->getTable(),
         'field'              => 'recovery_methode',
         'name'               => __("Méthode de récupération"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];
	  
	  $tab[] = [
         'id'                 => '10',
         'table'              => 'glpi_users',
         'field'              => 'name',
         'name'               => __("Utilisateur"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];
	  
	  $tab[] = [
         'id'                 => '11',
         'table'              => 'glpi_groups',
         'field'              => 'name',
         'name'               => __("Groupe"),
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
      ->addStandardTab('PluginDlteamsObject_document', $ong, $options)
      ->addStandardTab('ManualLink', $ong, $options)	  
	  ->addStandardTab('PluginDlteamsObject_allitem', $ong, $options)
      ->addStandardTab('Ticket', $ong, $options)
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

  
}
