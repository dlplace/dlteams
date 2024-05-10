<?php
use Glpi\Event\MassiveAction;
class PluginDlteamsMassiveAction extends MassiveAction
{
    public function doSpecificMassiveActions($input = [])
    {
        // Perform your action here
        // ...
		var_dump("Hell");
		die();
		
        // Return true if the action was successful, false otherwise
        return true;
    }
}