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
   Html::header(PluginDlteamsDataCatalog::getTypeName(2), '', 'dlteams', 'plugindlteamsmenu', 'datacatalog');
} else {
   Html::helpHeader(PluginDlteamsDataCatalog::getTypeName(2));
}

//check for ACLs
if (PluginDlteamsDataCatalog::canView()) {
   //View is granted: display the list.

   //Add page header
  /* Html::header(
      __('Personnes concernées', 'concernedperson'),
      $_SERVER['PHP_SELF'],
      'assets',
      'PluginDlteamsConcernedPerson',
      'PluginDlteamsConcernedPerson'
   );*/

   Search::show('PluginDlteamsDataCatalog');

   Html::footer();
} else {
   //View is not granted.
   Html::displayRightError();
}
