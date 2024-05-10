<?php

include_once('../../../inc/includes.php');
global $DB;
$rand = mt_rand();
echo "<form name='timeline_move_tickettask_form$rand' id='update_timeline_move_tickettask_form$rand' action='/marketplace/dlteams/front/move_timeline_item.form.php' method='post'>";
echo "<input type='hidden' name='tickettasks_id' value='".$_GET["tickettasks_id"]."'>";
global $CFG_GLPI;
Ticket::dropdown([
    'addicon' => Ticket::canCreate(),
    'name' => 'tickets_id',
    'width' => '300px',
    'value' => "",
    'url' => $CFG_GLPI['root_doc'] . "/marketplace/dlteams/ajax/getDropdownValue.php"
]);
echo '<br /><br />' . Html::submit(_x('button', 'Post'), ['name' => 'update']);

Html::closeForm();

