<?php
include("../../../inc/includes.php");
Plugin::load('dlteams', true);

if (strpos($_SERVER['PHP_SELF'], "record_profiling_auto_dropdown.php")) {

   $AJAX_INCLUDE = 1;

   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (array_key_exists('profiling', $_POST) && $_POST['profiling']) {
   //PluginDlteamsRecord_PersonalAndDataCategory::showProfilingAuto($_POST);
   PluginDlteamsRecord_SecurityMeasure::showProfilingAuto($_POST);
} else {
   echo '';
}
