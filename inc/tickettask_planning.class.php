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

class PluginDlteamsTicketTask_Planning extends CommonDBTM {
    static public $itemtype_2 = PluginDlteamsTicketTask::class;

    public function __construct() {
        parent::__construct();
        self::forceTable(TicketTask::getTable());
    }

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


    static function getTypeName($nb = 0) {
        return __("Planifications", 'dlteams');
    }

    // affichage de l'onglet et de son nom
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
        switch ($item->getType()) {
            case static::$itemtype_2:
			// var_dump(Session::haveRight($item::$rightname, READ));
			// if (Session::haveRight($item::$rightname, READ)) {
                        if ($_SESSION['glpishow_count_on_tabs']) {
                            return static::createTabEntry(static::getTypeName(), static::countForItem($item));
                        }
                        return static::getTypeName();
			// }
                break;

//            default:
//                if (!$withtemplate) {
//                    if (Session::haveRight($item::$rightname, READ)) {
//                        if ($_SESSION['glpishow_count_on_tabs']) {
//                            return static::createTabEntry(static::getTypeName(2), static::countForItem($item));
//                        }
//                        return static::getTypeName(2);
//                    }
//                }
//                break;
        }

        return '';
    }

    // comptage du nombre de liaison entre les 2 objets dans la table de l'objet courant
    static function countForItem(CommonDBTM $item) {
        $dbu = new DbUtils();
        return $dbu->countElementsInTable(static::getTable(), ['tickettasks_id' => $item->getID()]);
    }

    public static function getItemsRequest(CommonDBTM $object_item) {
        global $DB;
        $link_table = str_replace("_Item", "", __CLASS__);
        $temp = new $link_table();

        $items = $DB->request([
            'FROM' => self::getTable(),
            'SELECT' => [
                TicketTask::getTable() . '.id',
                TicketTask::getTable() . '.*',
                TicketTask::getTable() . '.id as linkid',
            ],
            'WHERE' => [
                TicketTask::getTable() . '.tickettasks_id' => $object_item->fields['id']
            ],
            'ORDER' => TicketTask::getTable() . '.id DESC'
        ]);

        return iterator_to_array($items);
    }

    public function update(array $input, $history = 1, $options = []) {
        if(isset($input["plugin_dlteams_protectivemeasures_id"]))
            $input["protectivemeasures_id"] = $input["plugin_dlteams_protectivemeasures_id"];

        /*        highlight_string("<?php\n\$data =\n" . var_export($input, true) . ";\n?>");*/
		//        die();
        parent::update($input, $history, $options);
        return true;
    }

    public function defineTabs($options = []) {
        $ong = [];
        $this->addDefaultFormTab($ong);
        $this->addImpactTab($ong, $options);
        return $ong;
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
        switch ($item->getType()) {
            case static::$itemtype_2:
                self::showItems($item);
                break;
        }
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
    public static function showItems(PluginDlteamsTicketTask $object_item) {
        global $DB;
//        $instID = $object_item->fields['id'];
//        if (!$object_item->can($instID, READ)) {
//            return false;
//        }
//        $canedit = $object_item->can($instID, UPDATE);
//        // for a measure,
//        // don't show here others protective measures associated to this one,
//        // it's done for both directions in self::showAssociated
//        $types_iterator = [];
//        $number = count($types_iterator);
//
//        $used = [];
//        $types = PluginDlteamsItemType::getTypes();
////        Enlève le choix de L'objet LegalBasi dans la dropdown qui affiche la liste des objets
//        $key = array_search(static::$itemtype_2, $types);
//        unset($types[$key]);
//        $rand = mt_rand();
//
//
//        if ($canedit) {
//            echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
//            action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";
//            echo "<input type='hidden' name='" . static::$items_id_1 . "' value='$instID'>";
//            echo "<input type='hidden' name='itemtype1' value='" . str_replace("_Item", "", __CLASS__) . "'>";
//            echo "<input type='hidden' name='items_id1' value='" . $instID . "'>";
//
//            echo "<table class='tab_cadre_fixe'>";
//            $title = "Related objects";
//            $entitled = "Indicate the objects related to this element";
//            echo "<tr class='tab_bg_2'><th colspan='2'>" . __($title, 'dlteams') .
//                "<br><i style='font-weight: normal'>" .
//                "</i></th>";
//            echo "</tr>";
//
//            echo "<tr class='tab_bg_1'><td class='left' width='40%'>" . __($entitled, 'dlteams');
//            echo "</td><td width='40%' class='left'>";
//            $types = PluginDlteamsItemType::getTypes();
//            $key = array_search("PluginDlteamsLegalBasi", $types);
//            unset($types[$key]);
//            Dropdown::showSelectItemFromItemtypes(['itemtypes' => $types,
//                'entity_restrict' => ($object_item->fields['is_recursive'] ? getSonsOf('glpi_entities', $object_item->fields['entities_id'])
//                    : $object_item->fields['entities_id']),
//                'checkright' => true,
//                'used' => $used
//            ]);
//            unset($types);
//            echo "</td><td width='20%' class='left'><input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
//            echo "</td></tr>";
//            echo "<tr class='tab_bg_1'><td width='35%' class=''>";
//            echo __("Comment");
//            echo "<br/><br/>";
//            echo "<textarea type='text' style='width:100%' maxlength=1000 rows='3' name='comment' class='comment_legalbasi_item'></textarea>";
//            echo "</td>";
//            echo "</table>";
//            Html::closeForm();
//        }



//        var_dump(self::getTable());
        if (!$object_item->fields["tickettasks_id"]) {
            $queryString = http_build_query([
                "itemtype" => TicketTask::class,
                "items_id" => $object_item->fields["id"],
            ]);
			// && $this->fields["begin"]
            echo "<button name='add_tickettask_plannification' id='add_tickettask_plannification' style='width: fit-content;' class='btn btn-primary add_tickettask_plannification'>" .
                _x('button', 'Ajouter planification') . "</button>";
			// echo "</div>";


            echo "<script>";
            $queryString = http_build_query([
                "itemtype" => TicketTask::class,
                "items_id" => $object_item->fields["id"],
            ]);

            echo "
        const observer = new MutationObserver((mutations) => {
        
        
        $('.add_tickettask_plannification').off('click').click(function(e){
                        e.preventDefault();
                        
                        var modalId = glpi_ajax_dialog({
                            dialogclass: 'modal-lg',
                            bs_focus: false,
                            url: '/marketplace/dlteams/ajax/tickettask_plannif.php?" . $queryString . "',
                            title: i18n.textdomain('dlteams').__('Plannifier', 'dlteams'),
                            close: function () {
            
                            },
                            fail: function () {
                                // displayAjaxMessageAfterRedirect();
                            }
                        });
                    });
                    
                    
             $('#subtaskplanplanif').off('click').click(function(e){
                       e.preventDefault();
                                              
                                    $('#subtaskviewplanPlanif').load('/ajax/planning.php', {
                                        action: 'add_event_classic_form',
                                        form: 'followups',
                                        entity: " . Session::getActiveEntity() . ",
                                        itemtype: '" . TicketTask::class . "',
                                        items_id: '" . $object_item->fields["id"] . "'
                                    });
                                    $(this).hide();
                   });
        });
        
        // Configuration de l'observateur : surveiller l'ajout et la suppression d'éléments, ainsi que les changements d'attribut
        const config = {
            childList: true,
            attributes: true,
            subtree: true // Observer les mutations sur des descendants également
        };
        
        // Commencer l'observation sur l'élément body pour couvrir tout le DOM
        observer.observe(document.body, config);
        ";
            echo "</script>";
        }

        $items = self::getItemsRequest($object_item);
        if (!count($items)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
//            if ($canedit) {
//                Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
//                $massiveactionparams = [
//                    'num_displayed' => min($_SESSION['glpilist_limit'], count($items)),
//                    'container' => 'mass' . __CLASS__ . $rand
//                ];
//                Html::showMassiveActions($massiveactionparams);
//            }

            echo "<table class='tab_cadre_fixehov'>";
            $header = "<tr>";
//            if ($canedit) {
//                $header .= "<th width='10'>";
//                $header .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
//                $header .= "</th>";
//            }
            $header .= "<th>" . __("Id") . "</th>";
            $header .= "<th>" . __("Statut") . "</th>";
            $header .= "<th>" . __("Acteur") . "</th>";
            $header .= "<th>" . __("Groupe") . "</th>";
            $header .= "<th>" . __("Date prévue") . "</th>";
            $header .= "<th>" . __("Durée prévue") . "</th>";
            $header .= "<th>" . __("Date planif") . "</th>";
            $header .= "<th>" . __("Durée planif") . "</th>";

            // colonne non generique
            $header .= "<th>" . __("Fin") . "</th>";
            $header .= "</tr>";
            echo $header;

            foreach ($items as $row) {
                $id = "<a target='_blank' href=\"" . TicketTask::getFormURLWithID($row["id"]) . "\">" . $row["id"]. "</a>";
                echo "<tr lass='tab_bg_1'>";
					// if ($canedit) {
					// echo "<td>";
					// Html::showMassiveActionCheckBox(__CLASS__, $row["id"]);
					// echo "</td>";
					//                }
					echo "<td>" . $id . "</td>";

					// Planning::dropdownState('state', $row["state"], false, [
					// 'width' => '250px',
					// ])
					echo "<td>" .Planning::getStatusIcon($row["state"]) . "</td>";

					// acteur
					$user = new User();
					$user->getFromDB($row["users_id"]);
					$acteurname = "<a target='_blank' href=\"" . User::getFormURLWithID($row["users_id"]) . "\">" . $user->getName(). "</a>";
					echo "<td>" . $acteurname . "</td>";

					// groupe
					$group = new Group();
					$group->getFromDB($row["groups_id_tech"]);
					$groupname = isset($group->fields["name"])? "<a target='_blank' href=\"" . Group::getFormURLWithID($row["groups_id_tech"]) . "\">" . $group->fields["name"]. "</a>":"--";
					echo "<td>" . $groupname . "</td>";

					echo "<td>" . date('d-m-Y H:i', strtotime($row["date"]??"")) . "</td>";
					echo "<td>" . static::convertirTemps($row["estimate_duration"]). "</td>";
					echo "<td>" . date('d-m-Y H:i', strtotime($row["begin"]??"")) . "</td>";
					echo "<td>" . static::convertirTemps($row["actiontime"]). "</td>";
					echo "<td>" . $row["end"]. "</td>";
                echo "</tr>";
            }
            echo $header;
            echo "</table>";

//            if ($canedit && count($items)) {
//                $massiveactionparams['ontop'] = false;
//                Html::showMassiveActions($massiveactionparams);
//            }
//            if ($canedit) {
//                Html::closeForm();
//            }
        }
    }

    public static function convertirTemps($tempsEnSecondes) {
        if($tempsEnSecondes && is_integer($tempsEnSecondes)){
            $heures = floor($tempsEnSecondes / 3600);
            $minutes = floor(($tempsEnSecondes % 3600) / 60);
            $secondes = $tempsEnSecondes % 60;

            return sprintf("%02d:%02d:%02d", $heures, $minutes, $secondes);
        }
        return "";
    }


    public function getForbiddenStandardMassiveAction() {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
//        $forbidden[] = 'MassiveAction:update';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }

}
