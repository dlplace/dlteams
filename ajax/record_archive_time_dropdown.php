<?php
include("../../../inc/includes.php");
Plugin::load('dlteams', true);

if (strpos($_SERVER['PHP_SELF'], "record_archive_time_dropdown.php")) {

   $AJAX_INCLUDE = 1;

   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (array_key_exists('archive_required', $_POST) && $_POST['archive_required']) {
   PluginDlteamsRecord_LegalBasisAct::showArchiveConservationTime($_POST, $_POST['rand']);
} else {
   echo '';
}
