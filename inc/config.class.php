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

class PluginDlteamsConfig extends CommonGLPI
{
    static $rightname = 'plugin_dlteams_record';

    /**
     * Data fields of the Item.
     *
     * @var mixed[]
     */
    public $fields = [];

//    /**
//     * Add/Update fields input. Filled during add/update process.
//     *
//     * @var mixed[]
//     */
//    public $input = [];
//
//    /**
//     * Updated fields keys. Filled during update process.
//     *
//     * @var mixed[]
//     */
//    public $updates = [];
//
//    /**
//     * Previous values of updated fields. Filled during update process.
//     *
//     * @var mixed[]
//     */
//    public $oldvalues = [];
//
//
//    /**
//     * Flag to determine whether or not changes must be logged into history.
//     *
//     * @var boolean
//     */
//    public $dohistory = false;
//
//    /**
//     * List of fields that must not be taken into account when logging history or computating last
//     * modification date.
//     *
//     * @var string[]
//     */
//    public $history_blacklist = [];
//
//    /**
//     * Flag to determine whether or not automatic messages must be generated on actions.
//     *
//     * @var boolean
//     */
//    public $auto_message_on_action = true;
//
//    /**
//     * Flag to determine whether or not a link to item form can be automatically generated via
//     * self::getLink() method.
//     *
//     * @var boolean
//     */
//    public $no_form_page = false;
//
//    /**
//     * Flag to determine whether or not table name of item can be automatically generated via
//     * self::getTable() method.
//     *
//     * @var boolean
//     */
//    protected static $notable = false;
//
//    /**
//     * List of fields that must not be taken into account for dictionary processing.
//     *
//     * @var string[]
//     */
//    public $additional_fields_for_dictionnary = [];
//
//    /**
//     * List of linked item types on which entities information should be forwarded on update.
//     *
//     * @var string[]
//     */
//    protected static $forward_entity_to = [];
//
//    /**
//     * Foreign key field cache : set dynamically calling getForeignKeyField
//     *
//     * @TODO Remove this variable as it is not used ?
//     */
//    protected $fkfield = "";
//
//    /**
//     * Search option of item. Initialized on first call to self::getOptions() and used as cache.
//     *
//     * @var array
//     *
//     * @TODO Should be removed and replaced by real cache usage.
//     */
//    protected $searchopt = false;
//
//    /**
//     * {@inheritDoc}
//     */
//    public $taborientation = 'vertical';
//
//    /**
//     * {@inheritDoc}
//     */
//    public $get_item_to_display_tab = true;
//
//    /**
//     * List of linked item types from plugins on which entities information should be forwarded on update.
//     *
//     * @var array
//     */
//    protected static $plugins_forward_entity = [];
//
//    /**
//     * Flag to determine whether or not table name of item has a notepad.
//     *
//     * @var boolean
//     */
//    protected $usenotepad = false;
//
//    /**
//     * Flag to determine whether or not queued notifications should be deduplicated.
//     * Deduplication is done when a new notification is raised.
//     * Any existing notification for same object, event and recipient is dropped to be replaced by the new one.
//     *
//     * @var boolean
//     */
//    public $deduplicate_queued_notifications = true;
//
//    /**
//     * Computed/forced values of classes tables.
//     * @var string[]
//     */
//    protected static $tables_of = [];
//
//    /**
//     * Computed values of classes foreign keys.
//     * @var string[]
//     */
//    protected static $foreign_key_fields_of = [];
//
//
//    /**
//     * Fields to remove when querying data with api
//     * @var array
//     */
//    public static $undisclosedFields = [];
//
//    /**
//     * Current right that can be evaluated in "item_can" hook.
//     * Variable is set prior to hook call then unset.
//     * @var int
//     */
//    public $right;

    protected $core_tcpdf_fonts = [
        'courier' => 'Courier',
        'courierB' => 'Courier-Bold',
        'courierI' => 'Courier-Oblique',
        'courierBI' => 'Courier-BoldOblique',
        'dejavusans' => 'DejaVu Sans',
        'helvetica' => 'Helvetica',
        'helveticaB' => 'Helvetica-Bold',
        'helveticaI' => 'Helvetica-Oblique',
        'helveticaBI' => 'Helvetica-BoldOblique',
        'times' => 'Times-Roman',
        'timesB' => 'Times-Bold',
        'timesI' => 'Times-Italic',
        'timesBI' => 'Times-BoldItalic',
        'symbol' => 'Symbol',
        'zapfdingbats' => 'ZapfDingbats'
    ];

    public static function getTypeName($nb = 0)
    {
        return _n("Config", "Config", $nb, 'dlteams');
    }

    static function getConfigDefault()
    {
        $config = [];

        $config['system'] = [
            'keep_is_special_category_strict' => 1,
            'limit_retention_contracttypes' => 1,
            'remove_software_when_paper_only' => 1,
            'allow_select_expired_contracts' => 1,
            'allow_software_from_every_entity' => 0,
            'allow_controllerinfo_from_ancestor' => 1,
        ];
        $config['print'] = [
            'codepage' => 'UTF-8',
            'font_name' => 'dejavusans',
            'font_size' => 8,
            'margin_left' => 10,
            'margin_top' => 30,
            'margin_right' => 10,
            'margin_bottom' => 20,
            'margin_header' => 10,
            'margin_footer' => 10,
            'logo_show' => 1,
            'logo_image' => 'dlteams_logo.png',
        ];
        return $config;
    }

    /**
     * Return the table used to store this object
     *
     * @param string $classname Force class (to avoid late_binding on inheritance)
     *
     * @return string
     **/
    public static function getTable($classname = null)
    {

        return 'glpi_plugin_dlteams_configs';
    }

    /**
     * Get an object using some criteria
     *
     * @param array $crit search criteria
     *
     * @return boolean|array
     * @since 9.2
     *
     */
    public function getFromDBByCrit(array $crit)
    {
        /** @var \DBmysql $DB */
        global $DB;

        $crit = ['SELECT' => 'id',
            'FROM' => $this->getTable(),
            'WHERE' => $crit
        ];

        $iter = $DB->request($crit);
        if (count($iter) == 1) {
            $row = $iter->current();
            return $this->getFromDB($row['id']);
        } else if (count($iter) > 1) {
            trigger_error(
                sprintf(
                    'getFromDBByCrit expects to get one result, %1$s found in query "%2$s".',
                    count($iter),
                    $iter->getSql()
                ),
                E_USER_WARNING
            );
        }
        return false;
    }

    /**
     * Retrieve an item from the database
     *
     * @param integer $ID ID of the item to get
     *
     * @return boolean true if succeed else false
     **/
    public function getFromDB($ID)
    {
        /** @var \DBmysql $DB */
        global $DB;
        // Make new database object and fill variables

        // != 0 because 0 is considered as empty
        if (strlen((string)$ID) == 0) {
            return false;
        }

        $iterator = $DB->request([
            'FROM' => $this->getTable(),
            'WHERE' => [
                $this->getTable() . '.' . $this->getIndexName() => Toolbox::cleanInteger($ID)
            ],
            'LIMIT' => 1
        ]);


        if (count($iterator) == 1) {
            $this->fields = $iterator->current();
//            $this->post_getFromDB();
            return true;
        } else if (count($iterator) > 1) {
            trigger_error(
                sprintf(
                    'getFromDB expects to get one result, %1$s found in query "%2$s".',
                    count($iterator),
                    $iterator->getSql()
                ),
                E_USER_WARNING
            );
        }

        return false;
    }

    /**
     * Get the name of the index field
     *
     * @return string name of the index field
     **/
    public static function getIndexName()
    {
        return "id";
    }


    static function getConfig($key = '', $key2 = '')
    {
        $config = new PluginDlteamsConfig();
        $config->getFromDBByCrit(['entities_id' => 0]);

        if (isset($config->fields['id'])) {
            $config = importArrayFromDB($config->fields['config']);
            if (!empty($key)) {
                if (!empty($key2)) {
                    return $config[$key][$key2];
                } else {
                    return $config[$key];
                }
            } else {
                return $config;
            }
        } else {
            // get default config
            $default = PluginDlteamsConfig::getConfigDefault();

            if (!empty($key)) {
                if (!empty($key2)) {
                    return $default[$key][$key2];
                } else {
                    return $default[$key];
                }
            } else {
                return $default;
            }
        }
    }

    private function prepareJSON($input)
    {
        $array = [];
        foreach (PluginDlteamsConfig::getConfigDefault() as $key => $value) {
            $array[$key] = $input[$key];
            unset($input[$key]);
        }
        $input['config'] = exportArrayToDB($array);
        return $input;
    }

    function prepareInputForAdd($input)
    {
        $input = $this->prepareJSON($input);
        $input['users_id_creator'] = Session::getLoginUserID();
        return parent::prepareInputForAdd($input);
    }

    function prepareInputForUpdate($input)
    {
        $input = $this->prepareJSON($input);
        $input['users_id_lastupdater'] = Session::getLoginUserID();
        return parent::prepareInputForUpdate($input);
    }

    // dossier pour les fichiers export
    function prepareFolderExport()
    {
        $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
        if (!file_exists($glpiRoot . "/files/_plugins/" . "dlteams" . "/")) {
            mkdir($glpiRoot . "/files/_plugins/" . "dlteams" . "/");
            chmod($glpiRoot . "/files/_plugins/" . "dlteams" . "/", 0310);
        }
        return parent::prepareFolderExport();
    }

    public static function canUpdate()
    {
        return true;
    }

    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addStandardTab(__CLASS__, $ong, $options);

        return $ong;
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if ($item->getType() == __CLASS__) {
            $tabs = [
                0 => __('Général'),
                1 => __('Entité modèle'),
                2 => __('Import / Export'),
                3 => __('Mises a jour Dlteams & opérations base de données'),
                4 => __('Erreurs sql'),
            ];

            return $tabs;
        }
        return '';
    }

    public function showForm($ID, $options = [])
    {
        global $DB;
        global $CFG_GLPI;

        if (!self::canUpdate()) {
            return;
        }

        $this->getFromDBByCrit(['entities_id' => 0]);
        $config = self::getConfig();

        echo "<div class='center' width='50%'>";
        echo "<form method='post' action='./config.form.php'>";
        echo "<table class='tab_cadre' cellpadding='5' width='50%'>";
        echo "<tr>";
        echo "<th colspan='2'>" . __("Manage GDPR RoPA configuration", 'dlteams') . "</th>";
        echo "</tr>";
		
	date_default_timezone_set('Europe/Paris');

	if (date_default_timezone_get()) {
    echo 'date_default_timezone_set: ' . date_default_timezone_get() . '<br />';
	}

	if (ini_get('date.timezone')) {
    echo 'date.timezone: ' . ini_get('date.timezone');
	}

    /*echo "<tr>";
    echo "<td colspan='2' class='center b'>" . __("System configuration", 'dlteams') . "</td>";
    echo "</tr>";
    echo "<tr>";
    echo "<td width='80%'>" . __("Keep 'is special category' strict", 'dlteams') . "</td>";
    echo "<td width='20%'>";

        dropdown::showYesNo('system[keep_is_special_category_strict]', $config['system']['keep_is_special_category_strict']);
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>" . __("Limit retention contracts list to those selected in controller info", 'dlteams') . "</td>";
        echo "<td>";

        dropdown::showYesNo('system[limit_retention_contracttypes]', $config['system']['limit_retention_contracttypes']);
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>" . __("Remove sofrware when record storage medium set to Paper only if any was assigned previously", 'dlteams') . "</td>";
        echo "<td>";

        dropdown::showYesNo('system[remove_software_when_paper_only]', $config['system']['remove_software_when_paper_only']);
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>" . __("Allow add expired contract (show expired on dropdown list)", 'dlteams') . "</td>";
        echo "<td>";

        dropdown::showYesNo('system[allow_select_expired_contracts]', $config['system']['allow_select_expired_contracts']);
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>" . __("Allow add software from any entity", 'dlteams') . "</td>";
        echo "<td>";

        dropdown::showYesNo('system[allow_software_from_every_entity]', $config['system']['allow_software_from_every_entity']);
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>" . __("Allow retrieve controller info from ancestor entity (set as recursive) when current entity controller info is not set", 'dlteams') . "</td>";
        echo "<td>";

        dropdown::showYesNo('system[allow_controllerinfo_from_ancestor]', $config['system']['allow_controllerinfo_from_ancestor']);
        echo "</td>";
        echo "</tr>";*/
    
    echo "<tr>";
    echo "<td colspan='2' class='center b'>" . __("PDF creating configuration", 'dlteams') . "</td>";
    echo "</tr>";

    echo "<tr>";
    echo "<td>" . __("Codepage", 'dlteams') . "</td>";
    echo "<td>";

        Dropdown::showFromArray('print[codepage]', ['UTF-8', 'ISO-8859-1', 'ISO-8859-2'], [
        'value' => $config['print']['codepage']
    ]);
    echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Font name", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showFromArray('print[font_name]', $this->core_tcpdf_fonts, [
            'value' => $config['print']['font_name']
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Font size", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showNumber('print[font_size]', [
            'value' => $config['print']['font_size'],
            'min' => 8,
            'max' => 16,
            'step' => 1,
            'unit' => 'pt',
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Margin left", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showNumber('print[margin_left]', [
            'value' => $config['print']['margin_left'],
            'min' => 5,
            'max' => 50,
            'unit' => 'mm',
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Margin top", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showNumber('print[margin_top]', [
            'value' => $config['print']['margin_top'],
            'min' => 5,
            'max' => 50,
            'unit' => 'mm',
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Margin right", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showNumber('print[margin_right]', [
            'value' => $config['print']['margin_right'],
            'min' => 5,
            'max' => 50,
            'unit' => 'mm',
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Margin bottom", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showNumber('print[margin_bottom]', [
            'value' => $config['print']['margin_bottom'],
            'min' => 5,
            'max' => 50,
            'unit' => 'mm',
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Header margin (from top)", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showNumber('print[margin_header]', [
            'value' => $config['print']['margin_header'],
            'min' => 10,
            'max' => 30,
            'unit' => 'mm',
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Footer margin (from bottom)", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showNumber('print[margin_footer]', [
            'value' => $config['print']['margin_footer'],
            'min' => 10,
            'max' => 30,
            'unit' => 'mm',
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Show logo", 'dlteams') . "</td>";
        echo "<td>";

        dropdown::showYesNo('print[logo_show]', $config['print']['logo_show']);
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>" . __("Logo image filename (located in /plugins/dlteams/images/)", 'dlteams') . "</td>";
        echo "<td>";

        echo "<input type='text' maxlength='254' name='print[logo_image]' value=\"" . $config['print']['logo_image'] . "\">";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'>";
        echo "<div class='center'>";

        echo "<input type='hidden' name='entities_id' value=\"" . 0 . "\">";

        $value = 0;
        if (isset($this->fields['id'])) {
            $value = $this->fields['id'];
        }
        echo "<input type='hidden' name='id' value=\"" . $value . "\">";
        if (isset($this->fields['id'])) {
            $action = 'update';
        } else {
            $action = 'add';
        }
        echo "<input type='submit' name='" . $action . "' value=\"" . _sx('button', 'Save') . "\" class='submit'>";
        echo "</div>";
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        Html::closeForm();
        echo "</div>";
        echo "<br><br>";

        ///////////////////////////////////////////Importation de données
		echo "<table class='tab_cadre' cellpadding='5' width='50%'>";
        echo "<tr>";
        echo "<th colspan='2'><center>" . __("Importation de données", 'dlteams') . "</th>";
        echo "</tr>";

        // importer des utilisateurs Microsoft 365
		/* // cela vient du fichier clientinjection du plug'in injection
		$models = PluginDatainjectionModel::getModels(
          Session::getLoginUserID(), 'name',
          $_SESSION['glpiactive_entity'], false
		);
		
		if (count($models) > 0) {
          echo "<td class='center'>".__('Model')."&nbsp;";
		PluginDatainjectionModel::dropdown();}
		 
		if (PluginDatainjectionSession::getParam('models_id')) {
		$p['models_id'] = PluginDatainjectionSession::getParam('models_id');}
		*/
        echo "<form method='post'  action='./import_m365.php' enctype='multipart/form-data'>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Sélectionner le fichier .csv issu de l'export Entra Id", 'dlteams') . "</td>";
        echo "<td>";
		
		echo "<tr class='tab_bg_1'>";
		echo "<td>" . __('Choose a file', 'datainjection') . "</td>";
		echo "<td><input type='file' name='filename'>";
		echo "<input type='hidden' name='id' value='"."'>";
		echo "</td></tr>";
		echo "<td>";
		// echo "<th colspan='2'>".sprintf(__('%1$s: %2$s'), __('Model'), $model->fields['name'])."</th>";
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
		// Vérifier si un fichier a été téléchargé
			if(isset($_FILES['filename']) && $_FILES['filename']['error'] == UPLOAD_ERR_OK) {
				// Récupérer le nom du fichier
				$filename = $_FILES['filename']['name'];
				// Afficher le nom du fichier
				echo "Nom du fichier choisi : " . $filename;
			} else {
				// Si aucun fichier n'a été téléchargé ou s'il y a une erreur
				echo "Aucun fichier sélectionné ou une erreur est survenue lors du téléchargement.";
			}
		}
		// var_dump ($filename);die;
        //echo "<input type='file' id='ctrl' name='files_to_import[]' placeholder='Choisissez un dossier' webkitdirectory directory multiple/>";
        echo "</td> </tr>";

        echo "<td>" . __("Indiquer l'entité cible", 'dlteams') . "</td>";
        echo "<td>";
        Entity::dropdown(['name' => 'entities_id_target', 'width' => '250px']);
        echo "</td> </tr>";

        echo "<tr class='tab_bg_1'> <td colspan='2'> <div class='center'>";
		echo "<input type='submit' name='import_m365' value=\"" . __("Importer des utilisateurs", 'dlteams') . "\" class='submit'>";
        echo "</div></td></tr>";
        Html::closeForm();

        ///////////////////////////////////////////Transfert massif de données entre entités
        echo "<table class='tab_cadre' cellpadding='5' width='50%'>";
        echo "<tr>";
        echo "<th colspan='2'><center>" . __("Transfert massif de données entre entités", 'dlteams') . "</th>";
        echo "</tr>";

        // duplicate records from an entity to an other
        echo "<tr class='tab_bg_1'> <td colspan='2'><strong>" . __("Copier l'ensemble des traitements + les éléments liés", 'dlteams') . "</strong></td> </tr>";

        // dupliquer les traitements
//        echo "<form method='post' action='./duplicate_recordsrgpd.php'>";
//        echo "<tr class='tab_bg_1'>";
//        echo "<td>" . __("Sélectionner l'entité source ", 'dlteams') . "</td>";
//        echo "<td>";
//        Entity::dropdown(['name' => 'entities_id_origin', 'width' => '250px']) . "</td>";
//        $entities_id_origin = "origin";
//        echo "</td> </tr>";
//        echo "<td>" . __("Indiquer l'entité cible", 'dlteams') . "</td>";
//        echo "<td>";
//        Entity::dropdown(['name' => 'entities_id_target', 'width' => '250px']);
//        $entities_id_target = "target";
//        echo "</td> </tr>";
//        echo "<tr class='tab_bg_1'> <td colspan='2'> <div class='center'>";
//        echo "<input type='hidden' id='entities_id_origin' name='entities_id_origin' value=$entities_id_origin><input type='hidden' id='entities_id_target' name='entities_id_target' value=$entities_id_target><input type='submit' name='export_recordsrgpd' value=\"" . __("Dupliquer Traitements", 'dlteams') . "\" class='submit'>";
//        echo "</div></td></tr>";
//        Html::closeForm();

        // exporter les traitements
        echo "<form method='post' action='./export_recordsrgpd.php'>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Sélectionner l'entité à exporter", 'dlteams') . "</td>";
        echo "<td>";
        Entity::dropdown(['name' => 'entities_id_origin', 'width' => '250px']) . "</td>";
//        $entities_id_origin = "origin";
        echo "</td> </tr>";
        echo "<td>" . __("Indiquer le dossier cible", 'dlteams') . "</td>";
        echo "<td>";
        Link::dropdown(['name' => 'folder_id_target', 'width' => '250px']);
//        $folder_id_target = "target";
        echo "</td> </tr>";
        echo "<tr class='tab_bg_1'> <td colspan='2'> <div class='center'>";
        echo "<input type='submit' name='export_recordsrgpd' value=\"" . __("Exporter Traitements", 'dlteams') . "\" class='submit'>";
        echo "</div></td></tr>";
        Html::closeForm();

        // importer les traitements
        echo "<form method='post'  action='./import_recordsrgpd.php' enctype='multipart/form-data'>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Sélectionner le dossier source", 'dlteams') . "</td>";
        echo "<td>";
        echo "<input type='file' id='ctrl' name='files_to_import[]' placeholder='Choisissez un dossier' webkitdirectory directory multiple/>";

        echo "</td> </tr>";
        echo "<td>" . __("Indiquer l'entité cible", 'dlteams') . "</td>";
        echo "<td>";
        Entity::dropdown(['name' => 'entities_id_target', 'width' => '250px']);

        echo "</td> </tr>";
        echo "<tr class='tab_bg_1'> <td colspan='2'> <div class='center'>";
//        echo "<input type='submit' name='import_recordsrgpd' value=\"" . __("Importer Traitements", 'dlteams') . "\" class='submit'>";
        echo "</div></td></tr>";
        Html::closeForm();


        // duplicate a project from an entity to an other
        echo "<form method='post' action='./import_projectrgpd_model.php'>";
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'><strong>" . __("Copier un projet, les tâches et événements liés)", 'dlteams') . "</strong></td>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Sélectionner le projet source ", 'dlteams') . "</td>";
        echo "<td>";
        Project::dropdown(['name' => 'projects_id', 'width' => '250px']);
        echo "</td>";
        echo "<tr class='tab_bg_1'> <td>" . __("Indiquer l'entité cible", 'dlteams') . "</td>";
        echo "<td>";
        Entity::dropdown(['name' => 'entities_id', 'width' => '250px']);
        echo "</td></tr>";
        echo "<tr class='tab_bg_1'> <td colspan='2'> <div class='center'>";
        echo "<input type='submit' name='import_project' value=\"" . __("Copier Projet", 'dlteams') . "\" class='submit'>";
        echo "</div></td></tr>";
        Html::closeForm();
        // duplicate forms from an entity to an other

//        copier les formulaires

//        copier les livrables
        echo "</table>";

//      echo "<form method='post' action='./config.form.php'>";

//        copier les données de l'entité model


    // echo "<form method='post' action='./config.form.php'>";
    // var_dump ("currententity_id", $currententity_id, "rgpdmodel_id", $rgpdmodel_id);
    // import model-rgpd objects datas 			// var_dump ($mandatory_sites);

	// profil N°4 = super-admin
	$activ_profil = $_SESSION['glpiactiveprofile']["id"];

	// Copier les fichiers des dossiers fork et whitelabel
     echo "<table class='tab_cadre' cellpadding='5' width='50%'>";
            echo "<tr>";
            echo "<th colspan='2'> <center>" . __("Implémenter Fork et WhiteLabel", 'dlteams') . "</th>";
            echo "</tr>";

	if ($activ_profil == 4) {
        echo "<form method='post' action='./fork.php'>";
		// echo "<form method='post' action=\"" . Html::showSimpleForm(static::getFormURL(),'test_ldap_replicate',_sx('button', 'Test'),['champ1', 'champ2']) . "\">";
		// echo "<form method='post' action=\"" . static::getFormURL() . "\">";
        echo "<tr class='tab_bg_1'> ";
        echo "<td colspan='2'> <div class='center'>";
        echo "<input type='submit' name='fork_on' value=\"" . __("Activer fork", 'dlteams') . "\" class='submit'>";
        echo "</div> </td> </tr>";

        echo "<tr class='tab_bg_1'> ";
        echo "<td colspan='2'> <div class='center'>";
        echo "<input type='submit' name='fork_off' value=\"" . __("Désactiver fork", 'dlteams') . "\" class='submit'>";
        echo "</div> </td> </tr>";

        // echo "<form method='post' action='./fork.php'>";
        echo "<tr class='tab_bg_1'> ";
        echo "<td colspan='2'> <div class='center'>";
        echo "<input type='submit' name='whitelabel_on' value=\"" . __("Activer whitelabel", 'dlteams') . "\" class='submit'>";
        echo "</div> </td> </tr>";

        echo "<tr class='tab_bg_1'> ";
        echo "<td colspan='2'> <div class='center'>";
        echo "<input type='submit' name='whitelabel_off' value=\"" . __("Désactiver whitelabel", 'dlteams') . "\" class='submit'>";
        echo "</div> </td> </tr>";

        echo "</table>";
        Html::closeForm();
	}

	// for Super-Admin only
	/*    $activ_profil = $_SESSION['glpiactiveprofile']["id"];
    if ($activ_profil == 4 && $mandatory_sites == 0) {
            // model-rgpd entite and datas
            echo "<table class='tab_cadre' cellpadding='5' width='50%'>";
            echo "<tr>";
            echo "<th colspan='2'> <center>" . __("Forker GLPI)", 'dlteams') . "</th>";
            echo "</tr>";

            echo Html::submit(_x('button', 'Copier fork'), ['name' => 'specific_whitelabel_on', 'class' => 'btn-sm btn btn-primary']);
            // if($can_copy_origin)
            echo Html::submit(_x('button', 'Copier original'), ['name' => 'massiveaction', 'class' => 'btn-sm btn btn-primary']);
            echo "</td></tr>";

            echo Html::submit(_x('button', 'Copier fork'), ['name' => 'specific_whitelabel_on', 'class' => 'btn-sm btn btn-primary']);
            // if($can_copy_origin)
            echo Html::submit(_x('button', 'Copier original'), ['name' => 'massiveaction', 'class' => 'btn-sm btn btn-primary']);
            echo "</td></tr>";
            Html::closeForm();
    }

	function copyfiles ($source, $target){
        echo "</table></div>";
		// Application du fork dlteams
        echo "<div class='spaced center' style='width: 100%'>";
        echo "<table class='tab_cadrehov table-striped table-hover' style='width: 50%'>";
        echo "<tr><th colspan='5'>" . __('Fork 10.0.14') . "</th></tr>";

        echo "<tr class='tab_bg_2'>";
        echo "<td class='b'>" . _n('Fichier', 'Criteria', 1) . "</td>";
        echo "<td class='b'>" . __('Version installée') . "</td>";
        echo "<td class='b'>" . "" . "</td>";
        echo "</tr>\n";

        $directory = GLPI_ROOT . "/marketplace/dlteams/install/fork/" . "10.0.14" . "/fork_dlteams";
        $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
        $files = array();
        foreach ($rii as $file) {
            if ($file->isDir()) {
                continue;
            }
            $files[] = $file->getPathname();
        }
        foreach ($files as $path) {
		// $resp = copy($path, GLPI_ROOT . str_replace($directory, "", $path));
            echo "<form method='post' action='./whitelabel.php'>";
            echo "<tr class='tab_bg_1'>";
            $origin_path = str_replace("fork_dlteams", "origin_glpi", $path);
            $can_copy_origin = true;
            if(!file_exists($origin_path)){
                $origin_path = "<i class='fa fa-warning text-warning'></i>";
                $can_copy_origin = false;
            }
            else
                $origin_path = str_replace(GLPI_ROOT . "/marketplace/dlteams/install/fork/10.0.14", "", $path);
			}
		
            echo "<input type='hidden' name='origin' value=''>";
            echo "<td>" . $origin_path . "</td>";
            echo "<td >" .str_replace(GLPI_ROOT . "/marketplace/dlteams/install/fork/10.0.14", "", $path). "</td>";
            echo "<td style='white-space: nowrap'>";
	}*/

	/*
	// TODO: decommenter pour faire afficher le bouton de mise a jour manuel
	echo "<table class='tab_cadre' cellpadding='5' width='50%'>";
	echo "<tr class='tab_bg_1'>";
	echo "<td colspan='2'><strong>" . __("Mise a jour", 'dlteams') . "</strong></td>";
	echo "</tr>";
    echo "<tr class='tab_bg_1'> <td colspan='2'> <div class='center'>";
    echo "<form method='post' action='". "/marketplace/dlteams/install/install.php"."'><input type='hidden' name='redirect' value='true'/> <input type='submit' name='import_project' value=\"" . __("Mettre a jour maintenant", 'dlteams') . "\" class='submit'>";Html::closeForm();
    echo "</div></td></tr>";
    echo "</table>";
    // end mise a jour manuel*/

	/*echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'><strong>" . __("Import vers l'entité courante", 'dlteams') . "</strong></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Personnes concernées par ce traitement et catégories de données", 'dlteams') . "</td>";
        echo "<td>";
        Html::showCheckbox([
            'name'  => 'migrate_categories_of_data_subjects',
            'title' => __("Categories of data subjects", 'dlteams'),
            'checked' => 1]);
        echo "</td>";
        echo "</tr>";*/
		
        /*echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Legality and retention period", 'dlteams') . "</td>";
        echo "<td>";
        Html::showCheckbox([
           'name'  => 'migrate_legal_bases',
           'title' => __("Legal bases", 'dlteams'),
           'checked' => 0]);
        echo "</td>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Security measures", 'dlteams') . "</td>";
        echo "<td>";
        Html::showCheckbox([
           'name'  => 'migrate_security_measures',
           'title' => __("Security measures", 'dlteams'),
           'checked' => 0]);
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Impact", 'dlteams') . "</td>";
        echo "<td>";
        Html::showCheckbox([
           'name'  => 'migrate_impact',
           'title' => __("Impact", 'dlteams'),
           'checked' => 0]);
        echo "</td>";
        echo "</tr>";*/



}




//    public function addDefaultFormTab()
//    {
////        var_dump("zzz");
////        die();
//        $ong = [];
//        $ong = array();
//        //add main tab for current object
//        $this->addStandardTab('Notepad', $ong, $options)
//            ->addStandardTab('Log', $ong, $options);
//        return $ong;
//    }

    static function listFiles($accessToken) {
            $url = 'https://www.googleapis.com/drive/v3/files';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $files = json_decode($response, true);

            return $files;
        }


    public function showGeneral(){
        global $DB;
        global $CFG_GLPI;

        if (!self::canUpdate()) {
            return;
        }

//        $this->getFromDBByCrit(['entities_id' => 0]);
        $config = self::getConfig();

        echo "<div class='center' width='50%'>";
        echo "<form method='post' action='./config.form.php'>";
        echo "<table class='tab_cadre' cellpadding='5' width='50%'>";
        echo "<tr>";
        echo "<th colspan='2'>" . __("Manage GDPR RoPA configuration", 'dlteams') . "</th>";
        echo "</tr>";

        date_default_timezone_set('Europe/Paris');

        if (date_default_timezone_get()) {
            echo 'date_default_timezone_set: ' . date_default_timezone_get() . '<br />';
        }

        if (ini_get('date.timezone')) {
            echo 'date.timezone: ' . ini_get('date.timezone');
        }

        echo "<tr>";
        echo "<td colspan='2' class='center b'>" . __("PDF creating configuration", 'dlteams') . "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>" . __("Codepage", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showFromArray('print[codepage]', ['UTF-8', 'ISO-8859-1', 'ISO-8859-2'], [
            'value' => $config['print']['codepage']
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Font name", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showFromArray('print[font_name]', $this->core_tcpdf_fonts, [
            'value' => $config['print']['font_name']
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Font size", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showNumber('print[font_size]', [
            'value' => $config['print']['font_size'],
            'min' => 8,
            'max' => 16,
            'step' => 1,
            'unit' => 'pt',
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Margin left", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showNumber('print[margin_left]', [
            'value' => $config['print']['margin_left'],
            'min' => 5,
            'max' => 50,
            'unit' => 'mm',
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Margin top", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showNumber('print[margin_top]', [
            'value' => $config['print']['margin_top'],
            'min' => 5,
            'max' => 50,
            'unit' => 'mm',
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Margin right", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showNumber('print[margin_right]', [
            'value' => $config['print']['margin_right'],
            'min' => 5,
            'max' => 50,
            'unit' => 'mm',
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Margin bottom", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showNumber('print[margin_bottom]', [
            'value' => $config['print']['margin_bottom'],
            'min' => 5,
            'max' => 50,
            'unit' => 'mm',
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Header margin (from top)", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showNumber('print[margin_header]', [
            'value' => $config['print']['margin_header'],
            'min' => 10,
            'max' => 30,
            'unit' => 'mm',
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Footer margin (from bottom)", 'dlteams') . "</td>";
        echo "<td>";

        Dropdown::showNumber('print[margin_footer]', [
            'value' => $config['print']['margin_footer'],
            'min' => 10,
            'max' => 30,
            'unit' => 'mm',
        ]);
        echo "</td>";

        echo "<tr>";
        echo "<td>" . __("Show logo", 'dlteams') . "</td>";
        echo "<td>";

        dropdown::showYesNo('print[logo_show]', $config['print']['logo_show']);
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td>" . __("Logo image filename (located in /plugins/dlteams/images/)", 'dlteams') . "</td>";
        echo "<td>";

        echo "<input type='text' maxlength='254' name='print[logo_image]' value=\"" . $config['print']['logo_image'] . "\">";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'>";
        echo "<div class='center'>";

        echo "<input type='hidden' name='entities_id' value=\"" . 0 . "\">";

        $value = 0;
        if (isset($this->fields['id'])) {
            $value = $this->fields['id'];
        }
        echo "<input type='hidden' name='id' value=\"" . $value . "\">";
        if (isset($this->fields['id'])) {
            $action = 'update';
        } else {
            $action = 'add';
        }
        echo "<input type='submit' name='" . $action . "' value=\"" . _sx('button', 'Save') . "\" class='submit'>";
        echo "</div>";
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        Html::closeForm();
        echo "</div>";
        echo "<br><br>";
    }

    public function showImportExport(){
        echo "<table class='tab_cadre' cellpadding='5' width='50%'>";
        echo "<tr>";
        echo "<th colspan='2'><center>" . __("Importation de données", 'dlteams') . "</th>";
        echo "</tr>";

        echo "<form method='post'  action='./import_m365.php' enctype='multipart/form-data'>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Sélectionner le fichier .csv issu de l'export Entra Id", 'dlteams') . "</td>";
        echo "<td>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __('Choose a file', 'datainjection') . "</td>";
        echo "<td><input type='file' name='filename'>";
        echo "<input type='hidden' name='id' value='"."'>";
        echo "</td></tr>";
        echo "<td>";
        // echo "<th colspan='2'>".sprintf(__('%1$s: %2$s'), __('Model'), $model->fields['name'])."</th>";
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Vérifier si un fichier a été téléchargé
            if(isset($_FILES['filename']) && $_FILES['filename']['error'] == UPLOAD_ERR_OK) {
                // Récupérer le nom du fichier
                $filename = $_FILES['filename']['name'];
                // Afficher le nom du fichier
                echo "Nom du fichier choisi : " . $filename;
            } else {
                // Si aucun fichier n'a été téléchargé ou s'il y a une erreur
                echo "Aucun fichier sélectionné ou une erreur est survenue lors du téléchargement.";
            }
        }
        // var_dump ($filename);die;
        //echo "<input type='file' id='ctrl' name='files_to_import[]' placeholder='Choisissez un dossier' webkitdirectory directory multiple/>";
        echo "</td> </tr>";

        echo "<td>" . __("Indiquer l'entité cible", 'dlteams') . "</td>";
        echo "<td>";
        Entity::dropdown(['name' => 'entities_id_target', 'width' => '250px']);
        echo "</td> </tr>";

        echo "<tr class='tab_bg_1'> <td colspan='2'> <div class='center'>";
        echo "<input type='submit' name='import_m365' value=\"" . __("Importer des utilisateurs", 'dlteams') . "\" class='submit'>";
        echo "</div></td></tr>";
        Html::closeForm();

        ///////////////////////////////////////////Transfert massif de données entre entités
        echo "<table class='tab_cadre' cellpadding='5' width='50%'>";
        echo "<tr>";
        echo "<th colspan='2'><center>" . __("Transfert massif de données entre entités", 'dlteams') . "</th>";
        echo "</tr>";

        // duplicate records from an entity to an other
        echo "<tr class='tab_bg_1'> <td colspan='2'><strong>" . __("Copier l'ensemble des traitements + les éléments liés", 'dlteams') . "</strong></td> </tr>";

        // exporter les traitements
        echo "<form method='post' action='./export_recordsrgpd.php'>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Sélectionner l'entité à exporter", 'dlteams') . "</td>";
        echo "<td>";
        Entity::dropdown(['name' => 'entities_id_origin', 'width' => '250px']) . "</td>";
//        $entities_id_origin = "origin";
        echo "</td> </tr>";
        echo "<td>" . __("Indiquer le dossier cible", 'dlteams') . "</td>";
        echo "<td>";
        Link::dropdown(['name' => 'folder_id_target', 'width' => '250px']);
//        $folder_id_target = "target";
        echo "</td> </tr>";
        echo "<tr class='tab_bg_1'> <td colspan='2'> <div class='center'>";
        echo "<input type='submit' name='export_recordsrgpd' value=\"" . __("Exporter Traitements", 'dlteams') . "\" class='submit'>";
        echo "</div></td></tr>";
        Html::closeForm();

        // importer les traitements
        echo "<form method='post'  action='./import_recordsrgpd.php' enctype='multipart/form-data'>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Sélectionner le dossier source", 'dlteams') . "</td>";
        echo "<td>";
        echo "<input type='file' id='ctrl' name='files_to_import[]' placeholder='Choisissez un dossier' webkitdirectory directory multiple/>";

        echo "</td> </tr>";
        echo "<td>" . __("Indiquer l'entité cible", 'dlteams') . "</td>";
        echo "<td>";
        Entity::dropdown(['name' => 'entities_id_target', 'width' => '250px']);

        echo "</td> </tr>";
        echo "<tr class='tab_bg_1'> <td colspan='2'> <div class='center'>";
//        echo "<input type='submit' name='import_recordsrgpd' value=\"" . __("Importer Traitements", 'dlteams') . "\" class='submit'>";
        echo "</div></td></tr>";
        Html::closeForm();


        // duplicate a project from an entity to an other
        echo "<form method='post' action='./import_projectrgpd_model.php'>";
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'><strong>" . __("Copier un projet, les tâches et événements liés)", 'dlteams') . "</strong></td>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Sélectionner le projet source ", 'dlteams') . "</td>";
        echo "<td>";
        Project::dropdown(['name' => 'projects_id', 'width' => '250px']);
        echo "</td>";
        echo "<tr class='tab_bg_1'> <td>" . __("Indiquer l'entité cible", 'dlteams') . "</td>";
        echo "<td>";
        Entity::dropdown(['name' => 'entities_id', 'width' => '250px']);
        echo "</td></tr>";
        echo "<tr class='tab_bg_1'> <td colspan='2'> <div class='center'>";
        echo "<input type='submit' name='import_project' value=\"" . __("Copier Projet", 'dlteams') . "\" class='submit'>";
        echo "</div></td></tr>";
        Html::closeForm();
        // duplicate forms from an entity to an other


        echo "<form method='post' action='./import_forms.php'>";
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'><strong>" . __("Copier les formulaires", 'dlteams') . "</strong></td>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Indiquer l'entité modèle", 'dlteams') . "</td>";
        echo "<td>";
        Entity::dropdown(['name' => 'entities_id_origin', 'width' => '250px']);
        echo "</td>";
        echo "</tr>";
        echo "<td>" . __("Indiquer l'entité cible", 'dlteams') . "</td>";
        echo "<td>";
        Entity::dropdown(['name' => 'entities_id_target', 'width' => '250px']);
        echo "</td>";
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'> <div class='center'>";
//        echo "<input type='submit' name='copyto_forms' value=\"" . __("Copier Formulaires", 'dlteams') . "\" class='submit'>";
        echo "</div></td></tr>";
        Html::closeForm();

        echo "<form method='post' action='./import_deliverables.php'>";
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'><strong>" . __("Copier les livrables", 'dlteams') . "</strong></td>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Indiquer l'entité modèle", 'dlteams') . "</td>";
        echo "<td>";
        Entity::dropdown(['name' => 'entities_id_origin', 'width' => '250px']);
        echo "</td>";
        echo "</tr>";
        echo "<td>" . __("Indiquer l'entité cible", 'dlteams') . "</td>";
        echo "<td>";
        Entity::dropdown(['name' => 'entities_id_target', 'width' => '250px']);
        echo "</td>";
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'> <div class='center'>";
//        echo "<input type='submit' name='import_deliverables' value=\"" . __("Copier Livrables", 'dlteams') . "\" class='submit'>";
        echo "</div></td></tr>";
        Html::closeForm();
        echo "</table>";

//      echo "<form method='post' action='./config.form.php'>";
        echo "<form method='post' action='#'>";

        echo "<table class='tab_cadre' cellpadding='5' width='50%'>";
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'><strong>" . __("Copier les données de l'entité modèle dans l'entité actuelle", 'dlteams') . "</strong></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Categories of data subjects", 'dlteams') . "</td>";
        echo "<td>";
        Html::showCheckbox(['name' => 'install_categories_of_data_subjects', 'title' => __("Categories of data subjects", 'dlteams'), 'checked' => 0]);
        echo "</td> </tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Legal bases", 'dlteams') . "</td>";
        echo "<td>";
        Html::showCheckbox(['name' => 'install_legal_bases', 'title' => __("Legal bases", 'dlteams'), 'checked' => 0]);
        echo "</td> </tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Security measures", 'dlteams') . "</td>";
        echo "<td>";
        Html::showCheckbox(['name' => 'install_security_measures', 'title' => __("Security measures", 'dlteams'), 'checked' => 0]);
        echo "</td> </tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Contract types", 'dlteams') . "</td>";
        echo "<td>";
        Html::showCheckbox(['name' => 'install_contract_types', 'title' => __("Contract types", 'dlteams'), 'checked' => 0]);
        echo "</td> </tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Personal data types", 'dlteams') . "</td>";
        echo "<td>";
        Html::showCheckbox(['name' => 'install_personal_data_types', 'title' => __("Personal data types", 'dlteams'), 'checked' => 0]);
        echo "</td> </tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Référentiel (projet Conformité RGPD)", 'dlteams') . "</td>";
        echo "<td>";
        Html::showCheckbox(['name' => 'import_project', 'title' => __("Référentiel", 'dlteams'), 'checked' => 0]);
        echo "</td> </tr>";

        echo "<tr class='tab_bg_1'> ";
        echo "<td colspan='2'> <div class='center'>";
        echo "<input type='submit' name='sampledata' value=\"" . __("Importer les données", 'dlteams') . "\" class='submit'>";
        echo "</div> </td> </tr>";

        echo "</table>";
        Html::closeForm();

        // echo "<form method='post' action='./config.form.php'>";
        // var_dump ("currententity_id", $currententity_id, "rgpdmodel_id", $rgpdmodel_id);
        // import model-rgpd objects datas 			// var_dump ($mandatory_sites);
        if (plugin_dlteams_root == "/var/www/dlteams_app/marketplace/dlteams" or plugin_dlteams_root == "/var/www/model_dlteams_app/marketplace/dlteams") {
            $mandatory_sites = 1;
        } else {
            $mandatory_sites = 0;
        }
        // profil N°4 = super-admin
        $activ_profil = $_SESSION['glpiactiveprofile']["id"];

        if ($activ_profil == 4) {
            echo "<form method='post' action='./fork.php'>";
        // Copier les fichiers des dossiers fork et whitelabel
        echo "<table class='tab_cadre' cellpadding='5' width='50%'>";
        echo "<tr>";
        echo "<th colspan='2'> <center>" . __("Implémenter Fork et WhiteLabel", 'dlteams') . "</th>";
        echo "</tr>";

            // echo "<form method='post' action=\"" . Html::showSimpleForm(static::getFormURL(),'test_ldap_replicate',_sx('button', 'Test'),['champ1', 'champ2']) . "\">";
            // echo "<form method='post' action=\"" . static::getFormURL() . "\">";
            echo "<tr class='tab_bg_1'> ";
            echo "<td colspan='2'> <div class='center'>";
            echo "<input type='submit' name='fork_on' value=\"" . __("Activer fork", 'dlteams') . "\" class='submit'>";
            echo "</div> </td> </tr>";

            echo "<tr class='tab_bg_1'> ";
            echo "<td colspan='2'> <div class='center'>";
            echo "<input type='submit' name='fork_off' value=\"" . __("Désactiver fork", 'dlteams') . "\" class='submit'>";
            echo "</div> </td> </tr>";

            // echo "<form method='post' action='./fork.php'>";
            echo "<tr class='tab_bg_1'> ";
            echo "<td colspan='2'> <div class='center'>";
            echo "<input type='submit' name='whitelabel_on' value=\"" . __("Activer whitelabel", 'dlteams') . "\" class='submit'>";
            echo "</div> </td> </tr>";

            echo "<tr class='tab_bg_1'> ";
            echo "<td colspan='2'> <div class='center'>";
            echo "<input type='submit' name='whitelabel_off' value=\"" . __("Désactiver whitelabel", 'dlteams') . "\" class='submit'>";
            echo "</div> </td> </tr>";

//            echo "</table>";

            echo "</table>";
            Html::closeForm();
        }
    }

    public function showEntiteModel(){

        if (plugin_dlteams_root == "/var/www/dlteams_app/marketplace/dlteams" or plugin_dlteams_root == "/var/www/model_dlteams_app/marketplace/dlteams") {
            $mandatory_sites = 1;
        } else {
            $mandatory_sites = 0;
        }
        global $DB;
        $activ_profil = $_SESSION['glpiactiveprofile']["id"];
        if ($activ_profil == 4) {
            echo "<table class='tab_cadrehov table-striped table-hover' style='width: 50%'>";
            echo "<tr><th colspan='5'><center>" . __("Opérations sur l'entité modèle (model-rgpd)") . "</th></tr>";

            // if no model-rgpd then purpose création
            $currententity_id = intval(Session::getActiveEntity()); // id of current entity
            $result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
            $rgpdmodel_id = 0;
            if ($result && $DB->numrows($result) > 0) {
                $data = $DB->fetchAssoc($result);
                $rgpdmodel_id = $data['id'];
            } // on a l'id de model-rgpd
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='2'>" . __("Créer l'entité model-rgpd", 'dlteams') . "</td>";
            echo "</tr>";
            if ($rgpdmodel_id == 0) { // si rgpd_model existe
                echo "<tr class='tab_bg_1'>";
                echo "<td colspan='2'>";
                echo "<div style='display: flex; gap: 6px; width: 100%; justify-content: center; align-items: center;' class='center'>";
                echo "<form method='post' action='./config.form.php' ><input type='submit' name='create_modelrgpd' value=\"" . __("Créer", 'dlteams') . "\" class='submit'>";
                Html::closeForm();
                echo "</tr>";
            }
            echo "<tr class='tab_bg_1'> ";
            echo "<td colspan='2' ><height='250px'><strong><center>" . __("Importer les données du plug'in vers l'entité rgpd-model", 'dlteams') . "</strong></td>";
            echo "</tr>";
            // import untitled
            echo "<tr class='tab_bg_1'> <td colspan='2'>" . __("Importer les intitulés conformité RGPD", 'dlteams') . "</td>";
            echo "<td colspan='2'>" . "<div style='display: flex; gap: 6px; width: 100%; justify-content: right; align-items: center;' class='right'>";
            if ($rgpdmodel_id <> 0 && $mandatory_sites == 0) {
                echo "<form method='post' action='./import_rgpduntitled.php'><input type='submit' name='migrate' value=\"" . __("Importer les intitulés", 'dlteams') . "\" class='submit'>";
                Html::closeForm();
            }
            // import de toutes les tables traitements et tables liées
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='2'>" . __("Importer et mettre à jour toutes les tables RGPD (traitements, bases légales, etc...)", 'dlteams') . "</td>";
            echo "<td colspan='2'>" . "<div style='display: flex; gap: 6px; width: 100%; justify-content: right; align-items: center;' class='center'>";
            if ($rgpdmodel_id <> 0 && $mandatory_sites == 0) {
                echo "<form method='post' action='./import_allrgpd_model.php'><input type='submit' name='migrate' value=\"" . __("RAZ et import des tables", 'dlteams') . "\" class='submit'>";
                Html::closeForm();
            }
            if ($rgpdmodel_id <> 0 && $mandatory_sites == 0) {
                echo "<form method='post' action='./update_allrgpd_model.php'><input type='submit' name='migrate' value=\"" . __("Mise à jour des tables", 'dlteams') . "\" class='submit'>";
                Html::closeForm();
            }
            echo "</tr>";
            // import model-rgpd project
            // if ($rgpdmodel_id <> 0 && plugin_dlteams_root <> "/var/www/model_dlteams_app/marketplace/dlteams") {
            echo "<tr class='tab_bg_1'> <td colspan='2'>" . __("Importer (ou effacer et ré-importer) le projet \"Conformité RGPD\"", 'dlteams') . "</td>";
            echo "<td colspan='2'>" . "<div style='display: flex; gap: 6px; width: 100%; justify-content: right; align-items: center;' class='center'>";
            if ($rgpdmodel_id <> 0 && $mandatory_sites == 0) {
                echo "<form method='post' action='./import_projectrgpd_model.php'><input type='submit' name='migrate' value=\"" . __("Import du référentiel de l'entité modèle", 'dlteams') . "\" class='submit'>";
                Html::closeForm();
            }
            echo "</tr>";
            // import forms
            echo "<tr class='tab_bg_1'> <td colspan='2'>" . __("Importer les formulaires de model-rgpd", 'dlteams') . "</td>";
            echo "<td colspan='2'>" . "<div style='display: flex; gap: 6px; width: 100%; justify-content: right; align-items: center;' class='center'>";
            if ($rgpdmodel_id <> 0 && $mandatory_sites == 0) {
                echo "<form method='post' action='./import_formrgpd_model.php'><input type='submit' name='migrate' value=\"" . __("Import des formulaires de l'entité modèle", 'dlteams') . "\" class='submit'>";
                Html::closeForm();
            }
            echo "</tr>";
            // import knowledge base
            echo "<tr class='tab_bg_1'> <td colspan='2'>" . __("Importer la base de connaissance de l'entité modèle", 'dlteams') . "</td>";
            echo "<td colspan='2'>" . "<div style='display: flex; gap: 6px; width: 100%; justify-content: right; align-items: center;' class='center'>";
            if ($rgpdmodel_id <> 0 && $mandatory_sites == 0) {
                echo "<form method='post' action='./import_knowledgergpd_model.php'><input type='submit' name='migrate' value=\"" . __("Importer la base de connaissance de l'entité modèle", 'dlteams') . "\" class='submit'>";
                Html::closeForm();
            }
            echo "</tr>";

            // Export datas from model-rgpd entity to "/install/datas/" when root = dlteams_app
            echo "<td></td>";
            echo "<tr class='noHover'><th colspan='2' >" . __("Exporter les données de l'entité model-rgpd vers le plug'in", 'dlteams') . "</th></tr>";

            // Export records objects
            echo "<td>" . __("Exporter les données de l'entité rgpd-model ainsi que leurs éléments liés", 'dlteams') . "</td>";
            echo "<td colspan='2'> <div class='left'>";
            if ($mandatory_sites == 1) {
                echo "<form method='post' action='./export_allrgpd_model.php'><input type='submit' name='export_allrgpd_model' value=\"" . __("Exporter tous les objets et éléments liés", 'dlteams') . "\" class='submit'>";
                Html::closeForm();
            }
            echo "</td></tr>";
            // Export untitled
            echo "<tr class='tab_bg_1'>" . "<td>" . __("Exporter intitulés", 'dlteams') . "</td>";
            echo "<td colspan='2'> <div class='left'>";
            if ($mandatory_sites == 1) {
                echo "<form method='post' action='./export_rgpduntitled.php'><input type='submit' name='export_rgpduntitled' value=\"" . __("Exporter les intitulés", 'dlteams') . "\" class='submit'>";
                Html::closeForm();
            }
            echo "</td></tr>";

            // Export Project
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __("Exporter le projet \"conformité RGPD\" (+ tâches & actions)", 'dlteams') . "</td>";
            echo "<td colspan='2'> <div class='left'>";
            if ($mandatory_sites == 1) {
                echo "<form method='post' action='./export_projectrgpd_model.php'><input type='submit' name='export_project' value=\"" . __("Exporter Projet", 'dlteams') . "\" class='submit'>";
                Html::closeForm();
            }
            echo "</td></tr>";
            // Export Forms
            echo "<tr class='tab_bg_1'>" . "<td>" . __("Exporter les formulaires", 'dlteams') . "</td>";
            echo "<td colspan='2'> <div class='left'>";
            if ($mandatory_sites == 1) {
                echo "<form method='post' action='./export_formrgpd_model.php'><input type='submit' name='export_form' value=\"" . __("Exporter Formulaires", 'dlteams') . "\" class='submit'>";
                Html::closeForm();
            }
            echo "</td></tr>";
            // Export Knowledge base
            echo "<tr class='tab_bg_1'>" . "<td>" . __("Exporter la base de connaissances", 'dlteams') . "</td>";
            echo "<td colspan='2'> <div class='left'>";
            if ($mandatory_sites == 1) {
                echo "<form method='post' action='./export_knowledgergpd_model.php'><input type='submit' name='export_knowledge' value=\"" . __("Exporter Base de connaissance", 'dlteams') . "\" class='submit'>";
                Html::closeForm();
            }
            echo "</td></tr>";

            // Export records objects
            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __("Exporter les livrables de l'entité rgpd-model", 'dlteams') . "</td>";
            echo "<td colspan='2'> <div class='left'>";
            if ($mandatory_sites == 1) {
                echo "<form method='post' action='./export_deliverablergpd_model.php'><input type='submit' name='export_deliverablergpd_model' value=\"" . __("Export des livrables", 'dlteams') . "\" class='submit'>";
                Html::closeForm();
            }
            echo "</td></tr>";

            echo "<td></td>";
        }
    }

    public function showUpdateSection(){
        $activ_profil = $_SESSION['glpiactiveprofile']["id"];
//        if ($activ_profil == 4) {
//            echo "<table class='tab_cadrehov table-striped table-hover' style='width: 50%'>";
//            echo "<tr><th colspan='5'><center>" . __("Opérations sur l'entité modèle (model-rgpd)") . "</th></tr>";

            // if no model-rgpd then purpose création
            $currententity_id = intval(Session::getActiveEntity()); // id of current entity
        global $DB;
        if (plugin_dlteams_root == "/var/www/dlteams_app/marketplace/dlteams" or plugin_dlteams_root == "/var/www/model_dlteams_app/marketplace/dlteams") {
            $mandatory_sites = 1;
        } else {
            $mandatory_sites = 0;
        }
        $result = $DB->query('SELECT * FROM `glpi_entities` WHERE `name` = "model-rgpd"');
        $rgpdmodel_id = 0;
        if ($result && $DB->numrows($result) > 0) {
            $data = $DB->fetchAssoc($result);
            $rgpdmodel_id = $data['id'];
        }

//        var_dump("zzz");
//        die();

        echo "<table class='tab_cadrehov table-striped table-hover' style='width: 50%'>";
        echo  "<tr class='noHover'><th colspan='2' >" . __("Suppression et nettoyage", 'dlteams') . "</th></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'>" . __("Supprimer l'entité model-rgpd et toutes ses données", 'dlteams') . "</td>";
        echo "<td colspan='2'> <div style='display: flex; gap: 6px; width: 100%; justify-content: right; align-items: center;' class='center'>";
        // si rgpd_model existe
        if ($rgpdmodel_id <> 0 && $mandatory_sites == 0) {
            echo "<form method='post' action='./config.form.php'><input type='submit' name='delete_modelrgpd' value=\"" . __("Supprimer rgpd-model", 'dlteams') . "\" class='submit'>";
            Html::closeForm();
        }
        echo "</div></td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'>" . __("Supprimer les _items orphelins", 'dlteams') . "</td>";
        echo "<td colspan='2'> <div class='right'>";
        //if ($mandatory_sites == 1) {
        echo "<form method='post' action='./clean_items.php'><input type='submit' name='clean_items' value=\"" . __("Nettoyage _items", 'dlteams') . "\" class='submit'>";
        Html::closeForm();
        //}
        echo "</div></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'>" . __("Upgrader les tables vers dlteams", 'dlteams') . "</td>";
        echo "<td colspan='2'> <div class='right'>";
        //if ($mandatory_sites == 1) {
        echo "<form method='post' action='./update-dlteams.php'><input type='submit' name='update_dlteams' value=\"" . __("Updater dlteams", 'dlteams') . "\" class='submit'>";
        Html::closeForm();
        //}
        echo "</div></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'>" . __("Rejouer les updates sql", 'dlteams') . "</td>";
        echo "<td colspan='2'> <div class='right'>";
        echo "<form method='post' action='./config.form.php'><input type='submit' name='update_database' value=\"" . __("Executer SQL", 'dlteams') . "\" class='submit'>";
        Html::closeForm();
        echo "</div></td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'>" . __("Mettre à jour le Plug'in", 'dlteams') . "</td>";
        echo "<td colspan='2'> <div class='right'>";
        echo "<form method='post' action='./updatedlteamsfiles.php' enctype='multipart/form-data'>";

        echo "<input type='file' accept='.zip,.rar,.tar,.gz' name='file'>";
        echo "<input type='submit' name='file_id' value=\"" . __("Mettre à jour", 'dlteams') . "\" class='submit'>";
        Html::closeForm();
        echo "</div></td></tr>";

        echo "</table>";
        echo "<br/><br/><br/>";
    }
    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
//        var_dump($tabnum);

        if ($item->getType() == "PluginDlteamsConfig") {
            switch ($tabnum) {
                case 0:
                    $item->showGeneral();
                    break;

                case 1:
                    $item->showEntiteModel();
                    break;
//
                case 3:
                    $item->showUpdateSection();
                    break;
//
                case 2:
                    $item->showImportExport();
                    break;

                case 4:
                    $filename = GLPI_ROOT . "/files/_log/sql-errors.log";

                    $file = fopen($filename, "r");
                    $logs = []; // Assurez-vous que le tableau $logs est initialisé avant utilisation

                    if ($file) {
                        // Lire le fichier ligne par ligne
                        while (($line = fgets($file)) !== false) {
                            // Extraction de la date et du niveau de log
                            if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\./', $line, $matches)) {
                                $date = $matches[1];
                                $level = strtolower($matches[2]);
                                $logs[$date][] = ['level' => $level, 'message' => $line];
                            }
                        }
                        fclose($file);

                        // Trier les logs par date en ordre décroissant
                        krsort($logs);

                        // Affichage des logs
                        foreach ($logs as $date => $entries) {
                            echo "<h2>" . $date . "</h2>";
                            foreach ($entries as $entry) {
                                echo "<pre class='" . $entry['level'] . "'>" . htmlspecialchars($entry['message']) . "</pre>";
                            }
                        }
                    } else {
                        echo "Unable to open file.";
                    }

                    break;
            }
        }
        return true;
    }

    public static function refreshAccessToken($refreshToken) {
            $url = 'https://oauth2.googleapis.com/token';
            $params = [
                'refresh_token' => $refreshToken,
                'client_id' => '768127373707-5nmv2lg018jrjv6srhnel2hsl7p9h1dd.apps.googleusercontent.com',
                'client_secret' => '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',
                'grant_type' => 'refresh_token',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            $data = json_decode($response, true);

            return $data['access_token'];
        }


    public function checkForUpdateFile($accessToken, $fileName) {
            $url = "https://www.googleapis.com/drive/v3/files";
            $folderId = "1Qr6IsId1LDByX3BsiivZjkgFkqnTCSE0";
            $query = urlencode("'$folderId' in parents and name = '$fileName' and trashed = false");
            $url .= "?q=$query";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $accessToken, 'Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);

            $files = json_decode($response, true);
            return !empty($files['files']);
        }

    /*// Recuperate Donnees Duree de Conservation
            echo "<form method='post' action='./config.form.php'>";
            echo "<table class='tab_cadre' cellpadding='5' width='50%'>";
            echo "<tr>";
            echo "<th colspan='2'>" . __("Cron Jobs", 'dlteams') . "</th>";
            echo "</tr>";
            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='2'><strong>" . __("Cron Tasks.", 'dlteams') . "</strong></td>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __("GUID FILL", 'dlteams') . "</td>";
            echo "<td>";
            Html::showCheckbox([
               'name'  => 'migrate_guid',
               'title' => __("Cron Guid", 'dlteams'),
               'checked' => 0]);
            echo "</td>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td colspan='2'>";
            echo "<div class='center'>";
            echo "<input type='submit' name='migrate' value=\"" . __("Lancer Cron", 'dlteams') . "\" class='submit'>";
            echo "</div>";
            echo "</td>";
            echo "</tr>";

            echo "</table>";
            Html::closeForm();
            echo "</div>";*/

    /*   function installSampleData($data = []) {

          if (isset($data['install_categories_of_data_subjects']) && ($data['install_categories_of_data_subjects'] == 1)) {
             $sample_data = new PluginDlteamsDataSubjectsCategory();
             $sample_data->add([
                'name' => __("Employees", 'dlteams'),
                'comment' => __("Company employees", 'dlteams'),
                'entities_id' => $_SESSION['glpiactive_entity'],
             ]);
             $sample_data = new PluginDlteamsDataSubjectsCategory();
             $sample_data->add([
                'name' => __("Consumers", 'dlteams'),
                'comment' => __("Company consumers/clients", 'dlteams'),
                'entities_id' => $_SESSION['glpiactive_entity'],
             ]);
          }

          if (isset($data['install_legal_bases']) && ($data['install_legal_bases'] == 1)) {
             $sample_data = new PluginDlteamsLegalBasisAct();
             $sample_data->add([
                'name' => __("Undefined", 'dlteams'),
                'content' => __("N/A", 'dlteams'),
                'type' => PluginDlteamsLegalBasisAct::LEGALBASISACT_BLANK,
                'entities_id' => $_SESSION['glpiactive_entity'],
                'injected' => 1,
             ]);
             $sample_data->add([
                'name' => __("Article 6-1a", 'dlteams'),
                'content' =>  __("The data subject has given consent to the processing of his or her personal data for one or more specific purposes.", 'dlteams'),
                'type' => PluginDlteamsLegalBasisAct::LEGALBASISACT_GDPR,
                'entities_id' => $_SESSION['glpiactive_entity'],
                'injected' => 1,
             ]);
             $sample_data->add([
                'name' => __("Article 6-1b", 'dlteams'),
                'content' => __("Processing is necessary for the performance of a contract to which the data subject is party or in order to take steps at the request of the data subject prior to entering into a contract.", 'dlteams'),
                'type' => PluginDlteamsLegalBasisAct::LEGALBASISACT_GDPR,
                'entities_id' => $_SESSION['glpiactive_entity'],
                'injected' => 1,
             ]);
             $sample_data->add([
                'name' => __("Article 6-1c", 'dlteams'),
                'content' => __("Processing is necessary for compliance with a legal obligation to which the controller is subject.", 'dlteams'),
                'type' => PluginDlteamsLegalBasisAct::LEGALBASISACT_GDPR,
                'entities_id' => $_SESSION['glpiactive_entity'],
                'injected' => 1,
             ]);
             $sample_data->add([
                'name' => __("Article 6-1d", 'dlteams'),
                'content' =>  __("Processing is necessary in order to protect the vital interests of the data subject or of another natural person.", 'dlteams'),
                'type' => PluginDlteamsLegalBasisAct::LEGALBASISACT_GDPR,
                'entities_id' => $_SESSION['glpiactive_entity'],
                'injected' => 1,
             ]);
             $sample_data->add([
                'name' => __("Article 6-1e", 'dlteams'),
                'content' => __("Processing is necessary for the performance of a task carried out in the public interest or in the exercise of official authority vested in the controller.", 'dlteams'),
                'type' => PluginDlteamsLegalBasisAct::LEGALBASISACT_GDPR,
                'entities_id' => $_SESSION['glpiactive_entity'],
                'injected' => 1,
             ]);
             $sample_data->add([
                'name' => __("Article 6-1f", 'dlteams'),
                'content' => __("Processing is necessary for the purposes of the legitimate interests pursued by the controller or by a third party, except where such interests are overridden by the interests or fundamental rights and freedoms of the data subject which require protection of personal data, in particular where the data subject is a child.", 'dlteams'),
                'type' => PluginDlteamsLegalBasisAct::LEGALBASISACT_GDPR,
                'entities_id' => $_SESSION['glpiactive_entity'],
                'injected' => 1,
             ]);
          }

          if (isset($data['install_security_measures']) && ($data['install_security_measures'] == 1)) {
             $sample_data = new PluginDlteamsSecurityMeasure();
             $sample_data->add([
                'name' => __("DPO was appointed", 'dlteams'),
                'type' => PluginDlteamsSecurityMeasure::SECURITYMEASURE_TYPE_ORGANIZATION,
                'comment' => __("Data Protection Officer was appointed", 'dlteams'),
                'entities_id' => $_SESSION['glpiactive_entity'],
             ]);
             $sample_data->add([
                'name' => __("Computers Usage Policy", 'dlteams'),
                'type' => PluginDlteamsSecurityMeasure::SECURITYMEASURE_TYPE_ORGANIZATION,
                'comment' => __("Internal policy regarding usage of computers", 'dlteams'),
                'entities_id' => $_SESSION['glpiactive_entity'],
             ]);
             $sample_data->add([
                'name' => __("24h Security", 'dlteams'),
                'type' => PluginDlteamsSecurityMeasure::SECURITYMEASURE_TYPE_PHYSICAL,
                'comment' => __("Securtiy personel on site for 24h", 'dlteams'),
                'entities_id' => $_SESSION['glpiactive_entity'],
             ]);
             $sample_data->add([
                'name' => __("UPS system", 'dlteams'),
                'type' => PluginDlteamsSecurityMeasure::SECURITYMEASURE_TYPE_PHYSICAL,
                'comment' => __("Uninterruptidle power supply is installed", 'dlteams'),
                'entities_id' => $_SESSION['glpiactive_entity'],
             ]);
             $sample_data->add([
                'name' => __("Antivirus App", 'dlteams'),
                'type' => PluginDlteamsSecurityMeasure::SECURITYMEASURE_TYPE_IT,
                'comment' => __("Computers have Antivirus app installed", 'dlteams'),
                'entities_id' => $_SESSION['glpiactive_entity'],
             ]);
             $sample_data->add([
                'name' => __("Firewall", 'dlteams'),
                'type' => PluginDlteamsSecurityMeasure::SECURITYMEASURE_TYPE_IT,
                'comment' => __("Firewall protects internal network", 'dlteams'),
                'entities_id' => $_SESSION['glpiactive_entity'],
             ]);
          }

          if (isset($data['install_contract_types']) && ($data['install_contract_types'] == 1)) {
             $sample_data = new ContractType();
             $sample_data->add([
                'name' => __("GDPR Joint Controller Contract", 'dlteams'),
                'comment' => __("GDPR Joint Controller Contract", 'dlteams'),
             ]);
             $sample_data->add([
                'name' => __("GDPR Processor Contract", 'dlteams'),
                'comment' => __("GDPR Processor Contract", 'dlteams'),
             ]);
             $sample_data->add([
                'name' => __("GDPR Thirdparty Contract", 'dlteams'),
                'comment' => __("GDPR Thirdparty Contract", 'dlteams'),
             ]);
             $sample_data->add([
                'name' => __("GDPR Internal Contract", 'dlteams'),
                'comment' => __("GDPR Internal Contract", 'dlteams'),
             ]);
             $sample_data->add([
                'name' => __("GDPR Other Contract", 'dlteams'),
                'comment' => __("GDPR Other Contract", 'dlteams'),
             ]);
          }

          if (isset($data['install_personal_data_types']) && ($data['install_personal_data_types'] == 1)) {
             $sample_data = new PluginDlteamsPersonalDataCategory();
             $parent_id = $sample_data->add([
                'name' => __("Employees record", 'dlteams'),
                'comment' => __("Employee personal data", 'dlteams'),
                'is_special_category' => false,
                'entities_id' => $_SESSION['glpiactive_entity'],
             ]);
             if ($parent_id) {
                $sample_data->add([
                   'name' => __("First name", 'dlteams'),
                   'comment' => __("Employee first name", 'dlteams'),
                   'entities_id' => $_SESSION['glpiactive_entity'],
                   'is_special_category' => false,
                   'plugin_dlteams_personaldatacategories_id' => $parent_id
                ]);
                $sample_data->add([
                   'name' => __("Last name", 'dlteams'),
                   'comment' => __("Employee last name", 'dlteams'),
                   'entities_id' => $_SESSION['glpiactive_entity'],
                   'is_special_category' => false,
                   'plugin_dlteams_personaldatacategories_id' => $parent_id
                ]);
                $sample_data->add([
                   'name' => __("Personal ID", 'dlteams'),
                   'comment' => __("Employee personal ID", 'dlteams'),
                   'entities_id' => $_SESSION['glpiactive_entity'],
                   'is_special_category' => true,
                   'plugin_dlteams_personaldatacategories_id' => $parent_id
                ]);
             }
          }
       }*/

    /* function MigrateGenericObjectData($data = []) {
       global $DB;
       $glpiRoot=str_replace('\\', '/', GLPI_ROOT);
       if (isset($data['migrate_categories_of_data_subjects']) && ($data['migrate_categories_of_data_subjects'] == 1)) {
          // Vider les tables glpi_plugin_genericobject_personnesconcernees et glpi_plugin_genericobject_dcptraitees avant
          // de commencer l'import
          $DB->request("TRUNCATE TABLE `glpi_plugin_genericobject_personnesconcernees`");
          $DB->request("TRUNCATE TABLE `glpi_plugin_genericobject_dcptraitees`");

          $keys = ['gpddname', 'gpddcomment', 'gpdpname', 'gpdpcomment'];
          $iterator = $DB->request(self::getRequest());
          $number = count($iterator);

          $items_list = [];
          while ($data = $iterator->next()) {
             foreach ($data as $key => $value) {
                if (in_array($key,  $keys)) {
                   $data[$key] = $DB->escape($data[$key]);
                }
             }
             $items_list[$data['linkid']] = $data;
          }

          foreach ($items_list as $data) {
             // Categories de Personnes
             $personne_concernee_id = '';
             $exist = $DB->request('glpi_plugin_genericobject_dcptraitees', ['name' => $data['gpdpname'], 'entities_id'=> intval($data['gpdrentitiesid']) ]);
             if (empty(count($exist))) {
                $personne_concernee_data = new PluginGenericobjectDcptraitee();
                $personne_concernee_id = $personne_concernee_data->add([
                   'name' => $data['gpdpname'],
                   'comment' => $data['gpdpcomment'],
                   'entities_id' => intval($data['gpdrentitiesid'])
                ]);
             } else {
                $row = $exist->next();
                $personne_concernee_id = $row['id'];
             }

             // Categories de Donnes
             $categorie_donnee_id = '';
             $exist = $DB->request('glpi_plugin_genericobject_personnesconcernees', ['name' => $data['gpddname'], 'entities_id'=> intval($data['gpdrentitiesid']) ]);
             if (empty(count($exist))) {
                $categorie_donnee_data = new PluginGenericobjectPersonnesconcernee();
                $categorie_donnee_id = $categorie_donnee_data->add([
                   'name' => $data['gpddname'],
                   'comment' => $data['gpddcomment'],
                   'entities_id' => intval($data['gpdrentitiesid'])
                ]);
             } else {
                $row = $exist->next();
                $categorie_donnee_id = $row['id'];
             }

             // Update Relationnal Table
             $relatinal_data = new PluginDlteamsRecord_PersonalAndDataCategory();
             $relatinal_data->update([
                'id' =>  $data['linkid'],
                'plugin_genericobject_personnesconcernees_id' => $categorie_donnee_id,
                'plugin_genericobject_dcptraitees_id' => $personne_concernee_id ,
             ]);
          }

       }
       if (isset($data['migrate_legal_bases']) && ($data['migrate_legal_bases'] == 1)) {
          //table types legalbases
          //$DB->request("TRUNCATE TABLE `glpi_plugin_genericobject_baseslegaletypes`");
          $items_list_type = PluginDlteamsLegalBasisAct::getAllTypesArray();
          $base_legale_type_id = [];
          foreach ($items_list_type as $key => $data) {
             $exist_type = $DB->request('glpi_plugin_genericobject_baseslegaletypes', ['name' => $DB->escape($data)]);
             if (empty(count($exist_type))) {
                $base_legale_type_data = new PluginGenericobjectBaseslegaletype();
                $base_legale_type_id[$key] = $base_legale_type_data->add([
                   'name' => $DB->escape($data),
                ]);
             } else {
                $row_type = $exist_type->next();
                $base_legale_type_id[$key]= $row_type['id'];
             }
          }
          // table types legalbases

          // Vider les tables glpi_plugin_genericobject_baseslegales avant de commencer l'import
          $DB->request("TRUNCATE TABLE `glpi_plugin_genericobject_baseslegales`");

          $keys = ['gpbname', 'gpbcomment','contenu'];
          $iterator = $DB->request(self::getRequest2());
          $number = count($iterator);

          $items_list = [];
          while ($data = $iterator->next()) {
             foreach ($data as $key => $value) {
                if (in_array($key,  $keys)) {
                   $data[$key] = $DB->escape($data[$key]);
                }
             }
             $items_list[$data['linkid']] = $data;
          }

          foreach ($items_list as $data) {
             $base_legale_id = '';
             $exist = $DB->request('glpi_plugin_genericobject_baseslegales', ['name' => $data['gpbname'], 'entities_id'=> intval($data['gpbentitiesid']) ]);
             if (empty(count($exist))) {
                $base_legale_data = new PluginGenericobjectBaseslegale();
                $base_legale_id = $base_legale_data->add([
                   'name' => $data['gpbname'],
                   'contenu'=>$data['contenu'],
                   'plugin_genericobject_baseslegaletypes_id' => $base_legale_type_id[$data['plugin_genericobject_baseslegaletypes_id']],
                   'comment' => $data['gpbcomment'],
                   'entities_id' => intval($data['gpbentitiesid']),
                ]);
             } else {
                $row = $exist->next();
                $base_legale_id = $row['id'];
             }

             // Update Relationnal Table
             $relatinal_data = new PluginDlteamsRecord_LegalBasisAct();
             $relatinal_data->update([
                'id' =>  $data['linkid'],
                'plugin_genericobject_baseslegales_id' => $base_legale_id,
             ]);
          }

       }

       if (isset($data['migrate_guid']) && ($data['migrate_guid'] == 1)) {

          $iterator = $DB->request(self::getRequest3());
          $number = count($iterator);
          $items_list = [];

          while ($data = $iterator->next()) {
             $items_list[$data['linkid']] = $data;
             $used[$data['linkid']] = $data['linkid'];
           }

           foreach($items_list as $value){

             if($value['guid']==0 || $value['guid']==NULL){
                $guidgenerated = bin2hex(openssl_random_pseudo_bytes(16));
                $guidkey=$value['linkid'];
                $query = "UPDATE `glpi_plugin_dlteams_controllerinfos` SET guid='$guidgenerated' WHERE id='$guidkey'";
              $DB->queryOrDie($query, $DB->error());
              if (!file_exists($glpiRoot. "/" . "ppd"."/" .$guidgenerated."/")) {
              mkdir($glpiRoot. "/" . "ppd"."/" .$guidgenerated."/");
           }
             }else if($value['guid']!=0 && $value['guid']!=NULL || (!file_exists($glpiRoot. "/" . "ppd"."/" .$value['guid']."/"))){
             mkdir($glpiRoot. "/" . "ppd"."/" .$value['guid']."/");
             }

           }
       }

       if (isset($data['migrate_security_measures']) && ($data['migrate_security_measures'] == 1)) {
          //table types mesuresecurite
          $DB->request("TRUNCATE TABLE `glpi_plugin_genericobject_mesuressecuritetypes`");
          $items_list_type = PluginDlteamsSecurityMeasure::getAllTypesArray();
          $mesure_securite_type_id = [];
          foreach ($items_list_type as $key => $data) {
             $exist_type = $DB->request('glpi_plugin_genericobject_mesuressecuritetypes', ['name' => $DB->escape($data)]);
             if (empty(count($exist_type))) {
                $mesure_securite_type_data = new PluginGenericobjectMesuressecuritetype();
                $mesure_securite_type_id[$key] = $mesure_securite_type_data->add([
                   'name' => $DB->escape($data),
                ]);
             } else {
                $row_type = $exist_type->next();
                $mesure_securite_type_id[$key]= $row_type['id'];
             }
          }
          // table types mesuresecurite


          // Vider les tables glpi_plugin_genericobject_mesuressecurites avant de commencer l'import
          $DB->request("TRUNCATE TABLE `glpi_plugin_genericobject_mesuressecurites`");

          $keys = ['gpbname', 'gpbcomment','contenu'];
          $iterator = $DB->request(self::getRequest4());
          $number = count($iterator);

          $items_list = [];
          while ($data = $iterator->next()) {
             foreach ($data as $key => $value) {
                if (in_array($key,  $keys)) {
                   $data[$key] = $DB->escape($data[$key]);
                }
             }
             $items_list[$data['linkid']] = $data;
          }

          foreach ($items_list as $data) {
             $mesure_securite_id = '';
             $exist = $DB->request('glpi_plugin_genericobject_mesuressecurites', ['name' => $data['gpbname'], 'entities_id'=> intval($data['gpbentitiesid']) ]);
             if (empty(count($exist))) {
                $mesure_securite_data = new PluginGenericobjectMesuressecurite();
                $mesure_securite_id = $mesure_securite_data->add([
                   'name' => $data['gpbname'],
                   'contenu'=>$data['contenu'],
                   'comment' => $data['gpbcomment'],
                   'entities_id' => intval($data['gpbentitiesid']),
                   'plugin_genericobject_mesuressecuritetypes_id'=>$mesure_securite_type_id[$data['typeid']],
                ]);
             } else {
                $row = $exist->next();
                $mesure_securite_id = $row['id'];
             }

             // Update Relationnal Table
             $relatinal_data = new PluginDlteamsRecord_SecurityMeasure();
             $relatinal_data->update([
                'id' =>  $data['linkid'],
                'plugin_genericobject_mesuressecurite_id' => $mesure_securite_id,
             ]);
          }

       }
       if (isset($data['migrate_impact']) && ($data['migrate_impact'] == 1)) {
          $iterator = $DB->request(self::getRequest6());
          $items_list = [];
          while ($data = $iterator->next()) {
             $items_list[$data['linkid']] = $data;
          }

          $impact_ids = [];
          $impact_organism_ids = [];
          $action_fin_periode_ids = [];
          $impact_names = [$DB->escape('Négligeable'), $DB->escape('Limité'), $DB->escape('Important'), $DB->escape('Maximum')];

          foreach ($impact_names  as $key => $name)  {
             $exist = $DB->request('glpi_plugin_dlteams_impacts', ['name' => $name, 'entities_id'=> '0' ]);
             if (empty(count($exist))) {
                $impact_data = new PluginDlteamsImpact();
                $impact_ids[$key]  = $impact_data->add([
                   'name' => $name,
                   'entities_id' => '0',
                ]);
             } else {
                $row = $exist->next();
                $impact_ids[$key] = $row['id'];
             }
          }


          foreach ($impact_names  as $key => $name)  {
             $exist = $DB->request('glpi_plugin_dlteams_actionfinperiodes', ['name' => $name, 'entities_id'=> '0' ]);
             if (empty(count($exist))) {
                $impact_data = new PluginDlteamsActionFinPeriode();
                $action_fin_periode_ids[$key]  = $impact_data->add([
                   'name' => $name,
                   'entities_id' => '0',
                ]);
             } else {
                $row = $exist->next();
                $action_fin_periode_ids[$key] = $row['id'];
             }
          }

          foreach ($impact_names  as $key => $name)  {
             $exist = $DB->request('glpi_plugin_dlteams_impactorganisms', ['name' => $name, 'entities_id'=> '0' ]);
             if (empty(count($exist))) {
                $impact_data = new PluginDlteamsImpactOrganism();
                $impact_organism_ids[$key]  = $impact_data->add([
                   'name' => $name,
                   'entities_id' => '0',
                ]);
             } else {
                $row = $exist->next();
                $impact_organism_ids[$key] = $row['id'];
             }
          }

          foreach ($items_list as $data) {
             if (!empty($data['violation_impact'])) {
                $checked = json_decode($data['violation_impact']);
                $exist = $DB->request('glpi_plugin_dlteams_records_impacts', ['plugin_dlteams_records_id' => $data['linkid']]);
                if (empty(count($exist))) {
                   $impact_data = new PluginDlteamsRecord_Impact();
                   $impact_id = $impact_data->add([
                      'plugin_dlteams_records_id' => $data['linkid'],
                      'plugin_dlteams_impacts_id'=> $impact_ids[$checked->checked],
                   ]);
                } else {
                   $impact_data = new PluginDlteamsRecord_Impact();
                   $impact_id = $impact_data->update([
                      'plugin_dlteams_records_id' => $data['linkid'],
                      'plugin_dlteams_impacts_id'=> $impact_ids[$checked->checked],
                   ]);
                }
             }

             if (!empty($data['violation_impact'])) {
                $checked = json_decode($data['violation_impact']);
                $exist = $DB->request('glpi_plugin_dlteams_records_actionfinperiodes', ['plugin_dlteams_records_id' => $data['linkid']]);
                if (empty(count($exist))) {
                   $impact_data = new PluginDlteamsRecord_ActionFinPeriode();
                   $action_fin_periode_ids = $impact_data->add([
                      'plugin_dlteams_records_id' => $data['linkid'],
                      'plugin_dlteams_actionfinperiodes_id'=> $action_fin_periode_ids[$checked->checked],
                   ]);
                } else {
                   $impact_data = new PluginDlteamsRecord_Impact();
                   $action_fin_periode_ids= $impact_data->update([
                      'plugin_dlteams_records_id' => $data['linkid'],
                      'plugin_dlteams_actionfinperiodes_id'=> $action_fin_periode_ids[$checked->checked],
                   ]);
                }
             }

             if (!empty($data['violation_impact_level'])) {
                $checked = json_decode($data['violation_impact_level']);
 */

    /*         if (!empty($checked->other)) {
                  $exist = $DB->request('glpi_plugin_dlteams_impactorganisms', ['name' => $checked->other, 'entities_id'=> '0' ]);
                  if (empty(count($exist))) {
                     $impact_data = new PluginDlteamsImpactOrganism();
                     $impact_organism_ids['4']  = $impact_data->add([
                        'name' => $checked->other,
                        'entities_id' => '0',
                     ]);
                  } else {
                     $row = $exist->next();
                     $impact_organism_ids['4'] = $row['id'];
                  }

               } */

    /*               $exist = $DB->request('glpi_plugin_dlteams_records_impactorganisms', ['plugin_dlteams_records_id' => $data['linkid']]);
                   if (empty(count($exist)) && property_exists($checked, 'checked')) {
                      $impact_data = new PluginDlteamsRecord_ImpactOrganism();
                      $impact_organism_id = $impact_data->add([
                         'plugin_dlteams_records_id' => $data['linkid'],
                         'plugin_dlteams_impactorganisms_id'=> $impact_organism_ids[$checked->checked],
                      ]);
                   } elseif (!empty(count($exist)) && property_exists($checked, 'checked')) {
                      $impact_data = new PluginDlteamsRecord_ImpactOrganism();
                      $impact_organism_id = $impact_data->update([
                         'plugin_dlteams_records_id' => $data['linkid'],
                         'plugin_dlteams_impactorganisms_id'=> $impact_organism_ids[$checked->checked],
                      ]);
                   }
                }


             }

          }

          if (isset($data['import_duree_conservation']) && ($data['import_duree_conservation'] == 1)) {
             $DB->request("TRUNCATE TABLE `glpi_plugin_dlteams_records_storages`");
             $DB->request("TRUNCATE TABLE `glpi_plugin_dlteams_storages`");

             $iterator = $DB->request(self::getRequest5());
             $items_list = [];
             while ($data = $iterator->next()) {
                $items_list[$data['linkid']] = $data;
             }


             $storages_names = [$DB->escape('01 Bases actives'), $DB->escape('02 Archives intermédiaires'), $DB->escape('03 Archives longues')];
             $rgpdconservations_names = [$DB->escape('durée initiale'),  $DB->escape('mixte')];
             $storages_ids = [];

             foreach ($storages_names  as $key => $name)  {
                $exist = $DB->request('glpi_plugin_dlteams_storages', ['name' => $name, 'entities_id'=> '0' ]);
                if (empty(count($exist))) {
                   $storage_data= new PluginDlteamsStorage();
                   $storages_ids[$key]  = $storage_data->add([
                      'name' => $name,
                      'entities_id' => intval($data['entities_id']),
                   ]);
                } else {
                   $row = $exist->next();
                   $storages_ids[$key] = $row['id'];
                }
             }

             foreach ($items_list as $data) {
                $rgpdconservations_ids = [];
                // creation de stockage par entité
                foreach ($rgpdconservations_names  as $key => $name)  {
                   $exist = $DB->request('glpi_plugin_genericobject_rgpdconservations', ['name' => $name, 'entities_id'=> intval($data['entities_id']) ]);
                   if (empty(count($exist))) {
                      $rgpdconservations_data= new PluginGenericobjectRgpdconservation();
                      $rgpdconservations_ids[$key]  = $rgpdconservations_data->add([
                         'name' => $name,
                         'entities_id' => intval($data['entities_id'])
                      ]);

                   } else {
                      $row = $exist->next();
                      $rgpdconservations_ids[$key] = $row['id'];
                   }
                }
                if (!empty($data['conservation_time'])) {
                   $record_storage = new PluginDlteamsRecord_Storage();
                   $record_storage_id = $record_storage->add([
                      'plugin_dlteams_records_id' => $data['linkid'],
                      'plugin_dlteams_storages_id' => $storages_ids['0'],
                      'plugin_genericobject_rgpdconservations_id' => $rgpdconservations_ids['0'],
                      'storage_comment' => $DB->escape($data['conservation_time']),
                      'storage_action' => 'Suppression',
                   ]);
                }

                if ($data['archive_required'] == 1) {
                   $record_storage = new PluginDlteamsRecord_Storage();
                   $record_storage_id = $record_storage->add([
                      'plugin_dlteams_records_id' => $data['linkid'],
                      'plugin_dlteams_storages_id' => $storages_ids['1'],
                      'plugin_genericobject_rgpdconservations_id' => $rgpdconservations_ids['1'],
                      'storage_comment' => $DB->escape($data['archive_time']),
                      'storage_action' => 'Archivage',
                   ]);
                }

             }
          }
       }

       static function getRequest() {
          return [
             'SELECT' => [
                'glpi_plugin_dlteams_records_personalanddatacategories.id AS linkid',
                'glpi_plugin_dlteams_records.entities_id AS gpdrentitiesid',
                'glpi_plugin_dlteams_datasubjectscategories.id AS gpddid',
                'glpi_plugin_dlteams_datasubjectscategories.name AS gpddname',
                'glpi_plugin_dlteams_datasubjectscategories.comment AS gpddcomment',
                'glpi_plugin_dlteams_datasubjectscategories.entities_id AS gpddentitiesid',
                'glpi_plugin_dlteams_datasubjectscategories.is_recursive AS gpddrecursive',
                'glpi_plugin_dlteams_personaldatacategories.id AS gpdpid',
                'glpi_plugin_dlteams_personaldatacategories.name AS gpdpname',
                'glpi_plugin_dlteams_personaldatacategories.comment AS gpdpcomment',
                'glpi_plugin_dlteams_personaldatacategories.entities_id AS gpdpentitiesid',
                'glpi_plugin_dlteams_personaldatacategories.is_recursive AS gpdprecursive',
             ],
             'FROM' => 'glpi_plugin_dlteams_records_personalanddatacategories',
             'INNER JOIN' => [
                'glpi_plugin_dlteams_personaldatacategories' => [
                   'FKEY' => [
                      'glpi_plugin_dlteams_records_personalanddatacategories' => "plugin_dlteams_personaldatacategories_id",
                      'glpi_plugin_dlteams_personaldatacategories' => "id",
                   ]
                ]
             ],
             'JOIN' => [
                'glpi_plugin_dlteams_datasubjectscategories' => [
                   'FKEY' => [
                      'glpi_plugin_dlteams_records_personalanddatacategories' => "plugin_dlteams_datasubjectscategories_id",
                      'glpi_plugin_dlteams_datasubjectscategories' => "id",
                   ]
                ]
             ],
             'LEFT JOIN' => [
                'glpi_plugin_dlteams_records' => [
                   'FKEY' => [
                      'glpi_plugin_dlteams_records_personalanddatacategories' => "plugin_dlteams_records_id",
                      'glpi_plugin_dlteams_records' => "id",
                   ]
                ]
             ]
          ];
       }

       static function getRequest2() {
          return [
             'SELECT' => [
                'glpi_plugin_dlteams_records_legalbasisacts.id AS linkid',
                'glpi_plugin_dlteams_legalbasisacts.id AS gpbid',
                'glpi_plugin_dlteams_legalbasisacts.name AS gpbname',
                'glpi_plugin_dlteams_legalbasisacts.comment AS gpbcomment',
                'glpi_plugin_dlteams_legalbasisacts.is_recursive AS gpbrecursive',
                'glpi_plugin_dlteams_records.entities_id AS gpbentitiesid',
                'glpi_plugin_dlteams_legalbasisacts.content AS contenu',
                'glpi_plugin_dlteams_legalbasisacts.type AS plugin_genericobject_baseslegaletypes_id',

             ],
             'FROM' => 'glpi_plugin_dlteams_records_legalbasisacts',
             'LEFT JOIN' => [
                'glpi_plugin_dlteams_legalbasisacts' => [
                   'FKEY' => [
                      'glpi_plugin_dlteams_records_legalbasisacts' => "plugin_dlteams_legalbasisacts_id",
                      'glpi_plugin_dlteams_legalbasisacts' => "id",
                   ]
                ]
             ],
             'JOIN' => [
                'glpi_plugin_dlteams_records' => [
                   'FKEY' => [
                      'glpi_plugin_dlteams_records_legalbasisacts' => "plugin_dlteams_records_id",
                      'glpi_plugin_dlteams_records' => "id",
                   ]
                ]
             ]
          ];
       }
       static function getRequest3() {
          return [
             'SELECT' => [
                'glpi_plugin_dlteams_controllerinfos.id AS linkid',
                'glpi_plugin_dlteams_controllerinfos.guid AS guid',

             ],
             'FROM' => 'glpi_plugin_dlteams_controllerinfos',
          ];
       }

       static function getRequest4() {
          return [
             'SELECT' => [
                'glpi_plugin_dlteams_records_securitymeasures.id AS linkid',
                'glpi_plugin_dlteams_securitymeasures.id AS gpbid',
                'glpi_plugin_dlteams_securitymeasures.name AS gpbname',
                'glpi_plugin_dlteams_securitymeasures.comment AS gpbcomment',
                'glpi_plugin_dlteams_securitymeasures.is_recursive AS gpbrecursive',
                'glpi_plugin_dlteams_records.entities_id AS gpbentitiesid',
                'glpi_plugin_dlteams_securitymeasures.content AS contenu',
                'glpi_plugin_dlteams_securitymeasures.type AS typeid',

             ],
             'FROM' => 'glpi_plugin_dlteams_records_securitymeasures',
             'LEFT JOIN' => [
                'glpi_plugin_dlteams_securitymeasures' => [
                   'FKEY' => [
                      'glpi_plugin_dlteams_records_securitymeasures' => "plugin_dlteams_securitymeasures_id",
                      'glpi_plugin_dlteams_securitymeasures' => "id",
                   ]
             ],
             ],
             'JOIN' => [
                'glpi_plugin_dlteams_records' => [
                   'FKEY' => [
                      'glpi_plugin_dlteams_records_securitymeasures' => "plugin_dlteams_records_id",
                      'glpi_plugin_dlteams_records' => "id",
                   ]
             ],
             ],
          ];
       }
       static function getRequest5() {
          return [
             'SELECT' => [
                'glpi_plugin_dlteams_records.entities_id AS entities_id',
                'glpi_plugin_dlteams_records.archive_required AS archive_required',
                'glpi_plugin_dlteams_records.conservation_time AS conservation_time',
                'glpi_plugin_dlteams_records.archive_time AS archive_time',
                'glpi_plugin_dlteams_records.id AS linkid',
             ],
             'FROM' => 'glpi_plugin_dlteams_records',
          ];
       }
       static function getRequest6() {
          return [
             'SELECT' => [
                'glpi_plugin_dlteams_records.entities_id AS entities_id',
                'glpi_plugin_dlteams_records.violation_impact AS violation_impact',
                'glpi_plugin_dlteams_records.violation_impact_level AS violation_impact_level',
                'glpi_plugin_dlteams_records.id AS linkid',
             ],
             'FROM' => 'glpi_plugin_dlteams_records',
          ];
       }*/

    /*   function project(){
           global $DB;
                $dbu = new DbUtils();
                $name=str_replace('"', '', addslashes($item->fields['name']));
                $entities_ori=$item->fields['entities_id'];
                $id_ori=$item->fields['id'];
                $date = date('Y-m-d H:i:s');
                $iduser=Session::getLoginUserID();

                $nb=$dbu->countElementsInTable('glpi_projects', ['name' => $name, 'entities_id' => $entity]);
                    if($nb<=0){

                    // glpi_project
                         $DB->request("INSERT INTO glpi_projects (name,priority,entities_id,is_recursive,date,date_mod,users_id,content,comment,is_deleted,date_creation,is_template,template_name) SELECT name',priority,'$entity',is_recursive,'$date','$date','$users_id',content,comment,is_deleted,'$date',is_template,template_name FROM glpi_projects WHERE id='$id_ori'");
                         $req=$DB->request("SELECT id FROM glpi_projects WHERE name = '$name' AND entities_id = '$entity'");
                         foreach ($req as $id => $row) {
                            $idProjet=$row['id']; //get id of copied project
                         }

                         // glpi_projecttasks
                            $reqprojecttasks=$DB->request("SELECT * FROM glpi_projecttasks WHERE projects_id='$id_ori' AND entities_id='$entities_ori'");
                            var_dump(count($reqprojecttasks));
                                if (count($reqprojecttasks)) {
                                    foreach ($reqprojecttasks as $id => $row) {
                                        $valC=$row['id'];
                                        $nameC=str_replace('"', '', addslashes($row['name']));

                                        //check value
                                        $nb=$dbu->countElementsInTable('glpi_projecttasks', ['name' => $nameC, 'entities_id' => $entity]);
                                        if($nb<=0){
                                            $DB->request("INSERT INTO glpi_projecttasks (name,content,comment,entities_id,is_recursive,projects_id,date_creation,date_mod,projectstates_id,projecttasktypes_id,users_id,is_template,template_name) SELECT name,content,comment,'$entity',is_recursive,'$idProjet','$date','$date',projectstates_id,projecttasktypes_id,'$users_id',is_template,template_name FROM glpi_projecttasks WHERE id='$valC'");
                                        }else{
                                            //we do nothing
                                        }
                                        //check value

                                        // glpi_projecttasks_tickets
                                        $reqprojecttasks_tickets=$DB->request("SELECT * FROM glpi_projecttasks_tickets WHERE projecttasks_id='$valC'");
                                        var_dump(count($reqprojecttasks_tickets));
                                            if (count($reqprojecttasks_tickets)) {
                                                foreach ($reqprojecttasks_tickets as $id => $row) {
                                                    $valD=$row['tickets_id'];

                                                    $DB->request("INSERT INTO glpi_projecttasks_tickets (tickets_id,projecttasks_id) SELECT tickets_id,projecttasks_id FROM glpi_projecttasks_tickets WHERE projecttasks_id='$valC'");

                                                    //
                                                    $test=$DB->request("SELECT name FROM glpi_projecttasks WHERE id='$valC' AND entities_id='$entities_ori'");
                                                    foreach ($test as $id => $row) {
                                                        $name1=str_replace('"', '', addslashes( $row['name']));
                                                        $test1=$DB->request("SELECT * FROM glpi_projecttasks WHERE name='$name1' AND entities_id='$entity'");

                                                        if (count($test1)>0) {
                                                            foreach ($test1 as $id => $row) {
                                                                $idprojecttasks=$row['id']; // new copied id
                                                            }
                                                             $DB->request("UPDATE glpi_projecttasks_tickets set projecttasks_id='$idprojecttasks' WHERE projecttasks_id='$valC' and tickets_id='$valD'");
                                                        }
                                                    }
                                                    //

                                                    //copy ticket
                                                    $DB->request("INSERT INTO glpi_tickets (entities_id,name,date,date_mod,users_id_lastupdater,status,users_id_recipient,requesttypes_id,content,urgency,impact,priority,itilcategories_id,type,global_validation,date_creation) SELECT '$entity',name,'$date','$date','$iduser',status,'$iduser',requesttypes_id,content,urgency,impact,priority,itilcategories_id,type,global_validation,'$date' FROM glpi_tickets WHERE id='$valD'");

                                                     //
                                                    $test=$DB->request("SELECT name FROM glpi_tickets WHERE id='$valD' AND entities_id='$entities_ori'");
                                                    foreach ($test as $id => $row) {
                                                        $name2=str_replace('"', '', addslashes( $row['name']));
                                                        $test1=$DB->request("SELECT * FROM glpi_tickets WHERE name='$name2' AND entities_id='$entity'");

                                                        if (count($test1)>0) {
                                                            foreach ($test1 as $id => $row) {
                                                                $idtickets=$row['id']; // new copied id
                                                            }
                                                             $DB->request("UPDATE glpi_projecttasks_tickets set tickets_id='$idtickets' WHERE projecttasks_id='$idprojecttasks' AND tickets_id='$valD'");
                                                        }
                                                    }

                                                     //
                                                    //copy ticket

                                                    // glpi_tickettasks
                                                    $reqtickettasks=$DB->request("SELECT * FROM glpi_tickettasks WHERE tickets_id='$valD'");
                                                    var_dump(count($reqtickettasks));
                                                        if (count($reqtickettasks)) {
                                                            foreach ($reqtickettasks as $id => $row) {
                                                                 $valE=$row['id'];
                                                                 // copy ticket task

                                                                 $DB->request("INSERT INTO glpi_tickettasks (tickets_id,taskcategories_id,date,users_id,content,state,users_id_tech,date_mod,date_creation,tasktemplates_id) SELECT '$idtickets',taskcategories_id,'$date','$iduser',content,state,'$iduser','$date','$date',tasktemplates_id FROM glpi_tickettasks WHERE tickets_id='$valD'");
                                                                 //copy ticket task
                                                            }
                                                        }
                                                    // glpi_tickettasks
                                                }
                                            }else{
                                            }
                                        // glpi_projecttasks_tickets
                                    }
                                }else{

                                    //we do nothing

                                }
                            // glpi_projecttasks


                        // glpi_project

                        return true;

                    }else{

                        return false;

                    }

       }*/

}