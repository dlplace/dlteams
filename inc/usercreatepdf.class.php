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


class PluginDlteamsUserCreatePDF extends PluginDlteamsCreatePDFBase
{

    static protected $default_print_options = [
        'show_representative' => [
            'show' => 1,
            'show_title' => 0,
            'show_address' => 0,
            'show_phone' => 1,
            'show_email' => 1,
        ],
        'show_dpo' => [
            'show' => 1,
            'show_title' => 0,
            'show_address' => 0,
            'show_phone' => 1,
            'show_email' => 1,
        ],
        'page_orientation' => 'P',
        'show_inherited_from' => false,
        'show_comments' => false,
        'show_print_date_time' => true,
        'show_is_deleted_header' => 1,
        'show_status_in_header' => 1,
        'show_full_personaldatacategorylist' => 1,
        'show_expired_contracts' => 1,
        'show_contracs_types_header_if_empty' => 0,
        'show_record_owner' => 1,
        'show_assets_owners' => 1,
        'show_deleted_records_for_entity' => 0,
        'show_representative_dpo_per_record' => 0,
        'show_supplier_informations' => 1,
    ];
    private $HTML;
    private $peripherals = [
        Computer::class,
        Phone::class,
        NetworkEquipment::class,
        Monitor::class,
        Peripheral::class,
        PassiveDCEquipment::class,
        Printer::class,
//        Datacenter::class,
//        Domain::class,
        Appliance::class,
//        PluginDlteamsPhysicalStorage::class
    ];


    public function __construct()
    {
        $this->HTML = false;
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$item->canView()) {
            return false;
        }

        switch ($item->getType()) {
            case PluginDlteamsRecord::class :
                return self::createTabEntry(PluginDlteamsUserCreatePDF::getTypeName(0), 0);
            default:
                return self::createTabEntry(PluginDlteamsUserCreatePDF::getTypeName(0), 0);
                break;
        }


        return '';
    }

    static function getTypeName($nb = 0)
    {
        return __("Fiche Utilisateur", 'dlteams');
    }



    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        global $CFG_GLPI;
        $user = new User();
        $user_id = $item->fields["id"];
        $user->getFromDB($user_id);


        echo "<form name='form' method='GET' action=\"" . $CFG_GLPI['root_doc'] . "/marketplace/dlteams/front/usercreatepdf.php\" enctype='multipart/form-data'>";

        echo "<div class='spaced' id='tabsbody'>";

        echo "<table class='tab_cadre_fixe' id='mainformtable'>";
        echo "<tbody>";
        echo "<tr class='headerRow'>";
        echo "<th colspan='3' class=''>" . __("PDF creation settings", 'dlteams') . "</th>";
        echo "</tr>";


//      $_config['report_type'] = $report_type;
        self::showConfigFormElements($user->fields);

        echo "</table>";
        echo "</div>";

        echo "<input type='hidden' name='user_id' value='" . $user_id . "'>";

//        echo "<input type='submit' class='submit' name='save' value='" . __("Save") . "' />";
//        echo "&nbsp;";

        echo "<input type='submit' class='submit' name='save' value='" . __("Enrégistrer", 'dlteams') . "' />";
        echo "&nbsp;";

        echo "<input type='submit' class='submit' name='createpdf' value='" . __("Generate PDF", 'dlteams') . "' />";
        echo "&nbsp;";
        echo "<input type='submit' class='submit' name='createhtml' value='" . __("Generate HTML", 'dlteams') . "' />";
        echo "&nbsp;";
        echo "<input type='submit' class='submit' name='createhtmlppd' value='" . __("Publier DLregister", 'dlteams') . "' />";
        Html::closeForm();
    }

    static function showConfigFormElements($user)
    {


        echo "<tr class='tab_bg_1'>";
        echo "<td width='50%'>" . __("Print logo", 'dlteams') . "</td>";
        echo "<td width='50%'>";


        /**add by me**/
//      Dropdown::showYesNo('print_first_page', 0);
        if(isset($user["print_logo"]) && $user["print_logo"])
            $checked = "checked";
        else
            $checked = "";

        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title='Imprimer le logo'>
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='print_logo' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";
        /**add by me**/
        echo "</td>";
        echo "</tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td width='50%'>" . __("Autoriser l'utilisation des périphériques personnels", 'dlteams') . "</td>";
        echo "<td width='50%'>";


        /**add by me**/
//      Dropdown::showYesNo('print_first_page', 0);
        if(isset($user["allow_specific_personnal_device"]) && $user["allow_specific_personnal_device"])
            $checked = "checked";
        else
            $checked = "";

        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title='Autoriser l\'utilisation des périphériques personnels'>
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='allow_specific_personnal_device' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";
        /**add by me**/
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Show print date/time", 'dlteams') . "</td>";
        echo "<td>";
//        Dropdown::showYesNo('show_print_date_time', $config['show_print_date_time']);
        if(isset($user["print_datetime"]) && $user["print_datetime"])
            $checked = "checked";
        else
            $checked = "";
        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title=\"" . addslashes(__("Show print date/time", 'dlteams')) . "\">
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='show_print_date_time' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";


        echo "</td>";
        echo "</tr>";

//        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("L'organisme autorise l'utilisation de matériel personnel pour se connecter à tout ou partie de son système d'information et l'utilisateur accepte d'utiliser certains de ces périphériques", 'dlteams') . "</td>";
        echo "<td>";
//        Dropdown::showYesNo('show_print_date_time', $config['show_print_date_time']);
        isset($user["personal_equipment"]) && $user["personal_equipment"] ? $checked = '' : $checked = 'checked';
        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title=\"" . stripslashes(__("L'organisme autorise l'utilisation de matériel personnel pour se connecter à tout ou partie de son système d'information et l'utilisateur accepte d'utiliser certains de ces périphériques", 'dlteams')) . "\">
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='show_print_date_time' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";
        echo "</td>";
        echo "</tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Choose dlteams folder", 'dlteams') . "</td>";
        echo "<td colspan='2' style='display: flex; gap: 5px; align-items: center'>";

        $links = static::getPublicationFoldersLinks();

        $links_list = [];
        foreach ($links as $link) {
            array_push($links_list, $link);
        }

        Dropdown::show(Link::class, [
            'name' => 'choosen_publication_folder',
            'value' => $user["links_id"]
        ]);


        if ($user && isset($user["links_id"]) && $user["links_id"]) {
            $link = new Link();
            $link->getFromDB($user["links_id"]);
            $folder_link = $link->fields["link"];
            echo "<div> <a class='btn btn-outline-secondary' style='display: block' target='_blank' href='" . $folder_link . "' id='btn_publication_folder'><i class='fa fa-eye'></i></a> </div>";
        }

      else
            echo "<div> <a class='btn btn-outline-secondary' style='display: none' target='_blank' id='btn_publication_folder'><i class='fa fa-eye'></i></a> </div>";
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Commentaires", 'dlteams') . "</td>";
        echo "<td width='75%'>";
        $cols = 10;
        $rows = 5;
        Html::textarea(['name' => 'edition_comment',
            'value' => $user["edition_comment"],
            'enable_fileupload' => false,
            'enable_richtext' => false,
            'cols' => $cols,
            'rows' => $rows
        ]);
        echo "</td></tr>";


        echo "
        <script>
    $('select[name=choosen_publication_folder]').on('change', function (e) {
        if ($(this).val() != '0') {
            document.getElementById('btn_publication_folder').style.display = 'block';
            // alert($(this).val());


            $.ajax({
                url: '/marketplace/dlteams/ajax/get_publication_folder_link.php',
                type: 'POST',
                data: {
                    'folder_id': $(this).val()
                },
                success: function (html) {
                    // $('#document_bloc').html(html);
                    document.getElementById('btn_publication_folder').setAttribute('href', html);
                }
            });
        } else
            document.getElementById('btn_publication_folder').style.display = 'none';
    })

</script>
        ";
    }

    static function getPublicationFoldersLinks()
    {
        global $DB;

        $query = [
            "FROM" => Link::getTable()
        ];

        return $DB->request($query);
    }

    public function addPageForUser($user_id, $options = []){

/*        highlight_string("<?php\n\$data =\n" . var_export($options, true) . ";\n?>");*/
//        die();
        $user = new User();
        $user->getFromDB($user_id);

        $usercategory = new UserCategory();
        $usercategory->getFromDB($user->fields["usercategories_id"]);

        $usersupervisor = new User();
        $usersupervisor->getFromDB($user->fields["users_id_supervisor"]);

        $entity = new Entity();
        $entity->getFromDB(Session::getActiveEntity());


        $usergroup = new Group();
        $usergroup->getFromDB($user->fields["groups_id"]);


//        periphériques individuels, elements utilisés ( statut user )
        $peripheriquesindividuels = $this->getPeripheriquesIndividuels($user_id);
        $peripheriquesmutualises = $this->getPeripheriquesMutualises($user_id);
        $peripheriquesadmin["throughgroup"] = $this->getPeripheriquesAdminTroughtGroup($user_id);
        $peripheriquesadmin["throughuser"] = $this->getPeripheriquesAdminTroughtUser($user_id);
        $clesphysiques = $this->getClesPhysiques($user_id);
        $cles = $this->getCles($user_id);
        $usercatalogs = $this->getUserCatalogs($user_id);
        /*        highlight_string("<?php\n\$data =\n" . var_export($usercatalogs, true) . ";\n?>");*/
//        die();
        $user_phones = $this->getPhones($user_id);
        $user_computers = $this->getComputers($user_id);

        $controller_info = PluginDlteamsControllerInfo::getFirstControllerInfo(Session::getActiveEntity());

        $logo = new Document();
        $logo->getFromDB($controller_info->fields['logo_id']);
        $logo_path = isset($logo->fields['filepath']) ? ($logo->fields['filepath']) : '';

        $logo_uri = "";
        if (isset($logo->fields['filepath'])) {
            $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
            $logo_uri = PluginDlteamsUtils::dataUri($glpiRoot . "/" . "files" . "/" . $logo_path);
        }

        \Glpi\Application\View\TemplateRenderer::getInstance()->display('@dlteams/pages/user_card.html.twig', [
            "isHtml" => $this->HTML,
            "user" => $user->fields,
            "organisme" => $entity->fields,
            "supervisor" => $usersupervisor->fields,
            "group" => $usergroup->fields,
            "logo_uri" => $logo_uri,
            "options" => $options,
            "print_logo" => $logo_uri,
            "user_category" => $usercategory->fields,
            "ispdf" => isset($options["ispdf"]) ? $options["ispdf"] : false,
            "title" => "Fiche utilisateur",
            "print_comments" => isset($print_options['print_comments']) ? $print_options['print_comments'] : false,
            "first_page" => false,
            "peripheriquesindividuels" => $peripheriquesindividuels,
            "peripheriquesmutualises" => $peripheriquesmutualises,
            "peripheriquesadmin" => $peripheriquesadmin,
            "clesphysiques" => $clesphysiques,
            "cles" => $cles,
            "usercatalogs" => $usercatalogs,
            "phones" => $user_phones,
            "computers" => $user_computers,
            "css_files" => [
                [
                    "path" => "https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
                ]
            ],
            "js_files" => [
                [
                    "path" => "https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"
                ]
            ]
        ]);
    }

    public function generatePDF($user_id, $options = [])
    {
        $this->addPageForUser($user_id, $options);
    }


    private function getPeripheriquesIndividuels($user_id)
    {


        global $CFG_GLPI;
        global $DB;
//        $type_user = $CFG_GLPI['linkuser_types'];
        $type_user = $this->peripherals;
        $field_user = 'users_id';
        $peripheriques = [];
        foreach ($type_user as $itemtype) {
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }
            if ($item->canView()) {
                $itemtable = getTableForItemType($itemtype);
                $iterator_params = [
                    'FROM' => $itemtable,
                    'WHERE' => [$field_user => $user_id]
                ];

                if ($item->maybeTemplate()) {
                    $iterator_params['WHERE']['is_template'] = 0;
                }
                if ($item->maybeDeleted()) {
                    $iterator_params['WHERE']['is_deleted'] = 0;
                }

/*                highlight_string("<?php\n\$data =\n" . var_export($iterator_params, true) . ";\n?>");*/
//                die();
                $item_iterator = $DB->request($iterator_params);

                $type_name = $item->getTypeName();

                foreach ($item_iterator as $data) {
                    $data["typename"] = $type_name;

                    if ($data[$field_user] == $user_id) {
//                        status user
                        $peripheriques[] = $data;
                    }
                }
            }
        }

        return $peripheriques;
    }

    private function getPeripheriquesMutualises($user_id)
    {
        global $CFG_GLPI;
        global $DB;
        $type_group = $this->peripherals;
        $field_group = 'groups_id';
        $peripheriques = [];


        $groups = [];

        $iterator = $DB->request([
            'SELECT' => [
                'glpi_groups_users.groups_id',
                'glpi_groups.name'
            ],
            'FROM' => 'glpi_groups_users',
            'LEFT JOIN' => [
                'glpi_groups' => [
                    'FKEY' => [
                        'glpi_groups_users' => 'groups_id',
                        'glpi_groups' => 'id'
                    ]
                ]
            ],
            'WHERE' => ['glpi_groups_users.users_id' => $user_id]
        ]);

        $group_where = [];
        foreach ($iterator as $data) {
            $group_where[$field_group][] = $data['groups_id'];
            $groups[$data["groups_id"]] = $data["name"];
        }

        foreach ($type_group as $itemtype) {
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }
            if ($item->canView() && $item->isField($field_group)) {
                $itemtable = getTableForItemType($itemtype);
                $iterator_params = [
                    'FROM' => $itemtable,
                    'WHERE' => ['OR' => $group_where]
                ];

                if ($item->maybeTemplate()) {
                    $iterator_params['WHERE']['is_template'] = 0;
                }
                if ($item->maybeDeleted()) {
                    $iterator_params['WHERE']['is_deleted'] = 0;
                }

                $group_iterator = $DB->request($iterator_params);

                $type_name = $item->getTypeName();

                foreach ($group_iterator as $data) {

                    $data["typename"] = $type_name;
                    if (isset($groups[$data[$field_group]])) {
                        $peripheriques[] = $data;
                    }

                }
            }
        }


        return $peripheriques;
    }


    private function getPeripheriquesAdminTroughtGroup($user_id)
    {
        global $CFG_GLPI;
        global $DB;
        $throughgroup = [];

        $type_group = $this->peripherals;
        $field_group = 'groups_id_tech';


        $groups = [];

        $iterator = $DB->request([
            'SELECT' => [
                'glpi_groups_users.groups_id',
                'glpi_groups.name'
            ],
            'FROM' => 'glpi_groups_users',
            'LEFT JOIN' => [
                'glpi_groups' => [
                    'FKEY' => [
                        'glpi_groups_users' => 'groups_id',
                        'glpi_groups' => 'id'
                    ]
                ]
            ],
            'WHERE' => ['glpi_groups_users.users_id' => $user_id]
        ]);

        $group_where = [];
        foreach ($iterator as $data) {
            $group_where[$field_group][] = $data['groups_id'];
            $groups[$data["groups_id"]] = $data["name"];
        }


        foreach ($type_group as $itemtype) {
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }

            $itemtable = getTableForItemType($itemtype);
            $iterator_params = [
                'FROM' => $itemtable,
                'WHERE' => ['OR' => $group_where]
            ];

            if ($item->maybeTemplate()) {
                $iterator_params['WHERE']['is_template'] = 0;
            }
            if ($item->maybeDeleted()) {
                $iterator_params['WHERE']['is_deleted'] = 0;
            }

            $group_iterator = $DB->request($iterator_params);

            $type_name = $item->getTypeName();

            foreach ($group_iterator as $data) {

                $data["typename"] = $type_name;
                if (isset($groups[$data[$field_group]])) {
                    $throughgroup[] = $data;
                }

            }
        }


        return $throughgroup;
    }

    private function getPeripheriquesAdminTroughtUser($user_id)
    {
        global $CFG_GLPI;
        global $DB;

        $type_user = $this->peripherals;
        $field_user = 'users_id_tech';


        $peripheriques = [];
        foreach ($type_user as $itemtype) {
            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }
            if ($item->canView()) {
                $itemtable = getTableForItemType($itemtype);
                $iterator_params = [
                    'FROM' => $itemtable,
                    'WHERE' => [$field_user => $user_id]
                ];

                if ($item->maybeTemplate()) {
                    $iterator_params['WHERE']['is_template'] = 0;
                }
                if ($item->maybeDeleted()) {
                    $iterator_params['WHERE']['is_deleted'] = 0;
                }

                $item_iterator = $DB->request($iterator_params);

                $type_name = $item->getTypeName();

                foreach ($item_iterator as $data) {
                    $data["typename"] = $type_name;

                    if ($data[$field_user] == $user_id) {
//                        status user
                        $peripheriques[] = $data;
                    }
                }
            }
        }

        return $peripheriques;
    }


    private function getClesPhysiques($user_id)
    {
        $user = new User();
        $user->getFromDB($user_id);
        $iterator = PluginDlteamsAccountKey_Item::getUserComptesPhysique($user);
        $keys = [];
        foreach ($iterator as $key) {
            $keys[] = $key;
        }

        return $keys;
    }


    private function getCles($user_id)
    {
        $user = new User();
        $user->getFromDB($user_id);
        $iterator = PluginDlteamsAccountKey_Item::getUserComptes($user);
        $keys = [];
        foreach ($iterator as $key) {
            $keys[] = $key;
        }

        return $keys;
    }

    private function getPhones($user_id)
    {
        $user = new User();
        $user->getFromDB($user_id);
        $iterator = PluginDlteamsAccountKey_Item::getUserPhones($user_id);
        $phones = [];
        foreach ($iterator as $phone) {
            $phones[] = $phone;
        }

        return $phones;
    }


    private function getComputers($user_id)
    {
        $user = new User();
        $user->getFromDB($user_id);
        $iterator = PluginDlteamsAccountKey_Item::getUserComputers($user_id);
        $computers = [];
        foreach ($iterator as $computer) {
            $computers[] = $computer;
        }

        return $computers;
    }


    private function getUserCatalogs($user_id)
    {
        return PluginDlteamsAccountKey_Item::getUserCatalogs($user_id);
    }


    function publishDlRegister($generator_options, $print_options)
    {

        $temp_user = new User();
        $temp_user->getFromDB($generator_options['user_id']);


        $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
        ob_start();
        global $DB;
        $this->HTML = true;

        $entities_id = $_SESSION['glpiactive_entity'];
        $entity = new Entity();
        $entity->getFromDB($entities_id);
        $filename = PluginDlteamsUtils::normalize(sprintf("fiche-utilisateur-%s", $temp_user->fields["name"]));
        $this->preparePrintOptions($print_options);



        $this->addPageForUser($generator_options['user_id'], $generator_options);

        $directory = $glpiRoot . "/pub/" . $print_options['guid_value'];

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file_path = $directory . "/" . $filename . ".html";
        file_put_contents($file_path, ob_get_contents());
//        var_dump($filename);
//        die();
    }

    static function preparePrintOptionsFromForm($config = [])
    {

        $mod_config = self::getDefaultPrintOptions();

        if (is_array($config) && count($config)) {
            foreach ($config as $key => $val) {
                if (is_array($val) && count($val)) {
                    foreach ($val as $key2 => $val2) {
                        $mod_config[$key][$key2] = $val2;
                    }
                } else {
                    $mod_config[$key] = $val;
                }
            }
        }

        return $mod_config;
    }

    static function getDefaultPrintOptions()
    {

        $opt = self::$default_print_options;

        if (PluginDlteamsCreatePDFBase::isGdprownerPluginActive()) {
            $opt['show_assets_owners'] = 1;
        } else {
            $opt['show_assets_owners'] = 0;
        }
        $opt['show_record_owner'] = $opt['show_assets_owners'];

        return $opt;
    }

    function generateGuid($generator_options, $print_options)
    {

        global $DB;
        $glpiRoot = str_replace('\\', '/', GLPI_ROOT);

        $guidgenerated = bin2hex(openssl_random_pseudo_bytes(16));
        $query = "UPDATE `glpi_plugin_dlteams_controllerinfos` SET guid='$guidgenerated' WHERE entities_id='$entities_id'";
        $DB->queryOrDie($query, $DB->error());
        if (!file_exists($glpiRoot . "/" . "pub" . "/" . $guidgenerated . "/")) {
            $directory = $glpiRoot . "/pub/" . $guidgenerated;
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
        }
    }

    public function generateHTML(mixed $user_id, array $options)
    {
        $this->addPageForUser($user_id, $options);
    }

}

