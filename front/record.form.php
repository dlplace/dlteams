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

$record = new PluginDlteamsRecord();

if (isset($_POST['add'])) {

    $record->check(-1, CREATE, $_POST);
    $data = [];
    $data = $_POST;
    if ($data["parentnumber"] != 0)
        $data["completenumber"] = $data["number"] + $data["parentnumber"] / 100;
    else
        $data["completenumber"] = $data["number"];
    $data["is_grouping"] === "on"?$data["is_grouping"] = true:$data["is_grouping"] = false;
    $id = $record->add($data);
    Html::redirect($record->getFormURLWithID($id));

} else if (isset($_POST['update'])) {

    $record->check($_POST['id'], UPDATE);

    $data = $_POST;
/*    highlight_string("<?php\n\$data =\n" . var_export($data, true) . ";\n?>");*/
//    die();
//    $data["is_grouping"] === "on"?$data["is_grouping"] = true:$data["is_grouping"] = false;

    if ($data["parentnumber"] != 0)
        $data["completenumber"] = $data["number"] + $data["parentnumber"] / 100;
    else
        $data["completenumber"] = $data["number"];
/*    highlight_string("<?php\n\$data =\n" . var_export($data, true) . ";\n?>");*/
//    die();
    $record->update($data);
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
        Html::header(PluginDlteamsRecord::getTypeName(0), '', 'dlteams', 'plugindlteamsmenu', 'record');
    } else {
        Html::helpHeader(PluginDlteamsRecord::getTypeName(0));
    }

    $record->display(['id' => $_GET['id']]);

    if (Session::getCurrentInterface() == 'central') {
        Html::footer();
    } else {
        Html::helpFooter();
    }

}
