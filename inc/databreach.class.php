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

class PluginDlteamsDataBreach extends CommonDBTM {

   static $rightname = 'plugin_dlteams_databreach';

   public $dohistory = true;
   protected $usenotepad = true;

   static function getTypeName($nb = 0) {
      return _n("Data breach", "Data breaches", $nb, 'dlteams');
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
      ->addStandardTab('Ticket', $ong, $options)
	  ->addStandardTab('KnowbaseItem_Item', $ong, $options)
      ->addImpactTab($ong, $options)
      ->addStandardTab('Notepad', $ong, $options)
      ->addStandardTab('Log', $ong, $options);
      return $ong;
   }
}
