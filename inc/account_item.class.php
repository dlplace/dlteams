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

class PluginDlteamsAccount_Item extends CommonDBTM
{
    static public $itemtype_2 = 'PluginDlteamsAccount';
    static public $itemtype_1;
    public static $items_id_1 = 'plugin_accounts_accounts_id';
    public static $title;
    public static $sub_title;
    public static $table_match_str = [];

    public function __construct()
    {

        static::$itemtype_1 = str_replace("_Item", "", __CLASS__); // $itemtype_1 ---> PluginDlteamsDataCatalog
        static::$title = __("Indiquez les comptes pouvant accéder à ce catalogue et leur rôle", 'dlteams');
        static::$sub_title = __("Choisir un catalogue à lier à cet élément", 'dlteams');
        static::$table_match_str = [
            [
                'head_text' => PluginAccountsAccount::getTypeName(),
                'column_name' => 'name',
                'show_as_link' => true
            ],
            [
                'head_text' => __("Rôle"),
                'column_name' => 'content',
            ],
            [
                'head_text' => __("Comment"),
                'column_name' => 'comment',
            ]
        ];
        parent::__construct();
        self::forceTable(PluginAccountsAccount_Item::getTable());
    }

    static function canCreate()
    {
        return true;
    }

    static function canView()
    {
        return true;
    }

    static function canUpdate()
    {
        return true;
    }

    static function canDelete()
    {
        return true;
    }

    static function canPurge()
    {
        return true;
    }

    function canCreateItem()
    {
        return true;
    }

    function canViewItem()
    {
        return true;
    }

    function canUpdateItem()
    {
        return true;
    }

    function canDeleteItem()
    {
        return true;
    }

    function canPurgeItem()
    {
        return true;
    }

    static function getTypeName($nb = 0)
    {
        return __("Data catalog", 'dlteams');
    }

    static function getTypeNameForClass($nb = 0)
    {
        return __("Eléments rattachés", 'dlteams');
    }


    // affichage de l'onglet et de son nom
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case static::$itemtype_2:
                if (!$withtemplate) {
                    if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            return static::createTabEntry(static::getTypeNameForClass(), static::countForItem($item));
                        }
                        return static::getTypeNameForClass();
                    }
                }
                break;
            default:
                if (!$withtemplate) {
                    if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            return static::createTabEntry("Gestion des accès", static::countForItem($item));
                        }
                        return static::getTypeName(2);
                    }
                }
                break;
        }

        return '';
    }

    // comptage du nombre de liaison entre les 2 objets dans la table de l'objet courant
    static function countForItem(CommonDBTM $item)
    {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable(static::getTable(), ['items_id' => $item->getID(), 'itemtype' => $item->getType()]);
    }


    public static function getItemsRequest(CommonDBTM $object_item)
    {
        global $DB;
        $link_table = str_replace("_Item", "", __CLASS__);
        $temp = new $link_table();

        $items = $DB->request([
            'FROM' => self::getTable(),
            'SELECT' => [
                self::getTable() . '.id',
                self::getTable() . '.id as linkid',
                self::getTable() . '.comment',
                self::getTable() . '.itemtype as itemtype',
                self::getTable() . '.items_id as items_id',
            ],
            'WHERE' => [
                static::getTable() . '.' . static::$items_id_1 => $object_item->fields['id']
            ],
            'LEFT JOIN' => [
                $temp->getTable() => [
                    'FKEY' => [
                        static::getTable() => static::$items_id_1,
                        $temp->getTable() => 'id'
                    ]
                ]
            ],
//            'ORDER' => self::getTable() . '.id DESC',
            'ORDER' => [$temp->getTable() . '.name ASC', self::getTable() . '.itemtype ASC'],
        ]);

        return iterator_to_array($items);
    }


    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addImpactTab($ong, $options);
        return $ong;
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case static::$itemtype_2:
                self::showItems($item);
                break;
            default:
                self::showForItem($item);
                break;
        }
    }

    /**
     * Show items links to a document
     *
     * @param $doc Document object
     *
     * @return void
     **@since 0.84
     *
     */
    public static function showItems(PluginDlteamsDataCatalog $object_item)
    {
        global $DB;
        $instID = $object_item->fields['id'];
        if (!$object_item->can($instID, READ)) {
            return false;
        }
        $canedit = $object_item->can($instID, UPDATE);
        // for a measure,
        // don't show here others protective measures associated to this one,
        // it's done for both directions in self::showAssociated
        $types_iterator = [];
        $number = count($types_iterator);

        $used = [];
        $types = PluginDlteamsItemType::getTypes();
//        Enlève le choix de L'objet LegalBasi dans la dropdown qui affiche la liste des objets
        $key = array_search("PluginDlteamsDataCatalog", $types);
        unset($types[$key]);
        $rand = mt_rand();

        if ($canedit) {
            echo "<form name='datacatalogitem_form$rand' id='datacatalogitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
            echo "<input type='hidden' name='" . static::$items_id_1 . "' value='$instID'>";
            echo "<input type='hidden' name='itemtype1' value='" . str_replace("_Item", "", __CLASS__) . "'>";
            echo "<input type='hidden' name='items_id1' value='" . $instID . "'>";

            echo "<table class='tab_cadre_fixe'>";
            $title = "Related objects";
            $entitled = "Indicate the objects related to this element";
            echo "<tr class='tab_bg_2'><th colspan='3'>" . __($title, 'dlteams') .
                "</th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'><td class='right' style='text-wrap: nowrap;' width='40%'>" . __($entitled, 'dlteams');
            echo "</td><td width='40%' class='left'>";
            $types = PluginDlteamsItemType::getTypes();
            $key = array_search("PluginDlteamsLegalBasi", $types);
            unset($types[$key]);
            echo "<div style='display: flex; gap: 4px;'>";
            Dropdown::showSelectItemFromItemtypes(['itemtypes' => $types,
                'entity_restrict' => ($object_item->fields['is_recursive'] ? getSonsOf('glpi_entities', $object_item->fields['entities_id'])
                    : $object_item->fields['entities_id']),
                'checkright' => true,
                'used' => $used,
                'ajax_page' => "/marketplace/dlteams/ajax/dlteamsDropdownAllItem.php"
            ]);
            echo "</div>";
            unset($types);
            echo "</td><td width='20%' class='left'>";
            echo "</td></tr>";

            echo "<tr class='tab_bg_1' style='display: none' id='field_comment'><td class='right' width='40%'>" . __("Comment");
            echo "</td><td width='40%' class='left comment-td'>";
            echo "<div style='display: flex; gap: 4px;'>";
            echo "<textarea type='text' style='width:100%' maxlength=1000 rows='3' name='comment' class='comment'></textarea>";
            echo "</div>";
            echo "</td><td width='20%' class='left'>";
            echo "</td></tr>";
            echo "<style>
                .comment-td {width: 40%;}
                @media (max-width: 767px) {.comment-td {width: 100%;}}
              </style>";

            echo "<tr class='tab_bg_1' style='display: none' id='field_submit'><td class='right' width='40%'>";
            echo "</td><td width='40%' class='left'>";

            echo "<div style='display: flex; gap: 4px;'>";
            echo "<input for='datacatalogitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</div>";
            echo "</td><td width='20%' class='left'>";
            echo "</td></tr>";

            echo "</table>";
            Html::closeForm();
        }

        echo "<script>
                $(document).ready(function(e){
                    $(document).on('change', 'select[name=items_id]', function () {
                        if($(this).val() != '0'){
                            $('#field_submit').css('display', 'revert');
                            $('#field_comment').css('display', 'revert');
                            
                            $.ajax({
                                url: '/marketplace/dlteams/ajax/get_object_specific_field.php',
                                type: 'POST',
                                data: {
                                    id: $(this).val(),
                                    object: $('select[name=itemtype]').val(),
                                    field: 'content'
                                },
                                success: function (data) {
                                    // Handle the returned data here
                                    let comm_field = $('textarea[name=comment]');
                                    comm_field.val(data);
                                    comm_field.val(comm_field.val().replace(/^\s+/, ''));
                                }
                            });  
                        } else {
                            $('textarea[name=comment]').val('');
                            $('#field_submit').css('display', 'none');
                            $('#field_comment').css('display', 'none');   
                        }
                    });
                });
        </script>";

//        var_dump(self::getTable());
        $items = self::getItemsRequest($object_item);

        if (!count($items)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
            if ($canedit) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = [
                    'num_displayed' => min($_SESSION['glpilist_limit'], count($items)),
                    'container' => 'mass' . __CLASS__ . $rand
                ];
                Html::showMassiveActions($massiveactionparams);
            }

            echo "<table class='tab_cadre_fixehov'>";
            $header = "<tr>";
            if ($canedit) {
                $header .= "<th width='10'>";
                $header .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                $header .= "</th>";
            }
            $header .= "<th>" . __("Element") . "</th>";
            $header .= "<th>" . __("Objet") . "</th>";
            $header .= "<th>" . __("Comment") . "</th>";
            $header .= "</tr>";
            echo $header;

            foreach ($items as $row) {
                $item = new $row['itemtype'](); //plante si itemtype is null
                $item->getFromDB($row['items_id']);
                $name = "<a target='_blank' href=\"" . $item::getFormURLWithID($item->getField('id')) . "\">" . $item->getField('name') . "</a>";
                echo "<tr lass='tab_bg_1'>";
                if ($canedit) {
                    echo "<td>";
                    Html::showMassiveActionCheckBox(__CLASS__, $row["id"]);
                    echo "</td>";
                }
                echo "<td>" . $name . "</td>";
                echo "<td>" . $row["itemtype"]::getTypeName() . "</td>";
                echo "<td>" . $row['comment'] . "</td>";
                echo "</tr>";
            }
            echo $header;
            echo "</table>";

            if ($canedit && count($items)) {
                $massiveactionparams['ontop'] = false;
                Html::showMassiveActions($massiveactionparams);
            }
            if ($canedit) {
                Html::closeForm();
            }
        }
    }

    static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {
        $id = $item->fields['id'];
        $canedit = $item->can($id, UPDATE); // canedit booleen = true
        $rand = mt_rand(1, mt_getrandmax());
        global $DB;

        $iterator = static::getRequest($item);
        $number = count($iterator);
        $items_list = [];
        $used = [];

        //while ($data = $iterator->next()) {
        foreach ($iterator as $id => $data) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        /***form new**/
        if ($canedit) {
            echo "<div class='firstbloc'>";
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsAccount_Item::class) . "'>";
            $iden = $item->fields['id'];
            echo "<input type='hidden' name='datacatalogs_id' value='$id' />";
            echo "<input type='hidden' name='itemtype1' value='" . $item->getType() . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $item->getID() . "' />";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class=''><th colspan='3'>" . static::$title .
                "<br><i style='font-weight: normal'>" .
//                __("In application of legal basis, what are conservation times of the data retained", 'dlteams') .
                "</i></th>";
            echo "</tr>";


            echo "<tr class='tab_bg_1'><td class='' colspan='1'>";

            echo __("Compte ou clé", "dlteams");
            echo "&nbsp";
//
            PluginDlteamsAccountKey::dropdown([
                "name" => "accountkeys_id",
                "width" => "150px"
            ]);

            echo "</td>";

            echo "<td>";
            echo "<span style='float:right;width:100%;display: none' id='td1'>";

            echo __("Rôle", "dlteams");
            echo "&nbsp";
            PluginDlteamsUserProfile::dropdown([
                "name" => "profiles_id",
                "width" => "150px"
            ]);


            echo "</span>";
            echo "</td>";

            echo "<td style='display: flex'>";
            echo "<span style='float:right;width:100%;display: none' id='td2'>";
            global $CFG_GLPI;

            $id = $item->fields['id'];
            if (!$item->can($id, READ)) {
                return false;
            }

            $canedit = PluginDlteamsDataCatalog::canUpdate();
            $rand = mt_rand(1, mt_getrandmax());


            $rand = Dropdown::showFromArray("itemtype_ref", [
                __("------", 'dlteams'),
                __("Groupe", 'dlteams'),
                __("Utilisateur", 'dlteams'),
                __(Contact::getTypeName(), 'dlteams'),
                __(Supplier::getTypeName(), 'dlteams'),
            ], [
                'value' => 0,
                'width' => '100%'
            ]);
            $params = [
                'itemtype_ref' => '__VALUE__',
                'datacatalogs_id' => $id,
            ];
            Ajax::updateItemOnSelectEvent(
                "dropdown_itemtype_ref$rand",
                'itemtype_row',
                $CFG_GLPI['root_doc'] . '/marketplace/dlteams/ajax/account_item_dropdown.php',
                $params
            );
            echo "</span>";
            echo "<span id='itemtype_row' style='margin-left:5px!important'>";
            echo "</span>";

            echo "</td>";

            echo "<td style='display: flex;align-items: center;'>";
            echo "<span style='float:right;width:100%;display: none' id='td3'>";

//            echo "<br/><br/>";
            echo "<textarea type='text' rows='1' name='comment' placeholder='Commentaire'  style='margin-bottom:-15px;width:90%'></textarea>";

            echo "</span>";
            echo "</td>";

            echo "<td>";
            echo "<span style='display:none;float:left;margin-left:10px;' id='td4'>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</span>";
            echo "</td>";

            echo "</table>";
            Html::closeForm();
            echo "</div>";
        }

        echo "
            <script>
$(document).ready(function(){
    $('select[name=accountkeys_id]').on('change',function(){
		//alert($(this).val());
		//
		if($(this).val()!='0'){
			document.getElementById('td1').style.display = 'block';
			document.getElementById('td2').style.display = 'block';
			document.getElementById('td3').style.display = 'block';
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
//
});
});
</script>
        ";


//        if ($iterator) {

        echo "<div class='spaced'>";
        if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massive_action_params = [
                'container' => 'mass' . __CLASS__ . $rand,
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
            $header_top .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
//                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            $header_end .= "</th>";
        }


        foreach (static::$table_match_str as $column) {
            $header_end .= "<th>" . $column["head_text"] . "</th>";
        }
        $header_end .= "</tr>";

        echo $header_begin . $header_top . $header_end;
        //var_dump($items_list);
        foreach ($items_list as $data) {
            echo "<tr class='tab_bg_1'>";

            if ($canedit && $number) {
                echo "<td width='10'>";

                $item_str = $item::class . "_Item";
//                        Computer_Item::class;
                Html::showMassiveActionCheckBox(__CLASS__, $data['linkid']);
                echo "</td>";

                $id = $data['linkid'];
            }


            $name = $data["name"];
            $name = "<a target='_blank' href=\"" . static::$itemtype_2::getFormURLWithID($data['plugin_accounts_accounts_id']) . "\">" . $name . "</a>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $name . "</td>";


            $userprofile = new PluginDlteamsUserProfile();
            $userprofile->getFromDB($data["plugin_dlteams_userprofiles_id"]);
            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $userprofile->fields["name"] . "</td>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $data["comment"] . "</td>";

            echo "</tr>";
        }

        echo "</table>";

        Html::closeForm();

        echo "
                <script>
                    $(document).ready(function(e) {
                        var window.eventBinded = false;
                
                        $(document).on('change', 'select[name=id_field]', function () {
                            console.log('shhhh');
                            
                            if (!alertShown) {
                                $.ajax({
                                        url: '/marketplace/dlteams/ajax/get_object_specific_field.php',
                                        type: 'POST',
                                        data: {
                                            id: " . $id . ",
                                            object: '" . static::$itemtype_2 . "_Item" . "',
                                            field: 'comment'
                                        },
                                        success: function (data) {
                                            // Handle the returned data here
                                            console.log(data);
                                            $('textarea[name=comment]').val(data);
                                        }
                            });
                                eventBinded = true;
                            }
                        });
                    });
                </script>
                ";

        echo "</div>";
//        }

    }


    public function post_purgeItem()
    {
//        purge relations
        $relation_item_str = $this->fields["itemtype"] . "_Item";
        if (!class_exists($relation_item_str))
            $relation_item_str = "PluginDlteams" . $relation_item_str;
        $relation_item = new $relation_item_str();

        $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

        $criteria = [
            "itemtype" => static::$itemtype_2,
            "items_id" => $this->fields[static::$items_id_1],
            $relation_column_id => $this->fields["items_id"],
            "comment" => $this->fields["comment"]
        ];

        $relation_item->deleteByCriteria($criteria);
    }

    public function post_updateItem($history = 1)
    {
        $relation_item_str = $this->fields["itemtype"] . "_Item";
        if (!class_exists($relation_item_str))
            $relation_item_str = "PluginDlteams" . $relation_item_str;
        $relation_item = new $relation_item_str();
        $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

        $criteria = [
            "itemtype" => static::$itemtype_2,
            "items_id" => $this->fields[static::$items_id_1],
            $relation_column_id => $this->fields["items_id"],
        ];
        if (isset($this->oldvalues["comment"]))
            $criteria["comment"] = $this->oldvalues["comment"];


        global $DB;
        if ($DB->fieldExists($relation_item->getTable(), 'plugin_accounts_accounts_id')) {
            $criteria["plugin_accounts_accounts_id"] = $this->fields["plugin_dlteams_userprofiles_id"];
        }

        if ($relation_item->deleteByCriteria($criteria)) {
            if (isset($_POST["plugin_dlteams_userprofiles_id"]))
                $criteria["plugin_dlteams_userprofiles_id"] = $_POST["plugin_dlteams_userprofiles_id"];

            $relation_item->add([
                ...$criteria,
                "comment" => $this->fields["comment"]
            ]);
        }
    }

    function rawSearchOptions()
    {
        $tab[] = [
            'id' => '44',
            'table' => PluginDlteamsUserProfile::getTable(),
            'field' => 'items_id',
            'datatype' => 'dropdown',
            'name' => PluginDlteamsUserProfile::getTypeName(),
            'forcegroupby' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '45',
            'table' => static::getTable(),
            'field' => 'comment',
            'datatype' => 'text',
            'name' => __("Commentaire"),
            'forcegroupby' => true,
            'massiveaction' => true,
        ];

        return $tab;
    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }

    public static function getRequest(CommonDBTM $item)
    {

        $table_name = PluginAccountsAccount::getTable();

        $columnid_name = "plugin_accounts_accounts_id";
        global $DB;        //var_dump ($table_name, $columnid_name);
        $table_item_name = getTableForItemType(PluginAccountsAccount_Item::class);


        $query = [
            'SELECT' => [
                $table_item_name . '.id AS linkid',
                $table_item_name . '.itemtype AS itemtype',
                $table_item_name . '.items_id AS items_id',
                $table_item_name . '.*',
                $table_name . '.id AS id',
                $table_name . '.name AS name',
//                $table_name . '.content AS content',
            ],
            'FROM' => $table_item_name,
            'LEFT JOIN' => [
                $table_name => [
                    'ON' => [
                        $table_item_name => $columnid_name,
                        $table_name => 'id'
                    ]
                ]
            ],
            'WHERE' => [
                $table_item_name . '.itemtype' => ['LIKE', $item::getType()],
                $table_item_name . '.' . 'items_id' => $item->fields['id'],
            ],
            'ORDERBY' => ['name ASC']
        ];


        /*        highlight_string("<?php\n\$data =\n" . var_export($query, true) . ";\n?>");*/
//        die();
        if ($DB->fieldExists($table_item_name, 'comment')) {
            $query['SELECT'][] = $table_item_name . '.comment AS comment';
        }

        if ($DB->fieldExists($table_name, 'content')) {
            $query['SELECT'][] = $table_name . '.content AS content';
        }


        $iterator = $DB->request($query);
        $temp = [];

        foreach ($iterator as $id => $data) {

            if ($data["itemtype"]) {
                $item_object = null;
                $item_str = $data["itemtype"];
                $item_object = new $item_str();
                $item_object->getFromDB($data["items_id"]);


                if (isset($item_object->fields["entities_id"])) {
                    array_push($temp, $data);
                }

            }

        }
        return $temp;
    }
}
