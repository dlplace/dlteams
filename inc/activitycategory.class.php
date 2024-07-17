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

class PluginDlteamsActivityCategory extends CommonTreeDropdown {
   static $rightname = 'plugin_dlteams_activitycategory';
   public $dohistory = true;
   protected $usenotepad = true;

    static function canCreate() {return true;}
    static function canView() {return true;}
    static function canUpdate() {return true;}
    static function canDelete() {return true;}
    static function canPurge() {return true;}
    function canCreateItem() {return true;}
    function canViewItem() {return true;}
    function canUpdateItem() {return true;}
    function canDeleteItem() {return true;}
    function canPurgeItem() {return true;}


   static function getTypeName($nb = 0) {
      return _n("Activity or profession", "Activities, professions", $nb, 'dlteams');
   }


   function prepareInputForAdd($input) {
      $input['users_id_creator'] = Session::getLoginUserID();
      $input["activitycategories_idx"] = json_encode($_POST["activitycategories_idx"]);
      return parent::prepareInputForAdd($input);

   }

    function showForm($id, $options = [])
    {

        global $CFG_GLPI;

        $this->initForm($id, $options);
        $this->showFormHeader($options);

//        parent::showForm($id, $options);

//        echo "<tr class='tab_bg_1'>";
//        echo "<td style='text-align:right'>" .PluginDlteamsActivityCategory::getTypeName() . "</td>";
//        $randDropdown = mt_rand();
//        echo "<td colspan='2'>";
//        PluginDlteamsActivityCategory::dropdown([
//            'name' => 'plugin_dlteams_activitycategories_id',
//            'value' => $this->fields["plugin_dlteams_activitycategories_id"] ?? "", //$responsible,
//            'entity' => $this->fields["entities_id"],
//            'right' => 'all',
//            'width' => "250px",
//            'rand' => $randDropdown
//        ]);
//        echo "</td></tr>";

        /* echo "<tr class='tab_bg_1'>";
        echo "<td width='40%' style='text-align:right'>" . __("Number (order)", 'dlteams') . "</td>";
        echo "<td colspan='2'>";
        $number = Html::cleanInputText($this->fields['number']);
        echo "<input type='number' min='1' max='9999' name='number' required value='" . $number . "'>";
        //echo "<input type='number' style='width:10%' step='0.1' name='number' required value='" . $number . "'>";
        echo "</td></tr>";*/

        echo "<tr class='tab_bg_1'>";
        echo "<td width='40%' style='text-align:right'>" . __("Name", 'dlteams') . "</td>";
        echo "<td colspan='2'>";
        $title = Html::cleanInputText($this->fields['name']);
        echo "<input type='text' style='width:98%' maxlength=250 name='name' value='" . $title . "'>";
        echo "</td></tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td width='40%' style='text-align:right'>" . __("As child of") . "</td>";
        echo "<td colspan='2'>";
//        $title = Html::cleanInputText($this->fields['name']);

            Dropdown::show(PluginDlteamsActivityCategory::class, [
                'name' => 'activitycategories_id',
                'value' => $this->fields["activitycategories_id"],
                'used'   => (($id > 0) ? getSonsOf($this->getTable(), $id) : []),
                'width'  => '250px'
            ]);
        echo "</td></tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td width='40%' style='text-align:right'>" . __("Comment", 'dlteams') . "</td>";

        echo "<td colspan='2' >";
        $purpose = Html::cleanInputText($this->fields['comment']);
        echo "<textarea style='width:98%' name='comment' maxlength='1000' rows='5'>" . $purpose . "</textarea>";
        echo "</td></tr>";

        $this->showFormButtons($options);

        return true;
    }

   function prepareInputForUpdate($input) {
      $input['users_id_lastupdater'] = Session::getLoginUserID();
      return parent::prepareInputForUpdate($input);
   }

   function cleanDBonPurge() {
      $rel = new PluginDlteamsRecord_RecordCategory();
      $rel->deleteByCriteria(['plugin_dlteams_activitycategories_id' => $this->fields['id']]);
   }

    public function defineTabs($options = [])
    {
        $ong = [];
        $ong = array();
        //add main tab for current object
        $this->addDefaultFormTab($ong)
            ->addStandardTab('PluginDlteamsRecord_Item', $ong, $options)
            ->addStandardTab(PluginDlteamsLegalBasi_Item::class, $ong, $options)
            ->addStandardTab(PluginDlteamsPolicieForm_Item::class, $ong, $options);
        return $ong;
    }

    function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => __("Characteristics")
      ];

//      $tab[] = [
//         'id'                 => '1',
//         'table'              => $this->getTable(),
//         'field'              => 'number',
//         'name'               => __("Num"),
//         'datatype'           => 'number',
//         'massiveaction'      => false,
//      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __("Name"),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'autocomplete'       => true,
      ];

        $tab[] = [
            'id' => '3',
            'table' => $this->getTable(),
            'field' => 'completename',
            'name' => self::getTypeName(),
            'datatype' => 'dropdown',
            'massiveaction' => true,
        ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __("ID"),
         'massiveaction'      => false,
         'datatype'           => 'number',
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
         'datatype'           => 'dropdown',
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __("Child entities"),
         'datatype'           => 'bool',
         'massiveaction'      => false,
      ];

      return $tab;
   }
}
