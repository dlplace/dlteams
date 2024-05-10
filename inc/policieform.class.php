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

class PluginDlteamsPolicieForm extends CommonDropdown implements
    PluginDlteamsExportableInterface
{
    use PluginDlteamsExportable;

    static $rightname = 'plugin_dlteams_policieform';
    public $dohistory = true;
    protected $usenotepad = true;

    static function getTypeName($nb = 0)
    {
        return _n("Processing document", "Processing documents", $nb, 'dlteams');
    }

    function showForm($id, $options = [])
    {
        global $CFG_GLPI;
        $this->initForm($id, $options);
        $this->showFormHeader($options);

        echo "<table, th, td width='100%'>";

        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td width='15%' style='text-align:right' >" . __("Name", 'dlteams') . "</td>";
        echo "<td>";
        $name = Html::cleanInputText($this->fields['name']);
        echo "<input type='text' style='width:98%' name='name' required value='" . $name . "'>" . "</td>";
        echo "<td width='15%' style='text-align:right'>" . "" . "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td width='15%' style='text-align:right'>" . __("Content", 'dlteams') . "</td>";
        echo "<td>";
        $content = Html::cleanInputText($this->fields['content']);
        echo "<textarea style='width: 98%;' name='content' rows='3'>" . $content . "</textarea>";
        echo "</td></tr>";

        
        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td width='15%' div style='text-align:right'>" . __("Catégorie de document", 'dlteams') . "</td>";
        echo "<td>";
        DocumentCategory::dropdown([
            'addicon' => DocumentCategory::canCreate(),
            'name' => 'documentcategories_id',
            'value' => $this->fields['documentcategories_id'] ?? "", //$responsible,
            'right' => 'all',
            'width' => "300px",
            // 'rand'   => $randDropdown
        ]);
        echo "</td></tr>";

        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td width='15%' style='text-align:right'>" . __("Document modèle", 'dlteams') . "</td>";
        echo "<td style='display: flex;' class='left'>";
//        Document::dropdown([
//            'entity' => Session::getActiveEntity(),
//            'name' => 'documents_id',
//            'value' => $this->fields['documents_id'] ?? "", //$responsible,
//            'width' => "300px",
//        ]);
        Dropdown::show(Document::getType(), [
            'entity' => Session::getActiveEntity(),
            'name' => 'documents_id',
            'value' => $this->fields['documents_id'] ?? "", //$responsible,
            'width' => "300px",
        ]);
        $field_id = Html::cleanId("dropdown_" . 'items_' . mt_rand());
        $item_link = getItemForItemtype(Document::class);
        echo '<div style="padding: 2px; border-radius:2px; border: solid 1px #d9dbde; border-left: 0px" class="btn btn-outline-secondary"
                           title="' . __s('Add') . '" data-bs-toggle="modal" data-bs-target="#add_' . $field_id . '">'
            . Ajax::createIframeModalWindow('add_' . $field_id, $item_link->getFormURL(), ['display' => false])
            . "<span data-bs-toggle='tooltip'>
              <i class='fa-fw ti ti-plus'></i>
              <span class='sr-only'>" . __s('Add') . "</span>
                </span>"
            . '</div>';
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td width='15%' style='text-align:right'>" . "Fichier " . "</td>";
        echo "<td>";
        // echo "<span>";
        echo "<div id='document_additional_fields'>";
        // echo "<div style='display: flex; flex-direction: column; gap: 4px;'>";
        $output = "";
        /*        highlight_string("<?php\n\$data =\n" . var_export($dc->fields, true) . ";\n?>");*/
//        die();
        $dc = new Document();
        // echo "<span>";

        $dc->getFromDB($this->fields["documents_id"]);
        $output .= "<a href='/front/document.send.php?docid=" . $this->fields["documents_id"] . "' target='_blank'>" . $dc->getDownloadLink(null, 45) . "</a> <NOBR>";
        // $output.="<a href='/front/document.send.php?docid=".$this->fields["documents_id"]."' target='_blank'>".$dc->getField("filename")."</a> <NOBR>";
        $output .= "Lien : " . "<a href='/front/document.send.php?docid=" . $this->fields["documents_id"] . "' target='_blank'>" . $dc->getField("link") . "</a>";
        echo $output;
        // echo "</span>";
        echo "</div>";
        echo "</div>";
        echo "</td></tr>";


        $script = "<script>";
        $script .= "$(document).ready(function(){
            $('select[name=documents_id').change(function(e){            
                $.ajax({
                                url: '/marketplace/dlteams/ajax/typedocument_additionalfields.php',
                                type: 'POST',
                                data: {
                                    id: $(this).val(),
                                },
                                success: function (data) {
                                    $('#document_additional_fields').html(data);
                                   
                                }
                            }); 
//                               end ajax
            });
        });";
        $script .= "</script>";
        echo $script;

        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td width='15%' style='text-align:right'>" . __("Commentaire", 'dlteams') . "</td>";
        echo "<td>";
        $comment = Html::cleanInputText($this->fields['comment']);
        echo "<textarea style='width: 98%;' name='comment' rows='3'>" . $comment . "</textarea>";
        echo "</td></tr>";
        echo "</table>";

        $this->showFormButtons($options);
		$this->ShowDcpType($id);
        $this->ShowDatacarrierType($id);
        return true;
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

    public function ShowDcpType($id)
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

            $iterator = $DB->request([
                'SELECT' => [
                    'glpi_plugin_dlteams_policieforms_items.id AS linkid',
                    'glpi_plugin_dlteams_policieforms_items.comment as comment',
                    'glpi_plugin_dlteams_processeddatas.id as id',
                    'glpi_plugin_dlteams_processeddatas.name as name',
                ],
                'FROM' => 'glpi_plugin_dlteams_policieforms_items',
                'JOIN' => [
                    'glpi_plugin_dlteams_processeddatas' => [
                        'FKEY' => [
                            'glpi_plugin_dlteams_policieforms_items' => 'items_id',
                            'glpi_plugin_dlteams_processeddatas' => 'id'
                        ]
                    ]
                ],
                'WHERE' => [
                    'glpi_plugin_dlteams_policieforms_items.policieforms_id' => $this->fields['id'],
                    'glpi_plugin_dlteams_policieforms_items.itemtype' => "PluginDlteamsProcessedData"
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
                echo "<form name='allitemitem_form$rand' id='allitemitem_form$rand' method='post' action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
                echo "<input type='hidden' name='itemtype1' value='" . $this->getType() . "' />";
                echo "<input type='hidden' name='items_id1' value='" . $this->getID() . "' />";
                echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsProcessedData::getType() . "' />";
                echo "<input type='hidden' name='entities_id' value='" . $this->getID(Entity::class) . "' />";
                echo "<input type='hidden' name='link_element' value='" . true . "' />";
                // echo "<input type='hidden' name='comment' value='".$this->fields['comment']."' />";

                echo "<table class='tab_cadre_fixe'>";
                echo "<tr class='tab_bg_2'><th style='text-align:left!important'>" . __("Types de données à caractère personnel pouvant être contenues dans ce jeu de données", 'dlteams') . "</th></tr>";
                echo "</table>";

                echo "<table class='tab_cadre_fixe'>";
                echo "<td style='text-align:right'>" . __("Type de DCP", 'dlteams') . "</td>";
                echo "<td>";
                PluginDlteamsProcessedData::dropdown([
                    'addicon' => PluginDlteamsProcessedData::canCreate(),
                    'name' => 'items_id',
                    'width' => '300px',
                    'used' => $used
                ]);
                echo "</td>";

                echo "<tr>";
                echo "<td style='text-align:right'>" . __("Comment") . " " . "</td>";
//                $comment = Html::cleanInputText($this->fields['comment']);
                echo "<td>" . "<textarea style='width:100%' rows='1' name='comment' ></textarea>" . "</td>";
                echo "<td class='left'><input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin:0px auto!important'>" . "</td>";
                echo "</tr>";
                echo "</table>";
                Html::closeForm();
            }

            if ($iterator) {
                echo "<div class='spaced'>";
                if ($canedit && $number) {
                    Html::openMassiveActionsForm('mass' . PluginDlteamsPolicieForm_Item::class . $rand);
                    $massive_action_params = ['container' => 'mass' . PluginDlteamsPolicieForm_Item::class . $rand,
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
                    $header_top .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsPolicieForm_Item::class . $rand);
                    $header_bottom .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsPolicieForm_Item::class . $rand);
                    $header_end .= "</th>";
                }

                $header_end .= "<th width='30%' style='text-align:left'>" . __("Type de DCP", 'dlteams') . "</th>";
                // $header_end .= "<th width='30%'>" . __("Type", 'dlteams') . "</th>";
                $header_end .= "<th width='70%' style='text-align:left'>" . __("Comment") . "</th>";
                $header_end .= "</tr>";

                echo $header_begin . $header_top . $header_end;
                foreach ($items_list as $data) {
                    if ($data['name']) {
                        echo "<tr class='tab_bg_1'>";

                        if ($canedit && $number) {
                            echo "<td width='10'>";
                            Html::showMassiveActionCheckBox(PluginDlteamsPolicieForm_Item::class, $data['linkid']);
                            echo "</td>";
                        }

                        $link = $data['name'];
                        if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
                            $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
                        }
                        $name = "<a target='_blank' href=\"" . PluginDlteamsProcessedData::getFormURLWithID($data['id']) . "\">" . $link . "</a>";

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
        }
    }

    public function ShowDatacarrierType($id)
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

            $iterator = $DB->request([
                'SELECT' => [
                    'glpi_plugin_dlteams_policieforms_items.id AS linkid',
                    'glpi_plugin_dlteams_policieforms_items.comment as comment',
                    'glpi_plugin_dlteams_datacarriertypes.id as id',
                    'glpi_plugin_dlteams_datacarriertypes.name as name',
                ],
                'FROM' => 'glpi_plugin_dlteams_policieforms_items',
                'JOIN' => [
                    'glpi_plugin_dlteams_datacarriertypes' => [
                        'FKEY' => [
                            'glpi_plugin_dlteams_policieforms_items' => 'items_id',
                            'glpi_plugin_dlteams_datacarriertypes' => 'id'
                        ]
                    ]
                ],
                'WHERE' => [
                    'glpi_plugin_dlteams_policieforms_items.policieforms_id' => $this->fields['id'],
                    'glpi_plugin_dlteams_policieforms_items.itemtype' => "PluginDlteamsDataCarrierType"
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
                echo "<form name='allitemitem_form$rand' id='allitemitem_form$rand' method='post' action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
                echo "<input type='hidden' name='itemtype1' value='" . $this->getType() . "' />";
                echo "<input type='hidden' name='items_id1' value='" . $this->getID() . "' />";
                echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsDataCarrierType::getType() . "' />";
                echo "<input type='hidden' name='entities_id' value='" . $this->getID(Entity::class) . "' />";
                echo "<input type='hidden' name='link_element' value='" . true . "' />";
                // echo "<input type='hidden' name='comment' value='".$this->fields['comment']."' />";

                echo "<table class='tab_cadre_fixe'>";
                echo "<tr class='tab_bg_2'><th style='text-align:left!important'>" . __("Autres types de données (comptables, commerciales, ...)", 'dlteams') . "</th></tr>";
                echo "</table>";

                echo "<table class='tab_cadre_fixe'>";
                echo "<td style='text-align:right'>" . __("Type de données", 'dlteams') . "</td>";
                echo "<td>";
                PluginDlteamsDataCarrierType::dropdown([
                    'addicon' => PluginDlteamsDataCarrierType::canCreate(),
                    'name' => 'items_id',
                    'width' => '300px',
                    'used' => $used
                ]);
                echo "</td>";

                echo "<tr>";
                echo "<td style='text-align:right'>" . __("Comment") . " " . "</td>";
//                $comment = Html::cleanInputText($this->fields['comment']);
                echo "<td>" . "<textarea style='width:100%' rows='1' name='comment' ></textarea>" . "</td>";
                echo "<td class='left'><input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin:0px auto!important'>" . "</td>";
                echo "</tr>";
                echo "</table>";
                Html::closeForm();
            }

            if ($iterator) {
                echo "<div class='spaced'>";
                if ($canedit && $number) {
                    Html::openMassiveActionsForm('mass' . PluginDlteamsPolicieForm_Item::class . $rand);
                    $massive_action_params = ['container' => 'mass' . PluginDlteamsPolicieForm_Item::class . $rand,
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
                    $header_top .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsPolicieForm_Item::class . $rand);
                    $header_bottom .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsPolicieForm_Item::class . $rand);
                    $header_end .= "</th>";
                }

                $header_end .= "<th width='30%' style='text-align:left'>" . __("Type de données", 'dlteams') . "</th>";
                // $header_end .= "<th width='30%'>" . __("Type", 'dlteams') . "</th>";
                $header_end .= "<th width='70%' style='text-align:left'>" . __("Comment") . "</th>";
                $header_end .= "</tr>";

                echo $header_begin . $header_top . $header_end;
                foreach ($items_list as $data) {
                    if ($data['name']) {
                        echo "<tr class='tab_bg_1'>";

                        if ($canedit && $number) {
                            echo "<td width='10'>";
                            Html::showMassiveActionCheckBox(PluginDlteamsPolicieForm_Item::class, $data['linkid']);
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
        }
    }
	
    function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __("Characteristics")
        ];

        $tab[] = [
            'id' => '1',
            'table' => $this->getTable(),
            'field' => 'name',
            'name' => __("Name"),
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
            'table' => $this->getTable(),
            'field' => 'comment',
            'name' => __("Comments"),
            'datatype' => 'text',
            'massiveaction' => false,
        ];

        $tab[] = [
            'id' => '4',
            'table' => $this->getTable(),
            'field' => 'content',
            'name' => __("Contenu"),
            'datatype' => 'text',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '5',
            'table' => 'glpi_entities',
            'field' => 'completename',
            'name' => __("Entity"),
            'datatype' => 'dropdown',
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '6',
            'table' => $this->getTable(),
            'field' => 'is_recursive',
            'name' => __("Child entities"),
            'datatype' => 'bool',
            'massiveaction' => false,
        ];

        $tab[] = [
            'id' => '7',
            'table' => $this->getTable(),
            'field' => 'doc_a_signer',
            'name' => __("Document à signer"),
            'datatype' => 'bool',
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '8',
            'table' => 'glpi_documentcategories',
            'field' => 'name',
            'name' => __("Catégorie"),
            'datatype' => 'dropdown',
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '101',
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
            'id' => '102',
            'table' => 'glpi_plugin_dlteams_datacatalogs_items',
            'field' => 'id',
            'name' => _x('quantity', 'Catalogues'),
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
            'table' => 'glpi_plugin_dlteams_audits_items',
            'field' => 'id',
            'name' => _x('quantity', 'Audits'),
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
            'table' => 'glpi_plugin_dlteams_legalbasis_items',
            'field' => 'id',
            'name' => _x('quantity', 'Licéité'),
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
            'table' => 'glpi_plugin_dlteams_storageperiods_items',
            'field' => 'id',
            'name' => _x('quantity', 'Conservation'),
            'forcegroupby' => true,
            'usehaving' => true,
            'datatype' => 'count',
            'massiveaction' => false,
            'joinparams' => [
                'jointype' => 'itemtype_item'
            ]
        ];

        $tab[] = [
            'id' => '107',
            'table' => 'glpi_plugin_dlteams_protectivemeasures_items',
            'field' => 'id',
            'name' => _x('quantity', 'Protection'),
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
            'table' => 'glpi_plugin_dlteams_processeddatas_items',
            'field' => 'id',
            'name' => _x('quantity', 'DCP'),
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
            'table' => 'glpi_plugin_dlteams_processeddatas_items',
            'field' => 'id',
            'name' => _x('quantity', 'DCP2'),
            'forcegroupby' => true,
            'usehaving' => true,
            // 'datatype' => 'count',
			'datatype' => 'dropdown',
			'datatype' => 'itemlink',
            'massiveaction' => false,
            'joinparams' => [
                'jointype' => 'itemtype_item'
            ]
			
        ];


        /*$tab[] = [
            'id' => '108',
            'table' => 'glpi_plugin_dlteams_policiesforms_items',
            'field' => 'id',
            'name' => _x('quantity', 'Jeu de données'),
            'forcegroupby' => true,
            'usehaving' => true,
            'datatype' => 'count',
            'massiveaction' => false,
            'joinparams' => [
             'jointype' => 'itemtype_item'
            ]
        ];*/

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

    public function defineTabs($options = [])
    {
        $ong = [];
        $ong = array();

        $this->addDefaultFormTab($ong)
            ->addStandardTab('PluginDlteamsRecord_Item', $ong, $options)
            ->addStandardTab(PluginDlteamsDataCatalog_Item::class, $ong, $options)
            ->addStandardTab(PluginDlteamsConservation_Element::class, $ong, $options)
            ->addStandardTab(PluginDlteamsProtectiveMeasure_Item::class, $ong, $options)
            ->addStandardTab(PluginDlteamsPolicieForm_PersonalAndDataCategory::class, $ong, $options)
            ->addStandardTab(PluginDlteamsAcces_PolicieForm::class, $ong, $options)
            ->addStandardTab('PluginDlteamsPolicieForm_Item', $ong, $options)
            ->addStandardTab('PluginDlteamsObject_document', $ong, $options)
            ->addStandardTab('ManualLink', $ong, $options)
            ->addStandardTab(PluginDlteamsTicket_Item::class, $ong, $options)
            ->addStandardTab('KnowbaseItem_Item', $ong, $options)
            ->addImpactTab($ong, $options)
            ->addStandardTab('Notepad', $ong, $options)
            ->addStandardTab('Log', $ong, $options);
        return $ong;
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

//                $submit_button = "<button id='submit_copy' class='submit'>Envoyer</button>";
//                echo $submit_button;
//                $script = "<script>";
//                $script.="$(document).ready(function(){
//               // alert('uu');
//                    $('#submit_copy').click(function(e){
//                    e.preventDefault();
//                    alert('oh yes');
//                    });
//                });";
//                $script.= "</script>";
//
//                echo  $script;
                echo '<br /><br />' . Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                return true;
        }

        return parent::showMassiveActionsSubForm($ma);
    }

    function exportToDB($subItems = [])
    {
        if ($this->isNewItem()) {
            return false;
        }

        $export = $this->fields;
        return $export;
    }


    /* Execute massive action for dlteams Plugin
     * @see CommonDBTM::processMassiveActionsForOneItemtype()
     */
    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        switch ($ma->getAction()) {
            case 'copyTo':
                if ($item->getType() == PluginDlteamsPolicieForm::class) {
                    // @var PluginDlteamsRecord $item
                    global $DB;
                    $DB->beginTransaction();
                    $error = false;
                    foreach ($ids as $id) {
                        if ($item->getFromDB($id)) {
                            if ($item->copyPolicieForm($ma->POST['entities_id'], $id, $item)) {
                                //Session::addMessageAfterRedirect(sprintf(__('Record copied: %s', 'dlteams'), $item->getName()));
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                            }
                        } else {
                            // Example of ko count
                            $DB->rollback();
                            $error = true;
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        }
                    }
                    if ($error)
                        $DB->rollback();
                    else
                        $DB->commit();
                }
                return;
                break;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    public static function importToDB(PluginDlteamsLinker $linker, $input = [], $containerId = 0, $subItems = [])
    {
        $item = new self();
        $originalId = $input['id'];
        unset($input['id']);
        $input['entities_id'] = $_POST['entities_id'];;
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


}
