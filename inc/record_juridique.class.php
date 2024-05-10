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

class PluginDlteamsRecord_Juridique extends CommonDBRelation {

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      switch ($item->getType()) {
         case PluginDlteamsRecord::class :
            self::showForItem($item, $withtemplate);
            break;
      }

      return true;
   }

   static function showForItem1(CommonDBTM $item, $withtemplate = 0) {
      global $DB;


      //if ($canedit && $number) {
      //   $massive_action_params['ontop'] = false;
      //   Html::showMassiveActions($massive_action_params);
      //   Html::closeForm();
      //}
	  ////////////////////////
	  $id = $item->fields['id'];
      if (!$item->can($id, READ)) {
         return false;
      }

      $canedit = $item->can($id, UPDATE);
      $rand = mt_rand(1, mt_getrandmax());


		/* Test Field */
         //global $CFG_GLPI;
         echo "<div class='firstbloc'>";
         echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__class__) . "'>";
			$iden=$item->fields['id'];

         echo "<input type='hidden' name='plugin_dlteams_records_id' value='$iden' />";
         echo "<table class='tab_cadre_fixe'>";


		 /**add y me**/
		  echo "<tr class='tab_bg_2'><th colspan='3'>" . __("Process", 'dlteams') . "</th></tr>";
		  echo "<tr class='tab_bg_1'><td>";
        // echo __("Process", 'dlteams') .
            echo "<br><i>" . __("The UE law says that user should give and informed consent if data is treated outside of UE", 'dlteams') . "</i>";
         echo "</td><td>";
         $checked = json_decode($item->fields['external_process'] ?? '{}', true);
         $choices = [
            __("France", 'dlteams'),
            __("European Union", 'dlteams'),
            __("World", 'dlteams'),
            "<input type='text' disabled=true placeholder='" . __("Other") .
            "' name='process_other' id='process_other' value='" . ($checked['other'] ?? '') . "'>",
         ];
         echo PluginDlteamsUtils::displayCheckboxes($checked, $choices, 'process');
         echo "</td>";
		 echo "<td class='left' style='padding-left:0px'>";
         echo "<input type='submit' name='add2' value=\"" . _sx('button', 'Save') . "\" class='submit' style='margin-left:0px'>";
         echo "</td></tr>";
		 /**add by me**/




         echo "</table>";
         Html::closeForm();
         echo "</div>";


	  //////////////////////////////////////

      echo "</div>";
   }



}
