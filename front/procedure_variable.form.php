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
use Glpi\Event;

if (!isset($_GET['id'])) {
    $_GET['id'] = "";
}

$dv = new PluginDlteamsProcedure_Variable();

if (isset($_POST['add'])) {
    $dv->check(-1, CREATE, $_POST);
    $id = $dv->add($_POST);
    Html::redirect($dv->getFormURLWithID($id));

} else if (isset($_POST['update'])) {
    $dv->check($_POST['id'], UPDATE);
    $dv->update($_POST);
    Html::back();

} else if (isset($_POST['delete'])) {
    $dv->check($_POST['id'], DELETE);
    $dv->delete($_POST);
    $dv->redirectToList();

} else if (isset($_POST['purge'])) {
    $dv->check($_POST['id'], PURGE);
    $dv->purge($_POST);
    $dv->redirectToList();

} else {
    $dv->checkGlobal(READ);

    if (Session::getCurrentInterface() == 'central') {
        Html::header(PluginDlteamsProcedure_Variable::getTypeName(2), '', 'dlteams', 'plugindlteamsmenu', 'procedure_variable');
    } else {
        Html::helpHeader(PluginDlteamsProcedure_Variable::getTypeName(0));
    }

    $dv->display(['id' => $_GET['id']]);

    if (Session::getCurrentInterface() == 'central') {
        Html::footer();
    } else {
        Html::helpFooter();
    }
}
