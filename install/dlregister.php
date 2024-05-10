<?php

class PluginDlteams extends Plugin {
  // ... other code ...
  
   public static function defineMassiveActions() {
    return [
      'PluginDlteamsRecord' . MassiveAction::CLASS_ACTION_SEPARATOR . 'MyActionssssssssss' => __('My Actiony sllll', 'dlteams'),
    ];
  }
  
  public function myMassiveActionSubmit() {
   // Your custom logic for submission here
   // ...
}

  public static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
    switch ($ma->getAction()) {
      case 'MyActionssssssssss':
	  var_dump("cc");
	  die();
        // Do something when the action is triggered
        break;
    }
    return;
  }
}