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

$_SESSION['debug']['post'] = $_POST;

if (isset($_POST['add']) && isset($_POST['items_id'])) {

    global $DB;
    //add to item
    $dbu  = new DbUtils();

    $tab = [
        'items_id1'  => $_POST['items_id1'],
        'itemtype1'  => $_POST['itemtype1'],
        'items_id2'  => $_POST['items_id'],
        'itemtype2'  => $_POST['itemtype'] 
    ];
    
    if (isset($_POST['comment'])) {
        $tab['comment'] = $_POST['comment'];
    }

    $_SESSION['debug']['db'] = $DB->insert(PluginDlteamsAllItem::getTable(), $tab);

    if ($_POST['itemtype1'] == 'Document' || $_POST['itemtype'] == 'Document') {
        $one = $_POST['itemtype1'] == 'Document';
        $tabDoc = [
            'documents_id' => $one ? $_POST['items_id1'] : $_POST['items_id'],
            'items_id' => $one ? $_POST['items_id'] : $_POST['items_id1'],
            'itemtype' => $one ? $_POST['itemtype'] : $_POST['itemtype1'],
        ];
        $document_item   = new Document_Item();
        $document_item->check(-1, CREATE, $tabDoc);
        $document_item->add($tabDoc);
    }
}

Html::back();