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
 
class PluginDlteamsSecurityMeasure extends CommonDropdown {

   static $rightname = 'plugin_dlteams_securitymeasure';

   public $dohistory = true;

   const SECURITYMEASURE_TYPE_ORGANIZATION = 1;
   const SECURITYMEASURE_TYPE_PHYSICAL = 4;
   const SECURITYMEASURE_TYPE_IT = 8;

   const DROPDOWN_GROUPBY_TYPE = 1;
   const DROPDOWN_GROUPBY_TYPE_2 = 2;
   const DROPDOWN_GROUPBY_ENTITY = 3;

   static function getTypeName($nb = 0) {

      return _n("Security Measure", "Security Measures", $nb, 'dlteams');
   }

   function getAdditionalFields() {

      return [
         [
            'name' => 'type',
            'label' => __("Type"),
            'list' => true,
         ],
         [
            'name' => 'content',
            'label' => __("Description"),
            'type' => 'textarea',
         ],
      ];
   }

   static function getSpecificValueToDisplay($field, $values, array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }

      switch ($field) {
         case 'type' :
            $types = self::getAllTypesArray();

            return $types[$values[$field]];
      }

      return '';
   }

   static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []) {

      if (!is_array($values)) {
         $values = [$field => $values];
      }
      $options['display'] = false;

      switch ($field) {
         case 'type' :

            return self::dropdownTypes($name, $values[$field], false);
      }

      return parent::getSpecificValueToSelect($field, $name, $values, $options);
   }

   function displaySpecificTypeField($id, $field = [], array $options = []) {

      if ($field['name'] == 'type') {
         self::dropdownTypes($field['name'], $this->fields[$field['name']], true);
      }
   }

   static function dropdownTypes($name, $value = 0, $display = true) {

      return Dropdown::showFromArray($name, self::getAllTypesArray(), [
         'value' => $value, 'display' => $display]);
   }

   static function getAllTypesArray() {

      return [
         //'' => Dropdown::EMPTY_VALUE,
         self::SECURITYMEASURE_TYPE_ORGANIZATION => __("Organizational", 'dlteams'),
         self::SECURITYMEASURE_TYPE_PHYSICAL     => __("Physical", 'dlteams'),
         self::SECURITYMEASURE_TYPE_IT           => __("IT", 'dlteams')
      ];
   }

   function prepareInputForAdd($input) {

      $input['users_id_creator'] = Session::getLoginUserID();

      return parent::prepareInputForAdd($input);
   }

   function prepareInputForUpdate($input) {

      $input['users_id_lastupdater'] = Session::getLoginUserID();

      return parent::prepareInputForUpdate($input);
   }

   function cleanDBonPurge() {

      $rel = new PluginDlteamsRecord_SecurityMeasure();
      $rel->deleteByCriteria(['plugin_dlteams_securitymeasures_id' => $this->fields['id']]);

   }

   static function dropdown($options = []) {

      global $DB;

      $p = [
         'name'             => 'plugin_dlteams_securitymeasures_id',
         'value'            => '',
         'all'              => 0,
         'width'            => '80%',
         'entity'           => -1,
         'entity_sons'      => false,
         'used'             => [],
         'rand'             => mt_rand(),
         'display'          => true,
         'specific_tags'    => [],
         'option_tooltips'  => [],
         'group_by'         => self::DROPDOWN_GROUPBY_TYPE,
         'addicon'          => false
      ];

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      if ((strlen($p['value']) == 0) || !is_numeric($p['value'])) {
         $p['value'] = 0;
      }

      $tab = [];
      $tab[] = Dropdown::EMPTY_VALUE;

      $error = false;

      if (!($p['entity'] < 0) && $p['entity_sons']) {
         if (is_array($p['entity'])) {
            $tab[] = "entity_sons options is not available with array of entity";
            $error = true;
         } else {
            $p['entity'] = getSonsOf('glpi_entities', $p['entity']);
         }
      }

      if (!$error) {

         $entities = getAncestorsOf('glpi_entities', $p['entity']);
         array_push($entities, $p['entity']);

         $query = '
            SELECT
               `glpi_plugin_dlteams_securitymeasures`.`id`,
               `glpi_plugin_dlteams_securitymeasures`.`name`,
               `glpi_plugin_dlteams_securitymeasures`.`type`,
               `glpi_plugin_dlteams_securitymeasures`.`entities_id`,
               `glpi_entities`.`completename`
            FROM
               `glpi_plugin_dlteams_securitymeasures` 
            LEFT JOIN
               `glpi_entities` ON (`glpi_plugin_dlteams_securitymeasures`.`entities_id` = `glpi_entities`.`id`)
            WHERE
               (
                  (`glpi_plugin_dlteams_securitymeasures`.`is_recursive` = 1 AND
                   `glpi_plugin_dlteams_securitymeasures`.`entities_id` IN (' . implode(',', $entities) . ')
                  ) OR (
                   `glpi_plugin_dlteams_securitymeasures`.`entities_id` = ' . $p['entity'] . '
                  )
               )
            ORDER BY
               FIELD(`glpi_plugin_dlteams_securitymeasures`.`entities_id`, 4) DESC,
               `glpi_plugin_dlteams_securitymeasures`.`type`';

         $types = self::getAllTypesArray();

         $result = $DB->request($query);

         $p['group_by'] = self::DROPDOWN_GROUPBY_TYPE_2;

         switch ($p['group_by']) {
            case self::DROPDOWN_GROUPBY_ENTITY :

               $cur_name = '';
               while ($item = $result->next()) {
                  if ($cur_name != $item['completename']) {
                     $cur_name = $item['completename'];
                  }

                  $name = $types[$item['type']] . ':&nbsp&nbsp&nbsp&nbsp' . $item['name'];
                  if ($_SESSION['glpiis_ids_visible'] || empty($item['name'])) {
                     $name = sprintf(__('%1$s (%2$s)'), $name, $item['id']);
                  }
                  $tab[$cur_name][$item['id']] = $name;
                  $p['option_tooltips'][$cur_name]['__optgroup_label'] = '';
               }
               break;

            case self::DROPDOWN_GROUPBY_TYPE :
            case self::DROPDOWN_GROUPBY_TYPE_2 :

               $cur_type = '';
               while ($item = $result->next()) {
                  if ($cur_type != $item['type']) {
                     $cur_type = $item['type'];
                  }

                  if ($p['group_by'] == self::DROPDOWN_GROUPBY_TYPE) {
                     $name = $item['completename'] . ':&nbsp;&nbsp;&nbsp;&nbsp;' . $item['name'];
                  } else {
                     $name = $item['completename'] . " > " .  $item['name'];
                  }
                  if ($_SESSION['glpiis_ids_visible'] || empty($item['name'])) {
                     $name = sprintf(__('%1$s (%2$s)'), $name, $item['id']);
                  }
                  $tab[$types[$item['type']]][$item['id']] = $name;
                  $p['option_tooltips'][$types[$item['type']]]['__optgroup_label'] = '';
               }

               break;

         }

      }

      $rand = Dropdown::showFromArray($p['name'], $tab, $p);

      // Mostly yanked and modded from glpi/inc/dropdown.class.php
      // Show "i"
      $comment_id      = Html::cleanId("comment_".$p['name'].$p['rand']);
      $link_id         = Html::cleanId("comment_link_".$p['name'].$p['rand']);
      $options_tooltip = ['contentid' => $comment_id,
         'linkid'    => $link_id,
         'display'   => false];
      $options_tooltip['link']       = self::getSearchURL();

      if (empty($comment))
         $comment = Toolbox::ucfirst(sprintf(__('Show %1$s'), self::getTypeName(2)));

      $output = "&nbsp;".Html::showToolTip($comment, $options_tooltip);

      // Show "+"
      if ($p['addicon']) {
         $output .= "<span class='fa fa-plus-circle pointer' title=\"".__s('Add')."\"
                            onClick=\"".Html::jsGetElementbyID('add_security'.$rand).".dialog('open');\"
                           ><span class='sr-only'>" . __s('Add') . "</span></span>";
         $output .= Ajax::createIframeModalWindow('add_security'.$rand,
            self::getFormURL(),
            ['display' => false]);
      }

      echo $output;
   }

   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id'                 => 'common',
         'name'               => __("Characteristics")
      ];

      $tab[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __("Name"),
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'autocomplete'       => true,
      ];

      $tab[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'id',
         'name'               => __("ID"),
         'massiveaction'      => false,
         'datatype'           => 'number',
      ];

      $tab[] = [
         'id'                 => '3',
         'table'              => $this->getTable(),
         'field'              => 'type',
         'name'               => __("Type", 'dlteams'),
         'searchtype'         => 'equals',
         'massiveaction'      => true,
         'datatype'           => 'specific'
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'content',
         'name'               => __("Description"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __("Comments"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __("Entity"),
         'massiveaction'      => true,
         'datatype'           => 'dropdown',
      ];

      $tab[] = [
         'id'                 => '7',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __("Child entities"),
         'massiveaction'      => false,
         'datatype'           => 'bool',
      ];

      return $tab;
   }

}
