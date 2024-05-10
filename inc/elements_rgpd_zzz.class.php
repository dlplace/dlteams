<?php

class PluginDlteamsElementsRGPD extends CommonDBChild {

    function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
        switch ($item::getType()) {
            case Document::getType():
//                var_dump("sss");
//                die();
                return __('Tab from my plugin', 'dlteams');
                break;
        }
        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
        switch ($item::getType()) {
            case Computer::getType():
                //display form for computers
                self::displayTabContentForComputer($item);
                break;
            case Phone::getType():
                self::displayTabContentForPhone($item);
                break;
        }
        if ($item->getType() == 'ObjetDuCoeur') {
            $monplugin = new self();
            $ID = $item->getField('id');
            // j'affiche le formulaire
            $monplugin->nomDeLaFonctionQuiAfficheraLeContenuDeMonOnglet();
        }
        return true;
    }

    private static function displayTabContentForComputer(Computer $item) {
        //...
    }

    private static function displayTabContentForPhone(Phone $item) {
        //...
    }
}
