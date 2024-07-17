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

class PluginDlteamsDeliverableNotification extends CommonDBTM
{

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

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
//        $iterator = PluginDlteamsDocumentsRGPD::getRequest($item);
//        $nbitem = count($iterator);
        $iterator = static::getRequest($item);
        return static::createTabEntry(__('Notification', 'dlteams'), count($iterator));

    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        $id = $item->fields['id'];

        echo "<form name='form' method='POST' action=\"" . Toolbox::getItemTypeFormURL(__CLASS__) . "\">";

        echo "<div class='spaced' id='tabsbody'>";

        echo "<table class='tab_cadre_fixe' id='mainformtable'>";
        echo "<tbody>";
        echo "<tr class='headerRow'>";
        echo "<th colspan='3' class=''>" . __("Notification", 'dlteams') . "</th>";
        echo "</tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%' style='text-align: right'>" . __("Objet du mail de notification", 'dlteams') . "</td>";
        echo "<td width='75%'>";
        $object = htmlspecialchars($item->fields["object_notification"]??"");
        echo "<input type='text' style='width:71%' maxlength=250 name='object' value='$object' required>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%' style='text-align: right'>" . __("Texte de notification", 'dlteams') . "</td>";
        echo "<td width='75%'>";

        /**add by me**/
        Html::textarea([
            "name" => "notification_text",
            "cols" => 100,
            "rows" => 5,
            "value" => $item->fields["text_notification"],
            "enable_richtext" => true
        ]);
        /**add by me**/
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%' style='text-align: right'>" . __("Objet du mail d'approbation", 'dlteams') . "</td>";
        echo "<td width='75%'>";
        $object = htmlspecialchars($item->fields["object_approval"]??"");
        echo "<input type='text' style='width:71%' maxlength=250 name='object_approbation' value='$object' required>";
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%' style='text-align: right'>" . __("Texte d'approbation", 'dlteams') . "</td>";
        echo "<td width='75%'>";

        /**add by me**/
        Html::textarea([
            "name" => "approbation_text",
            "cols" => 100,
            "rows" => 5,
            "value" => $item->fields["text_approval"],
            "enable_richtext" => true
        ]);
        /**add by me**/
        echo "</td>";
        echo "</tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%' style='text-align: right'>" . __("Destinataire", 'dlteams') . "</td>";
        echo "<td width='75%'>";

        /**add by me**/
        $types = [
            Contact::class,
            User::class
        ];
        echo "<div style='display: flex; gap: 5px; align-items: center'>";
        Dropdown::showSelectItemFromItemtypes([
            'itemtypes' => $types,
            'checkright' => true,
            'ajax_page' => "/marketplace/dlteams/ajax/dlteamsDropdownAllItem.php"
        ]);


//        echo "<div style='display: flex; gap: 2px; align-items: center'>";
//        echo "<input type='checkbox' class='form-check-input' name='demande_approbation'>";
//        echo "<label>Demander approbation</label>";
//        echo "</div>";

        echo "</div>";
        /**add by me**/
        echo "</td>";
        echo "</tr>";


        echo "</table>";
        echo "</div>";
        echo "<input type='hidden' name='deliverable_id' value=\"" . $item->fields["id"] . "\">";

        echo "<input type='hidden' name='action' value=\"print\">";

        echo "<input type='hidden' name='guid_value' value='55'>";


        echo "<div id='notification_submit_section'>";
        echo "<div style='width: 100%; display: flex; justify-content: center; gap: 5px;'>";
        echo "<input type='submit' class='submit btn-secondary' name='save_notification_data' value='" . __("Enregistrer", 'dlteams') . "' />";
        echo "<input type='submit' class='submit btn-secondary' name='send_notification' value='" . __("Envoyer la notification", 'dlteams') . "' />";
        echo "<input type='submit' class='submit' name='send_approval' value='" . __("Demander une approbation", 'dlteams') . "' />";
        echo "<input type='submit' class='submit' name='send_notification_and_approval' value='" . __("Notifier + demander une approbation", 'dlteams') . "' />";
        echo "</div>";

        echo "</div>";
        Html::closeForm();

        echo "<br/>";
        echo "<br/>";

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr class='tab_bg_2'><th colspan='4'>" . __("Notification log", 'dlteams') .
            "</th>";
        echo "</tr>";
        echo "<th colspan='2'></th></tr>";
        echo "</table>";


        $canedit = $item->can($id, UPDATE); // canedit booleen = true
        $iterator = static::getRequest($item);

        $number = count($iterator);
        $item_list = [];
        foreach ($iterator as $data) {
            array_push($item_list, $data);
        }
        $rand = mt_rand();
        echo "<div class='spaced' > ";
        if ($canedit && count($iterator) > 0) {
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


        $header_end .= "<th> " . __("Name") . " </th > ";
        $header_end .= "<th> " . __("Type") . " </th > ";
        $header_end .= "<th> " . __("Date envoi") . " </th > ";
//        $header_end .= "<th> " . __("Demande d'approbation") . " </th > ";
        $header_end .= "<th> " . __("Heure d'approbation") . " </th > ";
        $header_end .= "<th> " . "" . " </th > ";
        $header_end .= "</tr > ";

        /*        highlight_string("<?php\n\$data =\n" . var_export($item_list, true) . ";\n?>");*/
//        die();

        echo $header_begin . $header_top . $header_end;
        foreach ($item_list as $data) {
            echo "<tr class='tab_bg_1' > ";

            if ($canedit && $number) {
                echo "<td width = '10' > ";
                Html::showMassiveActionCheckBox(__CLASS__, $data['linkid']);
                echo "</td > ";
            }


            $itemtype = $data["itemtype"];
            $oi = new $itemtype();
            $oi->getFromDB($data['items_id']);
            $name = "<a target = '_blank' href = \"" . $itemtype::getFormURLWithID($data['items_id']) . "\">" . $oi->fields["name"] . "</a>";

            echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
            echo ">" . $name . "</td>";


            echo "<td class='left'>" . $data['itemtype']::getTypeName() . " </td>";

            echo "<td class='left'>" . date('d/m/Y H:i:s', strtotime($data["date_creation"])) . "</td>";
//            echo "<td class='left'>";
//            echo $data["approval_request"] ? "Oui" : "Non";
//            echo "</td>";

            if($data["date_approval"])
            echo "<td class='left'>" . date('d/m/Y H:i:s', strtotime($data["date_approval"])) . "</td>";
            else
                echo "<td class='left'>--</td>";


            echo "<td class='left'>";
            $id = $data["id"];
            echo "<a style='border: solid 1px; border-radius: 2px; padding: 2px; border-color: #6d9dc8; color: #6d9dc8; cursor: pointer;' class='btn-showcomment' data-comment-id='$id'><i class='fas fa-eye' title='" . __("show comment", 'dlteams') . "'></i></a>";
            echo "</td>";


            echo "</tr>";
        }
        echo "</table>";

        echo "
        <script>
    $(document).ready(function () {
        $('.btn-showcomment').on('click', function () {
            var comment_id = $(this).attr('data-comment-id');


            glpi_ajax_dialog({
                dialogclass: 'modal-xl',
                bs_focus: false,
                url: '/marketplace/dlteams/ajax/get_deliverable_notification_data.php',
                params: {
                    comment_id,
                },
                title: i18n.textdomain('dlteams').__('Commentaires', 'dlteams'),
                close: function () {

                },
                fail: function () {
                    displayAjaxMessageAfterRedirect();
                }
            });
        });
    });

</script>
        ";

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


    /**
     * Display approbation view
     *
     * @param string $token approbation token
     *
     * @return void
     */
    public function showApprobationView($token, $error = false)
    {

        $di = new PluginDlteamsDeliverable_Item();
        $dix = $di->find([
            "approval_token" => $token
        ]);

        $di_list = null;
        foreach ($dix as $deliverable_item) {
            $di_list = $deliverable_item;
        }
        if ($di_list) {
//            $link_item =
        }

        Session::addMessageAfteRredirect(__('Merci d\'avoir approuvé ce document.'));
        Session::addMessageAfteRredirect(__('A bientôt.'));

        \Glpi\Application\View\TemplateRenderer::getInstance()->display('@dlteams/pages/approbation_form_base.html.twig', [
            'title' => __('Approbation documentaire'),
            'messages_only' => true,
            'error' => $error
        ]);
    }


    public static function getRequest(PluginDlteamsDeliverable $item)
    {
        $request = [
            "SELECT" => [
                PluginDlteamsDeliverable_Item::getTable() . ".*",
                PluginDlteamsDeliverable_Item::getTable() . ".id as linkid",
            ],
            "FROM" => PluginDlteamsDeliverable_Item::getTable(),
            "OR" => [
                [
                    "itemtype" => "Contact",
                    "deliverables_id" => $item->fields["id"]
                ],
                [
                    "itemtype" => "User",
                    "deliverables_id" => $item->fields["id"]
                ]
            ],
        ];
        global $DB;
        return $DB->request($request);
    }

    public function __construct()
    {
        static::forceTable(PluginDlteamsDeliverable_Item::getTable());
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
            "itemtype" => PluginDlteamsDeliverable::class,
            "items_id" => $this->fields["deliverables_id"],
            $relation_column_id => $this->fields["items_id"],
            "comment" => $this->fields["comment"]
        ];

        $relation_item->deleteByCriteria($criteria);
    }


    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'clone';
//        $forbidden[] = 'MassiveAction:purge';
        $forbidden[] = 'MassiveAction:update';
        $forbidden[] = 'MassiveAction:add_transfer_list';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }

}

?>
