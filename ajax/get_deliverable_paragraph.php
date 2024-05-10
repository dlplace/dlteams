<?php

include_once('../../../inc/includes.php');

global $DB;
$sections_id = $_POST["section_id"];
$deliverable_contents = $DB->request([
    "FROM" => PluginDlteamsDeliverable_Content::getTable(),
    'ORDER' => 'timeline_position DESC',
    "deliverable_sections_id" => $sections_id
]);
$item_list = [];
foreach ($deliverable_contents as $dc) {
    array_push($item_list, $dc);
}
$count = 0;
foreach ($item_list as $dc) {
    echo "<span id='bloc_" . $dc["id"] . "' style='background-color: #e2f2e3; border-color: #87aa8a;' class='mt-2 timeline-content left card paragraph-bloc'>
               <div class='card-body'>
               <div style='display: flex; justify-content: end; gap: 9px; align-items: center'>";

                    if($count === 0){
                        echo "<i class='fa fa-caret-down btn-moveparagraphdown'></i>
                        <i class='fa fa-trash btn-remove-paragraph' id='remove_paragraph" . $dc["id"] . "'></i>
                        <i class='fa fa-arrows'></i>";
                    }
                    elseif ($count == count($item_list)-1){
                        echo "<i class='fa fa-caret-up btn-moveparagraphup'></i>
                        
                        <i class='fa fa-trash btn-remove-paragraph' id='remove_paragraph" . $dc["id"] . "'></i>
                        <i class='fa fa-arrows'></i>";
                    }
                    else{
                        echo "<i class='fa fa-caret-up btn-moveparagraphup'></i>
                        <i class='fa fa-caret-down btn-moveparagraphdown'></i>
                        <i class='fa fa-trash btn-remove-paragraph' id='remove_paragraph" . $dc["id"] . "'></i>
                        <i class='fa fa-arrows'></i>";
                    }

                   echo "</div>
                    <div class='row flex-column bloc_content'>
                    <input type='hidden' name='id_field[]' value='" . $dc["id"] . "'>
                        <div class='form-field col-12  mb-2'>
                           <label class='col-form-label ' for=''>
                              Titre
                           </label>
                           <div class=' field-container'>
                               <input type='text' class='form-control' name='name[]' value='" . $dc["name"] . "' maxlength='255'>
                           </div>
                       </div>

                       <div class='form-field col-12 col-12 itil-textarea-content mb-2'>
                           <label class='col-form-label'>
                              Commentaire
                           </label>

                          <div class='field-container'>
                               <textarea class='form-control' name='comment[]' rows='3' style='width: 100%;' aria-hidden='true'>" . $dc["comment"] . "</textarea>
                          </div>
                       </div>

                       <div class='form-field col-12 col-12 itil-textarea-content mb-2'>
                           <label class='col-form-label'>
                              Contenu
                           </label>

                          <div class='field-container'>";

                    $cols = 100;
                    $rows = 60;
                    Html::textarea(['name' => 'content[]',
                        'value' => $dc["content"],
                        'enable_fileupload' => false,
                        'enable_richtext' => true,
                        'cols' => $cols,
                        'rows' => $rows
                    ]);
                    echo "</div>
                       </div>

                    </div>
               </div>

               <div style='display: flex; flex-direction: row; justify-content: end; margin-right: 20px; margin-bottom: 16px; gap: 5px;'>
                    <button type='button' class='btn btn-danger' id='remove_paragraph" . $dc["id"] . "' name='tabnum'>Supprimer le paragraphe</button>
                </div>
            </span>
                ";

    $count++;
}
