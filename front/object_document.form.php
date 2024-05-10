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


if (isset($_POST['add']) && isset($_POST['plugin_dlteams_records_id'])) {

   $record = new PluginDlteamsRecord();
   $record->check($_POST[PluginDlteamsRecord::getForeignKeyField()], UPDATE);

   global $DB;

   if ($_POST['documents_id'] != 0) {
      //add legal basis
      $item = new PluginDlteamsObject_document();

	  $_POST['items_id']= $_POST['documents_id'];
	  $_POST['itemtype']= 'Document';
      $item->add($_POST);

	  /**add in item document**/
	  $DB->insert(
		'glpi_documents_items', [
			'documents_id'      => $_POST['documents_id'],
			'items_id'  => $_POST['plugin_dlteams_records_id'],
			'itemtype'      => 'PluginDlteamsRecord'
		]
	  );

   }
   }

   if ($_POST['plugin_databases_databases_id'] != 0) {
      $DB->insert(
		'glpi_plugin_dlteams_records_databases', [
			'plugin_dlteams_records_id'      => $_POST['plugin_dlteams_records_id'],
			'plugin_databases_databases_id'  => $_POST['plugin_databases_databases_id']
		]
	 );
	}

	  /**add in accountkey**/
   if ($_POST['plugin_dlteams_accountkey_id'] != 0) {
		$DB->insert(
			'glpi_plugin_dlteams_accountkeys_items', [
				'accountkeys_id'  => $_POST['items_id1'],
				'items_id'  	=> $_POST['items_id'],
				'itemtype'      => $_POST['document'],
				'entities_id'   => $_POST['entities_id']
		 ]
		);
	}

Html::back();
