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

$record = new PluginDlteamsVehicle();

if (isset($_POST['add'])) {

   $record->check(-1, CREATE, $_POST);
    if(!$_POST["buyingdate"])
        $_POST["buyingdate"] = null;
    if(!$_POST["rentalamount"])
        $_POST["rentalamount"] = 0;
    if(!$_POST["maintenance"])
        $_POST["maintenance"] = 0;
    if(!$_POST["lastrental"])
        $_POST["lastrental"] = 0;
    if(!$_POST["firstrental"])
        $_POST["firstrental"] = 0;
    if(!$_POST["guarantee"])
        $_POST["guarantee"] = 0;
    if(!$_POST["rentalamount"])
        $_POST["rentalamount"] = 0;
    if(!$_POST["nb"])
        $_POST["nb"] = 1;
    if(!$_POST["taxbenefit"])
        $_POST["taxbenefit"] = 1;
    if(!$_POST["otherbenefit"])
        $_POST["otherbenefit"] = 1;

   $id = $record->add($_POST);
   Html::redirect($record->getFormURLWithID($id));

}
else if (isset($_POST['update'])) {

    if(!$_POST["buyingdate"])
        $_POST["buyingdate"] = null;
    if(!$_POST["rentalamount"])
        $_POST["rentalamount"] = 0;
    if(!$_POST["maintenance"])
        $_POST["maintenance"] = 0;
    if(!$_POST["lastrental"])
        $_POST["lastrental"] = 0;
    if(!$_POST["firstrental"])
        $_POST["firstrental"] = 0;
    if(!$_POST["guarantee"])
        $_POST["guarantee"] = 0;
    if(!$_POST["rentalamount"])
        $_POST["rentalamount"] = 0;
    if(!$_POST["nb"])
        $_POST["nb"] = 1;
    if(!$_POST["taxbenefit"])
        $_POST["taxbenefit"] = 1;
    if(!$_POST["otherbenefit"])
        $_POST["otherbenefit"] = 1;
   $record->check($_POST['id'], UPDATE);
   $record->update($_POST);
   Html::back();

}
else if (isset($_POST['delete'])) {

   $record->check($_POST['id'], DELETE);
   $record->delete($_POST);
   $record->redirectToList();

}
else if (isset($_POST['purge'])) {

   $record->check($_POST['id'], PURGE);
   $record->delete($_POST, true);
   $record->redirectToList();

} else {

   $record->checkGlobal(READ);

   if (Session::getCurrentInterface() == 'central') {
       Html::header(PluginDlteamsVehicle::getTypeName(2), '', "actifs","plugindlteamsvehicle");
   } else {
      Html::helpHeader(PluginDlteamsVehicle::getTypeName(0));
   }

   $record->display(['id' => $_GET['id']]);

   if (Session::getCurrentInterface() == 'central') {
      Html::footer();
   } else {
      Html::helpFooter();
   }

}

