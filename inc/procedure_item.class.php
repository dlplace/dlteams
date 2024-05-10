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

class PluginDlteamsProcedure_Item extends CommonDBRelation
{

    public static $itemtype_1 = "PluginDlteamsProcedure";
    public static $items_id_1 = 'procedures_id ';
    public static $take_entity_1 = false;

    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';
    public static $take_entity_2 = true;

    static function deleteItemAfterLinkDeletion(CommonDBTM $item)
    {
        $deliverable_item = new PluginDlteamsProcedure_Item();
        $result = $deliverable_item->deleteByCriteria([
            "items_id" => $item->fields["id"],
            "itemtype" => Link::getType(),
        ]);
        if ($result)
            Session::addMessageAfterRedirect("Dossier de publication supprimé avec succès");
    }

}
