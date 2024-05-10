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

$process = new PluginDlteamsProcess();

if (isset($_POST['add'])) {

   $process->check(-1, CREATE, $_POST);
   $id = $process->add($_POST);
   Html::redirect($process->getFormURLWithID($id));

} else if (isset($_POST['update'])) {

   $process->check($_POST['id'], UPDATE);
   $process->update($_POST);
   Html::back();

} else if (isset($_POST['delete'])) {

   $process->check($_POST['id'], DELETE);
   $process->delete($_POST);
   $process->redirectToList();
} else {

    $process->checkGlobal(READ);

    if (Session::getCurrentInterface() == 'central') {
       Html::header(PluginDlteamsProcess::getTypeName(2), '', 'dlteams', 'plugindlteamsmenu', 'process');
    } else {
        Html::helpHeader(PluginDlteamsProcess::getTypeName(0));
    }

    $process->display(['id' => $_GET['id']]);

    if (Session::getCurrentInterface() == 'central') {
        Html::footer();
    } else {
        Html::helpFooter();
    }
}

