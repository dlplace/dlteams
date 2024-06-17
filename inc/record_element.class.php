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

// require_once('record_basedonnee.class.php');
// require_once('record_juridique.class.php');

use GlpiPlugin\dlteams\Exception\ImportFailureException;

class PluginDlteamsRecord_Element extends CommonDBTM
{
    static function getTypeName($nb = 0)
    {
        //return __("Contracts, DB", 'dlteams');
		return __("Collecte, Stockage", 'dlteams');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!$item->canView()) {
            return false;
        }
        switch ($item->getType()) {
            case PluginDlteamsRecord::class :
                $nb = count(static::getDataCatalogRequest($item)) + count(static::getRequest($item)) + count(self::getPolicyFormRequest($item));
                return self::createTabEntry(static::getTypeName($nb), $nb);
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $record, $tabnum = 1, $withtemplate = 0)
    {
        static::showTransmissionMethods($record);
        //echo "<br/>";
        static::showPolicyform($record);
        //echo "<br/>";
        static::showCollectedDocuments($record);
        //echo "<br/>";
//        static::showDataCatalog($record);
        //echo "<br/>";
        static::showCollectComment($record);
    }

    static function showDataCatalog(CommonDBTM $item, $withtemplate = 0)
    {
        $id = $item->fields['id'];
        $canedit = $item->can($id, UPDATE); // canedit booleen = true
        $rand = mt_rand(1, mt_getrandmax());
        global $DB;

        $iterator = static::getDataCatalogRequest($item);
        $number = count($iterator);
        $items_list = [];
        $used = [];

        //while ($data = $iterator->next()) {
        foreach ($iterator as $id => $data) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($canedit) {
            echo "<form name='datacatalogitem_form$rand' id='datacatalogitem_form$rand' method='post'
             action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . $item->getType() . "' />";
            echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsDataCatalog::class . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $item->getID() . "' />";
            echo "<input type='hidden' name='entities_id' value='" . $item->fields['entities_id'] . "' />";
//
            echo "<table class='tab_cadre_fixe'>";

            echo "<tr class='tab_bg_2'><th colspan='3'>" . "Stockage des données" .
                "</th>";
            echo "</tr>";

            echo "<tr style='height: 25px;'></tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td class='right' style='text-wrap: nowrap;' width='35%'>";
            echo "Indiquez les catalogues de données où les informations sont stockées";
            echo "</td>";
            echo "<td style='display: flex;' class='left'>";
            PluginDlteamsDataCatalog::dropdown([
                'addicon' => true,
                'name' => 'items_id',
                'value' => "", //$responsible,
                // 'entity' => $this->fields["entities_id"],
                'right' => 'all',
                'width' => "250px",
            ]);
//            $field_id = Html::cleanId("dropdown_" . 'items_' . mt_rand());
//            $item_link = getItemForItemtype(PluginDlteamsDataCatalog::class);
//
//            echo '<div style="padding: 2px; border-radius:2px;" class="btn btn-outline-secondary"
//                           title="' . __s('Add') . '" data-bs-toggle="modal" data-bs-target="#add_' . $field_id . '">'
//                . Ajax::createIframeModalWindow('add_' . $field_id, $item_link->getFormURL(), ['display' => false])
//                . "<span data-bs-toggle='tooltip'>
//              <i class='fa-fw ti ti-plus'></i>
//              <span class='sr-only'>" . __s('Add') . "</span>
//                </span>"
//                . '</div>';
            echo "</td>";
            echo "<td class='left'>";
            echo "</td>";
            echo "</tr>";

            echo "<tr class='tab_bg_1' style='display: none;' id='field-createlink'>";
            echo "<td class='right' width='20%'>";
            echo __("Comment");
            echo "</td>";
            echo "<td style='display: flex;' class='left'>";
            echo "<textarea type='text' style='width:100%;' maxlength=1000 rows='2' name='comment' class='comment'></textarea>";
            echo "</td>";
            echo "<td class='left'>";
            echo "</td></tr>";

            echo "<tr>";
            echo "<td>";
            echo "</td>";
            echo "<td colspan='2' class='left'>";
            echo "<button name='link_element' style='display: none;' id='btn-createlink' class='btn btn-primary'>Relier cet élément</button>";
            echo "</td></tr>";

            echo "</table>";
            Html::closeForm();
        }

        echo "<script>
                $(document).ready(function(e){

                $('select[name=items_id]').on('change', function () {
                    if($(this).val() != '0'){
                        document.getElementById('btn-createlink').style.display = 'block';
                        document.getElementById('field-createlink').style.display = 'table-row';
                        
                        $.ajax({
                                url: '/marketplace/dlteams/ajax/get_object_specific_field.php',
                                type: 'POST',
                                data: {
                                    id: $(this).val(),
                                    object: '" . PluginDlteamsDataCatalog::class . "',
                                    field: 'content'
                                },
                                success: function (data) {
                                    // Handle the returned data here
                                    let comm_field = $('textarea[name=comment]');
                                    comm_field.val(data);
                                    comm_field.val(comm_field.val().replace(/^\s+/, ''));
                                }
                            });                      
                        
                        
                    }
                    else{
                        document.getElementById('btn-createlink').style.display = 'none';
                        document.getElementById('field-createlink').style.display = 'none';
                    }
                       
                    });
                });
        </script>";

        $massive_action_processor = PluginDlteamsDataCatalog_Item::class;

        echo "<div class='spaced'>";
        if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . $massive_action_processor . $rand);
            $massive_action_params = [
                'container' => 'mass' . $massive_action_processor . $rand,
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
            $header_top .= Html::getCheckAllAsCheckbox('mass' . $massive_action_processor . $rand);
//                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            $header_end .= "</th>";
        }
        $header_end .= "<th>" . "Catalogue de données" . "</th>";
        $header_end .= "<th>" . "Commentaires" . "</th>";
        $header_end .= "</tr>";

        echo $header_begin . $header_top . $header_end;
        //var_dump($items_list);
        foreach ($items_list as $data) {
            echo "<tr class='tab_bg_1'>";

            if ($canedit && $number) {
                echo "<td width='10'>";

                $item_str = $item::class . "_Item";
//                        Computer_Item::class;
                Html::showMassiveActionCheckBox($massive_action_processor, $data['linkid']);
                echo "</td>";

                $id = $data['linkid'];
            }


            $name = "<a target='_blank' href=\"" . PluginDlteamsDataCatalog::getFormURLWithID($data["datacatalogs_id"]) . "\">" . $data["name"] . "</a>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $name . "</td>";


            echo "<td class='left'>";
            echo $data["comment"] . "</td>";


            echo "</tr>";
        }

        echo "</table>";

        Html::closeForm();

        echo "
                <script>
                    $(document).ready(function(e) {
                        //var window.eventBinded = false;
                
                        $(document).on('change', 'select[name=id_field]', function () {
                            console.log('shhhh');
                            
                            if (!alertShown) {
                                $.ajax({
                                        url: '/marketplace/dlteams/ajax/get_object_specific_field.php',
                                        type: 'POST',
                                        data: {
                                            id: " . $id . ",
                                            object: '" . "PluginDlteamsDataCatalog" . "_Item" . "',
                                            field: 'comment'
                                        },
                                        success: function (data) {
                                            // Handle the returned data here
                                            console.log(data);
                                            $('textarea[name=comment]').val(data);
                                        }
                            });
                                //eventBinded = true;
                            }
                        });
                    });
                </script>
                ";

        echo "</div>";

    }

    static function showCollectedDocuments(CommonDBTM $item, $withtemplate = 0)
    {
        $id = $item->fields['id'];
        $canedit = $item->can($id, UPDATE); // canedit booleen = true
        $rand = mt_rand(1, mt_getrandmax());
        global $DB;

        $iterator = self::getRequest($item);

        $number = count($iterator); // $number est le nombre de ligne à afficher (=nombre de documents reliés)
        $items_list = [];
        $used = [];

        while ($data = $iterator->next()) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($canedit) {
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . $item->getType() . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $item->getID() . "' />";
            echo "<input type='hidden' name='itemtype' value='" . Document::getType() . "' />";
            echo "<input type='hidden' name='transformdocument' value='" . true . "' />";
            echo "<input type='hidden' name='link_element' value='" . true . "' />";
            echo "<input type='hidden' name='entities_id' value='" . $item->fields['entities_id'] . "' />";

            /*echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th colspan='4'>" . __("Autres documents pour l'information des personnes concernées", 'dlteams') .
                "<br><i style='font-weight: normal'>" . __("Charte, politique, CGV, CGU, ...", 'dlteams') . "</i></th>";
            echo "</tr>";*/

            $title = "Autres documents utilisés ou pouvant illustrer ce traitement (publication, états ou rapports, ...) ";
			$subtitle = "fournis aux personnes concernées (Charte, politique, CGV, CGU, ...) ";
            $entitled = "Selectionner un document";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th colspan='4'>" . __($title, 'dlteams') . "<br><i style='font-weight: normal'>" . "</i></th>";
            //echo "<th colspan='1'></th></tr>";
            // echo "<tr style='height: 25px;'>"
			echo "</tr>";
            echo "<tr class='tab_bg_1'>";
            echo "<td class='right' style='text-wrap: nowrap;' width='35%'>" . __($entitled, 'dlteams') . "</td>";
            echo "<td style='display: flex;' class='left'>";
            Dropdown::show(Document::class, [
                'addicon' => Document::canCreate(),
                'name' => 'items_id_document',
                'used' => $used,
                'width' => "250px",
            ]);
            $field_id = Html::cleanId("dropdown_" . 'items_' . mt_rand());
            $item_link = getItemForItemtype(Document::class);

            echo '<div style="padding: 2px; border-radius:2px;" class="btn btn-outline-secondary"
                           title="' . __s('Add') . '" data-bs-toggle="modal" data-bs-target="#add_' . $field_id . '">'
                . Ajax::createIframeModalWindow('add_' . $field_id, $item_link->getFormURL(), ['display' => false])
                . "<span data-bs-toggle='tooltip'>
              <i class='fa-fw ti ti-plus'></i>
              <span class='sr-only'>" . __s('Add') . "</span>
                </span>"
                . '</div>';
            echo "</td>";
            echo "<td class='left'>";
            echo "</td>";
            echo "</tr>";

            echo "<tr class='tab_bg_1' style='display: none;' id='field-comment'>";
            echo "<td class='right' width='20%'>";
            echo __("Comment");
            echo "</td>";
            echo "<td style='display: flex;' class='left'>";
            echo "<textarea type='text' maxlength=600 rows='2' name='comment' style='width:85%;margin-top:4px' placeholder='Commentaire'></textarea>";
            echo "</td>";
            echo "<td class='left'>";
            echo "</td></tr>";

            echo "<tr class='tab_bg_1' style='display: none;' id='field-mandatory'>";
            echo "<td class='right' width='20%'>";
            echo __("Obligatoire");
            echo "</td>";
            echo "<td class='left'>";
            Dropdown::showYesNo("mandatory", 1);
            echo "</td>";
            echo "<td class='left'>";
            echo "</td></tr>";

            echo "<tr style='display: none' id='field-documentlink'>";
            echo "<td></td>";
            echo "<td colspan='2' class='left'>";
            echo "<input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Relier cet élément') . "\" class='submit'>";
            echo "</td></tr>";

            echo "</table>";
            Html::closeForm();
        }

        echo "<script>
                $(document).ready(function () {
            
                    $('select[name=items_id_document]').on('change', function () {
                        // alert($(this).val());
                        if ($(this).val() != '0') {
                            document.getElementById('field-comment').style.display = 'revert';
                            document.getElementById('field-mandatory').style.display = 'revert';
                            document.getElementById('field-documentlink').style.display = 'revert';
            
                        } else {
                            document.getElementById('field-comment').style.display = 'none';
                            document.getElementById('field-mandatory').style.display = 'none';
                            document.getElementById('field-documentlink').style.display = 'none';
                        }
                    });
                });
            </script>";

        $massive_action_processor = PluginDlteamsDocument_Item::class;
        if ($iterator) {
            echo "<div class='spaced'>";
            if ($canedit && $number) {
                Html::openMassiveActionsForm('mass' . $massive_action_processor . $rand);
                $massive_action_params = ['container' => 'mass' . $massive_action_processor . $rand,
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
                $header_top .= Html::getCheckAllAsCheckbox('mass' . $massive_action_processor . $rand);
                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . $massive_action_processor . $rand);
                $header_end .= "</th>";
            }

            $header_end .= "<th>" . __("Document", 'dlteams') . "</th>";
            $header_end .= "<th>" . __("Fichier") . "</th>";
            $header_end .= "<th>" . __("URL") . "</th>";
            $header_end .= "<th>" . __("Comment") . "</th>";
            $header_end .= "<th>" . __("Obligatoire") . "</th>";
            $header_end .= "</tr>";

            echo $header_begin . $header_top . $header_end;
            //foreach ($items_list as $data) {

            foreach ($iterator as $data) {
//                    get record_item fields
                    $record_item = new PluginDlteamsRecord_Item();
                    $record_item->getFromDBByCrit([
                        "itemtype" => Document::class,
                        "items_id" => $data["documents_id"],
                        "records_id" => $data["items_id"],
                        "comment" => $data["comment"],
                    ]);

                    echo "<tr class='tab_bg_1'>";
                    if ($canedit && $number) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox($massive_action_processor, $data['linkid']);
                        echo "</td>";
                    }
                    $link = $data['name'];
                    if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
                        $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
                    }

                    $name = "<a target='_blank' href=\"" . Document::getFormURLWithID($data['id']) . "\">" . $link . "</a>";
                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo ">" . $name . "</td>";

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo ">";
                    if ($data['filename']) {
                        echo "<a href='../../../front/document.send.php?docid=" . $data['id'] . "' target='_blank'>" . "voir" . "</a>";
                    } else {
                        echo "---";
                    }
                    echo "</td>";

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'") . ">";
                    if ($data['link']) {
                        echo "<a href='" . $data['link'] . "' target='_blank'>" . "voir" . "</a>";
                    } else {
                        echo "---";
                    }
                    echo "</td>";

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo " width='40%'>";
                    if ($data['comment']) {
                        echo $data['comment'];
                    } else {
                        echo "---";
                    }
                    echo "</td>";

                    echo "<td>";
                    echo isset($record_item->fields['document_mandatory']) && $record_item->fields['document_mandatory'] ? "Oui" : "Non";
                    echo "</td></tr>";
            }

            if ($iterator->count() > 10) {
                echo $header_begin . $header_bottom . $header_end;
            }
            echo "</table>";

            if ($canedit && $number > 10) {
                $massive_action_params['ontop'] = false;
                Html::showMassiveActions($massive_action_params);
            }
                Html::closeForm();

            echo "</div>";
        }
    }

    static function showPolicyform(CommonDBTM $item, $withtemplate = 0)
    {
        $id = $item->fields['id'];
        $canedit = $item->can($id, UPDATE); // canedit booleen = true
        $rand = mt_rand(1, mt_getrandmax());
        global $DB;

        $iterator = self::getPolicyFormRequest($item);
        $number = count($iterator); // $number est le nombre de ligne à afficher (=nombre de documents reliés)
        $items_list = [];
        $used = [];

        while ($data = $iterator->next()) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($canedit) {
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . $item->getType() . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $item->getID() . "' />";
            echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsPolicieForm::getType() . "' />";
            echo "<input type='hidden' name='link_element' value='" . true . "' />";
            echo "<input type='hidden' name='entities_id' value='" . $item->fields['entities_id'] . "' />";

            echo "<table class='tab_cadre_fixe'>";

            $title = "Jeux de données utilisés lors de ce traitement"; // PluginDlteamsPolicieForm::getTypeName();
			$subtitle = "Papier et numérique, il peut s'agir d'un formulaire, un contrat, une fiche logicielle, un fichier ...)";
            $entitled = "Sélectionner jeu de données ou document type";
			// echo "<tr class='tab_bg_2'><th>" . __($title, 'dlteams') . "<br><i style='font-weight: normal'>" . "</i></th>";
            // echo "<th colspan='2'></th></tr>";

            echo "<tr class='tab_bg_2'><th colspan='4'>" . __($title, 'dlteams') . "<br><i style='font-weight: normal'>" .
                __($subtitle, 'dlteams') . "</i></th>";
			echo "</tr>";

            echo "<td class='right' style='text-wrap: nowrap;' width='35%'>" . __($entitled, 'dlteams') . "</td>";
            echo "<td>";
			//echo "<td style='display: flex;' class='left'>";
            Dropdown::show(PluginDlteamsPolicieForm::class, [
                'addicon' => PluginDlteamsPolicieForm::canCreate(),
                'name' => 'items_id_policie',
                'used' => $used,
                'width' => "250px",
            ]);
            echo "</td>";
            //echo "<td class='left'>";
            //echo "</td></tr>";
			echo "</tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td class='right' style='text-wrap: nowrap;' width='35%'></td>";
            echo "<td style='display: flex;' class='left'>";
            echo "<div id='document_additional_fields'>";

            echo "</div>";
            echo "</td>";
            echo "<td class='left'>";
            echo "</td>";
            echo "</tr>";

            echo "<tr class='tab_bg_1' style='display: none;' id='field-policiemandatory'>";
            echo "<td class='right' width='20%'>";
            echo __("Obligatoire");
            echo "</td>";
            echo "<td class='left'>";
            Dropdown::showYesNo("mandatory", 1);
            echo "</td>";
            echo "<td class='left'>";
            echo "</td>";
            echo "</tr>";

            echo "<tr class='tab_bg_1' style='display: none;' id='field-policiecomment'>";
            echo "<td class='right' width='20%'>";
            echo __("Comment");
            echo "</td>";
            echo "<td style='display: flex;' class='left'>";
            echo "<textarea type='text' maxlength=600 rows='2' name='comment' style='width:85%;margin-top:4px' placeholder='Commentaire'></textarea>";
            echo "</td>";
            echo "<td class='left'>";
            echo "</td>";
            echo "</tr>";

            echo "<tr style='display: none' id='field-policieformlink'>";
            echo "<td>";
            echo "</td>";
            echo "<td colspan='2' class='left'>";
            echo "<input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Relier cet élément') . "\" class='submit'>";
            echo "</td>";
            echo "</tr>";

            echo "</table>";
            Html::closeForm();

            $script = "<script>";
            $script.="$(document).ready(function(){
            $('select[name=items_id_policie').change(function(e){            
                $.ajax({
                                url: '/marketplace/dlteams/ajax/get_object_specific_field.php',
                                type: 'POST',
                                data: {
                                    id: $(this).val(),
                                    object: '" . PluginDlteamsPolicieForm::class . "',
                                    field: [
                                        {
                                            name: 'link',
                                            type: 'link'
                                        },
                                        {
                                            name: 'filename',
                                            type: 'link',
                                            documentlink: true
                                        }
                                    ]
                                },
                                success: function (data) {
                                    $('#document_additional_fields').html(data);
                                   
                                }
                            }); 
//                               end ajax
            });
        });";
            $script .="</script>";

            echo $script;
        }

        echo "<script>
                $(document).ready(function () {
            
                    $('select[name=items_id_policie]').on('change', function () {
                        // alert($(this).val());
                        if ($(this).val() != '0') {
                            document.getElementById('field-policiecomment').style.display = 'revert';
                            document.getElementById('field-policiemandatory').style.display = 'revert';
                            document.getElementById('field-policieformlink').style.display = 'revert';
            
                        } else {
                            document.getElementById('field-policiecomment').style.display = 'none';
                            document.getElementById('field-policiemandatory').style.display = 'none';
                            document.getElementById('field-policieformlink').style.display = 'none';
                        }
                        //
                    });
            
                });
            
            </script>";

        $massive_action_processor = PluginDlteamsPolicieForm_Item::class;
        if ($iterator) {
            echo "<div class='spaced'>";
            if ($canedit && $number) {
                Html::openMassiveActionsForm('mass' . $massive_action_processor . $rand);
                $massive_action_params = ['container' => 'mass' . $massive_action_processor . $rand,
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
                $header_top .= Html::getCheckAllAsCheckbox('mass' . $massive_action_processor . $rand);
                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . $massive_action_processor . $rand);
                $header_end .= "</th>";
            }

            $header_end .= "<th>" . __("Nom") . "</th>";
            $header_end .= "<th>" . __("Fichier") . "</th>";
            $header_end .= "<th>" . __("URL") . "</th>";
            $header_end .= "<th>" . __("Comment") . "</th>";
            $header_end .= "<th>" . __("Obligatoire") . "</th>";
            $header_end .= "</tr>";

            echo $header_begin . $header_top . $header_end;

            //foreach ($items_list as $data) {
            // var_dump ($iterator);
            foreach ($iterator as $data) {


//                    get record_item fields
                    $record_item = new PluginDlteamsRecord_Item();
                    $record_item->getFromDBByCrit([
                        "itemtype" => PluginDlteamsPolicieForm::class,
                        "items_id" => $data["policieforms_id"],
                        "records_id" => $data["items_id"],
                        "comment" => addslashes($data["comment"]),
                    ]);

                    echo "<tr class='tab_bg_1'>";

                    if ($canedit && $number) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox($massive_action_processor, $data['linkid']);
                        echo "</td>";
                    }
                    $link = $data['name'];
                    if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
                        $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
                    }

                    $name = "<a target='_blank' href=\"" . PluginDlteamsPolicieForm::getFormURLWithID($data['id']) . "\">" . $link . "</a>";
                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo ">" . $name . "</td>";

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo ">";
                        $policieform = new PluginDlteamsPolicieForm();
                        $policieform->getFromDB($data["policieform_id"]);
                    if ($policieform->fields['documents_id']) {
                        echo "<a href='../../../front/document.send.php?docid=" . $policieform->fields['documents_id'] . "' target='_blank'>" . "voir" . "</a>";
                    } else {
                        echo "---";
                    }
                    echo "</td>";

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'") . ">";
                    $document = new Document();
                    $document->getFromDB($policieform->fields['documents_id']);
                    if (isset($document->fields['link']) && $document->fields['link']) {
                        echo "<a href='" . $document->fields['link'] . "' target='_blank'>" . "voir" . "</a>";
                    } else {
                        echo "---";
                    }
                    echo "</td>";

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo " width='40%'>";
                    if ($data['comment']) {
                        echo $data['comment'];
                    } else {
                        echo "---";
                    }
                    echo "</td>";

                    echo "<td>";
                    echo isset($record_item->fields['mandatory']) && $record_item->fields['mandatory'] ? "Oui" : "Non";
                    echo "</td>";

                    echo "</tr>";

            }


            if ($iterator->count() > 10) {
                echo $header_begin . $header_bottom . $header_end;
            }
            echo "</table>";

            if ($canedit && $number > 10) {
                $massive_action_params['ontop'] = false;
                Html::showMassiveActions($massive_action_params);
            }
                Html::closeForm();

            echo "</div>";
        }

    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }

    public static function getDataCatalogRequest(CommonDBTM $item)
    {

        $table_name = PluginDlteamsDataCatalog::getTable(); // si $item = DataCatalog, $table_name contiendra data_carriers
        $columnid_name = strtolower(str_replace("PluginDlteams", "", PluginDlteamsDataCatalog::getType())) . "s_id"; // $columnid_name contiendra users_id si $item = User
        global $DB;        //var_dump ($table_name, $columnid_name);
        $table_item_name = getTableForItemType(PluginDlteamsDataCatalog::class . "_Item");

        $query = [
            'SELECT' => [
                $table_item_name . '.id AS linkid',
                $table_item_name . '.itemtype AS itemtype',
                $table_item_name . '.items_id AS items_id',
                $table_item_name . '.comment AS comment',
                $table_item_name . '.*',
                $table_name . '.id AS id',
                $table_name . '.name AS name',
                $table_name . '.content AS content',
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

    public static function showTransmissionMethods(CommonGLPI $record)
    {
        $rand = mt_rand();
        echo "<style>
    .form-element {
        display: flex;
        flex-direction: column;
        gap: 1em;
    }
    .input-field select {
        width: 100%; /* Full width on small screens */
    }
    @media (max-width: 768px) {
        .form-element {
            font-size: 16px; /* Larger text on small screens */
        }
        .input-field select {
            height: 40px; /* Easier to interact with on mobile */
        }
    }
    </style>";

        echo "<div class='firstbloc'>";
        echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post' action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";
        $iden = $record->fields['id'];
        echo "<style>
        .responsive_inline_element {
            display: flex;
            flex-direction: column;
            gap: 20px; /* Vous pouvez ajuster l'espacement entre les éléments selon vos besoins */
        }
        
      
        @media (min-width: 768px) { /* Cela cible les écrans d'au moins 768 pixels de large */
            .responsive_inline_element {
                display: flex;
                flex-direction: row;
            }
        }
        </style>";
       echo "<div class='responsive_inline_element'>";
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr>";
        echo "<th colspan='3'>" . __("Transmission method", 'dlteams') . "</th>";
        echo "</tr>";

        echo "<tr>";
        echo "<td class='form-element'>";
        echo __('Quel canal ou méthode de collecte?', 'dlteams');
        global $DB;
        $iterator = $DB->request(["FROM" => PluginDlteamsTransmissionMethod::getTable(), "SELECT" => ["name", "id"]]);
        $list = [];
        foreach ($iterator as $data) {
            $list[$data["id"]] = $data["name"];
        }
        echo "<div class='input-field'>";
//        var_dump($record->fields["transmissionmethod"]);
//        die();
        $instID = $record->fields["id"];
        echo "<input type='hidden' name='record_id' value='$instID'>";
        Dropdown::showFromArray(
            "transmission_methods",
            $list,
            [   'values' => json_decode($record->fields["transmissionmethod"] ?? '{}'),
                'multiple' => true,
                'rand' => $rand,
                'width' => '100%',
            ]
        );
        echo "</div>";
        echo "</td>";
        echo "</table>";



        echo "<table class='tab_cadre_fixe'>";
        echo "<tr>";
        echo "<th colspan='3'>" . __("Support and collection media", 'dlteams') . "</th>";
        echo "</tr>";

        echo "<td class='form-element'>";
        echo __('Y a t-il un média ou support transmis ?', 'dlteams');
        $iterator = $DB->request(["FROM" => PluginDlteamsMediaSupport::getTable(), "SELECT" => ["name", "id"]]);
        $list = [];
        foreach ($iterator as $data) {
            $list[$data["id"]] = $data["name"];
        }
        echo "<div class='input-field'>";
        Dropdown::showFromArray(
            "support_methods",
            $list,
            [
                'values' => json_decode($record->fields["mediasupport"] ?? '{}'),
                'multiple' => true,
                'rand' => $rand,
                'width' => '100%',
            ]
        );
        echo "</div>";
        echo "</td>";
        echo "</table>";

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr>";
        echo "<th colspan='3'>" . __("SI Integration", 'dlteams') . "</th>";
        echo "</tr>";
        echo "<td class='form-element'>";
        echo __('Mode d\'enregistrement dans les catalogues de données', 'dlteams');
        $iterator = $DB->request(["FROM" => PluginDlteamsSIIntegration::getTable(), "SELECT" => ["name", "id"]]);
        $list = [];
        foreach ($iterator as $data) {
            $list[$data["id"]] = $data["name"];
        }
        echo "<div class='input-field'>";
        Dropdown::showFromArray(
            "si_integration",
            $list,
            [   'values' => json_decode($record->fields["siintegration"] ?? '{}'),
                'multiple' => true,
                'rand' => $rand,
                'width' => '100%',
            ]
        );
        echo "</div>";
        echo "</td>";
        echo "</tr>";

//        echo "<tr><td colspan='3'>";
//        echo "<input type='submit' name='save' value=\"" . _sx('button', 'Save') . "\" class='submit' style='margin-top:5px;'>";
//        echo "</td></tr>";

        echo "</table>";
       echo "</div>";

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr><td colspan='3'>";
        echo "<input type='submit' name='save' value=\"" . _sx('button', 'Save') . "\" class='submit' style='margin-top:5px;'>";
        echo "</td></tr>";

        echo "</table>";
        Html::closeForm();
        echo "</div>";
    }

    static function getRequest($record)
    {
        $query = [
            'SELECT' => [
                'glpi_documents_items.id AS linkid',
                'glpi_documents_items.documents_id AS documents_id',
                'glpi_documents_items.items_id AS items_id',
//                'glpi_plugin_dlteams_records_items.document_mandatory AS document_mandatory',
                'glpi_documents.id AS id',
                'glpi_documents.name AS name',
                'glpi_documents.filename AS filename',
                'glpi_documents.link AS link',
                'glpi_documents_items.comment AS comment',

            ],
            'FROM' => 'glpi_documents_items',
            'LEFT JOIN' => [
                'glpi_documents' => [
                    'FKEY' => [
                        'glpi_documents_items' => "documents_id",
                        'glpi_documents' => "id",
                    ]
                ],
            ],

            'ORDER' => [
                'name ASC'
                //'contenu ASC'
            ],
            'WHERE' => [
                'glpi_documents_items.items_id' => $record->fields['id'],
                'glpi_documents_items.itemtype' => PluginDlteamsRecord::class,
//                'glpi_plugin_dlteams_records_items.itemtype' => Document::class,
//                'glpi_plugin_dlteams_records_items.records_id' => $record->fields['id'],
            ]
        ];

        global $DB;
        $iterator = $DB->request($query);
//        var_dump($iterator->getSql());
//        die();
        return $iterator;
    }

    static function getPolicyFormRequest($record)
    {
        $query = [
            'SELECT' => [
                'glpi_plugin_dlteams_policieforms_items.id AS linkid',
                'glpi_plugin_dlteams_policieforms_items.policieforms_id AS policieforms_id',
                'glpi_plugin_dlteams_policieforms_items.items_id AS items_id',
//                'glpi_plugin_dlteams_records_items.document_mandatory AS document_mandatory',
                'glpi_plugin_dlteams_policieforms.id AS id',
                'glpi_plugin_dlteams_policieforms.id AS policieform_id',
                'glpi_plugin_dlteams_policieforms.name AS name',
                'glpi_plugin_dlteams_policieforms_items.comment AS comment',

            ],
            'FROM' => 'glpi_plugin_dlteams_policieforms_items',
            'LEFT JOIN' => [
                'glpi_plugin_dlteams_policieforms' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_policieforms_items' => "policieforms_id",
                        'glpi_plugin_dlteams_policieforms' => "id",
                    ]
                ],
            ],

            'ORDER' => [
                'name ASC'
                //'contenu ASC'
            ],
            'WHERE' => [
                'glpi_plugin_dlteams_policieforms_items.items_id' => $record->fields['id'],
                'glpi_plugin_dlteams_policieforms_items.itemtype' => PluginDlteamsRecord::class,
//                'glpi_plugin_dlteams_records_items.itemtype' => Document::class,
//                'glpi_plugin_dlteams_records_items.records_id' => $record->fields['id'],
            ]
        ];

        global $DB;
        return $DB->request($query);
    }

    public static function showCollectComment(CommonGLPI $record)
    {
        $rand = mt_rand();

        echo "<form name='datacarrieritem_form$rand' id='datacarrieritem_form$rand' method='post'
             action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";

        echo "<table class='tab_cadre_fixe'>";

        echo "<tr class='tab_bg_2'><th colspan='3'>" . "Compléments d'information sur la collecte et le classement de l'information" .
            "</th>";
        echo "</tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td class='left' style='text-wrap: nowrap;' width='20%'>";
        echo "Ajoutez des informations utiles à la description du processus de collecte";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td class='left' style='text-wrap: nowrap;' width='20%'>";
        echo "<input type='hidden' name='record_id' value='" . $record->fields["id"] . "' />";
        Html::textarea([
            'name' => 'collect_comment',
            'value' => $record->fields["collect_comment"],
            'enable_fileupload' => false,
            'enable_richtext' => true,
            'width' => '100%',
            'rows' => 4
        ]);
        echo "</td>";
        echo "</tr>";

        echo "<tr><td>";
        echo "<input type='submit' name='save_comment' value=\"" . _sx('button', 'Save') . "\" class='submit' style='margin-top:5px;'>";
        echo "</td></tr>";

        echo "</table>";

        Html::closeForm();
    }

}

?>
