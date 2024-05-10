<?php

include_once('../../../inc/includes.php');
$cols = 100;
$rows = 60;

$dc = new PluginDlteamsDeliverable_Content();
$dc->getFromDB($_POST["content_id"]);
$rand = mt_rand();
echo "<form name='deliverable_form$rand' id='update_deliverablecontent_form$rand' method='post'>";
echo "<div class='row flex-column'>
                    <input type='hidden' name='id_field' value='" . $dc->fields['id'] . "'>
                        <div class='form-field col-12  mb-2'>
                           <label class='col-form-label ' for=''>
                              Titre
                           </label>
                           <div class=' field-container'>
                               <input type='text' class='form-control' required name='name' value='" . htmlspecialchars($dc->fields['name']) . "' maxlength='255'>
                           </div>
                       </div>

                       <div class='form-field col-12 col-12 itil-textarea-content mb-2'>
                           <label class='col-form-label'>
                              Commentaire
                           </label>

                          <div class='field-container'>
                               <textarea class='form-control' name='comment' rows='3' style='width: 100%;' aria-hidden='true'>" . $dc->fields['comment'] . "</textarea>
                          </div>
                       </div>

                       <div class='form-field col-12 col-12 itil-textarea-content mb-2'>
                           <label class='col-form-label'>
                              Contenu
                           </label>

                          <div class='field-container'>";
                            $cols = 100;
                            $rows = 60;
                            Html::textarea(['name' => 'content',
                                'value' => $dc->fields['content'],
                                'enable_fileupload' => false,
                                'enable_richtext' => true,
                                'cols' => $cols,
                                'rows' => $rows
                            ]);
                        echo "</div>".
                            "</div>".
                        "</div>";

            echo "<div style='background-color: white; box-shadow: #0a0a0a; padding: 20px; width: 100%; display: flex; gap: 10px; justify-content: end'> ".
                        "<button class='btn btn-primary' type='submit'>Enregistrer</button>
                       </div></div>";
echo "</form>";

echo "<script>
        $('#update_deliverablecontent_form$rand').off('submit').submit(function(event) {
          event.preventDefault();
          tinyMCE.triggerSave();
          $.ajax({
                    url: '/marketplace/dlteams/ajax/update_deliverable_paragraph.php',
                    type: 'POST',
                    data: $(this).serializeArray(),
                    success: function (html) {
                        $('#update_deliverablecontent_form$rand').closest('div.modal').modal('hide');
                        displayAjaxMessageAfterRedirect();
                        $('#document_bloc').html(html);
                        $('html, body').animate({scrollTop: $(document).height()}, 1000);
                    }
                });
        });
      </script>";
