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

class PluginDlteamsMeansOfAcce extends CommonDropdown {
   static $rightname = 'plugin_dlteams_meansofacces';
   public $dohistory = true;
   protected $usenotepad = true;

   static function getTypeName($nb = 0) {
      return _n("Means of access", "Means of access", $nb, 'dlteams');
   }

   function prepareInputForAdd($input) {
      $input['users_id_creator'] = Session::getLoginUserID();
      return parent::prepareInputForAdd($input);
   }


    public static function canView()
   {
       return true;
   }


   public static function canCreate()
   {
       return true;
   }

    function showForm($id, $options = [])
   {
      global $CFG_GLPI;
      $this->initForm($id, $options);
      $this->showFormHeader($options);

	  echo "<tr>";
		echo "<td style='text-align:right'>". __("Name", 'dlregiste') . "</td>";
		echo "<td>" . "<input type='text' style='width:70%' style='text-align:left' name='name' required value='" . Html::cleanInputText($this->fields['name']). "'>" . "</td>";
		echo "<td width='15%'>". " " . "</td>";
	  echo "</tr>" ;

      echo "<tr>";
		echo "<td style='text-align:right'>". __("Content", 'dlteams') . "</td>";
		echo "<td>" . "<textarea style='width:70%' rows='2' style='text-align:left' name='content' >" . Html::cleanInputText($this->fields['content']) . "</textarea>" . "</td>";
		echo "<td width='15%'>". " " . "</td>";
	  echo "</tr>" ;

      echo "<tr>";
		echo "<td style='text-align:right'>". __("Comment") . "</td>";
		echo "<td>" . "<textarea style='width:70%' rows='2' style='text-align:left' name='comment' >" . Html::cleanInputText($this->fields['comment']) . "</textarea>" . "</td>";
		echo "<td width='15%'>". " " . "</td>";
	  echo "</tr>" ;

      $this->showFormButtons($options);
      return true;
   }

   function prepareInputForUpdate($input) {
      $input['users_id_lastupdater'] = Session::getLoginUserID();
      return parent::prepareInputForUpdate($input);
   }

   function cleanDBonPurge() {
      $rel = new PluginDlteamsRecord_ServerType();
      $rel->deleteByCriteria(['plugin_dlteams_meansofacces_id' => $this->fields['id']]);
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
         'field'              => 'content',
         'name'               => __("Content"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];
      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __("Comments"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];
      $tab[] = [
         'id'                 => '5',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __("Entity"),
         'datatype'           => 'dropdown',
         'massiveaction'      => true,
      ];
      $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __("Child entities"),
         'datatype'           => 'bool',
         'massiveaction'      => false,
      ];

      return $tab;
   }

   /*public function defineTabs($options = [])
   {

      $ong = [];

      $ong = array();
      //add main tab for current object
      $this->addDefaultFormTab($ong);
      $this->addImpactTab($ong, $options);
      // $this->addStandardTab('Document_Item', $ong, $options);
      $this->addStandardTab('Ticket', $ong, $options);
      $this->addStandardTab('Notepad', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);

      return $ong;
   }*/


}
