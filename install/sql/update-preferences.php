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


 function ExportDAT () {
	global $DB;
	$ table = 'glpi_displaypreferences';
	$glpiRoot=str_replace('\\', '/', GLPI_ROOT);
	$file_pointer = $glpiRoot. "/marketplace/dlteams/install/datas/" . $table . ".dat";
	unlink($file_pointer);
	FolderExport () ; //verify if export folder exist

	$fields_export = [`glpi_displaypreferences`,`itemtype`, `num`, `rank`, `users_id`];
	$exportable_itemtype = ['"PluginDlteamsRecord", "PluginDlteamsConcernedPerson", "PluginDlteamsProcessedData", "PluginDlteamsLegalBasi", "PluginDlteamsStoragePeriod", "PluginDlteamsThirdPartyCategory",
	"PluginDlteamsRightMeasure", "PluginDlteamsPolicieform", "PluginDlteamsDataCatalog", "PluginDlteamsRiskassement", "PluginDlteamsAudit", "PluginDlteamsProtectiveMeasure",
	"PluginDlteamsDataCarrierType", "Appliance", "PluginDlteamsDeliverable"'];

	print_r ($glpiRoot. "/marketplace/dlteams/install/datas/");
	chmod($glpiRoot. "/marketplace/dlteams/install/datas/", 0777);

	$query = "SELECT $fields_export FROM $table WHERE `itemtype` IN " . $in . " INTO OUTFILE '" . $file_pointer . "' CHARACTER SET utf8";
	$DB->queryOrDie($query, $DB->error());
	
	chmod($glpiRoot. "/marketplace/dlteams/install/datas/", 0755);
}
/*

$querys = [
"INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
('PluginDlteamsAuditcategory', 3, 1, 0),
('PluginDlteamsAuditcategory', 4, 2, 0),
('PluginDlteamsAuditcategory', 5, 3, 0) ON DUPLICATE KEY UPDATE `num` = `num`;",
"INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
('PluginDlteamsAudit', 4, 1, 0),
('PluginDlteamsAudit', 5, 3, 0),
('PluginDlteamsAudit', 6, 4, 0),
('PluginDlteamsAudit', 3, 2, 0),
('PluginDlteamsAudit', 119, 5, 0),
('PluginDlteamsAudit', 60, 6, 0) ON DUPLICATE KEY UPDATE `num` = `num`;",
"INSERT INTO `glpi_displaypreferences` (`itemtype`, `num`, `rank`, `users_id`) VALUES
('PluginDlteamsCatalogClassification', 3, 1, 0),
('PluginDlteamsCatalogClassification', 4, 2, 0) ON DUPLICATE KEY UPDATE `num` = `num`;",


];
$i = 1;
global $DB;
foreach ($querys as $query) {
		// echo $query . "<br>";
		$result = $DB->query($query) or die("Erreur". $DB->error());
		if ($DB->error) {
			try {    
				throw new Exception("MySQL error $DB->error <br> Query:<br> $query", $msqli->errno);    
			} catch(Exception $e ) {
			echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
			echo nl2br($e->getTraceAsString());
			}
		}
		echo($i . "..."); $i++;
}
*/

// header("Refresh:0; url=config.form.php");
// Html::back();


