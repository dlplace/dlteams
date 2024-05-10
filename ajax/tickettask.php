<?php

include_once('../../../inc/includes.php');


if(isset($_POST["delete_task"]) && $_POST["delete_task"]){
    $tickettask = new TicketTask();
    if($tickettask->getFromDB($_POST["taskid"])){
        $tickettask->deleteByCriteria(["id" => $_POST["taskid"]]);
        Session::addMessageAfterRedirect("Suppression éffectuée avec succès");
    }
    else{
        Session::addMessageAfterRedirect("Suppression éffectuée avec succès");
    }
}
