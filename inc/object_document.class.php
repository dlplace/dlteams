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

// require_once ('record_basedonnee.class.php');
use GlpiPlugin\dlteams\Exception\ImportFailureException;

class PluginDlteamsObject_document extends CommonDBRelation {
//   static public $itemtype_1 = 'PluginDlteamsRecord';
//   static public $items_id_1 = 'plugin_dlteams_records_id';
   static public $itemtype_2 = 'Document';
//   static public $items_id_2 = 'items_id';

    static function canCreate() {
      return true;
   }
   static function canView() {
      return true;
   }
   static function canUpdate() {
      return true;
   }
   static function canDelete() {
      return true;
   }
   static function canPurge() {
      return true;
   }
   function canCreateItem() {
      return true;
   }
   function canViewItem() {
      return true;
   }
   function canUpdateItem() {
      return true;
   }
   function canDeleteItem() {
      return true;
   }
   function canPurgeItem() {
      return true;
   }

//   static function getTypeName($nb = 0) {
//      return __("Contracts, DB", 'dlteams');
//   }
   /**
    * Export in an array all the data of the current instanciated PluginDlteamsRecord_LegalBasisAct
    * @param boolean $remove_uuid remove the uuid key
    *
    * @return array the array with all data (with sub tables)
    */
/*   public function export($remove_uuid = false) {
      if ($this->isNewItem()) {
         return false;
      }

      $element = $this->fields;
      // remove fk
      return $element;
   }

   public static function import(PluginDlteamsLinker $linker, $input = [], $record_id = 0) {
      $recordFk = PluginDlteamsRecord::getForeignKeyField();
      $input[$recordFk] = $record_id;
      $item = new self();
      $originalId = $input['id'];
      unset($input['id']);
      $itemId = $item->add($input);
      if ($itemId === false) {
         $typeName = strtolower(self::getTypeName());
         throw new ImportFailureException(sprintf(__('failed to copy the %1$s record', 'dlteams'), $input['name']));
      }
      $linker->addObject($originalId, $item);
      return $itemId;
   }*/

/* fonction de comptage de l'onglet ????
   static function countForItem(CommonDBTM $item) {
      $dbu = new DbUtils();
      return $dbu->countElementsInTable(static::getTable(), ['items_id1' => $item->getID(), 'itemtype1' => $item->getType()])
      + $dbu->countElementsInTable(static::getTable(), ['items_id2' => $item->getID(), 'itemtype2' => $item->getType()]);
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($item->getType()) {
         default :
            static::showTab($item, $withtemplate);
            break;
      }
      return true;
   }
*/
	// fonction d'apparition et de comptage de l'onglet Documents
	function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (!$item->canView()) {
         return false;
      }
      switch ($item->getType()) {
         //case PluginDlteamsRecord::class :
         //   $nbitem = PluginDlteamsAllItem::countSpecificItems($item, [Document::getType(), /*PluginDlteamsDatabase::getType()*/ PluginDatabasesDatabase::getType()]);
         //   return self::createTabEntry(PluginDlteamsRecord_Element::getTypeName($nbitem), $nbitem);
         case PluginDlteamsConcernedPerson::class :
			$nbitem = countElementsInTable('glpi_documents_items', ['itemtype' => 'PluginDlteamsConcernedPerson', 'items_id' => $item->fields['id']]);
			return self::createTabEntry(Document::getTypeName(Session::getPluralNumber()), $nbitem);
         case PluginDlteamsPolicieForm::class :
			$nbitem = countElementsInTable('glpi_documents_items', ['itemtype' => 'PluginDlteamsPolicieForm', 'items_id' => $item->fields['id']]);
			return self::createTabEntry(Document::getTypeName(Session::getPluralNumber()), $nbitem);
         case PluginDlteamsStorageperiod::class :
			$nbitem = countElementsInTable('glpi_documents_items', ['itemtype' => 'PluginDlteamsStorageperiod', 'items_id' => $item->fields['id']]);
			return self::createTabEntry(Document::getTypeName(Session::getPluralNumber()), $nbitem);
         case PluginDlteamsDatabase::class :
			$nbitem = countElementsInTable('glpi_documents_items', ['itemtype' => 'PluginDlteamsDatabase', 'items_id' => $item->fields['id']]);
			return self::createTabEntry(Document::getTypeName(Session::getPluralNumber()), $nbitem);
         case PluginDlteamsDataCatalog::class :
			$nbitem = countElementsInTable('glpi_documents_items', ['itemtype' => 'PluginDlteamsDataCatalog', 'items_id' => $item->fields['id']]);
			return self::createTabEntry(Document::getTypeName(Session::getPluralNumber()), $nbitem);
         case PluginDlteamsAccountKey::class :
			$nbitem = countElementsInTable('glpi_documents_items', ['itemtype' => 'PluginDlteamsAccountKey', 'items_id' => $item->fields['id']]);
			return self::createTabEntry(Document::getTypeName(Session::getPluralNumber()), $nbitem);
          case Ticket::class :
              $nbitem = countElementsInTable('glpi_documents_items', ['itemtype' => 'Ticket', 'items_id' => $item->fields['id']]);
              return self::createTabEntry(Document::getTypeName(Session::getPluralNumber()), $nbitem);
         default:
			$nbitem = countElementsInTable('glpi_documents_items', ['itemtype' => $item->getType(), 'items_id' => $item->fields['id']]);
			return self::createTabEntry(Document::getTypeName(Session::getPluralNumber()), $nbitem);
       }
      return '';
   }

/*    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case 'Document':
                switch ($tabnum) {
                    case 1:
                        self::showForDocument($item);
                        break;
                    case 2:
                        self::showForItem($item, $withtemplate);
                        break;
                }
                return true;
            default:
                self::showForitem($item, $withtemplate);
        }
    }*/

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
		self::showForitem($item, $withtemplate);
    }

	public static function getMassiveActionsForItemtype(
        array &$actions,
        $itemtype,
        $is_deleted = 0,
        CommonDBTM $checkitem = null
    ) {
        global $CFG_GLPI;


                $action_prefix                    = 'Contract_Item' . MassiveAction::CLASS_ACTION_SEPARATOR;
                $actions[$action_prefix . 'add']    = "<i class='fa-fw " . self::getIcon() . "'></i>" .
                                                _x('button', 'Add a contract');
                $actions[$action_prefix . 'remove'] = _x('button', 'Remove a contract');

    }


   static function showForItem(CommonDBTM $item, $withtemplate = 0) {
      $id = $item->fields['id'];
      /*if (!$item->can($id, READ)) {
         return false;
      }*/
	  $canedit = $item->can($id, UPDATE); // canedit booleen = true
      $rand = mt_rand(1, mt_getrandmax());
      global $DB;

      //$iterator = $DB->request(PluginDlteamsAllItem::getRequestForItems($item,static::$itemtype_2,['id','name','filename','link','comment']));
	  $iterator = $DB->request(self::getRequest($item));
      $number = count($iterator); // $number est le nombre de ligne à afficher (=nombre de documents reliés)
      $items_list = [];
      $used = [];

      while ($data = $iterator->next()) {
         $items_list[$data['linkid']] = $data;
         $used[$data['id']] = $data['id'];
      }

      if ($canedit) {
         echo "<form name='ticketitem_form$rand' id='ticketitem_form$rand' method='post'
            action='" . Toolbox::getItemTypeFormURL(PluginDlteamsAllItem::class) . "'>";
            echo "<input type='hidden' name='itemtype1' value='".$item->getType()."' />";
            echo "<input type='hidden' name='items_id1' value='".$item->getID()."' />";
            echo "<input type='hidden' name='itemtype' value='".Document::getType()."' />";
			echo "<input type='hidden' name='entities_id' value='".$item->fields['entities_id']."' />";

			$title = "Documents related to this object";
			$entitled = "Add documents and examples or comment the relationship";
/* il faudrait que la traduction soit fonction de l'objet pour adater la traduction de l'onglet Documents
les traductions seront fonction de l'objet : alors $title = concatener(%classobjet,"_title"), $entitled = concatener (%classobjet; "_entitled")
	on remplace echo "<tr class='tab_bg_2'><th>" . __("Add Documents", 'dlteams') .
	par 		echo "<tr class='tab_bg_2'><th>" . __($title, 'dlteams') .
    on remplace echo "<tr class='tab_bg_1'><td class='left' width='40%'>". __("Documents in which data may be stored", 'dlteams');
	par 	    echo "<tr class='tab_bg_1'><td class='left' width='40%'>". __($entitled, 'dlteams');
Dans le fichier PO, il faudra ajouter les lignes record_element_title & record_element_entitled*/
//if class = "record" then $title = "record Add Documents" else $title = "not_record Add Documents" ;
         echo "<table class='tab_cadre_fixe'>";
			echo "<tr class='tab_bg_2'><th>" . __($title, 'dlteams') . "<br><i style='font-weight: normal'>" . "</i></th>";
			echo "<th colspan='2'></th></tr>";
//if class <> "record" then $entitled = "record Documents in which data may be stored" else $entitled = "not_record Documents in which data may be stored" ;
			echo "<tr class='tab_bg_1'><td class='left' width='40%'>". __($entitled, 'dlteams');
			echo "</td><td width='40%' class='left'>";
				Dropdown::show(Document::class,[
				'addicon'  => Document::canCreate(),
				'name' => 'items_id',
				'used' => $used
			]);
			echo "</td><td width='20%' class='left'><input for='ticketitem_form$rand' type='submit' name='add' value=\"" . _sx('button', 'Add') . "\" class='submit'>";
			echo "</td></tr>";
		 echo "</table>";
         Html::closeForm();
      }

      if ($iterator) {
		 echo "<div class='spaced'>";
         if ($canedit && $number) {
            Html::openMassiveActionsForm('mass' . Document_Item::class . $rand);
            $massive_action_params = ['container' => 'mass' . Document_Item::class . $rand,
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
            $header_top     .= Html::getCheckAllAsCheckbox('mass' . Document_Item::class . $rand);
            $header_bottom  .= Html::getCheckAllAsCheckbox('mass' . Document_Item::class . $rand);
            $header_end     .= "</th>";
         }

         $header_end .= "<th>" . __("Name") . "</th>";
         $header_end .= "<th>" . __("File link") . "</th>";
         $header_end .= "<th>" . __("Website") . "</th>";
		 $header_end .= "<th>" . __("Comment") . "</th>";
         $header_end .= "</tr>";

         echo $header_begin . $header_top . $header_end;

        //foreach ($items_list as $data) {
		// var_dump ($iterator);
		foreach ($iterator as $data) {
            if ($data['name']) {
               echo "<tr class='tab_bg_1'>";

               if ($canedit && $number) {
                  echo "<td width='10'>";
                  Html::showMassiveActionCheckBox(Document_Item::class, $data['linkid']);
                  echo "</td>";
               }
               $link = $data['name'];
               if ($_SESSION['glpiis_ids_visible'] || empty($data['name'])) {
                  $link = sprintf(__("%1\$s (%2\$s)"), $link, $data['id']);
               }

               $name = "<a target='_blank' href=\"" . static::$itemtype_2::getFormURLWithID($data['id']) . "\">" . $link . "</a>";
               echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
               echo ">" . $name . "</td>";

               echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
               echo ">" ;
               if($data['filename']){
                  echo "<a href='../../../front/document.send.php?docid=".$data['id']."' target='_blank'>" . "voir" . "</a>";
               } else {
                  echo "---";
               }
               echo"</td>";

               echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'") . ">" ;
               if($data['link']){
                  echo "<a href='".$data['link']."' target='_blank'>" . "voir" . "</a>";
               } else {
                  echo "---";
               }
               echo"</td>";

               echo "<td class='left" . (isset($data['is_deleted']) && $data['is_deleted'] ? " tab_bg_2_2'" : "'");
               echo " width='40%'>" ;
               if ($data['comment']) {
                  echo $data['comment'];
               } else {
                  echo "---";
               }
               echo "</td>";

               echo "</tr>";
            }
         }


         if ($iterator->count() > 10) {
            echo $header_begin . $header_bottom . $header_end;
         }
         echo "</table>";

         if ($canedit && $number>10) {
            $massive_action_params['ontop'] = false;
            Html::showMassiveActions($massive_action_params);
            Html::closeForm();
         }

         echo "</div>";
      }

   }

   /* Execute massive action for dlteams Plugin
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids)
   {

      switch ($ma->getAction()) {
         case 'copyTo':
            if ($item->getType() == 'PluginDlteamsRecord') {
               /** @var PluginDlteamsRecord $item */
               foreach ($ids as $id) {
                  if ($item->getFromDB($id)) {
                     if ($item->copy($ma->POST['entities_id'], $id, $item)) {
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


   function rawSearchOptions() {

      $tab = [];

      $tab[] = [
         'id' => '31',
         'table' => Document::getTable(),
         'field' => 'name',
         'name' => __("Nom du document"),
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
      ];

	  $tab[] = [
         'id' => '32',
         'table' => Document::getTable(),
         'field' => 'comment',
         'name' => __("Commentaire"),
         'forcegroupby' => true,
         'massiveaction' => true,
         'datatype' => 'text',
         'searchtype' => ['equals', 'notequals'],
         'joinparams' => [
            'beforejoin' => [
               'table' => self::getTable(),
               'joinparams' => [
                  'jointype' => 'child'
               ]
            ]
         ]
      ];

      return $tab;
   }

   public function cleanDBonPurge()
    {
        $this->deleteChildrenAndRelationsFromDb(
            [
                Appliance_Item_Relation::class,
            ]
        );
    }


   static function getRequest($record) {
      return [
         'SELECT' => [
            'glpi_documents_items.id AS linkid',
            'glpi_documents.id AS id',
            'glpi_documents.name AS name',
			'glpi_documents.filename AS filename',
			'glpi_documents.link AS link',
            'glpi_documents.comment AS comment',

         ],
         'FROM' => 'glpi_documents_items',
         'JOIN' => [
            'glpi_documents' => [
               'FKEY' => [
                  'glpi_documents_items' => "documents_id",
                  'glpi_documents' => "id",
               ]
         ],
         ],

         'ORDER' => [
            'name ASC'
			//'contenu ASC'
         ],
         'WHERE' => [
            'glpi_documents_items.items_id' => $record->fields['id'],
			'glpi_documents_items.itemtype' => $record->getType()
         ]
      ];
   }
}
