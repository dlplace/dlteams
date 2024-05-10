<?php

include_once('../../../inc/includes.php');

$rand = mt_rand();

echo "<br/>";
echo "<br/>";

$params = $_GET;
/** @var array $CFG_GLPI */
global $CFG_GLPI;

if(isset($params["edittickettask"]) && $params["edittickettask"]){
    $tickettask = new PluginDlteamsTicketTask();
    $tickettask->getFromDB($params["items_id"]);
    $tickettask->showForm($params["items_id"], ["in_modal" => true]);
    return;
}
$tickettask = new TicketTask();
$tickettask->getFromDB($params["items_id"]);
echo "<form name='tickettask_form$rand' id='tickettask_form$rand' method='post'
             action='" . Toolbox::getItemTypeFormURL(PluginDlteamsTicketTask::class) . "'>";
echo "<table class='tab_cadre_fixe' style='width: 100%'>";

echo "<tr>";
echo "<td style='text-align:left'>" . __("Id du ticket", 'dlteams') . "</td>";
echo "<td style='text-align: left'>";

echo "<a target='_blank' href=\"" . Ticket::getFormURLWithID($tickettask->fields['tickets_id']) . "\">" . $tickettask->fields['tickets_id'] . "</a>";
echo "</td>";
echo "<td width='15%'>" . " " . "</td>";
echo "</tr>";


echo "<tr>";
echo "<td style='text-align:left'>" . __("Contenu", 'dlteams') . "</td>";
echo "<td>";
echo sprintf("Suite de la tâche %s", $params["items_id"]);
echo "</td>";
echo "<td width='15%'>" . " " . "</td>";
echo "</tr>";


echo "<tr>";
echo "<td style='text-align:left'>" . __("Gabarit", 'dlteams') . "</td>";

echo "<td style='text-align: left'>";
$templategabarit = new TaskTemplate();
$templategabarit->getFromDB($tickettask->fields["tasktemplates_id"]);
echo $templategabarit->fields["name"] ?? "--";
echo "</td>";
echo "<td width='15%'>" . " " . "</td>";
echo "</tr>";

echo "<tr>";
echo "<td style='text-align:left'>" . __("Catégorie", 'dlteams') . "</td>";

echo "<td style='text-align: left'>";
$taskcategory = new TaskCategory();
$taskcategory->getFromDB($tickettask->fields["taskcategories_id"]);
echo $taskcategory->fields["name"] ?? "--";
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td style='text-align:left'>" . __("Statut", 'dlteams') . "</td>";
echo "<td style='text-align: left'>";
//echo Planning::getStatusIcon($tickettask->fields["state"]);
Planning::dropdownState('state', Planning::TODO, true, [
    'width' => '250px',
]);
echo "</td>";
echo "<td width='15%'>" . " " . "</td>";
echo "</tr>";
echo "</table>";

echo "<table class='tab_cadre_fixe' style='margin: auto'>";
echo "<tr>";
echo "<td>" . __("Acteur", 'dlteams') . "</td>";
echo "<td>";
User::dropdown([
    'addicon' => true,
    'name' => 'users_id_tech',
    'value' => "",
    'entity' => Session::getActiveEntity(),
    'right' => 'all',
    'width' => '200px',
]);
echo "</td>";
echo "<td style='width: 15px'></td>";
echo "<td>" . __("Groupe", 'dlteams') . "</td>";
echo "<td>";
Group::dropdown([
    'name' => 'groups_id_tech',
    'value' => "",
    'entity' => Session::getActiveEntity(),
    'right' => 'all',
    'width' => '200px',
]);
echo "</td>";
echo "</tr>";


echo "<tr>";
echo "<td>" . __("Date prévue", 'dlteams') . "</td>";
echo "<td>";

Html::showDateTimeField('date', [
    'value' => '',
    'rand' => $rand,
    'display' => true,
]);

echo "</td>";
echo "<td style='width: 15px'></td>";
echo "<td>" . __("Durée", 'dlteams') . "</td>";
echo "<td>";
Dropdown::showTimeStamp('estimate_duration', [
    'full_width' => true,
    'width' => '100%',
    'icon_label' => true,
    'rand' => $rand,
    'min' => 0,
    'max' => 8 * constant('HOUR_TIMESTAMP'),
    'addfirstminutes' => true,
    'inhours' => true,
    'value' => "",
    'toadd' => array_map(function ($i) {
        return $i * HOUR_TIMESTAMP;
    }, range(9, 100)),
]);
echo "</td>";
echo "</tr>";
echo "</table>";

echo "<div id='subtaskviewplanPlanif' ></div>";

//if (!$tickettask->fields["begin"]) {
echo "<table style='width: 20%; margin-top: 20px'>";
echo "<tr class='tab_bg_2'><td>";
echo "<button id='subtaskplanplanif' class='btn btn-outline-secondary text-truncate' type='button'>
                              <i class='fas fa-calendar'></i>
                              <span>" . __('Plan this task') . "</span>
                           </button>";
echo "</td>";
echo "</tr>";
echo "</table>";
//}

$items_id = $params["items_id"];
echo "<input type='hidden' name='tickettasks_id' value='$items_id'>";
echo "<input type='hidden' name='tickets_id' value='" . $tickettask->fields['tickets_id'] . "'>";
echo "<div class='center firstbloc'>";
echo "<button class='btn btn-primary' name='add_planification' type='submit'> <i class='fa-fw ti ti-plus'></i><span>" .
    _x('button', 'Add') . "</span></button>";
echo "</div>";
Html::closeForm();