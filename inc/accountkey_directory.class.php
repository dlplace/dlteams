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

class PluginDlteamsAccountKey_Directory extends CommonDBTM
{
    static public $itemtype_2 = PluginDlteamsAccountKey::class;
    static public $itemtype_1;
    public static $items_id_1 = 'accountkeys_id';
    private static string $title;

    function canCreateItem() {return true;}
    function canViewItem() {return true;}
    function canUpdateItem() {return true;}
    function canDeleteItem() {return true;}
    function canPurgeItem() {return true;}
    static function canCreate() {return true;}
    static function canView() {return true;}
    static function canUpdate() {return true;}
    static function canDelete() {return true;}

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case static::$itemtype_2:
                $ong = [];
                if (!$withtemplate) {
                    if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            $iterator = self::showItemsGetRequest($item);
                            $ong[] = static::createTabEntry(static::getTypeNameForClass(), count($iterator));
                        } else
                            $ong[] = static::getTypeNameForClass();
                    }
                }

                return $ong;
                break;
            default:
                if (!$withtemplate) {
                    $ong = [];
//                     on a ajoute l'onglet "Comptes de l'annuaire" si l'objet est un catalogue
                    if (($item::getType() == PluginDlteamsDataCatalog::class && $item->fields["is_directoryservice"]) || $item->fields["plugin_dlteams_datacatalogs_id"]) {
                        $iterator = static::showForItemgetRequest($item, true);
                        $ong[2] = static::createTabEntry(__("Comptes de l'annuaire", "dlteams"), count($iterator));
                    }
                    return $ong;
                }
                break;
        }
        return '';
    }

    public static function showForItemgetRequest(CommonDBTM $item, $isdirectory = false)
    {
        global $DB;

        if ($isdirectory && $item->getType() == PluginDlteamsDataCatalog::class)
            $query = [
                "SELECT" => [
                    PluginDlteamsDataCatalog_Item::getTable() . ".id as linkid",
                    PluginDlteamsDataCatalog_Item::getTable() . ".*",
                    PluginDlteamsAccountKey::getTable() . ".name as name",
                    'glpi_plugin_dlteams_keytypes' . '.name AS keytypename',
                    'glpi_plugin_dlteams_keytypes' . '.id AS keytypeid',
                ],
                "FROM" => PluginDlteamsDataCatalog_Item::getTable(),
                "LEFT JOIN" => [
                    PluginDlteamsAccountKey::getTable() => [
                        'FKEY' => [
                            PluginDlteamsAccountKey::getTable() => "id",
                            PluginDlteamsDataCatalog_Item::getTable() => "items_id"
                        ]
                    ],
                    'glpi_plugin_dlteams_keytypes' => [
                        'ON' => [
                            PluginDlteamsAccountKey::getTable() => 'plugin_dlteams_keytypes_id',
                            'glpi_plugin_dlteams_keytypes' => 'id'
                        ]
                    ]
                ],
                "WHERE" => [
                    "itemtype" => PluginDlteamsAccountKey::class,
                    "datacatalogs_id" => $item->fields["id"],
                    "is_directory" => true
                ]
            ];
        else
            $query = [
                "SELECT" => [
                    PluginDlteamsAccountKey_Item::getTable() . ".id as linkid",
                    PluginDlteamsAccountKey_Item::getTable() . ".*",
                    PluginDlteamsAccountKey::getTable() . ".name as name",
                    'glpi_plugin_dlteams_keytypes' . '.name AS keytypename',
                    'glpi_plugin_dlteams_keytypes' . '.id AS keytypeid',
                ],
                "FROM" => PluginDlteamsAccountKey_Item::getTable(),
                "LEFT JOIN" => [
                    PluginDlteamsAccountKey::getTable() => [
                        'FKEY' => [
                            PluginDlteamsAccountKey::getTable() => "id",
                            PluginDlteamsAccountKey_Item::getTable() => "accountkeys_id"
                        ]
                    ],
                    'glpi_plugin_dlteams_keytypes' => [
                        'ON' => [
                            PluginDlteamsAccountKey::getTable() => 'plugin_dlteams_keytypes_id',
                            'glpi_plugin_dlteams_keytypes' => 'id'
                        ]
                    ]
                ],
                "WHERE" => [
                    "itemtype" => $item->getType(),
                    "items_id" => $item->fields["id"]
                ]
            ];


        if ($item::getType() == PluginDlteamsDataCatalog::class && $item->fields["is_directoryservice"]) {
            $query = [
                "SELECT" => [
                    PluginDlteamsAccountKey::getTable() . ".*",
                    "glpi_plugin_dlteams_keytypes.name as keytypename"
                ],
                "FROM" => PluginDlteamsAccountKey::getTable(),
                "LEFT JOIN" => [
                    'glpi_plugin_dlteams_keytypes' => [
                        'ON' => [
                            'glpi_plugin_dlteams_keytypes' => "id",
                            PluginDlteamsAccountKey::getTable() => "plugin_dlteams_keytypes_id"
                        ]
                    ],
                ],
                "WHERE" => [
                    PluginDlteamsAccountKey::getTable() . ".entities_id" => $_SESSION['glpiactive_entity'],
                    PluginDlteamsAccountKey::getTable() . ".plugin_dlteams_datacatalogs_id" => $item->fields["id"]
                ],
                "ORDER" => ["name ASC"]
            ];
        } //         je pourrai retrouver pour ce catalogue enfant 2096, dont le parent est 2097 un onglet "Comptes de l'annuaire" ou je verrai les comptes du catalogue du parent (un compte ne peut être relié qu'à un seul annuaire et donc uniquement au catalogue parent)
        elseif ($item->fields["plugin_dlteams_datacatalogs_id"]) {
            $annuaire = new PluginDlteamsDataCatalog();
            $annuaire->getFromDB($item->fields["plugin_dlteams_datacatalogs_id"]);
            $annuaires_tiers_idx = [];
            foreach (PluginDlteamsDataCatalog::annuaireTiersRequest($annuaire) as $annuaire_tier)
                $annuaires_tiers_idx[] = $annuaire_tier["id"];
            $query = [
                "SELECT" => [
                    PluginDlteamsAccountKey::getTable() . ".*",
                    PluginDlteamsDataCatalog::getTable() . ".directory_name",
                    "glpi_plugin_dlteams_keytypes.name as keytypename"
                ],
                "FROM" => PluginDlteamsAccountKey::getTable(),
                "LEFT JOIN" => [
                    'glpi_plugin_dlteams_keytypes' => [
                        'ON' => [
                            'glpi_plugin_dlteams_keytypes' => "id",
                            PluginDlteamsAccountKey::getTable() => "plugin_dlteams_keytypes_id"
                        ]
                    ],
                    PluginDlteamsDataCatalog::getTable() => [
                        'ON' => [
                            PluginDlteamsDataCatalog::getTable() => "id",
                            PluginDlteamsAccountKey::getTable() => "plugin_dlteams_datacatalogs_id"
                        ]
                    ],
                ],
                "WHERE" => [
                    PluginDlteamsAccountKey::getTable() . ".entities_id" => $_SESSION['glpiactiveentities'],
                    PluginDlteamsAccountKey::getTable() . ".plugin_dlteams_datacatalogs_id" => [$item->fields["id"], $item->fields["plugin_dlteams_datacatalogs_id"], ...$annuaires_tiers_idx] // ajouter aussi ceux du parent
                ],
                "ORDER" => ["name ASC"]
            ];
        }
/*        highlight_string("<?php\n\$data =\n" . var_export($query, true) . ";\n?>");*/
//        die();

        $iterator = $DB->request($query);
        $result = [];
        foreach ($iterator as $data) {
            $result[] = $data;
        }
        return $result;
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
//            case static::$itemtype_2:
//                self::showItems($item);
//                break;
            default:
                self::showForItem($item, $tabnum == 2);
                break;
        }
    }

    public static function getTable($classname = null)
    {
        return PluginDlteamsAccountKey::getTable();
    }

    public function __construct()
    {
        self::forceTable(PluginDlteamsAccountKey::getTable());
    }

    public static function showForItemForm(CommonDBTM $item = null, $linkid = null, $is_directory = false)
    {
        $row = null;
        if ($linkid) {
            $row = new PluginDlteamsAccountKey_Item();
            $row->getFromDB($linkid);
        }
//        if($item::getType() == PluginDlteamsDataCatalog::class){
//            $row = new PluginDlteamsDataCatalog_Item();
//            $row->getFromDB($linkid);
//        }

        $rand = mt_rand();
        echo "<div class='firstbloc'>";
        echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
//            $iden = $item->fields['id'];
        echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsAccountKey::class . "' />";

        if ($linkid)
            echo "<input type='hidden' name='linkid' value='" . $linkid . "' />";

        if ($item) {
            echo "<input type='hidden' name='items_id1' value='" . $item->getID() . "' />";
            echo "<input type='hidden' name='entities_id' value='" . $item->fields['entities_id'] . "' />";
            echo "<input type='hidden' name='itemtype1' value='" . $item->getType() . "' />";
        }
        if ($is_directory)
            echo "<input type='hidden' name='is_directory' value='" . true . "' />";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class=''><th colspan='3'>" . __("Liste des comptes par source d'authentification", 'dlteams') . "<br><i style='font-weight: normal'>" . "</i></th>";
        echo "</tr>";
        echo "<tr class='tab_bg_1'><td width='24%' style='display: flex; align-items: center; gap: 5px'>";
        echo "<span style='white-space: nowrap'>" . __("Choisir un compte", 'dlteams') . "</span>";

        $value = "0";
        $datacatalog_item = new PluginDlteamsDataCatalog_Item();
        $datacatalog_item->getFromDB($linkid);

        if ($item::getType() == PluginDlteamsDataCatalog::class && isset($datacatalog_item->fields["items_id"])) {
            $value = $datacatalog_item->fields["items_id"];
        } elseif (isset($row->fields["accountkeys_id"]) && $row->fields["accountkeys_id"])
            $value = $row->fields["accountkeys_id"];
        $used = [];
//
        $iterator = static::showForItemgetRequest($item, $is_directory);
        foreach ($iterator as $ak){
            $used[$ak["id"]] = $ak["id"];
        }

//        on ajoute a $used les comptes clés déjà ajoutés
        $iterator = static::showForItemgetRequest($item, $is_directory);
        foreach ($iterator as $ak) {
            $used[$ak["id"]] = $ak["id"];
        }

        PluginDlteamsAccountKey::dropdown([
            "name" => "accountkeys_id",
            "addicon" => false,
            "width" => "150px",
            "used" => $used,
            "value" => $value,
            "condition" => [
                'plugin_dlteams_datacatalogs_id' => 0
            ]
        ]);

        global $CFG_GLPI;
        $field_id = Html::cleanId("dropdown_" . 'items_' . mt_rand());
        $item_link = getItemForItemtype(PluginDlteamsAccountKey::class);
        $url =  sprintf("%s?datacatalogs_id=%s", $item_link->getFormURL(), $item->fields["id"]);
//        $url = $CFG_GLPI['root_doc'] . "/marketplace/dlteams/ajax/annuaireAccount.php";


        echo '<div style="border-radius:2px;" class="btn btn-outline-secondary"
                           title="' . __s('Add') . '" data-bs-toggle="modal" data-bs-target="#add_' . $field_id . '">'
            . Ajax::createIframeModalWindow('add_' . $field_id, $url, ['display' => false])
            . "<span data-bs-toggle='tooltip'>
              <i class='fa-fw ti ti-plus'></i>
              <span class='sr-only'>" . __s('Add') . "</span>
                </span>"
            . '</div>';
        echo "</td>";

//        $displaycss = " display: none;";
//        if ($linkid)
        $displaycss = " display: block;";

        echo "<td>";
        if (!$is_directory && $item->getType() == PluginDlteamsDataCatalog::class) {
            echo "<span style='float:right;width:100%; $displaycss' id='td1'>";
            echo __("Permission", "dlteams");
            echo "&nbsp";
            PluginDlteamsUserProfile::dropdown([
                "name" => "profiles_idx[]",
                "width" => "150px",
                'value' => $row ? json_decode($row->fields["profiles_json"] ?? "[]") : [],
                'multiple' => true
            ]);
            echo "</span>";
        }
        echo "</td>";

        echo "<td style='$displaycss align-items: center;' id='td3'>";
        echo "<span style='float:right;width:100%;'>";
        echo "<textarea type='text' rows='1' name='comment' placeholder='Commentaire'  style='margin-bottom:-15px;width:90%'>";
        if ($row)
            echo $row->fields["comment"];
        echo "</textarea>";
        echo "</span>";
        echo "</td>";

        echo "<td>";
        echo "<span style='$displaycss float:left;margin-left:10px;' id='td4'>";
        if ($row)
            echo "<input type='submit' name='update' value=\"" . _sx('button', 'Update') . "\" class='submit'>";
        else
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
        echo "</span>";
        echo "</td>";
        echo "</table>";
        Html::closeForm();
        echo "</div>";

        $script = "
            <script>
                $(document).ready(function(){
                $('select[name=accountkeys_id]').on('change',function(){
        
                if($(this).val()!='0'){";
                if ($item::getType() != PluginDlteamsDataCatalog::class)
                    $script .= "document.getElementById('td1').style.display = 'block';";
                $script .= "document.getElementById('td3').style.display = 'table-cell';
                    document.getElementById('td4').style.display = 'block';
                    var content = $(this).val();
                        $.ajax({
                            url : 'getComment.php',
                            type: 'POST',
                            async : false,
                            data : { content : content},
                            success : function(data) {
                                //alert(data);
                                //userData = json.parse(data);
                                //alert(json.parse(data));
                              $('.comment1').val(data);
                            }
                });
                }else{
                    document.getElementById('td1').style.display = 'none';
                    document.getElementById('td2').style.display = 'none';
                    document.getElementById('td3').style.display = 'none';
                    document.getElementById('td4').style.display = 'none';
                }
                });});</script>";

//        echo $script;
    }

    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {


        switch ($ma->getAction()){
            case 'add_as_accesskey':
                echo "<span style='float:right;width:100%; margin-bottom: 1rem'>";

                echo __("Accès en tant que", "dlteams");
                echo "&nbsp";

                PluginDlteamsUserProfile::dropdown([
                    "name" => "profiles_idx[]",
                    "width" => "150px",
                    'value' => 1,
//                    'multiple' => false
                ]);

                echo "</span>";
                break;
        }
        return parent::showMassiveActionsSubForm($ma); // TODO: Change the autogenerated stub
    }

    static function showForItem(CommonDBTM $item, $is_directory = false)
    {
//        var_dump($is_directory);
//        die();
        $id = $item->fields['id'];
        $canedit = $item->can($id, UPDATE); // canedit booleen = true
        $rand = mt_rand(1, mt_getrandmax());
        global $DB;

        $iterator = static::showForItemgetRequest($item, $is_directory);
        $number = count($iterator);
        $items_list = [];
        $used = [];

        /***form new**/
        if ($canedit) {
            static::showForItemForm($item, null, $is_directory);
        }
        //if ($is_directory)
        //  $ma_processor = PluginDlteamsDataCatalog_Item::class;
        //else
        $ma_processor = __CLASS__;


        echo "<div class='spaced'>";
        if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . $ma_processor . $rand);
            $massive_action_params = [
                'container' => 'mass' . $ma_processor . $rand,
                'num_displayed' => min($_SESSION['glpilist_limit'], $number)
            ];
            Html::showMassiveActions($massive_action_params);
        }
        echo "<table class='tab_cadre_fixehov'>";

        $header_begin = "<tr>";
        $header_top = '';
        $header_bottom = '';
        $header_end = '';

        if ($canedit && $number) {
            $header_begin .= "<th width='10'>";
            $header_top .= Html::getCheckAllAsCheckbox('mass' . $ma_processor . $rand);
            $header_end .= "</th>";
        }

        $header_end .= "<th>";
        echo "</th>";
        $header_end .= "<th>" . __("Intitulé", "dlteams") . "</th>";
        $header_end .= "<th>" . __("Annuaire", "dlteams") . "</th>";
        $header_end .= "<th>" . __("Type", "dlteams") . "</th>";
        if (!$is_directory && $item->getType() == PluginDlteamsDataCatalog::class)
            $header_end .= "<th>" . __("Permissions", "dlteams") . "</th>";
        $header_end .= "<th>" . __("Commentaire", "dlteams") . "</th>";
        $header_end .= "</tr>";
        echo $header_begin . $header_top . $header_end;

        if ($item::getType() == PluginDlteamsDataCatalog::class && $item->fields["is_directoryservice"] && $is_directory) {
            foreach ($iterator as $data) {
                echo "<tr class='tab_bg_1'>";
                if ($canedit && $number) {
                    echo "<td width='10'>";
                    $item_str = $item::class . "_Item";
//                        Computer_Item::class;
                    Html::showMassiveActionCheckBox($ma_processor, $data['id']);
                    echo "</td>";
                }

                echo "<td width='10'>";
                $id = $data['id'];
                echo "<span style='border: none; background-color: transparent; cursor: pointer; margin-left: 10px' class='btn-updatepermissions' data-row-id='$id'>";
                echo "<i class='fas fa-edit me-2'></i>";
                echo "</span>";
                echo "</td>";
                $name = "<a target='_blank' href=\"" . PluginDlteamsAccountKey::getFormURLWithID($data['id']) . "\">" . $data["name"] . "</a>";
                echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                echo ">" . $name . "</td>";
                echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                echo ">" . $item->fields["directory_name"]. "</td>";
//            if ($item::getType() != PluginDlteamsDataCatalog::class) {
                echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                echo ">" . $data["keytypename"] ?? "--" . "</td>";
//            }
                echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                echo ">";
                echo $data["comment"];
                echo "</td>";
                echo "</tr>";
            }

//        begin script
            $script = "<script>
		$(document).ready(function () {
        $('.btn-updatepermissions').on('click', function () {
            var link_id = $(this).attr('data-row-id');

            glpi_ajax_dialog({
                dialogclass: 'modal-xl',
                bs_focus: false,
                url: '/marketplace/dlteams/ajax/object_item_single_update_action.php',
                params: {
                    object: '" . PluginDlteamsAccountKey_Item::class . "',";

            $script .= "linkid: link_id,
                    itemtype: '" . $item->getType() . "',
                    items_id: '" . $item->fields["id"] . "'
                },
                title: i18n.textdomain('dlteams').__('Action', 'dlteams'),
                close: function () {

                },
                fail: function () {
                    displayAjaxMessageAfterRedirect();
                }
            });});});</script>";

            echo $script;
        }
        else {
            foreach ($iterator as $data) {
                echo "<tr class='tab_bg_1'>";

                if ($canedit && $number) {
                    echo "<td width='10'>";
                    $item_str = $item::class . "_Item";
//                        Computer_Item::class;
                    if(!$data["directory_name"])
                    Html::showMassiveActionCheckBox($ma_processor, isset($data['linkid'])?$data['linkid']:$data['id']);
                    echo "</td>";
                }

                echo "<td width='10'>";
                $id = isset($data['linkid'])?$data['linkid']:$data['id'];
                echo "<span style='border: none; background-color: transparent; cursor: pointer; margin-left: 10px' class='btn-updatepermissions' data-row-id='$id'>";
                echo "<i class='fas fa-edit me-2'></i>";
                echo "</span>";
                echo "</td>";

                $name = $data["name"];

                if ($is_directory)
                    $accountkeys_id = $data['id'];
                else
                    $accountkeys_id = $data['accountkeys_id'];
                $name = "<a target='_blank' href=\"" . PluginDlteamsAccountKey::getFormURLWithID($accountkeys_id) . "\">" . $name . "</a>";

                echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                echo ">" . $name . "</td>";

                echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                echo ">" . $data["directory_name"]. "</td>";
//            if ($item::getType() != PluginDlteamsDataCatalog::class) {
                echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                echo ">" . $data["keytypename"] ?? "--" . "</td>";
//            }

                if (!$is_directory && $item->getType() == PluginDlteamsDataCatalog::class) {
                    $profiles_json = $data["profiles_json"] ? json_decode($data["profiles_json"]) : [];
                    $profile_names = "";
                    foreach ($profiles_json as $key => $profile_id) {
                        $profile = new PluginDlteamsUserProfile();
                        $profile->getFromDB($profile_id);
                        $profile_names .= "<a target='_blank' href=\"" . PluginDlteamsUserProfile::getFormURLWithID($profile_id) . "\">" . $profile->fields["name"] . "</a>";
                        if (count($profiles_json) != $key + 1)
                            $profile_names .= ",&nbsp;";
                    }

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo ">";
                    echo $profile_names;
                    echo "</td>";
                }

                echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                echo ">";
                echo $data["comment"];
                echo "</td>";

                echo "</tr>";
            }

//        begin script
            $script = "<script>
    $(document).ready(function () {
        $('.btn-updatepermissions').on('click', function () {
            var link_id = $(this).attr('data-row-id');


            glpi_ajax_dialog({
                dialogclass: 'modal-xl',
                bs_focus: false,
                url: '/marketplace/dlteams/ajax/object_item_single_update_action.php',
                params: {
                    object: '" . PluginDlteamsAccountKey_Item::class . "',";


            $script .= "linkid: link_id,
                    itemtype: '" . $item->getType() . "',
                    items_id: '" . $item->fields["id"] . "'
                },
                title: i18n.textdomain('dlteams').__('Action', 'dlteams'),
                close: function () {

                },
                fail: function () {
                    displayAjaxMessageAfterRedirect();
                }
            });});});</script>";

            echo $script;
        }
//        end script

    }

    function getSpecificMassiveActions($checkitem = NULL)
    {
        $actions = parent::getSpecificMassiveActions($checkitem);

        // add a single massive action
        $class = __CLASS__;

        $action_key = "delete_accountkey_catalog_relation_action";
        $action_label = _n("Supprimer ce compte de l'annuaire", "Delete this account from the directory", 0, "dlteams");
        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;


        $action_key = "add_as_accesskey";
        $action_label = _n("Ajouter comme clé d'accès", "Ajouter comme clé d'accès", 0, "dlteams");
        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;

        return $actions;
    }

    public static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        switch ($ma->getAction()) {
            case 'delete_accountkey_catalog_relation_action':
                foreach ($ids as $id) {
                    $accountkey = new PluginDlteamsAccountKey();
                    if($accountkey->update([
                        "id" => $id,
                        "plugin_dlteams_datacatalogs_id" => 0
                    ])){
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                        Session::addMessageAfterRedirect("La clé a bien été retiré de l'annuaire");
                    }
                    else{
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                    }
                }
                break;
            case 'add_as_accesskey':

                if(!$_POST["profiles_idx"]){
                    Session::addMessageAfterRedirect("Veuillez choisir une ou des attributions", 0, ERROR);
                }
                else{
                    foreach ($ids as $id) {
                        try {
                            $accountkey = new PluginDlteamsAccountKey();
                            $accountkey->getFromDB($id);
                            $datacatalog_id = $accountkey->fields["plugin_dlteams_datacatalogs_id"];

                            $accountkey_item = new PluginDlteamsAccountKey_Item();
                            $datacatalog_item = new PluginDlteamsDataCatalog_Item();
                            $ai_criteria = [
                                "accountkeys_id" => $id,
                                "itemtype" => PluginDlteamsDataCatalog::class,
                                "profiles_json" => json_encode($_POST["profiles_idx"]),
                                "items_id" => $datacatalog_id
                            ];
                            $di_criteria = [
                                "datacatalogs_id" => $datacatalog_id,
                                "itemtype" => PluginDlteamsAccountKey::class,
                                "profiles_json" => json_encode($_POST["profiles_idx"]),
                                "items_id" => $id
                            ];

                            if(!$datacatalog_item->getFromDBByCrit($di_criteria) && !$accountkey_item->getFromDBByCrit($ai_criteria)){
                                $accountkey_item->add($ai_criteria);
                                $datacatalog_item->add($di_criteria);
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                                Session::addMessageAfterRedirect("Clé d'accès ajouté avec succès");
                            }
                            else{
                                Session::addMessageAfterRedirect(sprintf("La clé <a href='%s'>%s</a> existe déjà dans cet annuaire",PluginDlteamsAccountKey::getFormURLWithID($accountkey->fields["id"]) , $accountkey->fields["name"]));
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                            }

                        }
                        catch (Exception $exception){
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        }
                    }
                }


                break;
        }


    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        $forbidden[] = 'MassiveAction:purge';
        $forbidden[] = 'MassiveAction:update';
        $forbidden[] = 'MassiveAction:delete';
        return $forbidden;
    }


}
