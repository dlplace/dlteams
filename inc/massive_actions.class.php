<?php



class PluginDlteamsMassiveAction extends CommonDBTM {
  
  function getSpecificMassiveActions($checkitem=NULL) {
	  return array(__CLASS__.MassiveAction::ACTION_BUTTON_NAME => __('Action name'));
   }
}