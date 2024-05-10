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
global $DB;
$record = new PluginDlteamsRecord();
$record->getFromDB($_POST["record_id"]);
if (isset($_POST['save'])) {
    $DB->update(
        $record->getTable(),
        [
            "transmissionmethod" => json_encode($_POST["transmission_methods"]),
            "siintegration" => json_encode($_POST["si_integration"]),
            "mediasupport" => json_encode($_POST["support_methods"]),
        ],
        [
            "id" => $record->fields["id"]
        ]
    );

    Session::addMessageAfterRedirect("Enrégistré avec succès");
}
elseif(isset($_POST["save_comment"])){
    $DB->update(
        $record->getTable(),
        [
            "collect_comment" => $_POST["collect_comment"],
        ],
        [
            "id" => $record->fields["id"]
        ]
    );

    Session::addMessageAfterRedirect("Enrégistré avec succès");
}
else{
    Session::addMessageAfterRedirect("Une erreur s'est produite", 0, ERROR);
}

Html::back();
