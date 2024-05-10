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


include("../../../inc/includes.php");

global $DB;

if (!isset($_GET['id'])) {
    $_GET['id'] = "";
}

$audit = new PluginDlteamsAudit();
$DB->beginTransaction();
if (isset($_POST['add'])) {

    $audit->check(-1, CREATE, $_POST);
    $id = $audit->add($_POST);

    $add_count = addAuditcategoryResources($id);
    Session::addMessageAfterRedirect(sprintf("%s ressources ajouté(e)s", $add_count));
    $DB->commit();
    Html::redirect($audit->getFormURLWithID($id));


} else if (isset($_POST['update'])) {

    /*    highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//    die();
    $audit->check($_POST['id'], UPDATE);
    $audit->update($_POST);
    $count_delete = deleteAuditcategoryResources($_POST['id']);
    $add_count = addAuditcategoryResources($_POST['id']);
    Session::addMessageAfterRedirect(sprintf("%s ressources supprimé(e)s", $count_delete));
    Session::addMessageAfterRedirect(sprintf("%s ressources ajouté(e)s", $add_count));
    $DB->commit();
    Html::back();

} else if (isset($_POST['delete'])) {

    $audit->check($_POST['id'], DELETE);
    $audit->delete($_POST);
    $delete_count = deleteAuditcategoryResources($_POST['id']);
    Session::addMessageAfterRedirect(sprintf("%s ressources supprimé(e)s", $delete_count));
    $DB->commit();
    $audit->redirectToList();
} else {

    $audit->checkGlobal(READ);

    if (Session::getCurrentInterface() == 'central') {
        Html::header(PluginDlteamsAudit::getTypeName(2), '', 'dlteams', 'PluginDlteamsmenu', 'audit');
    } else {
        Html::helpHeader(PluginDlteamsAudit::getTypeName(0));
    }

    $audit->display(['id' => $_GET['id']]);

    if (Session::getCurrentInterface() == 'central') {
        Html::footer();
    } else {
        Html::helpFooter();
    }
}

function addAuditcategoryResources($audit_id)
{
    global $DB;
    if (isset($_POST["plugin_dlteams_auditcategories_id"])) {
        $query = [
            "FROM" => PluginDlteamsAuditCategory_Item::getTable(),
            "WHERE" => [
                "itemtype" => KnowbaseItem::class,
                "auditcategories_id" => $_POST["plugin_dlteams_auditcategories_id"]
            ],
        ];

        $items_iterator = $DB->request($query);
        $count_copied = 0;
        foreach ($items_iterator as $item) {
            $audit_item = new PluginDlteamsAudit_Item();
            $data = [
                "audits_id" => $audit_id,
                "itemtype" => KnowbaseItem::class,
                "items_id" => $item["items_id"],
                "comment" => $item["comment"]
            ];

            $audit_item->add($data);


            $knowbaseitem_item = new KnowbaseItem_Item();
            $data1 = [
              "knowbaseitems_id" => $item["items_id"],
                "itemtype" => PluginDlteamsAudit::class,
                "items_id" => $audit_id,
                "comment" => $item["comment"]
            ];
            $knowbaseitem_item->add($data1);
            $count_copied++;
        }
    }
    return $count_copied;
}

function deleteAuditcategoryResources($audit_id){
    global $DB;

    $query = [
        "FROM" => PluginDlteamsAuditCategory_Item::getTable(),
        "WHERE" => [
            "itemtype" => KnowbaseItem::class,
            "auditcategories_id" => $_POST["plugin_dlteams_auditcategories_id"]
        ],
    ];

    $items_iterator = $DB->request($query);
    $count_delete = 0;
    foreach ($items_iterator as $item) {
        $audit_item = new PluginDlteamsAudit_Item();
        $data = [
            "audits_id" => $audit_id,
            "itemtype" => KnowbaseItem::class,
            "items_id" => $item["items_id"],
            "comment" => $item["comment"]
        ];

        $audit_item->delete($data);

        $knowbaseitem_item = new KnowbaseItem_Item();
        $data1 = [
            "knowbaseitems_id" => $item["items_id"],
            "itemtype" => PluginDlteamsAudit::class,
            "items_id" => $audit_id,
            "comment" => $item["comment"]
        ];

        $knowbaseitem_item->delete($data1);
        $count_delete++;
    }

    return $count_delete;
}
