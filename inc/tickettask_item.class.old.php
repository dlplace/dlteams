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

use Glpi\Toolbox\Sanitizer;

class PluginDlteamsTicketTask_Item extends CommonDBTM
{

    static $rightname = 'plugin_dlteams_tickettask_items';
    public $dohistory = true;
    protected $usenotepad = true;

    static public $itemtype_2 = 'PluginDlteamsDataCatalog';
    static public $itemtype_1;
    public static $items_id_1;
    public static $title;
    public static $sub_title;
    public static $table_match_str = [];


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

    static function getTypeNameForClass($nb = 0)
    {
        return __("Projets effectifs", 'dlteams');
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case PluginDlteamsTicketTask::class:
                $ong = [];
                //$count = count(static::getUtilisateursProjetsRequest($item));
                $ong[] = static::createTabEntry(static::getTypeNameForClass(), 0);

                return $ong;
                break;
        }
    }

    /**Show items links to a document
     * @param $doc Document object
     * @return void*/
    public static function showProjetEffectifs(PluginDlteamsDataCatalog $object_item)
    {
        global $DB;
        $instID = $object_item->fields['id'];
        if (!$object_item->can($instID, READ)) {
            return false;
        }
        $canedit = $object_item->can($instID, UPDATE);

        $types_iterator = [];
        $types = PluginDlteamsItemType::getTypes();
//        Enlève le choix de L'objet LegalBasi dans la dropdown qui affiche la liste des objets
        $key = array_search("PluginDlteamsDataCatalog", $types);
        unset($types[$key]);
        $rand = mt_rand();

        $items = self::getUtilisateursEffectifRequest($object_item);
        if (!count($items)) {
            echo "<table class='tab_cadre_fixe'><tr><th>" . __('No item found') . "</th></tr>";
            echo "</table>";
        } else {
            echo "<table class='tab_cadre_fixehov'>";
            $header = "<tr>";
//            if ($canedit) {
//                $header .= "<th width='10'>";
//                $header .= Html::getCheckAllAsCheckbox('mass' . __CLASS__ . $rand);
//                $header .= "</th>";
//            }
            $header .= "<th>" . __("Intitulé") . "</th>";
            $header .= "<th>" . __("Nom") . "</th>";
            $header .= "<th>" . __("Prénom") . "</th>";
            $header .= "<th>" . __("Type") . "</th>";
            $header .= "<th>" . __("Comment") . "</th>";
            $header .= "</tr>";
            echo $header;

            foreach ($items as $row) {
                $item = new $row['itemtype'](); //plante si itemtype is null
                $item->getFromDB($row['items_id']);
                $name = "<a target='_blank' href=\"" . $item::getFormURLWithID($item->getField('id')) . "\">" . $item->getField('name') . "</a>";
                echo "<tr lass='tab_bg_1'>";
//                if ($canedit) {
//                    echo "<td>";
//                    Html::showMassiveActionCheckBox(__CLASS__, $row["id"]);
//                    echo "</td>";
//                }
                echo "<td>" . $name . "</td>";

                $nom = "";
                $prenom = "";
                if ($row["itemtype"]::getTypeName() == __("User")) {
                    $nom = $item->getField('realname');
                    $prenom = $item->getField('firstname');
                }
                if ($row["itemtype"]::getTypeName() == __("Contact")) {
                    $nom = $item->getField('name');
                    $prenom = $item->getField('firstname');
                }
                echo "<td>" . $nom . "</td>";
                echo "<td>" . $prenom . "</td>";
                echo "<td>" . $row["itemtype"]::getTypeName() . "</td>";
                echo "<td>" . $row['comment'] . "</td>";
                echo "</tr>";
            }
            echo $header;
            echo "</table>";

        }
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
//            si onglet objet datacatalog, on affiche tous les utilisateurs et groupes et contacts et suppliers qui on acces a ce catalogue
            case static::$itemtype_2:
//                utilisateurs effectifs
                self::showProjetEffectifs($item);
        }
    }

    public static function getUtilisateursProjetsRequest(CommonDBTM $item)
    {
        global $DB;        //var_dump ($table_name, $columnid_name);
        $table_item_name = getTableForItemType(static::$itemtype_2 . "_Item");

        $query = [];

        if ($DB->fieldExists($table_item_name, 'comment')) {
            $query['SELECT'][] = $table_item_name . '.comment AS comment';
        }

        $iterator = $DB->request($query);
        $temp = [];

        foreach ($iterator as $id => $data) {

            if ($data["itemtype"]) {
                $item_object = null;
                $item_str = $data["itemtype"];
                $item_object = new $item_str();
                $item_object->getFromDB($data["items_id"]);

                if (isset($item_object->fields["entities_id"])) {
                    array_push($temp, $data);
                }
            }
        }

        $users = [];
//      get users that have acces to this catalog through account or keys
        foreach ($temp as $datacatalog_item) {

            $request2 = [
                'SELECT' => [
                    PluginDlteamsAccountKey_Item::getTable() . ".id as linkid",
                    PluginDlteamsAccountKey_Item::getTable() . ".*",
                ],
                'FROM' => PluginDlteamsAccountKey_Item::getTable(),
                'OR' => [
                    [
                        'itemtype' => User::class,
                        'accountkeys_id' => $datacatalog_item["items_id"]
                    ],
                    [
                        'itemtype' => Group::class,
                        'accountkeys_id' => $datacatalog_item["items_id"]
                    ],
                    [
                        'itemtype' => Supplier::class,
                        'accountkeys_id' => $datacatalog_item["items_id"]
                    ],
                    [
                        'itemtype' => Contact::class,
                        'accountkeys_id' => $datacatalog_item["items_id"]
                    ],
                ],
            ];

            $iterator = $DB->request($request2);
            $temp = [];

            foreach ($iterator as $id => $data) {
                if ($data["itemtype"]) {
                    array_push($users, $data);
                }
            }
        }
        return $users;
    }
}