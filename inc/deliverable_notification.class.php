<?php

/*
 -------------------------------------------------------------------------
 DLTeams plugin for GLPI
 -------------------------------------------------------------------------
 LICENSE : This file is part of DLTeams Plugin.

 DLTeams Plugin is a GNU Free Copylefted software.
 It disallow others people than DLPlace developers to distribute, sell,
 or add additional requirements to this software.
 Though, a limited set of safe added requirements can be allowed, but
 for private or internal usage only ;  without even the implied warranty
 of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

 You should have received a copy of the GNU General Public License
 along with DLTeams Plugin. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
  @package   dlteams
  @author    DLPlace developers
  @copyright Copyright (c) 2022 DLPlace
  @inspired	 DPO register plugin (Karhel Tmarr) & gdprropa (Yild)
  @license   GPLv3+ http://www.gnu.org/licenses/gpl.txt
  @link      https://github.com/dlplace/dlteams
  @since     2021
 --------------------------------------------------------------------------
 */

class PluginDlteamsDeliverableNotification extends CommonDBTM{

    static function canCreate() {return true;}
    static function canView() {return true;}
    static function canUpdate() {return true;}
    static function canDelete() {return true;}
	static function canPurge() {return true;}
    function canCreateItem() {return true;}
    function canViewItem() {return true;}
    function canUpdateItem() {return true;}
    function canDeleteItem() {return true;}
    function canPurgeItem() {return true;}

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
//        $iterator = PluginDlteamsDocumentsRGPD::getRequest($item);
//        $nbitem = count($iterator);
        return static::createTabEntry(__('Notification', 'dlteams'), 0);

    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        global $CFG_GLPI;
        

        echo "<form name='form' method='POST' action=\"" . Toolbox::getItemTypeFormURL(__CLASS__) . "\">";

        echo "<div class='spaced' id='tabsbody'>";

        echo "<table class='tab_cadre_fixe' id='mainformtable'>";
        echo "<tbody>";
        echo "<tr class='headerRow'>";
        echo "<th colspan='3' class=''>" . __("Notification", 'dlteams') . "</th>";
        echo "</tr>";

//        $_config = PluginDlteamsCreatePDF::getDefaultPrintOptions();
//        $_config['report_type'] = $report_type;
//        PluginDlteamsCreatePDF::showDeliverableConfigElements($_config, $deliverable_id);


        echo "</table>";
        echo "</div>";
//        echo "<input type='hidden' name='report_type' value=\"" . $report_type . "\">";
//        echo "<input type='hidden' name='deliverable_id' value=\"" . $deliverable_id . "\">";
//        echo "<input type='hidden' name='items_id' value=\"" . $deliverable_id . "\">";

        echo "<input type='hidden' name='action' value=\"print\">";

        echo "<input type='hidden' name='guid_value' value='55'>";

        echo "<input type='submit' class='submit' name='send_notification' value='" . __("Envoyer", 'dlteams') . "' />";
        Html::closeForm();
    }
}
