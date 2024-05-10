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

    static $rightname = 'plugin_dlteams_record';

    static function getMenuName()
    {
        return static::getTypeName(1);
    }

    static function getMenuContent()
    {

        /*if (PluginDlteamsRecord::canView()) {
             $image = "<i class='fas fa-print fa-2x' title='" . __("Create PDF for all records within active entity and its sons", 'dlteams') . "'></i>";

             $menu = [];
             $menu['title'] = PluginDlteamsMenu::getMenuName();
             $menu['page'] = '/plugins/dlteams/front/sousmenu.php';
             $menu['links']['search'] = PluginDlteamsRecord::getSearchURL(false);
             $menu['links'][$image] = PluginDlteamsCreatepdf::getSearchURL(false) . '?createpdf&action=prepare&type=' . PluginDlteamsCreatePDF::REPORT_ALL;
             if (PluginDlteamsRecord::canCreate()) {
                $menu['links']['add'] = PluginDlteamsRecord::getFormURL(false);
             }

             $menu['options']['dlteams']['title'] = PluginDlteamsMenu::getMenuName();
             $menu['options']['dlteams']['page'] = PluginDlteamsRecord::getSearchURL(false);
             $menu['options']['dlteams']['links']['search'] = PluginDlteamsRecord::getSearchURL(false);
             $menu['options']['dlteams']['links'][$image] = PluginDlteamsCreatepdf::getSearchURL(false) . '?report_type=3&action=print&createpdf';//'?createpdf&action=prepare&type=' . PluginDlteamsCreatePDF::REPORT_ALL;
             if (PluginDlteamsRecord::canCreate()) {
                $menu['options']['dlteams']['links']['add'] = PluginDlteamsRecord::getFormURL(false);
             }
          }*/

        //global $CFG_GLPI;

        $menu = [];

//        $types = PluginDlteamsItemType::getTypes();
//        foreach ($types as $type) {
//
//            if (preg_match('/^PluginDlteams([a-zA-Z]+)/', $type) == 1) {
//                $shorttype = strtolower(str_replace('PluginDlteams', '', $type));
//
////                if ($type == PluginDlteamsTicketTask::class){
////                    var_dump($type::canView());
////                    die();
////                }
//
//
//                if ($type::canView()) {
//                    $image = "<i class='fas fa-question' title='" . 'Aide' . "'></i>";
//
//                    $menu['options'][$shorttype]['title'] = $type::getTypeName(2);
//                    $menu['options'][$shorttype]['page'] = $type::getSearchURL(false);
//                    $menu['options'][$shorttype]['links']['search'] = $type::getSearchURL(false);
////                    $image = "Aide";
//                    // permet l'ajout d'entrée de menus dans le fil d'arianne //'/front/helpdesk.faq.php?id=4', '_blank');
//                    if (PluginDlteamsRecord::class == $type) {
//                        $menu['options'][$shorttype]['links'][$image] = '/front/helpdesk.faq.php?id=4';
//                    }
//                    if (PluginDlteamsDataCatalog::class == $type) {
//                        $menu['options'][$shorttype]['links'][$image] = KnowbaseItem::getSearchURL(false) . '?contains=catalogues';
//                    }
////                    if (PluginDlteamsTickettask::class == $type) {
//
////                    }
//
//                    if ($type::canCreate()) {
//                        switch ($type) {
//                            case PluginDlteamsStep::class:
//                                if (isset($_GET["projects_id"]))
//                                    $menu['options'][$shorttype]['links']['add'] = $type::getFormURL(false) . "?projects_id=" . $_GET["projects_id"];
//                                break;
//                            default:
//                                $menu['options'][$shorttype]['links']['add'] = $type::getFormURL(false);
//                                break;
//                        }
//                    }
//
//
//                    echo "<script>
//                        \$(document).ready(function () {
//                            // Attendre que la page soit complètement chargée
//                            \$('a[href^=\"/front/knowbaseitem.php?contains=\"]').attr('target', '_blank');
//                        });
//                    </script>";
//                    global $DB;
////            bouton vue modèle
//                    $record = new PluginDlteamsRecord();
////                    $record->checkGlobal(READ);
//
//                    if (count($_SESSION['glpiprofiles']) > 1) {
//                        $profile = new Profile();
//                        $name = 'Vue-Modele';
//                        $options = [
//                            'SELECT' => [
//                                'id'
//                            ],
//                            'WHERE' => [
//                                'name' => $name,
//                            ]
//                        ];
//
//                        $req = $DB->request($profile->getTable(), $options);
//                        foreach ($req as $id => $row) {
//                            //if ($row = $req->next()) {
//                            $profile->getFromDB($row['id']);
//                            if (array_key_exists($profile->getID(), $_SESSION['glpiprofiles'])) {
//                                $swap = $_SESSION['glpiactiveprofile']['id'] == $profile->getID();
//                                $text = '<i class="fa fa-layer-group pointer" style="margin-right: 0.4em;"></i>' . __("Swap to model view", "dlteams");
//                                $returnKey = array_key_first($_SESSION['glpiprofiles']) == $profile->getID() ? array_keys($_SESSION['glpiprofiles'])[1] : array_key_first($_SESSION['glpiprofiles']);
//                                $prodif = $profile->getID();
//                                $server_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
//                                empty($swap) ? $text = "vue modèle" : $text = "vue modèle";
//                                empty($swap) ? $val = $prodif : $val = $returnKey;
//                                empty($swap) ? $checked = '' : $checked = 'checked';
//
//
//                                $text_temp = "<div id='switchmodelcontainer' style='width:100%; display: flex;justify-content: center; margin-bottom: 1px; margin-right: 25px;'>
//                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
//                                            title='$text'>
//                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' role='button'
//                                                autocomplete='off'
//                                                $checked
//                                                onclick='window.location.href=`$server_url/front/central.php?newprofile=$val`'
//                                                />
//                                         <span class='form-check-label mb-1 mb-sm-0'>
//                                            $text
//                                         </span>
//                                      </label></div>
//
//                                      <script>
//                                            var child = document.getElementById('switchmodelcontainer');
//                                            var parent = child.parentNode;
//                                            parent.style.border = 'none';
//                                            parent.classList.remove('btn-outline-secondary');
//                                        </script>
//                            ";
//
//                                $menu['options'][$shorttype]['links'][$text_temp] = "/front/central.php?newprofile=$val";
//                                //if (self::getCurrentId()) {
//
//                                //}
//
//                            }
//                        }
//
//                        switch ($type) {
//                            case PluginDlteamsDeliverable::class:
//                                $plus = "<i class='fas fa-add' onclick='' title='" . __("add tab", 'dlteams') . "'></i> <span style='margin-left: 4px;'>ajouter un onglet</span>";
//
//                                $temp_url = Toolbox::getItemTypeFormURL(PluginDlteamsDeliverable_Section::class) . "?deliverables_id=" . static::getCurrentId() . "&add_tab=" . true;
//                                $menu['options'][$shorttype]['links'][$plus] = $temp_url;
//                                break;
//                            case PluginDlteamsProcedure::class:
//                                $plus = "<i class='fas fa-add' onclick='' title='" . __("add tab", 'dlteams') . "'></i> <span style='margin-left: 4px;'>ajouter un onglet</span>";
//
//                                $temp_url = Toolbox::getItemTypeFormURL(PluginDlteamsProcedure_Section::class) . "?procedures_id=" . static::getCurrentId() . "&add_tab=" . true;
//                                $menu['options'][$shorttype]['links'][$plus] = $temp_url;
//                                break;
//                        }
//                    }
//                }
//            }
//        }

        if (PluginDlteamsRecord::canView()) {
            $menu['title'] = PluginDlteamsIso27001::getMenuName();
            $menu['page'] = '/marketplace/dlteams/front/iso27001.php';
            $menu['icon'] = self::getIcon();
        }

        return $menu;
    }

    public static function getCurrentId()
    {
        // Get the current URL
        $currentURL = $_SERVER['REQUEST_URI'];

        // Get the query string from the URL
        $queryString = parse_url($currentURL, PHP_URL_QUERY);

        // Initialize an empty array for query parameters
        $queryParams = [];

        // Parse the query string into an array of parameters if it exists
        if ($queryString) {
            parse_str($queryString, $queryParams);
        }

        // Get the value of the "id" parameter
        $id = isset($queryParams['id']) ? $queryParams['id'] : null;

        return $id;
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
