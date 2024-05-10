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

if (Session::getCurrentInterface() == 'central') {
   Html::header(PluginDlteamsAppliance::getTypeName(2), '', 'dlteams', 'PluginDlteamsmenu', 'appliance');
} else {
   Html::helpHeader(PluginDlteamsAppliance::getTypeName(2));
}

//check for ACLs
if (PluginDlteamsAppliance::canView()) {
   //View is granted: display the list.

   //Add page header
  /* Html::header(
      __('Personnes concernées', 'concernedperson'),
      $_SERVER['PHP_SELF'],
      'assets',
      'PluginDlteamsAccountKey',
      'PluginDlteamsAccountKey'
   );*/

   Search::show('PluginDlteamsAppliance');

   Html::footer();
} else {
   //View is not granted.
   Html::displayRightError();
}
