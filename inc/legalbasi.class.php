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

//class PluginDlteamsLegalbasi extends CommonDBTM {
class PluginDlteamsLegalbasi extends CommonDropdown {
   use Glpi\Features\Clonable;
   static $rightname = 'plugin_dlteams_legalbasi';
   public $dohistory = true;
   protected $usenotepad = true;

    public function getCloneRelations(): array
    {
        return [
            Document_Item::class,
            Notepad::class,
            KnowbaseItem_Item::class,
        ];

    }

   static function getTypeName($nb = 0) {
      return _n("Legal basis", "Legal bases", $nb, 'dlteams');
   }

   function showForm($id, $options = []) {
      global $CFG_GLPI;
      $this->initForm($id, $options);
      $this->showFormHeader($options);

       echo "<style>";
       echo "
            .form-table-text {
                text-align: right;
                width: 25%;
            }
            
            
            @media (max-width: 800px) {
                .form-table-text {
                    text-align: left;
                    width: 100%;
                }
            }
        ";

       echo "</style>";

	echo "<table, th, td width='100%'>";
	  echo "<tr>";
//		echo "<td width='15%' style='text-align:right'>". " " . "</td>";
		echo "<td class='form-table-text' >". __("Name", 'dlteams') . "</td>";
		echo "<td>";
		$name = Html::cleanInputText($this->fields['name']);
		echo "<input type='text' style='width:98%' name='name' required value='" . $name. "'>" . "</td>";
		echo "<td width='15%' style='text-align:right'>". " " . "</td>";
	  echo "</tr>" ;

	  echo "<tr>";
//        echo "<td width='15%' style='text-align:right'>". " " . "</td>";
		echo "<td class='form-table-text'>". __("Type de bases légales", 'dlteams') . "</div></td>";
	    echo "<td>";
		PluginDlteamsLegalBasisType::dropdown([
            'addicon'  => PluginDlteamsLegalBasisType::canCreate(),
            'name' => 'plugin_dlteams_legalbasistypes_id',
			'width' => '300px', // '100%',
			'value' => $this->fields['plugin_dlteams_legalbasistypes_id']
		 ]);
	  echo "</td></tr>";

	  echo "<tr>";
//      echo "<td width='15%' style='text-align:right'>". " " . "</td>";
	  echo "<td class='form-table-text'>" . __("Content", 'dlteams') . "</td>";
	  echo "<td>";
      $content = Html::cleanInputText($this->fields['content']);
//      echo "<textarea style='width: 98%;' name='content' rows='3'>" . $content . "</textarea>";

       Html::textarea(['name'            => 'content',
           'value'           => $content,
           'cols'            => 60,
           'rows'            => 3,
           'enable_richtext' => true]);

      echo "</td></tr>";

/*	  echo "<tr>";
	  $content = Html::textarea(['name' => 'content',
		'value' => $this->fields['content'],
        'enable_fileupload' => false,
        'enable_richtext' => true,
        'cols' => 100,
        'rows' => 10
        ]);
	  echo "<textarea style='width: 98%;' name='content' rows='3'>" . $content . "</textarea>";
	  echo "</tr>";*/

      echo "<tr>";
//      echo "<td width='15%' style='text-align:right'>". " " . "</td>";
	  echo "<td class='form-table-text'>" . __("Comment", 'dlteams') . "</td>";
      echo "<td>";
	  $comment = Html::cleanInputText($this->fields['comment']);
      echo "<textarea style='width: 98%;' name='comment' rows='3'>" . $comment . "</textarea>";
      echo "</td></tr>";

	  echo "<tr>";
//	  echo "<td width='15%' style='text-align:right'>". " " . "</td>";
      echo "<td class='form-table-text'>". __("URL", 'dlteams')."</td>";
      echo "<td>";
            Html::autocompletionTextField($this, "url");
	  echo "&nbsp;<a target='_blank' href='" . $this->fields["url"] . "'><i class=\"fas fa-link\"></i></a>";
	  echo "</td></tr>";

	  echo "<tr>";
//	  echo "<td width='15%' style='text-align:right'>". " " . "</td>";
      echo "<td class='form-table-text'>". __("URL", 'dlteams')."</td>";
      echo "<td>";
           Html::autocompletionTextField($this, "url2");
	  echo "&nbsp;<a target='_blank' href='" . $this->fields["url2"] . "'><i class=\"fas fa-link\"></i></a>";
      echo "</td></tr>";
    echo "</table>";

       $this->showFormButtons($options);
      return true;
   }

   function getAdditionalFields() {
      return [
         [
            'name' => 'type',
            'label' => __("Type"),
            'list' => true,
         ],
         [
            'name' => 'content',
            'label' => __("Content"),
            'type' => 'textarea',
            'rows' => 6
         ]
      ];
   }

   static function getSpecificValueToDisplay($field, $values, array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      switch ($field) {
         case 'type' :
            $legalbases = self::getAllTypesArray();
            return $legalbases[$values[$field]];
      }
      return parent::getSpecificValueToDisplay($field, $values, $options);
   }

   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {
      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;

      switch ($field) {
         case 'type' :

            return self::dropdownTypes($name, $values[$field], false);
      }

      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }

   function displaySpecificTypeField($ID, $field = [], array $options = []) {
      if ($field['name'] == 'type') {
         self::dropdownTypes($field['name'], $this->fields[$field['name']], true);
      }
   }

   static function dropdownTypes($name, $value = 0, $display = true) {
      return Dropdown::showFromArray($name, self::getAllTypesArray(), [
         'value' => $value, 'display' => $display]);
   }

   static function getAllTypesArray() {
      return [
         self::LEGALBASISACT_BLANK => __("Undefined", 'dlteams'),
         self::LEGALBASISACT_GDPR => __("GDPR Article", 'dlteams'),
         self::LEGALBASISACT_NATIONAL => __("Local law regulation", 'dlteams'),
         self::LEGALBASISACT_INTERNATIONAL => __("International regulation", 'dlteams'),
         self::LEGALBASISACT_INTERNAL => __("Controller internal regulation", 'dlteams'),
         self::LEGALBASISACT_OTHER => __("Other regulation", 'dlteams'),
      ];
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
      $rel = new PluginDlteamsAllItem();
      $rel->deleteByCriteria(['items_id1' => $this->fields['id'], 'itemtype1' => $this->getType()]);
      $rel->deleteByCriteria(['items_id2' => $this->fields['id'], 'itemtype2' => $this->getType()]);
   }

   function rawSearchOptions($forcetotal=false) {
     $tab = parent::rawSearchOptions();
     $tab = array_merge($tab, Location::rawSearchOptionsToAdd());
     $tab = [];
     
	 $tab[] = [
         'id'                 => 'common',
         'name'               => __("Characteristics")
      ];

	  /*$tab[] = [
         'id' => '101',
         'table' => 'users',
         'field' => 'users_id_responsible',
         'name' => __("Responsable du traitement"),
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
      ];*/
      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __("Name"),
         'datatype'           => 'itemlink',
		 'toview' 		      => true,
         'massiveaction'      => true,
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'plugin_dlteams_legalbasistypes_id',
         'name'               => __("N°"),
         'datatype'   	      => 'number',
         'toview' 		      => false,
         'massiveaction'      => false,
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => 'glpi_plugin_dlteams_legalbasistypes',
         'field'              => 'name',
         'name'               => __("Type"),
         'datatype'    		  => 'dropdown',
         'toview' 		      => true,
         'massiveaction'      => true,
      ];
	  
      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __('ID'),
         'datatype'           => 'number',
         'massiveaction'      => false, // implicit field is id
         'toview'             => true,
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'content',
         'name'               => __("Content"),
         'datatype'           => 'text',
         'htmltext'           => true,
         'toview'             => true,
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __("Comments"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

	  $tab[] = [
         'id'                 => '10',
         'table'              => $this->getTable(),
         'field'              => 'url',
         'name'               => __("URL"),
         'massiveaction'      => true,
         'datatype'           => 'weblink',
      ];

      $tab[] = [
         'id'                 => '13',
         'table'              => $this->getTable(),
         'field'              => 'url2',
         'name'               => __("URL 2"),
         'massiveaction'      => true,
         'datatype'           => 'weblink',
      ];

      $tab[] = [
         'id'                 => '14',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __("Entity"),
         'massiveaction'      => true,
         'datatype'           => 'dropdown',
      ];

      $tab[] = [
         'id'                 => '15',
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
      ->addStandardTab('PluginDlteamsRecord_Item', $ong, $options)
      ->addStandardTab('PluginDlteamsLegalBasi_Item', $ong, $options)
	  ->addStandardTab('PluginDlteamsObject_document', $ong, $options)
	  ->addStandardTab('ManualLink', $ong, $options)
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
            if ($item->getType() == 'PluginDlteamsLegalbasi') {
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
	  $name=$item->fields['name'];
      $nb=$dbu->countElementsInTable(static::getTable(), ['name' => addslashes($name), 'entities_id' => $entity]);
	  if($nb<=0){
		  $DB->request("INSERT INTO ".static::getTable()." (entities_id, is_recursive, date_mod, is_deleted, date_creation, name, plugin_dlteams_legalbasistypes_id, content, comment, url, url2) SELECT '$entity', is_recursive, date_mod, is_deleted, date_creation, name, plugin_dlteams_legalbasistypes_id, content, comment, url, url2 FROM ".static::getTable()." WHERE id='$id'");
		  return true;
	  }else{
		  return false;
	  }
   }
}
