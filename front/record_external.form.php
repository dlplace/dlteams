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

Session::checkLoginUser();


if (isset($_POST['add']) && (isset($_POST['plugin_dlteams_records_id']))) {

    $record_item = new PluginDlteamsRecord_Item();
    switch ($_POST["consent_type"]) {
        case 1:
            $r_id = $record_item->add([
                'records_id' => $_POST['plugin_dlteams_records_id'],
                'itemtype' => 'PluginDlteamsConcernedPerson',
                'items_id' => $_POST['plugin_dlteams_concernedpersons_id'],
                'itemtype1' => 'PluginDlteamsSendingReason',
                'items_id1' => $_POST['plugin_dlteams_sendingreasons_id'],
                'comment' => $_POST['recipient_comment']
            ]);

            $cp_i = new PluginDlteamsConcernedPerson_Item();
            $cp_i->add([
                'concernedpersons_id' => $_POST['plugin_dlteams_concernedpersons_id'],
                'itemtype' => 'PluginDlteamsRecord',
                'items_id' => $_POST['plugin_dlteams_records_id'],
                'itemtype1' => 'PluginDlteamsSendingReason',
                'items_id1' => $_POST['plugin_dlteams_sendingreasons_id'],
                'comment' => $_POST['recipient_comment'],
                'foreign_id' => $r_id
            ]);

            $sr_i = new PluginDlteamsSendingReason_Item();
            $sr_i->add([
                'sendingreasons_id' => $_POST['plugin_dlteams_sendingreasons_id'],
                'itemtype' => 'PluginDlteamsRecord',
                'items_id' => $_POST['plugin_dlteams_records_id'],
                'itemtype1' => 'PluginDlteamsConcernedPerson',
                'items_id1' => $_POST['plugin_dlteams_concernedpersons_id'],
                'comment' => $_POST['recipient_comment'],
                'foreign_id' => $r_id
            ]);

            Session::addMessageAfterRedirect("Destinataire ajouté avec succès");
            break;
        case 2:
            $r_id = $record_item->add([
                'records_id' => $_POST['plugin_dlteams_records_id'],
                'itemtype' => 'PluginDlteamsThirdPartyCategory',
                'items_id' => $_POST['plugin_dlteams_thirdpartycategories_id'],
                'itemtype1' => 'PluginDlteamsSendingReason',
                'items_id1' => $_POST['plugin_dlteams_sendingreasons_id'],
                'comment' => $_POST['recipient_comment']
            ]);

            $cp_i = new PluginDlteamsThirdPartyCategory_Item();
            $cp_i->add([
                'thirdpartycategories_id' => $_POST['plugin_dlteams_thirdpartycategories_id'],
                'itemtype' => 'PluginDlteamsRecord',
                'items_id' => $_POST['plugin_dlteams_records_id'],
                'itemtype1' => 'PluginDlteamsSendingReason',
                'items_id1' => $_POST['plugin_dlteams_sendingreasons_id'],
                'comment' => $_POST['recipient_comment'],
                'foreign_id' => $r_id
            ]);

            $sr_i = new PluginDlteamsSendingReason_Item();
            $sr_i->add([
                'sendingreasons_id' => $_POST['plugin_dlteams_sendingreasons_id'],
                'itemtype' => 'PluginDlteamsRecord',
                'items_id' => $_POST['plugin_dlteams_records_id'],
                'itemtype1' => 'PluginDlteamsThirdPartyCategory',
                'items_id1' => $_POST['plugin_dlteams_thirdpartycategories_id'],
                'comment' => $_POST['recipient_comment'],
                'foreign_id' => $r_id
            ]);

            Session::addMessageAfterRedirect("Destinataire ajouté avec succès");
            break;
        case 3:
            $r_id = $record_item->add([
                'records_id' => $_POST['plugin_dlteams_records_id'],
                'itemtype' => 'Supplier',
                'items_id' => $_POST['suppliers_id'],
                'itemtype1' => 'PluginDlteamsSendingReason',
                'items_id1' => $_POST['plugin_dlteams_sendingreasons_id'],
                'comment' => $_POST['recipient_comment']
            ]);

            $cp_i = new PluginDlteamsSupplier_Item();
            $cp_i->add([
                'suppliers_id' => $_POST['suppliers_id'],
                'itemtype' => 'PluginDlteamsRecord',
                'items_id' => $_POST['plugin_dlteams_records_id'],
                'itemtype1' => 'PluginDlteamsSendingReason',
                'items_id1' => $_POST['plugin_dlteams_sendingreasons_id'],
                'comment' => $_POST['recipient_comment'],
                'foreign_id' => $r_id
            ]);

            $sr_i = new PluginDlteamsSendingReason_Item();
            $sr_i->add([
                'sendingreasons_id' => $_POST['plugin_dlteams_sendingreasons_id'],
                'itemtype' => 'PluginDlteamsRecord',
                'items_id' => $_POST['plugin_dlteams_records_id'],
                'itemtype1' => 'Supplier',
                'items_id1' => $_POST['suppliers_id'],
                'comment' => $_POST['recipient_comment'],
                'foreign_id' => $r_id
            ]);

            Session::addMessageAfterRedirect("Destinataire ajouté avec succès");
            break;

        default:
           break;
    }
}
elseif (isset($_POST['add2']) && (isset($_POST['plugin_dlteams_records_id']))) {
    global $DB;
    $record = new PluginDlteamsRecord();
    $record->canEdit($_POST['plugin_dlteams_records_id']);

    // Add/update process
    foreach ($_POST as $key => $value) {
        if (preg_match('/process_.*/',$key)) {
            $process_array [preg_replace('/process_/', '',$key)] = $value;
        }
    }

    $process_json = json_encode($process_array, JSON_UNESCAPED_UNICODE);

    $DB->updateOrDie(
        'glpi_plugin_dlteams_records',
        [
            'external_group' => json_encode($_POST['external_group']),
            'external_supplier' => json_encode($_POST['external_supplier']),
            'external_process' => $process_json,
        ],
        ['id' => $_POST['plugin_dlteams_records_id']]
    );

}

Html::back();
