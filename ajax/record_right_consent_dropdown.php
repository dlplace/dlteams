<?php
include("../../../inc/includes.php");
Plugin::load('dlteams', true);

if (strpos($_SERVER['PHP_SELF'], "record_right_consent_dropdown.php")) {

   $AJAX_INCLUDE = 1;

   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (array_key_exists('consent_type', $_POST)) {

   $record  = new PluginDlteamsRecord();
   $record->check($_POST['plugin_dlteams_records_id'], UPDATE);
   $record->getFromDB($_POST['plugin_dlteams_records_id']);

    $_POST['consent_explicit'] = $record->fields['consent_explicit'];
   PluginDlteamsRecord_Rights::showConsent($record, $_POST);
}

