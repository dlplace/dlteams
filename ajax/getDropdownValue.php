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

include_once('../../../inc/includes.php');

Session::checkLoginUser();
echo getDropdownValue($_POST);


function getDropdownValue($post, $json = true)
{
    /**
     * @var array $CFG_GLPI
     * @var \DBmysql $DB
     */
    global $CFG_GLPI, $DB;

    // check if asked itemtype is the one originaly requested by the form
    if (!Session::validateIDOR($post)) {
        return;
    }

    if (
        isset($post["entity_restrict"])
        && !is_array($post["entity_restrict"])
        && (substr($post["entity_restrict"], 0, 1) === '[')
        && (substr($post["entity_restrict"], -1) === ']')
    ) {
        $decoded = Toolbox::jsonDecode($post['entity_restrict']);
        $entities = [];
        if (is_array($decoded)) {
            foreach ($decoded as $value) {
                $entities[] = (int)$value;
            }
        }
        $post["entity_restrict"] = $entities;
    }
    if (isset($post['entity_restrict']) && 'default' === $post['entity_restrict']) {
        $post['entity_restrict'] = $_SESSION['glpiactiveentities'];
    }

    // Security
    if (!($item = getItemForItemtype($post['itemtype']))) {
        return;
    }

    $table = $item->getTable();
    $datas = [];

    $displaywith = false;
    if (isset($post['displaywith'])) {
        if (is_array($post['displaywith']) && count($post['displaywith'])) {
            $table = getTableForItemType($post['itemtype']);
            foreach ($post['displaywith'] as $key => $value) {
                if (!$DB->fieldExists($table, $value)) {
                    unset($post['displaywith'][$key]);
                }
            }
            if (count($post['displaywith'])) {
                $displaywith = true;
            }
        }
    }

    if (!isset($post['permit_select_parent'])) {
        $post['permit_select_parent'] = false;
    }

    if (isset($post['condition']) && !empty($post['condition']) && !is_array($post['condition'])) {
        // Retreive conditions from SESSION using its key
        $key = $post['condition'];
        if (isset($_SESSION['glpicondition']) && isset($_SESSION['glpicondition'][$key])) {
            $post['condition'] = $_SESSION['glpicondition'][$key];
        } else {
            $post['condition'] = [];
        }
    }

    if (!isset($post['emptylabel']) || ($post['emptylabel'] == '')) {
        $post['emptylabel'] = Dropdown::EMPTY_VALUE;
    }

    $where = [];

    if ($item->maybeDeleted()) {
        $where["$table.is_deleted"] = 0;
    }
    if ($item->maybeTemplate()) {
        $where["$table.is_template"] = 0;
    }

    if (!isset($post['page'])) {
        $post['page'] = 1;
        $post['page_limit'] = $CFG_GLPI['dropdown_max'];
    }

    $start = intval(($post['page'] - 1) * $post['page_limit']);
    $limit = intval($post['page_limit']);

    if (isset($post['used'])) {
        $used = $post['used'];

        if (count($used)) {
            $where['NOT'] = ["$table.id" => $used];
        }
    }

    if (isset($post['toadd'])) {
        $toadd = $post['toadd'];
    } else {
        $toadd = [];
    }

    $ljoin = [];

    if (isset($post['condition']) && !empty($post['condition'])) {
        if (isset($post['condition']['LEFT JOIN'])) {
            $ljoin = $post['condition']['LEFT JOIN'];
            unset($post['condition']['LEFT JOIN']);
        }
        if (isset($post['condition']['WHERE'])) {
            $where = array_merge($where, $post['condition']['WHERE']);
        } else {
            $where = array_merge($where, $post['condition']);
        }
    }

    $one_item = -1;
    if (isset($post['_one_id'])) {
        $one_item = $post['_one_id'];
    }

    // Count real items returned
    $count = 0;
    if ($item instanceof CommonTreeDropdown) {
        if (isset($post['parent_id']) && $post['parent_id'] != '') {
            $sons = getSonsOf($table, $post['parent_id']);
            $where[] = [
                ["$table.id" => $sons],
                ["NOT" => ["$table.id" => $post['parent_id']]],
            ];
        }
        if ($one_item >= 0) {
            $where["$table.id"] = $one_item;
        } else {
            if (!empty($post['searchText'])) {
                $raw_search = Search::makeTextSearchValue($post['searchText']);
                $encoded_search = \Glpi\Toolbox\Sanitizer::encodeHtmlSpecialChars($raw_search);

                $swhere = [
                    ["$table.completename" => ['LIKE', $raw_search]],
                    ["$table.completename" => ['LIKE', $encoded_search]],
                ];
                if (Session::haveTranslations($post['itemtype'], 'completename')) {
                    $swhere[] = ["namet.value" => ['LIKE', $raw_search]];
                    $swhere[] = ["namet.value" => ['LIKE', $encoded_search]];
                }

                if (
                    $_SESSION['glpiis_ids_visible']
                    && is_numeric($post['searchText']) && (int)$post['searchText'] == $post['searchText']
                ) {
                    $swhere[$table . '.' . $item->getIndexName()] = ['LIKE', "%{$post['searchText']}%"];
                }

                // search also in displaywith columns
                if ($displaywith && count($post['displaywith'])) {
                    foreach ($post['displaywith'] as $with) {
                        $swhere[] = ["$table.$with" => ['LIKE', $raw_search]];
                        $swhere[] = ["$table.$with" => ['LIKE', $encoded_search]];
                    }
                }

                $where[] = ['OR' => $swhere];
            }
        }


        $multi = false;

        // Manage multiple Entities dropdowns
        $order = ["$table.completename"];

        // No multi if get one item
        if ($item->isEntityAssign()) {
            $recur = $item->maybeRecursive();

            // Entities are not really recursive : do not display parents
            if ($post['itemtype'] == 'Entity') {
                $recur = false;
            }

            if (isset($post["entity_restrict"]) && !($post["entity_restrict"] < 0)) {
                $where = $where + getEntitiesRestrictCriteria(
                        $table,
                        '',
                        $post["entity_restrict"],
                        $recur
                    );

                if (is_array($post["entity_restrict"]) && (count($post["entity_restrict"]) > 1)) {
                    $multi = true;
                }
            } else {
                // If private item do not use entity
                if (!$item->maybePrivate()) {
                    $where = $where + getEntitiesRestrictCriteria($table, '', '', $recur);

                    if (count($_SESSION['glpiactiveentities']) > 1) {
                        $multi = true;
                    }
                } else {
                    $multi = false;
                }
            }

            // Force recursive items to multi entity view
            if ($recur) {
                $multi = true;
            }

            // no multi view for entitites
            if ($post['itemtype'] == "Entity") {
                $multi = false;
            }

            if ($multi) {
                array_unshift($order, "$table.entities_id");
            }
        }

        $addselect = [];
        if (Session::haveTranslations($post['itemtype'], 'completename')) {
            $addselect[] = "namet.value AS transcompletename";
            $ljoin['glpi_dropdowntranslations AS namet'] = [
                'ON' => [
                    'namet' => 'items_id',
                    $table => 'id', [
                        'AND' => [
                            'namet.itemtype' => $post['itemtype'],
                            'namet.language' => $_SESSION['glpilanguage'],
                            'namet.field' => 'completename'
                        ]
                    ]
                ]
            ];
        }
        if (Session::haveTranslations($post['itemtype'], 'name')) {
            $addselect[] = "namet2.value AS transname";
            $ljoin['glpi_dropdowntranslations AS namet2'] = [
                'ON' => [
                    'namet2' => 'items_id',
                    $table => 'id', [
                        'AND' => [
                            'namet2.itemtype' => $post['itemtype'],
                            'namet2.language' => $_SESSION['glpilanguage'],
                            'namet2.field' => 'name'
                        ]
                    ]
                ]
            ];
        }
        if (Session::haveTranslations($post['itemtype'], 'comment')) {
            $addselect[] = "commentt.value AS transcomment";
            $ljoin['glpi_dropdowntranslations AS commentt'] = [
                'ON' => [
                    'commentt' => 'items_id',
                    $table => 'id', [
                        'AND' => [
                            'commentt.itemtype' => $post['itemtype'],
                            'commentt.language' => $_SESSION['glpilanguage'],
                            'commentt.field' => 'comment'
                        ]
                    ]
                ]
            ];
        }

        if ($start > 0 && $multi) {
            //we want to load last entry of previous page
            //(and therefore one more result) to check if
            //entity name must be displayed again
            --$start;
            ++$limit;
        }

        $criteria = [
            'SELECT' => array_merge(["$table.*"], $addselect),
            'DISTINCT' => true,
            'FROM' => $table,
            'WHERE' => $where,
            'ORDER' => $order,
            'START' => $start,
            'LIMIT' => $limit
        ];
        if (count($ljoin)) {
            $criteria['LEFT JOIN'] = $ljoin;
        }

        $iterator = $DB->request($criteria);

        // Empty search text : display first
        if ($post['page'] == 1 && empty($post['searchText'])) {
            if ($post['display_emptychoice']) {
                $datas[] = [
                    'id' => 0,
                    'text' => $post['emptylabel']
                ];
            }
        }

        if ($post['page'] == 1) {
            if (count($toadd)) {
                foreach ($toadd as $key => $val) {
                    $datas[] = [
                        'id' => $key,
                        'text' => stripslashes($val)
                    ];
                }
            }
        }
        $last_level_displayed = [];
        $datastoadd = [];

        // Ignore first item for all pages except first page
        $firstitem = (($post['page'] > 1));
        $firstitem_entity = -1;
        $prev = -1;
        if (count($iterator)) {
            foreach ($iterator as $data) {
                $ID = $data['id'];
                $level = $data['level'];

                if (isset($data['transname']) && !empty($data['transname'])) {
                    $outputval = $data['transname'];
                } else {
                    $outputval = $data['name'];
                }

                if (
                    $multi
                    && ($data["entities_id"] != $prev)
                ) {
                    // Do not do it for first item for next page load
                    if (!$firstitem) {
                        if ($prev >= 0) {
                            if (count($datastoadd)) {
                                $datas[] = [
                                    'text' => Dropdown::getDropdownName("glpi_entities", $prev),
                                    'children' => $datastoadd,
                                    'itemtype' => "Entity",
                                ];
                            }
                        }
                    }
                    $prev = $data["entities_id"];
                    if ($firstitem) {
                        $firstitem_entity = $prev;
                    }
                    // Reset last level displayed :
                    $datastoadd = [];
                }

                if ($_SESSION['glpiuse_flat_dropdowntree']) {
                    if (isset($data['transcompletename']) && !empty($data['transcompletename'])) {
                        $outputval = $data['transcompletename'];
                    } else {
                        $outputval = $data['completename'];
                    }

                    $outputval = CommonTreeDropdown::sanitizeSeparatorInCompletename($outputval);

                    $level = 0;
                } else { // Need to check if parent is the good one
                    // Do not do if only get one item
                    if (($level > 1)) {
                        // Last parent is not the good one need to display arbo
                        if (
                            !isset($last_level_displayed[$level - 1])
                            || ($last_level_displayed[$level - 1] != $data[$item->getForeignKeyField()])
                        ) {
                            $work_level = $level - 1;
                            $work_parentID = $data[$item->getForeignKeyField()];
                            $parent_datas = [];
                            do {
                                // Get parent
                                if ($item->getFromDB($work_parentID)) {
                                    // Do not do for first item for next page load
                                    if (!$firstitem) {
                                        $title = $item->fields['completename'];

                                        $title = CommonTreeDropdown::sanitizeSeparatorInCompletename($title);

                                        $selection_text = $title;

                                        if (isset($item->fields["comment"])) {
                                            $addcomment
                                                = DropdownTranslation::getTranslatedValue(
                                                $ID,
                                                $post['itemtype'],
                                                'comment',
                                                $_SESSION['glpilanguage'],
                                                $item->fields['comment']
                                            );
                                            $title = sprintf(__('%1$s - %2$s'), $title, $addcomment);
                                        }
                                        $output2 = DropdownTranslation::getTranslatedValue(
                                            $item->fields['id'],
                                            $post['itemtype'],
                                            'name',
                                            $_SESSION['glpilanguage'],
                                            $item->fields['name']
                                        );

                                        $temp = ['id' => $work_parentID,
                                            'text' => $output2,
                                            'level' => (int)$work_level,
                                            'disabled' => true
                                        ];
                                        if ($post['permit_select_parent']) {
                                            $temp['title'] = $title;
                                            $temp['selection_text'] = $selection_text;
                                            unset($temp['disabled']);
                                        }
                                        array_unshift($parent_datas, $temp);
                                    }
                                    $last_level_displayed[$work_level] = $item->fields['id'];
                                    $work_level--;
                                    $work_parentID = $item->fields[$item->getForeignKeyField()];
                                } else { // Error getting item : stop
                                    $work_level = -1;
                                }
                            } while (
                                ($work_level >= 1)
                                && (!isset($last_level_displayed[$work_level])
                                    || ($last_level_displayed[$work_level] != $work_parentID))
                            );
                            // Add parents
                            foreach ($parent_datas as $val) {
                                $datastoadd[] = $val;
                            }
                        }
                    }
                    $last_level_displayed[$level] = $data['id'];
                }

//                TODO Dlregister: add datacatalog directory name
                if ($post["itemtype"] == PluginDlteamsDataCatalog::class) {
                    if ($data["directory_name"])
                        $outputval = sprintf('%1$s ( %2$s )', $data['directory_name'], $outputval) ?? "";

                }

                // Do not do for first item for next page load
                if (!$firstitem) {

                    if (
                        $_SESSION["glpiis_ids_visible"]
                        || (Toolbox::strlen($outputval) == 0)
                    ) {
                        $outputval = sprintf(__('%1$s (%2$s)'), $outputval, $ID);
                    }

                    if (isset($data['transcompletename']) && !empty($data['transcompletename'])) {
                        $title = $data['transcompletename'];
                    } else {
                        $title = $data['completename'];
                    }

                    $title = CommonTreeDropdown::sanitizeSeparatorInCompletename($title);

                    $selection_text = $title;

                    if (isset($data["comment"])) {
                        if (isset($data['transcomment']) && !empty($data['transcomment'])) {
                            $addcomment = $data['transcomment'];
                        } else {
                            $addcomment = $data['comment'];
                        }
                        $title = sprintf(__('%1$s - %2$s'), $title, $addcomment);
                    }
                    $datastoadd[] = [
                        'id' => $ID,
                        'text' => $outputval,
                        'level' => (int)$level,
                        'title' => $title,
                        'selection_text' => $selection_text
                    ];
                    $count++;
                }
                $firstitem = false;
            }
        }

        if ($multi) {
            if (count($datastoadd)) {
                // On paging mode do not add entity information each time
                if ($prev == $firstitem_entity) {
                    $datas = array_merge($datas, $datastoadd);
                } else {
                    $datas[] = [
                        'text' => Dropdown::getDropdownName("glpi_entities", $prev),
                        'children' => $datastoadd,
                        'itemtype' => "Entity",
                    ];
                }
            }
        } else {
            if (count($datastoadd)) {
                $datas = array_merge($datas, $datastoadd);
            }
        }
    } else { // Not a dropdowntree

        $multi = false;
        // No multi if get one item
        if ($item->isEntityAssign()) {
            $multi = $item->maybeRecursive();

            if (isset($post["entity_restrict"]) && !($post["entity_restrict"] < 0)) {
                $where = $where + getEntitiesRestrictCriteria(
                        $table,
                        "entities_id",
                        $post["entity_restrict"],
                        $multi
                    );

                if (is_array($post["entity_restrict"]) && (count($post["entity_restrict"]) > 1)) {
                    $multi = true;
                }
            } else {
                // Do not use entity if may be private
                if (!$item->maybePrivate()) {
                    $where = $where + getEntitiesRestrictCriteria($table, '', '', $multi);

                    if (count($_SESSION['glpiactiveentities']) > 1) {
                        $multi = true;
                    }
                } else {
                    $multi = false;
                }
            }
        }

        $field = "name";
        if ($item instanceof CommonDevice) {
            $field = "designation";
        } else if ($item instanceof Item_Devices) {
            $field = "itemtype";
        }

        if (!empty($post['searchText'])) {
            $raw_search = Search::makeTextSearchValue($post['searchText']);
            $encoded_search = \Glpi\Toolbox\Sanitizer::encodeHtmlSpecialChars($raw_search);

            $orwhere = [
                ["$table.$field" => ['LIKE', $raw_search]],
                ["$table.$field" => ['LIKE', $encoded_search]],
            ];

//            TODO dlteams
            if($item::getType() == Ticket::class){
                $orwhere[] = new \QueryExpression("CONCAT(id, ' - ', name) LIKE '%$raw_search%'");
            }
            if($item::getType() == PluginDlteamsAccountKey::class){
                $orwhere[] = new \QueryExpression("CONCAT(directory_name, ' | ', glpi_plugin_dlteams_accountkeys.name) LIKE '%$raw_search%'");
            }

            if (
                $_SESSION['glpiis_ids_visible']
                && is_numeric($post['searchText']) && (int)$post['searchText'] == $post['searchText']
            ) {
                $orwhere[$table . '.' . $item->getIndexName()] = ['LIKE', "%{$post['searchText']}%"];
            }

            if ($item instanceof CommonDCModelDropdown) {
                $orwhere[] = [$table . '.product_number' => ['LIKE', $raw_search]];
                $orwhere[] = [$table . '.product_number' => ['LIKE', $encoded_search]];
            }

            if (Session::haveTranslations($post['itemtype'], $field)) {
                $orwhere[] = ['namet.value' => ['LIKE', $raw_search]];
                $orwhere[] = ['namet.value' => ['LIKE', $encoded_search]];
            }
            if ($post['itemtype'] == "SoftwareLicense") {
                $orwhere[] = ['glpi_softwares.name' => ['LIKE', $raw_search]];
                $orwhere[] = ['glpi_softwares.name' => ['LIKE', $encoded_search]];
            }

            // search also in displaywith columns
            if ($displaywith && count($post['displaywith'])) {
                foreach ($post['displaywith'] as $with) {
                    $orwhere[] = ["$table.$with" => ['LIKE', $raw_search]];
                    $orwhere[] = ["$table.$with" => ['LIKE', $encoded_search]];
                }
            }

            $where[] = ['OR' => $orwhere];
        }
        $addselect = [];
        if($item::getType() == Ticket::class){
            $addselect[] = new \QueryExpression("CONCAT(id, ' ', name) AS concat_name_id");
        }

        if (Session::haveTranslations($post['itemtype'], $field)) {
            $addselect[] = "namet.value AS transname";
            $ljoin['glpi_dropdowntranslations AS namet'] = [
                'ON' => [
                    'namet' => 'items_id',
                    $table => 'id', [
                        'AND' => [
                            'namet.itemtype' => $post['itemtype'],
                            'namet.language' => $_SESSION['glpilanguage'],
                            'namet.field' => $field
                        ]
                    ]
                ]
            ];
        }
        if (Session::haveTranslations($post['itemtype'], 'comment')) {
            $addselect[] = "commentt.value AS transcomment";
            $ljoin['glpi_dropdowntranslations AS commentt'] = [
                'ON' => [
                    'commentt' => 'items_id',
                    $table => 'id', [
                        'AND' => [
                            'commentt.itemtype' => $post['itemtype'],
                            'commentt.language' => $_SESSION['glpilanguage'],
                            'commentt.field' => 'comment'
                        ]
                    ]
                ]
            ];
        }

        $criteria = [];
        switch ($post['itemtype']) {
            case "Contact":
                $criteria = [
                    'SELECT' => [
                        "$table.entities_id",
                        new \QueryExpression(
                            "CONCAT(IFNULL(" . $DB->quoteName('name') . ",''),' ',IFNULL(" .
                            $DB->quoteName('firstname') . ",'')) AS " . $DB->quoteName($field)
                        ),
                        "$table.comment",
                        "$table.id"
                    ],
                    'FROM' => $table
                ];
                break;

            case "SoftwareLicense":
                $criteria = [
                    'SELECT' => [
                        "$table.*",
                        new \QueryExpression("CONCAT(glpi_softwares.name,' - ',glpi_softwarelicenses.name) AS $field")
                    ],
                    'FROM' => $table,
                    'LEFT JOIN' => [
                        'glpi_softwares' => [
                            'ON' => [
                                'glpi_softwarelicenses' => 'softwares_id',
                                'glpi_softwares' => 'id'
                            ]
                        ]
                    ]
                ];
                break;

            case "Profile":
                $criteria = [
                    'SELECT' => "$table.*",
                    'DISTINCT' => true,
                    'FROM' => $table,
                    'LEFT JOIN' => [
                        'glpi_profilerights' => [
                            'ON' => [
                                'glpi_profilerights' => 'profiles_id',
                                $table => 'id'
                            ]
                        ]
                    ]
                ];
                break;

            case KnowbaseItem::getType():
                $criteria = [
                    'SELECT' => array_merge(["$table.*"], $addselect),
                    'DISTINCT' => true,
                    'FROM' => $table
                ];
                if (count($ljoin)) {
                    $criteria['LEFT JOIN'] = $ljoin;
                }

                $visibility = KnowbaseItem::getVisibilityCriteria();
                if (count($visibility['LEFT JOIN'])) {
                    $criteria['LEFT JOIN'] = array_merge(
                        (isset($criteria['LEFT JOIN']) ? $criteria['LEFT JOIN'] : []),
                        $visibility['LEFT JOIN']
                    );
                    //Do not use where??
                    /*if (isset($visibility['WHERE'])) {
                      $where = $visibility['WHERE'];
                    }*/
                }
                break;

            case Project::getType():
                $visibility = Project::getVisibilityCriteria();
                if (count($visibility['LEFT JOIN'])) {
                    $ljoin = array_merge($ljoin, $visibility['LEFT JOIN']);
                    if (isset($visibility['WHERE'])) {
                        $where[] = $visibility['WHERE'];
                    }
                }
            //no break to reach default case.

            default:
                $criteria = [
                    'SELECT' => array_merge(["$table.*"], $addselect),
                    'FROM' => $table
                ];
                if (count($ljoin)) {
                    $criteria['LEFT JOIN'] = $ljoin;
                }
        }

        $criteria = array_merge(
            $criteria,
            [
                'DISTINCT' => true,
                'WHERE' => $where,
                'START' => $start,
                'LIMIT' => $limit
            ]
        );

        $order_field = "$table.$field";
        if (isset($post['order']) && !empty($post['order'])) {
            $order_field = $post['order'];
        }
        if ($multi) {
            $criteria['ORDERBY'] = ["$table.entities_id", $order_field];
        } else {
            $criteria['ORDERBY'] = [$order_field];
        }


//        TODO Dlregister

        switch ($table) {
            case PluginDlteamsRecord::getTable():
                $criteria['ORDERBY'] = ["$table.entities_id", "$table.number ASC"];
                break;
            case PluginDlteamsProtectiveMeasure::getTable():
                $criteria['LEFT JOIN']['glpi_plugin_dlteams_protectivetypes'] = [
                    'FKEY' => [
                        'glpi_plugin_dlteams_protectivemeasures' => "plugin_dlteams_protectivetypes_id",
                        'glpi_plugin_dlteams_protectivetypes' => "id"
                    ]
                ];

                $criteria['LEFT JOIN']['glpi_plugin_dlteams_protectivecategories'] = [
                    'FKEY' => [
                        'glpi_plugin_dlteams_protectivemeasures' => "plugin_dlteams_protectivecategories_id",
                        'glpi_plugin_dlteams_protectivecategories' => "id"
                    ]
                ];


                $criteria['SELECT'][] = 'glpi_plugin_dlteams_protectivetypes.name AS typename';
                $criteria['SELECT'][] = 'glpi_plugin_dlteams_protectivecategories.name as nameCat';

                $criteria['ORDERBY'] = ["glpi_plugin_dlteams_protectivetypes.name ASC", "glpi_plugin_dlteams_protectivecategories.name ASC", "glpi_plugin_dlteams_protectivemeasures.name ASC"];
                break;
            case PluginDlteamsLegalBasi::getTable():
                $criteria['LEFT JOIN']['glpi_plugin_dlteams_legalbasistypes'] = [
                    'FKEY' => [
                        'glpi_plugin_dlteams_legalbasis' => "plugin_dlteams_legalbasistypes_id",
                        'glpi_plugin_dlteams_legalbasistypes' => "id"
                    ]
                ];


                $criteria['SELECT'][] = 'glpi_plugin_dlteams_legalbasistypes.name AS typename';

                $criteria['ORDERBY'] = ["glpi_plugin_dlteams_legalbasistypes.id ASC", "$table.name ASC"];
                break;
            case PluginDlteamsAccountKey::getTable():
                $criteria['LEFT JOIN'][PluginDlteamsDataCatalog::getTable()] = [
                    'FKEY' => [
                        PluginDlteamsAccountKey::getTable() => "plugin_dlteams_datacatalogs_id",
                        PluginDlteamsDataCatalog::getTable() => "id"
                    ]
                ];

                $criteria['ORDERBY'] = [PluginDlteamsDataCatalog::getTable().".name ASC", "$table.name ASC"];
                $criteria['SELECT'][] = PluginDlteamsDataCatalog::getTable().'.name AS cataloguename';
                $criteria['SELECT'][] = PluginDlteamsDataCatalog::getTable().'.directory_name AS directory_name';
                break;
        }


        $iterator = $DB->request($criteria);

        // Display first if no search
        if ($post['page'] == 1 && empty($post['searchText'])) {
            if (!isset($post['display_emptychoice']) || $post['display_emptychoice']) {
                $datas[] = [
                    'id' => 0,
                    'text' => $post["emptylabel"]
                ];
            }
        }
        if ($post['page'] == 1) {
            if (count($toadd)) {
                foreach ($toadd as $key => $val) {
                    $datas[] = [
                        'id' => $key,
                        'text' => stripslashes($val)
                    ];
                }
            }
        }

        $datastoadd = [];

        if (count($iterator)) {
            $prev = -1;

            foreach ($iterator as $data) {
                if (
                    $multi
                    && ($data["entities_id"] != $prev)
                ) {
                    if ($prev >= 0) {
                        if (count($datastoadd)) {
                            $datas[] = [
                                'text' => Dropdown::getDropdownName("glpi_entities", $prev),
                                'children' => $datastoadd,
                                'itemtype' => "Entity",
                            ];
                        }
                    }
                    $prev = $data["entities_id"];
                    $datastoadd = [];
                }

                if (isset($data['transname']) && !empty($data['transname'])) {
                    $outputval = $data['transname'];
                } else if ($field == 'itemtype' && class_exists($data['itemtype'])) {
                    $tmpitem = new $data[$field]();
                    if ($tmpitem->getFromDB($data['items_id'])) {
                        $outputval = sprintf(__('%1$s - %2$s'), $tmpitem->getTypeName(), $tmpitem->getName());
                    } else {
                        $outputval = $tmpitem->getTypeName();
                    }
                } else if ($item instanceof CommonDCModelDropdown) {
                    $outputval = sprintf(__('%1$s - %2$s'), $data[$field], $data['product_number']);
                } else {
//                    TODO Dlregister
//                    var_dump($post["itemtype"]);
//                    die();
                    switch ($post["itemtype"]) {
                        case PluginDlteamsRecord::class:
                            $outputval = sprintf('%1$s - %2$s', $data['number'], $data[$field]) ?? "";
                            break;
                        case Ticket::class:
                            $outputval = sprintf('%1$s - %2$s', $data['id'], $data[$field]) ?? "";
                            break;
                        case PluginDlteamsProtectiveMeasure::class:
                            $outputval = sprintf('%1$s %2$s %3$s', $data['typename'] ? $data['typename'] . " |" : "", $data['nameCat'] ? $data['nameCat'] . " |" : "", $data[$field]) ?? "";
                            break;
                        case "PluginDlteamsLegalBasi":

                            $outputval = sprintf('%1$s %2$s', $data['typename'] ? $data['typename'] . " |" : "", $data[$field]) ?? "";
                            break;
                        case PluginDlteamsAccountKey::class:
                            $outputval = sprintf('%1$s %2$s', $data['directory_name'] ? $data['directory_name'] . " |" : "", $data[$field]) ?? "";
                            break;
                        default:
                            $outputval = $data[$field] ?? "";
                            break;
                    }
                }

                $ID = $data['id'];
                $addcomment = "";
                $title = $outputval;
                if (isset($data["comment"])) {
                    if (isset($data['transcomment']) && !empty($data['transcomment'])) {
                        $addcomment .= $data['transcomment'];
                    } else {
                        $addcomment .= $data["comment"];
                    }

                    $title = sprintf(__('%1$s - %2$s'), $title, $addcomment);
                }
                if (
                    $_SESSION["glpiis_ids_visible"]
                    || (strlen($outputval) == 0)
                ) {
                    //TRANS: %1$s is the name, %2$s the ID
                    $outputval = sprintf(__('%1$s (%2$s)'), $outputval, $ID);
                }
                if ($displaywith) {
                    foreach ($post['displaywith'] as $key) {
                        if (isset($data[$key])) {
                            $withoutput = $data[$key];
                            if (isForeignKeyField($key)) {
                                $withoutput = Dropdown::getDropdownName(
                                    getTableNameForForeignKeyField($key),
                                    $data[$key]
                                );
                            }
                            if ((strlen($withoutput) > 0) && ($withoutput != '&nbsp;')) {
                                $outputval = sprintf(__('%1$s - %2$s'), $outputval, $withoutput);
                            }
                        }
                    }
                }
                $datastoadd[] = [
                    'id' => $ID,
                    'text' => $outputval,
                    'title' => $title
                ];
                $count++;
            }
            if ($multi) {
                if (count($datastoadd)) {
                    $datas[] = [
                        'text' => Dropdown::getDropdownName("glpi_entities", $prev),
                        'children' => $datastoadd,
                        'itemtype' => "Entity",
                    ];
                }
            } else {
                if (count($datastoadd)) {
                    $datas = array_merge($datas, $datastoadd);
                }
            }
        }
    }

    $ret['results'] = \Glpi\Toolbox\Sanitizer::unsanitize($datas);
    $ret['count'] = $count;

    return ($json === true) ? json_encode($ret) : $ret;
}
