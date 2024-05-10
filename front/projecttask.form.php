<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2023 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

include("../../../inc/includes.php");
use Glpi\Event;

if (!isset($_GET['id'])) {
    $_GET['id'] = "";
}

$record = new PluginDlteamsProjectTask();

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
	// indique quel formulaire est ouvert quand on clique sur un élément de la liste
    if (Session::getCurrentInterface() == 'central') {
        Html::header(PluginDlteamsProjectTask::getTypeName(2), '', 'dlteams', 'plugindlteamsmenu', 'projecttask');
    } else {
        Html::helpHeader(PluginDlteamsProjectTask::getTypeName(0));
    }

    $record->display(['id' => $_GET['id']]);

    if (Session::getCurrentInterface() == 'central') {
        Html::footer();
    } else {
        Html::helpFooter();
    }
}

/* ORIGINAL
use Glpi\Event;

include('../inc/includes.php');

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
    $menus = ["tools", "project"];
    ProjectTask::displayFullPageForItem($_GET['id'], $menus, $_GET);
}
*/