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

class PluginDlteamsLegalBasi_Item extends CommonDBTM
{
    public static $itemtype_1 = "PluginDlteamsLegalBasi";
    public static $items_id_1 = 'legalbasis_id';
    public static $take_entity_1 = false;

    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';
    public static $take_entity_2 = true;

    static function canCreate() {return true;}
    static function canView() {return true;}
    static function canUpdate() {return true;}
    static function canDelete() {return true;}
    static function canPurge() {return true;}
    function canCreateItem() {return true;}
    function canViewItem() {return true;}
    function canUpdateItem() {return true;}
    function canDeleteItem() {return true;}
    function canPurgeItem() {return true;}
    function canEdit($id) {return true;}


    static function getTypeName($nb = 0)
    {
        return __("Legal basis", 'dlteams');
    }

    static function getTypeNameForClass($nb = 0)
    {
        return __("Eléments reliés", 'dlteams');
    }


    // affichage de l'onglet et de son nom
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $nbdoc = $nbitem = 0;
        switch ($item->getType()) {
            case 'PluginDlteamsLegalbasi':
                $ong = [];
                if ($_SESSION['glpishow_count_on_tabs'] && !$item->isNewItem()) {
                    $nbitem = count(self::getItemsRequest($item));
                }

                $ong[1] = self::createTabEntry(_n(
                    'Associated item',
                    'Associated items',
                    Session::getPluralNumber()
                ), $nbitem);
                return $ong;

            default:

                if ($_SESSION['glpishow_count_on_tabs']) {
                    $nbitem = self::countForItem($item);
                }
                return static::createTabEntry(static::getTypeName(Session::getPluralNumber()), $nbitem);
        }
    }

    // comptage du nombre de liaison entre les 2 objets dans la table de l'objet courant
    static function countForItem(CommonDBTM $item)
    {
        $dbu = new DbUtils();
//        var_dump(static::getTable());
//        die();
        return $dbu->countElementsInTable(static::getTable(), ['items_id' => $item->getID(), 'itemtype' => $item->getType()]);
    }


    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addImpactTab($ong, $options);

        return $ong;
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
//        var_dump($item->getType());
//        die();
        switch ($item->getType()) {
            case 'PluginDlteamsLegalbasi':
                self::showItems($item);
                break;
            default:
//                self::showForitem($item, $withtemplate);
                break;
        }
        return true;

    }


    /**
     * Show items links to a document
     *
     * @param $doc Document object
     *
     * @return void
     **@since 0.84
     *
     */
    public static function showItems(PluginDlteamsLegalbasi $object_item)
    {
        global $DB;
        $instID = $object_item->fields['id'];
        if (!$object_item->can($instID, READ)) {
            return false;
        }
        $canedit = $object_item->can($instID, UPDATE);
        // for a measure,
        // don't show here others protective measures associated to this one,
        // it's done for both directions in self::showAssociated
        $types_iterator = [];
        $number = count($types_iterator);

        $used = [];
        $types = PluginDlteamsItemType::getTypes();
//        Enlève le choix de L'objet LegalBasi dans la dropdown qui affiche la liste des objets
        $key = array_search("PluginDlteamsLegalBasi", $types);
        unset($types[$key]);
        $rand = mt_rand();


        if ($canedit) {
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
            echo "<input type='hidden' name='" . static::$items_id_1 . "' value='$instID'>";
            echo "<input type='hidden' name='itemtype1' value='" . str_replace("_Item", "", __CLASS__) . "'>";
            echo "<input type='hidden' name='items_id1' value='" . $instID . "'>";

            echo "<table class='tab_cadre_fixe'>";
            $title = "Related objects";
            $entitled = "Indicate the objects related to this element";
            echo "<tr class='tab_bg_2'><th>" . __($title, 'dlteams') .
                "<br><i style='font-weight: normal'>" .
                "</i></th>";
            echo "<th colspan='2'></th></tr>";

            echo "<tr class='tab_bg_1'><td class='left' width='40%'>". __($entitled, 'dlteams');
            echo "</td><td width='40%' class='left'>";
            $types = PluginDlteamsItemType::getTypes();
            $key = array_search("PluginDlteamsLegalBasi", $types);
            unset($types[$key]);
            Dropdown::showSelectItemFromItemtypes(['itemtypes' => $types,
                'entity_restrict' => ($object_item->fields['is_recursive'] ? getSonsOf('glpi_entities', $object_item->fields['entities_id'])
                    : $object_item->fields['entities_id']),
                'checkright' => true,
                'used' => $used
            ]);
            unset($types);
            echo "</td><td width='20%' class='left'><input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td></tr>";
            echo "<tr class='tab_bg_1'><td width='35%' class=''>";
            echo __("Comment");
            echo "<br/><br/>";
            echo "<textarea type='text' style='width:100%' maxlength=1000 rows='3' name='comment' class='comment_legalbasi_item'></textarea>";
            echo "</td>";
            echo "</table>";
            Html::closeForm();
        }

//        var_dump(self::getTable());
        $items = self::getItemsRequest($object_item);

        if (!count($items)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
            if ($canedit) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = [
                    'num_displayed' => min($_SESSION['glpilist_limit'], count($items)),
                    'container' => 'mass' . __CLASS__ . $rand
                ];
                Html::showMassiveActions($massiveactionparams);
            }

            echo "<table class='tab_cadre_fixehov'>";
            $header = "<tr>";
            if ($canedit) {
                $header .= "<th width='10'>";
                $header .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                $header .= "</th>";
            }
            $header .= "<th>" . __("Object") . "</th>";
            $header .= "<th>" . __("Type") . "</th>";
            $header .= "<th>" . __("Comment") . "</th>";
            $header .= "</tr>";
            echo $header;

            foreach ($items as $row) {
                $item = new $row['itemtype']();
                $item->getFromDB($row['items_id']);
                $name = "<a target='_blank' href=\"" . $item::getFormURLWithID($item->getField('id')) . "\">" . $item->getField('name') . "</a>";
                echo "<tr lass='tab_bg_1'>";
                if ($canedit) {
                    echo "<td>";
                    Html::showMassiveActionCheckBox(__CLASS__, $row["id"]);
                    echo "</td>";
                }
                echo "<td>" . $name . "</td>";
                echo "<td>" . $row["itemtype"]::getTypeName() . "</td>";
                echo "<td>" . $row['comment'] . "</td>";
                echo "</tr>";
            }
            echo $header;
            echo "</table>";

            if ($canedit && count($items)) {
                $massiveactionparams['ontop'] = false;
                Html::showMassiveActions($massiveactionparams);
            }
            if ($canedit) {
                Html::closeForm();
            }

        }
    }

    public static function getItemsRequest(CommonDBTM $object_item)
    {
        global $DB;
        $link_table = str_replace("_Item", "", __CLASS__);
        $temp = new $link_table();

        $items = $DB->request([
            'FROM' => self::getTable(),
            'SELECT' => [
                self::getTable() . '.id',
                self::getTable() . '.id as linkid',
                self::getTable() . '.comment',
                self::getTable() . '.itemtype as itemtype',
                self::getTable() . '.items_id as items_id',
            ],
            'WHERE' => [
                static::getTable() . '.' . static::$items_id_1 => $object_item->fields['id']
            ],
            'LEFT JOIN' => [
                $temp->getTable() => [
                    'FKEY' => [
                        static::getTable() => static::$items_id_1,
                        $temp->getTable() => 'id'
                    ]
                ]
            ],
//            'JOIN' => [
//                'glpi_plugin_dlteams_protectivetypes' => [
//                    'FKEY' => [
//                        'glpi_plugin_dlteams_protectivemeasures' => "plugin_dlteams_protectivetypes_id",
//                        'glpi_plugin_dlteams_protectivetypes' => "id"
//                    ]
//                ],
//                'glpi_plugin_dlteams_protectivecategories' => [
//                    'FKEY' => [
//                        'glpi_plugin_dlteams_protectivemeasures' => "plugin_dlteams_protectivecategories_id",
//                        'glpi_plugin_dlteams_protectivecategories' => "id"
//                    ]
//                ],
//            ],
            'ORDER' => self::getTable() . '.id DESC'
        ]);

        return iterator_to_array($items);
    }

    public static function countElements(CommonDBTM $object_item)
    {
        $nb = 0;
        $iterator = self::getRequest($object_item);

        $items_list = [];
        $used = [];

        foreach ($iterator as $data) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($iterator) {
            foreach ($iterator as $data) {

                if ($data['name']) {
                    $nb++;
                }
            }
        }

        return $nb;
    }



    public static function showItemsForItemType(CommonDBTM $object_item)
    {
        $id = $object_item->fields['id'];

        $canedit = $object_item->can($id, UPDATE); // canedit booleen = true
        $rand = mt_rand(1, mt_getrandmax());
        global $DB;

        $iterator = self::getRequest($object_item);

        $number = count($iterator); // $number est le nombre de ligne à afficher (=nombre de documents reliés)
        $items_list = [];
        $used = [];

        foreach ($iterator as $data) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($canedit) {
            echo "<form name='legalbasiitem_item_form$rand' id='legalbasiitem_item_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . $object_item->getType() . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $object_item->getID() . "' />";
//            echo "<input type='hidden' name='records_id' value='" . $item->getID() . "' />";
            echo "<input type='hidden' name='itemtype' value='" . static::$itemtype_1 . "' />";
            echo "<input type='hidden' name='entities_id' value='" . $object_item->fields['entities_id'] . "' />";

            $title = __("Add Legal Basis", 'dlteams');

            $entitled = "Add Legal Basis to record";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th colspan='4'>" . $title .
                "<br><i style='font-weight: normal'>" .
                __("Organism must be based on legal bases that allow this processing", 'dlteams') .
                "</i></th>";
            echo "</tr>";
            echo "<th colspan='2'></th></tr>";
            echo "<tr class='tab_bg_1'><td class='left' width='30%'>" . __($entitled, 'dlteams');
            echo "</td><td width='20%' class='left'>";
            global $CFG_GLPI;
            Dropdown::show(static::$itemtype_1, [
                'addicon' => static::$itemtype_1::canCreate(),
                'name' => 'items_id',
                'used' => $used,
                'url' => $CFG_GLPI['root_doc'] . "/marketplace/dlteams/ajax/getDropdownValue.php"
            ]);
            echo "</td>";
            echo "<td width='' colspan='1'>";
            echo "<span>";

            echo "<br/><br/>";
            echo "<textarea type='text' style='width:100%;float:right;margin-right:5%; display:none' maxlength=1000 rows='3' id='update_comment_textarea' name='comment' class='storage_comment1' placeholder='commentaire'></textarea>";
            echo "</span>";
            echo "</td>";

            echo "<td width='20%' class='left'><input for='legalbasiitem_item_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td></tr>";
            echo "</table>";

            Html::closeForm();

            echo "<script>
                    $(document).ready(function () {

                        $('select[name=items_id]').on('change', function () {
                        // alert($(this).val());
                        let comment = document.getElementById('update_comment_textarea');
                            if(comment){
                        if ($(this).val() != '0') {   
                            comment.style.display = 'block';
                            $.ajax({
                                url: '/marketplace/dlteams/ajax/get_object_specific_field.php',
                                type: 'POST',
                                data: {
                                    id: $(this).val(),
                                    object: 'PluginDlteamsLegalbasi',
                                    field: 'comment'
                                },
                                success: function (data) {
                                    // Handle the returned data here
                                    console.log(data);
                                    $('#update_comment_textarea').val(data);
                                }
                            });
                        }
                        else {
                            document.getElementById('update_comment_textarea') . style . display = 'none';
                        }
                        }
                    });
                    });
                 </script>";
        }


        if ($iterator) {

            echo "<div class='spaced' > ";
            if ($canedit && $number) {
                Html::openMassiveActionsForm('mass' . __class__ . $rand);
//                Html::openMassiveActionsForm();
                $massive_action_params = [
                    'container' => 'mass' . __class__ . $rand,
                    'num_displayed' => min($_SESSION['glpilist_limit'], $number)];
                Html::showMassiveActions($massive_action_params);
            }
            echo "<br />";

            echo "<table class='tab_cadre_fixehov' > ";

            $header_begin = "<tr > ";
            $header_top = '';
            $header_bottom = '';
            $header_end = '';

            if ($canedit && $number) {
                $header_begin .= "<th width = '10' > ";
                $header_top .= Html::getCheckAllAsCheckbox('mass' . __class__ . $rand);
                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . __class__ . $rand);
                $header_end .= "</th > ";
            }

            $header_end .= "<th width='20%'> " . __("Name") . " </th > ";
            $header_end .= "<th width='10%'> " . __("Type") . " </th > ";
            $header_end .= "<th width='35%' > " . __("Content", 'dlteams') . " </th > ";
            $header_end .= "<th width='35%' > " . __("Comment") . " </th > ";
            $header_end .= "</tr > ";

            echo $header_begin . $header_top . $header_end;
            foreach ($iterator as $data) {

                if ($data['name']) {
                    echo "<tr class='tab_bg_1' > ";

                    if ($canedit && $number) {
                        echo "<td width = '10' > ";
                        Html::showMassiveActionCheckBox(__CLASS__, $data['linkid']);
                        echo "</td > ";
                    }

                    $link = $data['name'];
                    if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
                        $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
                    }
                    $name = "<a target = '_blank' href = \"" . static::$itemtype_1::getFormURLWithID($data['id']) . "\">" . $link . "</a>";

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo ">" . $name . "</td>";

                    $lbt = new PluginDlteamsLegalBasisType();
                    $lbt->getFromDB($data["legalbasistypes_id"]);
                    $data["type_name"] = $lbt->fields["name"];
                    echo "<td class='left'>" . $data['type_name'] . " </td>";
                    echo "<td class='left'>" . htmlspecialchars_decode($data['content']) . " </td>";

                    echo "<td class='left'>" . $data['comment'] . "</td>";
                    echo "</tr>";
                }
            }
            echo "</table>";

            if ($canedit && $number > 10) {
                $massive_action_params['ontop'] = false;
                Html::showMassiveActions($massive_action_params);
                Html::closeForm();
            }
            if ($canedit) {
                Html::closeForm();
            }
            echo "</div>";
        }
    }

    public  static function getRequest(CommonDBTM $item){
        $params = [
            'SELECT' => [
                'glpi_plugin_dlteams_legalbasis_items.id AS linkid',
                'glpi_plugin_dlteams_legalbasis_items.comment AS comment',
                'glpi_plugin_dlteams_legalbasis.' . 'name as name',
                'glpi_plugin_dlteams_legalbasis.' . 'id as id',
                'glpi_plugin_dlteams_legalbasis.content',
                'glpi_plugin_dlteams_legalbasis.plugin_dlteams_legalbasistypes_id as legalbasistypes_id',
            ],
            'FROM' => 'glpi_plugin_dlteams_legalbasis_items',
            'JOIN' => [
                'glpi_plugin_dlteams_legalbasis' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_legalbasis_items' => 'legalbasis_id',
                        'glpi_plugin_dlteams_legalbasis' => "id",
                    ]
                ],
            ],
            'WHERE' => [
                'glpi_plugin_dlteams_legalbasis_items.items_id' => $item->fields['id'],
                'glpi_plugin_dlteams_legalbasis_items.itemtype' => $item->getType(),
                'glpi_plugin_dlteams_legalbasis.is_deleted' => 0
            ],
			//'ORDER' => 'glpi_plugin_dlteams_records_items.id',
			  'ORDER' => 'glpi_plugin_dlteams_legalbasis.plugin_dlteams_legalbasistypes_id',
        ];


        global $DB;
        return $DB->request($params);
    }

    /**
     * Show items links to a document
     *
     * @param $doc Document object
     *
     * @return void
     **@since 0.84
     *
     */
    public static function showForRecords(PluginDlteamsRecord $record)
    {
        global $DB;
        $instID = $record->fields['id'];
        if (!$record->can($instID, READ)) {
            return false;
        }
        $canedit = $record->can($instID, UPDATE);
        // for a measure,
        // don't show here others protective measures associated to this one,
        // it's done for both directions in self::showAssociated
        $types_iterator = [];
        $number = count($types_iterator);

        $used = [];
        $types = PluginDlteamsItemType::getTypes();
        $key = array_search(get_class($record), $types);
        unset($types[$key]);
        $rand = mt_rand();
        if ($canedit) {
            echo "<div class='firstbloc'>";
            echo "<form name='protectivemeasureitem_form$rand' id='protectivemeasureitem_form$rand' method='post'
               action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th colspan='2'>" . __('Add an item') . "</th></tr>";

            echo "<tr class='tab_bg_1'><td class='right'>";

            Dropdown::show(static::$itemtype_1, [
                'addicon' => static::$itemtype_1::canCreate(),
                'name' => 'items_id',
                'used' => $used
            ]);
            echo "</td><td class='center'>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='btn btn-primary'>";
            echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsRecord::class . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . str_replace("_Item", "", __CLASS__) . "'>";
            echo "<input type='hidden' name='items_id1' value='" . $instID . "'>";
            echo "</td></tr>";
            echo "</table>";
            echo "<textarea type='text' style='width:100%;margin-right:5%; display:none;margin-bottom: 10px;' maxlength=1000 rows='3' id='update_comment_textarea' name='comment' class='storage_comment1' placeholder='commentaire'></textarea>";
            Html::closeForm();
            echo "</div>";
        }
        $link_table = str_replace("_Item", "", __CLASS__);
        $temp = new $link_table();
//        var_dump(self::getTable());
//        die();

        $items = $DB->request([
            'FROM' => 'glpi_plugin_dlteams_records_items',
            'SELECT' => [
                'glpi_plugin_dlteams_records_items.id',
                'glpi_plugin_dlteams_records_items.id as linkid',
                'glpi_plugin_dlteams_records_items.comment',
                'glpi_plugin_dlteams_records_items.itemtype as itemtype',
                'glpi_plugin_dlteams_records_items.items_id as items_id',
                'glpi_plugin_dlteams_protectivetypes.name AS typename',
                'glpi_plugin_dlteams_protectivecategories.name as nameCat',
                'glpi_plugin_dlteams_protectivemeasures.name as name',
                'glpi_plugin_dlteams_protectivemeasures.id as pm_id',
            ],
            'WHERE' => [
                'glpi_plugin_dlteams_records_items.itemtype' => PluginDlteamsProtectiveMeasure::class,
                'glpi_plugin_dlteams_records_items.items_id' => $instID,
            ],
            'LEFT JOIN' => [
                'glpi_plugin_dlteams_protectivemeasures' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_records_items' => 'items_id',
                        'glpi_plugin_dlteams_protectivemeasures' => 'id'
                    ]
                ]
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
            'ORDER' => 'glpi_plugin_dlteams_records_items.id DESC'
        ]);

        $items = iterator_to_array($items);

        if (!count($items)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
            if ($canedit) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = [
                    'num_displayed' => min($_SESSION['glpilist_limit'], count($items)),
                    'container' => 'mass' . __CLASS__ . $rand
                ];
                Html::showMassiveActions($massiveactionparams);
            }

            echo "<table class='tab_cadre_fixehov'>";
            $header = "<tr>";
            if ($canedit) {
                $header .= "<th width='10'>";
                $header .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
                $header .= "</th>";
            }
            $header .= "<th>" . __("Name") . "</th>";
            $header .= "<th>" . __("Type") . "</th>";
            $header .= "<th>" . __("Category") . "</th>";
            $header .= "<th>" . __("Comment") . "</th>";
            $header .= "</tr>";
            echo $header;

            foreach ($items as $row) {
                $item = new PluginDlteamsProtectiveMeasure();
                $item->getFromDB($row['pm_id']);
                $name = "<a target='_blank' href=\"" . $item::getFormURLWithID($item->getField('id')) . "\">" . $item->getField('name') . "</a>";
                echo "<tr lass='tab_bg_1'>";
                if ($canedit) {
                    echo "<td>";
                    Html::showMassiveActionCheckBox(__CLASS__, $row["id"]);
                    echo "</td>";
                }
                echo "<td>" . $name . "</td>";
                echo "<td>" . $row['typename'] . "</td>";
                echo "<td>" . $row['nameCat'] . "</td>";
                echo "<td>" . $row['comment'] . "</td>";
                echo "</tr>";
            }
            echo $header;
            echo "</table>";

            if ($canedit && count($items)) {
                $massiveactionparams['ontop'] = false;
                Html::showMassiveActions($massiveactionparams);
            }
            if ($canedit) {
                Html::closeForm();
            }

        }
    }


    // affichage du formulaire
    static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {
        $id = $item->fields['id'];
        $canedit = $item->can($id, UPDATE); // canedit booleen = true
        $rand = mt_rand(1, mt_getrandmax());
        global $DB;

        $iterator = self::getRequest($item);


        $number = count($iterator); // $number est le nombre de ligne à afficher (=nombre de documents reliés)
        $items_list = [];
        $used = [];

        foreach ($iterator as $data) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($canedit) {
            echo "<form name='legalbasi_item_form$rand' id='legalbasi_item_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . $item->getType() . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $item->getID() . "' />";
//            echo "<input type='hidden' name='records_id' value='" . $item->getID() . "' />";
            echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsLegalbasi::getType() . "' />";
            echo "<input type='hidden' name='entities_id' value='" . $item->fields['entities_id'] . "' />";

            $title = __("Add Legal basi", 'dlteams');
            $entitled = "Add Legal basi and comment the relationship";
            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th colspan='3'>" . $title . "<br><i style='font-weight: normal'>" . "</i></th>";
            echo "<th colspan='1'></th></tr>";
            echo "<th colspan='2'></th></tr>";
            echo "<tr class='tab_bg_1'><td class='left' width='30%'>" . __($entitled, 'dlteams');
            echo "</td><td width='20%' class='left'>";
            Dropdown::show(static::$itemtype_1, [
                'addicon' => static::$itemtype_1::canCreate(),
                'name' => 'items_id',
                'used' => $used
            ]);
            echo "</td>";
            echo "<td width='' colspan='1'>";
            echo "<span style='display:none' id='td1'>";

            echo "<br/><br/>";
            echo "<textarea type='text' style='width:100%;float:right;margin-right:5%' maxlength=1000 rows='3' id='update_comment_textarea' name='comment' class='storage_comment1' placeholder='commentaire'></textarea>";
            echo "</span>";
            echo "</td>";

            echo "<td width='20%' class='left'><input for='legalbasi_item_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td></tr>";
            echo "</table>";

            Html::closeForm();
        }


        $iterator = self::getRequest($item);
        if ($iterator) {

            echo "<div class='spaced'>";
            if ($canedit && $number) {
                Html::openMassiveActionsForm('mass' . __class__ . $rand);
                $massive_action_params = [
                    'container' => 'mass' . __class__ . $rand,
                    'num_displayed' => min($_SESSION['glpilist_limit'], $number)];
                Html::showMassiveActions($massive_action_params);
            }
            echo "<br/>";

            echo "<table class='tab_cadre_fixehov'>";

            $header_begin = "<tr>";
            $header_top = '';
            $header_bottom = '';
            $header_end = '';

            if ($canedit && $number) {
                $header_begin .= "<th width='10'>";
                $header_top .= Html::getCheckAllAsCheckbox('mass' . __class__ . $rand);
                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . __class__ . $rand);
                $header_end .= "</th>";
            }

            $header_end .= "<th>" . __("Legal basi") . "</th>";
            $header_end .= "<th width='20%'>" . __("Type", 'dlteams') . "</th>";
            $header_end .= "<th width='20%'>" . __("Category", 'dlteams') . "</th>";
            $header_end .= "<th width='40%'>" . __("Comment") . "</th>";
            $header_end .= "</tr>";


            echo $header_begin . $header_top . $header_end;
            foreach ($iterator as $data) {
                if ($data['name']) {
                    echo "<tr class='tab_bg_1'>";

                    if ($canedit && $number) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox(PluginDlteamsLegalBasi_Item::class, $data['linkid']);
                        echo "</td>";
                    }

                    $link = $data['name'];
                    if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
                        $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
                    }
                    $name = "<a target='_blank' href=\"" . static::$itemtype_1::getFormURLWithID($data['id']) . "\">" . $link . "</a>";

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo ">" . $name . "</td>";


                    echo "<td class='left'>" . $data['typename'] . " </td>";
                    echo "<td class='left'>" . $data['nameCat'] . " </td>";

                    echo "<td class='left'>" . $data['comment'] . "</td>";
                    echo "</tr>";
                }
            }

            if ($iterator->count() > 10) {
                //echo $header_begin . $header_bottom . $header_end;
            }
            echo "</table>";

            if ($canedit && $number > 10) {
                $massive_action_params['ontop'] = false;
                Html::showMassiveActions($massive_action_params);
                Html::closeForm();
            }

            echo "</div>";
        }
    }

    function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id' => "20",
            'table' => PluginDlteamsLegalBasi::getTable(),
            'field' => 'items_id',
            'name' => __("Base legale"),
            'datatype' => 'dropdown',
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => "21",
            'table' => PluginDlteamsLegalBasi_Item::getTable(),
            'field' => 'comment',
            'name' => __('Commentaire'),
            'datatype' => 'text',
            'massiveaction' => true // <- NO MASSIVE ACTION
        ];


        return $tab;
    }

    public function update(array $input, $history = 1, $options = [])
    {
        $legalbasi_item = new PluginDlteamsLegalBasi_Item();
        $legalbasi_item->getFromDB($input["id"]);
        $legalbasi_item_oldfields = $legalbasi_item->fields;

        global $DB;
        $DB->beginTransaction();
        if(isset($input["plugin_dlteams_legalbasis_id"])){
            $DB->update(
                $legalbasi_item->getTable(),
                [
                    "legalbasis_id" => $input["plugin_dlteams_legalbasis_id"]
                ],
                [
                    "id" => $input["id"]
                ]
            );


//            mis a jour de record
            $record_item = new PluginDlteamsRecord_Item();
            $record_item->getFromDBByCrit([
                "records_id" => $legalbasi_item_oldfields["items_id"],
                "itemtype" => PluginDlteamsLegalbasi::class,
                "items_id" => $legalbasi_item_oldfields["legalbasis_id"],
                "comment" => $legalbasi_item_oldfields["comment"],
            ]);
            if($record_item){
                $DB->update(
                    $record_item->getTable(),
                    [
                        "items_id" => $input["plugin_dlteams_legalbasis_id"]
                    ],
                    [
                        "id" => $record_item->fields["id"]
                    ]
                );

                Session::addMessageAfterRedirect("Relation ".PluginDlteamsRecord::getTypeName()." mis a jour avec succès");
            }

        }


        if(isset($input["comment"])){
            $DB->update(
                $legalbasi_item->getTable(),
                [
                    "comment" => $input["comment"]
                ],
                [
                    "id" => $input["id"]
                ]
            );


            //            mis a jour de record
            $record_item = new PluginDlteamsRecord_Item();
            $record_item->getFromDBByCrit([
                "records_id" => $legalbasi_item_oldfields["items_id"],
                "itemtype" => PluginDlteamsLegalbasi::class,
                "items_id" => $legalbasi_item_oldfields["legalbasis_id"],
                "comment" => $legalbasi_item_oldfields["comment"],
            ]);

            if($record_item){
                $DB->update(
                    $record_item->getTable(),
                    [
                        "comment" => $input["comment"]
                    ],
                    [
                        "id" => $record_item->fields["id"]
                    ]
                );

                Session::addMessageAfterRedirect("Relation ".PluginDlteamsRecord::getTypeName()." mis a jour avec succès");
            }
        }

        $DB->commit();
        Session::addMessageAfterRedirect("Traitement modifié avec succès");
        return true;
    }

    public function post_purgeItem()
    {
//        purge relations
        $relation_item_str = $this->fields["itemtype"] . "_Item";
        if(!class_exists($relation_item_str))
            $relation_item_str = "PluginDlteams".$relation_item_str;
        $relation_item = new $relation_item_str();

        $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

        $criteria = [
            "itemtype" => PluginDlteamsLegalBasi::class,
            "items_id" => $this->fields["legalbasis_id"],
            $relation_column_id => $this->fields["items_id"],
            "comment" => $this->fields["comment"]
        ];

        $relation_item->deleteByCriteria($criteria);
        Session::addMessageAfterRedirect("Relation ".$this->fields["itemtype"]::getTypeName()." supprimé avec succès");
    }


    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }
}
