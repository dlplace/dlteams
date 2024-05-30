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

class PluginDlteamsDataCatalog extends CommonTreeDropdown implements
    PluginDlteamsExportableInterface
{
    use PluginDlteamsExportable;

    public $dohistory = true;
    protected $usenotepad = true;

    // From CommonDBTM
    public $auto_message_on_action = true;
    public static $rightname = 'plugin_dlteams_datacatalog';
    public static $mustBeAttached = false;

    // From CommonDBChild
    public static $itemtype = 'PluginDlteamsDataCatalog';
    public static $items_id = 'plugin_dlteams_datacatalogs_id';

    public static function getTypeName($nb = 0)
    {
        return _n('Data catalog', 'Data catalogs', $nb, 'dlteams');
    }

    public static function getAnnuairescatalogues()
    {
        global $DB;

        $query = [
            "FROM" => PluginDlteamsDataCatalog::getTable(),
            "WHERE" => [
                "is_directoryservice" => true,
                "entities_id" => $_SESSION['glpiactive_entity']
            ]
        ];
        $iterator = $DB->request($query);
        $result = [];
        foreach ($iterator as $row) {
            $result[] = $row;
        }

        return $result;
    }


    private static function getMeansOfAccesRequest(PluginDlteamsDataCatalog $item)
    {
        global $DB;

        $request = [
            'SELECT' => [
                PluginDlteamsDataCatalog_Item::getTable() . '.id AS linkid',
                PluginDlteamsDataCatalog_Item::getTable() . '.itemtype AS itemtype',
                PluginDlteamsDataCatalog_Item::getTable() . '.items_id AS items_id',
                PluginDlteamsDataCatalog_Item::getTable() . '.meansofacces_mandatory AS meansofacces_mandatory',
                PluginDlteamsDataCatalog_Item::getTable() . '.comment AS comment',
            ],
            'FROM' => PluginDlteamsDataCatalog_Item::getTable(),
            'WHERE' => [
                'itemtype' => PluginDlteamsMeansOfAcce::class,
                PluginDlteamsDataCatalog_Item::getTable() . '.datacatalogs_id' => $item->fields["id"]
            ]
        ];
        return $DB->request($request);
    }

    public static function additionalShowRelatedParentFields(PluginDlteamsDataCatalog $currentcatalog, PluginDlteamsDataCatalog $parentcatalog = null, $options = [])
    {
        $rand = mt_rand();
        echo "<tr> <td class='form-table-text'>" . __("Type de catalogue", 'dlteams') . "</td>";
        echo "<td>";
        $datacarriercategories_id = 0;
        $groups_id = 0;
        $users_id = 0;
        $suppliers_id = 0;
        $contacts_id = 0;
        if (!$parentcatalog && !isset($options["parentcatalog"])) {
            $datacarriercategories_id = $currentcatalog->fields['plugin_dlteams_datacarriercategories_id'];
            $groups_id = $currentcatalog->fields['groups_id_contact'];
            $users_id = $currentcatalog->fields['users_id_contact'];
            $suppliers_id = $currentcatalog->fields['suppliers_id_contact'];
            $contacts_id = $currentcatalog->fields['contacts_id_contact'];
        }
        if ($parentcatalog && isset($options["parentcatalog"]) && $options["parentcatalog"] > 0) {
            $datacarriercategories_id = $parentcatalog->fields['plugin_dlteams_datacarriercategories_id'];
            $groups_id = $parentcatalog->fields['groups_id_contact'];
            $users_id = $parentcatalog->fields['users_id_contact'];
            $suppliers_id = $parentcatalog->fields['suppliers_id_contact'];
            $contacts_id = $parentcatalog->fields['contacts_id_contact'];
        }

        // echo "<div style='display: flex; gap: 5px; align-items: center'>";
        PluginDlteamsDataCarrierCategory::dropdown([
            'addicon' => PluginDlteamsDataCarrierCategory::canCreate(),
            'name' => 'plugin_dlteams_datacarriercategories_id',
            'width' => '300px',
            'value' => $datacarriercategories_id,
        ]);
        echo "</div>";
        echo "</td></tr>";

        echo "</div>";
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


        echo "<tr> <td class='form-table-text'>" . __("Information classification", 'dlteams') . "</td>";
        echo "<td>";
        PluginDlteamsCatalogClassification::dropdown([
            'addicon' => PluginDlteamsCatalogClassification::canCreate(),
            'name' => 'plugin_dlteams_catalogclassifications_id',
            'width' => '300px',
            'value' => $currentcatalog->fields['plugin_dlteams_catalogclassifications_id'],
        ]);
        echo "</td></tr>";

        if (!$currentcatalog->fields['plugin_dlteams_datacatalogs_id']) {
            echo "<tr>";
            echo "<td class='form-table-text'></td>";
            echo "<td>";
            echo "<div style='display: flex; align-items: center; gap: 3px;'>";

            if(!$currentcatalog->fields["is_directoryservice"] || !$currentcatalog->fields["use_other_directory"]){
                Html::showCheckbox([
                    'name' => 'is_helpdesk_visible',
                    'checked' => $currentcatalog->fields["is_helpdesk_visible"],
                ]);
                echo "<label> Aucune clé ou compte nécéssaire</label>";
            }
            echo "</div>";
            echo "</td>";
            echo "</tr>";

                echo "<tr><td class='form-table-text'>" . "Protection de l'accès à ce catalogue" . "</td>";

                echo "<td>";

            Html::showCheckbox([
                'name' => 'is_directoryservice',
                'checked' => $currentcatalog->fields["is_directoryservice"],
            ]);

            echo " clés ou comptes spécifique à ce catalogue </label>";
//            echo "</div>";
            echo "</td>";
            echo "<script>
				$(document).ready(function(e){
				    
				let is_directory = true;
					if(is_directory){
							$('#directory_name_field').css('display', 'contents');
							$('#directory_name_field2').css('display', 'table-row');
						}
						else{
							$('#directory_name_field').css('display', 'none');
							$('#directory_name_field2').css('display', 'none');
						}
                        
					$('input[name=is_directoryservice]').change(function() {
							if(this.checked){
							$('#directory_name_field').css('display', 'contents');
							$('#directory_name_field2').css('display', 'table-row');
							}
						else{
                          $('#directory_name_field').css('display', 'none');
                          $('#directory_name_field2').css('display', 'none');
						}
                    });
				});
				</script>";
            echo "</tr><tr><td></td><td>";
            echo "<div style='display: flex; flex-direction: row; gap: 0.1rem'>";
            Html::showCheckbox([
                'name' => 'use_other_directory',
                'checked' => $currentcatalog->fields["use_other_directory"] == 1 ? true : false,
            ]);
            echo "<label for='use_other_directory'>&nbsp; utilise les clés ou comptes d'autres catalogues (services d'annuaire tiers)</label></td></tr>";
            echo "</div>";

            echo "<tr id='directory_name_field'>";
            echo "<td class='form-table-text'>";
            echo "<label style='white-space: nowrap'>Nom du service d'annuaire </label>";
            echo "</td>";

            echo "<td>" . "<input type='text' style='width:90%' style='text-align:left; display:none;' name='directory_name' value='" . Html::cleanInputText($currentcatalog->fields['directory_name']) . "'>" . "</td>";
            echo "</tr>";

            echo "<tr id='directory_name_field2'> <td class='form-table-text'>" . __("Type de clé par défaut", 'dlteams') . "</td>";
            echo "<td>";
            PluginDlteamsKeyType::dropdown([
                'addicon' => PluginDlteamsKeyType::canCreate(),
                'name' => 'default_keytype',
                'width' => '300px',
                'value' => $currentcatalog->fields['default_keytype'],
            ]);
            echo "</td></tr>";
        }
        echo "</tr>";

        echo "<tr class='tab_bg_2'><th colspan='4'> <i class='fa fa-information' style='font-weight: normal'></i>" . __("Informations de contact / référent de la ressource", 'dlteams')  . "</th>" . "</tr>";

        echo "<tr class='tab_bg_4'>";
        echo "<td class='form-table-text'>" . __("Groupe", 'dlteams') . "</td>";
        echo "<td>";
        Group::dropdown([
            'addicon' => Group::canCreate(),
            'name' => 'groups_id_contact',
            'width' => '300px',
            'value' => $groups_id,
        ]);
        echo "</td>";

        echo "<td class='form-table-text'>" . __("Utilisateur ", 'dlteams') . "</td>";
        echo "<td>";
        User::dropdown([
            'addicon' => User::canCreate(),
            'name' => 'users_id_contact',
            'width' => '250px',
            'value' => $users_id,
            'entity' => Session::getActiveEntity(),
            'right' => 'all',
        ]);
        echo "</td></tr>";

        echo "<tr>";
        echo "<td class='form-table-text'>" . __("Fournisseur ", 'dlteams') . "</td>";
        echo "<td>";
        Supplier::dropdown([
            'addicon' => Supplier::canCreate(),
            'name' => 'suppliers_id_contact',
            'width' => '300px',
            'value' => $suppliers_id,
        ]);
        echo "</td>";
        echo "<td class='form-table-text'>" . __("Contact ", 'dlteams') . "</td>";
        echo "<td>";
        Contact::dropdown([
            'addicon' => Contact::canCreate(),
            'name' => 'contact_id_contact',
            'width' => '300px',
            'value' => $contacts_id,
        ]);
        echo "</td></tr>";
        // echo "</table>";
    }

    function showForm($id, $options = [])
    {
        echo "<div id='content_form'>";
        $this->initForm($id, $options);
        $this->showFormHeader($options);
        echo "<tr>";
        echo "<td class='form-table-text'>" . __("Name", 'dlteams') . "</td>";

        echo "<td colspan='2'>";
        $input = Html::cleanInputText($this->fields['name']);
        echo "<input type='text' style='width:75%' maxlength=250 name='name' required value='" . $input . "'>";
        echo "</td></tr>";

        if ($options && isset($options["parentcatalog"])) {
            $parentcatalog = new PluginDlteamsDataCatalog();
            $parentcatalog->getFromDB($options["parentcatalog"]);
        }

        echo "<tr> <td class='form-table-text'>" . __("Comme sous-catalogue de ", 'dlteams') . "</td>";
        echo "<td>";
        $parentcatalog_value = 0;
        if (isset($parentcatalog) && $options["parentcatalog"] != 0)
            $parentcatalog_value = $parentcatalog->fields["id"];
        else if (isset($options["parentcatalog"]) && $options["parentcatalog"] == 0)
            $parentcatalog_value = 0;
        else if ($this->fields['plugin_dlteams_datacatalogs_id'] && !isset($options["parentcatalog"]) != 0)
            $parentcatalog_value = $this->fields['plugin_dlteams_datacatalogs_id'];

        self::dropdown([
            'addicon' => self::canCreate(),
            'name' => 'plugin_dlteams_datacatalogs_id',
            'width' => '300px',
            'entity_sons' => true,
            'value' => $parentcatalog_value,
        ]);
        echo "</td></tr>";

        echo "<tr> <td class='form-table-text'>" . __("Description des données visibles, url, chemin d'accès, ...", 'dlteams') . "</td>";
		echo "<td colspan='2'>" . "<textarea class='form-control' style='width:75%' rows='4' name='content' >" . Html::cleanInputText($this->fields['content']) . "</textarea>" . "</td>";
        echo "</td></tr>";


        static::additionalShowRelatedParentFields($this, isset($parentcatalog) ? $parentcatalog : null, $options);

        echo "<script>
            $('select[name=plugin_dlteams_datacatalogs_id]').change(function(e){
                $.ajax({
                    url: '/marketplace/dlteams/ajax/parent_catalog_update_fields.php',
                    type: 'POST',
                    data: {
                        id: $(this).val(),
                        currentcatalog: " . $this->fields["id"] . "
                    },
                    success: function (data) {
                        $('#content_form').html(data);
                    }
                }); 
            });
        </script>";

        $this->showFormButtons($options);
        echo "</div>";

        $this->additionalShowOtherCatalogs($id);


        echo "<style>";
        echo "
            .form-table-text {
                text-align: right;
            }
            
            
            @media (max-width: 600px) {
                .form-table-text {
                    text-align: left;
                }
            }
        ";

        echo "</style>";
        return true;
    }

    public function additionalShow($id)
    {
        if ($id) {
            $id = $this->fields['id'];
            if (!$this->can($id, READ)) {
                return false;
            }
            $canedit = $this->can($id, UPDATE);
            $rand = mt_rand(1, mt_getrandmax());
            global $CFG_GLPI;
            global $DB;


            // Section Data type - BEGIN
            $id = $this->fields['id'];
            if (!$this->can($id, READ)) {
                return false;
            }
            $canedit = $this->can($id, UPDATE);
            $rand = mt_rand(1, mt_getrandmax());

            global $DB;

            $iterator = $DB->request([
                'SELECT' => [
                    'glpi_plugin_dlteams_datacatalogs_items.id AS linkid',
                    'glpi_plugin_dlteams_datacatalogs_items.comment as comment',
                    'glpi_plugin_dlteams_datacarriertypes.id as id',
                    'glpi_plugin_dlteams_datacarriertypes.name as name',
                ],
                'FROM' => 'glpi_plugin_dlteams_datacatalogs_items',
                'JOIN' => [
                    'glpi_plugin_dlteams_datacarriertypes' => [
                        'FKEY' => [
                            'glpi_plugin_dlteams_datacatalogs_items' => 'items_id',
                            'glpi_plugin_dlteams_datacarriertypes' => 'id'
                        ]
                    ]
                ],
                'WHERE' => [
                    'glpi_plugin_dlteams_datacatalogs_items.datacatalogs_id' => $this->fields['id'],
                    'glpi_plugin_dlteams_datacatalogs_items.itemtype' => "PluginDlteamsDataCarrierType"
                ],
                'ORDER' => ['name ASC'],
            ], "", true);

            $number = count($iterator);
            $items_list = [];
            $used = [];
            //var_dump(count($iterator));
            // while ($data = $iterator->next()) {
            foreach ($iterator as $id => $data) {
                $items_list[$data['linkid']] = $data;
                $used[$data['id']] = $data['id'];
            }

            if ($canedit) {
                echo "<form name='allitemitem_form$rand' id='allitemitem_form$rand' method='post' action='" . Toolbox::getItemTypeFormURL(PluginDlteamsAllItem::class) . "'>";
                echo "<input type='hidden' name='itemtype1' value='" . $this->getType() . "' />";
                echo "<input type='hidden' name='items_id1' value='" . $this->getID() . "' />";
                echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsDataCarrierType::getType() . "' />";
                echo "<input type='hidden' name='entities_id' value='" . $this->getID(Entity::class) . "' />";
                // echo "<input type='hidden' name='comment' value='".$this->fields['comment']."' />";

                echo "<table class='tab_cadre_fixe'>";
                echo "<tr class='tab_bg_2'><th style='text-align:left!important'>" . __("Types de données contenues par ce catalogue", 'dlteams') . "</th></tr>";
                $subtitle = "ne pas inclure les types de données des sous-catalogues";
                echo "</table>";


                echo "<table class='tab_cadre_fixe'>";
                echo "<td style='text-align:right'>" . __("Type de données", 'dlteams') . "</td>";
                echo "<td>";
                PluginDlteamsDataCarrierType::dropdown([
                    'addicon' => PluginDlteamsDataCarrierType::canCreate(),
                    'name' => 'items_id',
                    'width' => '300px'
                ]);
                echo "</td>";

                echo "<tr>";
                echo "<td style='text-align:right'>" . __("Comment") . " " . "</td>";
                $comment = Html::cleanInputText($this->fields['comment']);
                echo "<td>" . "<textarea style='width:100%' rows='1' name='comment' >" . $comment . "</textarea>" . "</td>";
                echo "<td class='left'><input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin:0px auto!important'>" . "</td>";
                echo "</tr>";
                echo "</table>";

                Html::closeForm();
            }

            if ($iterator) {
                echo "<div class='spaced'>";
                if ($canedit && $number) {
                    Html::openMassiveActionsForm('mass' . PluginDlteamsDataCatalog_Item::class . $rand);
                    $massive_action_params = ['container' => 'mass' . PluginDlteamsDataCatalog_Item::class . $rand,
                        'num_displayed' => min($_SESSION['glpilist_limit'], $number)];
                    Html::showMassiveActions($massive_action_params);
                }
                echo "<table class='tab_cadre_fixehov'>";

                $header_begin = "<tr>";
                $header_top = '';
                $header_bottom = '';
                $header_end = '';

                if ($canedit && $number) {
                    $header_begin .= "<th width='10'>";
                    $header_top .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsDataCatalog_Item::class . $rand);
                    $header_bottom .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsDataCatalog_Item::class . $rand);
                    $header_end .= "</th>";
                }

                $header_end .= "<th width='20%' style='text-align:left'>" . __("Specify the data format", 'dlteams') . "</th>";
                // $header_end .= "<th width='20%'>" . __("Type", 'dlteams') . "</th>";
                $header_end .= "<th width='80%' style='text-align:left'>" . __("Comment", 'dlteams') . "</th>";
                $header_end .= "</tr>";

                echo $header_begin . $header_top . $header_end;
                foreach ($items_list as $data) {
                    if ($data['name']) {
                        echo "<tr class='tab_bg_1'>";

                        if ($canedit && $number) {
                            echo "<td width='10'>";
                            Html::showMassiveActionCheckBox(PluginDlteamsDataCatalog_Item::class, $data['linkid']);
                            echo "</td>";
                        }

                        $link = $data['name'];
                        if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
                            $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
                        }
                        $name = "<a target='_blank' href=\"" . PluginDlteamsDataCarrierType::getFormURLWithID($data['id']) . "\">" . $link . "</a>";

                        echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                        echo ">" . $name . "</td>";
                        // echo "<td class='left'>" . $data['type'] . " </td>";
                        echo "<td class='left'>" . $data['comment'] . "</td>";
                        echo "</tr>";
                    }
                }

                if ($iterator->count() > 10) {
                    echo $header_begin . $header_bottom . $header_end;
                }
                echo "</table>";

                if ($canedit && $number) {
                    //$massive_action_params['ontop'] = false;
                    //Html::showMassiveActions($massive_action_params);
                    Html::closeForm();
                }
                echo "</div>";
            }

//            means of access
//            if ($canedit) {
//                echo "<form name='meansofaccess_form$rand' id='meansofaccess_form$rand' method='post'
//         action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
//                echo "<input type='hidden' name='itemtype' value='" . $this->getType() . "' />";
//                echo "<input type='hidden' name='datacatalogs_id' value='" . $this->getID() . "' />";
//                echo "<input type='hidden' name='itemtype1' value='" . PluginAccountsAccount::getType() . "' />";
//                echo "<input type='hidden' name='entities_id' value='" . $this->getID(Entity::class) . "' />";
//                echo "<input type='hidden' name='comment' value='" . $this->fields['comment'] . "' />";
//
//                echo "<table class='tab_cadre_fixe'>";
//                echo "<tr class='tab_bg_2'><th style='text-align:center!important'>" . __("Indiquez les moyens d'accès au catalogue", 'dlteams') . "</th></tr>";
//                echo "</table>";
//
//                echo "<table class='tab_cadre_fixe'>";
//                echo "<td style='text-align:right'>" . __("Moyen d'accès", 'dlteams') . "</td>";
//                echo "<td>";
//                echo "<div style='display: flex; gap: 4px; align-items: center'>";
//
//                PluginDlteamsMeansOfAcce::dropdown([
//                    'addicon' => PluginDlteamsMeansOfAcce::canCreate(),
//                    'name' => 'meansofacces_id',
//
//                ]);
//
//                echo "</td>";
//
//                echo "</div>";
//
//
//                echo "<tr class='tab_bg_1' style='display: none' id='mandatory_field'>";
//                echo "<td width='25%' style='text-align: right'>" . __('Mandatory') . "</td>";
//                echo "<td width='75%'>";
//
//
//                Html::showCheckbox(['name' => 'mandatory',
//                    'title' => __('Mandatory', 'dlteams')
//                ]);
//                /**add by me**/
//                echo "</td>";
//                echo "</tr>";
//
//                echo "<tr id='comment_meoa' style='display: none'>";
//                echo "<td id='comment_label_meansofacces' style='text-align:right'>" . __("Commentaires", 'dlteams') . " " . "</td>";
//                $comment = Html::cleanInputText($this->fields['comment']);
//                echo "<td>" . "<textarea style='width:100%' rows='2' name='comment' >" . $comment . "</textarea>" . "</td>";
//                echo "<td class='left'><input type='submit' name='addmeansofaccess' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin:0px auto!important'>" . "</td>";
//                echo "</tr>";
//
//                echo "</table>";
//
//                Html::closeForm();
//
//                echo "<script>
//                $(document).ready(function(e){
//
//                $('select[name=meansofacces_id]').on('change', function () {
//
//                    if ($(this).val() != '0') {
//                            document.getElementById('comment_meoa').style.display = '';
//                            document.getElementById('mandatory_field').style.display = '';
//
//                        } else {
//                            document.getElementById('comment_meoa').style.display = 'none';
//                            document.getElementById('mandatory_field').style.display = 'none';
//                        }
//
//                        switch ($(this).val()) {
//                          case '1':
//                              $('#account_field').css('display', 'block');
//                              break;
//                          case '2':
//                              $('#account_field').css('display', 'none');
//                              break;
//                          case '3':
//                              $('#account_field').css('display', 'none');
//                              break;
//                          case '4':
//                              $('#comment_label_meansofacces').text('" . __("Commentaires", 'dlteams') . "');
//                              $('#account_field').css('display', 'none');
//                              break;
//                          case '5':
//                              $('#comment_label_meansofacces').text('quelles habilitation ?');
//                              $('#account_field').css('display', 'none');
//                              break;
//                          case '6':
//                              $('#comment_label_meansofacces').text('quelles matériels ?');
//                              $('#account_field').css('display', 'none');
//                              break;
//                          case '7':
//                              $('#comment_label_meansofacces').text('quelles horaires ?');
//                              $('#account_field').css('display', 'none');
//                              break;
//                        }
//                    });
//
//                });
//        </script>";
//            }
//
//            $items_list = static::getMeansOfAccesRequest($this);
//
//            if ($canedit) {
//                // Display recipients
//                echo "<div class='spaced'>";
//                if ($canedit && count($items_list)) {
//                    Html::openMassiveActionsForm('mass' . PluginDlteamsDataCatalog_Item::class . $rand);
//                    $massive_action_params = ['container' => 'mass' . PluginDlteamsDataCatalog_Item::class . $rand,
//                        'num_displayed' => min($_SESSION['glpilist_limit'], $number)];
//                    Html::showMassiveActions($massive_action_params);
//                }
//                echo "<table class='tab_cadre_fixehov'>";
//
//                $header_begin = "<tr>";
//                $header_top = '';
//                $header_bottom = '';
//                $header_end = '';
//
//                if ($canedit && count($items_list)) {
//
//                    $header_begin .= "<th width='10'>";
//                    $header_top .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsDataCatalog_Item::class . $rand);
//                    $header_bottom .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsDataCatalog_Item::class . $rand);
//                    $header_end .= "</th>";
//                }
//
//                $header_end .= "<th>" . __("Moyen d'accès") . "</th>";
//                $header_end .= "<th>" . __("Obligatoire", 'dlteams') . "</th>";
//                $header_end .= "<th>" . __("Comment") . "</th>";
//                $header_end .= "</tr>";
//
//
//                echo $header_begin . $header_top . $header_end;
//                //var_dump($items_list);
//
//                foreach ($items_list as $data) {
//                    echo "<tr class='tab_bg_1'>";
//
//                    if ($canedit && count($items_list)) {
//                        echo "<td width='10'>";
//                        Html::showMassiveActionCheckBox(PluginDlteamsDataCatalog_Item::class, $data['linkid']);
//                        echo "</td>";
//                    }
//
//                    $itemtype_str = $data['itemtype'];
//                    $itemtype_object = new $itemtype_str();
//                    $itemtype_object->getFromDB($data['items_id']);
//
//                    $name = "<a target='_blank' href=\"" . $data['itemtype']::getFormURLWithID($data['items_id']) . "\">" . $itemtype_object->fields['name'] . "</a>";
//
//                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
//                    echo ">" . $name . "</td>";
//
//                    $mandatory = isset($data["meansofacces_mandatory"]) && $data["meansofacces_mandatory"] ? "oui" : "Non";
//                    echo "<td class='left' width='30%'>" . $mandatory . "</td>";
//                    echo "<td class='left' width='40%'>" . ($data['comment'] ?? "") . "</td>";
//                    echo "</tr>";
//                }
//
//                if ($iterator->count() > 10) {
//                    echo $header_begin . $header_bottom . $header_end;
//                }
//                echo "</table>";
//
//                if ($canedit && $number > 10) {
//                    $massive_action_params['ontop'] = false;
//                    Html::showMassiveActions($massive_action_params);
//                }
//                Html::closeForm();
//
//
//                echo "</div>";
//            }
        }
    }


    public function isUsed()
    {

        if (parent::isUsed()) {
            return true;
        }

        return $this->isUsedInIemsTables();
    }


    /**
     * Check if group is used in consumables.
     *
     * @return boolean
     */
    private function isUsedInIemsTables()
    {

        $counts = countElementsInTable(
            PluginDlteamsDataCatalog_Item::getTable(),
            [
                'datacatalogs_id' => $this->fields['id'],
            ]
        );


        return $counts > 0;
    }

    public static function annuaireTiersRequest(PluginDlteamsDataCatalog $annuaire){
        global $DB;
        $data = [];
        $iterator = $DB->request([
            'SELECT' => [
                PluginDlteamsDataCatalog_Item::getTable() . '.id as linkid',
                PluginDlteamsDataCatalog::getTable() . '.id as id',
                PluginDlteamsDataCatalog::getTable() . '.name as name',
            ],
            'FROM' => PluginDlteamsDataCatalog_Item::getTable(),
            'JOIN' => [
                PluginDlteamsDataCatalog::getTable() => [
                    'FKEY' => [
                        PluginDlteamsDataCatalog_Item::getTable() => 'items_id',
                        PluginDlteamsDataCatalog::getTable() => 'id'
                    ]
                ]
            ],

            'WHERE' => [
                PluginDlteamsDataCatalog_Item::getTable() . '.datacatalogs_id' => $annuaire->fields['id'],
                PluginDlteamsDataCatalog_Item::getTable() . '.itemtype' => PluginDlteamsDataCatalog::class,
            ],
            'ORDER' => ['name ASC'],
        ], "", true);

        foreach ($iterator as $catalog_item)
            $data[] = $catalog_item;

        return $data;
    }

    public function additionalShowOtherCatalogs($id)
    {
        if ($id) {
            $id = $this->fields['id'];
            if (!$this->can($id, READ)) {
                return false;
            }
            $canedit = $this->can($id, UPDATE);
            $rand = mt_rand(1, mt_getrandmax());
            global $CFG_GLPI;
            global $DB;


            // Section Data type - BEGIN
            $id = $this->fields['id'];
            if (!$this->can($id, READ)) {
                return false;
            }
            $canedit = $this->can($id, UPDATE);
            $rand = mt_rand(1, mt_getrandmax());

            if ($this->fields["use_other_directory"]) {

                $iterator = static::annuaireTiersRequest($this);

//                var_dump($iterator->getSql());
//                die();
                $number = count($iterator);
                $items_list = [];
                $used = [];
                //var_dump(count($iterator));
                // while ($data = $iterator->next()) {
                foreach ($iterator as $id => $data) {
                    $items_list[$data['linkid']] = $data;
                    $used[$data['id']] = $data['id'];
                }

                if ($canedit) {
                    echo "<form name='allitemitem_form$rand' id='allitemitem_form$rand' method='post' action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
                    echo "<input type='hidden' name='datacatalogs_id' value='" . $this->getID() . "' />";

                    echo "<table class='tab_cadre_fixe'>";
                    echo "<tr class='tab_bg_2'><th style='text-align:left!important'>" . __("Services d'annuaire tiers utilisés pour les accès à ce catalogue", 'dlteams') . "</th></tr>";
                    echo "</table>";
                    echo "<table class='tab_cadre_fixe'>";
                    echo "<td style='text-align:right'>" . __("Service d'annuaire", 'dlteams') . "</td>";


                    /*                highlight_string("<?php\n\$data =\n" . var_export($used, true) . ";\n?>");*/
                    //                die();
                    echo "<td>";
                    PluginDlteamsDataCatalog::dropdown([
                        'addicon' => PluginDlteamsDataCatalog::canCreate(),
                        'name' => 'items_id',
                        'width' => '300px',
                        'used' => [$this->fields["id"] => $this->fields["id"], ...$used],
                        'condition' => [
                            ['is_directoryservice' => 1],
                        ]
                    ]);
                    echo "   ";
                    //echo "<tr>";
                    // echo "<td style='text-align:left'></td>";
                    echo "<class='left'><input type='submit' name='add_other_directory' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin:0px class='left' auto!important'>";
                    // echo "</tr>";
                    echo "</table>";
                    Html::closeForm();
                }

                if ($iterator) {
                    $ma_processor = PluginDlteamsDataCatalog_Otherdirectory_Ma::class;

                    echo "<div class='spaced'>";
                    if ($canedit && $number) {
                        Html::openMassiveActionsForm('mass' . $ma_processor . $rand);
                        $massive_action_params = ['container' => 'mass' . $ma_processor . $rand,
                            'num_displayed' => min($_SESSION['glpilist_limit'], $number)];
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
                        $header_bottom .= Html::getCheckAllAsCheckbox('mass' . $ma_processor . $rand);
                        $header_end .= "</th>";
                    }

                    $header_end .= "<th style='text-align:left'>" . __("Service annuaire", 'dlteams') . "</th>";
                    // $header_end .= "<th width='20%'>" . __("Type", 'dlteams') . "</th>";
//                $header_end .= "<th width='80%' style='text-align:left'>" . __("Comment", 'dlteams') . "</th>";
                    $header_end .= "</tr>";

                    echo $header_begin . $header_top . $header_end;
                    foreach ($items_list as $data) {
                        if ($data['name']) {
                            echo "<tr class='tab_bg_1'>";
                            if ($canedit && $number) {
                                echo "<td width='10'>";
                                Html::showMassiveActionCheckBox($ma_processor, $data['linkid']);
                                echo "</td>";
                            }

                            $link = $data['name'];
                            if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
                                $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
                            }
                            $name = "<a target='_blank' href=\"" . PluginDlteamsDataCatalog::getFormURLWithID($data['id']) . "\">" . $link . "</a>";

                            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                            echo ">" . $name . "</td>";
                            // echo "<td class='left'>" . $data['type'] . " </td>";
                            // echo "<td class='left'>" . $data['comment'] . "</td>";
                            echo "</tr>";
                        }
                    }

                    // if ($iterator->count() > 10) {
                    //    echo $header_begin . $header_bottom . $header_end;
                    // }
                    echo "</table>";

                    if ($canedit && $number) {
                        //$massive_action_params['ontop'] = false;
                        //Html::showMassiveActions($massive_action_params);
                        Html::closeForm();
                    }

                    echo "</div>";
                }
            }

        }
    }


    function prepareInputForAdd($input)
    {
        $input['users_id_creator'] = Session::getLoginUserID();
        return parent::prepareInputForAdd($input);
    }

    function prepareInputForUpdate($input)
    {
        $input['users_id_lastupdater'] = Session::getLoginUserID();
        return parent::prepareInputForUpdate($input);
    }

    function cleanDBonPurge()
    {
        /*$rel = new PluginDlteamsRecord_MotifEnvoi();
        $rel->deleteByCriteria(['plugin_dlteams_concernedpersons_id' => $this->fields['id']]);*/
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate && self::canView()) {
            $nb = 0;
            switch ($item->getType()) {
                case 'PluginDlteamsDataCatalog':

                    global $DB;
                    $ID = $item->getID();
                    $fk = $this->getForeignKeyField();

                    $result = $DB->request(
                        [
                            'FROM' => $this->getTable(),
                            'WHERE' => [$fk => $ID],
                            'ORDER' => 'name',
                        ]
                    );

                    $nb = count($result);

                    $ong = [];
                    $ong[1] = self::createTabEntry(_n('Child catalog', 'Childs catalogs', 2, 'dlteams'), $nb);
                    return $ong;
            }
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case 'PluginDlteamsDataCatalog':
                switch ($tabnum) {
                    case 1:
                        $item->showChildren();
                        return true;
                }
                break;
        }
        return false;
    }

    /**
     * @see CommonDBTM::showMassiveActionsSubForm()
     */
    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {

        switch ($ma->getAction()) {
            case 'copyTo':
                Entity::dropdown([
                    'name' => 'entities_id',
                ]);
                echo '<br /><br />' . Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                return true;
        }

        return parent::showMassiveActionsSubForm($ma);
    }

    /**
     * Print the HTML array children of a TreeDropdown
     *
     * @return void
     */
    public function showChildren()
    {
        global $DB;

        $ID = $this->getID();
        $this->check($ID, READ);
        $fields = array_filter(
            $this->getAdditionalFields(),
            function ($field) {
                return isset($field['list']) && $field['list'];
            }
        );
        $nb = count($fields);
        $entity_assign = $this->isEntityAssign();

        // Minimal form for quick input.
        if (static::canCreate()) {
            $link = $this->getFormURL();
            /*
            echo "<div class='firstbloc'>";
            echo "<form action='" . $link . "' method='post'>";
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr><th colspan='3'>" . __('New child heading') . "</th></tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td>" . __('Name') . "</td><td>";
            echo Html::input('name', ['value' => '']);

            if (
                $entity_assign
                && ($this->getForeignKeyField() != 'entities_id')
            ) {
                echo "<input type='hidden' name='entities_id' value='" . $_SESSION['glpiactive_entity'] . "'>";
            }

            if ($entity_assign && $this->isRecursive()) {
                echo "<input type='hidden' name='is_recursive' value='1'>";
            }
            echo "<input type='hidden' name='" . $this->getForeignKeyField() . "' value='$ID'></td>";
            echo "<td><input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='btn btn-primary'>";
            echo "</td></tr>\n";
            echo "</table>";
            Html::closeForm();
            echo "</div>\n";
            */

//            ajouter un catalogue existaant comme enfant

            echo "<div class='firstbloc'>";
            $this->initForm($this->fields["id"], []);
            $this->showFormHeader([]);
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr><th colspan='3'>" . __('Intitulé d\'enfant existant') . "</th></tr>";


            echo "<input type='hidden' name='id' value='" . $this->fields["id"] . "'>";
            echo "<tr class='tab_bg_1'><td style='text-align: right; width: 35%; white-space: nowrap'>" . __('Rattacher un catalogue comme enfant') . "</td><td style='min-width: 10%'>";
//            echo Html::input('name', ['value' => '']);
            PluginDlteamsDataCatalog::dropdown([
                'addicon' => self::canCreate(),
                'name' => 'son_id',
                'width' => '100%',
                'entity_sons' => true,
                'value' => null
            ]);

            if (
                $entity_assign
                && ($this->getForeignKeyField() != 'entities_id')
            ) {
                echo "<input type='hidden' name='entities_id' value='" . $_SESSION['glpiactive_entity'] . "'>";
            }

            if ($entity_assign && $this->isRecursive()) {
                echo "<input type='hidden' name='is_recursive' value='1'>";
            }
            echo "<input type='hidden' name='" . $this->getForeignKeyField() . "' value='$ID'></td>";
            echo "<td style='text-align: left'><input type='submit' name='update_as_child' value=\"" . _sx('button', 'Add') . "\" class='btn btn-primary'>";
            echo "</td></tr>\n";
            echo "</table>";
            Html::closeForm();
            echo "</div>\n";
        }


        $rand = mt_rand();
        echo "<div class='spaced'>";

        echo "<table class='tab_cadre_fixehov'>";

        echo "<tr class='noHover'><th colspan='" . ($nb + 3) . "'>" . sprintf(
                __('Sons of %s'),
                $this->getTreeLink()
            );
        echo "</th></tr>";

        echo "</table>";

        $fk = $this->getForeignKeyField();

        $result = $DB->request(
            [
                'FROM' => $this->getTable(),
                'WHERE' => [$fk => $ID],
                'ORDER' => 'name',
            ]
        );

//        $ma_processor = PluginDlteamsDatacatalog_Childs_Massiveactions::class;
//
//
//        Html::openMassiveActionsForm('mass' . $ma_processor . $rand);
//        $massiveactionparams = [
//            'num_displayed' => min($_SESSION['glpilist_limit'], count($result)),
//            'container' => 'mass' . $ma_processor . $rand
//        ];
//        Html::showMassiveActions($massiveactionparams);


        echo "<table class='tab_cadre_fixehov'>";
        $header = "<tr>";

//        $header .= "<th width='10'>";
//        $header .= Html::getCheckAllAsCheckbox('mass' . $ma_processor . $rand);
//        $header .= "</th>";

        echo "<th>" . __('Name') . "</th>";
        if ($entity_assign) {
            $header .= "<th>" . Entity::getTypeName(1) . "</th>";
        }
        foreach ($fields as $field) {
            $header .= "<th>" . $field['label'] . "</th>";
        }
        $header .= "<th>" . __('Comments') . "</th>";
        $header .= "</tr>\n";
        echo $header;


        $nb = 0;
        foreach ($result as $data) {
            $nb++;
            echo "<tr class='tab_bg_1'>";

//            echo "<td>";
//            Html::showMassiveActionCheckBox($ma_processor, $data["id"]);
//            echo "</td>";

            echo "<td>";


            if (
                (($fk == 'entities_id') && in_array($data['id'], $_SESSION['glpiactiveentities']))
                || !$entity_assign
                || (($fk != 'entities_id') && in_array($data['entities_id'], $_SESSION['glpiactiveentities']))
            ) {
                echo "<a href='" . $this->getFormURL();
                echo '?id=' . $data['id'] . "'>" . $data['name'] . "</a>";
            } else {
                echo $data['name'];
            }
            echo "</td>";
            if ($entity_assign) {
                echo "<td>" . Dropdown::getDropdownName("glpi_entities", $data["entities_id"]) . "</td>";
            }

            foreach ($fields as $field) {
                echo "<td>";
                switch ($field['type']) {
                    case 'UserDropdown':
                        echo getUserName($data[$field['name']]);
                        break;

                    case 'bool':
                        echo Dropdown::getYesNo($data[$field['name']]);
                        break;

                    case 'dropdownValue':
                        echo Dropdown::getDropdownName(
                            getTableNameForForeignKeyField($field['name']),
                            $data[$field['name']]
                        );
                        break;

                    default:
                        echo $data[$field['name']];
                }
                echo "</td>";
            }
            echo "<td>" . $data['comment'] . "</td>";
            echo "</tr>\n";
        }
//        if ($nb) {
//            echo $header;
//        }
        echo "</table></div>\n";
    }

    /* Execute massive action for dlteams Plugin
     * @see CommonDBTM::processMassiveActionsForOneItemtype()
     */
    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        switch ($ma->getAction()) {
            case 'copyTo':
                if ($item->getType() == PluginDlteamsDataCatalog::class) {
                    // @var PluginDlteamsRecord $item
                    foreach ($ids as $id) {
                        if ($item->getFromDB($id)) {
                            if ($item->copy1($ma->POST['entities_id'], $id, $item)) {
                                //Session::addMessageAfterRedirect(sprintf(__('Record copied: %s', 'dlteams'), $item->getName()));
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                            }
                        } else {
                            // Example of ko count
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        }
                    }
                }
                return;
                break;

        }
    }

    public static function rawSearchOptionsToAdd($itemtype = null)
    {
        $tab = [];

        /*$tab[] = [
            'id' => 'document',
            'name' => self::getTypeName(Session::getPluralNumber())
        ];*/

        $tab[] = [
            'id' => '100',
            'table' => 'glpi_documents_items',
            'field' => 'id',
            'name' => _x('quantity', 'Documents'),
            'forcegroupby' => true,
            'usehaving' => true,
            'datatype' => 'count',
            'massiveaction' => false,
            'joinparams' => [
                'jointype' => 'itemtype_item'
            ]
        ];

        return $tab;
    }


    function rawSearchOptions()
    {

        $tab = [];
//        $tab[] = [
//            'id'                 => '9',
//            'table'              => 'glpi_plugin_dlteams_datacatalogs',
//            'field'              => 'completename',
//            'name'               => PlugindlteamsDataCatalog::getTypeName(1),
//            'datatype'           => 'dropdown'
//        ];
        $tab[] = [
            'id' => '1',
            'table' => $this->getTable(),
            'field' => 'completename',
            'name' => __('Complete name'),
            'datatype' => 'itemlink',
            'massiveaction' => false,
            'autocomplete' => true,
        ];

        $tab[] = [
            'id' => '2',
            'table' => $this->getTable(),
            'field' => 'id',
            'name' => __("ID"),
            'massiveaction' => false,
            'datatype' => 'number',
        ];

        $tab[] = [
            'id' => '3',
            'table' => 'glpi_plugin_dlteams_datacatalogs',
            'field' => 'name',
            'linkfield' => 'plugin_dlteams_datacatalogs_id',
            'name' => __("Catalogue parent"),
            'datatype' => 'dropdown',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '4',
            'table' => 'glpi_plugin_dlteams_datacarriercategories',
            'field' => 'name',
            'name' => __("Type de données"),
            'datatype' => 'dropdown',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '5',
            'table' => $this->getTable(),
            'field' => 'content',
            'name' => __("Content"),
            'datatype' => 'text',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '6',
            'table' => 'glpi_plugin_dlteams_catalogclassifications',
            'field' => 'name',
            'name' => __("Classification"),
            'datatype' => 'dropdown',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '7',
            'table' => 'glpi_users',
            'field' => 'name',
            'linkfield' => 'users_id_contact',
            'name' => __("Utilisateur resp."),
            'datatype' => 'dropdown',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '8',
            'table' => 'glpi_groups',
            'field' => 'name',
            'linkfield' => 'groups_id_contact',
            'name' => __("Groupe resp."),
            //'condition' => ['is_assign' => 1],
            'datatype' => 'dropdown',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '9',
            'table' => $this->getTable(),
            'field' => 'comment',
            'name' => __("Comments"),
            'datatype' => 'text',
            'toview' => true,
            'massiveaction' => false,
        ];

        $tab[] = [
            'id' => '14',
            'table' => 'glpi_entities',
            'field' => 'completename',
            'name' => __("Entity"),
            'datatype' => 'dropdown',
            'massiveaction' => false,
        ];

        $tab[] = [
            'id' => '15',
            'table' => $this->getTable(),
            'field' => 'is_recursive',
            'name' => __("Child entities"),
            'datatype' => 'bool',
            'massiveaction' => false,
        ];

        $tab[] = [
            'id' => '16',
            'table' => $this->getTable(),
            'field' => 'is_directoryservice',
            'name' => __("Annuaire"),
            'datatype' => 'bool',
            'massiveaction' => false,
        ];

        $tab[] = [
            'id' => '101',
            'table' => 'glpi_plugin_dlteams_policieforms_items',
            'field' => 'id',
            'name' => _x('quantity', 'Jeux de données'),
            'forcegroupby' => true,
            'usehaving' => true,
            'datatype' => 'count',
            'massiveaction' => false,
            'joinparams' => [
                'jointype' => 'itemtype_item'
            ]
        ];

        $tab[] = [
            'id' => '102',
            'table' => 'glpi_appliances_items',
            'field' => 'id',
            'name' => _x('quantity', 'Applicatifs'),
            'forcegroupby' => true,
            'usehaving' => true,
            'datatype' => 'count',
            'massiveaction' => false,
            'joinparams' => [
                'jointype' => 'itemtype_item'
            ]
        ];

        $tab[] = [
            'id' => '104',
            'table' => 'glpi_plugin_dlteams_accountkeys_items',
            'field' => 'id',
            'name' => _x('quantity', "Clés d'accès"),
            'forcegroupby' => true,
            'usehaving' => true,
            'datatype' => 'count',
            'massiveaction' => false,
            'joinparams' => [
                'jointype' => 'itemtype_item'
            ]
        ];

        $tab[] = [
            'id' => '105',
            'table' => 'glpi_plugin_dlteams_records_items',
            'field' => 'id',
            'name' => _x('quantity', 'Traitements'),
            'forcegroupby' => true,
            'usehaving' => true,
            'datatype' => 'count',
            'massiveaction' => false,
            'joinparams' => [
                'jointype' => 'itemtype_item'
            ]
        ];

        $tab[] = [
            'id' => '106',
            'table' => 'glpi_plugin_dlteams_protectivemeasures_items',
            'field' => 'id',
            'name' => _x('quantity', 'Mesures Protection'),
            'forcegroupby' => true,
            'usehaving' => true,
            'datatype' => 'count',
            'massiveaction' => false,
            'joinparams' => [
                'jointype' => 'itemtype_item'
            ]
        ];

        $tab[] = [
            'id' => '108',
            'table' => 'glpi_plugin_dlteams_datacenters_items',
            'field' => 'id',
            'name' => _x('quantity', 'Datacenter'),
            'forcegroupby' => true,
            'usehaving' => true,
            'datatype' => 'count',
            'massiveaction' => false,
            'joinparams' => [
                'jointype' => 'itemtype_item'
            ]
        ];

        $tab[] = [
            'id' => '109',
            'table' => 'glpi_computers_items',
            'field' => 'id',
            'name' => _x('quantity', 'Computers'),
            'forcegroupby' => true,
            'usehaving' => true,
            'datatype' => 'count',
            'massiveaction' => false,
            'joinparams' => [
                'jointype' => 'itemtype_item'
            ]
        ];
        return $tab;
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $this->addDefaultFormTab($ong);
        // l'onglet Sous-catalogues n'apparait que si il existe des sous catalogues -> pas cohérent
		// if (!$this->fields["plugin_dlteams_datacarriercategories_id"]) 
        $this->addStandardTab('PluginDlteamsDataCatalog', $ong, $options);

        $this->addStandardTab(PluginDlteamsPolicieForm_Item::class, $ong, $options);
//        l'onglet est annuaire de n'apparait que si il s'agit d'un annuaire + il a été sélectionné comme service d'annuaire tiers
        $crit_est_annuaire_tier = [
          "itemtype" => PluginDlteamsDataCatalog::class,
          "items_id" => $this->fields["id"]
        ];
        $crit_est_annuaire_tier_catalog = new PluginDlteamsDataCatalog_Item();
        if($this->fields["is_directoryservice"] && $crit_est_annuaire_tier_catalog->getFromDBByCrit($crit_est_annuaire_tier))
            $this->addStandardTab(PluginDlteamsDatacatalog_Directory::class, $ong, $options);

        // Aucune clé ou compte nécéssaire
        if (!$this->fields["is_helpdesk_visible"]) {
            $this->addStandardTab(PluginDlteamsAccountKey_Directory::class, $ong, $options);
            $this->addStandardTab(PluginDlteamsAccountKey_Item::class, $ong, $options);
        }
        if ($this->fields["plugin_dlteams_datacarriercategories_id"] != 2) {
            $this->addStandardTab(PluginDlteamsAppliance_Item::class, $ong, $options);
        }
        $this->addStandardTab('PluginDlteamsDatacatalog_Protectivemeasure', $ong, $options);
        $this->addStandardTab(PluginDlteamsAcces_Catalog::class, $ong, $options);
        if (!$this->fields["plugin_dlteams_datacatalogs_id"]) {
            $this->addStandardTab('PluginDlteamsDataCatalogStorage_Item', $ong, $options);
        }
        $this->addStandardTab('PluginDlteamsObject_document', $ong, $options)
            //->addStandardTab('PluginDlteamsDataCatalog_Item', $ong, $options)
            ->addStandardTab('ManualLink', $ong, $options)
            ->addStandardTab(PluginDlteamsTicket_Item::class, $ong, $options)
            ->addStandardTab('KnowbaseItem_Item', $ong, $options)
            ->addImpactTab($ong, $options)
            ->addStandardTab('Notepad', $ong, $options)
            ->addStandardTab('Log', $ong, $options);
        return $ong;
    }

    private function showDatacarrierCategoryTab($type, $key)
    {
        return $this->fields["plugin_dlteams_datacarriercategories_id"] == $key;
    }


    private function canShowTabOfItemtype($itemtype, $items_id)
    {
        $request = [
            "FROM" => PluginDlteamsDataCatalog_Item::getTable(),
            "WHERE" => [
                "datacatalogs_id" => $this->fields["id"],
                "itemtype" => PluginDlteamsMeansOfAcce::class,
                "items_id" => $items_id
            ]
        ];
        global $DB;
        $iterator = $DB->request($request);
        return count($iterator) > 0;
    }

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
        $input['entities_id'] = $_POST['entities_id'];
        $input['comment'] = str_replace(['\'', '"'], "", $input['comment']);
        $input['name'] = str_replace(['\'', '"'], "", $input['name']);
        $input['content'] = str_replace(['\'', '"'], "", $input['content']);
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


    public function copy1($entity, $id, $item)
    {
        global $DB;
        $dbu = new DbUtils();
        $name = $item->fields['name'];

        $nb = $dbu->countElementsInTable(static::getTable(), ['name' => addslashes($name), 'entities_id' => $entity]);

//        $req = $DB->request("SELECT * FROM `glpi_plugin_dlteams_datacatalogs` WHERE `entities_id` = $entitiesB_id AND `name` = (SELECT `name` FROM `glpi_plugin_dlteams_datacatalogs` WHERE `id` = $id)");


        // var_dump (count($req)) ;
//        if (count($req)) { // oui existe -> on quitte
//            foreach ($req as $id => $row) {
//                $id = $row['id'];
//                // pour tests -- $DB->request("DELETE FROM glpi_plugin_dlteams_records WHERE id = $id");
//                // var_dump ("Traitement déjà existant (entité, record) : ", $entitiesB_id,", ", $id);
//                $message .= (sprintf(__('Catalogue de données déjà existant : %s', 'dlteams'), $item->getName())) . nl2br("\n") ;
//            }
//            Session::addMessageAfterRedirect($message, false, ERROR);
//            Html::back();
//        }

        $dt = new PluginDlteamsDataCatalog();
        $dt->getFromDB($id);

//        copy the new catalog
        $datacatalog = new PluginDlteamsDataCatalog();
        $datacatalog->add([

        ]);

        if ($nb <= 0) {
//            $DB->request("INSERT INTO " . static::getTable() . " (entities_id, is_recursive, date_mod, date_creation, name, content, comment, plugin_dlteams_rightmeasurecategories_id) SELECT '$entity', is_recursive, date_mod, date_creation, name, content, comment, 0 FROM " . static::getTable() . " WHERE id='$id'");
            $datacatalog = new PluginDlteamsDataCatalog();
            $datacatalog->add([
                "name" => addslashes($dt->fields["name"]),
                "content" => addslashes($dt->fields["content"]),
                "plugin_dlteams_catalogclassifications_id" => $dt->fields["plugin_dlteams_catalogclassifications_id"],
                "plugin_dlteams_datacarriercategories_id" => $dt->fields["plugin_dlteams_datacarriercategories_id"],
                "entities_id" => $entity,
            ]);
            $newid = $datacatalog->fields["id"];

            $relations_to_copy = [
                PluginDlteamsPolicieForm::class,
                PluginDlteamsAppliance::class,
                PluginDlteamsProtectiveMeasure::class
            ];

            $DB->request("INSERT INTO " . PluginDlteamsDataCatalog_Item::getTable() . " (datacatalogs_id, items_id, itemtype, comment) SELECT '$newid', items_id, itemtype, comment FROM " . PluginDlteamsDataCatalog_Item::getTable() . " WHERE datacatalogs_id='$id' AND (itemtype='PluginDlteamsPolicieForm' OR itemtype='PluginDlteamsAppliance' OR itemtype='PluginDlteamsProtectiveMeasure')");
            $DB->request("INSERT INTO " . PluginDlteamsPolicieForm_Item::getTable() . " (policieforms_id, items_id, itemtype, comment) SELECT policieforms_id, '$newid', itemtype, comment FROM " . PluginDlteamsPolicieForm_Item::getTable() . " WHERE itemtype='PluginDlteamsDataCatalog' AND items_id='$id'");
            $DB->request("INSERT INTO " . Appliance_Item::getTable() . " (appliances_id, items_id, itemtype, comment) SELECT appliances_id, '$newid', itemtype, comment FROM " . Appliance_Item::getTable() . " WHERE itemtype='PluginDlteamsDataCatalog' AND items_id='$id'");
            $DB->request("INSERT INTO " . PluginDlteamsProtectiveMeasure_Item::getTable() . " (protectivemeasures_id, items_id, itemtype, comment) SELECT protectivemeasures_id, '$newid', itemtype, comment FROM " . PluginDlteamsProtectiveMeasure_Item::getTable() . " WHERE itemtype='PluginDlteamsDataCatalog' AND items_id='$id'");

            return true;
        } else {
            return false;
        }
    }


}

?>

