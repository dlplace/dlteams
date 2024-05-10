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
class PluginDlteamsProtectiveMeasure extends CommonDropdown implements
   PluginDlteamsExportableInterface{
   use PluginDlteamsExportable;
   static $rightname = 'plugin_dlteams_protectivemeasure';
   public $dohistory = true;
   protected $usenotepad = true;

   static function getTypeName($nb = 0) {
      return _n("Protective measure", "Protective measures", $nb, 'dlteams');
   }

    // Get all the types that can have a document - @return array of the itemtypes
    public static function getItemtypesThatCanHave() {
        global $CFG_GLPI;
        return array_merge(
            $CFG_GLPI['document_types'],
            CommonDevice::getDeviceTypes(),
            Item_Devices::getDeviceTypes()
        );
    }

    function showForm($id, $options = []) {
      global $CFG_GLPI;
      $this->initForm($id, $options);
      $this->showFormHeader($options);

	echo "<tr class='tab_bg_1'>";
	echo "<td width='25%' style='text-align: right'>". __("Name", 'dlteams') . "</i></td>";
	echo "<td colspan='2'>";
    $name = Html::cleanInputText($this->fields['name']);
    echo "<input type='text' style='width:70%' maxlength=250 name='name' required value='" . $name . "'>";
    echo "</td></tr>";

	echo "<tr class='tab_bg_1'>";
    echo "<td style='text-align: right'>" . __("Content", 'dlteams') . "</td>";
    echo "<td colspan='2'>";
    $content = Html::cleanInputText($this->fields['content']);
    echo "<textarea style='width: 70%;' name='content' maxlength='1000' rows='3'>" . $content . "</textarea>";
    echo "</td></tr>";

	// echo "<table>";
		echo "<tr class='tab_bg_1'>";
		echo "<td width='25%' style='text-align: right'>". __("Type", 'dlteams') . "</i></td>";
		echo "<td colspan='2'>";
		PluginDlteamsProtectiveType::dropdown([
            'addicon'  => PluginDlteamsProtectiveType::canCreate(),
            'name' => 'plugin_dlteams_protectivetypes_id',
			// 'width' => '72.5%',
			'value' => $this->fields['plugin_dlteams_protectivetypes_id']
		]);
		echo "</td>";
		/* echo "<tr class='tab_bg_1'>";
		 echo "<td width='25%' style='text-align: right'>". __("Catégories", 'dlteams') . "</i></td>";
		 echo "<td colspan='2'>";
			PluginDlteamsProtectiveCategory::dropdown([
            'addicon'  => PluginDlteamsProtectiveCategory::canCreate(),
            'name' => 'plugin_dlteams_protectivecategories_id',
			// 'width' => '100%',
			'value' => $this->fields['plugin_dlteams_protectivecategories_id']
			]);
		echo "</td></tr>";*/
		echo "<td>" . __("Catégories", 'dlteams')."</td>";
		 echo "<td>";
			PluginDlteamsProtectiveCategory::dropdown([
            'addicon'  => PluginDlteamsProtectiveCategory::canCreate(),
            'name' => 'plugin_dlteams_protectivecategories_id',
			// 'width' => '100%',
			'value' => $this->fields['plugin_dlteams_protectivecategories_id']
			]);
		echo "</td></tr>";
	 
		// choix multiple "S'applique à Materiel : Ordinateur, Moniteur, Matériel réseau, Périphérique, Imprimantes, Téléphone, Baie, PDU, Equipement passif, Câble, Datacenter, Cluster | Catalogue : Application-base de données, Boite mail, Centre d'administration, Iot, Documents papier, Dossiers et fichiers - Drive, Journbaux & logs, Sauvegarde) | Compte ou Clé, Logiciel, Certificat, Jeu de données | Mobilier, Local, Véhicule
		/* echo "<tr class='tab_bg_1'>";
		echo "<td width='25%' style='text-align: right'>". __("S'applique à : ", 'dlteams') . "</i></td>";
		echo "<td colspan='2'>";
		PluginDlteamsProtectiveType::dropdown([
            'addicon'  => PluginDlteamsProtectiveType::canCreate(),
            'name' => 'plugin_dlteams_protectivetypes_id',
			// 'width' => '72.5%',
			'value' => $this->fields['plugin_dlteams_protectivetypes_id']
		]);
		echo "</td></tr>"; */
		// echo"</table>";
		
      echo "<tr class='tab_bg_1'>";
      echo "<td style='text-align: right'>" . __("Comment", 'dlteams') . "</td>";
      echo "<td colspan='2'>";
      $comment = Html::cleanInputText($this->fields['comment']);
      echo "<textarea style='width: 70%;' name='comment' maxlength='1000' rows='3'>" . $comment . "</textarea>";
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
         'name'               => __("Contenu"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

	   $tab[] = [
         'id'                 => '7',
         'table'              => 'glpi_plugin_dlteams_protectivetypes',
         'field'              => 'name',
         'name'               => __("Type"),
         'datatype'           => 'dropdown',
         'toview'             => true,
         'massiveaction'      => true,
      ];

	   $tab[] = [
         'id'                 => '8',
         'table'              => 'glpi_plugin_dlteams_protectivecategories',
         'field'              => 'name',
         'name'               => __("Categorie"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
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



      return $tab;
   }

   public function defineTabs($options = [])
   {

      $ong = [];

      $ong = array();
      //add main tab for current object
      $this->addDefaultFormTab($ong)
      ->addStandardTab('PluginDlteamsRecord_Item', $ong, $options)
      ->addStandardTab('PluginDlteamsObject_document', $ong, $options)
      ->addStandardTab('ManualLink', $ong, $options)
	  ->addStandardTab(PluginDlteamsProtectiveMeasure_Item::class, $ong, $options)
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
            if ($item->getType() == 'PluginDlteamsProtectiveMeasure') {
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
		  $DB->request("INSERT INTO ".static::getTable()." (is_template, template_name, is_deleted, entities_id, is_recursive, date_mod, date_creation, name, content, comment, plugin_dlteams_protectivetypes_id, plugin_dlteams_protectivecategories_id	) SELECT is_template, template_name, is_deleted, '$entity', is_recursive, date_mod, date_creation, name, content, comment, plugin_dlteams_protectivetypes_id, plugin_dlteams_protectivecategories_id FROM ".static::getTable()." WHERE id='$id'");
		  return true;
	  }else{
		  return false;
	  }
   }

}
