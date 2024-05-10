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

include("../../../inc/includes.php");


Session::checkLoginUser();
if(isset($_POST['update']) && isset($_POST['plugin_dlteams_records_id'])){
    global $DB;
    $record = new PluginDlteamsRecord();
    $record->check($_POST[PluginDlteamsRecord::getForeignKeyField()], UPDATE);


    if($_POST['profiling']) $profiling_auto = $_POST['profiling_auto'];
    else $profiling_auto = "";

    $DB->updateOrDie(
        'glpi_plugin_dlteams_records',
        [
            'impact_person'   => $_POST['impact_person'],
            'impact_organism'   => $_POST['impact_organism'],
            'specific_security_measures' => $_POST['specific_security_measures']
        ],
        [
            'id' => $_POST['plugin_dlteams_records_id']
        ]
    );
    $record->check($_POST['plugin_dlteams_records_id'], UPDATE);
    $record->update($_POST);
    Html::back();

}
elseif(isset($_POST['add'])){

	global $DB;
	 $result1 = $DB->insert(
				'glpi_plugin_dlteams_protectivemeasures_items', [
					'protectivemeasures_id'  => $_POST['items_id_m'],
					'items_id'  	=> $_POST['items_id1'],
					'itemtype'      => $_POST['itemtype1'],
					'comment'      => $_POST['comment'],
				]
			);

			/*if (!$result1) {
				highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");
				echo "<br/>";
		echo "Error 1: " . $DB->error() . "\n";
		die();
	}*/

	$result2 = $DB->insert(
				'glpi_plugin_dlteams_records_items', [
					'records_id'  => $_POST['items_id1'],
					'items_id'  	=> $_POST['items_id_m'],
					'itemtype'      => $_POST['itemtype'],
					'comment'      => $_POST['comment'],
				]
			);

/*			if (!$result2) {
				highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");
		echo "Error: " . $DB->error() . "\n";
		die();
	}
*/
}

Html::back();
