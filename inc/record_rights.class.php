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

class PluginDlteamsRecord_Rights extends CommonDBTM {

   static function getTypeName($nb = 0) {
      return __("Rights exercice", 'dlteams');
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (!$item->canView()) {
         return false;
      }

      switch ($item->getType()) {
         case PluginDlteamsRecord::class :
            return self::createTabEntry(PluginDlteamsRecord_Rights::getTypeName(0), 0);
      }

      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         case PluginDlteamsRecord::class :

            $rights = new self();
            $rights->showForRecord($item, $withtemplate = 0);
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

       echo "<style>";
       echo "
            .form-table-text {
                text-align: right;
                width: 40%;
            }
            @media (max-width: 800px) {
                .form-table-text {
                    text-align: left;
                    width: 100%;
                }
            }
        ";

       echo "</style>";
      //if ($canedit) {
      echo "<div class='firstbloc'>";
      echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
         action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";
      echo "<input type='hidden' name='plugin_dlteams_records_id' value='$id' />";

      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_2%'><th colspan='2'>";
      echo __("Add right exercice", 'dlteams') . "</th></tr>";

      echo "<tr><td  class='form-table-text'>" . __("Is the consent implicit or explicit?", 'dlteams') . "</td><td>";
      //choix
      $rand = Dropdown::showFromArray("consent_type", [
         __("Implicit", 'dlteams'),
         __("Explicit", 'dlteams')
      ], [
         'value' => $record->fields['consent_type'] ?? 0,
      ]);
      $params = [
         'consent_type' => '__VALUE__',
         'plugin_dlteams_records_id' => $id
      ];
      Ajax::updateItemOnSelectEvent(
         "dropdown_consent_type$rand",
         'consent_row',
         $CFG_GLPI['root_doc'] . '/marketplace/dlteams/ajax/record_right_consent_dropdown.php',
         $params
      );

      echo "<tr id='consent_row'>";
      self::showConsent($record, $record->fields);
      echo "</tr>";

      echo "<tr><td class='form-table-text'>" . __("Information right", 'dlteams') .
         "<br><i>" . __("Indicate different phases of information following the moment", 'dlteams') . "</i>" .
         "</td><td class='left'>";
//echo "<textarea type='text' style='width:98%' maxlength=1000 rows='3' name='right_information'>" .
//         $record->fields['right_information'] . "</textarea>";
       $cols = 100;
       $rows = 60;
       Html::textarea(['name' => 'right_information',
           'value' => $record->fields['right_information'],
           'enable_fileupload' => false,
           'enable_richtext' => true,
           'cols' => $cols,
           'rows' => $rows
       ]);
echo "</td></tr>";

      echo "<tr><td class='form-table-text'>" . __("Opposition right", 'dlteams') .
         "</td><td class='left'>";

//      echo "<textarea type='text' style='width:98%' maxlength=1000 rows='3' name='right_oposition'>" .
//         $record->fields['right_opposition'] . "</textarea>";
       $cols = 100;
       $rows = 60;
       Html::textarea(['name' => 'right_oposition',
           'value' => $record->fields['right_opposition'],
           'enable_fileupload' => false,
           'enable_richtext' => true,
           'cols' => $cols,
           'rows' => $rows
       ]);
      echo "</td></tr>";

      echo "<tr><td class='form-table-text'>" . __("Portability right", 'dlteams') .
         "<br><i>" . __("Explain modalities, or justify the absence of portability", 'dlteams') . "</i>" .
         "</td><td class='left'><textarea type='text' style='width:98%' maxlength=1000 rows='3' name='right_portabiliy'>" .
         $record->fields['right_portability'] . "</textarea></td></tr>";
      if ($canedit) {
         echo "<tr><td class='center' colspan='2'><input type='submit' name='update'  class='submit' value=" . __('Save') . ">";
         echo "</td></tr>";
      }
      echo "</table>";
      Html::closeForm();
      echo "</div>";
      //}
   }

   static function showConsent(PluginDlteamsRecord $record, $data = []) {
      if ($data['consent_type'] == 0) {
         // Display implicit consent

         echo "<td>" . __("Consent", 'dlteams') . "</td><td>";
         // Obtain array from json stored in the record DB table, if it doesn't exists, empty json string
         $consent = json_decode($record->fields['consent_json'] ?? "{}", true);
         $choices = [
            __("Punctual or contractual engagement", 'dlteams'),
            __("Prior consent to a third-party", 'dlteams'),
            __("Legitimate ou pre-contractual process", 'dlteams'),
            __("Legal obligation", 'dlteams'),
            __("Public interest mission or safeguard of vital interests", 'dlteams'),
            "<input type='text' disabled=false placeholder='" . __("Other") .
            "' name='consent_other' id='consent_other' value='" . ($consent['other'] ?? '') . "'>",
         ];
         echo PluginDlteamsUtils::displayCheckboxes($consent, $choices, 'consent', 'checkbox') . "</td>";

      } elseif ($data['consent_type'] == 1) {
         // Display explicit consent

         echo "<td>" . __("Explicit consent", 'dlteams') . "<br><i>" .
            __("Detail consent collect process", 'dlteams') . "</i></td><td>";
         echo "<textarea type='text' style='width:98%' maxlength=1000 rows='3' name='consent_explicit' required>" .
            ($data['consent_explicit'] ?? "") . "</textarea>";
      }
   }
}
