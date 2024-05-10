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

class PluginDlteamsRecord_ConservationTime extends CommonDBTM {

   static function getTypeName($nb = 0) {
      return __("Conservation time", 'dlteams');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {

      if (!$item->canView()) {
         return false;
      }

      switch ($item->getType()) {
         case PluginDlteamsRecord::class :

            return self::createTabEntry(PluginDlteamsRecord_ConservationTime::getTypeName(0), 0);
      }

      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      switch ($item->getType()) {
         case PluginDlteamsRecord::class :
            self::showForRecord($item, $withtemplate);
            break;
      }

      return true;
   }

   static function showForRecord(PluginDlteamsRecord $record, $withtemplate = 0) {

      global $CFG_GLPI;

      $id = $record->fields['id'];
      if (!$record->can($id, READ)) {
         return false;
      }

      $canedit = PluginDlteamsRecord::canUpdate();
      $rand = mt_rand(1, mt_getrandmax());

      $options['canedit'] = $canedit;
      $options['formtitle'] = __("Right exercice", 'dlteams');

      if ($canedit) {echo "<div class='firstbloc'>";
         echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";
         echo "<input type='hidden' name='plugin_dlteams_records_id' value='$id' />";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><th>" . __("Conservation time", 'dlteams') . "</th>";
         echo "<th colspan='2'></th></tr>";

         echo "<tr class='tab_bg_1'>";

         echo "<td>" . __("Conservation time", 'dlteams') .
            "<br><i>" . __("Of data used actively in your processing", 'dlteams') . "</i></td>";
         echo "<td class='right'>";
         $conservation_time = Html::cleanInputText($record->fields['conservation_time']);
         echo "<textarea type='text' style='width:98%' maxlength=1000 rows='3' name='conservation_time' required>$conservation_time</textarea>";
         echo "</td></tr>";

         echo "<tr><td>" . __("Do you store archived data ?", 'dlteams') . "</td><td>";

         $rand = Dropdown::showYesNo("archive_required", $record->fields['archive_required']);
         // if yes, display archive conservation time
         $params = [
            'archive_required' => '__VALUE__',
            'archive_time' => $record->fields['archive_time']
         ];
         Ajax::updateItemOnSelectEvent(
            "dropdown_archive_required$rand",
            'archive_time_row',
            $CFG_GLPI['root_doc'] . '/plugins/dlteams/ajax/record_archive_time_dropdown.php',
            $params
         );
         echo "</td></tr>";

         echo "<tr class='tab_bg_1' id='archive_time_row'>";
         self::showArchiveConservationTime($record->fields);
         echo "</tr>";

         echo "<tr><td class='center' colspan='2'><button type='submit' name='update'  class='vsubmit'>";
         echo "<i class='fas fa-save'></i>&nbsp;". _sx('button', 'Save') . "</button>";
         echo "</td></tr>";

         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }
   }

   static function showArchiveConservationTime($data = []){
      if ($data['archive_required']) {
         echo "<td>" . __("Archive conservation time", 'dlteams') . "</td>";
         echo "<td colspan='2'>";
         $archive_time = Html::setSimpleTextContent($data['archive_time']);
         echo "<textarea style='width: 98%;' name='archive_time' maxlength='1000' rows='3'>" . $archive_time. "</textarea>";
         echo "</td>";
      }
   }
}