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

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

/**
 *  NotificationMailing class extends phpmail and implements the NotificationInterface
 **/
class PluginDlteamsNotificationMail extends NotificationMailing
{

    /**
     * @param $options   array
     **/
    function sendNotification($options = [])
    {

        $mmail = new GLPIMailer();
        $mmail->AddCustomHeader("Auto-Submitted: auto-generated");
        // For exchange
        $mmail->AddCustomHeader("X-Auto-Response-Suppress: OOF, DR, NDR, RN, NRN");

        $mmail->SetFrom($options['from'], $options['fromname'], false);

        if ($options['replyto']) {
            $mmail->AddReplyTo($options['replyto'], $options['replytoname']);
        }
        $mmail->Subject = $options['subject'];

        if (empty($options['content_html'])) {
            $mmail->isHTML(false);
            $mmail->Body = $options['content_text'];
        } else {
            $mmail->isHTML(true);
            $mmail->Body = $options['content_html'];
            $mmail->AltBody = $options['content_text'];
        }

        $mmail->AddAddress($options['to'], $options['toname']);

        if (!empty($options['messageid'])) {
            $mmail->MessageID = "<" . $options['messageid'] . ">";
        }

        // Attach pdf to mail
        if (!empty($options['attachment'])) {

            $mmail->AddAttachment($options['attachment']['path'], $options['attachment']['name']);
        }

        $messageerror = __('Error in sending the email');

        if (!$mmail->Send()) {
            $senderror = true;
            Session::addMessageAfterRedirect($messageerror . "<br>" . $mmail->ErrorInfo, true, WARNING);
        } else {
            //TRANS to be written in logs %1$s is the to email / %2$s is the subject of the mail
            Toolbox::logInFile("mail", sprintf(__('%1$s: %2$s'),
                sprintf(__('An email was sent to %s'), $options['to']),
                $options['subject'] . "\n"));
        }

        $mmail->ClearAddresses();
        return true;
    }

}

