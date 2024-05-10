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
/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();

Session::checkLoginUser();
if (isset($_POST['link_element'])) {
    global $DB;
    $DB->beginTransaction();
    if (isset($_POST["items_id_document"]))
        $_POST["items_id"] = $_POST["items_id_document"];

    if (isset($_POST["items_id_policie"]))
        $_POST["items_id"] = $_POST["items_id_policie"];

    $itemtype = $_POST['itemtype']; // ex: PluginDlteamsProtectiveMeasure
    $itemtype1 = $_POST['itemtype1']; // ex: PluginDlteamsRecord

    $exception_list = PluginDlteamsUtils::itemtypeExceptionList();

    if (in_array($itemtype1, $exception_list)) {
        $itemtype1_item = 'Item_' . $itemtype1;
    } elseif (class_exists($itemtype1 . "_Item")) {
        $itemtype1_item = $itemtype1 . '_Item';
    } else {
        $itemtype1_item = "PluginDlteams" . $itemtype1 . "_Item";
    }

    if (!$_POST["items_id"]) {
        Session::addMessageAfterRedirect(__('Veuillez choisir un élément à relier'), 0, ERROR);
        Html::back();
    }

    $itemtype_item = $itemtype . '_Item'; // ex: PluginDlteamsProtectiveMeasure_Item
    if (class_exists($itemtype . "_Item")) {
        $itemtype_item = $itemtype . '_Item';
    } else {
        $itemtype_item = "PluginDlteams" . $itemtype . "_Item";
    }

//    var_dump($itemtype_item);
//    die();

    if (isset($_POST["transformdocument"])) {
        $itemtype_item = PluginDlteamsDocument_Item::class;
    }

    if (!class_exists($itemtype) || !class_exists($itemtype1_item)) {

        Session::addMessageAfterRedirect(__("L'un des éléments a relier n'existe pas"), 0, ERROR);
        Html::back();
    }

    $baseItem = new $itemtype();

    global $DB;

    if (isset($_POST['items_id'])) {

        $i2_itemsid_column = strtolower(str_replace("PluginDlteams", "", $itemtype1)) . "s_id";

        if ($itemtype1 == 'PluginDlteamsAuditCategory')
            $i2_itemsid_column = 'auditcategories_id';

        $i2 = [
            $i2_itemsid_column => $_POST["items_id1"],
            "items_id" => $_POST["items_id"],
            "itemtype" => $_POST["itemtype"],
//            "comment" => $_POST["comment"],
        ];

        $second_items = new $itemtype1_item();
        if ($DB->fieldExists($second_items->getTable(), 'comment')) {
            $i2['comment'] = $_POST["comment"];
        }

        if (isset($_POST["transformdocument"]) && isset($_POST["mandatory"])) {
            $i2["document_mandatory"] = $_POST["mandatory"];
        }

        if ($itemtype == PluginDlteamsPolicieForm::class && isset($_POST["mandatory"])) {
            $i2["mandatory"] = $_POST["mandatory"];
        }

        if ($itemtype1 == PluginDlteamsAccountKey::class && $itemtype == User::class) {
            $i2["users_id"] = $_POST["items_id"];
            $accountkey = new PluginDlteamsAccountKey();
            $accountkey->getFromDB($_POST["items_id1"]);
            $i2["name"] = $accountkey->fields["name"];
        }

        if ($itemtype1 == PluginDlteamsAccountKey::class && $itemtype == Group::class) {
            $i2["groups_id"] = $_POST["items_id"];
            $accountkey = new PluginDlteamsAccountKey();
            $accountkey->getFromDB($_POST["items_id1"]);
            $i2["name"] = $accountkey->fields["name"];
        }
//        $id = $second_items->add($i2);
        if($itemtype1 == PluginDlteamsDataCatalog::class && isset($_POST["is_directory"])){
            $i2["is_directory"] = $_POST["is_directory"];
        }

        $DB->beginTransaction();
        $insert = $DB->insert($itemtype1_item::getTable(), $i2);


        $baseItem_items = new $itemtype_item();


        if ($_POST["itemtype"] == 'PluginDlteamsThirdPartyCategory')
            $i1_itemsid_column = 'thirdpartycategories_id';
        else
            $i1_itemsid_column = strtolower(str_replace("PluginDlteams", "", $itemtype)) . "s_id";
        $i1 = [
            $i1_itemsid_column => $_POST["items_id"],
            "items_id" => $_POST["items_id1"],
            "itemtype" => $_POST["itemtype1"],
            "comment" => $_POST["comment"],
        ];


        if($itemtype == PluginDlteamsAccountKey::class && isset($_POST["is_directory"])){
            $i1["is_directory"] = $_POST["is_directory"];
        }
        $result = $DB->insert($itemtype_item::getTable(), $i1);


//        var_dump($result);
//        var_dump($DB->error());
//        die();


//        apply on children
        if (isset($_POST["apply_on_childs"]) && $_POST["apply_on_childs"] && $_POST["apply_on_childs"] == '1') {
            $children_request = [
                "FROM" => PluginDlteamsDataCatalog::getTable(),
                "WHERE" => [
                    "plugin_dlteams_datacatalogs_id" => $_POST["items_id1"]
                ]
            ];
            $iterator = $DB->request($children_request);
            if ($iterator) foreach ($iterator as $key => $child) {
                $array1 = [
                    $i1_itemsid_column => $_POST["items_id"],
                    "itemtype" => $_POST["itemtype1"],
                    "items_id" => $child["id"],
                    "comment" => $_POST["comment"],
                ];
                if (isset($_POST["userprofiles_id"])) {
                    $array1["plugin_dlteams_userprofiles_id"] = $_POST["userprofiles_id"];
                }


                $array2 = [
                    "datacatalogs_id" => $child["id"],
                    "itemtype" => $_POST["itemtype"],
                    "items_id" => $_POST["items_id"],
                    "comment" => $_POST["comment"],
                ];
                if (isset($_POST["userprofiles_id"])) {
                    $array2["plugin_dlteams_userprofiles_id"] = $_POST["userprofiles_id"];
                }


                if ($_POST["itemtype"] == Datacenter::class)
                    $temp = PluginDlteamsDatacenter_Item::class;
                else
                    $temp = $_POST["itemtype"] . "_Item";

                $itemtype_item1 = new $temp();
//                on ajoute aux enfants si la relation n'existe pas encore
                if (!$itemtype_item1->getFromDBByCrit($array1)) {
                    if (!($result = $DB->insert($itemtype_item1->getTable(), $array1))) {
                        if (Session::DEBUG_MODE)
                            Session::addMessageAfterRedirect($DB->error(), false, ERROR);
                        $DB->rollBack();
                        Session::addMessageAfterRedirect("Une erreur s'est produite dans la relation enfant ", false, ERROR);
                        Html::back();
                    }
                }


                $temp = $_POST["itemtype1"] . "_Item";
                $itemtype_item = new $temp();
//                on ajoute aux enfants si la relation n'existe pas encore
                if (!$itemtype_item->getFromDBByCrit($array2)) {
                    if (!$DB->insert($itemtype_item->getTable(), $array2)) {
                        if (Session::DEBUG_MODE)
                            Session::addMessageAfterRedirect($DB->error(), false, ERROR);
                        $DB->rollBack();
                        Session::addMessageAfterRedirect("Une erreur s'est produite dans la relation enfant ", false, ERROR);
                        Html::back();
                    }
                }
            }
        }

        $DB->commit();
        Session::addMessageAfterRedirect(__('Ajoutée avec succès'));

    }
}


if (isset($_POST["make_replacement"]) && $_POST["make_replacement"]) {
    /*    highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//    die();
    global $DB;
    $deliverable_sections = $DB->request(PluginDlteamsDeliverable_Section::getTable(), ["deliverables_id" => $_POST["items_id1"]]);
    $replacements_count = 0;
    $request = [
        'SELECT' => [
            PluginDlteamsDeliverable_Variable_Item::getTable() . '.id AS linkid',
            PluginDlteamsDeliverable_Variable_Item::getTable() . '.comment as comment',
            PluginDlteamsDeliverable_Variable::getTable() . '.id as id',
            PluginDlteamsDeliverable_Variable::getTable() . '.name as name',
        ],
        'FROM' => PluginDlteamsDeliverable_Variable_Item::getTable(),
        'JOIN' => [
            PluginDlteamsDeliverable_Variable::getTable() => [
                'FKEY' => [
                    PluginDlteamsDeliverable_Variable_Item::getTable() => 'deliverable_variables_id',
                    PluginDlteamsDeliverable_Variable::getTable() => 'id'
                ]
            ]
        ],
        'WHERE' => [
            PluginDlteamsDeliverable_Variable_Item::getTable() . '.items_id' => $_POST["items_id1"],
            PluginDlteamsDeliverable_Variable_Item::getTable() . '.itemtype' => "PluginDlteamsDeliverable"
        ],
        'ORDER' => ['name ASC'],
    ];
    $deliverable_variables_item = $DB->request($request, "", true);


    foreach ($deliverable_sections as $section) {

//make the replacement in concerned deliverable section's name and content
        $deliverable_contents = $DB->request(PluginDlteamsDeliverable_Content::getTable(), ["deliverable_sections_id" => $section["id"]]);
        foreach ($deliverable_variables_item as $variable) {

            $deliverable_section = new PluginDlteamsDeliverable_Section();
            $deliverable_section->getFromDB($section["id"]);

            if (str_contains($section["content"]??"", $variable["name"]) || str_contains($section["name"]??"", $variable["name"])) {

                $deliverable_section->update(
                    [
                        "name" => str_replace($variable["name"], $variable["comment"], $section["name"]),
                        "content" => str_replace($variable["name"], $variable["comment"], $section["content"]??""),
                        "id" => $section["id"]
                    ]
                );
//                    $replacements_count++;
            }

        }


        foreach ($deliverable_contents as $content) {

            foreach ($deliverable_variables_item as $variable) {

                $deliverable_content = new PluginDlteamsDeliverable_Content();
                if (str_contains($content["content"], $variable["name"]) || str_contains($content["name"], $variable["name"])) {

/*                    highlight_string("<?php\n\$data =\n" . var_export($content["id"], true) . ";\n?>");*/
/*                    highlight_string("<?php\n\$data =\n" . var_export(stripslashes(str_replace($variable["name"], $variable["comment"], $content["content"])), true) . ";\n?>");*/
//                    die();
//                    $DB->update($deliverable_content->getTable(),
//                        [
//                            "name" => str_replace($variable["name"], $variable["comment"], $content["name"]),
//                            "content" => str_replace($variable["name"], $variable["comment"], $content["content"]),
//                            "id" => $content["id"]
//                        ],
//                        ["id" => $content["id"]]
//                    );

                    $replace_character = ["<", ">", "'"];
                    $content_str = str_replace($variable["name"], $variable["comment"], $content["content"]);
                    $name_str = str_replace($variable["name"], $variable["comment"], $content["name"]);
                    foreach ($replace_character as $characher){
                        $content_str = str_replace($characher, sprintf("\%s",$characher), $content_str);
                        $name_str = str_replace($characher, sprintf("\%s",$characher), $name_str);
                    }
                    $deliverable_content->update(
                        [
                            "name" => $name_str,
                            "content" => $content_str,
                            "id" => $content["id"]
                        ]
                    );


//                    $deliverable_content->update(
//                        [
//                            "name" => $content["name"],
//                            "content" => $content["content"],
//                            "id" => $content["id"]
//                        ]
//                    );

//                    $replacements_count++;
                }

            }
        }
        /*        highlight_string("<?php\n\$data =\n" . var_export($section, true) . ";\n?>");*/
//        die();
    }
//    Session::addMessageAfterRedirect($replacements_count . " remplacement(s) éffectué(s)");
    Session::addMessageAfterRedirect("Remplacement(s) éffectué(s)");
}


if (isset($_POST["procedures_make_replacement"]) && $_POST["procedures_make_replacement"]) {
    /*    highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//    die();
    global $DB;
    $procedure_sections = $DB->request(PluginDlteamsProcedure_Section::getTable(), ["procedures_id" => $_POST["items_id1"]]);
    $replacements_count = 0;
    $request = [
        'SELECT' => [
            PluginDlteamsProcedure_Variable_Item::getTable() . '.id AS linkid',
            PluginDlteamsProcedure_Variable_Item::getTable() . '.comment as comment',
            PluginDlteamsProcedure_Variable::getTable() . '.id as id',
            PluginDlteamsProcedure_Variable::getTable() . '.name as name',
        ],
        'FROM' => PluginDlteamsProcedure_Variable_Item::getTable(),
        'JOIN' => [
            PluginDlteamsProcedure_Variable::getTable() => [
                'FKEY' => [
                    PluginDlteamsProcedure_Variable_Item::getTable() => 'procedure_variables_id',
                    PluginDlteamsProcedure_Variable::getTable() => 'id'
                ]
            ]
        ],
        'WHERE' => [
            PluginDlteamsProcedure_Variable_Item::getTable() . '.items_id' => $_POST["items_id1"],
            PluginDlteamsProcedure_Variable_Item::getTable() . '.itemtype' => "PluginDlteamsProcedure"
        ],
        'ORDER' => ['name ASC'],
    ];
    $procedure_variables_item = $DB->request($request, "", true);


    foreach ($procedure_sections as $section) {

//make the replacement in concerned deliverable section's name and content
        $procedure_contents = $DB->request(PluginDlteamsProcedure_Content::getTable(), ["procedure_sections_id" => $section["id"]]);
        foreach ($procedure_variables_item as $variable) {

            $procedure_section = new PluginDlteamsProcedure_Section();
            $procedure_section->getFromDB($section["id"]);

            if (str_contains($section["content"]??"", $variable["name"]) || str_contains($section["name"]??"", $variable["name"])) {

                $procedure_section->update(
                    [
                        "name" => str_replace($variable["name"], $variable["comment"], $section["name"]),
                        "content" => str_replace($variable["name"], $variable["comment"], $section["content"]??""),
                        "id" => $section["id"]
                    ]
                );
//                    $replacements_count++;
            }

        }


        foreach ($procedure_contents as $content) {

            foreach ($procedure_variables_item as $variable) {

                $procedure_content = new PluginDlteamsProcedure_Content();
                if (str_contains($content["content"], $variable["name"]) || str_contains($content["name"], $variable["name"])) {

                    $replace_character = ["<", ">", "'"];
                    $content_str = str_replace($variable["name"], $variable["comment"], $content["content"]);
                    $name_str = str_replace($variable["name"], $variable["comment"], $content["name"]);
                    foreach ($replace_character as $characher){
                        $content_str = str_replace($characher, sprintf("\%s",$characher), $content_str);
                        $name_str = str_replace($characher, sprintf("\%s",$characher), $name_str);
                    }
                    $procedure_content->update(
                        [
                            "name" => $name_str,
                            "content" => $content_str,
                            "id" => $content["id"]
                        ]
                    );


//

//                    $replacements_count++;
                }

            }
        }
        /*        highlight_string("<?php\n\$data =\n" . var_export($section, true) . ";\n?>");*/
//        die();
    }
//    Session::addMessageAfterRedirect($replacements_count . " remplacement(s) éffectué(s)");
    Session::addMessageAfterRedirect("Remplacement(s) éffectué(s)");
}

Html::back();
