<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2024 Teclib' and contributors.
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

class TicketTask extends CommonITILTask
{
    public static $rightname = 'task';


    public static function getTypeName($nb = 0)
    {
        return _n('Ticket task', 'Ticket tasks', $nb);
    }


    public static function canCreate()
    {
        return (Session::haveRight(self::$rightname, parent::ADDALLITEM)
              || Session::haveRight('ticket', Ticket::OWN));
    }


    public static function canView()
    {
        return (Session::haveRightsOr(self::$rightname, [parent::SEEPUBLIC, parent::SEEPRIVATE])
              || Session::haveRight('ticket', Ticket::OWN));
    }


    public static function canUpdate()
    {
        return (Session::haveRight(self::$rightname, parent::UPDATEALL)
              || Session::haveRight('ticket', Ticket::OWN));
    }


    public function canViewPrivates()
    {
        return Session::haveRight(self::$rightname, parent::SEEPRIVATE);
    }


    public function canEditAll()
    {
        return Session::haveRight(self::$rightname, parent::UPDATEALL);
    }


    /**
     * Does current user have right to show the current task?
     *
     * @return boolean
     **/
    public function canViewItem()
    {

        if (!$this->canReadITILItem()) {
            return false;
        }

        if (Session::haveRight(self::$rightname, parent::SEEPRIVATE)) {
            return true;
        }

        if (
            !$this->fields['is_private']
            && Session::haveRight(self::$rightname, parent::SEEPUBLIC)
        ) {
            return true;
        }

       // see task created or affected to me
        if (
            Session::getCurrentInterface() == "central"
            && ($this->fields["users_id"] === Session::getLoginUserID())
              || ($this->fields["users_id_tech"] === Session::getLoginUserID())
        ) {
            return true;
        }

        if (
            $this->fields["groups_id_tech"] && ($this->fields["groups_id_tech"] > 0)
            && isset($_SESSION["glpigroups"])
            && in_array($this->fields["groups_id_tech"], $_SESSION["glpigroups"])
        ) {
            return true;
        }

        return false;
    }


    /**
     * Does current user have right to create the current task?
     *
     * @return boolean
     **/
    public function canCreateItem()
    {

        if (!$this->canReadITILItem()) {
            return false;
        }

        $ticket = new Ticket();
        if (
            $ticket->getFromDB($this->fields['tickets_id'])
            // No validation for closed tickets
            && !in_array($ticket->fields['status'], $ticket->getClosedStatusArray())
        ) {
            return (Session::haveRight(self::$rightname, parent::ADDALLITEM)
                 || $ticket->isUser(CommonITILActor::ASSIGN, Session::getLoginUserID())
                 || (isset($_SESSION["glpigroups"])
                     && $ticket->haveAGroup(
                         CommonITILActor::ASSIGN,
                         $_SESSION['glpigroups']
                     )));
        }
        return false;
    }


    /**
     * Does current user have right to update the current task?
     *
     * @return boolean
     **/
    public function canUpdateItem()
    {

        if (!$this->canReadITILItem()) {
            return false;
        }

        $ticket = new Ticket();
        if (
            $ticket->getFromDB($this->fields['tickets_id'])
            && in_array($ticket->fields['status'], $ticket->getClosedStatusArray())
        ) {
            return false;
        }

        if (
            ($this->fields["users_id"] != Session::getLoginUserID())
            && !Session::haveRight(self::$rightname, parent::UPDATEALL)
        ) {
            return false;
        }

        return true;
    }


    /**
     * Does current user have right to purge the current task?
     *
     * @return boolean
     **/
    public function canPurgeItem()
    {
        $ticket = new Ticket();
        if (
            $ticket->getFromDB($this->fields['tickets_id'])
            && in_array($ticket->fields['status'], $ticket->getClosedStatusArray())
        ) {
            return false;
        }

        return Session::haveRight(self::$rightname, PURGE);
    }


    /**
     * Populate the planning with planned ticket tasks
     *
     * @param $options   array of possible options:
     *    - who          ID of the user (0 = undefined)
     *    - whogroup     ID of the group of users (0 = undefined)
     *    - begin        Date
     *    - end          Date
     *
     * @return array of planning item
     **/
    public static function populatePlanning($options = []): array
    {
        return parent::genericPopulatePlanning(__CLASS__, $options);
    }


    /**
     * Populate the planning with planned ticket tasks
     *
     * @param $options   array of possible options:
     *    - who          ID of the user (0 = undefined)
     *    - whogroup     ID of the group of users (0 = undefined)
     *    - begin        Date
     *    - end          Date
     *
     * @return array of planning item
     **/
    public static function populateNotPlanned($options = []): array
    {
        return parent::genericPopulateNotPlanned(__CLASS__, $options);
    }


    /**
     * Display a Planning Item
     *
     * @param array           $val       array of the item to display
     * @param integer         $who       ID of the user (0 if all)
     * @param string          $type      position of the item in the time block (in, through, begin or end)
     * @param integer|boolean $complete  complete display (more details)
     *
     * @return string
     */
    public static function displayPlanningItem(array $val, $who, $type = "", $complete = 0)
    {
//        todo dlteams
        return static::genericDisplayPlanningItem(__CLASS__, $val, $who, $type, $complete);
    }

//    todo dlteams
    /**
     * Display a Planning Item
     *
     * @param string          $itemtype  itemtype
     * @param array           $val       the item to display
     * @param integer         $who       ID of the user (0 if all)
     * @param string          $type      position of the item in the time block (in, through, begin or end)
     * @param integer|boolean $complete  complete display (more details) (default 0)
     *
     * @return string Output
     **/
    public static function genericDisplayPlanningItem($itemtype, array $val, $who, $type = "", $complete = 0)
    {
        /** @var array $CFG_GLPI */
        global $CFG_GLPI;

        $html = "";
        $rand      = mt_rand();
        $styleText = "";
        if (isset($val["state"])) {
            switch ($val["state"]) {
                case 2: // Done
                    $styleText = "color:#747474;";
                    break;
            }
        }

        $parenttype = str_replace('Task', '', $itemtype);
        if ($parent = getItemForItemtype($parenttype)) {
            $parenttype_fk = $parent->getForeignKeyField();
        } else {
            return;
        }

        $html .= "<img src='" . $CFG_GLPI["root_doc"] . "/pics/rdv_interv.png' alt='' title=\"" .
            Html::entities_deep($parent->getTypeName(1)) . "\">&nbsp;&nbsp;";
        $html .= $parent->getStatusIcon($val['status']);
//        todo dlteams, add tickettask id on planning
//        if($tickettask->getType() == TicketTask::getType())
        $html .= sprintf("<b><a href='/marketplace/dlteams/front/tickettask.form.php?id=%s' target='_blank'>%s</a></b>", $val['id'], $val['id']);
        $tickettask = new TicketTask();
        if($tickettask->getFromDB($val['id']) && $tickettask->fields["tickettasks_id"]){
            $html .= sprintf("<b> suite de <a href='/marketplace/dlteams/front/tickettask.form.php?id=%s' target='_blank'>%s</a></b>", $tickettask->fields["tickettasks_id"], $tickettask->fields["tickettasks_id"]);
        }
        $html .= sprintf("<b> du ticket #<a href='/marketplace/dlteams/front/ticket.form.php?id=%s' target='_blank'>%s: </a></b>", $tickettask->fields["tickets_id"], $tickettask->fields["tickets_id"]);

        $html .= "&nbsp;<a id='content_tracking_" . $val["id"] . $rand . "'
                   href='" . $parenttype::getFormURLWithID($val[$parenttype_fk]) . "'
                   style='$styleText'>";

        if (!empty($val["device"])) {
            $html .= "<br>" . $val["device"];
        }

        if ($who <= 0) { // show tech for "show all and show group"
            $html .= "<br>";
            //TRANS: %s is user name
            $html .= sprintf(__('By %s'), getUserName($val["users_id_tech"]));
        }

        $html .= "</a>";

        $recall = '';
        if (
            isset($val[getForeignKeyFieldForItemType($itemtype)])
            && PlanningRecall::isAvailable()
        ) {
            $pr = new PlanningRecall();
            if (
            $pr->getFromDBForItemAndUser(
                $val['itemtype'],
                $val[getForeignKeyFieldForItemType($itemtype)],
                Session::getLoginUserID()
            )
            ) {
                $recall = "<span class='b'>" . sprintf(
                        __('Recall on %s'),
                        Html::convDateTime($pr->fields['when'])
                    ) .
                    "<span>";
            }
        }

        if (isset($val["state"])) {
            $html .= "<span>";
//            TODO Dlregister
            $html .= Planning::getStatusIcon($val["state"]);
            $html .= "</span>";
        }
        $html .= "<div>";
        $html .= sprintf(__('%1$s: %2$s'), __('Priority'), $parent->getPriorityName($val["priority"]));
        $html .= "</div>";

        // $val['content'] has already been sanitized and decoded by self::populatePlanning()
        $content = $val['content'];
        $html .= "<div class='event-description rich_text_container'>" . $content . "</div>";
        $html .= $recall;

        return $html;
    }

    /**
     * @since 0.85
     *
     * @see commonDBTM::getRights()
     **/
    public function getRights($interface = 'central')
    {

        $values = parent::getRights();
        unset($values[UPDATE], $values[CREATE], $values[READ]);

        if ($interface == 'central') {
            $values[parent::UPDATEALL]      = __('Update all');
            $values[parent::ADDALLITEM  ]   = __('Add to all items');
            $values[parent::SEEPRIVATE]     = __('See private ones');
        }

        $values[parent::SEEPUBLIC]   = __('See public ones');

        if ($interface == 'helpdesk') {
            unset($values[PURGE]);
        }

        return $values;
    }

//    todo dlteams
    /** form for Task
     *
     * @param $ID        Integer : Id of the task
     * @param $options   array
     *     -  parent Object : the object
     **/
    public function showForm($ID, array $options = [])
    {
        $parenttask = new TicketTask();
        $parenttask->getFromDB($this->fields["tickettasks_id"]);
        if(isset($parenttask->fields["content"]))
            $parenttask->fields["content"] = htmlspecialchars_decode($parenttask->fields["content"]);
        \Glpi\Application\View\TemplateRenderer::getInstance()->display('components/itilobject/timeline/form_task.html.twig', [
            'item'               => $options['parent'],
            'subitem'            => $this,
            'parenttask'         => $parenttask,
            'is_subtask'         => !!$this->fields["tickettasks_id"],
            'has_pending_reason' => PendingReason_Item::getForItem($options['parent']) !== false,
            'params'             => $options,
        ]);

        return true;
    }
//    end dlteams

    /**
     * Build parent condition for search
     *
     * @return string
     */
    public static function buildParentCondition()
    {
        return "(0 = 1 " . Ticket::buildCanViewCondition("tickets_id") . ") ";
    }

}
