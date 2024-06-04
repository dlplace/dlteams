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

class PluginDlteamsPhysicalStorage extends CommonDropdown implements
   PluginDlteamsExportableInterface {
   use PluginDlteamsExportable;
   static $rightname = 'plugin_dlteams_physicalstorage';
   public $dohistory = true;
   protected $usenotepad = true;

    static function getMenuName() {

        return _n('Physical storage', 'Physical storages', 2, 'dlteams');
    }

    static function canCreate() {return true;}
    static function canView() {return true;}
    static function canUpdate() {return true;}
    static function canDelete() {return true;}
    static function canPurge() {return true;}
    function canCreateItem() {return true;}
    function canViewItem() {return true;}
    function canUpdateItem() {return true;}
    function canDeleteItem() {return true;}
    function canPurgeItem() {return true;}

    static function getMenuContent() {
        $physicalstorageFormUrl = "/".Plugin::getWebDir('dlteams', false).'/front/physicalstorage.php';
        $menu = [
            'title' => self::getMenuName(),
            'page'  => $physicalstorageFormUrl,
            'icon'  => 'fas fa-file-import',
        ];

        if (PluginDlteamsPhysicalStorage::canCreate()) {
            $menu['links']['add'] = PluginDlteamsPhysicalStorage::getFormURL(false);
        }

        $menu['links']['search'] = PluginDlteamsPhysicalStorage::getSearchURL(false);

        return $menu;
    }

   static function getTypeName($nb = 0) {
      return _n('Physical storage', 'Physical storages', $nb, 'dlteams');
   }

    function showForm($id, $options = [])
   {
      global $CFG_GLPI;
      $this->initForm($id, $options);
      $this->showFormHeader($options);

	  echo "<tr>";
		echo "<td style='text-align:right'>". __("Name", 'dlteams') . "</td>";
		echo "<td>" . "<input type='text' style='width:70%' style='text-align:left' name='name' required value='" . Html::cleanInputText($this->fields['name']). "'>" . "</td>";
		echo "<td width='15%'>". " " . "</td>";
	  echo "</tr>" ;

	  /*echo "<tr>";
		echo "<td width='30%' style='text-align:right'>" . __("Account Management (Directory Service)", 'dlteams') . "</td>";
			echo "<td>";
				PluginDlteamsDatacatalog::dropdown([
				'addicon'  => PluginDlteamsDatacatalog::canCreate(),
				'name' => 'plugin_dlteams_datacatalogs_id',
				'width' => '250px',
				'value' => $this->fields['plugin_dlteams_datacatalogs_id']
			]);
		echo "</td>";
	  echo "</tr>";*/

      echo "<tr>";
		echo "<td style='text-align:right'>". __("Content", 'dlteams') . "</td>";
		echo "<td>" . "<textarea style='width:70%' rows='2' style='text-align:left' name='content' >" . Html::cleanInputText($this->fields['content']) . "</textarea>" . "</td>";
		echo "<td width='15%'>". " " . "</td>";
	  echo "</tr>" ;

       echo "<tr>";
       echo "<td style='text-align:right'>". __("Type de rangement") . "</td>";
       echo "<td>";
       PluginDlteamsStorageUnitType::dropdown([
           'value'  => $this->fields["plugin_dlteams_storageunittypes_id"],
           'entity' => $this->fields["entities_id"],
           'width' => '250px'
       ]);
       echo "</td>";
       echo "<td width='15%'>". " " . "</td>";
       echo "</tr>" ;

      echo "<tr>";
		echo "<td style='text-align:right'>". __("Comment") . "</td>";
		echo "<td>" . "<textarea style='width:70%' rows='2' style='text-align:left' name='comment' >" . Html::cleanInputText($this->fields['comment']) . "</textarea>" . "</td>";
		echo "<td width='15%'>". " " . "</td>";
	  echo "</tr>" ;

       echo "<tr>";
       echo "<td style='text-align:right'>". __("Lieu") . "</td>";
       echo "<td>";
       Location::dropdown(['value'  => $this->fields["locations_id"],
           'entity' => $this->fields["entities_id"],
           'width' => '250px'
       ]);
       echo "</td>";
       echo "<td width='15%'>". " " . "</td>";
       echo "</tr>" ;

      $this->showFormButtons($options);

/*****************************************Section Catalog Content - BEGIN
	  $id = $this->fields['id'];
      if (!$this->can($id, READ)) {
         return false;
      }
      $canedit = $this->can($id, UPDATE);
      $rand = mt_rand(1, mt_getrandmax());
      global $CFG_GLPI;
      global $DB;

      $iterator = $DB->request([
         'SELECT' => [
			'glpi_plugin_dlteams_allitems.id AS linkid',
            'glpi_plugin_dlteams_datacatalogs.id as id',
            'glpi_plugin_dlteams_datacatalogs.name as name',
            'glpi_plugin_dlteams_allitems.comment as comment',
            'glpi_plugin_dlteams_datacatalogs.entities_id as entities_id',

         ],
         'FROM' => 'glpi_plugin_dlteams_allitems',
         'JOIN' => [
            'glpi_plugin_dlteams_datacatalogs' => [
               'FKEY' => [
                  'glpi_plugin_dlteams_allitems' => 'items_id2',
                  'glpi_plugin_dlteams_datacatalogs' => 'id'
               ]
            ]
         ],

         'WHERE' => [
			'glpi_plugin_dlteams_allitems.items_id1' => $this->fields['id'],
			'glpi_plugin_dlteams_allitems.itemtype2' => "PluginDlteamsDatacatalog",
			'glpi_plugin_dlteams_allitems.itemtype1' => "PluginDlteamsAccountKey"
         ],
        'ORDER' => ['name ASC'],
      ], "", true);

	  $number = count($iterator);

      $items_list = [];
      $used = [];
      //var_dump(count($iterator));
     // while ($data = $iterator->next()) {
	  foreach ($iterator as $id => $data){
         $items_list[$data['linkid']] = $data;
         $used[$data['id']] = $data['id'];
      }

	  if ($canedit) {
         echo "<form name='allitemitem_form$rand' id='allitemitem_form$rand' method='post'
         action='" . Toolbox::getItemTypeFormURL(PluginDlteamsAllItem::class) . "'>";
         echo "<input type='hidden' name='itemtype1' value='".$this->getType()."' />";
         echo "<input type='hidden' name='items_id1' value='".$this->getID()."' />";
         echo "<input type='hidden' name='itemtype' value='".PluginDlteamsDatacatalog::getType()."' />";
		 // echo "<input type='hidden' name='comment' value='".$this->fields['comment']."' />";

    echo "<table class='tab_cadre_fixe'>";
    echo "<tr class='tab_bg_2'><th style='text-align:center!important'>" . __("Catalogues accessibles", 'dlteams') . "</th></tr>";
    echo "</table>";

	echo "<table class='tab_cadre_fixe'>";
			echo "<td width='30%' style='text-align:right'>" . __("Data catalog", 'dlteams') . "</td>";
			echo "<td>";
				PluginDlteamsDatacatalog::dropdown([
				'addicon'  => PluginDlteamsDatacatalog::canCreate(),
				'name' => 'items_id',
				'width' => '250px',
				//'value' => $this->fields['plugin_dlteams_datacatalogs_id']
			]);
			echo "</td>";

	  echo "<tr>";
			echo "<td style='text-align:right'>". __("Access rules", 'dlteams') . " " . "</td>";
			$comment = Html::cleanInputText($this->fields['comment']);
			echo "<td>" . "<textarea style='width:70%' rows='1' name='comment' >" . $comment . "</textarea>" . "</td>";
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
            $header_begin   .= "<th width='10'>";
            $header_top     .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsAllItem::class . $rand);
            $header_bottom  .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsAllItem::class . $rand);
            $header_end     .= "</th>";
         }

         $header_end .= "<th width='20%' style='text-align:left'>" . __("Catalog", 'dlteams') . "</th>";
         // $header_end .= "<th width='20%'>" . __("Type", 'dlteams') . "</th>";
         $header_end .= "<th width='80%' style='text-align:left'>" . __("Comment")  . "</th>";
         $header_end .= "</tr>";

         echo $header_begin . $header_top . $header_end;
         foreach ($items_list as $data) {
            if($data['name']){
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
               $name = "<a target='_blank' href=\"" . PluginDlteamsDatacatalog::getFormURLWithID($data['id']) . "\">" . $link . "</a>";

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
	  */
/****************************************Section Catalog Content - END

      // Redacteurs
      /*if ($responsible = $this->fields["users_id_responsible"]);
      // if empty, take legal representative of the entity
      else {
         global $DB;
         $iterator = $DB->request([
            'SELECT' => 'users_id_representative',
            'FROM' => 'glpi_plugin_dlteams_controllerinfos',
            'WHERE' => ['entities_id' => $this->getEntityID()]
         ]);
         $responsible = $iterator->next()['users_id_representative'];
      }

      echo "<tr class='tab_bg_1'>";
      echo "<td>" . __("Process responsible", 'dlteams') . "</td>";
      $randDropdown = mt_rand();
      echo "<td colspan='2'>";
      User::dropdown([
         'name'   => 'users_id_responsible',
         'value'  => $responsible,
         'entity' => $this->fields["entities_id"],
         'right'  => 'all',
         'width'  => "60%",
         'rand'   => $randDropdown
      ]);
      echo "</td></tr>";*/

/*****************************************Section User attribution - BEGIN
	  $randDropdown = mt_rand();
	  $id = $this->fields['id'];
      if (!$this->can($id, READ)) {
         return false;
      }
      $canedit = $this->can($id, UPDATE);
      $rand = mt_rand(1, mt_getrandmax());
      global $CFG_GLPI;
      global $DB;

      $iterator = $DB->request([
         'SELECT' => [
			'glpi_plugin_dlteams_allitems.id AS linkid',
            'glpi_users.id as id',
            'glpi_users.name as name',
			'glpi_users.realname as realname',
            'glpi_plugin_dlteams_allitems.comment as comment',
            'glpi_users.entities_id as entities_id',

         ],
         'FROM' => 'glpi_plugin_dlteams_allitems',
         'JOIN' => [
            'glpi_users' => [
               'FKEY' => [
                  'glpi_plugin_dlteams_allitems' => 'items_id2',
                  'glpi_users' => 'id'
               ]
            ]
         ],

         'WHERE' => [
			'glpi_plugin_dlteams_allitems.items_id1' => $this->fields['id'],
			'glpi_plugin_dlteams_allitems.itemtype2' => "User",
			'glpi_plugin_dlteams_allitems.itemtype1' => "PluginDlteamsAccountKey"
         ],
        'ORDER' => ['name ASC'],
      ], "", true);

	  $number = count($iterator);

      $items_list = [];
      $used = [];
      //var_dump(count($iterator));
     // while ($data = $iterator->next()) {
	  foreach ($iterator as $id => $data){
         $items_list[$data['linkid']] = $data;
         $used[$data['id']] = $data['id'];
      }

	  if ($canedit) {
         echo "<form name='allitemitem_form$rand' id='allitemitem_form$rand' method='post'
         action='" . Toolbox::getItemTypeFormURL(PluginDlteamsAllItem::class) . "'>";
         echo "<input type='hidden' name='itemtype1' value='".$this->getType()."' />";
         echo "<input type='hidden' name='items_id1' value='".$this->getID()."' />";
         echo "<input type='hidden' name='itemtype' value='".User::getType()."' />";
		 // echo "<input type='hidden' name='comment' value='".$this->fields['comment']."' />";

    echo "<table class='tab_cadre_fixe'>";
    echo "<tr class='tab_bg_2'><th style='text-align:center!important'>" . __("Attribution du compte (Utilisateurs)", 'dlteams') . "</th></tr>";
    echo "</table>";

	echo "<table class='tab_cadre_fixe'>";
			echo "<td width='30%' style='text-align:right'>". __("User") . "</td>";
			echo "<td>";
				User::dropdown([
				'name'   => 'items_id',
				'value'  => $this->fields["users_id_responsible"] ?? "", //$responsible,
				'entity' => $this->fields["entities_id"],
				'right'  => 'all',
				'addicon'  => User::canCreate(),
				'width'  => "250px",
				'rand'   => $randDropdown
			]);
			echo "</td>";

		echo "<tr>";
			echo "<td style='text-align:right'>". __("Comment") . " " . "</td>";
			$comment = Html::cleanInputText($this->fields['comment']);
			echo "<td>" . "<textarea style='width:70%' rows='1' name='comment' >" . $comment . "</textarea>" . "</td>";
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
            $header_begin   .= "<th width='10'>";
            $header_top     .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsAllItem::class . $rand);
            $header_bottom  .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsAllItem::class . $rand);
            $header_end     .= "</th>";
         }

         $header_end .= "<th width='20%' style='text-align:left'>" . __("User") . "</th>";
         // $header_end .= "<th width='20%'>" . __("Type", 'dlteams') . "</th>";
         $header_end .= "<th width='80%' style='text-align:left'>" . __("Comment")  . "</th>";
         $header_end .= "</tr>";

         echo $header_begin . $header_top . $header_end;
         foreach ($items_list as $data) {
            if($data['name']){
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
               $name = "<a target='_blank' href=\"" . User::getFormURLWithID($data['id']) . "\">" . $link . "</a>";
			   $realname = "<a target='_blank' href=\"" . User::getFormURLWithID($data['id']) . "\">" . $data['realname'] . "</a>";

               echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'") . ">" . $name . "</td>";
               echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'") . ">" . $realname . "</td>";
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

****************************************Section User Attribution - END **/

/*****************************************Section Group attribution - BEGIN
	  $id = $this->fields['id'];
      if (!$this->can($id, READ)) {
         return false;
      }
      $canedit = $this->can($id, UPDATE);
      $rand = mt_rand(1, mt_getrandmax());
      global $CFG_GLPI;
      global $DB;

      $iterator = $DB->request([
         'SELECT' => [
			'glpi_plugin_dlteams_allitems.id AS linkid',
            'glpi_groups.id as id',
            'glpi_groups.name as name',
            'glpi_plugin_dlteams_allitems.comment as comment',
            'glpi_groups.entities_id as entities_id',

         ],
         'FROM' => 'glpi_plugin_dlteams_allitems',
         'JOIN' => [
            'glpi_groups' => [
               'FKEY' => [
                  'glpi_plugin_dlteams_allitems' => 'items_id2',
                  'glpi_groups' => 'id'
               ]
            ]
         ],

         'WHERE' => [
			'glpi_plugin_dlteams_allitems.items_id1' => $this->fields['id'],
			'glpi_plugin_dlteams_allitems.itemtype2' => "Group",
			'glpi_plugin_dlteams_allitems.itemtype1' => "PluginDlteamsAccountKey"
         ],
        'ORDER' => ['name ASC'],
      ], "", true);

	  $number = count($iterator);

      $items_list = [];
      $used = [];
      //var_dump(count($iterator));
     // while ($data = $iterator->next()) {
	  foreach ($iterator as $id => $data){
         $items_list[$data['linkid']] = $data;
         $used[$data['id']] = $data['id'];
      }

	  if ($canedit) {
         echo "<form name='allitemitem_form$rand' id='allitemitem_form$rand' method='post'
         action='" . Toolbox::getItemTypeFormURL(PluginDlteamsAllItem::class) . "'>";
         echo "<input type='hidden' name='itemtype1' value='".$this->getType()."' />";
         echo "<input type='hidden' name='items_id1' value='".$this->getID()."' />";
         echo "<input type='hidden' name='itemtype' value='".Group::getType()."' />";
		 // echo "<input type='hidden' name='comment' value='".$this->fields['comment']."' />";

    echo "<table class='tab_cadre_fixe'>";
    echo "<tr class='tab_bg_2'><th style='text-align:center!important'>" . __("Attribution du compte (groupes)", 'dlteams') . "</th></tr>";
    echo "</table>";

	echo "<table class='tab_cadre_fixe'>";
			echo "<td style='text-align:right'>". __("Group") . "</td>";
			echo "<td>";
				Group::dropdown([
				'addicon'  => Group::canCreate(),
				'name' => 'items_id',
				'width' => '300px'
			]);
			echo "</td>";

		echo "<tr>";
			echo "<td width='30%' style='text-align:right'>". __("Comment") . " " . "</td>";
			$comment = Html::cleanInputText($this->fields['comment']);
			echo "<td>" . "<textarea style='width:70%' rows='1' name='comment' >" . $comment . "</textarea>" . "</td>";
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
            $header_begin   .= "<th width='10'>";
            $header_top     .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsAllItem::class . $rand);
            $header_bottom  .= Html::getCheckAllAsCheckbox('mass' . PluginDlteamsAllItem::class . $rand);
            $header_end     .= "</th>";
         }

         $header_end .= "<th width='20%' style='text-align:left'>" . __("Group") . "</th>";
         // $header_end .= "<th width='20%'>" . __("Type", 'dlteams') . "</th>";
         $header_end .= "<th width='80%' style='text-align:left'>" . __("Comment")  . "</th>";
         $header_end .= "</tr>";

         echo $header_begin . $header_top . $header_end;
         foreach ($items_list as $data) {
            if($data['name']){
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
               $name = "<a target='_blank' href=\"" . Group::getFormURLWithID($data['id']) . "\">" . $link . "</a>";

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
****************************************Section Group Attribution - END **/

      return true;
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
      /*$rel = new PluginDlteamsRecord_MotifEnvoi();
      $rel->deleteByCriteria(['plugin_dlteams_concernedpersons_id' => $this->fields['id']]);*/
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
         'field'              => 'comment',
         'name'               => __("Comments"),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '4',
         'table'              => 'glpi_entities',
         'field'              => 'completename',
         'name'               => __("Entity"),
         'datatype'           => 'dropdown',
         'massiveaction'      => true,
      ];

      $tab[] = [
         'id'                 => '5',
         'table'              => $this->getTable(),
         'field'              => 'is_recursive',
         'name'               => __("Child entities"),
         'datatype'           => 'bool',
         'massiveaction'      => false,
      ];

	  $tab[] = [
         'id'                 => '6',
         'table'              => $this->getTable(),
         'field'              => 'content',
         'name'               => __("Content", 'dlteams'),
         'datatype'           => 'text',
         'toview'             => true,
         'massiveaction'      => true,
      ];

       $tab[] = [
           'id'                 => '7',
           'table'              => Location::getTable(),
           'field'              => 'completename',
           'name'               => __("Lieu"),
           'datatype'           => 'dropdown',
           'massiveaction'      => true,
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

   public function defineTabs($options = []) {
      $ong = [];
      $ong = array();
      //add main tab for current object
      $this->addDefaultFormTab($ong)
	  ->addStandardTab('PluginDlteamsDataCatalog_Item', $ong, $options)
	  ->addStandardTab('PluginDlteamsPhysicalStorage_Item', $ong, $options)
	  ->addStandardTab('PluginDlteamsProtectiveMeasure_Item', $ong, $options)
	  ->addStandardTab('PluginDlteamsObject_document', $ong, $options)
      ->addStandardTab('ManualLink', $ong, $options)
      ->addStandardTab(Location::class, $ong, $options)
      ->addStandardTab(PluginDlteamsTicket_Item::class, $ong, $options)
      ->addImpactTab($ong, $options)
      ->addStandardTab('Notepad', $ong, $options)
      ->addStandardTab('Log', $ong, $options);
      return $ong;
   }

   function exportToDB($subItems = []) {
      if ($this->isNewItem()) {
         return false;
      }

      $export = $this->fields;
      return $export;
   }

    public static function importToDB(PluginDlteamsLinker $linker, $input = [], $containerId = 0, $subItems = []) {
      $item = new self();
      $originalId = $input['id'];
      unset($input['id']);
	  $input['entities_id']= $_POST['entities_id'];;
	  $input['comment']=str_replace(['\'', '"'], "", $input['comment']);
	  $input['name']=str_replace(['\'', '"'], "", $input['name']);
	  $input['content']=str_replace(['\'', '"'], "", $input['content']);
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
