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

//require_once('record_storage.class.php');

class PluginDlteamsRecord_LegalBasi_ShowForRecord extends CommonDBRelation
{
    static public $itemtype_1 = 'PluginDlteamsRecord';
    static public $items_id_1 = 'plugin_dlteams_records_id';
    static public $itemtype_2 = 'pluginDlregisterLegalbasi';
    static public $items_id_2 = 'plugin_dlteams_legalbasis_id';

    
    static function canCreate()
    {
        return true;
    }

    static function canView()
    {
        return true;
    }

    static function canUpdate()
    {
        return true;
    }

    static function canDelete()
    {
        return true;
    }

    static function canPurge()
    {
        return true;
    }

    function canCreateItem()
    {
        return true;
    }

    function canViewItem()
    {
        return true;
    }

    function canUpdateItem()
    {
        return true;
    }

    function canDeleteItem()
    {
        return true;
    }

    function canPurgeItem()
    {
        return true;
    }

    static function showForRecord(PluginDlteamsRecord $record, $withtemplate = 0)
    {
        $id = $record->fields['id'];
        if (!$record->can($id, READ)) {
            return false;
        }
        $canedit = PluginDlteamsRecord::canUpdate();
        $rand = mt_rand(1, mt_getrandmax());
        global $CFG_GLPI;
        global $DB;
        $iterator = $DB->request(self::getRequest($record));
        $number = count($iterator);
        //var_dump ($number) ;
        $items_list = [];
        $used = [];

        foreach ($iterator as $id => $data) {
            //while ($data = $iterator->next()) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }
        // choose legalbasis for this processing
        if ($canedit) {
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsLegalBasi_Item::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . $record->getType() . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $record->getID() . "' />";
            echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsLegalbasi::getType() . "' />";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th colspan='3'>" . __("Add Legal Basis", 'dlteams') .
                "<br><i style='font-weight: normal'>" .
                __("Organism must be based on legal bases that allow this processing", 'dlteams') .
                "</i></th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'><td class='' width='20%'>" . __("Add Legal Basis to record", 'dlteams');
            echo "</td><td width='30%' class='left'>";
            PluginDlteamsLegalbasi::dropdown([
                'addicon' => PluginDlteamsLegalbasi::canCreate(),
                'name' => 'items_id',
                'width' => '250px',
                //'used' => $used
            ]);

            echo "</td>";
            echo "<td width='30%' class='left'>";

            echo "<textarea type='text' maxlength=600 rows='3' name='comment' class='storage_comment2' style='display:none;width:85%;margin-top:4px' placeholder='Commentaire' id='update_comment_textarea'></textarea>";
            echo "</td>";
            echo "<td width='' class='left'>";
            echo "<input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin-left:-63px;display:none' id='btnLegalbasi'>";
            echo "</td></tr>";
            echo "</table>";
            Html::closeForm();
        }

        // send data to form
        if ($iterator) {
            echo "<div class='spaced'>";
            if ($canedit && $number) {
                Html::openMassiveActionsForm('mass' . __class__ . $rand);
                $massive_action_params = ['container' => 'mass' . __class__ . $rand,
                    'num_displayed' => min($_SESSION['glpilist_limit'], $number)];
                Html::showMassiveActions($massive_action_params);
            }
            echo "<table class='tab_cadre_fixehov'>";

            $header_begin = "<tr>";
            $header_top = '';
            $header_bottom = '';
            $header_end = '';

            if ($canedit && $number) {
                $header_begin .= "<th width='10'>";
                $header_top .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsRecord_LegalBasi::class . $rand);

                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsRecord_LegalBasi::class . $rand);
                $header_end .= "</th>";
            }

            $header_end .= "<th>" . __("Name") . "</th>";
            $header_end .= "<th>" . __("Type") . "</th>";
            $header_end .= "<th>" . __("Content") . "</th>";
            $header_end .= "<th>" . __("Comment") . "</th>";
            $header_end .= "</tr>";

            echo $header_begin . $header_top . $header_end; // affiche la ligne d'entete

            //foreach ($items_list as $data) {
            foreach ($iterator as $data) {
                if ($data['name']) {
                    echo "<tr class='tab_bg_1'>";

                    if ($canedit && $number) {
                        echo "<td width='10'>";
//var_dump ($data['linkid']);
                        Html::showMassiveActionCheckBox(PluginDlteamsRecord_LegalBasi::class, $data['linkid']);
                        echo "</td>";
                    }
                    foreach ([
                                 "" => 'PluginDlteamsLegalBasi',
                             ] as $table => $class) {
                        $link = $data[$table . 'name'];
                        if ($_SESSION['glpiis_ids_visible'] || empty($data[$table . 'name'])) {
                            $link = sprintf(__("%1\$s (%2\$s)"), $link, $data[$table . 'id']);
                        }

                        $name = "<a target='_blank' href=\"" . $class::getFormURLWithID($data[$table . 'id']) . "\">" . $link . "</a>";
                        //$type = "<a href=\"" . $class::getFormURLWithID($data[$table.'id']) . "\">" . $link1 . "</a>";

                        // dysplay check box & name
                        echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                        echo " style='width:350px'>" . $name . "</td>";

                        // dysplay nametype
                        echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                        echo " style='width:150px'>";
                        //if($type){ 	    // if($data['nametype']){
                        echo($data['typename']);  //   echo($data['nametype']);
                        // }
                        echo "</td>";

                        // dysplay content
                        echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                        echo " style='width:420px'>";
                        if ($data['content']) {
                            echo $data['content'];
                        }
                        echo "</td>";

                        // dysplay comment
                        echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                        echo ">";
                        if ($data['comment']) {
                            echo $data['comment'];
                        }
                        echo "</td>";

                    }
                    echo "</tr>";
                }
            }

            if ($iterator->count() > 25) {
                echo $header_begin . $header_bottom . $header_end;
            }
            echo "</table>";

            if ($canedit && $number) {
                $massive_action_params['ontop'] = false;
                if ($iterator->count() > 25) {
                    Html::showMassiveActions($massive_action_params);
                }
                Html::closeForm();
            }

            echo "</div>";
        }


//        show record storage now
    }


    function rawSearchOptions()
    {

        $tab = [];

        /*$tab[] = [
           'id' => 'datasubjectscategory',
           'name' => PluginGenericobjectPersonnesconcernee::getTypeName(0)
        ];*/

        $tab[] = [
            'id' => static::$column1_id,
            'table' => PluginDlteamsStoragePeriod::getTable(),
            'field' => 'name',
            'name' => __("Durée de conservation"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'dropdown',
            'searchtype' => ['equals', 'notequals'],
            'joinparams' => [
                'beforejoin' => [
                    'table' => self::getTable(),
                    'joinparams' => [
                        'jointype' => 'child'
                    ]
                ]
            ]
        ];

        $tab[] = [
            'id' => static::$column2_id,
            'table' => PluginDlteamsStorageType::getTable(),
            'field' => 'name',
            'name' => __("Stockage"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'dropdown',
            'searchtype' => ['equals', 'notequals'],
            'joinparams' => [
                'beforejoin' => [
                    'table' => self::getTable(),
                    'joinparams' => [
                        'jointype' => 'child'
                    ]
                ]
            ]
        ];

        $tab[] = [
            'id' => static::$column3_id,
            'table' => PluginDlteamsStorageEndAction::getTable(),
            'field' => 'name',
            'name' => __("En fin de période"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'dropdown',
            'searchtype' => ['equals', 'notequals'],
            'joinparams' => [
                'beforejoin' => [
                    'table' => self::getTable(),
                    'joinparams' => [
                        'jointype' => 'child'
                    ]
                ]
            ]
        ];

        $tab[] = [
            'id' => static::$column4_id,
            'table' => PluginDlteamsRecord_Item::getTable(),
            'field' => 'comment',
            'name' => __("Commentaire"),
            'forcegroupby' => true,
            'massiveaction' => true,
            'datatype' => 'text',
            'searchtype' => ['equals', 'notequals'],
        ];


        return $tab;
    }

    public function exportToDB($subItems = [])
    {
        // TODO: Implement exportToDB() method.
    }

    public static function importToDB(PluginDlteamsLinker $linker, $input = [], $containerId = 0, $subItems = [])
    {
        // TODO: Implement importToDB() method.
    }

    public function deleteObsoleteItems(CommonDBTM $container, array $exclude)
    {
        // TODO: Implement deleteObsoleteItems() method.
    }
}
