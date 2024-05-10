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

if (Session::getCurrentInterface() == 'central') {
   Html::header(PluginDlteamsProtectiveMeasure::getTypeName(2), '', 'dlteams', 'plugindlteamsmenu', 'protectivemeasure');
} else {
   Html::helpHeader(PluginDlteamsProtectiveMeasure::getTypeName(2));
}

if (count($_SESSION['glpiprofiles']) > 1) {
   $profile = new Profile();
   $name = 'Referent-RGPD';
   $options = [
      'SELECT' => [
         'id'
      ],
      'WHERE' => [
         'name' => $name,
      ]
   ];

   $req = $DB->request($profile->getTable(), $options);
  // if ($row = $req->next()) {
	foreach ($req as $id => $row){
      $profile->getFromDB($row['id']);
      if (array_key_exists($profile->getID(), $_SESSION['glpiprofiles'])) {
         $swap = $_SESSION['glpiactiveprofile']['id'] == $profile->getID();
         $text = '<i class="fa fa-layer-group pointer" style="margin-right: 0.4em;"></i>' . __("Swap to model view", "dlteams");
         $returnKey = array_key_first($_SESSION['glpiprofiles']) == $profile->getID() ? array_keys($_SESSION['glpiprofiles'])[1] : array_key_first($_SESSION['glpiprofiles']);

         /*echo "<div class='center switch grey_border pager_controls' style='width:100%'>".
                  "<label for='swapswitch' title='" . $text . "' >".
                     "<span class='sr-only'>" . $text . "</span>" .
                     "<input type='hidden' name='is_deleted' value='0' /> ".
                     "<input type='checkbox' id='swapswitch' name='is_deleted' value='1' ".
                     ($swap ? "checked='checked'" : "").
                     " onClick = \"document.forms['form'].newprofile.value='".($swap ? $returnKey : $profile->getID())."';
                     document.forms['form'].submit();\"" .
                     "/>".
                     "<span style='margin:15px'>" . $text . "</span>".
                     "<span class='lever'></span>" .
                  "</label>".
               "</div>";*/
		$prodif=$profile->getID();

		echo "<div class='switch grey_border pager_controls' style='width:100%'>";
        $server_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
        echo "<div class='switch grey_border pager_controls' style='width:100%'>";
//		if(!empty($swap)){
//			echo "<a href='$server_url/front/central.php?newprofile=$returnKey'><p style='text-align:center'><button class='btn btn-md btn-secondary' style='display: flex; align-items: center; gap: 2px; margin: auto'>
//                    <i class='fas fa-eye-slash'></i>
//                    <span>Masquer la vue modéle</span>
//
//                    </button></p></a>";
//		}else{
//			 echo "<a href='$server_url/front/central.php?newprofile=$prodif'><p style='text-align:center'> <button class='btn btn-md btn-secondary' style='display: flex; align-items: center; gap: 2px; margin: auto'>
//                    <i class='fas fa-eye'></i>
//                    <span>Afficher la vue modéle</span>
//
//                    </button></p></a>";
//		}
                  /*"<label for='swapswitch' title='" . $text . "' >".
                     "<span class='sr-only'>" . $text . "</span>" .
                     "<input type='hidden' name='is_deleted' value='0' /> ".
                     "<input type='checkbox' id='swapswitch' name='is_deleted' value='1' ".
                     ($swap ? "checked='checked'" : "").
                     " onClick = \"document.forms['form'].newprofile.value='".($swap ? $returnKey : $profile->getID())."';
                     document.forms['form'].submit();\"" .
                     "/>".
                     "<span style='margin:15px'>" . $text . "</span>".
                     "<span class='lever'></span>" .
                  "</label>".*/
               "</div>";
      }
   }
}

//check for ACLs
if (PluginDlteamsProtectiveMeasure::canView()) {
   //View is granted: display the list.

   //Add page header
  /* Html::header(
      __('Personnes concernées', 'concernedperson'),
      $_SERVER['PHP_SELF'],
      'assets',
      'PluginDlteamsProtectiveMeasure',
      'PluginDlteamsProtectiveMeasure'
   );*/

   Search::show('PluginDlteamsProtectiveMeasure');

   Html::footer();
} else {
   //View is not granted.
   Html::displayRightError();
}
