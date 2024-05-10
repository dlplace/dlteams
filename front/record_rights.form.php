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

if(isset($_POST['update'], $_POST['plugin_dlteams_records_id'])) {
   $record = new PluginDlteamsRecord();
   $record->check($_POST['plugin_dlteams_records_id'], UPDATE);

   // Transform $_POST['consent_*'] into array then into json to put it in DB
   // (dirty but avoid adding another table & relations)
   foreach ($_POST as $key => $value) {
      if (preg_match('/consent_(\d+|other)/',$key)) {
         $consent_array [preg_replace('/consent_/', '',$key)] = $value;
      }
   }
   $consent_json = json_encode($consent_array, JSON_UNESCAPED_UNICODE);

   global $DB;
   $DB->updateOrDie(
      'glpi_plugin_dlteams_records',
      [
         // Consent fields
         'consent_json' => $consent_json,
         'consent_type' => $_POST['consent_type'] ?? 0,
         'consent_explicit' => $_POST['consent_explicit'] ?? "",
         // Rights fields
         'right_information' => $_POST['right_information'],
         'right_opposition' => $_POST['right_oposition'],
         'right_portability' => $_POST['right_portabiliy'],
      ],
      ['id' => $_POST['plugin_dlteams_records_id']]
);
}

Html::back();
