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

class PluginDlteamsCopy extends CommonDBTM implements PluginDlteamsExportableInterface
{
    use PluginDlteamsExportable;
    
    /**
     * Add the copy to the register in the DB
     * @param String $itemtype
     * @param integer $originalId
     * @param integer $copyId
     * @param integer $copyEntity
     */
    public function addCopytoDB($itemtype, $originalId, $copyId, $copyEntity) {
        $input['itemtype'] = $itemtype;
        $input['original_id'] = $originalId;
        $input['copy_id'] = $copyId;
        $input['copy_entity'] = $copyEntity;
        $this->add($input);
    }

    /**
     * Check if there is a copy already
     * @param String $itemtype
     * @param integer $originalId
     * @param integer $copyEntity
     * 
     * @return integer the id of the copy or -1 if there isn't 
     */
    public function CheckCopy($itemType, $originalId, $copyEntity) {
        global $DB;

        $res = -1;

        $options = [
            'SELECT' => [
                'id',
                'copy_id'
            ],
            'WHERE' => [
                'itemtype' => $itemType,
                'original_id' => $originalId,
                'copy_entity' => $copyEntity
            ],
            'ORDER' => 'copy_id DESC'
        ];

        $table = $this->getTable();
        $req = $DB->request($table, $options);
        $first = true;
        foreach ($req as $row) {
            if ($first) {
                $item = new $itemType();
                if ($item->getFromDB($row['copy_id']) && (!isset($item->fields['is_deleted']) || $item->fields['is_deleted'] == 0)) {
                    $first = false;
                    $res = $row['copy_id'];
                } else {
                    $DB->delete($table, ['id' => $row['id']]);
                }
            } else {
                $DB->delete($table, ['id' => $row['id']]);
            }
        }
        return $res;
    }
}
