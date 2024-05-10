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

Session::checkCentralAccess();

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}
if (!isset($_GET["projects_id"])) {
    $_GET["projects_id"] = "";
}
if (!isset($_GET["projecttasks_id"])) {
    $_GET["projecttasks_id"] = "";
}
$task = new ProjectTask();

if (isset($_POST["add"])) {
    $task->check(-1, CREATE, $_POST);
    $task->add($_POST);

    Event::log(
        $task->fields['projects_id'],
        'project',
        4,
        "maintain",
        //TRANS: %s is the user login
        sprintf(__('%s adds a task'), $_SESSION["glpiname"])
    );
    if ($_SESSION['glpibackcreated']) {
        Html::redirect($task->getLinkURL());
    } else {
        Html::redirect(ProjectTask::getFormURL() . "?projects_id=" . $task->fields['projects_id']);
    }
} else if (isset($_POST["purge"])) {
    $task->check($_POST['id'], PURGE);
    $task->delete($_POST, 1);

    Event::log(
        $task->fields['projects_id'],
        'project',
        4,
        "maintain",
        //TRANS: %s is the user login
        sprintf(__('%s purges a task'), $_SESSION["glpiname"])
    );
    Html::redirect(Project::getFormURLWithID($task->fields['projects_id']));
} else if (isset($_POST["update"])) {
    $task->check($_POST["id"], UPDATE);
    $task->update($_POST);

    Event::log(
        $task->fields['projects_id'],
        'project',
        4,
        "maintain",
        //TRANS: %s is the user login
        sprintf(__('%s updates a task'), $_SESSION["glpiname"])
    );
    Html::back();
} else if (isset($_GET['_in_modal'])) {
    Html::popHeader(ProjectTask::getTypeName(1), $_SERVER['PHP_SELF'], true);
    $task->showForm($_GET["id"], ['withtemplate' => $_GET["withtemplate"]]);
    Html::popFooter();
} else {
    $menus = ["dlteams", "project"];
    ProjectTask::displayFullPageForItem($_GET['id'], $menus, $_GET);
}
