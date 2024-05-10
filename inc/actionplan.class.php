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

class PluginDlteamsActionPlan extends CommonTreeDropdown {
   static $rightname = 'plugin_dlteams_step';
   public $dohistory = true;
   protected $usenotepad = true;
   
   static function getTypeName($nb = 0) {
      return _n("Project task", "Project tasks", $nb, 'dlteams');
   }
   
   public function getName($options = []) {
   return sprintf("%s | %s", $this->fields["projects_id"], $this->fields["completename"]);
   }
// il fautdra filter pou run seul projet 
   /**
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
      echo "<td>" . __("Comment", 'dlteams') . "</td>";
      echo "<td colspan='2'>";
		$comment = Html::cleanInputText($this->fields['comment']);
		echo "<textarea style='width: 70%;' name='comment' maxlength='1000' rows='3'>" . $comment . "</textarea>";
      echo "</td></tr>";
	  
	  $this->showFormButtons($options);
      return true;
   }
   **/
   
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
            'name'               => __('Characteristics')
        ];

        $tab[] = [
            'id'                 => '1',
            'table'              => $this->getTable(),
            'field'              => 'name',
            'name'               => __('Name'),
            'datatype'           => 'itemlink',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '2',
            'table'              => 'glpi_projects',
            'field'              => 'name',
            'name'               => Project::getTypeName(1),
            'massiveaction'      => false,
            'datatype'           => 'dropdown'
        ];
	  // il faudrait ajouter un bouton sur la ligne "filtrer ce projet"
	  
        $tab[] = [
            'id'                 => '13',
            'table'              => $this->getTable(),
            'field'              => 'name',
            'name'               => __('Father'),
            'datatype'           => 'dropdown',
            'massiveaction'      => false,
         // Add virtual condition to relink table
            'joinparams'         => [
                'condition'          => 'AND 1=1'
            ]
        ];

        $tab[] = [
            'id'                 => '21',
            'table'              => $this->getTable(),
            'field'              => 'content',
            'name'               => __('Description'),
            'massiveaction'      => false,
            'datatype'           => 'text',
            'htmltext'           => true,
        ];

        $tab[] = [
            'id'                 => '12',
            'table'              => 'glpi_projectstates',
            'field'              => 'name',
            'name'               => _x('item', 'State'),
            'datatype'           => 'dropdown'
        ];

        $tab[] = [
            'id'                 => '14',
            'table'              => 'glpi_projecttasktypes',
            'field'              => 'name',
            'name'               => _n('Type', 'Types', 1),
            'datatype'           => 'dropdown'
        ];

        $tab[] = [
            'id'                 => '121',
            'table'              => $this->getTable(),
            'field'              => 'date_creation',
            'name'               => __('Creation date'),
            'datatype'           => 'datetime',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '19',
            'table'              => $this->getTable(),
            'field'              => 'date_mod',
            'name'               => __('Last update'),
            'datatype'           => 'datetime',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '5',
            'table'              => $this->getTable(),
            'field'              => 'percent_done',
            'name'               => __('Percent done'),
            'datatype'           => 'number',
            'unit'               => '%',
            'min'                => 0,
            'max'                => 100,
            'step'               => 5
        ];

        $tab[] = [
            'id'                 => '24',
            'table'              => 'glpi_users',
            'field'              => 'name',
            'linkfield'          => 'users_id',
            'name'               => __('Creator'),
            'datatype'           => 'dropdown'
        ];

        $tab[] = [
            'id'                 => '7',
            'table'              => $this->getTable(),
            'field'              => 'plan_start_date',
            'name'               => __('Planned start date'),
            'datatype'           => 'datetime'
        ];

        $tab[] = [
            'id'                 => '8',
            'table'              => $this->getTable(),
            'field'              => 'plan_end_date',
            'name'               => __('Planned end date'),
            'datatype'           => 'datetime'
        ];

        $tab[] = [
            'id'                 => '9',
            'table'              => $this->getTable(),
            'field'              => 'real_start_date',
            'name'               => __('Real start date'),
            'datatype'           => 'datetime'
        ];

        $tab[] = [
            'id'                 => '10',
            'table'              => $this->getTable(),
            'field'              => 'real_end_date',
            'name'               => __('Real end date'),
            'datatype'           => 'datetime'
        ];

        $tab[] = [
            'id'                 => '11',
            'table'              => $this->getTable(),
            'field'              => 'planned_duration',
            'name'               => __('Planned duration'),
            'datatype'           => 'timestamp',
            'min'                => 0,
            'max'                => 100 * HOUR_TIMESTAMP,
            'step'               => HOUR_TIMESTAMP,
            'addfirstminutes'    => true,
            'inhours'            => true
        ];

        $tab[] = [
            'id'                 => '17',
            'table'              => $this->getTable(),
            'field'              => 'effective_duration',
            'name'               => __('Effective duration'),
            'datatype'           => 'timestamp',
            'min'                => 0,
            'max'                => 100 * HOUR_TIMESTAMP,
            'step'               => HOUR_TIMESTAMP,
            'addfirstminutes'    => true,
            'inhours'            => true
        ];

        $tab[] = [
            'id'                 => '16',
            'table'              => $this->getTable(),
            'field'              => 'comment',
            'name'               => __('Comments'),
            'datatype'           => 'text'
        ];

        $tab[] = [
            'id'                 => '18',
            'table'              => $this->getTable(),
            'field'              => 'is_milestone',
            'name'               => __('Milestone'),
            'datatype'           => 'bool'
        ];

        $tab[] = [
            'id'                 => '50',
            'table'              => $this->getTable(),
            'field'              => 'template_name',
            'name'               => __('Template name'),
            'datatype'           => 'text',
            'massiveaction'      => false,
            'nosearch'           => true,
            'nodisplay'          => true,
        ];

        $tab[] = [
            'id'                 => '80',
            'table'              => 'glpi_entities',
            'field'              => 'completename',
            'name'               => Entity::getTypeName(1),
            'datatype'           => 'dropdown'
        ];

        $tab[] = [
            'id'                 => '86',
            'table'              => $this->getTable(),
            'field'              => 'is_recursive',
            'name'               => __('Child entities'),
            'datatype'           => 'bool'
        ];

        $tab = array_merge($tab, Notepad::rawSearchOptionsToAdd());

      return $tab;
   }
   
   public function defineTabs($options = []) {
      $ong = [];
      $ong = array();
      //add main tab for current object
      $this->addDefaultFormTab($ong)
      ->addStandardTab('ManualLink', $ong, $options)
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
