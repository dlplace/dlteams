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

class PluginDlteamsControllerInfo extends CommonDBChild
{
    static public $itemtype = 'Entity';
    static public $items_id = 'entities_id';
    static public $logs_for_parent = true;
    static public $checkParentRights = true;
    static $rightname = 'plugin_dlteams_controllerinfo';

    static function getTypeName($nb = 0)
    {
        return __("GDPR Controller Info", 'dlteams');
    }

    function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if (!PluginDlteamsControllerInfo::canView()) {
            return false;
        }

        switch ($item->getType()) {
            case Entity::class :
                return self::createTabEntry(PluginDlteamsControllerInfo::getTypeName(), 0);
        }

        return '';
    }

    static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case Entity::class :

                $info = new self();
                $info->showForEntity($item);
                break;
        }
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

    function showForEntity(Entity $entity, $options = [])
    {
        global $CFG_GLPI;
        global $DB;
        $colsize1 = '15%';
        $colsize2 = '35%';
        $colsize3 = '15%';
        $colsize4 = '35%';
        $this->getFromDBByCrit(['entities_id' => $entity->fields['id']]);

        if (!isset($this->fields['id'])) {
            $this->fields['id'] = -1;
        }

        $canedit = $this->can($this->fields['id'], UPDATE);

        if ($this->fields['id'] <= 0 && !PluginDlteamsControllerInfo::canCreate()) {
            echo "<br><br><span class='b'>" . __("Controller information not set.", 'dlteams') . "</span><br><br>";
            return;
        }

        $options['canedit'] = $canedit;
        $options['formtitle'] = __("Manage entity Controller information", 'dlteams');
        $this->initForm($this->fields['id'], $options);
        $this->showFormHeader($options);

        echo "<table class='tab_cadre_fixe' id='mainformtable'>";
        echo "<tr class='headerRow'>";
        echo "<th colspan='4' class=''>" . __('Entete du Registre (RGPD Article 30-1a)', 'dlteams') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_2'><td width='$colsize1'>";
        echo __("Legal representative", 'dlteams');
        echo "</td><td width='$colsize2'>";
        User::dropdown([
            'right' => 'all',
            'name' => 'users_id_representative',
            'value' => array_key_exists('users_id_representative', $this->fields) ? $this->fields['users_id_representative'] : null
        ]);

        echo "</td><td width='$colsize3'>";
        echo __("Data Protection Officer", 'dlteams');
        echo "</td><td  width='$colsize4'>";
        User::dropdown([
            'right' => 'all',
            'name' => 'users_id_dpo',
            'value' => array_key_exists('users_id_dpo', $this->fields) ? $this->fields['users_id_dpo'] : null
        ]);
        echo "</td></tr>";

        echo "</td><td width='$colsize1'>";
        echo __("Intitulé de l'organisme", 'dlteams');
        echo "</td><td colspan='3'>";
        if ($this->fields['id'] <= 0) {
            $this->fields['controllername'] = '';
        }
        $controller_name = Html::cleanInputText($this->fields['controllername']);
        echo "<input type='text' style='width:98%' maxlength=150 name='controllername' required value='" . $controller_name . "'/>";

        echo "</td></tr><tr><td colspan='2'>" . __("PDF Header logo", 'dlteams') . "<br><i>" .
            __("Recommended : less than 50KB to avoid slowing down PDF generation", 'dlteams') .
            "</i></td><td colspan='2'>";
        Document::dropdown([
            'name' => "logo_id",
            'entity' => $entity->fields['id'],
        ]);
        $logo = new Document();
        if (array_key_exists('logo_id', $this->fields) && $this->fields['logo_id'] !== 0) {
            $logo->getFromDB($this->fields['logo_id']);

            echo "<br><i>" .
                sprintf(__("Actual logo document: \"%s\" (filename: \"%s\")", 'dlteams'), (isset($logo->fields['name']) ? $logo->fields['name'] : ''), (isset($logo->fields['filename']) ? $logo->fields['filename'] : '')) .
                "</i>";
        }
        echo "</td></tr>";
        echo "<tr class='tab_bg_1'>";
        echo "<td class='center' colspan='4'>";
        echo "<input type='hidden' name='entities_id' value='" . $entity->fields['id'] . "'/>";
        echo "</td></tr>";
        echo "</br>";
        echo "</table>";
        $this->showFormButtons($options);
        Html::closeForm();

        //Guid Emplacement
        echo "<table class='tab_cadre_fixe' id='mainformtable'>";
        echo "<tr class='headerRow'>";
        echo "<th colspan='3' class=''>" . __('Dossier de publication', 'dlteams') . "</th>";
        echo "</tr>";

        echo "<tr class='tab_bg_3'><td colspan='4'><center>"; //<strong>";
        echo __("Cliquez pour accéder au dossier", 'dlteams');
        echo "</strong></center></td></tr>";

        //show the guid value
        $iterator = $DB->request(self::getRequest($entity->fields['id']));
        $number = count($iterator);
        $items_list = [];
        foreach ($iterator as $id => $data) {
            $items_list[$data['linkid']] = $data;
            $used[$data['linkid']] = $data['linkid'];
        }
        foreach ($items_list as $value) {
            $guid = $value['guid'];
        }
        echo "<tr class='tab_bg_2'><td width='$colsize1'>";
        if (isset($guid)) {
            echo "<div class='center' >";
            echo "GUID Unique : ";
            // echo "<a target='_blank' href = 'https://dlregister.app/pub/$guid'> $guid </a>";
            $server_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
            echo "<a target='_blank' href=" . $server_url . "/pub/" . $guid . "> $guid </a>";
            echo "</div>";
        }
        echo "</td></tr>";
        //echo "<tr class='tab_bg_1'>";
        // to create new GUID
        echo "</table>";
        echo "<form method='post' action=\"" . $CFG_GLPI['root_doc'] . "/marketplace/dlteams/front/controllerinfo.form.php\">";
        if (!(isset($guid) && $guid != NULL)) {
            //echo "<br>";
            echo "<td colspan='4'>";
            echo "<div class='center'>";
            echo "<input type='hidden' name='id_value' value='" . $entity->fields['id'] . "'/>";
            echo "<input type='submit' name='generate_guid' value=\"" . __("Générer GUID", 'dlteams') . "\" class='submit'>";
            echo "</div>";
            echo "</td>";
            //echo "</br>";
        }
        Html::closeForm();

//    to create dossier de publication
        echo "<form method='post' action=\"" . $CFG_GLPI['root_doc'] . "/marketplace/dlteams/front/controllerinfo.form.php\">";
        echo "<td colspan='4'>";
        echo "<div class='center'>";
        echo "<input type='hidden' name='guid_value' value='" . (isset($guid) ? $guid : 0) . "'/>";
        echo "<input type='hidden' name='id_value' value='" . $entity->fields['id'] . "'/>";
        echo "<input type='hidden' name='deliverables_id' value='" . $this->fields["id"] . "'/>";
        echo "<input type='submit' name='generate_publication_folder' value=\"" . __("Générer dossier de publication", 'dlteams') . "\" class='submit'>";
        echo "</div>";
        echo "</td>";

        /*echo "<td colspan='4'>";
        echo "<div class='center'>";
        echo "<input type='hidden' name='guid_value' value='" . (isset($guid) ? $guid : 0 ) . "'/>";
        echo "<input type='hidden' name='id_value' value='" . $entity->fields['id'] . "'/>";
        echo "<input type='submit' name='delete_guid' value=\"" . __("Supprimer GUID", 'dlteams') . "\" class='submit'>";
        echo "</div>";
        echo "</td>";*/
        echo "</tr>";
        Html::closeForm();
        //GUID


        /*if ((Session::haveRight('plugin_dlteams_createpdf', CREATE))) {
            echo "<div class='firstbloc'>";
            echo "<form method='GET' action=\"" . $CFG_GLPI['root_doc'] . "/marketplace/dlteams/front/createpdf.php\">";
            echo "<table class='tab_cadre_fixe' id='mainformtable'>";
            echo "<tbody>";
            echo "<tr class='headerRow'>";
            echo "<th colspan='3' class=''>" . __('PDF creation settings', 'dlteams') . "</th>";
            echo "</tr>";

            $_config = PluginDlteamsCreatePDF::getDefaultPrintOptions();
            $_config['report_type'] = PluginDlteamsCreatePDF::REPORT_FOR_ENTITY;
            PluginDlteamsCreatePDF::showConfigFormElements($_config);

            echo "</table>";
            echo "<input type='hidden' name='report_type' value=\"" . PluginDlteamsCreatePDF::REPORT_FOR_ENTITY . "\">";
            echo "<input type='hidden' name='entities_id' value='" . $entity->fields['id'] . "'>";
            echo "<input type='hidden' name='action' value=\"print\">";
            echo "<input type='submit' class='submit' name='createpdf' value='" . __("Create Controller RoPA PDF for Entity", 'dlteams') . "' />";
            Html::closeForm();
            echo "</div>";
        }*/

        // DATA MIGRATION -------------------------------------
 /*       if ((Session::haveRight('plugin_dlteams_createpdf', CREATE))) {
			echo "<form method='post' action='#'>";
			echo "<table class='tab_cadre' cellpadding='5' width='100%'>";
			echo "<tr>";
			echo "<th colspan='2'>" . __("Data Migration", 'dlteams') . "</th>";
			echo "</tr>";
			echo "<tr class='tab_bg_1'>";
			echo "<td colspan='2'><strong>" . __("Importer des éléments en masse", 'dlteams') . "</strong></td>";
			echo "</tr>";
			*//*      echo "<tr class='tab_bg_1'>";
              echo "<td>" . __("Personnes concernées par ce traitement et catégories de données", 'dlteams') . "</td>";
              echo "<td>";
              Html::showCheckbox([
                 'name'  => 'migrate_categories_of_data_subjects',
                 'title' => __("Categories of data subjects", 'dlteams'),
                 'checked' => 1]);
              echo "</td>";
              echo "</tr>";*//*
			echo "<tr class='tab_bg_1'>";
			echo "<td>" . __("Référentiel RGPD : projet, tâches de projets, évenements, ToDo", 'dlteams') . "</td>";
			echo "<td>";
			/*Html::showCheckbox([
			'name'  => 'migrate_categories_of_data_subjects',
			'title' => __("Categories of data subjects", 'dlteams'),
			'checked' => 0]);*//*
			Project::dropdown([
				'addicon' => Project::canCreate(),
				'name' => 'projects_id',
				'width' => '250px'
			]);
			echo "</td></tr>";*/
        
			/*echo "<tr class='tab_bg_1'>";
			echo "<td>" . __("Legality and retention period", 'dlteams') . "</td>";
			echo "<td>";
			Html::showCheckbox([
           'name'  => 'migrate_legal_bases',
           'title' => __("Legal bases", 'dlteams'),
           'checked' => 0]);
			echo "</td></tr>";
			echo "<tr class='tab_bg_1'>";
			echo "<td>" . __("Security measures", 'dlteams') . "</td>";
			echo "<td>";
			Html::showCheckbox([
				'name'  => 'migrate_security_measures',
				'title' => __("Security measures", 'dlteams'),
				'checked' => 0]);
			echo "</td>";
			echo "</tr>";

			echo "<tr class='tab_bg_1'>";
			echo "<td>" . __("Impact", 'dlteams') . "</td>";
			echo "<td>";
			Html::showCheckbox([
				'name'  => 'migrate_impact',
				'title' => __("Impact", 'dlteams'),
				'checked' => 0]);
			echo "</td></tr>";*//*
  
        echo "<tr class='tab_bg_1'>";
        echo "<td colspan='2'>";
        echo "<div class='center'>";
        echo "<input type='submit' name='migrate' value=\"" . __("Migrate", 'dlteams') . "\" class='submit'>";
        echo "</div>";
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        Html::closeForm();
*/
        echo "<p></p>";
    }

    static function getFirstControllerInfo($entity_id, $allow_from_ancestors = true)
    {

        $controllerinfo = new PluginDlteamsControllerInfo();
        $controllerinfo->getFromDBByCrit(['entities_id' => $entity_id]);

        if (!$allow_from_ancestors) {
            return $controllerinfo;
        } else {
            if ((isset($controllerinfo->fields['id'])) &&
                ($controllerinfo->fields['is_recursive'] &&
                    ($controllerinfo->fields['entities_id'] == $entity_id))
            ) {
                return $controllerinfo;
            } else {
                $ancestors = getAncestorsOf('glpi_entities', $entity_id);
                foreach (array_reverse($ancestors) as $ancestor) {
                    return PluginDlteamsControllerInfo::getFirstControllerInfo($ancestor);
                }
            }
        }
    }

    static function getContractTypes($entity_id, $compact = false)
    {

        $controllerinfo = PluginDlteamsControllerInfo::getFirstControllerInfo($entity_id, PluginDlteamsConfig::getConfig('system', 'allow_controllerinfo_from_ancestor'));

        $out = [
            'contracttypes_id_jointcontroller' => -1,
            'contracttypes_id_processor' => -1,
            'contracttypes_id_thirdparty' => -1,
            'contracttypes_id_internal' => -1,
            'contracttypes_id_other' => -1,
        ];

        foreach ($out as $key => $value) {
            if (isset($controllerinfo->fields[$key])) {
                $out[$key] = $controllerinfo->fields[$key];
            }
        }

        if ($compact) {
            $out = array_values($out);
        }

        return $out;
    }

    static function getSearchOptionsControllerInfo()
    {

        $options = [];

        $options[5601] = [
            'id' => '5601',
            'table' => 'glpi_users',
            'field' => 'name',
            'linkfield' => 'users_id_representative',
            'name' => __("Legal representative", 'dlteams'),
            'massiveaction' => false,
            'datatype' => 'dropdown',
            'joinparams' => [
                'beforejoin' => [
                    'table' => self::getTable(),
                    'joinparams' => [
                        'jointype' => 'child'
                    ]
                ]
            ]
        ];

        $options[5602] = [
            'id' => '5602',
            'table' => 'glpi_users',
            'field' => 'name',
            'linkfield' => 'users_id_dpo',
            'name' => __("Data Protection Officer", 'dlteams'),
            'massiveaction' => false,
            'datatype' => 'dropdown',
            'joinparams' => [
                'beforejoin' => [
                    'table' => self::getTable(),
                    'joinparams' => [
                        'jointype' => 'child'
                    ]
                ]
            ]
        ];

        $options[5603] = [
            'id' => '5603',
            'table' => self::getTable(),
            'field' => 'controllername',
            'name' => __("Controller Name", 'dlteams'),
            'massiveaction' => false,
            'joinparams' => [
                'jointype' => 'child'
            ],
        ];

        return $options;
    }

    function rawSearchOptions()
    {

        $tab = [];

        $tab[] = [
            'id' => '11',
            'table' => $this->getTable(),
            'field' => 'controllername',
            'name' => __("Controller Name", 'dlteams'),
            'massiveaction' => false,
            'datatype' => 'text',
        ];

        $tab = array_merge(parent::rawSearchOptions(), $tab);

        return $tab;
    }

    static function getRequest($id)
    {
        return [
            'SELECT' => [
                'glpi_plugin_dlteams_controllerinfos.id AS linkid',
                'glpi_plugin_dlteams_controllerinfos.guid AS guid',
                'glpi_plugin_dlteams_controllerinfos.entities_id AS entitiesid',

            ],
            'FROM' => 'glpi_plugin_dlteams_controllerinfos',
            'WHERE' => [
                'glpi_plugin_dlteams_controllerinfos.entities_id' => $id
            ]

        ];
    }

}
