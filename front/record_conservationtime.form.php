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

if (isset($_POST['update']) &&
   isset($_POST['plugin_dlteams_records_id'])) {

   $record = new PluginDlteamsRecord();
   $record->canEdit($_POST['conservation_time']);

   global $DB;

   if (isset($_POST['conservation_time'])) {
      // update conservation time in record
      $DB->updateOrDie(
         'glpi_plugin_dlteams_records',
         ['conservation_time' => $_POST['conservation_time']],
         ['id' => $_POST['plugin_dlteams_records_id']]);
   }

   if (isset($_POST['archive_required'])) {
      // update archive conservation time in record
      if ($_POST['archive_required']) {
         $DB->updateOrDie(
            'glpi_plugin_dlteams_records',
            ['archive_time' => $_POST['archive_time'],
               'archive_required' => 1],
            ['id' => $_POST['plugin_dlteams_records_id']]);
      } else {
         $DB->updateOrDie(
            'glpi_plugin_dlteams_records',
            ['archive_time' => "",
               'archive_required' => 0],
            ['id' => $_POST['plugin_dlteams_records_id']]);
      }
   }
}

Html::back();
