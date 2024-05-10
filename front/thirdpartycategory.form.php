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

if (!isset($_GET['id'])) {
   $_GET['id'] = "";
}

$record = new PluginDlteamsThirdPartyCategory();

if (isset($_POST['add'])) {

   $record->check(-1, CREATE, $_POST);
   $id = $record->add($_POST);
   Html::redirect($record->getFormURLWithID($id));

} else if (isset($_POST['update'])) {

   $record->check($_POST['id'], UPDATE);
   $record->update($_POST);
   Html::back();

} else if (isset($_POST['delete'])) {

   $record->check($_POST['id'], DELETE);
   $record->delete($_POST);
   $record->redirectToList();

} else if (isset($_POST['purge'])) {

   $record->check($_POST['id'], PURGE);
   $record->delete($_POST, true);
   $record->redirectToList();

} else {

   $record->checkGlobal(READ);

   if (Session::getCurrentInterface() == 'central') {
     Html::header(PluginDlteamsThirdPartyCategory::getTypeName(2), '', 'dlteams', 'plugindlteamsmenu', 'thirdpartycategory');
   } else {
      Html::helpHeader(PluginDlteamsThirdPartyCategory::getTypeName(0));
   }

   $record->display(['id' => $_GET['id']]);

   if (Session::getCurrentInterface() == 'central') {
      Html::footer();
   } else {
      Html::helpFooter();
   }

}