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


use GlpiPlugin\dlteams\Exception\ImportFailureException;

class PluginDlteamsProtectiveMeasure_Item extends CommonDBTM{



    // show table list
    static function getRequest($record)
    {

        return [
            'SELECT' => [
                'glpi_plugin_dlteams_protectivemeasures_items.id AS linkid',
                'glpi_plugin_dlteams_protectivemeasures.id AS id',
                'glpi_plugin_dlteams_protectivemeasures.name AS name',
                'glpi_plugin_dlteams_protectivemeasures.plugin_dlteams_protectivetypes_id AS type',
                'glpi_plugin_dlteams_protectivetypes.name AS typename',
                'glpi_plugin_dlteams_protectivecategories.name as nameCat',
                //'glpi_plugin_dlteams_protectivemeasures.content AS content',
                'glpi_plugin_dlteams_protectivemeasures_items.comment AS comment',
            ],
            'FROM' => 'glpi_plugin_dlteams_protectivemeasures_items',
            'LEFT JOIN' => [
                'glpi_plugin_dlteams_protectivemeasures' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_protectivemeasures_items' => "protectivemeasures_id",
                        'glpi_plugin_dlteams_protectivemeasures' => "id",
                    ]
                ],
            ],
            'JOIN' => [
                'glpi_plugin_dlteams_protectivetypes' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_protectivemeasures' => "plugin_dlteams_protectivetypes_id",
                        'glpi_plugin_dlteams_protectivetypes' => "id"
                    ]
                ],
                'glpi_plugin_dlteams_protectivecategories' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_protectivemeasures' => "plugin_dlteams_protectivecategories_id",
                        'glpi_plugin_dlteams_protectivecategories' => "id"
                    ]
                ],
            ],
            'ORDER' => [ /*'glpi_plugin_dlteams_legalbasistypes.id ASC',*/ 'name ASC'],
            'WHERE' => [
                'glpi_plugin_dlteams_protectivemeasures_items.items_id' => $record->fields['id']
            ]
        ];
    }

    /**
     * Export in an array all the data of the current instanciated PluginDlteamsRecord_SecurityMeasure
     * @param CommonDBTM $record record linked to impacts
     * @param boolean $impactorganism impact or impactorganism ?
     *
     * @return array the array with all data (with sub tables)
     */
    static function getRequestForImpacts($record, bool $immpactorganism = false)
    {
        return [
            'SELECT' => [
                'glpi_plugin_dlteams_records.id AS linkid',
                'glpi_plugin_dlteams_impacts.id AS id',

            ],
            'FROM' => 'glpi_plugin_dlteams_records',
            'LEFT JOIN' => [
                'glpi_plugin_dlteams_impacts' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_records' => $immpactorganism ? "impact_organism" : "impact_person",
                        'glpi_plugin_dlteams_impacts' => "id",
                    ]
                ],
            ],
            'WHERE' => [
                'glpi_plugin_dlteams_records.id' => $record->fields['id']
            ]
        ];
    }

    // affichage de l'onglet et de son nom
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        if (!$withtemplate) {
            if (Session::haveRight($item::$rightname, READ)) {
                if ($_SESSION['glpishow_count_on_tabs']) {
                    return static::createTabEntry(static::getTypeName(2), static::countForItem($item));
                }
                return static::getTypeName(2);
            }
        }
        return '';
    }

    static function countForItem(CommonDBTM $item) {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable(static::getTable(), ['items_id' => $item->getID(), 'itemtype' => $item->getType()]);
    }

    static function getTypeName($nb = 0) {
        return __("Mesures de protection", 'dlteams');
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case PluginDlteamsRecord::class :
                self::showForItem($item, $withtemplate);
                break;
        }

        return true;
    }

    static function showForItem(CommonDBTM $item, $withtemplate = 0) {
        $id = $item->fields['id'];
        /*if (!$item->can($id, READ)) {
           return false;
        }*/
        $canedit = $item->can($id, UPDATE); // canedit booleen = true
        $rand = mt_rand(1, mt_getrandmax());
        global $DB;

        //$iterator = $DB->request(PluginDlteamsAllItem::getRequestForItems($item,static::$itemtype_2,['id','name','filename','link','comment']));
        $iterator = $DB->request(self::getRequest($item));
        $number = count($iterator); // $number est le nombre de ligne à afficher (=nombre de documents reliés)
        $items_list = [];
        $used = [];

        while ($data = $iterator->next()) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($canedit) {
            echo "<form name='protectivemeasure_form$rand' id='protectivemeasure_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsRecord_ProtectiveMeasure_Item::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='".$item->getType()."' />";
            echo "<input type='hidden' name='items_id1' value='".$item->getID()."' />";
            echo "<input type='hidden' name='itemtype' value='".Document::getType()."' />";
            echo "<input type='hidden' name='entities_id' value='".$item->fields['entities_id']."' />";

            $title = "Mesures de protection liées a ce record";
            $entitled = "Associer une mesure de protection";
            /* il faudrait que la traduction soit fonction de l'objet pour adater la traduction de l'onglet Documents
            les traductions seront fonction de l'objet : alors $title = concatener(%classobjet,"_title"), $entitled = concatener (%classobjet; "_entitled")
                on remplace echo "<tr class='tab_bg_2'><th>" . __("Add Documents", 'dlteams') .
                par 		echo "<tr class='tab_bg_2'><th>" . __($title, 'dlteams') .
                on remplace echo "<tr class='tab_bg_1'><td class='left' width='40%'>". __("Documents in which data may be stored", 'dlteams');
                par 	    echo "<tr class='tab_bg_1'><td class='left' width='40%'>". __($entitled, 'dlteams');
            Dans le fichier PO, il faudra ajouter les lignes record_element_title & record_element_entitled*/
//if class = "record" then $title = "record Add Documents" else $title = "not_record Add Documents" ;
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th>" . __($title, 'dlteams') . "<br><i style='font-weight: normal'>" . "</i></th>";
            echo "<th colspan='2'></th></tr>";
//if class <> "record" then $entitled = "record Documents in which data may be stored" else $entitled = "not_record Documents in which data may be stored" ;
            echo "<tr class='tab_bg_1'><td class='left' width='40%'>". __($entitled, 'dlteams');
            echo "</td><td width='40%' class='left'>";
            Dropdown::show(PluginDlteamsProtectiveMeasure::class,[
                'addicon'  => PluginDlteamsProtectiveMeasure::canCreate(),
                'name' => 'items_id',
                'used' => $used
            ]);
            echo "</td><td width='20%' class='left'><input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td></tr>";
            echo "</table>";
            Html::closeForm();
        }

        if ($iterator) {
            echo "<div class='spaced'>";
            if ($canedit && $number) {
                Html::openMassiveActionsForm('mass' . Document_Item::class . $rand);
                $massive_action_params = ['container' => 'mass' . Document_Item::class . $rand,
                    'num_displayed' => min($_SESSION['glpilist_limit'], $number)];

                Html::showMassiveActions($massive_action_params);
            }
            echo "<table class='tab_cadre_fixehov'>";

            $header_begin = "<tr>";
            $header_top = '';
            $header_bottom = '';
            $header_end = '';

            if ($canedit && $number) {
                $header_begin   .= "<th width='10'>";
                $header_top     .= Html::getCheckAllAsCheckbox('mass' . Document_Item::class . $rand);
                $header_bottom  .= Html::getCheckAllAsCheckbox('mass' . Document_Item::class . $rand);
                $header_end     .= "</th>";
            }

            $header_end .= "<th>" . __("Name") . "</th>";
            $header_end .= "<th>" . __("File link") . "</th>";
            $header_end .= "<th>" . __("Website") . "</th>";
            $header_end .= "<th>" . __("Comment") . "</th>";
            $header_end .= "</tr>";

            echo $header_begin . $header_top . $header_end;

            //foreach ($items_list as $data) {
            // var_dump ($iterator);
            foreach ($iterator as $data) {
                if ($data['name']) {
                    echo "<tr class='tab_bg_1'>";

                    if ($canedit && $number) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox(Document_Item::class, $data['linkid']);
                        echo "</td>";
                    }
                    $link = $data['name'];
                    if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
                        $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
                    }

                    $name = "<a target='_blank' href=\"" . static::$itemtype_2::getFormURLWithID($data['id']) . "\">" . $link . "</a>";
                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo ">" . $name . "</td>";

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo ">" ;
                    if($data['filename']){
                        echo "<a href='../../../front/document.send.php?docid=".$data['id']."' target='_blank'>" . "voir" . "</a>";
                    } else {
                        echo "---";
                    }
                    echo"</td>";

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'") . ">" ;
                    if($data['link']){
                        echo "<a href='".$data['link']."' target='_blank'>" . "voir" . "</a>";
                    } else {
                        echo "---";
                    }
                    echo"</td>";

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo " width='40%'>" ;
                    if ($data['comment']) {
                        echo $data['comment'];
                    } else {
                        echo "---";
                    }
                    echo "</td>";

                    echo "</tr>";
                }
            }


            if ($iterator->count() > 10) {
                echo $header_begin . $header_bottom . $header_end;
            }
            echo "</table>";

            if ($canedit && $number) {
                $massive_action_params['ontop'] = false;
                Html::showMassiveActions($massive_action_params);
                Html::closeForm();
            }

            echo "</div>";
        }

    }
}
