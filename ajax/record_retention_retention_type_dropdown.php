<?php
/*
 -------------------------------------------------------------------------
 GDPR Records of Processing Activities plugin for GLPI
 Copyright (C) 2020 by Yild.

 https://github.com/yild/gdprropa
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GDPR Records of Processing Activities.

 GDPR Records of Processing Activities is free software; you can
 redistribute it and/or modify it under the terms of the
 GNU General Public License as published by the Free Software
 Foundation; either version 3 of the License, or (at your option)
 any later version.

 GDPR Records of Processing Activities is distributed in the hope that
 it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 See the GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GDPR Records of Processing Activities.
 If not, see <http://www.gnu.org/licenses/>.

 Based on DPO Register plugin, by Karhel Tmarr and then gdprrop by Yild

 --------------------------------------------------------------------------

  @package   dlteams
  @author    Yild - DL Place developers
  @copyright Copyright (c) 2021 by Yild and DL Place developers
  @license   GPLv3+
             http://www.gnu.org/licenses/gpl.txt
  @link      https://github.com/yild/gdprropa
  @since     2020
 --------------------------------------------------------------------------
 */

include("../../../inc/includes.php");
Plugin::load('dlteams', true);

if (strpos($_SERVER['PHP_SELF'], 'record_retention_retention_type_dropdown.php')) {

   $AJAX_INCLUDE = 1;

   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

if (array_key_exists('type', $_POST)) {

   switch ($_POST['type']) {

      case PluginDlteamsRecord_Retention::RETENTION_TYPE_CONTRACT:
         PluginDlteamsRecord_Retention::showContractInputs($_POST);
         break;

      case PluginDlteamsRecord_Retention::RETENTION_TYPE_LEGALBASISACT:
         PluginDlteamsRecord_Retention::showLegalBasesInputs($_POST);
         break;

      case PluginDlteamsRecord_Retention::RETENTION_TYPE_OTHER:
         PluginDlteamsRecord_Retention::showOtherInputs($_POST);
         break;
   }
} else {
   echo '';
}
