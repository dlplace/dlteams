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

define('broadcast_part', [
    0 => __("Editer ce traitement", 'dlteams'),
    1 => __("Third party data protection politics", 'dlteams'),
    2 => __("Employees data protection politics", 'dlteams'),
    3 => __("Rapport complet : ne pas tenir en compte la diffusion", 'dlteams')
]);

class PluginDlteamsCreatePDF extends PluginDlteamsCreatePDFBase
{
    static $rightname = 'plugin_dlteams_createpdf';
    const broadcast_part = broadcast_part;
    const REPORT_SINGLE_RECORD = 0;
    const REPORT_BROADCAST_THRIDPARTIES = 1;
    const REPORT_BROADCAST_EMPLOYEES = 2;
    const REPORT_ALL = 3;
//    const REPORT_FOR_ENTITY = 2;
//    const REPORT_BROADCAST_INTERNAL = 6;
    const REPORT_BROADCAST_DELIVERABLE = 7;


    //const FILL_COLOR = [43, 43, 43];
    const TEXT_COLOR_ONFILL = [255, 255, 255];
    //const BG_COLOR = "#2B2B2B";
    /**add by me**/
    const BG_COLOR = "#A8C0E3";
    /**add by me**/

    const TEXT_COLOR_ONBG = "#FFF";

    /**add by me**/
    const BG1_COLOR = "#447BD1";
    const FILL_COLOR = [168, 192, 227];
    /**add by me**/

    protected $entity;
    protected $controller_info;

    static protected $default_print_options = [
        'show_representative' => [
            'show' => 1,
            'show_title' => 0,
            'show_address' => 0,
            'show_phone' => 1,
            'show_email' => 1,
        ],
        'show_dpo' => [
            'show' => 1,
            'show_title' => 0,
            'show_address' => 0,
            'show_phone' => 1,
            'show_email' => 1,
        ],
        'page_orientation' => 'P',
        'show_inherited_from' => false,
        'show_comments' => false,
        'show_print_date_time' => true,
        'show_is_deleted_header' => 1,
        'show_status_in_header' => 1,
        'show_full_personaldatacategorylist' => 1,
        'show_expired_contracts' => 1,
        'show_contracs_types_header_if_empty' => 0,
        'show_record_owner' => 1,
        'show_assets_owners' => 1,
        'show_deleted_records_for_entity' => 0,
        'show_representative_dpo_per_record' => 0,
        'show_supplier_informations' => 1,
    ];
    protected bool $HTML = false;

    /**
     * Get an list of choices checked in $choices looking at $checked separated by HTML br
     * @param $choices string[] strings that contains choices
     * @param $checked array the present keys are used (in form of [id]=>"")
     * @param $radio bool set true if using radio choice (default false)
     * @return string|null
     */
    protected static function getCheckedList($choices, $checked, $radio = false)
    {
        // Put all the checked choices in $consent_text
        $consent_text = null;
        if (!$radio) {
            // Normal checkboxes : normal process
            foreach ($choices as $id => $text) {
                if (isset($checked[$id])) {
                    $consent_text .= $text;
                    $consent_text .= "<br>";
                }
            }
        } else {
            // If radio input, it's stored differently
            // Check if there is a value, otherwise "N/A"
            if (isset($checked['checked'])) $consent_text = $choices[$checked['checked']];
            else $consent_text = __("N/A", 'dlteams');
        }
        // Remove trailing <br>
        $consent_text = $consent_text ? preg_replace('/(<br>)*$/', "", $consent_text) : "";
        return $consent_text;
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {

        if (!$item->canView()) {
            return false;
        }

        switch ($item->getType()) {
            case PluginDlteamsRecord::class :
                return self::createTabEntry(PluginDlteamsCreatePDF::getTypeName(0), 0);
            default:
                return self::createTabEntry(PluginDlteamsCreatePDF::getTypeName(0), 0);
                break;
        }

        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {

        switch ($item->getType()) {
            case PluginDlteamsRecord::class :
                self::showForm(PluginDlteamsCreatePDF::REPORT_SINGLE_RECORD, $item->fields['id']);
                break;
            case PluginDlteamsDeliverable::class:
                self::showEditTabForDeliverable(PluginDlteamsCreatePDF::REPORT_SINGLE_RECORD, $item->fields['id']);
                break;

            case PluginDlteamsProcedure::class:
                self::showEditTabForProcedure(PluginDlteamsCreatePDF::REPORT_SINGLE_RECORD, $item->fields['id']);
                break;
        }

        return true;
    }

    protected function setEntityAndControllerInfo($entity_id)
    {

        $this->entity = new Entity();

        $this->controller_info = PluginDlteamsControllerInfo::getFirstControllerInfo($entity_id);
        if (is_null($this->controller_info)) {
        } else {
            $this->entity->getFromDB($this->controller_info->fields['entities_id']);
        }

    }

    static function showConfigFormElements($config = [], $record_id = -1)
    {

        $record = new PluginDlteamsRecord();
        $record->getFromDB($record_id);
        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Print logo", 'dlteams') . "</td>";
        echo "<td width='75%'>";

        /**add by me**/
//        Dropdown::showYesNo('print_first_page', 0);
        empty($record->fields["print_logo"]) || !$record->fields["print_logo"] ? $checked = '' : $checked = 'checked';
        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title='Imprimer le logo'>
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='print_logo' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";
        /**add by me**/
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Imprimer les commentaires", 'dlteams') . "</td>";
        echo "<td width='75%'>";

        /**add by me**/
//        Dropdown::showYesNo('print_first_page', 0);
        empty($record->fields["print_comments"]) || !$record->fields["print_comments"] ? $checked = '' : $checked = 'checked';
        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title='Imprimer les commentaires'>
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='print_comments' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";
        /**add by me**/
        echo "</td>";
        echo "</tr>";

        if (!isset($config['report_type'])) {
            $config['report_type'] = PluginDlteamsCreatePDF::REPORT_SINGLE_RECORD;
        }


        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Show print date/time", 'dlteams') . "</td>";
        echo "<td>";
//        Dropdown::showYesNo('show_print_date_time', $config['show_print_date_time']);
        false ? $checked = '' : $checked = 'checked';
        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title=\"" . addslashes(__("Show print date/time", 'dlteams')) . "\">
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='show_print_date_time' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";
        echo "</td>";
        echo "</tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Show supplier information", 'dlteams') . "</td>";
        echo "<td>";
//        Dropdown::showYesNo('show_supplier_informations', $config['show_supplier_informations']);
        false ? $checked = '' : $checked = 'checked';
        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title=\"" . __("Show supplier information", 'dlteams') . "\">
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='show_supplier_informations' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";
        echo "</td>";
        echo "</tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Choose dlteams folder", 'dlteams') . "</td>";
        echo "<td colspan='2' style='display: flex; gap: 5px; align-items: center'>";

        $links = static::getPublicationFoldersLinks();

        $links_list = [];
        foreach ($links as $link) {
            array_push($links_list, $link);
        }

        Dropdown::show(Link::class, [
            'name' => 'choosen_publication_folder',
            'value' => $record->fields["links_id"]
        ]);


        $record = new PluginDlteamsRecord();
        $record->getFromDB($record_id);
        if ($record && isset($record->fields["links_id"]) && $record->fields["links_id"]) {
            $link = new Link();
            $link->getFromDB($record->fields["links_id"]);
            $folder_link = $link->fields["link"];
            echo "<div> <a class='btn btn-outline-secondary' style='display: block' target='_blank' href='" . $folder_link . "' id='btn_publication_folder'><i class='fa fa-eye'></i></a> </div>";
        } else
            echo "<div> <a class='btn btn-outline-secondary' style='display: none' target='_blank' id='btn_publication_folder'><i class='fa fa-eye'></i></a> </div>";
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Document URL", 'dlteams') . "</td>";
        echo "<td width='75%' style='display: flex; align-items: center'>";

        $document_url = self::slugifyString($record->fields["name"]);

        $entity = new Entity();
        $entity->getFromDB($record->fields["entities_id"]);
        $entity_name = $entity->fields["name"];
        $slugified_entity_name = PluginDlteamsUtils::slugify($entity_name);

        echo Html::input('document_url', ['value' => $slugified_entity_name . "-" . $record->fields["number"], 'size' => 60]);

        if ($record && isset($record->fields["links_id"]) && $record->fields["links_id"]) {
            $link_text = $link->fields["link"] . "/" . $slugified_entity_name . "-" . $record->fields["number"] . ".html";
            echo "&nbsp;<a target='_blank' id='link_to_published' href='" . $link_text . "'><i class=\"fas fa-link\"></i></a>";
        }
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Document title", 'dlteams') . "</td>";
        echo "<td width='75%'>";


        $document_name = $record->fields["name"];
        echo Html::input('document_title', ['value' => $document_name, 'size' => 60]);
        echo "</td></tr>";

        echo "
        <script>
    $('select[name=choosen_publication_folder]').on('change', function (e) {
        if ($(this).val() != '0') {
            document.getElementById('btn_publication_folder').style.display = 'block';
            // alert($(this).val());


            $.ajax({
                url: '/marketplace/dlteams/ajax/get_publication_folder_link.php',
                type: 'POST',
                data: {
                    'folder_id': $(this).val()
                },
                success: function (html) {
                    // $('#document_bloc').html(html);
                    document.getElementById('btn_publication_folder').setAttribute('href', html);
                }
            });
        } else
            document.getElementById('btn_publication_folder').style.display = 'none';
    })

</script>
        ";
    }

    static function getPublicationFoldersLinks()
    {
        global $DB;

        $query = [
            "FROM" => Link::getTable()
        ];

        return $DB->request($query);
    }

    static function showDeliverableConfigElements($config = [], $deliverable_id = 1)
    {

        $deliverable = new PluginDlteamsDeliverable();
        $deliverable->getFromDB($deliverable_id);

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Print logo", 'dlteams') . "</td>";
        echo "<td width='75%'>";

        /**add by me**/
//        Dropdown::showYesNo('print_first_page', 0);
        empty($deliverable->fields["print_logo"]) || !$deliverable->fields["print_logo"] ? $checked = '' : $checked = 'checked';
        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title='Imprimer le logo'>
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='print_logo' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";
        /**add by me**/
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Print the first page", 'dlteams') . "</td>";
        echo "<td width='75%'>";

        /**add by me**/
//        Dropdown::showYesNo('print_first_page', 0);
        empty($deliverable->fields["is_firstpage"]) || !$deliverable->fields["is_firstpage"] ? $checked = '' : $checked = 'checked';
        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title='Imprimer les commentaires'>
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='print_first_page' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";
        /**add by me**/
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Print comments", 'dlteams') . "</td>";
        echo "<td width='75%' align='left'>";

        /**add by me**/
//        echo Html::input('document_title', ['value' => self::slugifyString($deliverable->fields["name"]), 'size' => 60]);
        empty($deliverable->fields["is_comment"]) || !$deliverable->fields["is_comment"] ? $checked = '' : $checked = 'checked';
        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title='Imprimer les commentaires'>
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='print_comments' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";
        /**add by me**/
        echo "</td>";
        echo "</tr>";


        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Empêcher les copies/coller", 'dlteams') . "</td>";
        echo "<td width='75%' align='left'>";

        /**add by me**/
//        echo Html::input('document_title', ['value' => self::slugifyString($deliverable->fields["name"]), 'size' => 60]);
        $checked = 'checked';
        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title='Empêcher les copies/coller'>
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='prevent_contextmenu' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";
        /**add by me**/
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Choose dlteams folder", 'dlteams') . "</td>";
        echo "<td colspan='2' style='display: flex; gap: 5px; align-items: center'>";

        $orientation = [];

        $links = static::getPublicationFoldersLinks();

        $links_list = [];
        foreach ($links as $link) {
            array_push($links_list, $link);
//            array_push($links_list, [$link['link'] => $link['name']." ( ".$link['link']." )"]);
        }

        Dropdown::show(Link::class, [
            'name' => 'choosen_publication_folder',
            'value' => $deliverable->fields["links_id"]
        ]);
        $link = new Link();
        $link->getFromDB($deliverable->fields["links_id"]);
        if ($deliverable->fields["links_id"]) {
            $folder_link = $link->fields["link"];
            echo "<div> <a class='btn btn-outline-secondary' style='display: block' target='_blank' href='" . $folder_link . "' id='btn_publication_folder'><i class='fa fa-eye'></i></a> </div>";
        } else
            echo "<div> <a class='btn btn-outline-secondary' style='display: none' target='_blank' id='btn_publication_folder'><i class='fa fa-eye'></i></a> </div>";
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Document URL", 'dlteams') . "</td>";
        echo "<td width='75%' style='display: flex; align-items: center'>";

        if ($deliverable->fields["document_name"])
            $document_url = self::slugifyString($deliverable->fields["document_name"]);
        else
            $document_url = self::slugifyString($deliverable->fields["name"]);

        echo Html::input('document_url', ['value' => $document_url, 'size' => 60]);

        if ($deliverable->fields["links_id"]) {
            $link_text = $link->fields["link"] . "/" . $document_url . ".html";
            echo "&nbsp;<a target='_blank' href='" . $link_text . "'><i class=\"fas fa-link\"></i></a>";
        }
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Document title", 'dlteams') . "</td>";
        echo "<td width='75%'>";

        if ($deliverable->fields["document_title"])
            $document_name = $deliverable->fields["document_title"];
        else
            $document_name = $deliverable->fields["name"];
        echo Html::input('document_title', ['value' => $document_name, 'size' => 60]);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Document content", 'dlteams') . "</td>";
        echo "<td width='75%'>";
        $cols = 100;
        $rows = 60;
        Html::textarea(['name' => 'document_content',
            'value' => empty($deliverable->fields["document_content"]) ? "" : $deliverable->fields["document_content"],
            'enable_fileupload' => false,
            'enable_richtext' => true,
            'cols' => $cols,
            'rows' => $rows
        ]);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Document comment", 'dlteams') . "</td>";
        echo "<td width='75%'>";
        Html::textarea(['name' => 'document_comment',
            'value' => empty($deliverable->fields["document_comment"]) ? $deliverable->fields["content"] : $deliverable->fields["document_comment"],
            'enable_fileupload' => false,
            'enable_richtext' => true,
            'cols' => $cols,
            'rows' => $rows
        ]);
        echo "</td></tr>";

        echo "
        <script>
    $('select[name=choosen_publication_folder]').on('change', function (e) {
        if ($(this).val() != '0') {
            document.getElementById('btn_publication_folder').style.display = 'block';
            // alert($(this).val());


            $.ajax({
                url: '/marketplace/dlteams/ajax/get_publication_folder_link.php',
                type: 'POST',
                data: {
                    'folder_id': $(this).val()
                },
                success: function (html) {
                    // $('#document_bloc').html(html);
                    document.getElementById('btn_publication_folder').setAttribute('href', html);
                }
            });
        } else
            document.getElementById('btn_publication_folder').style.display = 'none';
    })

</script>
        ";
    }


    static function showProcedueConfigElements($config = [], $procedure_id = 1)
    {

        $procedure = new PluginDlteamsProcedure();
        $procedure->getFromDB($procedure_id);

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Print logo", 'dlteams') . "</td>";
        echo "<td width='75%'>";

        /**add by me**/
//        Dropdown::showYesNo('print_first_page', 0);
        empty($procedure->fields["print_logo"]) || !$procedure->fields["print_logo"] ? $checked = '' : $checked = 'checked';
        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title='Imprimer le logo'>
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='print_logo' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";
        /**add by me**/
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Print the first page", 'dlteams') . "</td>";
        echo "<td width='75%'>";

        /**add by me**/
//        Dropdown::showYesNo('print_first_page', 0);
        empty($procedure->fields["is_firstpage"]) || !$procedure->fields["is_firstpage"] ? $checked = '' : $checked = 'checked';
        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title='Imprimer les commentaires'>
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='print_first_page' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";
        /**add by me**/
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Print comments", 'dlteams') . "</td>";
        echo "<td width='75%' align='left'>";

        /**add by me**/
//        echo Html::input('document_title', ['value' => self::slugifyString($deliverable->fields["name"]), 'size' => 60]);
        empty($procedure->fields["is_comment"]) || !$procedure->fields["is_comment"] ? $checked = '' : $checked = 'checked';
        echo "<div id='switchmodelcontainer' style='width:100%; display: flex; margin-bottom: 1px; margin-right: 25px;'>
                                        <label class='form-check form-switch btn-xs  me-0 me-sm-1 px-1 py-1 mb-0 flex-column-reverse flex-sm-row'
                                            title='Imprimer les commentaires'>
                                         <input type='checkbox' class='form-check-input ms-0 me-1 mt-0' name='print_comments' role='button' autocomplete='off' $checked/>
                                         <span class='mb-1 mb-sm-0'>
                                         </span>
                                      </label></div>";
        /**add by me**/
        echo "</td>";
        echo "</tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td>" . __("Choose dlteams folder", 'dlteams') . "</td>";
        echo "<td colspan='2' style='display: flex; gap: 5px; align-items: center'>";

        $orientation = [];

        $links = static::getPublicationFoldersLinks();

        $links_list = [];
        foreach ($links as $link) {
            array_push($links_list, $link);
//            array_push($links_list, [$link['link'] => $link['name']." ( ".$link['link']." )"]);
        }

        Dropdown::show(Link::class, [
            'name' => 'choosen_publication_folder',
            'value' => $procedure->fields["links_id"]
        ]);
        $link = new Link();
        $link->getFromDB($procedure->fields["links_id"]);
        if ($procedure->fields["links_id"]) {
            $folder_link = $link->fields["link"];
            echo "<div> <a class='btn btn-outline-secondary' style='display: block' target='_blank' href='" . $folder_link . "' id='btn_publication_folder'><i class='fa fa-eye'></i></a> </div>";
        } else
            echo "<div> <a class='btn btn-outline-secondary' style='display: none' target='_blank' id='btn_publication_folder'><i class='fa fa-eye'></i></a> </div>";
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Document URL", 'dlteams') . "</td>";
        echo "<td width='75%' style='display: flex; align-items: center'>";

        if ($procedure->fields["document_name"])
            $document_url = self::slugifyString($procedure->fields["document_name"]);
        else
            $document_url = self::slugifyString($procedure->fields["name"]);

        echo Html::input('document_url', ['value' => $document_url, 'size' => 60]);

        if ($procedure->fields["links_id"]) {
            $link_text = $link->fields["link"] . "/" . $document_url . ".html";
            echo "&nbsp;<a target='_blank' href='" . $link_text . "'><i class=\"fas fa-link\"></i></a>";
        }
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Document title", 'dlteams') . "</td>";
        echo "<td width='75%'>";

        if ($procedure->fields["document_title"])
            $document_name = $procedure->fields["document_title"];
        else
            $document_name = $procedure->fields["name"];
        echo Html::input('document_title', ['value' => $document_name, 'size' => 60]);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Document content", 'dlteams') . "</td>";
        echo "<td width='75%'>";
        $cols = 100;
        $rows = 60;
        Html::textarea(['name' => 'document_content',
            'value' => empty($procedure->fields["document_content"]) ? "" : $procedure->fields["document_content"],
            'enable_fileupload' => false,
            'enable_richtext' => true,
            'cols' => $cols,
            'rows' => $rows
        ]);
        echo "</td></tr>";

        echo "<tr class='tab_bg_1'>";
        echo "<td width='25%'>" . __("Document comment", 'dlteams') . "</td>";
        echo "<td width='75%'>";
        Html::textarea(['name' => 'document_comment',
            'value' => empty($procedure->fields["document_comment"]) ? $procedure->fields["content"] : $procedure->fields["document_comment"],
            'enable_fileupload' => false,
            'enable_richtext' => true,
            'cols' => $cols,
            'rows' => $rows
        ]);
        echo "</td></tr>";

        echo "
        <script>
    $('select[name=choosen_publication_folder]').on('change', function (e) {
        if ($(this).val() != '0') {
            document.getElementById('btn_publication_folder').style.display = 'block';
            // alert($(this).val());


            $.ajax({
                url: '/marketplace/dlteams/ajax/get_publication_folder_link.php',
                type: 'POST',
                data: {
                    'folder_id': $(this).val()
                },
                success: function (html) {
                    // $('#document_bloc').html(html);
                    document.getElementById('btn_publication_folder').setAttribute('href', html);
                }
            });
        } else
            document.getElementById('btn_publication_folder').style.display = 'none';
    })

</script>
        ";
    }

    static function showSelectType($record = [])
    {

        echo "<table class='tab_cadre_fixe'>";
        echo "<tr><th>" . __("Choose which document you'd like to generate according to the broadcast selected", 'dlteams') . "</th></tr>";
        $i = 0;
        foreach (self::broadcast_part as $id => $text) {

//            $id += 3;
//            $text ??= __("Traitement actuel", 'dlteams');
            if ($i === 0)
                echo "<tr><td><input checked type='radio' name='report_type' value='$id' id='$id'>";
            else
                echo "<tr><td><input type='radio' name='report_type' value='$id' id='$id'>";
            echo "<label for='$id'>&nbsp;$text</label></tr></td>";
            $i++;
        }
        echo "<input type='hidden' name='action' value='print'>";
        echo "</table>";

        if (isset($record['id']))
            $record_number = $record['number'];
        else
            $record_number = 0;

        if (isset($record['name']))
            $record_name = $record['name'];
        else
            $record_name = '';

//        $link = new Link();
//        $link->getFromDB($record['id']);
//
//        $link_text = $link->fields["link"];

        $temp_record = new PluginDlteamsRecord();
        $temp_record->getFromDB($record['id']);
        $entity = new Entity();
        $entity->getFromDB($temp_record->fields["entities_id"]);
        $entity_name = $entity->fields["name"];
        $slugified_entity_name = PluginDlteamsUtils::slugify($entity_name);

        $link = new Link();
        $link->getFromDB($temp_record->fields["links_id"]);
        $folder_link = isset($link->fields["link"]) && $link->fields["link"] ? $link->fields["link"] : "";
        echo "
        <script>

        
        $(document).ready(function() {
            
            $('input[type=radio][name=report_type]').change(function() {
                var selectedValue = $(this).val();
                    var url_field = $('input[name=document_url]');
                    var title_field = $('input[name=document_title]');
                    var link_to_published = $('#link_to_published');
                    var folder_link = '" . $folder_link . "';
                    
                    
                    
                    switch (selectedValue) {
                      case '0':
                          url_field.val('" . $slugified_entity_name . '-' . $record_number . "');
                          title_field.val('" . addslashes($record_name) . "');
                          var link = folder_link+'/'+'" . $slugified_entity_name . '-' . $record_number . ".html';
                          link_to_published.attr('href', link);
                          break;
                      case '1':
                          url_field.val('" . $slugified_entity_name . "-registre-des-traitements-tiers');
                          title_field.val('Registre des traitements concernant les tiers pour " . $entity_name . "');
                          var link = folder_link+'/'+'" . $slugified_entity_name . "-registre-des-traitements-tiers.html';
                          link_to_published.attr('href', link);
                          break;
                      case '2':
                          url_field.val('" . $slugified_entity_name . "-registre-des-traitements-employes');
                          title_field.val('Registre des traitements concernant les employés pour " . $entity_name . "');
                          var link = folder_link+'/'+'" . $slugified_entity_name . "-registre-des-traitements-employes.html';
                          link_to_published.attr('href', link);
                          break;
                      case '3':
                          url_field.val('" . $slugified_entity_name . "-registre-des-traitements-version-interne.html');
                          title_field.val('Registre des traitements pour " . $entity_name . "');
                          var link = folder_link+'/'+'" . $slugified_entity_name . "-registre-des-traitements-version-interne.html';
                          link_to_published.attr('href', link);
                          break;
                    }
            });
            
            
        });
    </script>
        ";
    }

    static function showPrepareForm($report_type)
    {

        echo "<div class='glpi_tabs pdf_tabs'>";
        echo '<div class="center vertical ui-tabs ui-widget ui-widget-content ui-corner-all ui-tabs-vertical ui-helper-clearfix ui-corner-left">';
        echo '<div class="ui-tabs-panel ui-widget-content ui-corner-bottom" aria-live="polite" role="tabpanel" aria-expanded="true" aria-hidden="false">';
        echo '<div class="firstbloc">';

        self::showForm($report_type);

        echo "</div>";
        echo "</div>";
        echo "</div>";
        echo "</div>";
        Html::footer();
    }

    static function showForm($report_type, $record_id = -1)
    {

        global $CFG_GLPI;
        $record = new PluginDlteamsRecord();
        $record->getFromDB($record_id);

        echo "<form name='form' method='POST' action=\"" . $CFG_GLPI['root_doc'] . "/marketplace/dlteams/front/createpdf.php\" enctype='multipart/form-data'>";

        echo "<div class='spaced' id='tabsbody'>";

        echo "<table class='tab_cadre_fixe' id='mainformtable'>";
        echo "<tbody>";
        echo "<tr class='headerRow'>";
        echo "<th colspan='3' class=''>" . __("PDF creation settings", 'dlteams') . "</th>";
        echo "</tr>";

        $_config = PluginDlteamsCreatePDF::getDefaultPrintOptions();
        $_config['report_type'] = $report_type;
        PluginDlteamsCreatePDF::showConfigFormElements($_config, $record_id);

        echo "</table>";
        echo "</div>";
        echo "<input type='hidden' name='report_type' value=\"" . $report_type . "\">";
        if ($report_type == PluginDlteamsCreatePDF::REPORT_SINGLE_RECORD) {
            echo "<input type='hidden' name='record_id' value=\"" . $record_id . "\">";
        }

//        if ($report_type != PluginDlteamsCreatePDF::REPORT_SINGLE_RECORD)
        self::showSelectType($record->fields);

        echo "<input type='hidden' name='action' value=\"print\">";
        echo "<input type='hidden' name='guid_value' value='55'>";
        echo "<input type='submit' class='submit' name='save' value='" . __("Save") . "' />";
        echo "&nbsp;";
        echo "<input type='submit' class='submit' name='createpdf' value='" . __("Generate PDF", 'dlteams') . "' />";
        echo "&nbsp;";
        echo "<input type='submit' class='submit' name='createhtml' value='" . __("Generate HTML", 'dlteams') . "' />";
        echo "&nbsp;";
        echo "<input type='submit' class='submit' name='createhtmlppd' value='" . __("Publier DLteams", 'dlteams') . "' />";
        Html::closeForm();
    }


    static function showEditTabForDeliverable($report_type, $deliverable_id = -1)
    {
        global $CFG_GLPI;

        echo "<form name='form' method='POST' action=\"" . Toolbox::getItemTypeFormURL("PluginDlteamsDeliverable") . "\">";
        echo "<div class='spaced' id='tabsbody'>";

        echo "<table class='tab_cadre_fixe' id='mainformtable'>";
        echo "<tbody>";
        echo "<tr class='headerRow'>";
        echo "<th colspan='3' class=''>" . __("PDF creation settings", 'dlteams') . "</th>";
        echo "</tr>";

        $_config = PluginDlteamsCreatePDF::getDefaultPrintOptions();
        $_config['report_type'] = $report_type;
        PluginDlteamsCreatePDF::showDeliverableConfigElements($_config, $deliverable_id);


        echo "</table>";
        echo "</div>";
        echo "<input type='hidden' name='report_type' value=\"" . $report_type . "\">";
        echo "<input type='hidden' name='deliverable_id' value=\"" . $deliverable_id . "\">";
//        echo "<input type='hidden' name='items_id' value=\"" . $deliverable_id . "\">";

        echo "<input type='hidden' name='action' value=\"print\">";

        echo "<input type='hidden' name='guid_value' value='55'>";
        echo "<input type='submit' class='submit' name='save' value='" . __("Save") . "' />";
        echo "&nbsp;";
        echo "<input type='submit' class='submit' name='edit_pdf' value='" . __("Edit / Print PDF", 'dlteams') . "' />";
        echo "&nbsp;";
        echo "<input type='submit' class='submit' name='edit_html' value='" . __("Generate HTML", 'dlteams') . "' />";
        echo "&nbsp;";

        echo "<input type='submit' class='submit' name='publish_dlteams' value='" . __("Publier DLteams", 'dlteams') . "' />";
        Html::closeForm();


        $rand = mt_rand();
        $canedit = true;

        global $DB;

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
                PluginDlteamsDeliverable_Variable_Item::getTable() . '.items_id' => $deliverable_id,
                PluginDlteamsDeliverable_Variable_Item::getTable() . '.itemtype' => "PluginDlteamsDeliverable"
            ],
            'ORDER' => ['name ASC'],
        ];
        $iterator = $DB->request($request, "", true);

        $number = count($iterator);
        $items_list = [];
        $used = [];
//        foreach ($iterator as $var){
//            $used[$var["id"]] = $var["id"];
//        }

        if ($canedit) {
            echo "<form name='allitemitem_form$rand' id='allitemitem_form$rand' method='post'
         action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . PluginDlteamsDeliverable::class . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $deliverable_id . "' />";
            echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsDeliverable_Variable::getType() . "' />";
            // echo "<input type='hidden' name='comment' value='".$this->fields['comment']."' />";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th style='text-align:center!important'>" . __("Indiquez les variables et leurs valeurs", 'dlteams') . "</th></tr>";
            echo "</table>";

            echo "<table class='tab_cadre_fixe'>";
            echo "<td style='text-align:right'>";
            echo __("Choisissez une variable", 'dlteams');
            echo "</td>";
            echo "<td style='width: 15%'>";
            PluginDlteamsDeliverable_Variable::dropdown([
                'addicon' => PluginDlteamsDeliverable_Variable::canCreate(),
                'name' => 'items_id',
                'used' => $used,
                'width' => '300px'
            ]);
            echo "</td>";


            echo "<td style='text-align:right'>";
            echo __("A remplacer par", 'dlteams');
            echo "</td>";

            $comment = Html::cleanInputText("");
            echo "<td>" . "<textarea style='width:100%' rows='1' name='comment' >" . $comment . "</textarea>" . "</td>";
            echo "<td class='left'><input type='submit' name='link_element' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin:0px auto!important'>" . "</td>";
            echo "<tr>";

//            echo "<td style='text-align:right'>" . __("Comment") . " " . "</td>";
//            $comment = Html::cleanInputText("");
//            echo "<td>" . "<textarea style='width:100%' rows='1' name='comment' >" . $comment . "</textarea>" . "</td>";
            echo "<td style='text-align: center' colspan='5'><input type='submit' name='make_replacement' value=\"" . _sx('button', 'Effectuer les remplacements') . "\" class='submit' style='margin:0px auto!important'>" . "</td>";
            echo "</tr>";

            echo "</table>";

            Html::closeForm();
        }


        //var_dump(count($iterator));
        // while ($data = $iterator->next()) {
        foreach ($iterator as $id => $data) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($iterator) {
            echo "<div class='spaced'>";
            if ($canedit && $number) {
                Html::openMassiveActionsForm('mass' . PluginDlteamsDeliverable_Item::class . $rand);
                $massive_action_params = ['container' => 'mass' . PluginDlteamsDeliverable_Item::class . $rand,
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
                $header_top .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsDeliverable_Variable_Item::class . $rand);
                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsDeliverable_Variable_Item::class . $rand);
                $header_end .= "</th>";
            }

            $header_end .= "<th width='20%' style='text-align:left'>" . __("Variable", 'dlteams') . "</th>";
            // $header_end .= "<th width='20%'>" . __("Type", 'dlteams') . "</th>";
            $header_end .= "<th width='80%' style='text-align:left'>" . __("Valeur", 'dlteams') . "</th>";
            $header_end .= "</tr>";

            echo $header_begin . $header_top . $header_end;
            foreach ($items_list as $data) {
                if ($data['name']) {
                    echo "<tr class='tab_bg_1'>";

                    if ($canedit && $number) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox(PluginDlteamsDeliverable_Variable_Item::class, $data['linkid']);
                        echo "</td>";
                    }

                    $link = $data['name'];
                    if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
                        $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
                    }
                    $name = "<a target='_blank' href=\"" . PluginDlteamsDeliverable_Variable::getFormURLWithID($data['id']) . "\">" . $link . "</a>";

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
    }


    static function showEditTabForProcedure($report_type, $procedure_id = -1)
    {
        global $CFG_GLPI;

        echo "<form name='form' method='POST' action=\"" . Toolbox::getItemTypeFormURL(PluginDlteamsProcedure::class) . "\">";
        echo "<div class='spaced' id='tabsbody'>";

        echo "<table class='tab_cadre_fixe' id='mainformtable'>";
        echo "<tbody>";
        echo "<tr class='headerRow'>";
        echo "<th colspan='3' class=''>" . __("PDF creation settings", 'dlteams') . "</th>";
        echo "</tr>";

        $_config = PluginDlteamsCreatePDF::getDefaultPrintOptions();
        $_config['report_type'] = $report_type;

        PluginDlteamsCreatePDF::showProcedueConfigElements($_config, $procedure_id);


        echo "</table>";
        echo "</div>";
        echo "<input type='hidden' name='report_type' value=\"" . $report_type . "\">";
        echo "<input type='hidden' name='procedure_id' value=\"" . $procedure_id . "\">";
//        echo "<input type='hidden' name='items_id' value=\"" . $deliverable_id . "\">";

        echo "<input type='hidden' name='action' value=\"print\">";

        echo "<input type='hidden' name='guid_value' value='55'>";
        echo "<input type='submit' class='submit' name='save' value='" . __("Save") . "' />";
        echo "&nbsp;";
        echo "<input type='submit' class='submit' name='edit_pdf' value='" . __("Edit / Print PDF", 'dlteams') . "' />";
        echo "&nbsp;";
        echo "<input type='submit' class='submit' name='edit_html' value='" . __("Generate HTML", 'dlteams') . "' />";
        echo "&nbsp;";

        echo "<input type='submit' class='submit' name='publish_dlteams' value='" . __("Publier DLteams", 'dlteams') . "' />";
        Html::closeForm();


        $rand = mt_rand();
        $canedit = true;

        global $DB;

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
                PluginDlteamsProcedure_Variable_Item::getTable() . '.items_id' => $procedure_id,
                PluginDlteamsProcedure_Variable_Item::getTable() . '.itemtype' => "PluginDlteamsProcedure"
            ],
            'ORDER' => ['name ASC'],
        ];
        $iterator = $DB->request($request, "", true);

        $number = count($iterator);
        $items_list = [];
        $used = [];
//        foreach ($iterator as $var){
//            $used[$var["id"]] = $var["id"];
//        }

        if ($canedit) {
            echo "<form name='allitemitem_form$rand' id='allitemitem_form$rand' method='post'
         action='" . Toolbox::getItemTypeFormURL(PluginDlteamsElementsRGPD::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='" . PluginDlteamsProcedure::class . "' />";
            echo "<input type='hidden' name='items_id1' value='" . $procedure_id . "' />";
            echo "<input type='hidden' name='itemtype' value='" . PluginDlteamsProcedure_Variable::getType() . "' />";
            // echo "<input type='hidden' name='comment' value='".$this->fields['comment']."' />";

            echo "<table class='tab_cadre_fixe'>";
            echo "<tr class='tab_bg_2'><th style='text-align:center!important'>" . __("Indiquez les variables et leurs valeurs", 'dlteams') . "</th></tr>";
            echo "</table>";

            echo "<table class='tab_cadre_fixe'>";
            echo "<td style='text-align:right'>";
            echo __("Choisissez une variable", 'dlteams');
            echo "</td>";
            echo "<td style='width: 15%'>";
            PluginDlteamsProcedure_Variable::dropdown([
                'addicon' => PluginDlteamsProcedure_Variable::canCreate(),
                'name' => 'items_id',
                'used' => $used,
                'width' => '300px'
            ]);
            echo "</td>";


            echo "<td style='text-align:right'>";
            echo __("A remplacer par", 'dlteams');
            echo "</td>";

            $comment = Html::cleanInputText("");
            echo "<td>" . "<textarea style='width:100%' rows='1' name='comment' >" . $comment . "</textarea>" . "</td>";
            echo "<td class='left'><input type='submit' name='link_element' value=\"" . _sx('button', 'Add') . "\" class='submit' style='margin:0px auto!important'>" . "</td>";
            echo "<tr>";

//            echo "<td style='text-align:right'>" . __("Comment") . " " . "</td>";
//            $comment = Html::cleanInputText("");
//            echo "<td>" . "<textarea style='width:100%' rows='1' name='comment' >" . $comment . "</textarea>" . "</td>";
            echo "<td style='text-align: center' colspan='5'><input type='submit' name='procedures_make_replacement' value=\"" . _sx('button', 'Effectuer les remplacements') . "\" class='submit' style='margin:0px auto!important'>" . "</td>";
            echo "</tr>";

            echo "</table>";

            Html::closeForm();
        }


        //var_dump(count($iterator));
        // while ($data = $iterator->next()) {
        foreach ($iterator as $id => $data) {
            $items_list[$data['linkid']] = $data;
            $used[$data['id']] = $data['id'];
        }

        if ($iterator) {
            echo "<div class='spaced'>";
            if ($canedit && $number) {
                Html::openMassiveActionsForm('mass' . PluginDlteamsDeliverable_Item::class . $rand);
                $massive_action_params = ['container' => 'mass' . PluginDlteamsDeliverable_Item::class . $rand,
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
                $header_top .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsDeliverable_Variable_Item::class . $rand);
                $header_bottom .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsDeliverable_Variable_Item::class . $rand);
                $header_end .= "</th>";
            }

            $header_end .= "<th width='20%' style='text-align:left'>" . __("Variable", 'dlteams') . "</th>";
            // $header_end .= "<th width='20%'>" . __("Type", 'dlteams') . "</th>";
            $header_end .= "<th width='80%' style='text-align:left'>" . __("Valeur", 'dlteams') . "</th>";
            $header_end .= "</tr>";

            echo $header_begin . $header_top . $header_end;
            foreach ($items_list as $data) {
                if ($data['name']) {
                    echo "<tr class='tab_bg_1'>";

                    if ($canedit && $number) {
                        echo "<td width='10'>";
                        Html::showMassiveActionCheckBox(PluginDlteamsDeliverable_Variable_Item::class, $data['linkid']);
                        echo "</td>";
                    }

                    $link = $data['name'];
                    if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
                        $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
                    }
                    $name = "<a target='_blank' href=\"" . PluginDlteamsDeliverable_Variable::getFormURLWithID($data['id']) . "\">" . $link . "</a>";

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
    }

    protected function prepareControllerInfo()
    {

        $controller_name = __("Controller name not set.", 'dlteams');
        $info = "<strong>" . $controller_name . "</strong>";
        if (isset($this->controller_info->fields['id'])) {

            $controller_name = trim($this->controller_info->fields['controllername']);
            if (!empty($controller_name)) {

                $info = "<ul>";
                $info .= "<li>" . __("Designation") . ": <strong>" . $controller_name . "</strong>" . "</li>";

                $address = $this->entity->fields['address'];
                if (empty($address)) {
                    $address = __("N/A", 'dlteams');
                } else {
                    $address = trim($this->entity->fields['address']);
                }
                $postcode = $this->entity->fields['postcode'];
                if (empty($postcode)) {
                    $postcode = __("N/A", 'dlteams');
                } else {
                    $postcode = trim($this->entity->fields['postcode']);
                }
                $town = $this->entity->fields['town'];
                if (empty($town)) {
                    $town = __("N/A", 'dlteams');
                } else {
                    $town = trim($this->entity->fields['town']);
                }

                $state = $this->entity->fields['state'];
                if (empty($state)) {
                    $state = __("N/A", 'dlteams');
                } else {
                    $state = trim($this->entity->fields['state']);
                }
                $country = $this->entity->fields['country'];
                if (empty($country)) {
                    $country = __("N/A", 'dlteams');
                } else {
                    $country = trim($this->entity->fields['country']);
                }

                $phone = $this->entity->fields['phonenumber'];
                if (empty($phone)) {
                    $town = __("N/A", 'dlteams');
                } else {
                    $phone = trim($this->entity->fields['phonenumber']);
                }
                $email = $this->entity->fields['email'];
                if (empty($email)) {
                    $town = __("N/A", 'dlteams');
                } else {
                    $email = trim($this->entity->fields['email']);
                }
                $web = $this->entity->fields['website'];
                if (empty($web)) {
                    $town = __("N/A", 'dlteams');
                } else {
                    $web = trim($this->entity->fields['website']);
                }

                $address_full = $address . ", " . $postcode . " " . $town;
                //if ($state) {$address_full .= " " . $state;}
                if ($country) {
                    $address_full .= " ($country)";
                }

                $info .= "<li>" . __("Address") . ": <strong>" . $address_full . "</strong></li>";
                $info .= "<li>" . __("Phone") . ": <strong>" . $phone . "</strong></li>";
                $info .= "<li>" . __("Email") . ": <strong>" . $email . "</strong></li>";
                $info .= "<li>" . __("Website") . ": <strong>" . $web . "</strong></li>";

                $info .= "</ul>";

            }
        }

        $result = [
            'section' => "<h3>" . __("Controller", 'dlteams') . "</h3>",
            'value' => $info
        ];

        return $result;
    }

    protected function preparePersonelInfo($person, $caption_not_set, $section_caption)
    {

        $info = '';

        if ((isset($this->controller_info->fields['users_id_' . $person]) && !$this->controller_info->fields['users_id_' . $person]) ||
            (!isset($this->controller_info->fields['users_id_' . $person]))) {
            $info = "<strong>" . $caption_not_set . "</strong>";
        } else {

            $user = new User();
            $user->getFromDB($this->controller_info->fields['users_id_' . $person]);

            $email = new UserEmail();
            $email->getFromDBByCrit(['users_id' => $user->fields['id'], 'is_default' => 1]);

            $location = new Location();
            $location->getFromDB($user->fields['locations_id']);

            $realname = trim($user->fields['realname']);
            if (empty($realname)) {
                $realname = __("N/A", 'dlteams');
            }
            $firstname = trim($user->fields['firstname']);
            if (empty($firstname)) {
                $firstname = __("N/A", 'dlteams');
            }

            /**add by me**/
            $civility = trim($user->fields['usertitles_id']);
            if ($civility == 1) {
                $civility = 'Mr';
            } else if ($civility == 2) {
                $civility = 'Mme';
            } else {
                $civility = '';
            }
            /**add by me**/

            $info = "<ul>";
            /*$info .= "<li>" . __("Surname") . ": <strong>" . $realname . "</strong> " . __("First name") . ": <strong>" . $firstname . "</strong>" . "</li>";*/
            /**add by me**/
            $info .= "<li>" . __("Name") . ": <strong>" . $civility . "  " . strtoupper($realname) . "  " . $firstname . "</strong> " . "</li>";
            /**add by me**/

            if ($this->print_options['show_' . $person]['show_title']) {
                $title = trim(Dropdown::getDropdownName('glpi_usertitles', $user->fields['usertitles_id']));
                if (empty($title) || ($title == '&nbsp;')) {
                    $title = __("N/A", 'dlteams');
                }
                $info .= "<li>" . _x('person', "Title") . " : <strong>" . $title . "</strong></li>";
            }
            if ($this->print_options['show_' . $person]['show_address']) {

                $address = isset($location->fields['address']) ? trim($location->fields['address']) : $address = __("N/A", 'dlteams');

                $postcode = isset($location->fields['postcode']) ? trim($location->fields['postcode']) : $postcode = __("N/A", 'dlteams');

                $town = isset($location->fields['town']) ? trim($location->fields['town']) : __("N/A", 'dlteams');

                $state = isset($location->fields['state']) ? trim($location->fields['state']) : '';

                $country = isset($location->fields['country']) ? trim($location->fields['country']) : '';

                $address_full = $address . ", " . $postcode . " " . $town;
                if ($state) {
                    $address_full .= " " . $state;
                }
                if ($country) {
                    $address_full .= " " . $country;
                }

                $info .= "<li>" . __("Address") . ": <strong>" . $address_full . "</strong></li>";
            }

            if ($this->print_options['show_' . $person]['show_phone']) {
                $phone = trim($user->fields['phone']);
                if (empty($phone)) {
                    $phone = __("N/A", 'dlteams');
                }
                $info .= "<li>" . __("Phone") . ": <strong>" . $phone . "</strong>" . "</li>";
            }
            if ($this->print_options['show_' . $person]['show_email']) {
                $email = isset($email->fields['email']) ? trim($email->fields['email']) : '';
                if (empty($email)) {
                    $email = __("N/A", 'dlteams');
                }
                $info .= "<li>" . __("Email") . ": <strong>" . $email . "</strong>" . "</li>";
            }

            $info .= "</ul>";

        }

        $result = [
            'section' => "<h3>" . $section_caption . "</h3>",
            'value' => $info,
        ];

        return $result;
    }

    protected function getRecordsForEntity($record_id, $print_options = null, $entities_id = 0)
    {
        global $DB;

        if ($print_options["report_type"] == self::REPORT_SINGLE_RECORD) {
            $record = new PluginDlteamsRecord();
            $record->getFromDB($record_id);

            return $record;
        } else {
            $request = [
                'SELECT' => 'glpi_plugin_dlteams_records.*',
                'FROM' => 'glpi_plugin_dlteams_records',
                'ORDER' => 'glpi_plugin_dlteams_records.completenumber',
                'WHERE' => [
                    'entities_id' => $entities_id,
                    'is_deleted' => 0
                ]
            ];

            if ($print_options["report_type"] != self::REPORT_ALL) {
                $request["WHERE"]["states_id"] = $print_options["report_type"];
            }


            $records_list = $DB->request($request);
            return $records_list;
        }
    }

    protected function getControllerName()
    {

        if (isset($this->controller_info->fields['controllername']) && !empty(trim($this->controller_info->fields['controllername']))) {

            return trim($this->controller_info->fields['controllername']);
        } else {
            return __("Controller information not set.", 'dlteams');
        }

    }

    function generateReport($generator_options, $print_options)
    {

        $this->preparePrintOptions($print_options);
        $this->preparePDF();


        $temp_record = new PluginDlteamsRecord();
        $temp_record->getFromDB($generator_options['record_id']);

        switch ($generator_options['report_type']) {
            case self::REPORT_SINGLE_RECORD:
                $record_id = $generator_options['record_id'];
                $record = new PluginDlteamsRecord();
                $record->getFromDB($record_id);
                break;
            default:
                $record = PluginDlteamsCreatePDF::getRecordsForEntity($generator_options['record_id'], $print_options, $temp_record->fields["entities_id"]);
                break;
        }


        $this->setEntityAndControllerInfo($temp_record->fields["entities_id"]);

//        $this->printHeader();
//        $this->printCoverPage($generator_options['report_type'], $record, $entities_id);


        $this->printRecordHeader($temp_record);
        $i = 0;
        if ($record instanceof DBmysqlIterator) {

            $lastpage = true;
            foreach ($record as $key => $item) {
                $rec = new PluginDlteamsRecord();
                $rec->getFromDB($item['id']);

                if (count($record) !== $i + 1)
                    $lastpage = false;
                $this->addPageForRecord($rec, $generator_options, $i);
                $i++;
            }
        } else if ($record instanceof PluginDlteamsRecord) {
            $rec = new PluginDlteamsRecord();
            $rec->getFromDB($record->fields['id']);
            $this->addPageForRecord($rec, $generator_options);
        } else {
            $this->addPageForRecord($record, $generator_options);
        }

        $this->printRecordFooter($print_options);
    }

    function deliverableGenerateReport($print_options, PluginDlteamsDeliverable $deliverable)
    {

        $this->preparePrintOptions($print_options);
        $this->preparePDF();


        $entities_id = $deliverable->fields["entities_id"];

        $this->setEntityAndControllerInfo($entities_id);

        $this->generalPrintHeader();
//        $this->printCoverPage($generator_options['report_type'], $record, $entities_id);


//        $this->deliverablePrintCoverPage(static::REPORT_BROADCAST_DELIVERABLE, $deliverable, $entities_id);


        $this->addPageForDeliverable($deliverable, static::REPORT_BROADCAST_DELIVERABLE, $print_options);
    }

    function procedureGenerateReport($print_options, PluginDlteamsProcedure $procedure)
    {

        $this->preparePrintOptions($print_options);
        $this->preparePDF();


        $entities_id = $procedure->fields["entities_id"];

        $this->setEntityAndControllerInfo($entities_id);

        $this->generalPrintHeader();
//        $this->printCoverPage($generator_options['report_type'], $record, $entities_id);


//        $this->deliverablePrintCoverPage(static::REPORT_BROADCAST_DELIVERABLE, $deliverable, $entities_id);


        $this->addPageForProcedure($procedure, static::REPORT_BROADCAST_DELIVERABLE, $print_options);
    }

    function generateHtml($generator_options, $print_options)
    {

        $this->HTML = true;

//        $filename = $this->generateFilename($generator_options);
//        $filename = $this->slugify($filename);
        $filename = $this->slugify($generator_options["document_url"]);
        header("Content-Disposition: attachement; filename=\"$filename.html\"");
//      header("Content-Disposition: inline; filename=\"$filename.html\"");

        $this->preparePrintOptions($print_options);

        $temp_record = new PluginDlteamsRecord();
        $temp_record->getFromDB($generator_options['record_id']);

        switch ($generator_options['report_type']) {
            case self::REPORT_SINGLE_RECORD:
                $record_id = $generator_options['record_id'];
                $record = new PluginDlteamsRecord();
                $record->getFromDB($record_id);
                break;
            default:
                $record = PluginDlteamsCreatePDF::getRecordsForEntity($generator_options['record_id'], $print_options, $temp_record->fields["entities_id"]);
                break;
        }

        $this->setEntityAndControllerInfo($temp_record->fields["entities_id"]);

//        $this->printHtmlHead($filename);

//        echo "<header>";
//        $this->printHeader();

//        echo "</header><div class='cover'>";
//        $this->printCoverPage($generator_options['report_type'], $record, $entities_id);

//        echo "</div><hr><div class='content'>";
        $this->printRecordHeader($temp_record);

        $i = 0;
        if ($record instanceof DBmysqlIterator) {
            foreach ($record as $key => $item) {
                $rec = new PluginDlteamsRecord();
                $rec->getFromDB($item['id']);
                $this->addPageForRecord($rec, $generator_options, $i);
                $i++;
            }
        } else if ($record instanceof PluginDlteamsRecord) {
            $rec = new PluginDlteamsRecord();
            $rec->getFromDB($record->fields['id']);
            $this->addPageForRecord($rec, $generator_options);
        } else {
            $this->addPageForRecord($record, $generator_options);
        }
//        echo "</div>";

//        $this->printHtmlEnd();
    }

    function deliverableGenerateHtml($print_options, PluginDlteamsDeliverable $deliverable)
    {

        $this->HTML = true;

        $generator_options = [];
        $generator_options["report_type"] = PluginDlteamsCreatePDF::REPORT_BROADCAST_DELIVERABLE;

//        $filename = $this->generateFilename($generator_options);
        $filename = $deliverable->fields["document_name"];
        header("Content-Disposition: attachement; filename=\"$filename.html\"");

        $this->preparePrintOptions($print_options);


        $entities_id = $_SESSION['glpiactive_entity'];

        $this->setEntityAndControllerInfo($entities_id);


//        $this->printHtmlHead($filename);

//        echo "<header>";
//        $this->printHeader();
//
//
//        echo "</header>";
//        echo "<div class='cover'>";

//        if ((bool)$print_options['print_first_page'])
//            $this->deliverablePrintCoverPage(static::REPORT_BROADCAST_DELIVERABLE, $deliverable, $entities_id);

//        echo "</div>";
        echo "<hr><div class='content'>";

        $this->addPageForDeliverable($deliverable, static::REPORT_BROADCAST_DELIVERABLE, $print_options);
        echo "</div>";

        $this->printHtmlEnd();
    }


    function procedureGenerateHtml($print_options, PluginDlteamsProcedure $procedure)
    {

        $this->HTML = true;

        $generator_options = [];
        $generator_options["report_type"] = PluginDlteamsCreatePDF::REPORT_BROADCAST_DELIVERABLE;

//        $filename = $this->generateFilename($generator_options);
        $filename = $procedure->fields["document_name"];
        header("Content-Disposition: attachement; filename=\"$filename.html\"");

        $this->preparePrintOptions($print_options);


        $entities_id = $_SESSION['glpiactive_entity'];

        $this->setEntityAndControllerInfo($entities_id);


//        $this->printHtmlHead($filename);

//        echo "<header>";
//        $this->printHeader();
//
//
//        echo "</header>";
//        echo "<div class='cover'>";

//        if ((bool)$print_options['print_first_page'])
//            $this->deliverablePrintCoverPage(static::REPORT_BROADCAST_DELIVERABLE, $deliverable, $entities_id);

//        echo "</div>";
        echo "<hr><div class='content'>";

        $this->addPageForProcedure($procedure, static::REPORT_BROADCAST_DELIVERABLE, $print_options);
        echo "</div>";

        $this->printHtmlEnd();
    }

    function slugify($string)
    {
        // Remove any accents from the string
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);

        // Replace any non-alphanumeric characters (including commas) with a hyphen
        $string = preg_replace('/[^a-zA-Z0-9,]+/', '-', $string);

        // Remove any leading or trailing hyphens
        $string = trim($string, '-');

        // Convert the string to lowercase
//        $string = strtolower($string);
        $string = str_replace(",", "", $string);
        $string = str_replace(" ", "-", $string);

        return strtolower($string);
    }

    static function slugifyString($string)
    {
        // Remove any accents from the string
        $string = iconv('UTF-8', 'ASCII//TRANSLIT', $string);

        // Replace any non-alphanumeric characters (including commas) with a hyphen
        $string = preg_replace('/[^a-zA-Z0-9,]+/', '-', $string);

        // Remove any leading or trailing hyphens
        $string = trim($string, '-');

        // Convert the string to lowercase
//        $string = strtolower($string);
        $string = str_replace(",", "", $string);
        $string = str_replace(" ", "-", $string);

        return strtolower($string);
    }

    function publishDlteams($generator_options, $print_options)
    {
        $temp_record = new PluginDlteamsRecord();
        $temp_record->getFromDB($generator_options['record_id']);
        switch ($generator_options['report_type']) {
            case self::REPORT_SINGLE_RECORD:
                $record_id = $generator_options['record_id'];
                $record = new PluginDlteamsRecord();
                $record->getFromDB($record_id);
                break;
            default:
                $record = PluginDlteamsCreatePDF::getRecordsForEntity($generator_options['record_id'], $print_options, $temp_record->fields["entities_id"]);
                break;
        }


        $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
        ob_start();
        global $DB;
        $this->HTML = true;

        $entities_id = $_SESSION['glpiactive_entity'];
        $entity = new Entity();
        $entity->getFromDB($entities_id);
        $filename = PluginDlteamsUtils::normalize($generator_options["document_url"]);
        $this->preparePrintOptions($print_options);


        $this->setEntityAndControllerInfo($entities_id);
//        $this->printHtmlHead($filename);
        $this->printRecordHeader($temp_record);

        if ($record instanceof DBmysqlIterator) {
            $i = 0;
            foreach ($record as $key => $item) {
                $rec = new PluginDlteamsRecord();
                $rec->getFromDB($item['id']);
                $this->addPageForRecord($rec, $generator_options, $i);
                $i++;
            }
        } else if ($record instanceof PluginDlteamsRecord) {
            $rec = new PluginDlteamsRecord();
            $rec->getFromDB($record->fields['id']);
            $this->addPageForRecord($rec, $generator_options);
        } else {
            $this->addPageForRecord($record, $generator_options);
        }

        $directory = $glpiRoot . "/pub/" . $print_options['guid_value'];
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file_path = $directory . "/" . $filename . ".html";
        file_put_contents($file_path, ob_get_contents());

    }

    function deliverablepublishDlteams($print_options, PluginDlteamsDeliverable $deliverable)
    {
        $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
        ob_start();
        global $DB;
        $this->HTML = true;


        $generator_options = [];
        $generator_options['report_type'] = PluginDlteamsCreatePDF::REPORT_BROADCAST_DELIVERABLE;
        $filename = $this->generateFilename($generator_options);

        $filename = $deliverable->fields["document_name"];
        $this->preparePrintOptions($print_options);

        $entities_id = $_SESSION['glpiactive_entity'];


        $this->setEntityAndControllerInfo($entities_id);
        $this->printHtmlHead($filename);

        echo "<header>";
//        $this->printHeader();

        echo "</header><div class='cover'>";
//        if ((bool)$print_options['print_first_page'])
//            $this->printCoverPage($generator_options['report_type'], $deliverable, $entities_id);

        echo "</div><hr><div class='content'>";

        //print the body
        $this->addPageForDeliverable($deliverable, static::REPORT_BROADCAST_DELIVERABLE, $print_options);
        echo "</div>";

        $this->printHtmlEnd();


        $directory = $glpiRoot . "/pub/" . $print_options['guid_value'];
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file_path = $directory . "/" . $filename . ".html";
        file_put_contents($file_path, ob_get_contents());
    }

    function procedurepublishDlteams($print_options, PluginDlteamsProcedure $procedure)
    {
        $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
        ob_start();
        global $DB;
        $this->HTML = true;

        $generator_options = [];
        $generator_options['report_type'] = PluginDlteamsCreatePDF::REPORT_BROADCAST_DELIVERABLE;
        $filename = $this->generateFilename($generator_options);

        $filename = $procedure->fields["document_name"];
        $this->preparePrintOptions($print_options);

        $entities_id = $_SESSION['glpiactive_entity'];


        $this->setEntityAndControllerInfo($entities_id);
        $this->printHtmlHead($filename);

        echo "<header>";
//        $this->printHeader();

        echo "</header><div class='cover'>";
//        if ((bool)$print_options['print_first_page'])
//            $this->printCoverPage($generator_options['report_type'], $deliverable, $entities_id);

        echo "</div><hr><div class='content'>";

        //print the body
        $this->addPageForProcedure($procedure, static::REPORT_BROADCAST_DELIVERABLE, $print_options);
        echo "</div>";

        $this->printHtmlEnd();


        $directory = $glpiRoot . "/pub/" . $print_options['guid_value'];
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $file_path = $directory . "/" . $filename . ".html";
        file_put_contents($file_path, ob_get_contents());
    }

    function getGuidValue($generator_options, $print_options)
    {

        global $DB;

        switch ($generator_options['report_type']) {
            case self::REPORT_SINGLE_RECORD:
                $record_id = $generator_options['record_id'];
                $record = new PluginDlteamsRecord();
                $record->getFromDB($record_id);
                $entities_id = $record->fields['entities_id'];

                break;
            case self::REPORT_FOR_ENTITY:
                $entities_id = $generator_options['entities_id'];
                $record = PluginDlteamsCreatePDF::getRecordsForEntity($entities_id, $print_options, true);
                break;
            case self::REPORT_ALL:
                $entities_id = $_SESSION['glpiactive_entity'];
                $record = PluginDlteamsCreatePDF::getRecordsForEntity($entities_id, $print_options);
                break;
            case self::REPORT_BROADCAST_THRIDPARTIES:
                $entities_id = $_SESSION['glpiactive_entity'];
                $record = PluginDlteamsCreatePDF::getRecordsForEntity($entities_id, $print_options, 0, 1);
                break;
            case self::REPORT_BROADCAST_EMPLOYEES:
                $entities_id = $_SESSION['glpiactive_entity'];
                $record = PluginDlteamsCreatePDF::getRecordsForEntity($entities_id, $print_options, 0, 2);
                break;
            case self::REPORT_BROADCAST_INTERNAL:
                $entities_id = $_SESSION['glpiactive_entity'];
                $record = PluginDlteamsCreatePDF::getRecordsForEntity($entities_id, $print_options, 0, 3);
                break;
            default:
                $entities_id = $_SESSION['glpiactive_entity'];
                break;
        }


        //get the guid value
        $iterator = $DB->request(self::getRequest4($entities_id));
        $number = count($iterator);

        $items_list = [];

        // while ($data = $iterator->next()) {
        foreach ($iterator as $id => $data) {
            $items_list[$data['linkid']] = $data;
            $used[$data['linkid']] = $data['linkid'];
        }

        if (!empty($items_list)) {
            foreach ($items_list as $value) {
                $guidvalue = $value['guid'];
            }
        }

        // get the guid value

        if (isset($guidvalue)) {
            $valeurtoreturn = $guidvalue;
        } else {
            $valeurtoreturn = 0;
        }

        return $valeurtoreturn;
    }

    function generateGuid($generator_options, $print_options)
    {

        global $DB;
        $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
        switch ($generator_options['report_type']) {
            case self::REPORT_SINGLE_RECORD:
                $record_id = $generator_options['record_id'];
                $record = new PluginDlteamsRecord();
                $record->getFromDB($record_id);
                $entities_id = $record->fields['entities_id'];

                break;
            case self::REPORT_FOR_ENTITY:
                $entities_id = $generator_options['entities_id'];
                $record = PluginDlteamsCreatePDF::getRecordsForEntity($entities_id, $print_options, true);
                break;
            case self::REPORT_ALL:
                $entities_id = $_SESSION['glpiactive_entity'];
                $record = PluginDlteamsCreatePDF::getRecordsForEntity($entities_id, $print_options);
                break;
            case self::REPORT_BROADCAST_THRIDPARTIES:
                $entities_id = $_SESSION['glpiactive_entity'];
                $record = PluginDlteamsCreatePDF::getRecordsForEntity($entities_id, $print_options, 0, 1);
                break;
            case self::REPORT_BROADCAST_EMPLOYEES:
                $entities_id = $_SESSION['glpiactive_entity'];
                $record = PluginDlteamsCreatePDF::getRecordsForEntity($entities_id, $print_options, 0, 2);
                break;
            case self::REPORT_BROADCAST_INTERNAL:
                $entities_id = $_SESSION['glpiactive_entity'];
                $record = PluginDlteamsCreatePDF::getRecordsForEntity($entities_id, $print_options, 0, 3);
                break;
        }
        $guidgenerated = bin2hex(openssl_random_pseudo_bytes(16));
        $query = "UPDATE `glpi_plugin_dlteams_controllerinfos` SET guid='$guidgenerated' WHERE entities_id='$entities_id'";
        $DB->queryOrDie($query, $DB->error());
        if (!file_exists($glpiRoot . "/" . "pub" . "/" . $guidgenerated . "/")) {
            $directory = $glpiRoot . "/pub/" . $guidgenerated;
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
        }
    }

    protected function printHeader()
    {

        $name = $this->getControllerName();

        $header = __("GDPR Record of Processing Activities", 'dlteams');
        $header .= " pour " . $name;
        if ($this->print_options['show_print_date_time']) {
            $header .= ",   " . sprintf(
                    __("print date/time: %1s", 'dlteams'),
                    substr(Html::convDateTime($_SESSION["glpi_currenttime"]), 3, 7));
        }


        $logo = new Document();
        $logo->getFromDB($this->controller_info->fields['logo_id']);
        $logo_path = isset($logo->fields['filepath']) ? ($logo->fields['filepath']) : '';

        if ($this->HTML) {
            // echo '<h2>' . $name . '</h2>';
            if (isset($logo->fields['filepath'])) {
                // $logo_uri = PluginDlteamsUtils::dataUri(K_PATH_IMAGES . $logo_path);
                $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
                $logo_uri = PluginDlteamsUtils::dataUri($glpiRoot . "/" . "files" . "/" . $logo_path);
                echo "<img id='logo' alt='Organisation logo' src='$logo_uri'><hr>";
                echo '<h2>' . $header . '</h2>';
            }
        } else $this->setHeader($header, $name, $logo_path);
    }

    protected function generalPrintHeader()
    {

        $name = $this->getControllerName();

//        $header = __("GDPR Record of Processing Activities", 'dlteams');
        $header = "Livrables";
        $header .= " pour " . $name;
        if ($this->print_options['show_print_date_time']) {
            $header .= ",   " . sprintf(
                    __("print date/time: %1s", 'dlteams'),
                    substr(Html::convDateTime($_SESSION["glpi_currenttime"]), 3, 7));
        }


        $logo = new Document();
        $logo->getFromDB($this->controller_info->fields['logo_id']);
        $logo_path = isset($logo->fields['filepath']) ? ($logo->fields['filepath']) : '';

        if ($this->HTML) {
            // echo '<h2>' . $name . '</h2>';
            if (isset($logo->fields['filepath'])) {
                // $logo_uri = PluginDlteamsUtils::dataUri(K_PATH_IMAGES . $logo_path);
                $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
                $logo_uri = PluginDlteamsUtils::dataUri($glpiRoot . "/" . "files" . "/" . $logo_path);
                echo "<img id='logo' alt='Organisation logo' src='$logo_uri'><hr>";
                echo '<h2>' . $header . '</h2>';
            }
        } else $this->setHeader($header, $name, $logo_path);
    }

    protected function printActivitiesList($records)
    {

        if ($records) {

            $display_introduced_in = $this->print_options['show_inherited_from'];
            $col_width = 42 + 50 * (int)!$display_introduced_in;

            $this->writeInternal(
                "<h2 style='color:#ffffff;background-color:#447BD1'>" . __("List of processing activities for which entity deals with personal data", 'dlteams') . "</h2>", [
                'linebefore' => 8
            ]);

            if (!count($records)) {
                $this->writeInternal(__("There are no activities.", 'dlteams'), [
                    'border' => 1,
                    'linebefore' => 1
                ]);
            } else {
                $tbl = '<table border="1" cellpadding="3" cellspacing="0">' .
                    '<thead><tr>' .
                    '<th width="8%" style="background-color:#323232;color:' . self::TEXT_COLOR_ONBG . ';text-align:center;"><h3>' . __("No", 'dlteams') . '</h3></th>' .
                    '<th width="' . $col_width . '%" style="background-color:#323232;color:' . self::TEXT_COLOR_ONBG . ';"><h3>' . __("Description of the activity", 'dlteams') . '</h3></th>';
                if ($display_introduced_in) {
                    $tbl .= '<th width="50%" style="background-color:#323232;color:' . self::TEXT_COLOR_ONBG . ';"><h3>' . __("Introduced in", 'dlteams') . '</h3></th>';
                }
                $tbl .= '</tr></thead><tbody>';

                foreach ($records as $item) {
                    $entity = new Entity();
                    $entity->getFromDB($item['entities_id']);
                    $tbl .= '<tr>' .
                        '<td width="8%" align="center">' . $item['number'] . ' </td><td width="' . $col_width . '%">';

                    //if ($this->HTML) $tbl .= '<a href="#' . $item['name'] . '">' . $item['name'] . '</a></td>';
                    if ($this->HTML) $tbl .= '<a href="#' . trim(str_replace(array('\'', '"'), '', $item['name'])) . '">' . $item['name'] . '</a></td>';

                    else $tbl .= $item['name'] . '</td>';

                    if ($display_introduced_in) {
                        $tbl .= '<td width="50%">' . $entity->fields['completename'] . '</td>';
                    }
                    $tbl .= '</tr>';
                }
                $tbl .= "</tbody></table>";


                $this->writeHtml($tbl);
            }
        }

    }

    protected function deliverablePrintActivitiesList($deliverable)
    {

        if ($deliverable) {

            $display_introduced_in = $this->print_options['show_inherited_from'];
            $col_width = 42 + 50 * (int)!$display_introduced_in;

            $this->writeInternal(
                "<h2 style='color:#ffffff;background-color:#447BD1'>" . __("List of processing activities for which entity deals with personal data", 'dlteams') . "</h2>", [
                'linebefore' => 8
            ]);

            if (!count($records)) {
                $this->writeInternal(__("There are no activities.", 'dlteams'), [
                    'border' => 1,
                    'linebefore' => 1
                ]);
            } else {
                $tbl = '<table border="1" cellpadding="3" cellspacing="0">' .
                    '<thead><tr>' .
                    '<th width="8%" style="background-color:#323232;color:' . self::TEXT_COLOR_ONBG . ';text-align:center;"><h3>' . __("No", 'dlteams') . '</h3></th>' .
                    '<th width="' . $col_width . '%" style="background-color:#323232;color:' . self::TEXT_COLOR_ONBG . ';"><h3>' . __("Description of the activity", 'dlteams') . '</h3></th>';
                if ($display_introduced_in) {
                    $tbl .= '<th width="50%" style="background-color:#323232;color:' . self::TEXT_COLOR_ONBG . ';"><h3>' . __("Introduced in", 'dlteams') . '</h3></th>';
                }
                $tbl .= '</tr></thead><tbody>';

                foreach ($records as $item) {
                    $entity = new Entity();
                    $entity->getFromDB($item['entities_id']);
                    $tbl .= '<tr>' .
                        '<td width="8%" align="center">' . $item['number'] . ' </td><td width="' . $col_width . '%">';

                    //if ($this->HTML) $tbl .= '<a href="#' . $item['name'] . '">' . $item['name'] . '</a></td>';
                    if ($this->HTML) $tbl .= '<a href="#' . trim(str_replace(array('\'', '"'), '', $item['name'])) . '">' . $item['name'] . '</a></td>';

                    else $tbl .= $item['name'] . '</td>';

                    if ($display_introduced_in) {
                        $tbl .= '<td width="50%">' . $entity->fields['completename'] . '</td>';
                    }
                    $tbl .= '</tr>';
                }
                $tbl .= "</tbody></table>";


                $this->writeHtml($tbl);
            }
        }

    }

    protected function printCoverPage($type, $records, $entities_id = -1)
    {

        if (!$this->HTML) $this->pdf->addPage($this->print_options['page_orientation'], 'A4');

        switch ($type) {
            case PluginDlteamsCreatePDF::REPORT_SINGLE_RECORD:
                $this->printPageTitle("<h1 style='background:#323232;color:#ffffff'><small>" . $records->fields['name'] . "</h1>");
                // $this->printPageTitle("<h1 style='background:#447BD1;color:#ffffff'><small>" . __("Processing Activity", 'dlteams') ."</small><br/>" . $records->fields['name'] . "</h1>");
                break;
            case PluginDlteamsCreatePDF::REPORT_FOR_ENTITY:
                $entity = new Entity();
                $entity->getFromDB($entities_id);
                $this->printPageTitle("<h1><small>" . sprintf(__("GDPR Records of Processing Activity for entity:<br/>%1s", 'dlteams'), $entity->fields['name']) . "</small><br/>" . '' . "</h1>");
                break;
            case PluginDlteamsCreatePDF::REPORT_ALL:
                // Broadcast spec is present on each reccord
                $this->printPageTitle("<h1><small>" . __("Complete GDPR Records of Processing Activity", 'dlteams') . "</small><br/>" . '' . "</h1>");
                break;
            case PluginDlteamsCreatePDF::REPORT_BROADCAST_EMPLOYEES:
            case PluginDlteamsCreatePDF::REPORT_BROADCAST_THRIDPARTIES:
//            case PluginDlteamsCreatePDF::REPORT_BROADCAST_INTERNAL:
                $this->printPageTitle("<h1><small>" . self::broadcast_part[$type - 3] . "</small><br/>" . '' . "</h1>");
                break;
        }

        $datas = [];
        $datas[] = $this->prepareControllerInfo();

        $datas[] = $this->preparePersonelInfo('representative', __("Legal representative not set.", 'dlteams'), __("Legal representative", 'dlteams'));
        $datas[] = $this->preparePersonelInfo('dpo', __("DPO not set.", 'dlteams'), __("Data Protection Officer", 'dlteams'));

        if ($this->HTML) echo "<table border='1' cellspacing='0'>";

        foreach ($datas as $d) {
            /*$this->write2ColsRow(
            $d['section'], [
               'fillcolor' => self::FILL_COLOR,
               'textcolor' => self::TEXT_COLOR_ONFILL,
               'fill' => 1,
               'linebefore' => 4,
               'border' => 1,
               'cellwidth' => 50,
               'align' => 'R'
            ],
            $d['value'], [
               'border' => 1
            ]
         );*/

            /**add by me**/
            if ($this->HTML) {
                $tbl = '<tr>';
                $tbl .= '<td width="50%">' . $d['section'] . '</td>';
                $tbl .= '<td width="50%">' . $d['value'] . '</td>';
                $tbl .= '<tr/>';
                $this->writeHtml($tbl);
            } else {
                $this->write2ColsRow(
                    $d['section'], [
                    'fillcolor' => self::FILL_COLOR,
                    'textcolor' => self::TEXT_COLOR_ONFILL,
                    'fill' => 1,
                    'linebefore' => 4,
                    'border' => 1,
                    'cellwidth' => 50,
                    'align' => 'R'
                ],
                    $d['value'], [
                        'border' => 1
                    ]
                );
            }
            /**add by me**/
        }

        if ($this->HTML) echo "</table>";

        switch ($type) {
            case PluginDlteamsCreatePDF::REPORT_SINGLE_RECORD:
                break;
//            case PluginDlteamsCreatePDF::REPORT_FOR_ENTITY:
            case PluginDlteamsCreatePDF::REPORT_ALL:
            case PluginDlteamsCreatePDF::REPORT_BROADCAST_THRIDPARTIES:
            case PluginDlteamsCreatePDF::REPORT_BROADCAST_EMPLOYEES:
//            case PluginDlteamsCreatePDF::REPORT_BROADCAST_INTERNAL:
                $this->printActivitiesList($records);
                break;
        }

        if (!$this->HTML) $this->pdf->lastPage();
    }

    protected function deliverablePrintCoverPage($type, $deliverable, $entities_id = -1)
    {

        if (!$this->HTML) $this->pdf->addPage($this->print_options['page_orientation'], 'A4');

        $this->printPageTitle("<h1 style='background:#323232;color:#ffffff'><small>" . $deliverable->fields['name'] . "</h1>");

        $datas = [];
        $datas[] = $this->prepareControllerInfo();

        $datas[] = $this->preparePersonelInfo('representative', __("Legal representative not set.", 'dlteams'), __("Legal representative", 'dlteams'));
        $datas[] = $this->preparePersonelInfo('dpo', __("DPO not set.", 'dlteams'), __("Data Protection Officer", 'dlteams'));

        if ($this->HTML) echo "<table border='1' cellspacing='0'>";

        foreach ($datas as $d) {


            /**add by me**/
            if ($this->HTML) {
                $tbl = '<tr>';
                $tbl .= '<td width="50%">' . $d['section'] . '</td>';
                $tbl .= '<td width="50%">' . $d['value'] . '</td>';
                $tbl .= '<tr/>';
                $this->writeHtml($tbl);
            } else {
                $this->write2ColsRow(
                    $d['section'], [
                    'fillcolor' => self::FILL_COLOR,
                    'textcolor' => self::TEXT_COLOR_ONFILL,
                    'fill' => 1,
                    'linebefore' => 4,
                    'border' => 1,
                    'cellwidth' => 50,
                    'align' => 'R'
                ],
                    $d['value'], [
                        'border' => 1
                    ]
                );
            }
            /**add by me**/
        }

        if ($this->HTML) echo "</table>";


//        $this->printActivitiesList($records);

        if (!$this->HTML) $this->pdf->lastPage();
    }

    protected function printRecordHeader(PluginDlteamsRecord $record)
    {

        $logo = new Document();
        $logo->getFromDB($this->controller_info->fields['logo_id']);
        $logo_path = isset($logo->fields['filepath']) ? ($logo->fields['filepath']) : '';


        $logo_uri = "";
        if (isset($logo->fields['filepath'])) {
            $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
            $logo_uri = PluginDlteamsUtils::dataUri($glpiRoot . "/" . "files" . "/" . $logo_path);
        }

        $print_first_page = true;
        $datas = [];
        if ($print_first_page) {
            $datas[] = $this->prepareControllerInfo();

            $datas[] = $this->preparePersonelInfo('representative', __("Legal representative not set.", 'dlteams'), __("Legal representative", 'dlteams'));
            $datas[] = $this->preparePersonelInfo('dpo', __("DPO not set.", 'dlteams'), __("Data Protection Officer", 'dlteams'));
        }


        \Glpi\Application\View\TemplateRenderer::getInstance()->display('@dlteams/pages/record_publish_base_header.html.twig', [
            "isHtml" => $this->HTML,
            "ispdf" => isset($print_options["ispdf"]) ? $print_options["ispdf"] : false,
            "logo_uri" => $logo_uri,
            "title" => "Traitement",
            "first_page_datas" => $datas,
            "record" => $record->fields,
            "css_files" => [
                [
                    "path" => "https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
                ],
                [
                    "path" => "https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.css"
                ],

            ],
            "js_files" => [
                [
                    "path" => "https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"
                ]
            ]
        ]);
    }

    protected function printRecordFooter($print_options)
    {

        \Glpi\Application\View\TemplateRenderer::getInstance()->display('@dlteams/pages/record_publish_base_footer.html.twig', [
            "show_date" => $print_options["show_print_date_time"] === 'on' ? true : false
        ]);
    }

    protected function addPageForRecord(PluginDlteamsRecord $record, $generator_options, $key = 0)
    {

        $logo = new Document();
        $logo->getFromDB($this->controller_info->fields['logo_id']);
        $logo_path = isset($logo->fields['filepath']) ? ($logo->fields['filepath']) : '';


        $logo_uri = "";
        if (isset($logo->fields['filepath'])) {
            $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
            $logo_uri = PluginDlteamsUtils::dataUri($glpiRoot . "/" . "files" . "/" . $logo_path);
        }

        $print_first_page = true;
        $datas = [];
        if ($print_first_page) {
            $datas[] = $this->prepareControllerInfo();

            $datas[] = $this->preparePersonelInfo('representative', __("Legal representative not set.", 'dlteams'), __("Legal representative", 'dlteams'));
            $datas[] = $this->preparePersonelInfo('dpo', __("DPO not set.", 'dlteams'), __("Data Protection Officer", 'dlteams'));
        }

        $pri_data = $this->printRecordInformation($record, $generator_options['report_type']);
        $prlb_data = $this->printLegalBasisActs($record);
        $ppd_data = $this->printPersonalAndDataCategories($record);
        $pct_data = $this->printConservationTime($record);
        $pre_data = $this->printRightExercice($record);
        $prexternal_data = $this->printExternal($record);
        $psm_data = $this->printSecurityMeasures($record);


        \Glpi\Application\View\TemplateRenderer::getInstance()->display('@dlteams/pages/record_content_placeholder.html.twig',
            [
            "isHtml" => $this->HTML,
            "ispdf" => isset($print_options["ispdf"]) ? $print_options["ispdf"] : false,
            "title" => "Traitement",
            "print_comments" => isset($print_options['print_comments']) ? $print_options['print_comments'] : false,
            "pri_data" => $pri_data,
            "prlb_data" => $prlb_data,
            "logo_uri" => $logo_uri,
            "ppd_data" => $ppd_data,
            "pct_data" => $pct_data,
            "pre_data" => $pre_data,
            "first_page" => false,
            "deplier_traitements" => $generator_options["report_type"] == self::REPORT_SINGLE_RECORD,
            "prexternal_data" => $prexternal_data,
            "psm_data" => $psm_data,
            "key" => $key,
            "show_supplier_informations" => isset($generator_options["show_supplier_informations"]) ? $generator_options["show_supplier_informations"] : false,
            "show_print_date_time" => isset($generator_options["show_print_date_time"]) ? $generator_options["show_print_date_time"] : false,
            "record" => $record->fields,
        ]);


    }

    protected function addPageForDeliverable(PluginDlteamsDeliverable $deliverable, $report_type, $print_options)
    {


        global $DB;
        $print_first_page = $print_options['print_first_page'] ?? false;

        $sections = $DB->request([
            "FROM" => 'glpi_plugin_dlteams_deliverables_sections',
            "WHERE" => [
                "deliverables_id" => $deliverable->fields["id"]
            ],
            "ORDER" => ['timeline_position ASC']
        ]);


        $section_list = [];
        foreach ($sections as $section) {
            array_push($section_list, $section);
        }

        $deliverable_variables = $DB->request([
            "FROM" => PluginDlteamsDeliverable_Item::getTable(),
            "WHERE" => [
                "itemtype" => PluginDlteamsDeliverable_Variable::class
            ],
            "ORDER" => ['timeline_position ASC']
        ]);

        $variable_list = [];
        foreach ($deliverable_variables as $variable) {
            array_push($variable_list, $variable);
        }


        foreach ($section_list as $id => $section) {
            $contents = new PluginDlteamsDeliverable_Content();
            $content_list = $contents->find(
                ['deliverable_sections_id' => $section["id"]],
                ['timeline_position ASC']
            );

            $section_list[$id]["section_content"] = [];
            foreach ($content_list as $content) {
                $content["content"] = html_entity_decode($content["content"]);
                foreach ($variable_list as $variable) {
                    $vr = new PluginDlteamsDeliverable_Variable();
                    $vr->getFromDB($variable["items_id"]);

                    $content["content"] = str_replace($vr->fields["name"], $vr->fields["content"], $content["content"]);
                }
                $section_list[$id]["section_content"][] = $content;
            }
        }


        $logo = new Document();
        $logo->getFromDB($this->controller_info->fields['logo_id']);
        $logo_path = isset($logo->fields['filepath']) ? ($logo->fields['filepath']) : '';

        $logo_uri = "";
        if (isset($logo->fields['filepath'])) {
            $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
            $logo_uri = PluginDlteamsUtils::dataUri($glpiRoot . "/" . "files" . "/" . $logo_path);
        }

        $datas = [];
        if ($print_first_page) {
            $datas[] = $this->prepareControllerInfo();

            $datas[] = $this->preparePersonelInfo('representative', __("Legal representative not set.", 'dlteams'), __("Legal representative", 'dlteams'));
            $datas[] = $this->preparePersonelInfo('dpo', __("DPO not set.", 'dlteams'), __("Data Protection Officer", 'dlteams'));
        }


        \Glpi\Application\View\TemplateRenderer::getInstance()->display('@dlteams/pages/deliverable_publish_base.html.twig', [
            "section_list" => $section_list,
            "deliverable" => [
                ...$deliverable->fields,
                "document_content" => html_entity_decode($deliverable->fields["document_content"]),
                "document_comment" => html_entity_decode($deliverable->fields["document_comment"]),
            ],
            "isHtml" => $this->HTML,
            "ispdf" => isset($print_options["ispdf"]) ? $print_options["ispdf"] : false,
            "logo_uri" => $logo_uri,
            "title" => "Livrables",
            "prevent_contextmenu" => isset($print_options["prevent_contextmenu"]) ? $print_options["prevent_contextmenu"] : false,
            "print_comments" => isset($print_options['print_comments']) ? $print_options['print_comments'] : false,
            "first_page_datas" => $datas,
            "css_files" => [
                [
                    "path" => "https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
                ]
            ],
            "js_files" => [
                [
                    "path" => "https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"
                ]
            ]
        ]);


//        if ($this->HTML) {
//
//            $deliverable_name = trim(str_replace(['"', "'"], "", $deliverable->fields['name']));
//            //$record_name = $record->fields['name'];
//            $deliverable_name = trim(str_replace(array('\'', '"'), '', $deliverable->fields['name']));
//            //$record_name = $record->fields['name'];
//            $deliverable_name = trim(str_replace(array('\'', '"'), '', $deliverable->fields['name']));
//            $deliverable_id = $deliverable->fields['id'];
//
//            echo "<div class='record' id='$deliverable_id'>";
//        } else $this->pdf->addPage('P', 'A4');
//
//        $this->printPageTitle("<h1 style='background:#323232;color:#ffffff'><small>" . $deliverable->fields['name'] . "</h1>");
//
//
//
//        $this->printDeliverableContent($deliverable);
//
//
//        if ($this->HTML) echo "</div>";
    }

    protected function addPageForProcedure(PluginDlteamsProcedure $procedure, $report_type, $print_options)
    {


        global $DB;
        $print_first_page = $print_options['print_first_page'] ?? false;

        $sections = $DB->request([
            "FROM" => 'glpi_plugin_dlteams_procedures_sections',
            "WHERE" => [
                "procedures_id" => $procedure->fields["id"]
            ],
            "ORDER" => ['timeline_position ASC']
        ]);


        $section_list = [];
        foreach ($sections as $section) {
            array_push($section_list, $section);
        }

        $procedure_variables = $DB->request([
            "FROM" => PluginDlteamsProcedure_Item::getTable(),
            "WHERE" => [
                "itemtype" => PluginDlteamsProcedure_Variable::class
            ],
            "ORDER" => ['timeline_position ASC']
        ]);

        $variable_list = [];
        foreach ($procedure_variables as $variable) {
            array_push($variable_list, $variable);
        }


        foreach ($section_list as $id => $section) {
            $contents = new PluginDlteamsProcedure_Content();
            $content_list = $contents->find(
                ['procedure_sections_id' => $section["id"]],
                ['timeline_position ASC']
            );

            $section_list[$id]["section_content"] = [];
            foreach ($content_list as $content) {
                $content["content"] = html_entity_decode($content["content"]);
                foreach ($variable_list as $variable) {
                    $vr = new PluginDlteamsProcedure_Variable();
                    $vr->getFromDB($variable["items_id"]);

                    $content["content"] = str_replace($vr->fields["name"], $vr->fields["content"], $content["content"]);
                }
                $section_list[$id]["section_content"][] = $content;
            }
        }


        $logo = new Document();
        $logo->getFromDB($this->controller_info->fields['logo_id']);
        $logo_path = isset($logo->fields['filepath']) ? ($logo->fields['filepath']) : '';

        $logo_uri = "";
        if (isset($logo->fields['filepath'])) {
            $glpiRoot = str_replace('\\', '/', GLPI_ROOT);
            $logo_uri = PluginDlteamsUtils::dataUri($glpiRoot . "/" . "files" . "/" . $logo_path);
        }

        $datas = [];
        if ($print_first_page) {
            $datas[] = $this->prepareControllerInfo();

            $datas[] = $this->preparePersonelInfo('representative', __("Legal representative not set.", 'dlteams'), __("Legal representative", 'dlteams'));
            $datas[] = $this->preparePersonelInfo('dpo', __("DPO not set.", 'dlteams'), __("Data Protection Officer", 'dlteams'));
        }


        \Glpi\Application\View\TemplateRenderer::getInstance()->display('@dlteams/pages/procedure_publish_base.html.twig', [
            "section_list" => $section_list,
            "procedure" => [
                ...$procedure->fields,
                "document_content" => html_entity_decode($procedure->fields["document_content"]),
                "document_comment" => html_entity_decode($procedure->fields["document_comment"]),
            ],
            "isHtml" => $this->HTML,
            "ispdf" => isset($print_options["ispdf"]) ? $print_options["ispdf"] : false,
            "logo_uri" => $logo_uri,
            "title" => "Livrables",
            "print_comments" => isset($print_options['print_comments']) ? $print_options['print_comments'] : false,
            "first_page_datas" => $datas,
            "css_files" => [
                [
                    "path" => "https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
                ]
            ],
            "js_files" => [
                [
                    "path" => "https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js"
                ]
            ]
        ]);


    }

    protected function printRecordInformation(PluginDlteamsRecord $record, $report_type)
    {
        $rows = [];
        /*$rows[] = [
            'section' => __("Name", 'dlteams'),
            'value' => $record->fields['name']
        ];*/
        $rows[] = [
            'section' => __("Purpose", 'dlteams'),
            'value' => nl2br($record->fields['content']??"")
        ];

        $users_types = [
            'user_id_responsible' => __("Process responsible", 'dlteams'),
            'user_id_auditor' => __("Process auditor", 'dlteams'),
            'user_id_actor' => __("Process actor", 'dlteams')
        ];
        // Display if exists in record
        foreach ($users_types as $id_field => $translation) {
            if (isset($record->fields[$id_field]) && $record->fields[$id_field] != 0) {
                $id = $record->fields[$id_field];

                $name = User::getById($id)->fields['firstname'];
                $name .= " ";
                $name .= User::getById($id)->fields['realname'];

                $rows[] = [
                    'section' => $translation,
                    'value' => $name
                ];
            }
        }

        if (!empty($record->fields['additional_info'])) {
            $rows[] = [
                'section' => __("Additional information", 'dlteams'),
                'value' => nl2br($record->fields['additional_info']??"")
            ];
        }
        // If it contains all records, print the broadcast value otherwise it's not needed because it's in document title
        if ($report_type == self::REPORT_ALL) {
            $status = 1; // dropdown::getDropdownName('glpi_states', $record->fields['states_id']);
            $rows[] = [
                'section' => __("Broadcast", 'dlteams'),
                'value' => $status ? 'Oui' : 'Non'
            ];
        }

        if (PluginDlteamsCreatePDFBase::isGdprownerPluginActive()) {
            if ($this->print_options['show_record_owner']) {

                $owner_info = PluginGdprownerOwner::getOwnerInfo($record->fields['id'], PluginDlteamsRecord::class);
                $owner = $owner_info['owner_type_name'] . ': ' . $owner_info['owner_name'];

                $rows[] = [
                    'section' => __("Owner", 'dlteams'),
                    'value' => $owner,
                ];
            }
        }

        return [
            "pri_rows" => $rows,
            "pri_title" => __("Processing Activity information PDF", 'dlteams')
        ];
    }

    protected function printDeliverableInformation(PluginDlteamsDeliverable $deliverable, $report_type)
    {

        /*$this->writeInternal(
         '<h2 style="color:#ffffff;background-color:#447BD1">' . __("Processing Activity information PDF1", 'dlteams') . '</h2>', [
            'linebefore' => 1
         ]);*/


        /**add by me**/
        if ($this->HTML) {
            echo '<h2 style="color:#ffffff;background-color:#447BD1;margin-bottom:5px">' . __("Processing Activity information PDF", 'dlteams') . '</h2>';
        } else {
            $this->writeInternal(
                '<h2 style="color:#ffffff;background-color:#447BD1">' . __("Processing Activity information PDF", 'dlteams') . '</h2>', [
                'linebefore' => 1
            ]);
        }
        /**add by me**/

        $rows = [];
        $rows[] = [
            'section' => __("Name", 'dlteams'),
            'value' => $deliverable->fields['name']
        ];
        $rows[] = [
            'section' => __("Purpose", 'dlteams'),
            'value' => nl2br($deliverable->fields['content']??"")
        ];

        $users_types = [
            'user_id_responsible' => __("Process responsible", 'dlteams'),
            'user_id_auditor' => __("Process auditor", 'dlteams'),
            'user_id_actor' => __("Process actor", 'dlteams')
        ];
        // Display if exists in record
        foreach ($users_types as $id_field => $translation) {
            if (isset($record->fields[$id_field]) && $record->fields[$id_field] != 0) {
                $id = $record->fields[$id_field];

                $name = User::getById($id)->fields['firstname'];
                $name .= " ";
                $name .= User::getById($id)->fields['realname'];

                $rows[] = [
                    'section' => $translation,
                    'value' => $name
                ];
            }
        }

        if (!empty($deliverable->fields['additional_info'])) {
            $rows[] = [
                'section' => __("Additional information", 'dlteams'),
                'value' => nl2br($record->fields['additional_info']??"")
            ];
        }
        // If it contains all records, print the broadcast value otherwise it's not needed because it's in document title
        if ($report_type == self::REPORT_ALL) {
            $status = 1; // dropdown::getDropdownName('glpi_states', $record->fields['states_id']);
            $rows[] = [
                'section' => __("Broadcast", 'dlteams'),
                'value' => $status
            ];
        }

//        if (PluginDlteamsCreatePDFBase::isGdprownerPluginActive()) {
//            if ($this->print_options['show_record_owner']) {
//
//                $owner_info = PluginGdprownerOwner::getOwnerInfo($record->fields['id'], PluginDlteamsRecord::class);
//                $owner = $owner_info['owner_type_name'] . ': ' . $owner_info['owner_name'];
//
//                $rows[] = [
//                    'section' => __("Owner", 'dlteams'),
//                    'value' => $owner,
//                ];
//            }
//        }

        if ($this->HTML) echo "<table border='1' cellspacing='0' style='width:100%'>";

        foreach ($rows as $item) {
            /*$this->write2ColsRow(
            $item['section'], [
               'fillcolor' => self::FILL_COLOR,
         'textcolor' => self::TEXT_COLOR_ONFILL,
               'fill' => 1,
               'linebefore' => 0,
               'border' => 1,
               'cellwidth' => 50,
               'align' => 'R'
            ],
            $item['value'], [
               'border' => 1
            ]
         );*/

            /**add by me**/
            if ($this->HTML) {
                $tbl = '<tr width="100%">';
                $tbl .= '<td width="20%" style="background:#A8C0E3;color:#ffffff">' . $item['section'] . '</td>';
                $tbl .= '<td width="80%">' . $item['value'] . '</td>';
                $tbl .= '<tr/>';
                $this->writeHtml($tbl);
            } else {
                $this->write2ColsRow(
                    $item['section'], [
                    'fillcolor' => self::FILL_COLOR,
                    'textcolor' => self::TEXT_COLOR_ONFILL,
                    'fill' => 1,
                    'linebefore' => 0,
                    'border' => 1,
                    'cellwidth' => 50,
                    'align' => 'R'
                ],
                    $item['value'], [
                        'border' => 1
                    ]
                );
            }
            /**add by me**/
        }


        if ($this->HTML) echo "</table>";

        else {
            $this->pdf->Ln();

            $this->insertNewPageIfBottomSpaceLeft();
        }
    }

    protected function printLegalBasisActs(PluginDlteamsRecord $record)
    {
        global $DB;

        $rows = [];

        $result = PluginDlteamsLegalBasi_Item::getRequest($record);


        foreach ($result as $id => $item) {

            $temp = [];
            $lbt = new PluginDlteamsLegalBasisType();
            $lbt->getFromDB($item["legalbasistypes_id"]);
            $temp["type"] = $lbt->fields["name"];
            $temp["name"] = $item['name'];
            $temp["content"] = htmlspecialchars_decode($item['content']);
            $temp["comment"] = nl2br($item['comment']??"");

            array_push($rows, $temp);
        }

        return [
            "prlb_rows" => $rows
        ];

    }


    protected function printDeliverableContent(PluginDlteamsDeliverable $deliverable)
    {

        global $DB;


//        if ($this->HTML) {
//            echo '<h2 style="color:#ffffff;background-color:#447BD1;margin-bottom:-30px">' . __("Deliverables PDF", 'dlteams') . '</h2>';
//        } else {
//            $this->writeInternal(
//                '<h2 style="color:#ffffff;background-color:#447BD1">' . __("Deliverables PDF", 'dlteams') . '</h2>', [
//                'linebefore' => 1
//            ]);
//        }

        $sections = $DB->request([
            "FROM" => 'glpi_plugin_dlteams_deliverables_sections',
            "WHERE" => [
                "deliverables_id" => $deliverable->fields["id"]
            ],
            "ORDER" => ['timeline_position ASC']
        ]);


        $section_list = [];
        foreach ($sections as $section) {
            array_push($section_list, $section);
        }

        $cols_width = ['25', '15', '0', '60', '0'];
//
        $tbl = "";
        foreach ($section_list as $id => $section) {
            $contents = new PluginDlteamsDeliverable_Content();
            $content_list = $contents->find(
                ['deliverable_sections_id' => $section["id"]],
                ['timeline_position DESC']
            );

            $tbl .=
                '<table cellpadding="3" cellspacing="0" width="100%">' .
                '<thead><tr>' .
                '<th width="' . '100' . '%" style=""><h1 style="padding:0px!important; margin-top: 100px!important;"><br/>' . $section["name"] . '</h1></th></tr></thead>';
            $tbl .=
                '</table>';

            foreach ($content_list as $content) {

                if ($content['content']) {
                    $tbl .=
                        '<table cellpadding="3" cellspacing="0" width="100%">' .
                        '<thead><tr>' .
                        '<th width="' . '100' . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . '; text-align:left;"><h2 style="padding:0px!important;margin:0px!important">&nbsp;&nbsp;' . $content["name"] . '</h2></th>';


                    $tbl .=
                        '</tr></thead>' .
                        '<tbody>';

                    $tbl .=
                        '<tr>' .
                        '<td width="100%">';
                    $tbl .= html_entity_decode($content['content']);


                    $tbl .= '</td>';

                    $tbl .=
                        '</tr>';

                    $tbl .=
                        '</tbody>' .
                        '</table>';
//                    $tbl .='<br/> <br/>';
                }
            }


//            $tbl .='<br/> <br/>';


        }

        $this->writeHtml("<br><br>" . $tbl);


        $this->insertNewPageIfBottomSpaceLeft();

    }

    protected function printConservationTime(PluginDlteamsRecord $record)
    {


        global $DB;

        $request = [
            'SELECT' => [
                'glpi_plugin_dlteams_storageperiods_items.id AS linkid',
                'glpi_plugin_dlteams_storageperiods_items.itemtype AS itemtype',
                'glpi_plugin_dlteams_storageperiods_items.items_id AS items_id',
                'glpi_plugin_dlteams_storageperiods.id AS glpi_plugin_dlteams_storageperiods_id',
                'glpi_plugin_dlteams_storageperiods.name AS duree',
                'glpi_plugin_dlteams_storageperiods.content AS content',
                'glpi_plugin_dlteams_storageperiods_items.comment AS comment',
                'glpi_plugin_dlteams_storageperiods_items.plugin_dlteams_storageendactions_id AS storageendactions_id',
                'glpi_plugin_dlteams_storageperiods_items.plugin_dlteams_storagetypes_id AS storagetypes_id',
            ],
            'FROM' => 'glpi_plugin_dlteams_storageperiods_items',
            'LEFT JOIN' => [
                'glpi_plugin_dlteams_storageperiods' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_storageperiods_items' => "storageperiods_id",
                        'glpi_plugin_dlteams_storageperiods' => "id",
                    ]
                ],
                'glpi_plugin_dlteams_storagetypes' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_storageperiods' => "plugin_dlteams_storagetypes_id",
                        'glpi_plugin_dlteams_storagetypes' => "id",
                    ]
                ],
            ],
            'ORDER' => ['glpi_plugin_dlteams_storageperiods_items.id ASC'],
            'WHERE' => [
                'glpi_plugin_dlteams_storageperiods_items.items_id' => $record->fields['id'],
                'glpi_plugin_dlteams_storageperiods_items.itemtype' => $record->getType(),
                'glpi_plugin_dlteams_storageperiods.is_deleted' => 0,
            ]
        ];
        $iterator = $DB->request($request, "", true);


        $rows = [];

        foreach ($iterator as $id => $item) {

            $temp = [];

            $endaction = new PluginDlteamsStorageEndAction();
            $storagetype = new PluginDlteamsStorageType();


            if (
                $endaction->getFromDB($item["storageendactions_id"])
                && isset($endaction->fields["name"])
            ) {

                $endactionname = $endaction->fields["name"];

            } else {
                $endactionname = "--";
            }

            if (
            $storagetype->getFromDB($item["storagetypes_id"])
            ) {

                $typename = $storagetype->fields["name"];
            } else {
                $typename = "--";
            }


            $temp["duree"] = $item["duree"];
            $temp["stockage"] = $typename;
            $temp["endaction"] = $endactionname;
            $temp["comment"] = html_entity_decode($item['comment']);


            array_push($rows, $temp);
        }


        return [
            "pct_rows" => $rows
        ];

    }

    protected function printPersonalAndDataCategories(PluginDlteamsRecord $record)
    {

        global $DB;
        $iterator = $DB->request(PluginDlteamsRecord_PersonalAndDataCategory::getRequest($record));

        $rows = [];

        foreach ($iterator as $item) {
            if ($item["itemtype"] && $item["items_id"]) {
                $item_object = new $item["itemtype"]();
                $item_object->getFromDB($item["items_id"]);
                $name_column_1 = $item_object->fields["name"];
            } else {
                $name_column_1 = "";
            }

            if ($item["itemtype1"] && $item["items_id1"]) {
                $item_object = new $item["itemtype1"]();
                $item_object->getFromDB($item["items_id1"]);
                $name_column_2 = $item_object->fields["name"];

            } else {
                $name_column_2 = "----";
            }


            $temp["concernedperson"] = $name_column_1;
            $temp["datacategrory"] = $name_column_2;

            if ($item['mandatory'] == 1) {
                $temp["mandatory"] = __("Yes");
            } else {
                $temp["mandatory"] = __("No");
            }

            array_push($rows, $temp);
        }

        return [
            "ppd_rows" => $rows
        ];
    }

    protected function printRightExercice(PluginDlteamsRecord $record)
    {

        $consent_type = $record->fields['consent_type'];
        if (!$consent_type) {
            // IMPLICIT Get all the checked choices
            $consent_checked = json_decode($record->fields['consent_json'] ?? "{}", true);
            $choices = [
                __("Punctual or contractual engagement", 'dlteams'),
                __("Prior consent to a third-party", 'dlteams'),
                __("Legitimate ou pre-contractual process", 'dlteams'),
                __("Legal obligation", 'dlteams'),
                __("Public interest mission or safeguard of vital interests", 'dlteams'),
                $consent_checked['other'] ?? ""
            ];
            $consent_text = self::getCheckedList($choices, $consent_checked);
        } else {
            // EXPLICIT
            $consent_text = nl2br($record->fields['consent_explicit']??"");
        }
        // Fill the 5 rows
        $rows = [];
        $rows[] = [
            'section' => __("Is the consent implicit or explicit?", 'dlteams'),
            'value' => $consent_type ? __("Explicit", 'dlteams') : __("Implicit", 'dlteams')
        ];
        $rows[] = [
            'section' => $consent_type ? __("Explicit consent", 'dlteams') : __("Consent", 'dlteams'),
            'value' => $consent_text
        ];
        $rows[] = [
            'section' => __("Information right", 'dlteams'),
            'value' => nl2br($record->fields['right_information']??"") ?: __("N/A", 'dlteams')
        ];
        $rows[] = [
            'section' => __("Opposition right", 'dlteams'),
            'value' => nl2br($record->fields['right_opposition']??"") ?: __("N/A", 'dlteams')
        ];
        $rows[] = [
            'section' => __("Portability right", 'dlteams'),
            'value' => nl2br($record->fields['right_portability']??"") ?: __("N/A", 'dlteams')
        ];

        return [
            "pre_rows" => $rows
        ];

    }

    protected function printExternal(PluginDlteamsRecord $record)
    {
        global $DB;

//        Group
        $req_group = [
            'SELECT' => [
                'glpi_plugin_dlteams_records_items.id AS linkid',
                'glpi_plugin_dlteams_records_items.itemtype AS itemtype',
                'glpi_plugin_dlteams_records_items.items_id AS items_id',
                'glpi_plugin_dlteams_records_items.comment AS comment',
            ],
            'FROM' => 'glpi_plugin_dlteams_records_items',
            'ORDER' => ['glpi_plugin_dlteams_records_items.id ASC'],
//            'JOIN' => [
//                Group::getTable() => [
//                    'FKEY' => [
//                        PluginDlteamsRecord::getTable() => 'items_id',
//                        Group::getTable() => 'id'
//                    ]
//                ]
//            ],
            'OR' => [
                [
                    'glpi_plugin_dlteams_records_items.records_id' => $record->fields['id'],
                    'glpi_plugin_dlteams_records_items.itemtype' => 'Group',
                    'glpi_plugin_dlteams_records_items.itemtype1' => null,
//                    Group::getTable().'.is_deleted' => 0,
                ],
            ]
        ];

        // Get recipients from DB
        $iterator['recipient'] = $DB->request($req_group);
        $recipients = [];
        $items_list = [];
        $used = [];


        foreach ($iterator['recipient'] as $id => $data) {
            // while ($data = $iterator->next()) {
            $items_list[$data['linkid']] = $data;

            $used[$data['linkid']] = $data['linkid'];
        }


        $groups = $this->generateTable1($items_list, 'group');

//      supplier
        $req_group = [
            'SELECT' => [
                'glpi_plugin_dlteams_records_items.id AS linkid',
                'glpi_plugin_dlteams_records_items.itemtype AS itemtype',
                'glpi_plugin_dlteams_records_items.items_id AS items_id',
                'glpi_plugin_dlteams_records_items.comment AS comment',
            ],
            'FROM' => 'glpi_plugin_dlteams_records_items',
            'ORDER' => ['glpi_plugin_dlteams_records_items.id ASC'],
            'OR' => [
                [
                    'glpi_plugin_dlteams_records_items.records_id' => $record->fields['id'],
                    'glpi_plugin_dlteams_records_items.itemtype' => 'PluginDlteamsThirdPartyCategory',
                    'glpi_plugin_dlteams_records_items.itemtype1' => null,
                ],
                [
                    'glpi_plugin_dlteams_records_items.records_id' => $record->fields['id'],
                    'glpi_plugin_dlteams_records_items.itemtype' => 'Supplier',
                    'glpi_plugin_dlteams_records_items.itemtype1' => null,
                ],
            ]
        ];

        // Get recipients from DB
        $iterator['recipient'] = $DB->request($req_group);
        $items_list = [];
        $used = [];


        foreach ($iterator['recipient'] as $id => $data) {
            $items_list[$data['linkid']] = $data;
            $used[$data['linkid']] = $data['linkid'];
        }

        $tiers = $this->generateTable2($items_list, 'tiers');


        // Get recipients from DB
        $iterator['recipient'] = PluginDlteamsRecord_External::getListForItem($record);
        $recipients = [];
        $used = [];

        foreach ($iterator['recipient'] as $id => $data) {
            // while ($data = $iterator->next()) {
            $recipients[$data['linkid']] = $data;

            $used[$data['linkid']] = $data['linkid'];
        }


        $recipients = $this->generateTable($recipients, 'recipient');


        // == Process stuff ==
        // Get process
        $process_checked = json_decode($record->fields['external_process'] ?? '{}', true);
        $choices = [
            __("France", 'dlteams'),
            __("European Union", 'dlteams'),
            __("World", 'dlteams'),
            $process_checked['other'] ?? ''
        ];
        $process_text = self::getCheckedList($choices, $process_checked) ?: __("N/A", 'dlteams');
        $row = [
            'section' => __("Process PDF", 'dlteams'),
            'value' => htmlspecialchars_decode($process_text)
        ];

        $lieux_de_traitements = $row;


        return [
            "lieux_de_traitements" => $lieux_de_traitements,
            "recipients" => $recipients,
            "tiers" => $tiers,
            "groups" => $groups
        ];
    }

    protected function printDataRetention(PluginDlteamsRecord $record)
    {

        $retention = (new PluginDlteamsRecord_Retention())
            ->find([PluginDlteamsRecord::getForeignKeyField() => $record->fields['id']]);

        $this->writeInternal('<h2 style="color:#ffffff;background-color:#447BD1">' . PluginDlteamsRecord_Retention::getTypeName(0) . '</h2>', [
            'linebefore' => 1
        ]);

        if (!count($retention)) {
            /**add by me**/
            if ($this->HTML) {
                echo '<p style="margin-top:30px">' . __("Data retention not set.") . '</p>';
            } else {
                $this->writeInternal(__("Data retention not set.", 'dlteams'), [
                    'border' => 1,
                    'linebefore' => 1,
                ]);
            }
            /**add by me**/


        } else {
            foreach ($retention as $item) {

                $type = PluginDlteamsRecord_Retention::getAllTypesArray();

                $tbl =
                    '<table border="1" cellpadding="3" cellspacing="0">' .
                    '<tbody><tr>' .
                    '<td width="25%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';">' . __("Retention regulated by", 'dlteams') . '</td>' .
                    '<td width="75%"><strong>' . $type[$item['type']] . '</strong></td>';

                switch ($item['type']) {
                    case PluginDlteamsRecord_Retention::RETENTION_TYPE_CONTRACT:

                        $contract = new Contract();
                        $contract->getFromDB($item['contracts_id']);

                        $period = '';
                        if (!$item['contract_until_is_valid']) {
                            $scale = PluginDlteamsRecord_Retention::getRetentionPeriodScales($item['contract_retention_scale'], $item['contract_retention_value']);
                            $period = $item['contract_retention_value'] . ' ' . $scale;

                            if ($item['contract_after_end_of']) {
                                $period = sprintf(__("Data retention: %1\$s after contract is terminated", 'dlteams'), $period);
                            }
                        } else {
                            $period = __("Until contract is valid", 'dlteams');
                        }

                        $name = $contract->fields['name'];
                        if ($name == null) {
                            $name = ' ';
                        }
                        $num = $contract->fields['num'];
                        if ($num == null || empty($num)) {
                            $num = '';
                        } else {
                            $num = ' ' . $num;
                        }

                        $s_names = PluginDlteamsRecord_Contract::getSuppliersNamesNoIds($item['contracts_id'], ', ');
                        if (empty($s_names)) {
                            $s_names = __("N/A", 'dlteams');
                        }

                        $c_name = trim(sprintf(__("Contract name/number: %1\$s %2\$s", 'dlteams'), $name, $num));
                        $s_names = trim($s_names);
                        $begin_date = trim(sprintf(__("Begin date: %1\$s", 'dlteams'), Html::convDate($contract->fields['begin_date'])));
                        $comment = trim(sprintf(__("Comment: %1\$s", 'dlteams'), $contract->fields['comment']));
                        $period = trim($period);
                        $comment = trim($comment);

                        $tbl .=
                            '</tr><tr>' .
                            '<td width="25%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';">' . __("Contract info", 'dlteams') . '</td>' .
                            '<td width="75%">' .
                            '<ul>' .
                            '<li><strong>' . $c_name . '</strong></li>' .
                            '<li>' . __("Supplier", 'dlteams') . ': ' . $s_names . '</li>' .
                            '<li>' . $begin_date . '</li>' .
                            '<li>' . $period . '</li>';
                        if ($this->print_options['show_comments']) {
                            $tbl .=
                                '<li>' . nl2br($comment) . '</li>';
                        }

                        $tbl .=
                            '</ul>' .
                            '</td>';

                        break;
                    case PluginDlteamsRecord_Retention::RETENTION_TYPE_LEGALBASISACT:

                        $legal_basis = new PluginDlteamsLegalBasisAct();
                        $legal_basis->getFromDB($item['plugin_dlteams_legalbasisacts_id']);

                        $name = __("N/A", 'dlteams');
                        if (isset($legal_basis->fields['id'])) {
                            $name = $legal_basis->fields['name'];
                        }
                        $tbl .=
                            '</tr><tr>' .
                            '<td width="25%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';">' . '' . '</td>' .
                            '<td width="75%">' . sprintf(__("Name: %1\$s", 'dlteams'), $name) . '</td>';

                        break;
                    case PluginDlteamsRecord_Retention::RETENTION_TYPE_NONE:
                        $tbl .=
                            '</tr><tr>' .
                            '<td width="25%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';">' . '' . '</td>' .
                            '<td width="75%">' . __("Data retention is not required", 'dlteams') . '</td>';
                        break;
                    case PluginDlteamsRecord_Retention::RETENTION_TYPE_OTHER:
                        break;
                }

                $tbl .=
                    '</tr><tr>' .
                    '<td width="25%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';">' . __("Additional information", 'dlteams') . '</td>' .
                    '<td width="75%">' . nl2br($item['additional_info']??"") . '</td>' .
                    '</tr></tbody>' .
                    '</table>';

            }

            $this->pdf->writeHTML($tbl, true, false, false, true, '');

        }

        $this->insertNewPageIfBottomSpaceLeft();

    }

    protected function printContracts(PluginDlteamsRecord $record, $type, $print_header)
    {

        $get_expired = $this->print_options['show_expired_contracts'];
        $iterator = PluginDlteamsRecord_Contract::getContracts($record, $type, $get_expired);
        $number = count($iterator);

        if (!$number && !$this->print_options['show_contracs_types_header_if_empty']) {
            $this->insertNewPageIfBottomSpaceLeft();

            return $number;
        }

        if (!$print_header) {
            $this->writeInternal('<h2 style="color:#ffffff;background-color:#447BD1">' . __("Contracts related to processing activity", 'dlteams') . '</h2>', [
                'linebefore' => 1
            ]);
        }

        $subtitle = PluginDlteamsRecord_Contract::getContractTypeStr($type);

        $this->writeInternal('<h3 style="color:#ffffff;background-color:#447BD1">' . $subtitle . '</h3>', [
            'linebefore' => 1
        ]);

        if (!$number) {
            $this->writeInternal(__("None.", 'dlteams'), [
                'border' => 1,
                'linebefore' => 1
            ]);
        } else {

            if ($this->print_options['show_comments']) {
                $cols_width = ['13', '23', '13', '18', '10', '23'];
            } else {
                $cols_width = ['18', '28', '18', '28', '10', '0'];
            }

            $tbl = '<table border="1" cellpadding="3" cellspacing="0">
            <thead><tr>
            <th width="' . $cols_width[0] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Supplier") . '</h4></th>
            <th width="' . $cols_width[1] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Location") . '</h4></th>
            <th width="' . $cols_width[2] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Contact") . '</h4></th>
            <th width="' . $cols_width[3] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Contract info", 'dlteams') . '</h4></th>
            <th width="' . $cols_width[4] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Expiry", 'dlteams') . '</h4></th>';
            if ($this->print_options['show_comments']) {
                $tbl .=
                    '<th width="' . $cols_width[5] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Comment") . '</h4></th>';
            }
            $tbl .=
                '</tr></thead><tbody>';

            //while ($data = $iterator->next()) {
            foreach ($iterator as $id => $data) {

                $supplier_name = '';
                $supplier_name .= $data['suppliers_name'];

                $location = '';
                if ($data['suppliers_address']) {
                    $location .= $data['suppliers_address'];
                }
                if ($data['suppliers_postcode']) {
                    $location .= ', ' . $data['suppliers_postcode'];
                }
                if ($data['suppliers_town']) {
                    $location .= ' ' . $data['suppliers_town'];
                }
                if ($data['suppliers_state']) {
                    $location .= ', ' . $data['suppliers_state'];
                }
                if ($data['suppliers_country']) {
                    $location .= ', ' . $data['suppliers_country'];
                }

                $contact = '';
                if ($data['suppliers_phonenumber']) {
                    $contact .= $data['suppliers_phonenumber'];
                }
                if ($data['suppliers_fax']) {
                    $contact .= ' ' . __("fax") . ': ' . $data['suppliers_fax'];
                }
                if ($data['suppliers_email']) {
                    $contact .= ' ' . __("email") . ': ' . $data['suppliers_email'];
                }

                $contract_info = '';
                if ($data['contracts_name']) {
                    $contract_info .= $data['contracts_name'];
                }
                if ($data['contracts_num']) {
                    $contract_info .= ' ' . $data['contracts_num'];
                }
                if ($data['contracts_begin_date']) {
                    $contract_info .= '<br>' . __("Begin date:", 'dlteams') . '' . $data['contracts_begin_date'];
                }

                $expiry = Infocom::getWarrantyExpir($data['contracts_begin_date'], $data['contracts_duration'], $data['contracts_notice'], false);
                $expiry_bkg = '' . self::TEXT_COLOR_ONBG . 'FFF';
                if (new DateTime($expiry) < new DateTime()) {
                    $expiry_bkg = '#FF0000';
                }

                $comments = '';
                if ($data['contracts_comment']) {
                    $comments = $data['contracts_comment'];
                }

                $tbl .= '<tr>
               <td width="' . $cols_width[0] . '%">' . $supplier_name . '</td>
               <td width="' . $cols_width[1] . '%">' . $location . '</td>
               <td width="' . $cols_width[2] . '%">' . $contact . '</td>
               <td width="' . $cols_width[3] . '%">' . $contract_info . '</td>
               <td width="' . $cols_width[4] . '%" style="background-color:' . $expiry_bkg . '">' . $expiry . '</td>';
                if ($this->print_options['show_comments']) {
                    $tbl .=
                        '<td width="' . $cols_width[5] . '%">' . nl2br($comments??"") . '</td>';
                }
                $tbl .=
                    '</tr>';

            }

            $tbl .= '</tbody></table>';

            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->writeHTML($tbl, true, false, false, true, '');

        }

        $this->insertNewPageIfBottomSpaceLeft();

        return $number;

    }

    protected function printPersonalDataCategories(PluginDlteamsRecord $record)
    {

        $this->writeInternal(
            '<h2 style="color:#ffffff;background-color:#447BD1">' . PluginDlteamsRecord_PersonalDataCategory::getTypeName(1) . '</h2>', [
            'linebefore' => 1
        ]);

        $pdc_list = (new PluginDlteamsRecord_PersonalDataCategory())
            ->find([PluginDlteamsRecord::getForeignKeyField() => $record->fields['id']]);

        if (!count($pdc_list)) {
            $this->writeInternal(__("None assigned.", 'dlteams'), [
                'border' => 1,
                'linebefore' => 1
            ]);
        } else {

            if ($this->print_options['show_inherited_from']) {
                if ($this->print_options['show_comments']) {
                    $cols_width = ['35', '10', '35', '20'];
                } else {
                    $cols_width = ['50', '10', '40', '0'];
                }
            } else {
                if ($this->print_options['show_comments']) {
                    $cols_width = ['60', '10', '0', '30'];
                } else {
                    $cols_width = ['90', '10', '0', '0'];
                }
            }

            $tbl = '<table border="1" cellpadding="3" cellspacing="0">';
            $tbl .=
                '<thead><tr>' .
                '<th width="' . $cols_width[0] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Complete name", 'dlteams') . '</h4></th>' .
                '<th width="' . $cols_width[1] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Special category", 'dlteams') . '</h4></th>';
            if ($this->print_options['show_inherited_from']) {
                $tbl .=
                    '<th width="' . $cols_width[2] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Introduced in", 'dlteams') . '</h4></th>';
            }
            if ($this->print_options['show_comments']) {
                $tbl .=
                    '<th width="' . $cols_width[3] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Comment") . '</h4></th>';
            }
            $tbl .=
                '</tr></thead><tbody>';

            foreach ($pdc_list as $pdc_item) {

                if ($this->print_options['show_full_personaldatacategorylist']) {
                    $sons = getSonsOf(PluginDlteamsPersonalDataCategory::getTable(), $pdc_item['plugin_dlteams_personaldatacategories_id']);
                } else {
                    $sons = [];
                    $sons[] = $pdc_item['plugin_dlteams_personaldatacategories_id'];
                }

                foreach ($sons as $son_item) {
                    $pdc = new PluginDlteamsPersonalDataCategory();
                    $pdc->getFromDB($son_item);

                    $tbl .=
                        '<tr> ' .
                        '<td width="' . $cols_width[0] . '%">' . $pdc->fields['completename'] . '</td>' .
                        '<td width="' . $cols_width[1] . '%" style="text-align:center;">' . Dropdown::getYesNo($pdc->fields['is_special_category']) . '</td>';
                    if ($this->print_options['show_inherited_from']) {
                        $tbl .=
                            '<td width="' . $cols_width[2] . '%">' . Dropdown::getDropdownName(Entity::getTable(), $pdc->fields['entities_id']) . '</td>';
                    }
                    if ($this->print_options['show_comments']) {
                        $tbl .=
                            '<td width="' . $cols_width[3] . '%">' . nl2br($pdc->fields['comment']??"") . '</td>';
                    }
                    $tbl .=
                        '</tr>';
                }
            }

            $tbl .= '</tbody></table>';

            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->writeHTML($tbl, true, false, false, true, '');

        }

        $this->insertNewPageIfBottomSpaceLeft();

    }

    protected function printSoftware(PluginDlteamsRecord $record)
    {

        global $DB;

        if (($record->fields['storage_medium'] == PluginDlteamsRecord::STORAGE_MEDIUM_PAPER_ONLY)
            || ($record->fields['storage_medium'] == PluginDlteamsRecord::STORAGE_MEDIUM_UNDEFINED)) {
            return;
        }

        $query = '
         SELECT
            glpi_softwares.id AS software_id,
             glpi_softwares.name AS software_name,
             glpi_softwares.comment AS software_comment,
             glpi_softwares.entities_id AS software_entities_id,
             glpi_softwarecategories.name AS software_category_name,
             glpi_softwarecategories.id AS softwarecategories_id,
             glpi_softwarecategories.completename AS sotwarecategories_completename,
             glpi_softwarecategories.comment AS softwarecategories_comment,
             glpi_manufacturers.id AS manufacturer_id,
             glpi_manufacturers.name AS manufacturer_name,
             glpi_manufacturers.comment AS manufacturer_comment
         FROM
            glpi_plugin_dlteams_records_softwares
         LEFT JOIN
            glpi_softwares
             ON (glpi_plugin_dlteams_records_softwares.softwares_id = glpi_softwares.id)
         LEFT JOIN
            glpi_manufacturers
            ON (glpi_softwares.manufacturers_id = glpi_manufacturers.id)
         LEFT JOIN
            glpi_softwarecategories
             ON (glpi_softwares.softwarecategories_id = glpi_softwarecategories.id)
         WHERE
            glpi_plugin_dlteams_records_softwares.plugin_dlteams_records_id = ' . $record->fields['id'] . ' AND
             glpi_softwares.is_deleted = 0
      ';
        $software_list = $DB->request($query);

        $this->writeInternal('<h2 style="color:#ffffff;background-color:#447BD1">' . __("Software", 'dlteams') . '</h2>', [
            'linebefore' => 1
        ]);

        if (!count($software_list)) {
            $this->writeInternal(__("Software not assigned.", 'dlteams'), ['border' => 1]);
        } else {
            if ($this->print_options['show_inherited_from']) {
                if ($this->print_options['show_assets_owners']) {
                    if ($this->print_options['show_comments']) {
                        $cols_width = [26, 12, 12, 20, 15, 15];
                    } else {
                        $cols_width = [26, 12, 12, 30, 20, 0];
                    }
                } else {
                    if ($this->print_options['show_comments']) {
                        $cols_width = [25, 12, 13, 35, 0, 15];
                    } else {
                        $cols_width = [25, 15, 15, 45, 0, 0];
                    }
                }
            } else {
                if ($this->print_options['show_assets_owners']) {
                    if ($this->print_options['show_comments']) {
                        $cols_width = [30, 20, 20, 0, 15, 15];
                    } else {
                        $cols_width = [40, 20, 20, 0, 20, 0];
                    }
                } else {
                    if ($this->print_options['show_comments']) {
                        $cols_width = [40, 20, 20, 0, 0, 20];
                    } else {
                        $cols_width = [60, 20, 20, 0, 0, 0];
                    }
                }
            }

            $tbl =
                '<table border="1" cellpadding="3" cellspacing="0">' .
                '<thead><tr>' .
                '<th width="' . $cols_width[0] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Name", 'dlteams') . '</h4></th>' .
                '<th width="' . $cols_width[1] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Type", 'dlteams') . '</h4></th>' .
                '<th width="' . $cols_width[2] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Publisher", 'dlteams') . '</h4></th>';

            if ($this->print_options['show_inherited_from']) {
                $tbl .=
                    '<th width="' . $cols_width[3] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Introduced in", 'dlteams') . '</h4></th>';
            }
            if ($this->print_options['show_assets_owners']) {
                $tbl .=
                    '<th width="' . $cols_width[4] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Owner", 'dlteams') . '</h4></th>';
            }
            if ($this->print_options['show_comments']) {
                $tbl .=
                    '<th width="' . $cols_width[5] . '%" style="background-color:' . self::BG_COLOR . ';color:' . self::TEXT_COLOR_ONBG . ';"><h4>' . __("Comment") . '</h4></th>';
            }
            $tbl .= '</tr></thead><tbody>';

            foreach ($software_list as $item) {
                $tbl .=
                    '<tr>' .
                    '<td width="' . $cols_width[0] . '%">' . $item['software_name'] . '</td>' .
                    '<td width="' . $cols_width[1] . '%">' . $item['software_category_name'] . '</td>' .
                    '<td width="' . $cols_width[2] . '%">' . $item['manufacturer_name'] . '</td>';

                if ($this->print_options['show_inherited_from']) {
                    $tbl .=
                        '<td width="' . $cols_width[3] . '%">' . Dropdown::getDropdownName(Entity::getTable(), $item['software_entities_id']) . '</td>';
                }
                if ($this->print_options['show_assets_owners']) {
                    $owner_info = PluginGdprownerOwner::getOwnerInfo($item['software_id'], Software::class);
                    if (empty($owner_info)) {
                        $owner = __("Not assigned", 'dlteams');
                    } else {
                        $owner = $owner_info['owner_type_name'] . ': ' . $owner_info['owner_name'];
                    }
                    $tbl .=
                        '<td width="' . $cols_width[4] . '%">' . $owner . '</td>';

                }
                if ($this->print_options['show_comments']) {
                    $tbl .=
                        '<td width="' . $cols_width[5] . '%">' . $item['software_comment'] . '</td>';
                }
                $tbl .= '</tr>';
            }

            $tbl .= "</tbody></table>";

            $this->writeHtml($tbl);
        }

        $this->insertNewPageIfBottomSpaceLeft();

    }

    /**
     * @param PluginDlteamsRecord $record
     */
    protected function printSecurityMeasures(PluginDlteamsRecord $record)
    {

        // Display sensitive/profiling infos
        $rows = [];
        $rows[] = [
            'section' => __("Personally sensitive", 'dlteams'),
            'value' => $record->fields['sensitive'] ? __("Yes") : __("No")
        ];
        $rows[] = [
            'section' => __("Permit profiling", 'dlteams'),
            'value' => $record->fields['profiling'] ? __("Yes") : __("No")
        ];
        if ($record->fields['profiling']) $rows[] = [
            'section' => __("Is there a fully automated process involved (without human intervention)? Precise", 'dlteams'),
            'value' => nl2br($record->fields['profiling_auto']??"")
        ];

        // Get the checked choice on violation impact
//        $checked = json_decode($record->fields['impact_person'] ?? "{}", true);
        $choices = [
            1 => __("Negligible", 'dlteams'),
            2 => __("Limited", 'dlteams'),
            3 => __("Important", 'dlteams'),
            4 => __("Maximum", 'dlteams')
        ];

        $violationimpact_text = $record->fields['impact_person']?$choices[$record->fields['impact_person']]:"N/A";
        $rows[] = [
            'section' => __("Maximum impact on concerned persons", 'dlteams'),
            'value' => $violationimpact_text
        ];

        // Get the checked choice on violation impact level
//        $checked = json_decode($record->fields['impact_organism'] ?? "{}", true);
//        $choices [] = $checked['other'] ?? '';
        $violationimpactlevel_text = $record->fields['impact_organism']?$choices[$record->fields['impact_organism']]:"N/A";
        $rows[] = [
            'section' => __("Maximum impact on organism", 'dlteams'),
            'value' => $violationimpactlevel_text
        ];

        // Print Specific security measures
        $rows[] = [
            'section' => __("Specific security measures", 'dlteams'),
            'value' => isset($record->fields['specific_security_measures']) ? nl2br($record->fields['specific_security_measures']??"") : ""
        ];


        return [
            "sensibilite_impact_violation" => $rows,
            "protective_measures" => $this->printGeneralSecurityMeasures($record, true)
        ];
    }

    protected function printGeneralSecurityMeasures(PluginDlteamsRecord $record, $header = false)
    {
        global $DB;

        $result = $DB->request([
            'FROM' => 'glpi_plugin_dlteams_records_items',
            'SELECT' => [
                'glpi_plugin_dlteams_records_items.id',
                'glpi_plugin_dlteams_records_items.id as linkid',
                'glpi_plugin_dlteams_records_items.comment',
                'glpi_plugin_dlteams_records_items.itemtype as itemtype',
                'glpi_plugin_dlteams_records_items.items_id as items_id',
                'glpi_plugin_dlteams_protectivetypes.name AS typename',
                'glpi_plugin_dlteams_protectivecategories.name as nameCat',
                'glpi_plugin_dlteams_protectivemeasures.name as name',
                'glpi_plugin_dlteams_protectivemeasures.id as pm_id',
                'glpi_plugin_dlteams_protectivemeasures.content as content',
                'glpi_plugin_dlteams_protectivemeasures.entities_id as entities_id',
            ],
            'WHERE' => [
                'glpi_plugin_dlteams_records_items.itemtype' => PluginDlteamsProtectiveMeasure::class,
                'glpi_plugin_dlteams_records_items.records_id' => $record->fields['id'],
            ],
            'LEFT JOIN' => [
                'glpi_plugin_dlteams_protectivemeasures' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_records_items' => 'items_id',
                        'glpi_plugin_dlteams_protectivemeasures' => 'id'
                    ]
                ]
            ],
            'JOIN' => [
                'glpi_plugin_dlteams_protectivetypes' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_protectivemeasures' => "plugin_dlteams_protectivetypes_id",
                        'glpi_plugin_dlteams_protectivetypes' => "id"
                    ]
                ],
                'glpi_plugin_dlteams_protectivecategories' => [
                    'FKEY' => [
                        'glpi_plugin_dlteams_protectivemeasures' => "plugin_dlteams_protectivecategories_id",
                        'glpi_plugin_dlteams_protectivecategories' => "id"
                    ]
                ],
            ],

        ], "", true);


        $items_list = [];
        foreach ($result as $id => $data) $items_list[$data['linkid']] = $data;


        $protective_measures = [];
        foreach ($items_list as $item) {
            $temp = [];
            if ($item['name']) {
                $temp["name"] = $item['name'];
                $temp["content"] = $item['content'];
                $temp["typename"] = isset($item['typename']) ? $item['typename'] : '';
                $temp["comment"] = nl2br($item['comment']??"");

                array_push($protective_measures, $temp);
            }

        }

        return $protective_measures;
    }

    static function preparePrintOptionsFromForm($config = [])
    {
        $mod_config = self::getDefaultPrintOptions();

        if (is_array($config) && count($config)) {
            foreach ($config as $key => $val) {
                if (is_array($val) && count($val)) {
                    foreach ($val as $key2 => $val2) {
                        $mod_config[$key][$key2] = $val2;
                    }
                } else {
                    $mod_config[$key] = $val;
                }
            }
        }

        return $mod_config;
    }

    static function getDefaultPrintOptions()
    {

        $opt = self::$default_print_options;

        if (PluginDlteamsCreatePDFBase::isGdprownerPluginActive()) {
            $opt['show_assets_owners'] = 1;
        } else {
            $opt['show_assets_owners'] = 0;
        }
        $opt['show_record_owner'] = $opt['show_assets_owners'];

        return $opt;
    }

    /**
     * Generate table for Group, Supplier and Recipient(supplier)
     * extracted method to improve readability
     * @param $elements array
     * @param $item_type string "group", "supplier", "recipient"
     * @return string
     */
    protected function generateTable(array $elements, string $item_type)
    {

        // Set table content (rows)
        $recipients = [];
        foreach ($elements as $element) {
            $temp_rows = [];

            $data = $element;

            $itemtype_str = $data['itemtype'];
            $itemtype_object = new $itemtype_str();
            $itemtype_object->getFromDB($data['items_id']);

            $name = $itemtype_object->fields['name'];


            $itemtype1_str = $data['itemtype1'];
            $itemtype1_object = new $itemtype1_str();
            $itemtype1_object->getFromDB($data['items_id1']);

            $name1 = $itemtype1_object->fields['name'];

            $temp_rows["name"] = $name;
            $temp_rows["name1"] = $name1;
            $temp_rows["comment"] = $data["comment"];
            $temp_rows["typename"] = $data['itemtype'];

            array_push($recipients, $temp_rows);
        }

        return $recipients;
    }

    /*****add by me***/
    protected function generateTable1(array $elements, string $item_type)
    {

        $recipients = [];
        foreach ($elements as $element) {
            $data = $element;
            $temp_rows = [];
            $itemtype_str = $data['itemtype'];
            $itemtype_object = new $itemtype_str();
            $itemtype_object->getFromDB($data['items_id']);

            $name = $itemtype_object->fields['name'];
            $temp_rows["name"] = $name;
            $temp_rows["type"] = "Groupe";
            $temp_rows["comment"] = $element['comment'];

            array_push($recipients, $temp_rows);
        }

        return $recipients;
    }


    protected function generateTable2(array $elements, string $item_type)
    {

        $recipients = [];
        // Set table content (rows)
        foreach ($elements as $element) {
            $data = $element;
            $temp_rows = [];
            $itemtype_str = $data['itemtype'];
            $itemtype_object = new $itemtype_str();
            $itemtype_object->getFromDB($data['items_id']);

            $name = $itemtype_object->fields['name'];
            $temp_rows["name"] = $name;
            $temp_rows["type"] = $data['itemtype']::getTypeName();
            $temp_rows["comment"] = $element['comment'];
            $temp_rows["typename"] = $data['itemtype'];

            array_push($recipients, $temp_rows);
        }

        return $recipients;
    }


    /***add by me****/

    protected function printHtmlHead($title)
    {
        ob_start();
        global $CFG_GLPI;
        // Start the page
        echo "<!DOCTYPE html>\n";
        echo "<html lang=\"{$CFG_GLPI["languages"][$_SESSION['glpilanguage']][3]}\">";
        echo "<head><title>$title</title>";
        echo "<meta charset=\"utf-8\">";
        echo "<!-- Here is CSS and JS files that you can create to include your style and more without modifying this html each time a report is generated -->\n";
        echo "<!-- Voici des fichiers CSS et JS que vous pouvez créer pour inclure du style ou des modifications avoir à modifier cet html à chaque génération de rapport -->";
        echo "<link rel='stylesheet' type='text/css' href='report.css'>";
        echo "<script src='report.js'></script>";
        echo "</head><body>";
    }

    protected function printHtmlEnd()
    {
        /*$year = date('Y');
      echo "<footer><a href='https://dlplace.eu' class='copyright'>dlplace.eu © $year</a></footer>";*/
        /**add by me**/
        /* echo "<footer><a href='https://dlteams.eu' class='copyright'>dlteams.eu © $year</a></footer>";
	  /*add by me**/
        /*echo "</body>";
      echo "</html>";*/
    }

    static function getRequest4($record)
    {
        return [
            'SELECT' => [
                'glpi_plugin_dlteams_controllerinfos.id AS linkid',
                'glpi_plugin_dlteams_controllerinfos.guid AS guid',
                'glpi_plugin_dlteams_controllerinfos.entities_id AS entitiesid',
            ],
            'FROM' => 'glpi_plugin_dlteams_controllerinfos',
            'WHERE' => [
                'glpi_plugin_dlteams_controllerinfos.entities_id' => $record
            ]
        ];
    }
}


