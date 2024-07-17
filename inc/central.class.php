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

use Glpi\Application\View\TemplateRenderer;
use Glpi\Event;
use Glpi\Plugin\Hooks;
use Glpi\System\Requirement\PhpSupportedVersion;
use Glpi\System\Requirement\SafeDocumentRoot;
use Glpi\System\Requirement\SessionsSecurityConfiguration;

/**
 * Central class
 **/
class PluginDlteamsCentral extends CommonDBTM
{

    /**
     * Return the localized name of the current Type
     * Should be overloaded in each new class
     *
     * @return string
     **/
    static function getTypeName($nb = 0) {

        return _n('Vue personnelle Dlteams', 'Vue personnelle Dlteams', $nb, 'resources');
    }

    /**
     * @param \CommonGLPI $item
     * @param int         $withtemplate
     *
     * @return array|string
     */
    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

         if ($item->getType() == 'Central') {
            return PluginDlteamsCentral::getTypeName(2);
        }
        return '';
    }

    /**
     * @param \CommonGLPI $item
     * @param int         $tabnum
     * @param int         $withtemplate
     *
     * @return bool
     */
    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

        $self = new self();
       if ($item->getType() == 'Central') {
            $self->showCentral(Session::getLoginUserID());
        }
        return true;
    }

    public static function canView()
    {
        return true;
    }

    /**
     * @param $who
     */
    function showCentral($who) {
        global $DB, $CFG_GLPI;

//        echo "<table class='tab_cadre_central'><tr><td>";

        if ($this->canView()) {
            $who = Session::getLoginUserID();

            if (Session::isMultiEntitiesMode()) {
                $colsup = 1;
            } else {
                $colsup = 0;
            }

            $dbu = new DbUtils();


            $result = $DB->request([
                "FROM" => ITILFollowup::getTable(),
                "WHERE" => [
                    "users_id" => $who,
                    "entities_id" => Session::getActiveEntity()
                ],
                "ORDERBY" => ["id desc"],
                "LIMIT" => 10
            ]);
            $messages = iterator_to_array($result);
            $number = count($messages);

            if ($number > 0) {

                echo "<table class='tab_cadre' width='100%'>";
                $messagerie_url = PluginDlteamsMessagerie::getSearchURL();
                echo "<tr><th colspan='" . (3 + $colsup) . "'>" . "Mes Messages" . "</th><th><a href='$messagerie_url' target='_blank'>voir tout</a></th></tr>";
                echo "<tr><th>" . __('#') . "</th>";
                if (Session::isMultiEntitiesMode()) {
                    echo "<th>" . __('Entity') . "</th>";
                }
                echo "<th>" . "Type" . "</th>";
                echo "<th>" . __('User') . "</th>";
                echo "<th>" . "Contenu" . "</th>";
                echo "</tr>";


                foreach ($messages as $data) {
/*                    highlight_string("<?php\n\$data =\n" . var_export($data, true) . ";\n?>");*/
//                    die();
//
                    $itemtype = $data["itemtype"];
                    if(class_exists($itemtype)){
                        $url = $itemtype::getFormURLWithID($data["items_id"]);
                    }
                    else
                        $url = "#";
                    echo "<tr class='tab_bg_1" . (isset($data["is_deleted"]) && $data["is_deleted"] == '1' ? "_2" : "") . "'>";
                    echo "<td class='center'><a href='" . $url . "'>" . $data["items_id"];
                    if ($_SESSION["glpiis_ids_visible"]) {
                        echo " (" . $data["items_id"] . ")";
                    }
                    echo "</a></td>";
                    if (Session::isMultiEntitiesMode()) {
                        echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $data['entities_id']) . "</td>";
                    }
                    if(class_exists($itemtype)){
                        $typename = $itemtype::getTypeName();
                    }
                    else
                        $typename = $itemtype;
                    echo "<td class='left'>" . $typename . "</td>";

                    echo "<td class='left'>" . $dbu->getUserName($data["users_id"]) . "</td>";

                    $rand = mt_rand();

                    echo "<td class='left' id='con'>" . substr(htmlspecialchars_decode($data["content"]), 0, 80)  . "</td>";

                    echo "<script>
                        $('#content".$rand."').qtip({
                                    content: '".htmlspecialchars_decode($data["content"])."',
                                    style: {classes: 'qtip-shadow qtip-bootstrap'}, hide: {
                        fixed: true,
                                        delay: 200,
                                        leave: false,
                                        when: {event: 'unfocus'}
                                    }
                                });
                    </script>";
//
                    echo "</tr>";
                }

                echo "</table><br>";

            }


//            taches
            $result = $DB->request([
                "FROM" => TicketTask::getTable(),
//                "LEFT JOIN" => [
//                    Ticket::getTable() => [
//                        'FKEY' => [
//                            TicketTask::getTable() => "id",
//                            Ticket::getTable() => "items_id"
//                        ]
//                    ],
//                ],
                "WHERE" => [
                    "users_id_tech" => $who
                ],
                "ORDERBY" => ["id desc"],
                "LIMIT" => 10
            ]);
            $messages = iterator_to_array($result);
            $number = count($messages);

            if ($number > 0) {

                echo "<table class='tab_cadre' width='100%'>";
                echo "<tr><th colspan='2'>" . "Mes Tâches" . "</th></tr>";

                echo "<tr><th style='width: 3%'>" . __('#') . "</th>";
                echo "<th>" . __('Contenu') . "</th>";
                echo "</tr>";


                foreach ($messages as $data) {
                    $url = PluginDlteamsTicketTask::getFormURLWithID($data["id"]);
                    echo "<tr>";
                    echo "<td class='center'><a href='" . $url . "'>" . $data["id"];
                    if ($_SESSION["glpiis_ids_visible"]) {
                        echo " (" . $data["id"] . ")";
                    }
                    echo "</a></td>";

                    echo "<td class='left'>" . substr(htmlspecialchars_decode($data["content"]), 0, 30)."..."  . "</td>";
//
                    echo "</tr>";
                }

                echo "</table><br>";
            }












            //            taches
            $result = $DB->request([
                "FROM" => TicketTask::getTable(),
//                "LEFT JOIN" => [
//                    Ticket::getTable() => [
//                        'FKEY' => [
//                            TicketTask::getTable() => "id",
//                            Ticket::getTable() => "items_id"
//                        ]
//                    ],
//                ],
                "WHERE" => [
                    "users_id_tech" => $who
                ],
                "ORDERBY" => ["id desc"],
                "LIMIT" => 10
            ]);
            $messages = iterator_to_array($result);
            $number = count($messages);

            if ($number > 0) {

                echo "<table class='tab_cadre' width='100%'>";
                echo "<tr><th colspan='2'>" . "Mes Tâches" . "</th></tr>";

                echo "<tr><th style='width: 3%'>" . __('#') . "</th>";
                echo "<th>" . __('Contenu') . "</th>";
                echo "</tr>";


                foreach ($messages as $data) {
                    $url = PluginDlteamsTicketTask::getFormURLWithID($data["id"]);
                    echo "<tr>";
                    echo "<td class='center'><a href='" . $url . "'>" . $data["id"];
                    if ($_SESSION["glpiis_ids_visible"]) {
                        echo " (" . $data["id"] . ")";
                    }
                    echo "</a></td>";

                    echo "<td class='left'>" . substr(htmlspecialchars_decode($data["content"]), 0, 30)."..."  . "</td>";
//
                    echo "</tr>";
                }

                echo "</table><br>";
            }






            //            validations
            $result = $DB->request([
                "SELECT" => [
                    TicketValidation::getTable().".id",
                    TicketValidation::getTable().".users_id_validate",
                    TicketValidation::getTable().".comment_submission",
                    TicketValidation::getTable().".comment_validation",
                    TicketValidation::getTable().".status",
                    TicketValidation::getTable().".submission_date",
                    TicketValidation::getTable().".validation_date",
                    Ticket::getTable().".name as ticket_name",
                    Ticket::getTable().".content as content",
                    User::getTable().".realname as user_realname",
                ],
                "FROM" => TicketValidation::getTable(),
                "LEFT JOIN" => [
                    Ticket::getTable() => [
                        'FKEY' => [
                            TicketValidation::getTable() => "tickets_id",
                            Ticket::getTable() => "id"
                        ]
                    ],
                    User::getTable() => [
                        'FKEY' => [
                            TicketValidation::getTable() => "users_id",
                            User::getTable() => "id"
                        ]
                    ],

                ],
                "OR" => [
                    ["users_id" => $who],
                    ["users_id_validate" => $who],
                ],
                "ORDERBY" => ["submission_date desc", "validation_date desc"],
                "LIMIT" => 10
            ]);
            $messages = iterator_to_array($result);
            $number = count($messages);

            if ($number > 0) {

                echo "<table class='tab_cadre' width='100%'>";
                echo "<tr><th colspan='2'>" . "Mes validations" . "</th></tr>";

                echo "<tr><th style='width: 3%'>" . __('#') . "</th>";
                echo "<th>" . __('Tâche') . "</th>";
                echo "<th>" . __('Commentaire soumission') . "</th>";
                echo "<th>" . __('Commentaire validation') . "</th>";
                echo "</tr>";


                foreach ($messages as $data) {
                    $url = TicketValidation::getFormURLWithID($data["id"]);
                    echo "<tr>";
                    echo "<td class='center'><a href='" . $url . "'>" . $data["id"];
                    if ($_SESSION["glpiis_ids_visible"]) {
                        echo " (" . $data["id"] . ")";
                    }
                    echo "</a></td>";

                    echo "<td class='left'>" . substr(htmlspecialchars_decode($data["ticket_name"]), 0, 30)."..."  . "</td>";
                    echo "<td class='left'>" . substr(htmlspecialchars_decode($data["comment_submission"]??""), 0, 30)."..."  . "</td>";
                    echo "<td class='left'>" . substr(htmlspecialchars_decode($data["comment_validation"]??""), 0, 30)."..."  . "</td>";
//
                    echo "</tr>";
                }

                echo "</table><br>";
            }




        }

//        $PluginResourcesChecklist = new PluginResourcesChecklist();
//        $PluginResourcesChecklist->showOnCentral(false);
//        echo "<br>";
//        $PluginResourcesChecklist->showOnCentral(true);

//        echo "</td></tr></table>";
    }

}