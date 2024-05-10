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

class PluginDlteamsPersonalDataCategory extends CommonTreeDropdown implements PluginDlteamsExportableInterface {

   use PluginDlteamsExportable;
   
   static $rightname = 'plugin_dlteams_personaldatacategory';

   public $dohistory = true;

   public $is_recursive = true;

   static function getTypeName($nb = 0) {

      return _n("Personal Data Category", "Personal Data Categories", $nb, 'dlteams');
   }

   public function getAdditionalFields() {

      return [
         [
            'name' => $this->getForeignKeyField(),
            'label' => __("As child of"),
            'type' => 'parent',
            'list' => false
         ],
         [
            'name' => 'is_special_category',
            'label' => __("Special category", 'dlteams'),
            'type' => 'bool',
            'list' => true
         ]
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

   function post_addItem() {

      if (PluginDlteamsConfig::getConfig('system', 'keep_is_special_category_strict')) {
         self::update_special_category($this);
      }
   }

   function post_updateItem($history = 1) {

      if (PluginDlteamsConfig::getConfig('system', 'keep_is_special_category_strict')) {
         self::update_special_category($this);
      }
   }

   function any_son_is_special_category($item) {

      $id = 0;
      if (isset($item->input['id'])) {
         $id = $item->input['id'];
      } else if (isset($item->fields['id'])) {
         $id = $item->fields['id'];

      }

      if (!$id) {

         return false;
      }

      $sons = getSonsOf($this->getTable(), $id);
      array_shift($sons);
      if (!count($sons)) {
         return false;
      }

      $pdc = new PluginDlteamsPersonalDataCategory();
      $result = $pdc->find(['is_special_category' => 1, 'id' => $sons]);

      return count($result) > 0;
   }

   function update_special_category($item) {

      global $DB;

      if ($item->input['is_special_category'] == '1') {

         $id = 0;
         if (isset($item->input['id'])) {
            $id = $item->input['id'];
         } else if (isset($item->fields['id'])) {
            $id = $item->fields['id'];
         }

         $table = getAncestorsOf($this->getTable(), $id);
         if (count($table)) {
            $DB->update($this->getTable(), ['is_special_category' => 1], ['id' => $table]);
         }

      } else if ($item->input['is_special_category'] == '0') {

         if (self::any_son_is_special_category($item)) {
            $DB->update($this->getTable(), ['is_special_category' => 1], ['id' => $item->fields['plugin_dlteams_personaldatacategories_id']]);
            $DB->update($this->getTable(), ['is_special_category' => 1], ['id' => $item->fields['id']]);
         }
      }
   }

   function cleanDBonPurge() {

      $rel = new PluginDlteamsRecord_PersonalDataCategory();
      $rel->deleteByCriteria(['plugin_dlteams_personaldatacategories_id' => $this->fields['id']]);

   }

   static function dropdownLimitLevel($options = []) {

      global $DB;

      $p = [
         'name'             => 'plugin_dlteams_personaldatacategories_id',
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
               `glpi_plugin_dlteams_personaldatacategories`.`id`,
               `glpi_plugin_dlteams_personaldatacategories`.`name`,
               `glpi_plugin_dlteams_personaldatacategories`.`entities_id`,
               `glpi_entities`.`completename`
            FROM
               `glpi_plugin_dlteams_personaldatacategories` 
            LEFT JOIN
               `glpi_entities` ON (`glpi_plugin_dlteams_personaldatacategories`.`entities_id` = `glpi_entities`.`id`)
            WHERE
               (
                  (`glpi_plugin_dlteams_personaldatacategories`.`is_recursive` = 1 AND
                   `glpi_plugin_dlteams_personaldatacategories`.`entities_id` IN (' . implode(",", $entities) . ')
                  ) OR (
                   `glpi_plugin_dlteams_personaldatacategories`.`entities_id` = ' . $p['entity'] . '
                  )
               ) AND (
                  `glpi_plugin_dlteams_personaldatacategories`.`level` = 1
               )
            ORDER BY
               FIELD(`glpi_plugin_dlteams_personaldatacategories`.`entities_id`, 4) DESC';

         $result = $DB->request($query);

         $cur_name = '';
         while ($item = $result->next()) {
            if ($cur_name != $item['completename']) {
               $cur_name = $item['completename'];
            }

            $name = $item['name'];
            if ($_SESSION['glpiis_ids_visible'] || empty($item['name'])) {
               $name = sprintf(__('%1$s (%2$s)'), $name, $item['id']);
            }
            $tab[$cur_name][$item['id']] = $name;
            $p['option_tooltips'][$cur_name]['__optgroup_label'] = '';
         }
      }

      return Dropdown::showFromArray($p['name'], $tab, $p);
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
         'field'              => 'completename',
         'name'               => __("Complete name"),
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
         'field'              => 'is_special_category',
         'name'               => __("Special category", 'dlteams'),
         'searchtype'         => 'equals',
         'massiveaction'      => true,
         'datatype'           => 'bool',
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => $this->getTable(),
         'field'              => 'comment',
         'name'               => __("Comments"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __("Entity"),
         'massiveaction'      => true,
         'datatype'           => 'dropdown',
      ];

      $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __("Child entities"),
         'massiveaction'      => false,
         'datatype'           => 'bool',
      ];

      return $tab;
   }
}

