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

$trainingcertification = new PluginDlteamsTrainingCertification();

if (isset($_POST['add'])) {

   $trainingcertification->check(-1, CREATE, $_POST);
   $id = $trainingcertification->add($_POST);
   Html::redirect($trainingcertification->getFormURLWithID($id));

} else if (isset($_POST['update'])) {

   $trainingcertification->check($_POST['id'], UPDATE);
   $trainingcertification->update($_POST);
   Html::back();

} else if (isset($_POST['delete'])) {

   $trainingcertification->check($_POST['id'], DELETE);
   $trainingcertification->delete($_POST);
   $trainingcertification->redirectToList();
} else {

    $trainingcertification->checkGlobal(READ);

    if (Session::getCurrentInterface() == 'central') {
        Html::header(PluginDlteamsTrainingCertification::getTypeName(2), '', 'dlteams', 'plugindlteamsmenu', 'trainingcertification');
    } else {
        Html::helpHeader(PluginDlteamsTrainingCertification::getTypeName(0));
    }

    $trainingcertification->display(['id' => $_GET['id']]);

    if (Session::getCurrentInterface() == 'central') {
        Html::footer();
    } else {
        Html::helpFooter();
    }
}

