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
global $DB, $message; 
	$message = "Nettoyage des relations". nl2br("\n");
	$objects = ['accountkeys', 'activitycategories', 'auditcategories', 'audits', 'concernedpersons', 'datacarriertypes', 'datacatalogs', 'deliverables', 'legalbasis', 'meansofacces', 'physicalstorages', 'policieforms', 'procedures', 'processeddatas', 'processes', 'protectivemeasures', 'records', 'rightmeasures', 'riskassessments', 'storageperiods',  'thirdpartycategories', 'trainingsessions'];
	$objects2 = ['documents', 'groups', 'contacts', 'links', 'networkequipments_items', 'pdu', 'peripherals', 'phones', 'printers', 'suppliers', 'users'];

	foreach ($objects as $object) {
		$table = "glpi_plugin_dlteams_".$object;
		$table_items = "glpi_plugin_dlteams_".$object."_items";
		//	if (exists $table) {
			$query = "DELETE FROM $table_items WHERE NOT EXISTS (SELECT 1 FROM $table AS t2 WHERE t2.`id` = $table_items.`".$object."_id`)";
			$DB->queryOrDie($query, $DB->error());
			$message.= $table . nl2br("\n"); 
		//	}
	}
	
	$message .= "Relations _items orhpelines nettoyées". nl2br("\n");
	Session::addMessageAfterRedirect($message, false, INFO);
	echo "<script>window.location.href='config.form.php';</script>";// revient sur la page