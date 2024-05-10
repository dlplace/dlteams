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

$app = new PluginDlteamsDeliverableNotification();

if (isset($_REQUEST['token'])) {
    $deliverable_item = new PluginDlteamsDeliverable_Item();
    $item_list = $deliverable_item->find([
        "approval_token" => $_REQUEST["token"]
    ]);

    $token_match = false;
    foreach ($item_list as $item) {
        if ($item["approval_token"] == $_REQUEST['token']) {

            if (!$item["date_approval"])
                $deliverable_item->update([
                    "deliverables_id" => $item["deliverables_id"],
                    "date_approval" => date('Y-m-d H:i:s'),
                    "id" => $item["id"]
                ]);
            $token_match = true;
        }
    }
    if ($token_match) {
        $app->showApprobationView($_REQUEST['token']);
    } else
        $app->showApprobationView($_REQUEST['token'], true);

} elseif (!isset($_REQUEST['token']))
    $app->showApprobationView($_REQUEST['token'], true);

