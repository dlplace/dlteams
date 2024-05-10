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


class PluginDlteamsDeliverable_Section extends CommonDBTM
{

    public static function showAllDeliverableParagraph($sections_id)
    {
        global $DB;
        $deliverable_contents = $DB->request([
            "FROM" => PluginDlteamsDeliverable_Content::getTable(),
            'ORDER' => 'timeline_position ASC',
            "deliverable_sections_id" => $sections_id
        ]);
        $item_list = [];
        foreach ($deliverable_contents as $dc) {
            array_push($item_list, $dc);
        }

        $count = 0;

        foreach ($item_list as $dc) {
            echo "

                    <span id='bloc_" . $dc["id"] . "' style='background-color: #dbdbdb; border-color: #87aa8a;' class='mt-2 timeline-content left card paragraph-bloc'>
               <div class='card-body'>
                    <div style='display: flex; justify-content: end; gap: 9px; align-items: center'>";
            if ($count === 0) {
                echo "<i class='fa fa-caret-down btn-moveparagraphdown'></i>
                        <i class='fa fa-trash btn-remove-paragraph' id='remove_paragraph" . $dc["id"] . "'></i>
                        <i class='fa fa-arrows'></i>";
            } elseif ($count == count($item_list) - 1) {
                echo "<i class='fa fa-caret-up btn-moveparagraphup'></i>

                        <i class='fa fa-trash btn-remove-paragraph' id='remove_paragraph" . $dc["id"] . "'></i>
                        <i class='fa fa-arrows'></i>";
            } else {
                echo "<i class='fa fa-caret-up btn-moveparagraphup'></i>
                        <i class='fa fa-caret-down btn-moveparagraphdown'></i>
                        <i class='fa fa-trash btn-remove-paragraph' id='remove_paragraph" . $dc["id"] . "'></i>
                        <i class='fa fa-arrows'></i>";
            }
            echo "</div>
                    <div class='row flex-column bloc_content'>
                    <input type='hidden' name='id_field[]' value='" . $dc["id"] . "'>
                        <h3><a href='#' id='title_" . $dc["id"] . "' style='color: royalblue; text-decoration: underline;' class='paragraph-title'>" . $dc["name"] . "</a></h3>
                        <p style='margin-bottom: 0px; display: flex; gap: 5px; align-items: center;' class='text-sm'>";
            echo "<i class='fa fa-commenting'></i>";
            echo substr($dc["comment"], 0, 20) . '...';

            echo "</p>";

            echo "

                    </div>
               </div>

            </span>
                ";
            $count++;

        }
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $nbdoc = $nbitem = 0;
        switch ($item->getType()) {
            case 'PluginDlteamsDeliverable':
                global $DB;
                $allchapter = $DB->request(PluginDlteamsDeliverable_Section::getTable(), ['ORDER' => ['timeline_position ASC'], 'deliverables_id' => $item->fields["id"]]);


                $ong = [];
                $onglets = [];
                if ($allchapter) {
                    foreach ($allchapter as $index => $chapter) {

                        $ong[$index] = self::createTabEntry(_n(
                            $chapter['tab_name'],
                            $chapter['tab_name'], // TODO: replace with plural name
                            Session::getPluralNumber()
                        ), $nbdoc);
                    }
                }

                return $ong;
        }
    }

    private static function isFirstTab($tabnum, CommonGLPI $item)
    {
        global $DB;
        $allchapter = $DB->request(PluginDlteamsDeliverable_Section::getTable(), ['ORDER' => ['timeline_position ASC'], 'deliverables_id' => $item->fields["id"]]);

        $position = 0;
        foreach ($allchapter as $chapter) {
            if ($chapter["id"] == $tabnum && $position === 0)
                return true;

            $position++;
        }

        return false;
    }


    private static function isLastTab($tabnum, CommonGLPI $item)
    {
        global $DB;
        $allchapter = $DB->request(PluginDlteamsDeliverable_Section::getTable(), ['ORDER' => ['timeline_position DESC'], 'deliverables_id' => $item->fields["id"]]);

        $position = 0;
        foreach ($allchapter as $chapter) {
            if ($chapter["id"] == $tabnum && $position === 0)
                return true;

            $position++;
        }

        return false;
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        global $DB;
        $sections_id = $tabnum;

        $instID = $item->fields['id'];
        if (!$item->can($instID, READ)) {
            return false;
        }
        $canedit = $item->can($instID, UPDATE);
        $tab = new self();
        $tab->getFromDB($tabnum);

        if ($canedit) {
            echo "<form method='post' id='form_bloc' action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'4>";
            echo "<input type='hidden' name='tabnum' value='" . $tabnum . "' />";
            echo "<input type='hidden' name='deliverables_id' value='" . $instID . "' />";
            echo "<input type='hidden' name='deliverables_sections_id' value='" . $tabnum . "' />";
            echo "<div style='display: flex; gap: 5px;'>";
            if (!static::isFirstTab($tabnum, $item)) {
                echo "<input type='submit' class='submit' name='moveup_tab' value='" . __("Monter", 'dlteams') . "' />";
            }
            if (!static::isLastTab($tabnum, $item)) {
                echo "<input type='submit' class='submit' name='movedown_tab' value='" . __("Descendre", 'dlteams') . "' />";
            }
            echo "<input type='submit' class='submit' name='delete_tab' value='" . __("Supprimer", 'dlteams') . "' />";

            echo "<div style='padding: 4px; background-color: #cbcbcb; border-radius: 5px; display: flex; gap: 4px;'>";
            echo "<input type='text' placeholder='Nom de l\'onglet' class='formcontrol' name='tabname' value='" . $tab->fields["tab_name"] . "' />";
            echo "<input type='submit' class='submit' name='rename_tab' value='" . __("Renommer onglet", 'dlteams') . "' />";
            echo "</div>";

            echo "<input type='button' class='submit addparagraphe_btn' name='addparagraphe_btn_$sections_id'  value='" . __("Ajouter un paragraphe", 'dlteams') . "' />";
            echo "</div>";

            echo "<div class='col-12 col-sm content-block' style='margin-bottom: 125px;'>
            
            <span style='background-color: #f2f0e2; border-color: #aa9d87;' class='mt-2 timeline-content left card'>
               <div class='card-body'>
                    <input type='hidden' name='id_field[]' value=''>
                    <div class='row flex-column'>
                        <div class='form-field col-12  mb-2'>
                           <label class='col-form-label ' for='name_567414212'>
                              Titre
                           </label>
                           <div class='field-container'>
                               <input type='text' class='form-control ' name='name[]' value='" . htmlspecialchars($tab->fields["name"]??"") . "' maxlength='255'>
                           </div>
                       </div>
                       
                       <div class='form-field col-12 col-12 itil-textarea-content mb-2'>
                           <label class='col-form-label'>
                              Commentaire
                           </label>
                    
                          <div class='field-container'>
                               <textarea class='form-control' name='comment[]' rows='3' style='width: 100%;' aria-hidden='true'>" . $tab->fields["comment"] . "</textarea>
                          </div>         
                       </div>

                       <div class='form-field col-12 col-12 itil-textarea-content mb-2'>
                           <label class='col-form-label'>
                              Contenu
                           </label>
                    
                          <div class='field-container'>
                               <textarea class='form-control' name='content[]' rows='3' style='width: 100%;' aria-hidden='true'>" . $tab->fields["content"] . "</textarea>
                          </div>         
                       </div>
                       
                    </div>
               </div>
            </span>
            
            <div style='display: flex; flex-direction: column; gap: 7px;' class='sortable' id='document_bloc'>";


            static::showAllDeliverableParagraph($sections_id);

            echo "</div>    
           <div style='background-color: white; box-shadow: #0a0a0a; padding: 20px; position: fixed; bottom: 0; width: 100%; display: flex; gap: 10px;'> 
            <button class='btn btn-primary' type='submit' name='save_section'>Enregistrer</button>
           
            <input type='button' class='submit addparagraphe_btn' name='addparagraphe_btn_$sections_id' value='" . __("Ajouter un paragraphe", 'dlteams') . "' />
           </div>

         </div>
        ";

            Html::closeForm();
        }

        return parent::displayTabContentForItem($item, $tabnum, $withtemplate); // TODO: Change the autogenerated stub
    }

    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        return parent::showMassiveActionsSubForm($ma); // TODO: Change the autogenerated stub
    }

    public static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids); // TODO: Change the autogenerated stub
    }

    public function prepareTabContentForm()
    {

    }
}


?>

<style>
    .highlight {
        border: 1px solid red;
        font-weight: bold;
        font-size: 45px;
        background-color: #333333;
    }
</style>
<script>
    $(document).ready(function () {
        // $('.sortable').sortable({
        //     'axis': 'y',
        //     'cursor': 'move',
        //     'placeholder': 'highlight'
        // });


        function bindEvents() {
            $('.btn-moveparagraphup').on('click', function () {
                var parentElement = $(this).closest('.paragraph-bloc');
                var parentId = parentElement.attr('id').replace("bloc_", "");


                let formArray = $("#form_bloc").serializeArray();
                const idx = formArray.filter(x => x.name === "id_field[]" && x.value).map(x => x.value);
                const section_id = formArray.filter(x => x.name === "deliverables_sections_id")[0].value;
                const data = {
                    idx,
                    tomove_id: parentId,
                    direction: 'up',
                }

                $.ajax({
                    url: '/marketplace/dlteams/ajax/move_deliverable_paragraph.php',
                    type: 'POST',
                    data,
                    success: function (html) {
                        $('#document_bloc').html(html);
                    }
                });

            });

            $('.btn-moveparagraphdown').on('click', function () {
                var parentElement = $(this).closest('.paragraph-bloc');
                var parentId = parentElement.attr('id').replace("bloc_", "");


                let formArray = $("#form_bloc").serializeArray();
                const idx = formArray.filter(x => x.name === "id_field[]" && x.value).map(x => x.value);
                const section_id = formArray.filter(x => x.name === "deliverables_sections_id")[0].value;
                const data = {
                    idx,
                    tomove_id: parentId,
                    direction: 'down',
                }

                $.ajax({
                    url: '/marketplace/dlteams/ajax/move_deliverable_paragraph.php',
                    type: 'POST',
                    data,
                    success: function (html) {
                        $('#document_bloc').html(html);
                    }
                });

            });


            $('.btn-remove-paragraph').click(function () {
                var selector_remove_paragraph = $(this).attr('id');
                var id = selector_remove_paragraph.replace('remove_paragraph', '');
                var bloc_selector = '#bloc_' + id;

                if (window.confirm('Êtes vous sur de retirer ce paragraphe?')) {
                    // $(bloc_selector).remove();
                    $.ajax({
                        url: '/marketplace/dlteams/ajax/remove_deliverable_paragraph.php?content_id='+id,
                        type: 'GET',
                        success: function (html) {
                            $('#document_bloc').html(html);
                        }
                    });
                }

            });

            $('.addparagraphe_btn').off('click').on('click', function (event) {
                event.preventDefault();
                const section_id = $(this).attr('name').replace('addparagraphe_btn_', '');
                $.ajax({
                    url: '/marketplace/dlteams/ajax/add_deliverable_paragraph.php?section_id='+section_id,
                    type: 'GET',
                    success: function (html) {
                        $('#document_bloc').html(html);
                        $("html, body").animate({scrollTop: $(document).height()}, 1000);
                    }
                });

            });


            $(".paragraph-title").off('click').click(function () {
                var content_id = $(this).attr('id').replace("title_", "")
                glpi_ajax_dialog({
                    dialogclass: 'modal-xl',
                    bs_focus: false,
                    url: '/marketplace/dlteams/ajax/get_deliverable_paragraph_content.php',
                    params: {
                        content_id,
                    },
                    title: i18n.textdomain('dlteams').__('Edit paragraph', 'dlteams'),
                    close: function () {

                    },
                    fail: function () {
                        displayAjaxMessageAfterRedirect();
                    }
                });
            });
        }


        //    add event listener on each arrow
        bindEvents();

    });
</script>
<style>
    .btn-remove-paragraph,
    .btn-moveparagraphdown,
    .btn-moveparagraphup {
        cursor: pointer;
    }
</style>
