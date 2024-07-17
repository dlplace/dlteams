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


if (!isset($_POST["itemtype"]) || !isset($_POST["items_id"]) || !$_POST["itemtype"] || !$_POST["items_id"]) {
    Session::addMessageAfterRedirect("Veuillez choisir un destinataire", false, ERROR);
    Html::back();
}
$itemtype = $_POST["itemtype"];
$items_id = $_POST["items_id"];

$receiver = new $itemtype();
$receiver->getFromDB($items_id);
$receiver_item = null;
$name = " ";
if ($receiver) {
    if ($itemtype == "Contact") {
        $receiver_mail = $receiver->fields["email"];
        $name = sprintf("%s %s", $receiver->fields["firstname"], $receiver->fields["name"]);
    } else {
//            le destinataire est un user
        $receiver_mail = UserEmail::getDefaultForUser($receiver->fields["id"]);
        $name = sprintf("%s %s", $receiver->fields["firstname"], $receiver->fields["realname"]);
    }



    if (!NotificationMailing::isUserAddressValid($receiver_mail)) {
        Session::addMessageAfterRedirect("Le destinataire ne possede pas d'adresse email valide ($receiver_mail)", false, WARNING);
        Html::back();
    }

} else{
    Session::addMessageAfterRedirect("Destinataire non trouvé", false, WARNING);
    Html::back();
}


if (isset($_POST["send_notification"])) {


//    $deliverable = new PluginDlteamsDeliverable();
//    $deliverable->getFromDB($_POST["deliverable_id"]);
//
//    if (!$deliverable->fields["object_notification"]
//        || !$deliverable->fields["text_notification"]
//        || strlen($deliverable->fields["object_notification"]) == 0
//        || strlen($deliverable->fields["text_notification"]) == 0
//    ) {
//        Session::addMessageAfterRedirect("Veuillez enrégistrer l'objet et le texte de notification", false, ERROR);
//        Html::back();
//    }

//    if ($deliverable) {


//
//    $itemtype_item_str = "PluginDlteams" . $itemtype . "_Item";
//    $itemtype_item = new $itemtype_item_str();
//    if ($itemtype == Contact::class)
//        $id_column_name = "contacts_id";
//    else
//        $id_column_name = "users_id";
//
//    $data2 = [
//        $id_column_name => $items_id,
//        "itemtype" => Ticket::class,
//        "items_id" => $_POST["tickets_id"],
//    ];
//
//    $itemtype_item->add([
//        ...$data2,
//        "comment" => $_POST["notification_text"],
//    ]);



    $fupi = new ITILFollowup();
    $objet = $_POST["object_notification"];
    $htmlcontent = "<b>De: </b> system@dlteams.fr <system@dlteams.fr> <br/>";
    $htmlcontent.="<b>À :</b> $name <$receiver_mail> <br/>";
    $htmlcontent.="<b>Objet :</b> $name <$receiver_mail> <br/>";
    $htmlcontent.=html_entity_decode($_POST["notification_text"]);
    $fupi->add([
        "itemtype" => Ticket::class,
        "items_id" => $_POST["tickets_id"],
        "content" => $htmlcontent
    ]);
//    $test = $ticket_item->add([
////        ...$data,
//        "text_notification" => $_POST["notification_text"],
//        "object_notification" => $_POST["object_notification"],
//        "itemtype" => $itemtype,
//        "items_id" => $_POST["items_id"],
//        "email" => $receiver_mail,
////        "object_notification" => $_POST["object"],
//    ]);

//    var_dump($ticket_item);
////    die();

        $data = [
            "subject" => $_POST["object_notification"],
            "content_html" =>  $htmlcontent,
            "to" => $receiver_mail,  // the name here is an email
            "toname" => $receiver_mail,
        ];

        $mail = new PluginDlteamsNotificationMail();
        if ($mail->sendNotification($data)) {
            $saveitem = true;
            Session::addMessageAfterRedirect("Notification envoyé");
        } else {
            Session::addMessageAfterRedirect("Notification non envoyé", false, ERROR);
        }
//    } else
//        Session::addMessageAfterRedirect("Une erreur s'est produite", false, ERROR);
}
//die();
Html::back();

