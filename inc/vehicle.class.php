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

class PluginDlteamsVehicle extends CommonDropdown implements
   PluginDlteamsExportableInterface {
   use PluginDlteamsExportable;
   static $rightname = 'plugin_dlteams_vehicle';
   public $dohistory = true;
   protected $usenotepad = true;

    static function getMenuName() {
        return _n('Vehicle', 'Vehicles', 2, 'dlteams');
    }

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

    static function getMenuContent() {
        $physicalstorageFormUrl = "/".Plugin::getWebDir('dlteams', false).'/front/vehicle.php';
        $menu = [
            'title' => self::getMenuName(),
            'page'  => $physicalstorageFormUrl,
            'icon'  => 'fas fa-truck',
        ];

        if (PluginDlteamsVehicle::canCreate()) {
            $menu['links']['add'] = PluginDlteamsVehicle::getFormURL(false);
        }
        $menu['links']['search'] = PluginDlteamsVehicle::getSearchURL(false);
        return $menu;
    }

   static function getTypeName($nb = 0) {
      return _n('Vehicle', 'Vehicles', $nb, 'dlteams');
   }

    function showForm($id, $options = []) {
      global $CFG_GLPI;
      $this->initForm($id, $options);
      $this->showFormHeader($options);

      // echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_2'><th colspan='2'>" . __("Information sur le véhicule", 'dlteams') . "</th>";
      echo "</tr>";
	  // echo "<th colspan='2'></th></tr></table>";

      echo "<tr class='tab_bg_1'>";
      echo "<td style='text-align:right'>".__('Immatriculation')."</td>";
      echo "<td>";
      echo Html::input('name', ['value' => $this->fields['name'], 'size' => 40]);
      echo "</td>";


      echo "<td style='text-align:right'>".__('Type', 'dlteams')."</td>";
      echo "<td>";
		$iterator = ['VU', 'VP'];

        Dropdown::showFromArray(
            'motor_type', // => $this->fields['motor_type'],
            $iterator,
            [   array('value' => $this->fields['motor_type'] ?? ""),
                'multiple' => false,
                // 'rand' => $rand,
                'width' => '100%',
            ]
        );
		//}
        echo "</div>";
        echo "</div>";
        echo "</td>";
      echo "</tr>";

        echo "<tr class='tab_bg_1'>";
      echo "<td style='text-align:right'>".__('Marque', 'dlteams')."</td>";
      echo "<td>";
        Manufacturer::dropdown([
            'name'      => 'manufacturers_id',
            'value'     => $this->fields['manufacturers_id'],
            'entity'    => $this->fields['entities_id'],
        ]);
      echo "</td>";
      echo "<td style='text-align:right'>".__('Modèle', 'dlteams')."</td>";
      echo "<td>";
	  PeripheralModel::dropdown([
            'name'      => 'peripheralmodels_id',
            'value'     => $this->fields['peripheralmodels_id'],
            'entity'    => $this->fields['entities_id'],
        ]);
      echo "</td>";
      echo "</tr>";



        echo "<tr class='tab_bg_1'>";
      echo "<td style='text-align:right'>".__('Motorisation', 'dlteams')."</td>";
      echo "<td>";
        $iterator = ['DIESEL', 'ESSENCE', 'HYBRIDE DIESEL'];

        Dropdown::showFromArray(
            'motor_type', // => $this->fields['motor_type'],
            $iterator,
            [   array('value' => $this->fields['motor_type'] ?? ""),
                'multiple' => false,
                'width' => '100%',
            ]
        );
      echo "</td>";
      echo "<td style='text-align:right'>".__('Le gestionnaire possède t-il le double des clé', 'dlteams')."</td>";
      echo "<td style='display: flex; align-items: center; padding-top: 13px'>";
//      echo Html::input('volume', ['value' => 1, 'size' => 10]);
        echo "<div style='display: flex; align-items: center; gap: 1rem'>";
        echo "<div>";
        echo "<input type='radio' name='doublekey' value='1' " .
            ($this->fields['doublekey'] ? "checked" : "") . "> " . __("Yes");
        echo "</div>";
        echo "<div>";
        echo "<input type='radio' name='doublekey' value='0' " .
            (!$this->fields['doublekey'] ? "checked" : "") . "> " . __("No");
        echo "</div>";
        echo "</div>";


      echo "</td>";
      echo "</tr>";

        echo "<tr class='tab_bg_1'>";
      echo "<td style='text-align:right'>".__('Commentaire', 'dlteams')."</td>";
      echo "<td>";
      echo Html::input('comment', ['value' => $this->fields['comment']]);
      echo "</td>";
      echo "<td style='text-align:right'>".__('Option de tarification', 'dlteams')."</td>";
      echo "<td  style='display: flex; align-items: center; padding-top: 13px'>";

        echo "<div style='display: flex; align-items: center; gap: 1rem'>";
        echo "<div>";
        echo "<input type='radio' name='withtax' value='1' " .
            ($this->fields['withtax'] ? "checked" : "") . "> " . __("TTC");
        echo "</div>";
        echo "<div>";
        echo "<input type='radio' name='withtax' value='0' " .
            (!$this->fields['withtax'] ? "checked" : "") . "> " . __("HT");
        echo "</div>";
        echo "</div>";

//      echo Html::input('withtax', ['value' => $this->fields['withtax'], 'size' => 10]);
      echo "</td>";
      echo "<td></td>";
      echo "</tr>";

      // echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_2'><th colspan='4'>" . __("Informations sur l'achat", 'dlteams') . "</th>";
      echo "</tr>";
	  // echo "<th colspan='2'></th></tr></table>";

      echo "<td style='text-align:right'>".__("Date d'achat")."</td>";
      echo "<td>";
      Html::showDateField("buyingdate", ['value' => $this->fields["buyingdate"], 'size' => 20, 'defaultDate' => null]);
      echo "</td>";
      echo "<td style='text-align:right'>". __('Financement', 'dlteams')."</td>";
      echo "<td>";
      echo Html::input('typeofpurchase', ['value' => $this->fields['typeofpurchase'], 'size' => 40]);
      echo "</td>";
      echo "</tr>";

      echo "<td style='text-align:right'>".__('Loyer', 'resources')."</td>";
      echo "<td>";
	  echo Html::input('rentalamount', ['value' => $this->fields['rentalamount'], 'size' => 40]);
      echo "</td>";
      echo "<td style='text-align:right'>". __("Maintenance") . "</td>";
      echo "<td>";
      echo Html::input('maintenance', ['value' => $this->fields['maintenance'], 'size' => 40]);
      echo "</td>";
      echo "<td width='15%'>". " " . "</td>";
      echo "</tr>" ;
	  
      echo "<td style='text-align:right'>".__('1er loyer', 'resources')."</td>";
      echo "<td>";
      echo Html::input('firstrental', ['value' => $this->fields['firstrental']??0, 'size' => 40]);
      echo "</td>";
      echo "<td style='text-align:right'>". __("Solde fin") . "</td>";
      echo "<td>";
      echo Html::input('lastrental', ['value' => $this->fields['lastrental'], 'size' => 40]);	  
      echo "</td>";
      echo "<td width='15%'>". " " . "</td>";
      echo "</tr>" ;

      echo "<td style='text-align:right'>". __("Garantie") . "</td>";
      echo "<td>";
      echo Html::input('guarantee', ['value' => $this->fields['guarantee'], 'size' => 40]);
      echo "</td>";
      echo "<td style='text-align:right'>". __("Reprise HT") . "</td>";
      echo "<td>";
      echo Html::input('soldprice', ['value' => isset($this->fields['soldprice'])?$this->fields['soldprice']:'', 'size' => 40]);
      echo "</td>";
      echo "<td width='15%'>". " " . "</td>";
      echo "</tr>" ;

      echo "<td style='text-align:right'>". __('Concessionnaire') . "</td>";
      echo "<td>";
	  Supplier::dropdown([
            'name'      => 'suppliers_id',
            'value'     => $this->fields['suppliers_id'],
            'entity'    => $this->fields['entities_id'],
        ]);
      echo "</td>";
      echo "<td style='text-align:right'>" . __('Durée financement') . "</td>";
      echo "<td>";
      echo Html::input('rentalperiod', ['value' => $this->fields['rentalperiod'], 'size' => 40]);
	  echo "</td></tr>\n";

      // echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_2'><th colspan='4'>" . __("Informations sur l'usager", 'dlteams') . "</th>";
	  echo "</tr>";
      // echo "<th colspan='2'></th></tr></table>";*/

      echo "<td style='text-align:right'>". User::getTypeName(1) . "</td>";
      echo "<td>";
      User::dropdown(['name'   => 'users_id',
          'value'  => $this->fields["users_id"],
          'entity' => $this->fields["entities_id"],
          'right' => 'all'
      ]);
      echo "</td>";
	  echo "<td style='text-align:right'>" . __('NB'). "</td>";
	  echo "<td>";
      echo Html::input('nb', ['value' => $this->fields['nb'], 'size' => 40]);	  
      echo "</td>";
      echo "<td width='15%'>". " " . "</td>";
      echo "</td></tr>\n";

      echo "<td style='text-align:right'>".__('Avantage en nature')."</td>";
      echo "<td>";
      echo Html::input('taxbenefit', ['value' => $this->fields['taxbenefit'], 'size' => 40]);
      echo "</td>";
      echo "<td style='text-align:right'>". __("Participation") . "</td>";
      echo "<td>";
	  echo Html::input('otherbenefit', ['value' => $this->fields['otherbenefit'], 'size' => 40]);
      echo "</td>";
      echo "<td width='15%'>". " " . "</td>";
      echo "</tr>" ;

      echo "<td style='text-align:right'>" .__('Véhicule de service pour : ') . "</td>";
      echo "<td>";
      Group::dropdown([
          'name'      => 'groups_id',
          'value'     => $this->fields['groups_id'],
          'entity'    => $this->fields['entities_id'],
      ]);
      echo "</td>\n";
	  echo "<td style='text-align:right'>".__('Lieu de garage')."</td>";
	  echo "<td>";
       Location::dropdown(['value'  => $this->fields["locations_id"],
           'entity' => $this->fields["entities_id"],
           'width' => '250px'
       ]);
       echo "</td>";
       echo "<td width='15%'>". " " . "</td>";
       echo "</tr>" ;

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
         'id'                 => '4',
         'table'              => 'glpi_plugin_dlteams_vehicletypes',
         'field'              => 'name',
         'name'               => __("Type"),
         'datatype'           => 'dropdown',
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => 'glpi_manufacturers',
         'field'              => 'name',
         'name'               => __("Const."),
         'datatype'           => 'dropdown',
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => 'glpi_peripheralmodels',
         'field'              => 'name',
         'name'               => __("Modèle"),
         'datatype'           => 'dropdown',
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this->getTable(),
         'field'              => 'motor_type',
         'name'               => __("Mot."),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '8',
         'table'              => $this->getTable(),
         'field'              => 'doublekey',
         'name'               => __("Double"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '9',
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
         'field'              => 'buyingdate',
         'name'               => __("Achat"),
         'datatype'           => 'date',
         'massiveaction'      => false,
      ];

      $tab[] = [
         'id'                 => '11',
         'table'              => $this->getTable(),
         'field'              => 'typeofpurchase',
         'name'               => __("Fin."),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

        $tab[] = [
          'id'       		  => '12',
          'table'    		  => $this->getTable(),
          'field'    		  => 'rentalamount',
          'name'     		  => __('Loyer'),
          'datatype' 		  => 'decimal'
        ];

        $tab[] = [
          'id'       		  => '13',
          'table'    		  => $this->getTable(),
          'field'    		  => 'maintenance',
          'name'     		  => __('Maint.'),
          'datatype' 		  => 'decimal'
        ];

        $tab[] = [
          'id'       		  => '14',
          'table'    		  => $this->getTable(),
          'field'    		  => 'firstrental',
          'name'     		  => __('1er Loyer'),
          'datatype' 		  => 'decimal'
        ];

        $tab[] = [
          'id'       		  => '15',
          'table'    		  => $this->getTable(),
          'field'    		  => 'lastrental',
          'name'     		  => __('Solde ttc'),
          'datatype' 		  => 'decimal'
        ];

      $tab[] = [
         'id'                 => '16',
         'table'              => $this->getTable(),
         'field'              => 'withtax',
         'name'               => __("ht/ttc"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];


//        $tab[] = [
//          'id'       		  => '17',
//          'table'    		  => $this->getTable(),
//          'field'    		  => 'soldprice',
//          'name'     		  => __('Reprise ht'),
//          'datatype' 		  => 'decimal'
//        ];

       $tab[] = [
           'id'               => '18',
           'table'            => Supplier::getTable(),
           'field'            => 'name',
           'name'             => __("Concess."),
           'datatype'         => 'dropdown',
           'massiveaction'    => true,
       ];

      $tab[] = [
         'id'                 => '19',
         'table'              => $this->getTable(),
         'field'              => 'rentalperiod',
         'name'               => __("Durée"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '20',
         'table'              => 'glpi_users',
         'field'              => 'realname',
         'name'               => __("Nom"),
         'datatype'           => 'dropdown',
         'massiveaction'      => true,
      ];
      $tab[] = [
         'id'                 => '21',
         'table'              => 'glpi_users',
         'field'              => 'firstname',
         'name'               => __("Prenom"),
         'datatype'           => 'dropdown',
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '22',
         'table'              => $this->getTable(),
         'field'              => 'nb',
         'name'               => __("NB"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

        $tab[] = [
          'id'       		  => '23',
          'table'    		  => $this->getTable(),
          'field'    		  => 'taxbenefit',
          'name'     		  => __('Avantage'),
          'datatype' 		  => 'decimal'
        ];

        $tab[] = [
          'id'       		  => '24',
          'table'    		  => $this->getTable(),
          'field'    		  => 'otherbenefit',
          'name'     		  => __('Partic.'),
          'datatype' 		  => 'decimal'
        ];


      $tab[] = [
         'id'                 => '30',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __("Entity"),
         'datatype'           => 'dropdown',
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '31',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __("Child entities"),
         'datatype'           => 'bool',
         'massiveaction'      => false,
      ];

       $tab[] = [
           'id'                 => '50',
           'table'              => Location::getTable(),
           'field'              => 'completename',
           'name'               => __("Garage"),
           'datatype'           => 'dropdown',
           'massiveaction'      => true,
       ];

       $tab = array_merge($tab, Location::rawSearchOptionsToAdd());

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

   public function defineTabs($options = []) {
      $ong = [];
      $ong = array();
      //add main tab for current object
      $this->addDefaultFormTab($ong)
	  // ->addStandardTab('PluginDlteamsDataCatalog_Item', $ong, $options)
	  // ->addStandardTab('PluginDlteamsVehicle_Item', $ong, $options)
	  ->addStandardTab('PluginDlteamsObject_document', $ong, $options)
      ->addStandardTab('ManualLink', $ong, $options)
      ->addStandardTab(Location::class, $ong, $options)
      ->addStandardTab(PluginDlteamsTicket_Item::class, $ong, $options)
      ->addImpactTab($ong, $options)
      ->addStandardTab('Notepad', $ong, $options)
      ->addStandardTab('Log', $ong, $options);
      return $ong;
   }

   function exportToDB($subItems = []) {
      if ($this->isNewItem()) {
         return false;
      }
      $export = $this->fields;
      return $export;
   }

    public static function importToDB(PluginDlteamsLinker $linker, $input = [], $containerId = 0, $subItems = []) {
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
