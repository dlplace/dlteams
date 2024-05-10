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

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class PluginDlteamsDeliverableSections extends CommonDBTM
{

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $nbdoc = $nbitem = 0;
        switch ($item->getType()) {
            case 'PluginDlteamsDeliverable':
                global $DB;
                $allchapter = $DB->request(PluginDlteamsDeliverableSections::getTable(), ['ORDER' => ['timeline_position ASC'], 'deliverables_id' => $item->fields["id"]]);


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
        $allchapter = $DB->request(PluginDlteamsDeliverableSections::getTable(), ['ORDER' => ['timeline_position ASC'], 'deliverables_id' => $item->fields["id"]]);

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
        $allchapter = $DB->request(PluginDlteamsDeliverableSections::getTable(), ['ORDER' => ['timeline_position DESC'], 'deliverables_id' => $item->fields["id"]]);

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
        // load tinymce lib
        Html::requireJs('tinymce');
        $themepath = 'https://dev.dlteams.app/public/lib/tinymce/skins/ui/oxide-dark/';
        $javascriptCode = "<script> window.themepath = '{$themepath}'; </script>";

        // Exécuter le code JavaScript (par exemple, l'afficher dans le corps de la page)
        echo $javascriptCode;
        $_SESSION['glpi_js_toload']['tinymce'][] = GLPI_ROOT . '/public/lib/tinymce.js';
        $_SESSION['glpi_js_toload']['tinymce'][] = GLPI_ROOT . '/js/RichText/UserMention.js';
        $_SESSION['glpi_js_toload']['tinymce'][] = GLPI_ROOT . '/js/RichText/ContentTemplatesParameters.js';
        $instID = $item->fields['id'];
        if (!$item->can($instID, READ)) {
            return false;
        }
        $canedit = $item->can($instID, UPDATE);
        $tab = new self();
        $tab->getFromDB($tabnum);

        if($canedit){
            echo "<form method='post' action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'4>";
            echo "<input type='hidden' name='tabnum' value='" . $tabnum . "' />";
            echo "<input type='hidden' name='deliverables_id' value='" . $instID . "' />";
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

            echo "<input type='button' class='submit' name='addparagraphe_btn' id='addparagraphe_btn' value='" . __("Ajouter un paragraphe", 'dlteams') . "' />";
            echo "</div>";

            echo "<div class='col-12 col-sm content-block' style='margin-bottom: 125px;'>
            
            <span style='background-color: #f2f0e2; border-color: #aa9d87;' class='mt-2 timeline-content left card'>
               <div class='card-body'>
                    
                    <div class='row flex-column'>
                        <div class='form-field col-12  mb-2'>
                           <label class='col-form-label ' for='name_567414212'>
                              Titre
                           </label>
                           <div class='field-container'>
                               <input type='text' class='form-control ' name='name[]' value='' maxlength='255'>
                           </div>
                       </div>
                       
                       <div class='form-field col-12 col-12 itil-textarea-content mb-2'>
                           <label class='col-form-label'>
                              Commentaire
                           </label>
                    
                          <div class='field-container'>
                               <textarea class='form-control' name='comment[]' rows='3' style='width: 100%;' aria-hidden='true'></textarea>
                          </div>         
                       </div>

                       <div class='form-field col-12 col-12 itil-textarea-content mb-2'>
                           <label class='col-form-label'>
                              Contenu
                           </label>
                    
                          <div class='field-container'>
                               <textarea class='form-control' name='content[]' rows='3' style='width: 100%;' aria-hidden='true'></textarea>
                          </div>         
                       </div>
                       
                    </div>
               </div>
            </span>
            
            <div style='display: flex; flex-direction: column; gap: 7px;' class='sortable' id='document_bloc'></div>
            
            
           <div style='background-color: white; box-shadow: #0a0a0a; padding: 20px; position: fixed; bottom: 0; width: 100%; display: flex;'> 
            <button class='btn btn-primary' type='submit'>Enregistrer</button>
           </div>
            
         </div>
        ";

            Html::closeForm();
        }

//        Html::initEditorSystem("uuu");

//        global $CFG_GLPI;
//
//
//        $rand = mt_rand();
//        $instID = $item->fields["id"];
//
//        echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
//            action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
//        echo "<input type='hidden' name='update' value='true'>";
//
//        echo "<table width='100%'><tr>";
//        echo "<td>";
//        echo __('Tab name');
//        echo "</td><tr>";
//        echo "<td>" . Html::input('name', ['value' => "", 'size' => 40, 'id' => 'tab_name']);
//        echo "</td>";
//
//        echo "</tr>";
//
//        echo "<tr>";
//        echo "<td>";
//        echo "<br/>";
//        echo __('Comments');
//        echo "<br/>";
//        Html::textarea(['name' => 'comment', 'value' => "", 'cols' => 10, 'rows' => 5]);
//        echo "</td>";
//        echo "</tr>";
//
//        echo "<tr>";
//        echo "<td>";
//        echo "<br/>";
//        echo __('Content');

//        $cols = 100;
//        $rows = 60;
//        Html::textarea(['name' => 'content',
//            'value' => "", //\Glpi\RichText\RichText::getSafeHtml($this->fields['content'], true),
//            'enable_fileupload' => false,
//            'enable_richtext' => true,
//            'cols' => $cols,
//            'rows' => $rows
//        ]);
//        echo "</td>";
//        echo "</tr>";
//
//        echo "<tr>";
//        echo "<td>" .
//            Html::submit(__('Save'), [
//                'name' => 'massiveaction',
//                'class' => 'btn btn-primary',
//            ]) . "</td>";
//        echo "</tr>";
//        echo "</table>";
//        Html::closeForm();
//

//        echo "<tr class='tab_bg_1'><td>" . __('Name') . "</td>";
//        echo "<td>";
////        Html::autocompletionTextField($this, "name");
//        echo "</td>";
//
//        echo "<td rowspan='1'>" . __('Comments') . "</td>";
//        $cols = 100;
//        $rows = 60;
//        echo "<td rowspan='1'>
//               <textarea cols='45' rows='2' name='comment' id='field_name' >";
//        echo "</textarea></td>";
//
//
//        echo "</tr>\n";
//        echo "<tr>";
//        echo "<td colspan='2'>";
//        echo __('Content');
//        echo "<br/>";
//        echo "<br/>";
//        Html::textarea(['name' => 'content',
//            'value' => "", //\Glpi\RichText\RichText::getSafeHtml($this->fields['content'], true),
//            'enable_fileupload' => false,
//            'enable_richtext' => true,
//            'cols' => $cols,
//            'rows' => $rows
//        ]);
//        echo "</td>";
//        echo "</tr>";
        return parent::displayTabContentForItem($item, $tabnum, $withtemplate); // TODO: Change the autogenerated stub
    }

    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        var_dump("mopiii");
        die();
        return parent::showMassiveActionsSubForm($ma); // TODO: Change the autogenerated stub
    }

    public static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        var_dump("hhh");
        die();
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids); // TODO: Change the autogenerated stub
    }

    public function prepareTabContentForm(){

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
        $('.sortable').sortable({
            'axis': 'y',
            'cursor': 'move',
            'placeholder': 'highlight'
        });

        function moveItemUp($item) {
            var currentIndex = $item.index();
            if (currentIndex > 0) {
                var $sortableList = $item.parent();
                var items = $sortableList.sortable("option", "items");
                var $prevItem = items.eq(currentIndex - 1);
                $item.insertAfter($prevItem);
                $sortableList.sortable("cancel");
            }
        }

        function uniqid(prefix = '', random = false) {
            const sec = Date.now() * 1000 + Math.random() * 1000;
            const id = sec.toString(16).replace(/\./g, '').padEnd(14, '0');
            return `${prefix}${id}${random ? `.${Math.trunc(Math.random() * 100000000)}`:''}`;
        }

        $('input[id=addparagraphe_btn]').on('click', function () {
            const str = uniqid();
            const bloc_selector = '#bloc_'+str;
            const selector_remove_paragraph = '#remove_paragraph'+str;
            const selector_paragraph_textarea = '#textarea'+str;

           const html = `
           <span id='bloc_`+str+`' style='background-color: #e2f2e3; border-color: #87aa8a;' class='mt-2 timeline-content left card'>
               <div class='card-body'>
                    <div class='row flex-column'>
                    <div style='display: flex; justify-content: end'>
                        <i class='fa fa-arrows'></i>
                    </div>
                        <div class='form-field col-12  mb-2'>
                           <label class='col-form-label ' for='name_567414212'>
                              Titre
                           </label>
                           <div class=' field-container'>
                               <input type='text' class='form-control ' name='name[]' value='' maxlength='255'>
                           </div>
                       </div>

                       <div class='form-field col-12 col-12 itil-textarea-content mb-2'>
                           <label class='col-form-label'>
                              Commentaire
                           </label>

                          <div class='field-container'>
                               <textarea class='form-control' name='comment[]' rows='3' style='width: 100%;' aria-hidden='true'></textarea>
                          </div>
                       </div>

                       <div class='form-field col-12 col-12 itil-textarea-content mb-2'>
                           <label class='col-form-label'>
                              Contenu
                           </label>

                          <div class='field-container'>
                               <textarea class='form-control' name='content[]' id='textarea`+str+`' rows='3' style='width: 100%;' aria-hidden='true'></textarea>
                          </div>
                       </div>

                    </div>
               </div>

               <div style="display: flex; flex-direction: row; justify-content: end; margin-right: 20px; margin-bottom: 16px; gap: 5px;">
                    <button class='btn btn-danger' id='remove_paragraph`+str+`' name='tabnum'>Supprimer le paragraphe</button>
                </div>
            </span>
           `;

           $('#document_bloc').append(html);

           $(selector_remove_paragraph).click(function () {
               if(window.confirm('Êtes vous sur de retirer ce paragraphe?')){
                   $(bloc_selector).remove();
               }
           });


            // tinyMCE.init(Object.assign({
            //     link_default_target: '_blank',
            //     branding: false,
            //     selector: selector_paragraph_textarea,
            //     text_patterns: false,
            //
            //     plugins: [
            //         'autoresize',
            //         'code',
            //         'directionality',
            //         'fullscreen',
            //         'link',
            //         'lists',
            //         'quickbars',
            //         'searchreplace',
            //         'table',
            //     ],
            //
            //     theme: window.themepath,
            //     // Appearance
            //     body_class: 'rich_text_container',
            //
            //
            //     min_height: 150,
            //     resize: true,
            //
            //     // disable path indicator in bottom bar
            //     elementpath: false,
            //
            //     // inline toolbar configuration
            //     menubar: false,
            //     toolbar: 'styles | bold italic | forecolor backcolor | bullist numlist outdent indent | emoticons table link image | code fullscreen',
            //     quickbars_insert_toolbar: 'emoticons quicktable quickimage quicklink | bullist numlist | outdent indent ',
            //     quickbars_selection_toolbar: 'bold italic | styles | forecolor backcolor ',
            //     contextmenu: 'copy paste | emoticons table image link | undo redo | code fullscreen',
            //
            //     // Content settings
            //     entity_encoding: 'raw',
            //     relative_urls: false,
            //     remove_script_host: false,
            //
            //     // Misc options
            //     browser_spellcheck: true,
            //
            //     setup: function(editor) {
            //         // "required" state handling
            //         if ($(selector_paragraph_textarea).attr('required') == 'required') {
            //             $(selector_paragraph_textarea).removeAttr('required'); // Necessary to bypass browser validation
            //
            //             editor.on('submit', function (e) {
            //                 if ($(selector_paragraph_textarea).val() == '') {
            //                     const field = $(selector_paragraph_textarea).closest('.form-field').find('label').text().replace('*', '').trim();
            //                     alert(__('The %s field is mandatory').replace('%s', field));
            //                     e.preventDefault();
            //
            //                     // Prevent other events to run
            //                     // Needed to not break single submit forms
            //                     e.stopPropagation();
            //                 }
            //             });
            //             editor.on('keyup', function (e) {
            //                 editor.save();
            //                 if ($(selector_paragraph_textarea).val() == '') {
            //                     $(editor.container).addClass('required');
            //                 } else {
            //                     $(editor.container).removeClass('required');
            //                 }
            //             });
            //             editor.on('init', function (e) {
            //                 if (strip_tags($(selector_paragraph_textarea).val()) == '') {
            //                     $(editor.container).addClass('required');
            //                 }
            //             });
            //             editor.on('paste', function (e) {
            //                 // Remove required on paste event
            //                 // This is only needed when pasting with right click (context menu)
            //                 // Pasting with Ctrl+V is already handled by keyup event above
            //                 $(editor.container).removeClass('required');
            //             });
            //         }
            //
            //         editor.on('Change', function (e) {
            //             // Nothing fancy here. Since this is only used for tracking unsaved changes,
            //             // we want to keep the logic in common.js with the other form input events.
            //             onTinyMCEChange(e);
            //         });
            //         // ctrl + enter submit the parent form
            //         editor.addShortcut('ctrl+13', 'submit', function() {
            //             editor.save();
            //             submitparentForm($(selector_paragraph_textarea));
            //         });
            //     }
            // }));
        });
    });
</script>
