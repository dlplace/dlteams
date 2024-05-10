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

class PluginDlteamsDataCarrier extends CommonDBTM implements
    PluginDlteamsExportableInterface
{
    use PluginDlteamsExportable;

    static $rightname = 'plugin_dlteams_datacarrier';
    public $dohistory = true;
    protected $usenotepad = true;

    static function getTypeName($nb = 0)
    {
        return _n("Data carrier", "Data carriers", $nb, 'dlteams');
    }

    public function getForbiddenStandardMassiveAction()
    {
        $forbidden = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        $forbidden[] = 'purge';
        $forbidden[] = 'delete';
        $forbidden[] = 'add_transfer_list';
        $forbidden[] = 'add_note';
        $forbidden[] = 'Document_Item:add';
        $forbidden[] = 'Document_Item:remove';
        $forbidden[] = 'Contract_Item:add';
        $forbidden[] = 'Contract_Item:remove';
        $forbidden[] = 'Infocom:activate';
        $forbidden[] = 'MassiveAction:amend_comment';
        return $forbidden;
    }


    // @see CommonDBTM::getSpecificMassiveActions()
    public function getSpecificMassiveActions($checkitem = NULL)
    {
        $actions = parent::getSpecificMassiveActions($checkitem);
        // add a single massive action
        $class = __CLASS__;
        $action_key = "delete_dlteams_action";
        $action_label = _n("Delete dlteams relation", "Delete dlteams relations", 0, "dlteams");

        // permet l'action ajout d'une licence
        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . $action_key] = $action_label;
        /*if (static::canUpdate()) {
            Computer_Item::getMassiveActionsForItemtype($actions, __CLASS__, 0, $checkitem);
            $actions += [
                'Item_SoftwareLicense' . MassiveAction::CLASS_ACTION_SEPARATOR . 'add'
               => "<i class='ma-icon fas fa-key'></i>" .
                  _x('button', 'Add a license')
            ];
            // KnowbaseItem_Item::getMassiveActionsForItemtype($actions, __CLASS__, 0, $checkitem);
        }*/
        return $actions;
    }

    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                        array $ids)
    {
        switch ($ma->getAction()) {
            case 'delete_dlteams_action':
                $input = $ma->getInput();
                foreach ($ids as $id) {
                    if ($item->getFromDB($id)
                        && $item->doIt($input)) {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                    } else {
                        $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        $ma->addMessage(__("Something went wrong"));
                    }
                }
                return;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    function showForm($id, $options = [])
    {

        global $CFG_GLPI;
        $this->initForm($id, $options);
        $this->showFormHeader($options);

        echo "<table, th, td width='100%' border='1px solid black'>";
        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td style='text-align:left' width='15%'>" . __("Name", 'dlteams') . "</td>";
        echo "<td>";
        $name = Html::cleanInputText($this->fields['name']);
        echo "<input type='text' style='width:70%' name='name' required value='" . $name . "'>" . "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td style='text-align:left' width='15%'>" . __("Storage description", 'dlteams') . "</td>";
        echo "<td>";
        $content = Html::cleanInputText($this->fields['content']);
        echo "<textarea style='width:70%' rows='3' name='content' >" . $content . "</textarea>" . "</td>";
        echo "</tr>";
        echo "</table>";

        echo "<table width='100%'>";
        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td style='text-align:left' width='15%'>" . __("Type of storage", 'dlteams') . "</td>";
        echo "<td>";
        PluginDlteamsDataCarrierCategory::dropdown([
            'addicon' => PluginDlteamsDataCarrierCategory::canCreate(),
            'name' => 'plugin_dlteams_datacarriercategories_id',
            'width' => '200px',
            'value' => $this->fields['plugin_dlteams_datacarriercategories_id']
        ]);
        echo "</td>";

        echo "<td style='text-align:left' width='15%'>" . __("Storage location", 'dlteams') . "</td>";
        echo "<td>";
        Location::dropdown([
            'addicon' => Location::canCreate(),
            'name' => 'locations_id',
            'width' => '200px',
            'value' => $this->fields['locations_id']
        ]);
        echo "</td>";
        echo "<td width='20%' style='text-align:right'>" . " " . "</td>";
        echo "</tr>";
        echo "<tr>" . "<td width='15%' style='text-align:right'>" . "  " . "</td>" . "</tr>";
        echo "</table>";

        echo "<table width='100%'>";
        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";

        echo "<td style='text-align:left' width='15%'>" . __("Technology, engine", 'dlteams') . "</td>";
        echo "<td>";
        PluginDlteamsServerType::dropdown([
            'addicon' => PluginDlteamsServerType::canCreate(),
            'name' => 'plugin_dlteams_servertypes_id',
            'width' => '200px',
            'value' => $this->fields['plugin_dlteams_servertypes_id']
        ]);
        echo "</td>";

        echo "<td style='text-align:left' width='15%'>" . __("Editor, brand", 'dlteams') . "</td>";
        echo "<td>";
        Manufacturer::dropdown([
            'addicon' => Manufacturer::canCreate(),
            'name' => 'manufacturers_id',
            'width' => '200px',
            'value' => $this->fields['manufacturers_id']
        ]);
        echo "</td>";
        echo "<td width='20%' style='text-align:right'>" . " " . "</td>";
        echo "</tr>";
        echo "<tr>" . "<td width='15%' style='text-align:right'>" . "  " . "</td>" . "</tr>";
        echo "</table>";


        echo "<table width='100%'>";
        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td style='text-align:left' width='15%'>" . __("Type of place", 'dlteams') . "</td>";
        echo "<td>";
        PluginDlteamsDataCarrierHosting::dropdown([
            'addicon' => PluginDlteamsDataCarrierHosting::canCreate(),
            'name' => 'plugin_dlteams_datacarrierhostings_id',
            'width' => '200px',
            'value' => $this->fields['plugin_dlteams_datacarrierhostings_id']
        ]);
        echo "</td>";

        echo "<td style='text-align:left' width='15%'>" . __("Storage management", 'dlteams') . "</td>";
        echo "<td>";
        PluginDlteamsDataCarrierManagement::dropdown([
            'addicon' => PluginDlteamsDataCarrierManagement::canCreate(),
            'name' => 'plugin_dlteams_datacarriermanagements_id',
            'width' => '200px',
            'value' => $this->fields['plugin_dlteams_datacarriermanagements_id']
        ]);
        echo "</td>";
        echo "<td width='20%' style='text-align:right'>" . " " . "</td>";
        echo "</tr>";
        echo "<tr>" . "<td width='15%' style='text-align:right'>" . "  " . "</td>" . "</tr>";
        echo "</table>";

        /*echo "<td style='text-align:left' width='15%'>". __("Data type", 'dlteams') . "</td>";
        echo "<td>";
        PluginDlteamsDataCarrierType::dropdown([
          'addicon'  => PluginDlteamsDataCarrierType::canCreate(),
          'name' => 'plugin_dlteams_datacarriertypes_id',
          'width' => '200px',
          'value' => $this->fields['plugin_dlteams_datacarriertypes_id']
        ]);
        echo "</td>";*/


        /*if(!is_numeric($this->fields['plugin_dlteams_datacarriertypes_id'])){
        PluginDlteamsUtils::select2Dropdown([
           'itemtype' => "PluginDlteamsDataCarrierType",
           'addicon'  => PluginDlteamsDataCarrierType::canCreate(),
           'name' => 'plugin_dlteams_datacarriertypes_id[]',
           'entity' => $this->fields['entities_id'],
           'display_emptychoice' => false,
           'width' => '85%',
           'values' => json_decode($this->fields['plugin_dlteams_datacarriertypes_id']),
           'specific_tags' => [
              'multiple' => true
           ],
        ]);
        }else{
            PluginDlteamsUtils::select2Dropdown([
           'itemtype' => "PluginDlteamsDataCarrierType",
           'addicon'  => PluginDlteamsDataCarrierType::canCreate(),
           'name' => 'plugin_dlteams_datacarriertypes_id[]',
           'entity' => $this->fields['entities_id'],
           'display_emptychoice' => false,
           'width' => '85%',
           'specific_tags' => [
              'multiple' => true
           ],
        ]);
        }*/


        echo "<table width='100%'>";
        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";

        echo "<td style='text-align:left' width='15%'>" . __("Group responsible for maintenance", 'dlteams') . "</td>";
        echo "<td>";
        $randDropdown = mt_rand();
        Group::dropdown([
            'addicon' => Group::canCreate(),
            'name' => 'groups_id',
            'value' => $this->fields['groups_id'],
            'entity' => $this->fields["entities_id"],
            //'right'  => 'all',
            'width' => '200px',
            'rand' => $randDropdown
        ]);
        echo "</td>";

        echo "<td style='text-align:left' width='15%'>" . __("Contact person for administration", 'dlteams') . "</td>";
        echo "<td>";
        $randDropdown = mt_rand();
        User::dropdown([
            'addicon' => User::canCreate(),
            'name' => 'users_id',
            'value' => $this->fields['users_id'],
            'entity' => $this->fields["entities_id"],
            'width' => '200px',
            'right' => 'all',
            'rand' => $randDropdown,
        ]);
        echo "</td>";
        echo "<td width='18%' style='text-align:right'>" . " " . "</td>";
        echo "</tr>";
        echo "<tr>" . "<td width='15%' style='text-align:right'>" . "  " . "</td>" . "</tr>";
        echo "</table>";

        echo "<table width='100%'>";
        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";


        echo "<td style='text-align:left' width='15%'>" . __("Service provider responsible for maintenance", 'dlteams') . "</td>";
        echo "<td>";
        Supplier::dropdown([
            'addicon' => Supplier::canCreate(),
            'name' => 'suppliers_id',
            'width' => '200px',
            'value' => $this->fields['suppliers_id']
        ]);
        echo "</td>";
        echo "<td width='20%' style='text-align:right'>" . " " . "</td>";
        echo "</tr>";
        echo "<tr>" . "<td width='15%' style='text-align:right'>" . "  " . "</td>" . "</tr>";
        echo "</table>";

        echo "<table width='100%'>";
        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td style='text-align:left' width='15%'>" . __("Logs", 'dlteams') . "</td>";
        echo "<td>";
        $logs = Html::cleanInputText($this->fields['logs']);
        echo "<textarea style='width:70%' rows='3' name='logs' >" . $logs . "</textarea>" . "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td style='text-align:left' width='15%'>" . __("Means of access", 'dlteams') . "</td>";
        echo "<td>";
        $meansofaccess = Html::cleanInputText($this->fields['meansofaccess']);
        echo "<textarea style='width:70%' rows='3' name='meansofaccess' >" . $meansofaccess . "</textarea>" . "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td style='text-align:left' width='15%'>" . __("Backup and versions", 'dlteams') . "</td>";
        echo "<td>";
        $backupandversions = Html::cleanInputText($this->fields['backupandversions']);
        echo "<textarea style='width:70%' rows='3' name='backupandversions' >" . $backupandversions . "</textarea>" . "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td style='text-align:left' width='15%'>" . __("Alerts and monitoring", 'dlteams') . "</td>";
        echo "<td>";
        $alertmonitoring = Html::cleanInputText($this->fields['alertmonitoring']);
        echo "<textarea style='width:70%' rows='3' name='alertmonitoring' >" . $alertmonitoring . "</textarea>" . "</td>";
        echo "</tr>";
        echo "</table>";

        echo "<table width='100%'>";
        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td width='15%'>" . __("URL", 'dlteams') . "</td>";
        echo "<td>";
        Html::autocompletionTextField($this, "url");
        echo "&nbsp;<a href='>" . $this->fields["url"] . "'><i class=\"fas fa-link\"></i></a>" . "</td>";
        echo "<td width='30%' style='text-align:right'>" . " " . "</td>";
        echo "</tr>";
        echo "</table>";

        echo "<table width='100%'>";
        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>" . " " . "</td>";
        echo "<td style='text-align:left' width='15%'>" . __("Comment", 'dlteams') . "</td>";
        echo "<td>";
        $comment = Html::cleanInputText($this->fields['comment']);
        echo "<textarea style='width:70%;' rows=3 name='comment' >" . $comment . "</textarea>" . "</td>";
        echo "</tr>";
        echo "</table>";

        /*echo "<tr class='tab_bg_1'><td width='50%' class='right'>";
             echo __("Type base de données", 'dlteams');
             echo "</td><td width='50%' class='left'><i class='fas fa-dolly'></i>&nbsp;";
             PluginDlteamsUtils::select2Dropdown([
                'itemtype' => "PluginDlteamsDataCarrierType",
                'addicon'  => PluginDlteamsDataCarrierType::canCreate(),
                'name' => 'plugin_dlteams_datacarriertypes_id[]',
                'entity' => $this->fields['entities_id'],
                'display_emptychoice' => false,
                'width' => '85%',
                'values' => json_decode($this->fields['plugin_dlteams_datacarriertypes_id']),
                'specific_tags' => [
                   'multiple' => true
                ],
                ]);
                echo "</td></tr>";*/

        $this->showFormButtons($options);

        /*****************************************Section access path (appliance) - BEGIN**/
        $id = $this->fields['id'];
        if (!$this->can($id, READ)) {
            return false;
        }
        $canedit = $this->can($id, UPDATE);
        $rand = mt_rand(1, mt_getrandmax());
        global $CFG_GLPI;
        global $DB;
        $itemtype1 = $this->getType();
        $itemtype2 = "Appliance";

        $iterator = $DB->request([
            'SELECT' => [
                'glpi_plugin_dlteams_datacarriers_items.id as linkid',
                'glpi_plugin_dlteams_datacarriers_items.comment as comment',
                'glpi_appliances.id as id',
                'glpi_appliances.name as name',
            ],
            'FROM' => 'glpi_plugin_dlteams_datacarriers_items',
            'JOIN' => [
                'glpi_appliances' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_datacarriers_items' => 'items_id',
                        'glpi_appliances' => 'id'
                    ]
                ]
            ],
            'WHERE' => [
                'glpi_plugin_dlteams_datacarriers_items.datacarriers_id' => $this->fields['id'],
                'glpi_plugin_dlteams_datacarriers_items.itemtype' => $itemtype2,
            ],
            'ORDER' => ['name ASC'],
        ], "", true);

        $number = count($iterator);

        $items_list = [];
        $used = [];
        //var_dump(count($iterator));
        // while ($data = $iterator->next()) {
        foreach ($iterator as $id => $data) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($canedit) {
            echo "<form name='allitemitem_form$rand' id='allitemitem_form$rand' method='post'
         action='" . Toolbox::getItemTypeFormURL(PluginDlteamsAllItem::class) . "'>";
            echo "<input type='hidden' name='itemtype' value='" . $this->getType() . "' />";
            echo "<input type='hidden' name='items_id' value='" . $this->getID() . "' />";
            echo "<input type='hidden' name='itemtype1' value='" . Appliance::getType() . "' />";
            echo "<input type='hidden' name='entities_id' value='" . $this->getID(Entity::class) . "' />";
            // echo "<input type='hidden' name='comment' value='".$this->fields['comment']."' />";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th style='text-align:center!important'>" . __("Moyens d'accès à l'entrepôt", 'dlteams') . "</th></tr>";
            echo "</table>";

            echo "<table class='tab_cadre_fixe'>";
				echo "<td style='text-align:right'>" . __("Utiliser un applicatif", 'dlteams') . "</td>";
				echo "<td>";
				Appliance::dropdown(['addicon' => Appliance::canCreate(),'name' => 'items_id1','width' => '300px']);
				echo "</td>";
				echo "<tr><td style='text-align:right'>" . __("Commentaires", 'dlteams') . " " . "</td>";
				echo "<td>" . "<textarea style='width:100%' rows='1' name='comment' >" . Html::cleanInputText($this->fields['comment']) . "</textarea>" . "</td>";
				echo "<td class='left'><input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin:0px auto!important'>" . "</td>";
				echo "</tr>";
				// disposer d'un applicatif et/ou d'un type de compte (et/ou avoir un badge) et/ou être dans un lieu et/ou disposer un matériel et/ou être connecté à un réseau

				echo "<td style='text-align:right'>" . __("Se trouver dans un lieu ", 'dlteams') . "</td>";
				echo "<td>";
				Location::dropdown(['addicon' => Location::canCreate(),'name' => 'items_id1','width' => '300px']);
				echo "</td>";
				echo "<tr><td style='text-align:right'>" . __("Commentaires", 'dlteams') . " " . "</td>";
				echo "<td>" . "<textarea style='width:100%' rows='1' name='comment' >" . Html::cleanInputText($this->fields['comment']) . "</textarea>" . "</td>";
				echo "<td class='left'><input type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin:0px auto!important'>" . "</td>";
				echo "</tr>";
            echo "</table>";

            Html::closeForm();
        }

        if ($iterator) {
            echo "<div class='spaced'>";
            if ($canedit && $number) {
                Html::openMassiveActionsForm('mass' . PluginDlteamsAllItem::class . $rand);
                $massive_action_params = ['container' => 'mass' . PluginDlteamsAllItem::class . $rand,
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
                $header_top .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsAllItem::class . $rand);
                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsAllItem::class . $rand);
                $header_end .= "</th>";
            }

            $header_end .= "<th width='20%' style='text-align:left'>" . __("Type d'accès", 'dlteams') . "</th>";
            // $header_end .= "<th width='20%'>" . __("Type", 'dlteams') . "</th>";
            $header_end .= "<th width='80%' style='text-align:left'>" . __("Description du chemin", 'dlteams') . "</th>";
            $header_end .= "</tr>";

            echo $header_begin . $header_top . $header_end;
            foreach ($items_list as $data) {
                if ($data['name']) {
                    echo "<tr class='tab_bg_1'>";

                    if ($canedit && $number) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox(PluginDlteamsAllItem::class, $data['linkid']);
                        echo "</td>";
                    }

                    $link = $data['name'];
                    if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
                        $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
                    }
                    $name = "<a target='_blank' href=\"" . Appliance::getFormURLWithID($data['id']) . "\">" . $link . "</a>";

                    echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
                    echo ">" . $name . "</td>";

                    // echo "<td class='left'>" . $data['type'] . " </td>";

                    echo "<td class='left'>" . $data['comment'] . "</td>";
                    echo "</tr>";
                }
            }

            if ($iterator->count() > 10) {
                echo $header_begin . $header_bottom . $header_end;
            }
            echo "</table>";

            if ($canedit && $number) {
                //$massive_action_params['ontop'] = false;
                //Html::showMassiveActions($massive_action_params);
                Html::closeForm();
            }

            echo "</div>";
        }
        /****************************************Section access path - END **/


        return true;
    }

    function prepareInputForAdd($input)
    {
        $input['users_id_creator'] = Session::getLoginUserID();
        return parent::prepareInputForAdd($input);
    }

    function prepareInputForUpdate($input)
    {
        $input['users_id_lastupdater'] = Session::getLoginUserID();
        return parent::prepareInputForUpdate($input);
    }

    function cleanDBonPurge()
    {
        /*$rel = new PluginDlteamsRecord_MotifEnvoi();
        $rel->deleteByCriteria(['plugin_dlteams_concernedpersons_id' => $this->fields['id']]);*/
    }

    function rawSearchOptions()
    {
        $tab = [];

        $tab[] = [
            'id' => 'common',
            'name' => __("Characteristics")
        ];

        $tab[] = [
            'id' => '1',
            'table' => $this->getTable(),
            'field' => 'name',
            'name' => __("Name"),
            'datatype' => 'itemlink',
            'massiveaction' => false,
            'autocomplete' => true,
        ];

        $tab[] = [
            'id' => '2',
            'table' => $this->getTable(),
            'field' => 'id',
            'name' => __("ID"),
            'massiveaction' => false,
            'datatype' => 'number',
        ];

        $tab[] = [
            'id' => '3',
            'table' => $this->getTable(),
            'field' => 'comment',
            'name' => __("Comments"),
            'datatype' => 'text',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '4',
            'table' => $this->getTable(),
            'field' => 'content',
            'name' => __("Contenu"),
            'datatype' => 'text',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '5',
            'table' => 'glpi_entities',
            'field' => 'completename',
            'name' => __("Entity"),
            'datatype' => 'dropdown',
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '6',
            'table' => $this->getTable(),
            'field' => 'is_recursive',
            'name' => __("Child entities"),
            'datatype' => 'bool',
            'massiveaction' => false,
        ];

        $tab[] = [
            'id' => '7',
            'table' => 'glpi_plugin_dlteams_datacarriercategories',
            'field' => 'name',
            'name' => __("Type of storage", 'dlteams'),
            'datatype' => 'text',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '8',
            'table' => 'glpi_plugin_dlteams_datacarrierhostings',
            'field' => 'name',
            'name' => __("Type of place", 'dlteams'),
            'datatype' => 'text',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '9',
            'table' => 'glpi_plugin_dlteams_datacarriermanagements',
            'field' => 'name',
            'name' => __("Storage management", 'dlteams'),
            'datatype' => 'text',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '10',
            'table' => 'glpi_locations',
            'field' => 'name',
            'name' => __("Location"),
            'datatype' => 'text',
            'toview' => true,
            'massiveaction' => true,
        ];

        $tab[] = [
            'id' => '11',
            'table' => $this->getTable(),
            'field' => 'logs',
            'name' => __("Logs", 'dlteams'),
            'datatype' => 'text',
            'toview' => true,
            'massiveaction' => true,
        ];
        $tab[] = [
            'id' => '12',
            'table' => $this->getTable(),
            'field' => 'meansofaccess',
            'name' => __("Means of access", 'dlteams'),
            'datatype' => 'text',
            'toview' => true,
            'massiveaction' => true,
        ];


        /*$tab[] = [
           'id' => '101',
           'table' => 'users',
           'field' => 'users_id_responsible',
           'name' => __("Responsable du traitement"),
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
        ];*/

        return $tab;
    }

    public function defineTabs($options = [])
    {
        $ong = [];
        $ong = array();
        $this->addDefaultFormTab($ong)
			->addStandardTab('PluginDlteamsRecord_Item', $ong, $options)
            ->addStandardTab('PluginDlteamsDataCatalog_Item', $ong, $options)
			//->addStandardTab('PluginDlteamsDataCarrierStorage_Item', $ong, $options)
            ->addStandardTab('PluginDlteamsDataCarrier_Item', $ong, $options)
            ->addStandardTab('Appliance_Item', $ong, $options)
            ->addStandardTab('Appliance_Item_Relation', $ong, $options)
            ->addStandardTab('PluginDlteamsObject_document', $ong, $options)
            ->addStandardTab('ManualLink', $ong, $options)
            ->addStandardTab('Contract_Item', $ong, $options)
            ->addStandardTab(PluginDlteamsTicket_Item::class, $ong, $options)
            ->addStandardTab('KnowbaseItem_Item', $ong, $options)
            ->addImpactTab($ong, $options)
            ->addStandardTab('Notepad', $ong, $options)
            ->addStandardTab('Log', $ong, $options);
        return $ong;
    }

    function exportToDB($subItems = [])
    {
        if ($this->isNewItem()) {
            return false;
        }

        $export = $this->fields;
        return $export;
    }

    public static function importToDB(PluginDlteamsLinker $linker, $input = [], $containerId = 0, $subItems = [])
    {
        $item = new self();
        $originalId = $input['id'];
        unset($input['id']);
        $input['entities_id'] = $_POST['entities_id'];;
        $input['comment'] = str_replace(['\'', '"'], "", $input['comment']);
        $input['name'] = str_replace(['\'', '"'], "", $input['name']);
        $input['content'] = str_replace(['\'', '"'], "", $input['content']);
        $itemId = $item->add($input);
        if ($itemId === false) {
            $typeName = strtolower(self::getTypeName());
            throw new ImportFailureException(sprintf(__('failed to copy the %1$s record', 'dlteams'), $input['name']));
        }
        return $itemId;
    }

    public function deleteObsoleteItems(CommonDBTM $container, array $exclude)
    {
    }

}
