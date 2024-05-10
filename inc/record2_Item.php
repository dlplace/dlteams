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

class Record2_Item extends CommonDBRelation
{
   // From CommonDBRelation
    public static $itemtype_1    = "PluginDlteamsRecord";
    public static $items_id_1    = 'records_id';
    public static $take_entity_1 = false;

    public static $itemtype_2    = 'itemtype';
    public static $items_id_2    = 'items_id';
    public static $take_entity_2 = true;

    /**
     * @since 9.2
     *
     **/
    public function getForbiddenStandardMassiveAction()
    {
        $forbidden   = parent::getForbiddenStandardMassiveAction();
        $forbidden[] = 'update';
        return $forbidden;
    }


    /**
     * @param CommonDBTM $item
     */
    public static function cleanForItem(CommonDBTM $item)
    {
        $temp = new self();
        $temp->deleteByCriteria(['itemtype' => $item->getType(),
            'items_id' => $item->getField('id')
        ]);
    }

    /**
     * @param CommonGLPI $item
     * @param int $withtemplate
     * @return string
     */
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$withtemplate) {
            if (
                $item->getType() == 'PluginDlteamsRecord'
                && count(PluginDlteamsRecord::getTypes(false))
            ) {
                if ($_SESSION['glpishow_count_on_tabs']) {
                    return self::createTabEntry(
                        _n('Associated item', 'Associated items', Session::getPluralNumber()),
                        self::countForMainItem($item)
                    );
                }
                return _n('Associated item', 'Associated items', Session::getPluralNumber());
            } else if (
                in_array($item->getType(), PluginDlteamsRecord::getTypes(true))
                && PluginDlteamsRecord::canView()
            ) {
                if ($_SESSION['glpishow_count_on_tabs']) {
                    return self::createTabEntry(
                        PluginDlteamsRecord::getTypeName(2),
                        self::countForItem($item)
                    );
                }
                return PluginDlteamsRecord::getTypeName(2);
            }
        }
        return '';
    }


    /**
     * @param CommonGLPI $item
     * @param int $tabnum
     * @param int $withtemplate
     * @return bool
     */
    public static function displayTabContentForItem(
        CommonGLPI $item,
        $tabnum = 1,
        $withtemplate = 0
    ) {

        if ($item->getType() == 'PluginDlteamsRecord') {
            self::showForRecord($item);
        } else if (in_array($item->getType(), PluginDlteamsRecord::getTypes(true))) {
            self::showForItem($item);
        }
        return true;
    }


    /**
     * @param $records_id
     * @param $items_id
     * @param $itemtype
     * @return bool
     */
    public function getFromDBbyRecordsAndItem($records_id, $items_id, $itemtype)
    {

        $record  = new self();
        $records = $record->find([
            'records_id' => $records_id,
            'itemtype'        => $itemtype,
            'items_id'        => $items_id
        ]);
        if (count($records) != 1) {
            return false;
        }

        $reco         = current($records);
        $this->fields = $reco;

        return true;
    }

    /**
     * Link a record to an item
     *
     * @since 9.2
     * @param $values
     */
    public function addItem($values)
    {

        $this->add(['records_id' => $values["records_id"],
            'items_id'        => $values["items_id"],
            'itemtype'        => $values["itemtype"]
        ]);
    }

    /**
     * Delete a record link to an item
     *
     * @since 9.2
     *
     * @param integer $records_id the record ID
     * @param integer $items_id the item's id
     * @param string $itemtype the itemtype
     */
    public function deleteItemByRecordsAndItem($records_id, $items_id, $itemtype)
    {

        if (
            $this->getFromDBbyRecordsAndItem(
                $records_id,
                $items_id,
                $itemtype
            )
        ) {
            $this->delete(['id' => $this->fields["id"]]);
        }
    }

    /**
     * Show items linked to a record
     *
     * @since 9.2
     *
     * @param Record $record Record object
     *
     * @return void|boolean (display) Returns false if there is a rights error.
     **/
    public static function showForRecord(Record $record)
    {

        $instID = $record->fields['id'];
        if (!$record->can($instID, READ)) {
            return false;
        }
        $canedit = $record->can($instID, UPDATE);
        $rand    = mt_rand();

        $types_iterator = self::getDistinctTypes($instID, ['itemtype' => Record::getTypes(true)]);
        $number = count($types_iterator);

        if (Session::isMultiEntitiesMode()) {
            $colsup = 1;
        } else {
            $colsup = 0;
        }

        if ($canedit) {
            echo "<div class='firstbloc'>";
            echo "<form method='post' name='records_form$rand'
                     id='records_form$rand'
                     action='" . Toolbox::getItemTypeFormURL(__CLASS__) . "'>";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'>";
            echo "<th colspan='" . ($canedit ? (5 + $colsup) : (4 + $colsup)) . "'>" .
               __('Add an item') . "</th></tr>";

            echo "<tr class='tab_bg_1'><td colspan='" . (3 + $colsup) . "' class='center'>";
            Dropdown::showSelectItemFromItemtypes(
                ['items_id_name'   => 'items_id',
                    'itemtypes'       => Record::getTypes(true),
                    'entity_restrict' => ($record->fields['is_recursive']
                                      ? getSonsOf(
                                          'glpi_entities',
                                          $record->fields['entities_id']
                                      )
                                       : $record->fields['entities_id']),
                    'checkright'      => true,
                ]
            );
            echo "</td><td colspan='2' class='center' class='tab_bg_1'>";
            echo Html::hidden('records_id', ['value' => $instID]);
            echo Html::submit(_x('button', 'Add'), ['name' => 'add']);
            echo "</td></tr>";
            echo "</table>";
            Html::closeForm();
            echo "</div>";
        }

        echo "<div class='spaced'>";
        if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            $massiveactionparams = [];
            Html::showMassiveActions($massiveactionparams);
        }
        echo "<table class='tab_cadre_fixe'>";
        echo "<tr>";

        if ($canedit && $number) {
            echo "<th width='10'>" .
            Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand) . "</th>";
        }

        echo "<th>" . _n('Type', 'Types', 1) . "</th>";
        echo "<th>" . __('Name') . "</th>";
        if (Session::isMultiEntitiesMode()) {
            echo "<th>" . Entity::getTypeName(1) . "</th>";
        }
        echo "<th>" . __('Serial number') . "</th>";
        echo "<th>" . __('Inventory number') . "</th>";
        echo "</tr>";

        foreach ($types_iterator as $type_row) {
            $itemtype = $type_row['itemtype'];

            if (!($item = getItemForItemtype($itemtype))) {
                continue;
            }

            if ($item->canView()) {
                $iterator = self::getTypeItems($instID, $itemtype);

                if (count($iterator)) {
                    Session::initNavigateListItems($itemtype, Record::getTypeName(2) . " = " . $record->fields['name']);
                    foreach ($iterator as $data) {
                        $item->getFromDB($data["id"]);
                        Session::addToNavigateListItems($itemtype, $data["id"]);
                        $ID = "";
                        if ($_SESSION["glpiis_ids_visible"] || empty($data["name"])) {
                            $ID = " (" . $data["id"] . ")";
                        }

                        $link = $itemtype::getFormURLWithID($data["id"]);
                        $name = "<a href=\"" . $link . "\">" . $data["name"] . "$ID</a>";

                        echo "<tr class='tab_bg_1'>";

                        if ($canedit) {
                            echo "<td width='10'>";
                            Html::showMassiveActionCheckBox(__CLASS__, $data["linkid"]);
                            echo "</td>";
                        }
                        echo "<td class='center'>" . $item->getTypeName(1) . "</td>";
                        echo "<td class='center' " . (isset($data['is_deleted']) && $data['is_deleted'] ? "class='tab_bg_2_2'" : "") .
                        ">" . $name . "</td>";
                        if (Session::isMultiEntitiesMode()) {
                            $entity = ($item->isEntityAssign() ?
                            Dropdown::getDropdownName("glpi_entities", $data['entity']) :
                            '-');
                             echo "<td class='center'>" . $entity . "</td>";
                        }
                        echo "<td class='center'>" . (isset($data["serial"]) ? "" . $data["serial"] . "" : "-") . "</td>";
                        echo "<td class='center'>" . (isset($data["otherserial"]) ? "" . $data["otherserial"] . "" : "-") . "</td>";
                        echo "</tr>";
                    }
                }
            }
        }
        echo "</table>";

        if ($canedit && $number) {
            $paramsma = [
                'ontop' => false,
            ];
            Html::showMassiveActions($paramsma);
            Html::closeForm();
        }
        echo "</div>";
    }

    /**
     * Show records associated to an item
     *
     * @since 9.2
     *
     * @param $item  CommonDBTM object for which associated records must be displayed
     * @param $withtemplate (default 0)
     *
     * @return bool
     */
    public static function showForItem(CommonDBTM $item, $withtemplate = 0)
    {

        $ID = $item->getField('id');

        if (
            $item->isNewID($ID)
            || !Record::canView()
            || !$item->can($item->fields['id'], READ)
        ) {
            return false;
        }

        $record  = new Record();

        if (empty($withtemplate)) {
            $withtemplate = 0;
        }

        $canedit      = $item->canAddItem('Record');
        $rand         = mt_rand();
        $is_recursive = $item->isRecursive();

        $iterator = self::getListForItem($item);
        $number   = $iterator->numrows();
        $i        = 0;

        $records = [];
        $used         = [];

        foreach ($iterator as $data) {
            $records[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($canedit && $withtemplate < 2) {
            if ($item->maybeRecursive()) {
                $is_recursive = $item->fields['is_recursive'];
            } else {
                $is_recursive = false;
            }
            $entity_restrict = getEntitiesRestrictCriteria(
                "glpi_plugin_dlteams_records",
                'entities_id',
                $item->fields['entities_id'],
                $is_recursive
            );

            $nb = countElementsInTable(
                'glpi_plugin_dlteams_records',
                [
                    'is_deleted'  => 0
                ] + $entity_restrict
            );

            echo "<div class='firstbloc'>";

            if (Record::canView() && (!$nb || ($nb > count($used)))) {
                echo "<form name='record_form$rand'
                        id='record_form$rand'
                        method='post'
                        action='" . Toolbox::getItemTypeFormURL('Record_Item')
                  . "'>";
                echo "<table class='tab_cadre_fixe'>";
                echo "<tr class='tab_bg_1'>";
                echo "<td colspan='4' class='center'>";
                echo Html::hidden(
                    'entities_id',
                    ['value' => $item->fields['entities_id']]
                );
                 echo Html::hidden(
                     'is_recursive',
                     ['value' => $is_recursive]
                 );
                 echo Html::hidden(
                     'itemtype',
                     ['value' => $item->getType()]
                 );
                 echo Html::hidden(
                     'items_id',
                     ['value' => $ID]
                 );
                if ($item->getType() == 'Ticket') {
                     echo Html::hidden('tickets_id', ['value' => $ID]);
                }
                 Dropdown::show('Record', ['entity' => $item->fields['entities_id'],
                     'is_recursive'       => $is_recursive,
                     'used'               => $used
                 ]);

                 echo "</td><td class='center' width='20%'>";
                 echo Html::submit(_sx('button', 'Associate'), ['name' => 'add']);
                 echo "</td>";
                 echo "</tr>";
                 echo "</table>";
                 Html::closeForm();
            }

            echo "</div>";
        }

        echo "<div class='spaced table-responsive'>";
        if ($canedit && $number && ($withtemplate < 2)) {
            $massiveactionparams = ['num_displayed' => $number];
            Html::openMassiveActionsForm('mass' . __CLASS__ . $rand);
            Html::showMassiveActions($massiveactionparams);
        }
        echo "<table class='tab_cadre_fixe'>";

        echo "<tr>";
        if ($canedit && $number && ($withtemplate < 2)) {
            echo "<th width='10'>";
            echo Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
            echo "</th>";
        }
        echo "<th>" . __('Name') . "</th>";
        if (Session::isMultiEntitiesMode()) {
            echo "<th>" . Entity::getTypeName(1) . "</th>";
        }
        echo "<th>" . _n('Type', 'Types', 1) . "</th>";
        echo "<th>" . __('DNS name') . "</th>";
        echo "<th>" . __('DNS suffix') . "</th>";
        echo "<th>" . __('Creation date') . "</th>";
        echo "<th>" . __('Expiration date') . "</th>";
        echo "<th>" . __('Status') . "</th>";
        echo "</tr>";

        $used = [];

        if ($number) {
            Session::initNavigateListItems(
                'Record',
                sprintf(
                    __('%1$s = %2$s'),
                    $item->getTypeName(1),
                    $item->getName()
                )
            );

            foreach ($records as $data) {
                $recordID = $data["id"];
                $link = NOT_AVAILABLE;

                if ($record->getFromDB($recordID)) {
                    $link = $record->getLink();
                }

                Session::addToNavigateListItems('Record', $recordID);

                $used[$recordID] = $recordID;

                echo "<tr class='tab_bg_1" . ($data["is_deleted"] ? "_2" : "") . "'>";
                if ($canedit && ($withtemplate < 2)) {
                    echo "<td width='10'>";
                    Html::showMassiveActionCheckBox(__CLASS__, $data["linkid"]);
                    echo "</td>";
                }
                echo "<td class='center'>$link</td>";
                if (Session::isMultiEntitiesMode()) {
                     echo "<td class='center'>" . Dropdown::getDropdownName("glpi_entities", $data['entities_id']) .
                     "</td>";
                }
                echo "<td class='center'>";
                echo Dropdown::getDropdownName(
                    "glpi_recordtypes",
                    $data["recordtypes_id"]
                );
                 echo "</td>";
                 echo "<td class='center'>" . $data["dns_name"] . "</td>";
                 echo "<td class='center'>" . $data["dns_suffix"] . "</td>";
                 echo "<td class='center'>" . Html::convDate($data["date_creation"]) . "</td>";
                if (
                    $data["date_expiration"] <= date('Y-m-d')
                     && !empty($data["date_expiration"])
                ) {
                     echo "<td class='center'>";
                     echo "<div class='deleted'>" . Html::convDate($data["date_expiration"]) . "</div>";
                     echo "</td>";
                } else if (empty($data["date_expiration"])) {
                    echo "<td class='center'>" . __('Does not expire') . "</td>";
                } else {
                    echo "<td class='center'>" . Html::convDate($data["date_expiration"]) . "</td>";
                }
                echo "<td class='center'>";
                echo Dropdown::getDropdownName("glpi_states", $data["states_id"]);
                echo "</td>";
                echo "</tr>";
                $i++;
            }
        }

        echo "</table>";
        if ($canedit && $number && ($withtemplate < 2)) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
        }
        echo "</div>";
    }
}
