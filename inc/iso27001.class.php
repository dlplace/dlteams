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

class PluginDlteamsIso27001 extends CommonGLPI

{
    static function getTypeName($nb = 0)
    {
        return __("ISO 27001", 'dlteams');
    }

    static $rightname = 'plugin_dlteams_iso27001menu';

    static function getMenuName()
    {
        return static::getTypeName(1);
    }

    static function getMenuContent()
    {

        $menu = [];

        if (self::canView()) {
            $menu['title'] = PluginDlteamsIso27001::getMenuName();
            $menu['page'] = '/marketplace/dlteams/front/iso27001.php';
            $menu['icon'] = self::getIcon();
        }

        return $menu;
    }

    public static function canView()
    {
        return true;
    }



    static function getIcon()
    {
        return "fa-sharp fa-solid fa-shield-halved";
    }

    static function removeRightsFromSession()
    {

        if (isset($_SESSION['glpimenu']['admin']['types']['PluginDlteamsMenu'])) {
            unset($_SESSION['glpimenu']['admin']['types']['PluginDlteamsMenu']);
        }
        if (isset($_SESSION['glpimenu']['admin']['content']['PluginDlteamsMenu'])) {
            unset($_SESSION['glpimenu']['admin']['content']['PluginDlteamsMenu']);
        }

    }

    static function getControllerInfo($entites_id)
    {
        return [
            'SELECT' => [
                'glpi_plugin_dlteams_controllerinfos.id AS linkid',
                'glpi_plugin_dlteams_controllerinfos.guid AS guid',
                'glpi_plugin_dlteams_controllerinfos.entities_id AS entitiesid',
            ],
            'FROM' => 'glpi_plugin_dlteams_controllerinfos',
            'WHERE' => [
                'glpi_plugin_dlteams_controllerinfos.entities_id' => $entites_id
            ]
        ];
    }
}

?>
<script>
    $(document).ready(function () {
        var linkElement = $('a[href^="/pub/"]');

// Ajoutez l'attribut target="_blank" à l'élément <a>
        linkElement.attr('target', '_blank');
    });

</script>
