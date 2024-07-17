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

class PluginDlteamsForm extends CommonDropdown implements
    PluginDlteamsExportableInterface {
    use PluginDlteamsExportable;
    static $rightname = 'plugin_dlteams_form';
    public $dohistory = true;
    protected $usenotepad = true;

    static function getMenuName() {
        return _n('Formulaire', 'Formulaires', 2, 'dlteams');
    }

    public function __construct()
    {
        self::forceTable(PluginFormcreatorForm::getTable());
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

    public static function getTable($classname = null)
    {
        return PluginFormcreatorForm::getTable();
    }
//    static function getMenuContent() {
//        $physicalstorageFormUrl = "/".Plugin::getWebDir('dlteams', false).'/front/accessopening.php';
//        $menu = [
//            'title' => self::getMenuName(),
//            'page'  => $physicalstorageFormUrl,
//            'icon'  => 'fas fa-window-maximize',
//        ];
//
//        if (PluginDlteamsAccessOpening::canCreate()) {
//            $menu['links']['add'] = PluginDlteamsAccessOpening::getFormURL(false);
//        }
//        $menu['links']['search'] = PluginDlteamsAccessOpening::getSearchURL(false);
//        return $menu;
//    }

    static function getTypeName($nb = 0) {
        return _n('Formulaire', 'Formulaires', $nb, 'dlteams');
    }

    function showForm($id, $options = []) {
        global $CFG_GLPI;
        $this->initForm($id, $options);
        $this->showFormHeader($options);

        // echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_2'><th colspan='2'>" . __("Information sur l'accès ou l'ouverture", 'dlteams') . "</th>";
        echo "</tr>";
        // echo "<th colspan='2'></th></tr></table>";

        echo "<tr class='tab_bg_1'>";
        echo "<td style='text-align:right'>".__('Désignation')."</td>";
        echo "<td>";
        echo Html::input('name', ['value' => $this->fields['name'], 'size' => 40]);
        echo "</td>";


//        echo "<td style='text-align:right'>".__('Type', 'dlteams')."</td>";
//        echo "<td>";
//        $iterator = ['VU', 'VP'];
//
//        Dropdown::showFromArray(
//            'motor_type', // => $this->fields['motor_type'],
//            $iterator,
//            [   array('value' => $this->fields['motor_type'] ?? ""),
//                'multiple' => false,
//                // 'rand' => $rand,
//                'width' => '100%',
//            ]
//        );
//        //}
//        echo "</div>";
//        echo "</div>";
//        echo "</td>";
        echo "</tr>";



//        echo "<tr>";
//        echo "<td style='text-align:right'>".__('Lieu')."</td>";
//        echo "<td>";
//        Location::dropdown(['value'  => $this->fields["locations_id"],
//            'entity' => $this->fields["entities_id"],
//            'width' => '250px'
//        ]);
//        echo "</td>";
//        echo "<td width='15%'>". " " . "</td>";
//        echo "</tr>" ;

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

        $pfc = new PluginFormcreatorForm();
        $tab = array_merge($tab, $pfc->rawSearchOptions());

        $tab[] = [
            'id'                 => '1',
            'table'              => $this::getTable(),
            'field'              => 'name',
            'name'               => __('Name'),
            'datatype'           => 'itemlink',
            'massiveaction'      => false
        ];

        return $tab;
    }


    public function defineTabs($options = []) {
        $ong = [];
        $ong = array();
        //add main tab for current object
        $this->addDefaultFormTab($ong)
            // ->addStandardTab('PluginDlteamsDataCatalog_Item', $ong, $options)
            // ->addStandardTab('PluginDlteamsVehicle_Item', $ong, $options)
            ->addStandardTab(PluginDlteamsLocation_Item::class, $ong, $options)
            ->addStandardTab(PluginDlteamsProtectiveMeasure_Item::class, $ong, $options)
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
