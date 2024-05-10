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
/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();

if (!isset($_GET['id'])) {
    $_GET['id'] = "";
}

$task = new PluginDlteamsTicketTask();

/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();
if (isset($_POST['add'])) {

    $task->check(-1, CREATE, $_POST);
    $id = $task->add($_POST);
    Html::redirect($task->getFormURLWithID($id));

}
else if (isset($_POST['update'])) {

    $task->check($_POST['id'], UPDATE);
    $id = $task->update($_POST);
    Html::back();

}
else if (isset($_POST['delete'])) {

    $task = new TicketTask();
    $task->check($_POST['id'], DELETE);
    $task->delete($_POST);
    $task->redirectToList();

}
else if (isset($_POST['purge'])) {


    $task = new PluginDlteamsTicketTask();
//    $task->check($_POST['id'], PURGE);

    $task->delete(["id" => $_POST['id']]);

    if($task->deleteByCriteria([
        "tickettasks_id" => $_POST['id']
    ])){
        Session::addMessageAfterRedirect("Toutes les planifications associés ont été supprimés");
    }

    $task->redirectToList();

}
else if(isset($_POST["add_planification"])){
    $tickettask = new TicketTask();

//    $tickettask->getFromDB($_POST["tickettasks_id"]);
//
//    $data = [
//        "tickettasks_id" => $_POST["tickettasks_id"],
//        "content" => sprintf("Suite de la tâche %s", $_POST["tickettasks_id"]),
//        "tickets_id" => $tickettask->fields["tickets_id"],
//        "tasktemplates_id" => $tickettask->fields["tasktemplates_id"],
//        "taskcategories_id" => $tickettask->fields["taskcategories_id"],
//        "taskcategories_id" => $tickettask->fields["taskcategories_id"],
//    ];

    $id = $tickettask->add([
        ...$_POST,
        "content" => sprintf("Suite de la tâche %s", $_POST["tickettasks_id"]),
    ]);
    Session::addMessageAfterRedirect("Planification ajoutée avec succès");
    Html::back();
}
else if (isset($_POST["unplan"])) {
    $task = new TicketTask();
    $task->getFromDB($_POST["id"]);
    $task->check($_POST["id"], UPDATE);
    $task->unplan();

    $fk = getForeignKeyFieldForItemType(TicketTask::class);
    \Glpi\Event::log(
        $task->getField($fk),
        strtolower(TicketTask::class),
        4,
        "tracking",
        //TRANS: %s is the user login
        sprintf(__('%s unplans a task'), $_SESSION["glpiname"])
    );
    Session::addMessageAfterRedirect("Tâche dé-planifié avec succès");
    Html::back();
}
else {

    $task->checkGlobal(READ);

//    if (Session::getCurrentInterface() == 'central') {
        Html::header(PluginDlteamsTicketTask::getTypeName(2), '', "helpdesk","plugindlteamstickettask");

//    } else {
//        Html::helpHeader(PluginDlteamsTicketTask::getTypeName(0));
//    }

    $task->display(['id' => $_GET['id']]);

    if (Session::getCurrentInterface() == 'central') {
        Html::footer();
    } else {
        Html::helpFooter();
    }

}
