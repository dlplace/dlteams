<?php

include_once('../../../inc/includes.php');

if (isset($_POST['id']) && isset($_POST["direction"])) {

    $currenttickettask = new TicketTask();

    global $DB;
    $currenttickettask->getFromDB($_POST['id']);
    $tickets_id = $currenttickettask->fields["tickets_id"];
    $items_id = $_POST['id'];
//            si la tache précédent existe
    $tickettaskquery = [
        "SELECT" => [
            TicketTask::getTable() . ".*"
        ],
        "FROM" => TicketTask::getTable(),
        "WHERE" => [
            Ticket::getTable() . ".entities_id" => $_SESSION['glpiactive_entity'],
            TicketTask::getTable() . ".tickets_id" => $tickets_id,
            TicketTask::getTable() . ".tickettasks_id" => null,
        ],
        'LEFT JOIN' => [
            Ticket::getTable() => [
                'FKEY' => [
                    TicketTask::getTable() => 'tickets_id',
                    Ticket::getTable() => 'id'
                ]
            ]
        ],
    ];
    if($_POST["direction"] == "up"){
        $tickettaskquery["WHERE"][TicketTask::getTable() . ".date_creation"] = ['<', $currenttickettask->fields["date_creation"]];
        $tickettaskquery["ORDER"] = [TicketTask::getTable() . '.id DESC'];
    }
    else{
//        down
        $tickettaskquery["WHERE"][TicketTask::getTable() . ".date_creation"] = ['>', $currenttickettask->fields["date_creation"]];
        $tickettaskquery["ORDER"] = [TicketTask::getTable() . '.id ASC'];
    }
    $tickettask_iterator = $DB->request($tickettaskquery);

    switch ($_POST["direction"]) {
        case "up":
            foreach ($tickettask_iterator as $data) {
                $tacheprecedente_date_creation = $data["date_creation"];
                $tickettask = new TicketTask();

                $date = new DateTime($tacheprecedente_date_creation);

//                  retrancher une seconde
                $date->modify('-1 second');

                $tickettask->update([
                    "date_creation" => $date->format('Y-m-d H:i:s'),
                    "id" => $_POST["id"],
                ]);

                Session::addMessageAfterRedirect("Tâche remonté avec succès");
                break;
            }
            break;
        case "down":
            foreach ($tickettask_iterator as $data) {
                $tacheprecedente_date_creation = $data["date_creation"];
                $tickettask = new TicketTask();

                $date = new DateTime($tacheprecedente_date_creation);

//                  retrancher une seconde
                $date->modify('+1 second');

                $tickettask->update([
                    "date_creation" => $date->format('Y-m-d H:i:s'),
                    "id" => $_POST["id"],
                ]);

                Session::addMessageAfterRedirect("Tâche descendu avec succès");
                break;
            }
            break;

    }
}