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

use Glpi\Event;

include("../../../inc/includes.php");
/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();
Session::checkLoginUser();

/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();
//$comment = new KnowbaseItem_Comment();
$comment = new ITILFollowup();
if (!isset($_POST['items_id'])) {
    $message = __('Mandatory fields are not filled!');
    Session::addMessageAfterRedirect($message, false, ERROR);
    Html::back();
}
$itemtype_str = $_POST["itemtype"];
$kbitem = new $itemtype_str();

if (!$kbitem->getFromDB($_POST['items_id'])) {
    Html::displayRightError();
}


if (isset($_POST["add"])) {
    if (!isset($_POST['items_id']) || !isset($_POST['content'])) {
        $message = __('Mandatory fields are not filled!');
        Session::addMessageAfterRedirect($message, false, ERROR);
        Html::back();
    }
    $data = [];
    $data["itemtype"] = $_POST["itemtype"];
    $data["items_id"] = $_POST["items_id"];
    $data["content"] = $_POST["content"];
    $data["parent_comment_id"] = isset($_POST["parent_comment_id"])?$_POST["parent_comment_id"]:null;

//    var_dump($_SESSION['glpiactive_entity']);
//    die();
    global $DB;
    $who = Session::getLoginUserID();
    $data["users_id"] = $who;
    $data["users_id_editor"] = $who;
    $data["requesttypes_id"] = 0;
    $data["timeline_position"] = 0;
    $data["sourceof_items_id"] = 0;
    $data["sourceitems_id"] = 0;
    $data["entities_id"] = $_SESSION['glpiactive_entity'];
    $data["date_creation"] = $_SESSION["glpi_currenttime"];

//    var_dump($DB->insert($comment->getTable(), $data));
//    var_dump($comment->add($_POST));
//    die();
//    die();
    if ($DB->insert($comment->getTable(), $data)) {
        Event::log(
            $_POST["items_id"],
            strtolower($_POST["itemtype"]),
            4,
            "tracking",
            sprintf(__('%s adds a comment on knowledge base'), $_SESSION["glpiname"])
        );
        Session::addMessageAfterRedirect(
            "<a href='#'>" . __('Your comment has been added') . "</a>",
            false,
            INFO
        );
    }
    Html::back();
}

if (isset($_POST["edit"])) {
    if (!isset($_POST['items_id']) || !isset($_POST['id']) || !isset($_POST['content'])) {
        $message = __('Mandatory fields are not filled!');
        Session::addMessageAfterRedirect($message, false, ERROR);
        Html::back();
    }

    $comment->getFromDB($_POST['id']);
    $data = array_merge($comment->fields, $_POST);
    unset($data['_glpi_csrf_token']);
    unset($data['edit']);

    if ($DB->update($comment->getTable(), $data, ['id' => $_POST['id']])) {
        Event::log(
            $_POST["items_id"],
            strtolower($_POST["itemtype"]),
            4,
            "tracking",
            sprintf(__('%s modifié'), $_SESSION["glpiname"])
        );
        Session::addMessageAfterRedirect(
            "<a href='#kbcomment{$comment->getID()}'>" . __('Your comment has been edited') . "</a>",
            false,
            INFO
        );
    }
    Html::back();
}

Html::displayErrorAndDie("lost");
