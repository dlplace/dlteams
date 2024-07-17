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

include("../../../inc/includes.php");

//if (Session::getCurrentInterface() == 'central') {
//   Html::header(PluginDlteamsPhysicalStorage::getTypeName(2), '', "helpdesk", "physicalstorage");
//   //Html::header(PluginDlteamsPhysicalStorage::getTypeName(2), '', 'dlteams', 'PluginDlteamsmenu', 'physicalstorage');
//} else {
//   //Html::helpHeader(PluginDlteamsPhysicalStorage::getTypeName(2));
//   Html::header(PluginDlteamsPhysicalStorage::getTypeName(2), '', "helpdesk", "physicalstorage");
//}

//Html::header(PluginDlteamsForm::getTypeName(2), '', "plugindlregistermenu","plugindlteamsform");
Html::header(PluginDlteamsForm::getTypeName(2), '', 'dlteams', 'PluginDlteamsmenu', 'plugindlteamsform');
//if (Session::getCurrentInterface() == 'central') {
//    Html::header(PluginDlteamsPhysicalStorage::getTypeName(2), '', 'dlteams', 'PluginDlteamsmenu', 'physicalstorage');
//} else {
//    Html::helpHeader(PluginDlteamsPhysicalStorage::getTypeName(0));
//}

//check for ACLs
if (PluginDlteamsForm::canView()) {
    //View is granted: display the list.
    Search::show(PluginDlteamsForm::class);
    Html::footer();
} else {
    //View is not granted.
    Html::displayRightError();
}
