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

class PluginDlteamsConcernedPerson_Item extends CommonDBTM
{

    public static $itemtype_1 = "PluginDlteamsConcernedPerson";
    public static $items_id_1 = 'concernedpersons_id';
    public static $take_entity_1 = false;

    public static $itemtype_2 = 'itemtype';
    public static $items_id_2 = 'items_id';
    public static $take_entity_2 = true;

    public static $column2_id = "34";

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

    static function getTypeNameForClass($nb = 0)
    {
        return __("Eléments reliés", 'dlteams');
    }

    static function getTypeNameForRecordsRelies($nb = 0)
    {
        return __("Records rattachés", 'dlteams');
    }

    // affichage de l'onglet et de son nom
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        global $DB;
//        return static::createTabEntry(static::getTypeName(Session::getPluralNumber()), 0);
        switch ($item->getType()) {
            case 'PluginDlteamsConcernedPerson':
                $nbitem = count($DB->request(static::getRequest($item->fields['id'])));
                $items = $DB->request(static::getRequest($item->fields['id']));
                $items_to_list = [];

                foreach ($items as $itemdata) {
                    $temp = array_filter($items_to_list, function ($k) use ($itemdata) {
                        return $k['items_id'] === $itemdata['items_id'] && $k['itemtype'] === $itemdata['itemtype'];
                    });
// Decommenter si on doit afficher les redondance
                    if (count($temp) === 0) {
                        array_push($items_to_list, $itemdata);
                    }
                }

                $nbitem1 = count($DB->request(static::getRecordsReliesRequest($item->fields['id'])));

                $ong = [];
                $ong[1] = static::createTabEntry(static::getTypeNameForClass(Session::getPluralNumber()), count($items_to_list));

//                $ong[2] = static::createTabEntry(static::getTypeNameForRecordsRelies(Session::getPluralNumber()), $nbitem1);

                return $ong;
            default:
                $nbdoc = $nbitem = 0;
                if ($_SESSION['glpishow_count_on_tabs']) {
                    $nbitem = self::countForItem($item);
                }
                return static::createTabEntry(static::getTypeName(Session::getPluralNumber()), $nbitem);

        }
    }

    static function getTypeName($nb = 0)
    {
        return __("Concerned persons", 'dlteams');
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case 'PluginDlteamsConcernedPerson':
                switch ($tabnum) {
                    case 1:
                        self::showForConcernedPersons($item);
                        break;

//                    case 2:
//                        self::showRecordsRelies($item);
//                        break;
//                        break;
                }

            default:
//                self::showForitem($item, $withtemplate);
                break;
        }

        return true;

    }

    public static function showForConcernedPersons(PluginDlteamsConcernedPerson $item)
    {
        global $DB;
        $instID = $item->fields['id'];
        if (!$item->can($instID, READ)) {
            return false;
        }
        $canedit = $item->can($instID, UPDATE);
        // for a measure,
        // don't show here others protective measures associated to this one,
        // it's done for both directions in self::showAssociated
        $types_iterator = [];
        $number = count($types_iterator);

        $used = [];
        $types = PluginDlteamsItemType::getTypes();
        $key = array_search(get_class($item), $types);
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

            echo "<tr class='tab_bg_1'><td class='left' width='40%'>" . __($entitled, 'dlteams');
            echo "</td><td width='40%' class='left'>";
            $types = PluginDlteamsItemType::getTypes();
            $key = array_search("PluginDlteamsConcernedPerson", $types);
            unset($types[$key]);
            Dropdown::showSelectItemFromItemtypes(['itemtypes' => $types,
                'entity_restrict' => ($item->fields['is_recursive'] ? getSonsOf('glpi_entities', $item->fields['entities_id'])
                    : $item->fields['entities_id']),
                'checkright' => true,
                'used' => $used
            ]);
            unset($types);
            echo "</td><td width='20%' class='left'><input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td></tr>";

            echo "<tr class='tab_bg_1'>";
            echo "<td style='text-align:left'>" . __("Domaine d'activité", 'dlteams') . "</td>";
            //$randDropdown = mt_rand();
            echo "<td colspan='2'>";
            PluginDlteamsConcernedPerson::dropdown([
                'addicon' => PluginDlteamsConcernedPerson::canCreate(),
                'name' => 'plugin_dlteams_recordcategories_id',
                'value' => "", //$responsible,
                // 'entity' => $this->fields["entities_id"],
                'right' => 'all',
                'width' => "250px",
                // 'rand'   => $randDropdown
            ]);
            echo "</td></tr>";
            echo "<tr class='tab_bg_1'><td width='35%' class=''>";
            echo __("Comment");
            echo "<br/><br/>";
            echo "<textarea type='text' style='width:100%' maxlength=1000 rows='3' name='comment' class='comment_legalbasi_item'></textarea>";
            echo "</td>";
            echo "</table>";
            Html::closeForm();
        }
        $items = $DB->request(static::getRequest($instID));
        $items = iterator_to_array($items);
        $items_to_list = [];
        foreach ($items as $itemdata) {
            $temp = array_filter($items_to_list, function ($k) use ($itemdata) {
                return $k['items_id'] === $itemdata['items_id'] && $k['itemtype'] === $itemdata['itemtype'];
            });

//          Decommenter si on doit afficher les redondance
            if (count($temp) === 0) {
                array_push($items_to_list, $itemdata);
            }
        }


        if (!count($items_to_list)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
            if ($canedit) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = [
                    'num_displayed' => min($_SESSION['glpilist_limit'], count($items)),
                    'container' => 'mass' . __CLASS__ . $rand,
                    'confirm' => true
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

            foreach ($items_to_list as $row) {
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


    public static function showRecordsRelies(PluginDlteamsConcernedPerson $item)
    {
        global $DB;
        $instID = $item->fields['id'];
        if (!$item->can($instID, READ)) {
            return false;
        }
        $canedit = $item->can($instID, UPDATE);
        // for a measure,
        // don't show here others protective measures associated to this one,
        // it's done for both directions in self::showAssociated
        $types_iterator = [];
        $number = count($types_iterator);

        $used = [];
        $types = PluginDlteamsItemType::getTypes();
        $key = array_search(get_class($item), $types);
        unset($types[$key]);
        $rand = mt_rand();
        if ($canedit) {
            echo "<div class='firstbloc'>";
            echo "<form name='concernedpersonsitem_form$rand' id='concernedpersonsitem_form$rand' method='post'
               action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th colspan='2'>" . _n("Personal Data Category", "Personal Data Categories", 0, 'dlteams') . "</th></tr>";

            echo "<tr class='tab_bg_1'><td class='right'>";
//            Dropdown::showSelectItemFromItemtypes(['itemtypes' => $types,
//                'entity_restrict' => ($item->fields['is_recursive'] ? getSonsOf('glpi_entities', $item->fields['entities_id'])
//                    : $item->fields['entities_id']),
//                'checkright' => true,
//                'used' => $used
//            ]);
            PluginDlteamsProcessedData::dropdown([
                'addicon' => PluginDlteamsProcessedData::canCreate(),
                'name' => 'items_id1',
                'width' => '300px'
            ]);
            echo "</td><td class='center'>";
            echo "<input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='btn btn-primary'>";
            echo "<input type='hidden' name='concernedpersons_id' value='$instID'>";
            echo "<input type='hidden' name='itemtype1' value='" . str_replace("_Item", "", __CLASS__) . "'>";
            echo "<input type='hidden' name='recordsrelies' value='" . true . "'>";

            echo "</td></tr>";
            echo "</table>";
            echo "<textarea type='text' style='width:40%;margin-right:5%; display:none;margin-bottom: 10px;' maxlength=1000 rows='3' id='update_comment_textarea' name='comment' class='storage_comment1' placeholder='commentaire'></textarea>";
            Html::closeForm();
            echo "</div>";
        }

        $items = $DB->request(static::getRecordsReliesRequest($instID));
        $items = iterator_to_array($items);
        $items_to_list = [];
        foreach ($items as $itemdata) {
            $temp = array_filter($items_to_list, function ($k) use ($itemdata) {
                return $k['items_id'] === $itemdata['items_id'] && $k['itemtype'] === $itemdata['itemtype'];
            });
// Decommenter si on doit afficher les redondance
//            if (count($temp) === 0) {
            array_push($items_to_list, $itemdata);
//            }
        }


        if (!count($items_to_list)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
            if ($canedit) {
                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
                $massiveactionparams = [
                    'num_displayed' => min($_SESSION['glpilist_limit'], count($items)),
                    'container' => 'mass' . __CLASS__ . $rand,
                    'confirm' => true
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
            $header .= "<th>" . __("Type") . "</th>";
            $header .= "<th>" . __("Nom") . "</th>";
            $header .= "<th>" . __("Comment") . "</th>";
            $header .= "</tr>";
            echo $header;

            foreach ($items_to_list as $row) {
                $tempitem = new $row['itemtype1']();
                $tempitem->getFromDB($row['items_id1']);

                $name = "<a target='_blank' href=\"" . $tempitem::getFormURLWithID($tempitem->getField('id')) . "\">" . $tempitem->getField('name') . "</a>";
                echo "<tr lass='tab_bg_1'>";
                if ($canedit) {
                    echo "<td>";
                    Html::showMassiveActionCheckBox(__CLASS__, $row["id"]);
                    echo "</td>";
                }
                echo "<td>" . $row["itemtype1"]::getTypeName() . "</td>";
                echo "<td>" . $name . "</td>";
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

    function rawSearchOptions()
    {

        $tab = [];


        $tab[] = [
            'id' => static::$column2_id,
            'table' => PluginDlteamsConcernedPerson_Item::getTable(),
            'field' => 'comment',
            'name' => __("Commentaire"),
            'massiveaction' => true,
            'datatype' => 'text',
        ];

        return $tab;
    }

    static function getRequest($instID)
    {
        $link_table = str_replace("_Item", "", __CLASS__);
        $temp = new $link_table();
        return [
            'FROM' => self::getTable(),
            'SELECT' => [
                self::getTable() . '.id',
                self::getTable() . '.id as linkid',
                self::getTable() . '.comment',
                self::getTable() . '.itemtype as itemtype',
                self::getTable() . '.items_id as items_id',
            ],
            'WHERE' => [
                static::getTable() . '.concernedpersons_id' => $instID
            ],

            'LEFT JOIN' => [
                $temp->getTable() => [
                    'FKEY' => [
                        static::getTable() => 'concernedpersons_id',
                        $temp->getTable() => 'id'
                    ]
                ]
            ],
            'ORDER' => self::getTable() . '.id DESC'
        ];
    }

    static function getRecordsReliesRequest($instID)
    {
        $link_table = str_replace("_Item", "", __CLASS__);
        $temp = new $link_table();
        return [
            'FROM' => self::getTable(),
            'SELECT' => [
                self::getTable() . '.id',
                self::getTable() . '.id as linkid',
                self::getTable() . '.comment',
                self::getTable() . '.itemtype as itemtype',
                self::getTable() . '.itemtype1 as itemtype1',
                self::getTable() . '.items_id as items_id',
                self::getTable() . '.items_id1 as items_id1',
            ],
            'WHERE' => [
                static::getTable() . '.concernedpersons_id' => $instID,
                static::getTable() . '.itemtype1' => 'PluginDlteamsProcessedData',
            ],

            'LEFT JOIN' => [
                $temp->getTable() => [
                    'FKEY' => [
                        static::getTable() => 'concernedpersons_id',
                        $temp->getTable() => 'id'
                    ]
                ]
            ],
            'ORDER' => self::getTable() . '.id DESC'
        ];
    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }

    public function post_purgeItem()
    {
        global $DB;
//        purge relations
        if ($this->fields["itemtype"] && $this->fields["items_id"]) {

            $relation_item_str = $this->fields["itemtype"] . "_Item";
            if (!class_exists($relation_item_str))
                $relation_item_str = "PluginDlteams" . $relation_item_str;
            $relation_item = new $relation_item_str();

            $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

            $criteria = [
                "itemtype" => PluginDlteamsConcernedPerson::class,
                "items_id" => $this->fields["concernedpersons_id"],
                $relation_column_id => $this->fields["items_id"],
                "comment" => $this->fields["comment"]
            ];

            $result = $relation_item->getFromDBByCrit($criteria);
            if ($result) {
                if ($result && $relation_item->fields["itemtype1"] && $relation_item->fields["items_id1"]) {
                    $DB->update(
                        $relation_item->getTable(),
                        [
                            "itemtype" => null,
                            "items_id" => 0,
                        ],
                        [
                            'id' => $relation_item->fields["id"]
                        ]
                    );

                    Session::addMessageAfterRedirect("Relation " . $relation_item::getTypeName() . " mis à jour avec succès");
                } else {
                    $relation_item->deleteByCriteria($criteria);
                    Session::addMessageAfterRedirect("Relation " . $relation_item::getTypeName() . " supprimé avec succès");
                }
            }
        }


//        traitement de la seconde relation
        if($this->fields["itemtype1"] && $this->fields["items_id1"]){
            $relation_item_str = $this->fields["itemtype1"] . "_Item";
            if (!class_exists($relation_item_str))
                $relation_item_str = "PluginDlteams" . $relation_item_str;
            $relation_item = new $relation_item_str();


            $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

            $criteria = [
                "itemtype1" => PluginDlteamsConcernedPerson::class,
                "items_id1" => $this->fields["concernedpersons_id"],
                "items_id" => $this->fields["items_id1"],
                "comment" => $this->fields["comment"]
            ];

            $result = $relation_item->getFromDBByCrit($criteria);

            if ($result) {
                if ($result && $relation_item->fields["itemtype"] && $relation_item->fields["items_id"]) {
                    $DB->update(
                        $relation_item->getTable(),
                        [
                            "itemtype1" => null,
                            "items_id1" => 0,
                        ],
                        [
                            'id' => $relation_item->fields["id"]
                        ]
                    );
                    Session::addMessageAfterRedirect("Relation " . $relation_item::getTypeName() . " mis à jour avec succès");
                } else {
                    $relation_item->deleteByCriteria($criteria);
                    Session::addMessageAfterRedirect("Relation " . $relation_item::getTypeName() . " supprimé avec succès");
                }
            }
        }
    }

    public function post_updateItem($history = 1)
    {
        global $DB;
        if($this->fields["itemtype"] && $this->fields["items_id"]){
            $relation_item_str = $this->fields["itemtype"] . "_Item";
            if(!class_exists($relation_item_str))
                $relation_item_str = "PluginDlteams".$relation_item_str;
            $relation_item = new $relation_item_str();
            $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype"]))) . "s_id";

            $criteria = [
                "itemtype" => PluginDlteamsConcernedPerson::class,
                "items_id" => $this->fields["concernedpersons_id"],
                $relation_column_id => $this->fields["items_id"],
                "itemtype1" => $this->fields["itemtype1"],
                "items_id1" => $this->fields["items_id1"],
                "comment" => $this->oldvalues["comment"]
            ];

            $relation_item->getFromDBByCrit($criteria);

            $DB->delete(
                $relation_item->getTable(),
                $criteria,
            );
            $relation_item->add([
                ...$criteria,
                "comment" => $this->fields["comment"]
            ]);
        }


        if($this->fields["itemtype1"] && $this->fields["items_id1"]){
            $relation_item_str = $this->fields["itemtype1"] . "_Item";
            if(!class_exists($relation_item_str))
                $relation_item_str = "PluginDlteams".$relation_item_str;
            $relation_item = new $relation_item_str();
            $relation_column_id = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", $this->fields["itemtype1"]))) . "s_id";

            $criteria = [
                "itemtype" => $this->fields["itemtype"],
                "items_id" => $this->fields["items_id"],
                $relation_column_id => $this->fields["items_id1"],
                "itemtype1" => PluginDlteamsConcernedPerson::class,
                "items_id1" => $this->fields["concernedpersons_id"],
                "comment" => $this->oldvalues["comment"]
            ];

            $relation_item->getFromDBByCrit($criteria);

            $DB->delete(
                $relation_item->getTable(),
                $criteria
            );
            $relation_item->add([
                ...$criteria,
                "comment" => $this->fields["comment"]
            ]);
        }
    }
}

?>

<script>
    $(document).ready(function () {

        var form = document.querySelector('[id^="massaction_"][id$="' + dynamicID + '"]');

        form.addEventListener('submit', function (event) {
            console.log(event);
            if (!confirm('Voulez-vous vraiment soumettre ce formulaire ?')) {
                event.preventDefault();
            }
        });

        $('select[name=items_id]').on('change', function () {
            // alert($(this).val());
            if ($(this).val() != '0') {
                document.getElementById('update_comment_textarea').style.display = 'block';

            } else {
                document.getElementById('update_comment_textarea').style.display = 'none';
            }
            //
        });


        $('select[name=itemtype]').on('change', function () {
            if ($(this).val() != '0') {
                document.getElementById('update_comment_textarea').style.display = 'block';
            } else {
                document.getElementById('update_comment_textarea').style.display = 'none';
            }
            //
        });

    });


</script>
