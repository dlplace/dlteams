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

$itemtype = 'PluginDlteamsTrainingCertification';
$itemtype_item = $itemtype . '_Item';

if (isset($_POST['add']) && isset($_POST[$itemtype::getForeignKeyField()])) {

    $baseItem = new $itemtype();
    $baseItem->check($_POST[$itemtype::getForeignKeyField()], UPDATE);

    global $DB;

    if (isset($_POST['items_id'])) {
        //add baseItem side
        $baseItem_items = new $itemtype_item();
        
        $baseItem_items->add($_POST);
        
        //add to item side
        $dbu  = new DbUtils();
        $otherItemTable = $dbu->getTableForItemtype($_POST['itemtype']);
        $otherItemTable_items = $otherItemTable . '_items';
        if ($DB->tableExists($otherItemTable_items)) {
            $otherItemIdField = $dbu->getForeignKeyFieldForTable($otherItemTable);

            $DB->insert($otherItemTable_items, [
                    $otherItemIdField      => $_POST['items_id'],
                    'items_id'  => $_POST[$itemtype::getForeignKeyField()],
                    'itemtype'      => $itemtype
                ]
            );
        }
	   
    }
}

Html::back();