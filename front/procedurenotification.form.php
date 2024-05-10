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
$saveitem = false;
if (isset($_POST["save_notification_data"]) && $_POST["save_notification_data"]) {

    $procedure = new PluginDlteamsProcedure();
    $procedure->getFromDB($_POST["procedure_id"]);


    if ($procedure) {
        $procedure->update([
            "text_approval" => $_POST["approbation_text"],
            "text_notification" => $_POST["notification_text"],
            "object_notification" => $_POST["object"],
            "object_approval" => $_POST["object_approbation"],
            "id" => $procedure->fields["id"]
        ]);

        Session::addMessageAfterRedirect("Paramètres de notification enrégistrés");
    }

    Html::back();
}

if (!isset($_POST["itemtype"]) || !isset($_POST["items_id"]) || !$_POST["itemtype"] || !$_POST["items_id"]) {
    Session::addMessageAfterRedirect("Veuillez choisir un destinataire", false, ERROR);
    Html::back();
}
$itemtype = $_POST["itemtype"];
$items_id = $_POST["items_id"];

$receiver = new $itemtype();
$receiver->getFromDB($items_id);
$receiver_item = null;
if ($receiver) {
    if ($itemtype == "Contact") {
        $receiver_mail = $receiver->fields["email"];
    } else {
//            le destinataire est un user
        $receiver_mail = UserEmail::getDefaultForUser($receiver->fields["id"]);
    }


    if (!NotificationMailing::isUserAddressValid($receiver_mail)) {
        Session::addMessageAfterRedirect("Le destinataire ne possede pas d'adresse email valide ($receiver_mail)", false, WARNING);
        Html::back();
    }

    $approbation_token = sha1(Toolbox::getRandomString(30));

    $server_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
//    $approbation_text = "<br/>" . $_POST["approbation_text"];
    $approbation_text = "<br/>";
    $approbation_text .= "<br/>";
    $approbation_text .= "Si vous n'arriver pas a valider en cliquant sur le bouton, veuillez copier le lien ci dessous et coller dans votre navigateur: ";
    $approbation_text .= $server_url . "/marketplace/dlteams/front/approve.form.php?token=" . $approbation_token;
    $approbation_link = $server_url . "/marketplace/dlteams/front/approve.form.php?token=" . $approbation_token;
    $approbation_button = "<center><a style='padding: 5px; background-color: #0a6aa1; color: white; border-radius: 3px;' href='$approbation_link'>cliquer pour approuver</a></center>";
} else{
    Session::addMessageAfterRedirect("Destinataire non trouvé", false, WARNING);
    Html::back();
}


if (isset($_POST["send_notification"])) {


    $procedure = new PluginDlteamsProcedure();
    $procedure->getFromDB($_POST["procedure_id"]);

    if (!$procedure->fields["object_notification"]
        || !$procedure->fields["text_notification"]
        || strlen($procedure->fields["object_notification"]) == 0
        || strlen($procedure->fields["text_notification"]) == 0
    ) {
        Session::addMessageAfterRedirect("Veuillez enrégistrer l'objet et le texte de notification", false, ERROR);
        Html::back();
    }

    if ($procedure) {
        $data = [
            "subject" => $procedure->fields["object_notification"],
            "content_html" =>  html_entity_decode($procedure->fields["text_notification"]),
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
    } else
        Session::addMessageAfterRedirect("Une erreur s'est produite", false, ERROR);
}
elseif (isset($_POST["send_approval"])) {
    $procedure = new PluginDlteamsProcedure();
    $procedure->getFromDB($_POST["procedure_id"]);

    if ($procedure) {
        $approbation_content_html = html_entity_decode($procedure->fields["text_approval"])."<br/>".$approbation_button . $approbation_text;
        $data = [
            "subject" => $procedure->fields["object_approval"],
            "content_html" => $approbation_content_html,
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
    } else
        Session::addMessageAfterRedirect("Une erreur s'est produite", false, ERROR);
}
elseif (isset($_POST["send_notification_and_approval"])) {
    $procedure = new PluginDlteamsProcedure();
    $procedure->getFromDB($_POST["procedure_id"]);

    if ($procedure) {
//        send notification
        $data = [
            "subject" => $procedure->fields["object_notification"],
            "content_html" => html_entity_decode($procedure->fields["text_notification"]),
            "to" => $receiver_mail,  // the name here is an email
            "toname" => $receiver_mail,
        ];

        $mail = new PluginDlteamsNotificationMail();
        if ($mail->sendNotification($data)) {
            Session::addMessageAfterRedirect("Notification envoyé");
        } else {
            Session::addMessageAfterRedirect("Notification non envoyé", false, ERROR);
        }

//        send approval

        $approbation_content_html = html_entity_decode($procedure->fields["text_approval"])."<br/>".$approbation_button . $approbation_text;
        $data = [
            "subject" => $procedure->fields["object_approval"],
            "content_html" => $approbation_content_html,
            "to" => $receiver_mail,  // the name here is an email
            "toname" => $receiver_mail,
        ];

        $mail = new PluginDlteamsNotificationMail();
        if ($mail->sendNotification($data)) {
            $saveitem = true;
            Session::addMessageAfterRedirect("Approbation envoyé");
        } else {
            Session::addMessageAfterRedirect("Approbation non envoyé", false, ERROR);
        }
    } else
        Session::addMessageAfterRedirect("Une erreur s'est produite", false, ERROR);
}

if ($saveitem) {

    $procedure->update([
        "text_approval" => $_POST["approbation_text"],
        "text_notification" => $_POST["notification_text"],
        "object_notification" => $_POST["object"],
        "object_approval" => $_POST["object_approbation"],
        "id" => $procedure->fields["id"]
    ]);



    $procedure_item = new PluginDlteamsProcedure_Item();
    $data = [
        "procedures_id" => $_POST["procedure_id"],
        "itemtype" => $_POST["itemtype"],
        "items_id" => $_POST["items_id"],
    ];

    $test = $procedure_item->add([
        ...$data,
        "comment" => $_POST["notification_text"],
        "approval_token" => $approbation_token,
        "email" => $receiver_mail,
        "object_notification" => $_POST["object"],
        "object_approval" => $_POST["object_approbation"],
        "text_approval" => $_POST["approbation_text"],
    ]);

//    var_dump($test);
//    die();

//    if ($procedure_item->getFromDBByCrit($data)) {
//        $procedure_item->update([
//            ...$data,
//            "comment" => $_POST["notification_text"],
//            "approval_token" => $approbation_token,
//            "email" => $receiver_mail,
//            "object_notification" => $_POST["object"],
//            "object_approval" => $_POST["object_approbation"],
//            "id" => $procedure->fields["id"]
//        ]);
//    } else {
//
//        $procedure_item->add([
//            ...$data,
//            "comment" => $_POST["notification_text"],
//            "approval_token" => $approbation_token,
//            "email" => $receiver_mail,
//            "object_notification" => $_POST["object"],
//            "object_approval" => $_POST["object_approbation"]
//        ]);
//    }

    $itemtype_item_str = "PluginDlteams" . $itemtype . "_Item";
    $itemtype_item = new $itemtype_item_str();
    if ($itemtype == Contact::class)
        $id_column_name = "contacts_id";
    else
        $id_column_name = "users_id";

    $data2 = [
        $id_column_name => $items_id,
        "itemtype" => PluginDlteamsProcedure::class,
        "items_id" => $_POST["items_id"],
    ];

    $itemtype_item->add([
        ...$data2,
        "comment" => $_POST["notification_text"],
    ]);



//    if ($itemtype_item->getFromDBByCrit($data2)) {
//        $itemtype_item->update([
//            ...$data2,
//            "comment" => $_POST["notification_text"],
//            "id" => $itemtype_item->fields["id"]
//        ]);
//    } else {
//        $itemtype_item->add([
//            ...$data2,
//            "comment" => $_POST["notification_text"],
//        ]);
//
//    }
}
//die();
Html::back();

