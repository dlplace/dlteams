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

class PluginDlteamsRecord_Impact extends CommonDBRelation {

   static public $itemtype_1 = 'PluginDlteamsRecord';
   static public $items_id_1 = 'plugin_dlteams_records_id';
   static public $itemtype_2 = 'PluginDlteamsImpact';
   static public $items_id_2 = 'plugin_dlteams_impacts_id';

   static function getListForItem(CommonDBTM $item) {
      global $DB;
      $params = static::getListForItemParams($item, true);
      $iterator = $DB->request($params);
      return $iterator;
   } 
}