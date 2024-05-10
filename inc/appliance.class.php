<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2023 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

class PluginDlteamsAppliance extends CommonDropdown
{
   static $rightname = 'appliance'; //permet la gestion des droits
   public $dohistory = true;
   protected $usenotepad = true;
   static function getTypeName($nb = 0) {
      return _n("Appliance", "Appliances", $nb, 'dlteams');
   } // affiche le titre et le 1er onglet

   // public function __construct() { self::forceTable(Appliance::getTable());}

	// mise à jour de glpi_plugin_dlteams_appliances à partir de glpi_appliances

    public static function getTable($classname = null)
    {
        return Appliance::getTable();
    }

    function showForm($id, $options = []) {
      global $CFG_GLPI;
      $this->initForm($id, $options);
      $this->showFormHeader($options);

      // echo "<form name='allitemitem_form$rand' id='allitemitem_form$rand' method='post' action='" . Toolbox::getItemTypeFormURL(PluginDlteamsAllItem::class) . "'>";
      // echo "<input type='hidden' name='itemtype' value='".$this->getType()."' />";
      // echo "<input type='hidden' name='items_id' value='".$this->getID()."' />";
      // echo "<input type='hidden' name='itemtype1' value='".PluginDlteamsRecord::getType()."' />";
      // echo "<input type='hidden' name='entities_id' value='".$this->getID(Entity::class) ."' />";
      // echo "<input type='hidden' name='comment' value='".$this->fields['comment']."' />";

//        echo "<td>";
      //echo "<tr class='tab_bg_1'><td>".__('Name')."</td>";
      //   echo "<td>";
      // echo "<input name='original_id' value='".Appliance::getID()."' />";
//       $data == 55 ;
//	   Html::showMassiveActionCheckBox(Appliance::class, $data['linkid']);
////	   $link = $data['name'];
//                        if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
//                            $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
//                        }
//	   $name = "<a target='_blank' href=\"" . Appliance::getFormURLWithID($data['id']) . "\">" . $link . "</a>";
//                        echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
//                        echo ">" . $name . "</td>";
//         echo "</td>";
	  //echo "<input name='appliace_id' value='".$this->getID(Appliance::class) ."' />";
	  //    echo "<td>";
	  //echo "<input name='current_id' value='".$this->getID()."' />";
      //   echo "</td>";*/



      echo "<tr class='tab_bg_1'><td>".__('Name')."</td>";
         echo "<td>";

            echo Html::input('name', ['value' => $this->fields['name'], 'size' => 40]);
         echo "</td>";

         echo "<td rowspan='1'>". __('Comments')."</td>";
         echo "<td rowspan='1'> <textarea cols='45' rows='2' name='comment' >".$this->fields["comment"];
         echo "</textarea></td>";
      echo "</tr>\n";

      $this->showFormButtons($options);
      return true;
   }

    public function rawSearchOptions()
    {
        $tab = parent::rawSearchOptions();
		// $tab = Infocom::rawSearchOptionsToAdd("PluginDlteamsAppliance");
		// $tab = PluginDlteamsAppliance::rawSearchOptions();

        $tab[] = [
            'id'            => '4',
            'table'         => self::getTable(),
			// 'table'         => PluginDlteamsAppliance::getTable(),
			// bad //'table'         => self::forceTable(Appliance::getTable()),
            'field'         =>  'comment',
            'name'          =>  __('Comments'),
            'datatype'      =>  'text'
        ];

        /*$tab = array_merge($tab, Location::rawSearchOptionsToAdd());

        $tab[] = [
            'id'            => '5',
            'table'         =>  Appliance_Item::getTable(),
            'field'         => 'items_id',
            'name'               => _n('Associated item', 'Associated items', 2),
            'nosearch'           => true,
            'massiveaction' => false,
            'forcegroupby'  =>  true,
            'additionalfields'   => ['itemtype'],
            'joinparams'    => ['jointype' => 'child']
        ];

        $tab[] = [
            'id'            => '6',
            // 'table'         => User::getTable(),
			'table'         => PluginDlteamsAppliance::getTable(),
            'field'         => 'name',
            'name'          => User::getTypeName(1),
            'datatype'      => 'dropdown'
        ];

        $tab[] = [
            'id'            => '8',
            'table'         => Group::getTable(),
            'field'         => 'completename',
            'name'          => Group::getTypeName(1),
            'condition'     => ['is_itemgroup' => 1],
            'datatype'      => 'dropdown'
        ];

        $tab[] = [
            'id'                 => '14',
            'table'              => $this->getTable(),
            'field'              => 'contact',
            'name'               => __('Alternate username'),
            'datatype'           => 'string',
        ];

        $tab[] = [
            'id'                 => '15',
            'table'              => $this->getTable(),
            'field'              => 'contact_num',
            'name'               => __('Alternate username number'),
            'datatype'           => 'string',
        ];

        $tab[] = [
            'id'            => '23',
            'table'         => 'glpi_manufacturers',
            'field'         => 'name',
            'name'          => Manufacturer::getTypeName(1),
            'datatype'      => 'dropdown'
        ];

        $tab[] = [
            'id'            => '24',
            'table'         => User::getTable(),
            'field'         => 'name',
            'linkfield'     => 'users_id_tech',
            'name'          => __('Technician in charge'),
            'datatype'      => 'dropdown',
            'right'         => 'own_ticket'
        ];

        $tab[] = [
            'id'            => '49',
            'table'         => Group::getTable(),
            'field'         => 'completename',
            'linkfield'     => 'groups_id_tech',
            'name'          => __('Group in charge'),
            'condition'     => ['is_assign' => 1],
            'datatype'      => 'dropdown'
        ];

        $tab[] = [
            'id'            => '9',
            'table'         => self::getTable(),
            'field'         => 'date_mod',
            'name'          => __('Last update'),
            'massiveaction' => false,
            'datatype'      => 'datetime'
        ];

        $tab[] = [
            'id'            => '10',
            'table'         => ApplianceEnvironment::getTable(),
            'field'         => 'name',
            'name'          => __('Environment'),
            'datatype'      => 'dropdown'
        ];

        $tab[] = [
            'id'            => '11',
            'table'         => ApplianceType::getTable(),
            'field'         => 'name',
            'name'          => _n('Type', 'Types', 1),
            'datatype'      => 'dropdown'
        ];

        $tab[] = [
            'id'            => '12',
            'table'         => self::getTable(),
            'field'         => 'serial',
            'name'          => __('Serial number'),
        ];

        $tab[] = [
            'id'            => '13',
            'table'         => self::getTable(),
            'field'         => 'otherserial',
            'name'          => __('Inventory number'),
        ];

        $tab[] = [
            'id'            => '31',
            'table'         => self::getTable(),
            'field'         => 'id',
            'name'          => __('ID'),
            'datatype'      => 'number',
            'massiveaction' => false
        ];

        $tab[] = [
            'id'            => '80',
            'table'         => 'glpi_entities',
            'field'         => 'completename',
            'name'          => Entity::getTypeName(1),
            'datatype'      => 'dropdown'
        ];

        $tab[] = [
            'id'            => '7',
            'table'         => self::getTable(),
            'field'         => 'is_recursive',
            'name'          => __('Child entities'),
            'massiveaction' => false,
            'datatype'      => 'bool'
        ];

        $tab[] = [
            'id'            => '81',
            'table'         => Entity::getTable(),
            'field'         => 'entities_id',
            'name'          => sprintf('%s-%s', Entity::getTypeName(1), __('ID'))
        ];

        $tab[] = [
            'id'                 => '61',
            'table'              => $this->getTable(),
            'field'              => 'is_helpdesk_visible',
            'name'               => __('Associable to a ticket'),
            'datatype'           => 'bool'
        ];

        $tab[] = [
            'id'                 => '32',
            'table'              => 'glpi_states',
            'field'              => 'completename',
            'name'               => __('Status'),
            'datatype'           => 'dropdown',
            'condition'          => ['is_visible_appliance' => 1]
        ];
        $tab = array_merge($tab, Certificate::rawSearchOptionsToAdd());*/
        return $tab;
    }

    /*public static function rawSearchOptionsToAdd(string $itemtype)
    {
        $tab = [];

        $tab[] = [
		         'id'   => 'common',
                 'name' => self::getTypeName(2)
            //'id' => 'appliance',
            //'name' => self::getTypeName(Session::getPluralNumber())
        ];

        $tab[] = [
            'id'                 => '1210',
            'table'              => self::getTable(),
            'field'              => 'name',
            'name'               => __('Name'),
            'forcegroupby'       => true,
            'datatype'           => 'itemlink',
            'itemlink_type'      => 'Appliance',
            'massiveaction'      => false,
            'joinparams'         => [
                'condition'  => ['NEWTABLE.is_deleted' => 0],
                'beforejoin' => [
                    'table'      => Appliance_Item::getTable(),
                    'joinparams' => ['jointype' => 'itemtype_item']
                ]
            ]
        ];

        $tab[] = [
            'id'                 => '1211',
            'table'              => ApplianceType::getTable(),
            'field'              => 'name',
            'name'               => ApplianceType::getTypeName(1),
            'forcegroupby'       => true,
            'massiveaction'      => false,
            'joinparams'         => [
                'beforejoin' => [
                    'table'      => Appliance::getTable(),
                    'joinparams' => [
                        'beforejoin' => [
                            'table'      => Appliance_Item::getTable(),
                            'joinparams' => ['jointype' => 'itemtype_item']
                        ]
                    ]
                ]
            ]
        ];

        $tab[] = [
            'id'                 => '1212',
            'table'              => User::getTable(),
            'field'              => 'name',
            'name'               => User::getTypeName(1),
            'forcegroupby'       => true,
            'massiveaction'      => false,
            'datatype'           => 'dropdown',
            'joinparams'         => [
                'beforejoin'         => [
                    'table'              => self::getTable(),
                    'joinparams'         => [
                        'beforejoin' => [
                            'table'      => Appliance_Item::getTable(),
                            'joinparams' => ['jointype' => 'itemtype_item']
                        ]
                    ]
                ]
            ]
        ];

        $tab[] = [
            'id'                 => '1213',
            'table'              => Group::getTable(),
            'field'              => 'name',
            'name'               => Group::getTypeName(1),
            'forcegroupby'       => true,
            'massiveaction'      => false,
            'datatype'           => 'dropdown',
            'joinparams'         => [
                'beforejoin'         => [
                    'table'              => self::getTable(),
                    'joinparams'         => [
                        'beforejoin' => [
                            'table'      => Appliance_Item::getTable(),
                            'joinparams' => ['jointype' => 'itemtype_item']
                        ]
                    ]
                ]
            ]
        ];

        return $tab;
    }*/


   public function defineTabs($options = [])
   {
      $ong = [];
      $ong = array();
      //add main tab for current object
      $this->addDefaultFormTab($ong);
//      $this->addStandardTab('PluginDlteamsElementsRGPD', $ong, $options);
       $this->addStandardTab(PluginDlteamsTicket_Item::class, $ong, $options);
      $this->addStandardTab('Notepad', $ong, $options);
      $this->addStandardTab('Log', $ong, $options);
      return $ong;
   }


    public static function showMassiveActionsSubForm(MassiveAction $ma)
    {
        switch ($ma->getAction()) {
            case 'copyTo':
                Entity::dropdown([
                    'name' => 'entities_id',
                ]);
                echo '<br /><br />' . Html::submit(_x('button', 'Post'), ['name' => 'massiveaction']);
                return true;
        }
        return parent::showMassiveActionsSubForm($ma);
    }

    static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
    {
        switch ($ma->getAction()) {
            case 'copyTo':
                if ($item->getType() == Appliance::getType()) {
                    $appliance = new PluginDlteamsAppliance();
                    foreach ($ids as $id) {
                        if ($item->getFromDB($id)) {
                            if ($appliance->copy1($ma->POST['entities_id'], $id, $item)) {

                                Session::addMessageAfterRedirect(sprintf(__('Record copied: %s', 'dlteams'), $item->getName()));
                                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                            }
                        } else {
                            // Example of ko count
                            $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
                        }
                    }
                }
                return;
        }
        parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
    }

    public function copy1($entity, $id, $item){
        global $DB;
        $dbu = new DbUtils();
        $name=$item->fields['name'];

        $nb=$dbu->countElementsInTable(static::getTable(), ['name' => addslashes($name), 'entities_id' => $entity]);

        if($nb<=0){
            $DB->request("INSERT INTO ".static::getTable()." (entities_id, is_recursive, date_mod, date_creation, name, comment) SELECT '$entity', is_recursive, date_mod, date_creation, name, comment FROM ".static::getTable()." WHERE id='$id'");
            return true;
        }else{
            return false;
        }
    }
}
