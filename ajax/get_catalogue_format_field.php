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


// This script is an advanced version of glpi native dropdownAllItem script




include_once('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkCentralAccess();

/** @global array $CFG_GLPI */

/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
if(isset($_POST["id_field"])){
    $datacatalog = new PluginDlteamsDataCatalog();
    $datacatalog->getFromDB($_POST["id_field"]);
    echo "<td class='right'>Format des comptes à créer</td>";
    echo "<td>";
    echo "<input type='text' style='width:200%' maxlength=250 name='name' required value='" . $datacatalog->fields["default_format"] . "'>";
    echo "</td>";
}