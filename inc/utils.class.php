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

class PluginDlteamsUtils {

   /**
    * Display list of checkboxes/radio
    * @param array $checked Checked values
    * @param array $choices Array of strings of choices, last one could be : input type='text'
    * @param string $field_name item/value name
    * @param string $type either 'checkbox' or 'radio' (default checkbox)
    * @return string You have to echo the return
    */
   static function displayCheckboxes (array $checked, array $choices, $field_name, $type='checkbox') {
      $out = '';

      // Display list of consent possible
      foreach ($choices as $id => $choice) {

         // for checkbox, each have a different name attribute
         if ($type == 'checkbox')
            $out .= "<input type='$type' id='".$field_name."_$id' name='".$field_name."_$id'";
         // for radio, all have same name attribute
         elseif ($type == 'radio') {
            $out .= "<input type='$type' id='" . $field_name . "_$id' name='$field_name' value='$id'";
            if (isset($checked['checked']) && $checked['checked'] == $id)
               $out .= "checked";
         }

         if (isset($checked[$id]) && $checked[$id]) $out .= "checked";
         $out .= ">&nbsp;<label for='".$field_name."_$id'>  $choice</label><br>";

         // Send 'other' radio id to empty input text if not selected (done in the *.form.php file)
         if ($type == 'radio' && preg_match("/input.*type='text'/", $choice))
            $out .= "<input type='hidden' name='other_id' value='$id'>";
      }

      // Needed if there are multiple call of this in the same page
      $rand = mt_rand(1, mt_getrandmax());

      // Enable/disable "other" input weather checkbox is checked or not
      //
      // I admit it's ~~pretty~~ totaly ugly : js into html into php string (containing php vars)
      // But honestly, it works, enjoy (feels free to git blame)

      if ($type == 'checkbox') {
         $out .= "<script>
            let other_checkbox$rand = document.getElementById('".$field_name."_$id');
            let other_input$rand = document.getElementById('".$field_name."_other');
            //Initial check
            check_check$rand(other_checkbox$rand);
            //Event : change
            other_checkbox".$rand.".onchange = function() { check_check$rand(this);};
            //Toggle & clear input.
            function check_check$rand(checkbox) {
               if (checkbox.checked) {
                   other_input".$rand.".disabled = false;
               } else {
                   other_input".$rand.".disabled = true;
                   other_input".$rand.".value = '';
               }
            }
      </script>";
      }

      elseif ($type == 'radio') {
         $out .= "<script>
            let radio$rand = document.getElementById('$field_name'+'_$id');
            let other_input$rand = document.getElementById('".$field_name."_other');
            //Initial check
            //Event : change
            other_input".$rand.".onfocus = function() {
                radio$rand.checked = true;
            };
      </script>";
      }

      return $out;
   }

    static function slugify($string)
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

   /**
    * Stolen and modified from gli user class :
    * Make a select box with all glpi users where select key = name
    *
    * @param $options array of possible options:
    *    - name             : string / name of the select (default is users_id)
    *    - value
    *    - values           : in case of select[multiple], pass the array of multiple values
    *    - right            : string / limit user who have specific right :
    *                             id -> only current user (default case);
    *                             interface -> central;
    *                             all -> all users;
    *                             specific right like Ticket::READALL, CREATE.... (is array passed one of all passed right is needed)
    *    - comments         : boolean / is the comments displayed near the dropdown (default true)
    *    - entity           : integer or array / restrict to a defined entity or array of entities
    *                          (default -1 : no restriction)
    *    - entity_sons      : boolean / if entity restrict specified auto select its sons
    *                          only available if entity is a single value not an array(default false)
    *    - all              : Nobody or All display for none selected
    *                             all=0 (default) -> Nobody
    *                             all=1 -> All
    *                             all=-1-> nothing
    *    - rand             : integer / already computed rand value
    *    - toupdate         : array / Update a specific item on select change on dropdown
    *                          (need value_fieldname, to_update, url
    *                          (see Ajax::updateItemOnSelectEvent for information)
    *                          and may have moreparams)
    *    - used             : array / Already used items ID: not to display in dropdown (default empty)
    *    - ldap_import
    *    - on_change        : string / value to transmit to "onChange"
    *    - display          : boolean / display or get string (default true)
    *    - width            : specific width needed (default 80%)
    *    - specific_tags    : array of HTML5 tags to add to the field
    *    - url              : url of the ajax php code which should return the json data to show in
    *                         the dropdown (default /ajax/getDropdownUsers.php)
    *    - inactive_deleted : retreive also inactive or deleted users
    *
    * @return integer|string Random value if displayed, string otherwise
    */
   static function select2Dropdown($options = []) {
      global $CFG_GLPI;

      // Default values
      $p = [
         'itemtype'            => '',
         'name'                => 'users_id',
         'value'               => '',
         'values'              => [],
         'right'               => 'id',
         'all'                 => 0,
         'display_emptychoice' => true,
         'placeholder'         => '',
         'on_change'           => '',
         'comments'            => 1,
         'width'               => '80%',
         'entity'              => -1,
         'entity_sons'         => false,
         'used'                => [],
         'ldap_import'         => false,
         'toupdate'            => '',
         'rand'                => mt_rand(),
         'display'             => true,
         '_user_index'         => 0,
         'specific_tags'       => [],
         'url'                 => $CFG_GLPI['root_doc'] . "/ajax/getDropdownValue.php",
         'inactive_deleted'    => 0,
      ];

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $p[$key] = $val;
         }
      }

      // check default value (in case of multiple observers)
      if (is_array($p['value'])) {
         $p['value'] = $p['value'][$p['_user_index']] ?? 0;
      }

      // Check default value for dropdown : need to be a numeric
      if ((strlen($p['value']) == 0) || !is_numeric($p['value'])) {
         $p['value'] = 0;
      }

      $output = '';
      if (!($p['entity'] < 0) && $p['entity_sons']) {
         if (is_array($p['entity'])) {
            $output .= "entity_sons options is not available with array of entity";
         } else {
            $p['entity'] = getSonsOf('glpi_entities', $p['entity']);
         }
      }

      $itemtype = $p['itemtype'];
      $item = new $itemtype();
      $item->getFromDB($p['value']);
      // Make a select box with all glpi users
//      $item = $group->fields['completename'] ?? "";

      $view_users = $itemtype::canView();

      if (!empty($p['value']) && ($p['value'] > 0)) {
         $default = $item->fields;
      } else {
         if ($p['all']) {
            $default = __('All');
         } else {
            $default = Dropdown::EMPTY_VALUE;
         }
      }

      // get multiple values name
      $valuesnames = [];
      if ($p['values']) foreach ($p['values'] as $value) {
         if (!empty($value) && ($value > 0)) {
            $item->getFromDB($value);
            // Try to get the most accurate name : complete name, if doesn't exists, search for the name
            $valuesnames[] = $item->fields['completename'] ?? $item->fields['name'];
         }
      }

      $field_id = Html::cleanId("dropdown_" . $p['name'] . $p['rand']);
      $param    = [
         'itemtype'            => $p['itemtype'],
         'value'               => $p['value'],
         'values'              => $p['values'],
         'valuename'           => $default,
         'valuesnames'         => $valuesnames,
         'width'               => $p['width'],
         'all'                 => $p['all'],
         'display_emptychoice' => $p['display_emptychoice'],
         'placeholder'         => $p['placeholder'],
         'right'               => $p['right'],
         'on_change'           => $p['on_change'],
         'used'                => $p['used'],
         'inactive_deleted'    => $p['inactive_deleted'],
         'entity_restrict'     => (is_array($p['entity']) ? json_encode(array_values($p['entity'])) : $p['entity']),
         'specific_tags'       => $p['specific_tags'],
         '_idor_token'         => Session::getNewIDORToken($p['itemtype']),
      ];

      $output   = Html::jsAjaxDropdown($p['name'], $field_id,
         $p['url'],
         $param);

      // Display comment
      if ($p['comments']) {
         $comment_id = Html::cleanId("comment_".$p['name'].$p['rand']);
         $link_id = Html::cleanId("comment_link_".$p["name"].$p['rand']);
         if (!$view_users) {
            $item->fields["link"] = '';
         } else if (empty($item->fields["link"])) {
//            $item->fields["link"] = $CFG_GLPI['root_doc']."/front/user.php";
            $item->fields["link"] = $item->getSearchURL();
         }

         if (empty($item->fields['comment'])) {
            $item->fields['comment'] = Toolbox::ucfirst(
               sprintf(
                  __('Show %1$s'),
                  $itemtype::getTypeName(Session::getPluralNumber())
               )
            );
         }
         $output .= "&nbsp;".Html::showToolTip($item->fields["comment"],
               ['contentid' => $comment_id,
                  'display'   => false,
                  'link'      => $item->fields["link"],
				  'linktarget'=> '_blank',
                  'linkid'    => $link_id]);

         $paramscomment = [
            'value'    => '__VALUE__',
            'itemtype' => User::getType()
         ];

         if ($view_users) {
            $paramscomment['withlink'] = $link_id;
         }
         $output .= Ajax::updateItemOnSelectEvent($field_id, $comment_id,
            $CFG_GLPI["root_doc"]."/ajax/comments.php",
            $paramscomment, false);
      }

      $output .= Ajax::commonDropdownUpdateItem($p, false);

      if ($p['display']) {
         echo $output;
         return $p['rand'];
      }
      return $output;
   }

   /**
    * Remove accents from a string
    * @param $string string Accentuated string
    * @return string Non accentuated string
    */
   static function normalize($string): string {
      $table = array(
         'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
         'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
         'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
         'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
         'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
         'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
         'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
         'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
      );

      return strtr($string, $table);
   }

   /**
    * Convert (image) file into embeddable URI (example image into single html file)
    * @param $file_path string OS file path
    * @return string URI
    */
   static function dataUri(string $file_path): string {
      $mime = mime_content_type($file_path);
      $content = file_get_contents($file_path);
      $encoded = base64_encode($content);

      return "data:$mime;base64,$encoded";
   }

    public static function itemtypeExceptionList()
    {
        return [
            Cluster::class,
            DeviceBattery::class,
            DeviceCamera::class,
            Item_DeviceCamera_ImageFormat::class,
            Item_DeviceCamera_ImageResolution::class,
            DeviceCase::class,
            DeviceControl::class,
            DeviceDrive::class,
            DeviceFirmware::class,
            DeviceGeneric::class,
            DeviceGraphicCard::class,
            DeviceHardDrive::class,
            DeviceMemory::class,
            DeviceMotherboard::class,
            DeviceNetworkCard::class,
            DevicePci::class,
            DevicePowerSupply::class,
            DeviceProcessor::class,
            DeviceSensor::class,
            DeviceSimcard::class,
            DeviceSoundCard::class,
            Enclosure::class,
            \Glpi\Features\Kanban::class,
            OperatingSystem::class,
            Problem::class,
            Project::class,
            Rack::class,
            \Glpi\Inventory\Asset\RemoteManagement::class,
            SoftwareLicense::class,
            SoftwareVersion::class,
            Ticket::class
        ];
    }
}
