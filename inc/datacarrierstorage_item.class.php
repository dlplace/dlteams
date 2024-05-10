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
class PluginDlteamsDataCarrierStorage_Item extends CommonDBTM
{
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
    static function getTypeName($nb = 0)
    {
        return __("Stockage");
    }

    public static $items_id_1;
    public static $itemtypes_list;

    public function __construct()
    {
        static::$items_id_1 = strtolower(str_replace("PluginDlteams", "", str_replace("_Item", "", PluginDlteamsDataCarrier_Item::class))) . "s_id";
        static::$itemtypes_list = [
            'Computer',
            'Datacenter',
            'NetworkEquipment',
            'Peripheral',
            'Printer',
            'Phone',
			'PluginDlteamsPhysicalStorage'
        ];
        self::forceTable(PluginDlteamsDataCarrier_Item::getTable());
        parent::__construct();
    }

    // affichage de l'onglet et de son nom
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (Session::haveRight($item::$rightname, READ)) {
            if ($_SESSION['glpishow_count_on_tabs']) {
                return static::createTabEntry(static::getTypeName(2), 0);
            }
            return static::getTypeName(2);
        }

        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        self::showItems($item);
    }

    /** Show items links to a document
	* @param $doc Document object
    * @return void */
    public static function showItems(PluginDlteamsDataCarrier $object_item)
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
        $key = array_search("PluginDlteamsDataCarrier", $types);
        unset($types[$key]);
        $rand = mt_rand();


        if ($canedit) {
            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
            echo "<input type='hidden' name='" . static::$items_id_1 . "' value='$instID'>";
            echo "<input type='hidden' name='itemtype1' value='" . str_replace("_Item", "", "PluginDlteamsDataCarrier_Item") . "'>";
            echo "<input type='hidden' name='items_id1' value='" . $instID . "'>";
            echo "<input type='hidden' name='link_element' value='" . true . "'>";

            echo "<table class='tab_cadre_fixe'>";
            $title = "Related objects";
            $entitled = "Indicate the objects related to this element";
            echo "<tr class='tab_bg_2'><th colspan='3'>" . __($title, 'dlteams') .
                "</th>";
            echo "</tr>";

            echo "<tr class='tab_bg_1'><td class='left' width='40%' style='text-wrap: nowrap'>" . __($entitled, 'dlteams');
            echo "</td><td width='40%' class='left'>";
            $types = static::$itemtypes_list;


            Dropdown::showSelectItemFromItemtypes(['itemtypes' => $types,
                'entity_restrict' => ($object_item->fields['is_recursive'] ? getSonsOf('glpi_entities', $object_item->fields['entities_id'])
                    : $object_item->fields['entities_id']),
                'checkright' => true,
                'used' => $used
            ]);
            unset($types);
            echo "</td><td width='20%' class='left'><input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
            echo "</td></tr>";
            echo "<tr class='tab_bg_1'><td class='comment-cell'>";
            echo __("Comment");
            echo "<br/><br/>";
            echo "<textarea type='text' style='width:100%' maxlength=1000 rows='3' name='comment' class='comment_legalbasi_item'></textarea>";
            echo "</td>";
            echo "</table>";
            Html::closeForm();
        }

        echo "<style>
                .comment-cell {width: 35%;}
                @media (max-width: 767px) {.comment-cell {width: 100%;}}
              </style>";

//        var_dump(self::getTable());
        $items = self::getItemsRequest($object_item);

        if (!count($items)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
            if ($canedit) {
                Html::openMassiveActionsForm('mass' . PluginDlteamsDataCarrier_Item::class . $rand);
                $massiveactionparams = [
                    'num_displayed' => min($_SESSION['glpilist_limit'], count($items)),
                    'container' => 'mass' . PluginDlteamsDataCarrier_Item::class . $rand
                ];
                Html::showMassiveActions($massiveactionparams);
            }

            echo "<table class='tab_cadre_fixehov'>";
            $header = "<tr>";
            if ($canedit) {
                $header .= "<th width='10'>";
                $header .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsDataCarrier_Item::class . $rand);
                $header .= "</th>";
            }
            $header .= "<th>" . __("Element") . "</th>";
            $header .= "<th>" . __("Objet") . "</th>";
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
                    Html::showMassiveActionCheckBox(PluginDlteamsDataCarrier_Item::class, $row["id"]);
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
//                Html::showMassiveActions($massiveactionparams);
            }
            if ($canedit) {
                Html::closeForm();
            }

        }
    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }

    public static function getItemsRequest(CommonDBTM $object_item)
    {
        global $DB;
        $link_table = str_replace("_Item", "", PluginDlteamsDataCarrier_Item::class);
        $temp = new $link_table();

        $query = [
            'FROM' => self::getTable(),
            'SELECT' => [
                self::getTable() . '.id',
                self::getTable() . '.id as linkid',
                self::getTable() . '.comment',
                self::getTable() . '.itemtype as itemtype',
                self::getTable() . '.items_id as items_id',
            ],
//            'WHERE' => [
//                static::getTable() . '.' . static::$items_id_1 => $object_item->fields['id']
//            ],
            'LEFT JOIN' => [
                $temp->getTable() => [
                    'FKEY' => [
                        static::getTable() => static::$items_id_1,
                        $temp->getTable() => 'id'
                    ]
                ]
            ],
            'ORDER' => [$temp->getTable().'.name ASC'],
        ];

        foreach (static::$itemtypes_list as $itemtype){
            $query['WHERE']['OR'][] = [
                'itemtype' => $itemtype,
                static::getTable() . '.' . static::$items_id_1 => $object_item->fields['id']
            ];
        }
        $items = $DB->request($query);

        return iterator_to_array($items);
    }
}
