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

interface PluginDlteamsExportableInterface
{
   /**
    * Export in an array all the data of the current instanciated form
    * @param array $subItems key/value pair list of sub items
    *
    * @return array the array with all data (with sub tables)
    */
   public function exportToDB($subItems = []);

   /**
    * Import an itemtype into the db
    * @see PluginFormcreatorForm::importJson
    *
    * @param  PluginDlteamsLinker $linker
    * @param  integer $containerId  id of the parent itemtype, 0 if not
    * @param  array   $input the target data (match the target table)
    * @param  array $subItems key/value pair list of sub items
    * @return integer|false the id of the imported item or false on error
    */
   public static function importToDB(PluginDlteamsLinker $linker, $input = [], $containerId = 0, $subItems = []);

   /**
    * Delete all items belonging to a container and not in the list of items to keep
    *
    * Used when importing objects. Items not matching imported objects are deleted
    * @param CommonDBTM $container instance of the object containing items of
    * @param array $exclude list of ID to keep
    *
    * @return boolean
    */
   public function deleteObsoleteItems(CommonDBTM $container, array $exclude);
}
