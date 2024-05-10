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

/**
 * Class PluginDlteamsCreatePDFBase
 * Used partially as a wrapper around QueuedNotification class in order to simplify pdf generation
 */
class PluginDlteamsCreateNotificationBase extends CommonGLPI {

    function sendNotification($options=array()) {

        $data = array();
        $data['itemtype']                             = $options['_itemtype'];
        $data['items_id']                             = $options['_items_id'];
        $data['notificationtemplates_id']             = $options['_notificationtemplates_id'];
        $data['entities_id']                          = $options['_entities_id'];

        $data['sendername']                           = $options['fromname'];

        $data['name']                                 = $options['subject'];
        $data['body_text']                            = $options['content_text'];
        $data['recipient']                            = $options['to'];

        $data['mode'] = Notification_NotificationTemplate::MODE_SMS;

        $mailqueue = new QueuedNotification();

        Session::getLoginUserID();

        if (!$mailqueue->add(Toolbox::addslashes_deep($data))) {
            Session::addMessageAfterRedirect(__('Error inserting sms notification to queue', 'sms'), true, ERROR);
            return false;
        } else {
            //TRANS to be written in logs %1$s is the to email / %2$s is the subject of the mail
            Toolbox::logInFile("notification",
                sprintf(__('%1$s: %2$s'),
                    sprintf(__('An SMS notification to %s was added to queue', 'sms'),
                        $options['to']),
                    $options['subject']."\n"));
        }

        return true;
    }


}
