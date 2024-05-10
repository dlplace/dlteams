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
$plugin = new Plugin();
global $DB;

if ($plugin->isActivated('dlteams')) {
   $config = new PluginDlteamsConfig();
	if (isset($_POST['create_modelrgpd'])) {
		$DB->request("SET @currententity_id := ".Session::getActiveEntity());
		$DB->request("SET @currentuser_id := ".Session::getLoginUserID());
		$DB->runFile(plugin_dlteams_root . "/install/sql/create-modelrgpd.sql");
		Session::addMessageAfterRedirect('Entité "rgpd-model" crée avec succès', true, 0);
		Session::addMessageAfterRedirect('Utilisez le profil "Vue-Modele" pour activer "Voir les données modèles"', true, 0);
		Session::addMessageAfterRedirect('Utilisez le user "admin-rgpd", mot de passe "Mai-2018" pour accéder à l\'entité', true, 0);
		Html::back();
	}
	elseif (isset($_POST['delete_modelrgpd'])) {
		$DB->runFile(plugin_dlteams_root . "/install/sql/delete-modelrgpd.sql");
		Session::addMessageAfterRedirect('Entité "rgpd-model", profil "Referent-RGPD", données modèles : supprimés avec succès', true, 0);
		Html::back();
	}
	elseif (isset($_POST['play_sql'])) {
		error_reporting(E_ALL & ~E_WARNING);
		mysqli_report(MYSQLI_REPORT_OFF);
		$DB->runFile(plugin_dlteams_root . "/install/sql/update-1.0.sql");
		echo ("/install/sql/update-1.0.sql : ok<br>");
		$DB->runFile(plugin_dlteams_root . "/install/sql/update-1.1.sql");
		echo ("/install/sql/update-1.1.sql : ok<br>");
		$DB->runFile(plugin_dlteams_root . "/install/sql/update-1.2.sql");
		echo ("/install/sql/update-1.2.sql : ok<br>");
		$DB->runFile(plugin_dlteams_root . "/install/sql/update-24.sql");
		echo ("/install/sql/update-24.sql : ok<br>");
		Session::addMessageAfterRedirect('update effectué', true, 0);
	}
	elseif (isset($_POST['add'])) {
		$config->check(-1, CREATE, $_POST);
		$config->add($_POST);
		Html::back();
	}
	elseif (isset($_POST['update'])) {
		$config->check($_POST['id'], UPDATE, $_POST);
		$config->update($_POST);
		Html::back();
   }
   else if (isset($_POST['sampledata'])) {
      // $config->check(-1, CREATE, $_POST);
      // $config->installSampleData($_POST);
      Html::back();
   }
   elseif (isset($_POST['export_project'])) {
			try{
			Session::addMessageAfterRedirect('Données "rgpd-model" importées avec succès', true, 0);
			Session::addMessageAfterRedirect('Utilisez "Voir les données modèles" puis "Copier Vers"', true, 0);
            }
            catch (Exception $e){
                Session::addMessageAfterRedirect($e->getMessage(), true, 1);
            }
   }
   elseif (isset($_POST['import_project'])) {
		//$config->check(-1, CREATE, $_POST);
		//$config->MigrateGenericObjectData($_POST);
		//$today = getdate();
		//$minute = SELECT DATE_FORMAT(CURDATE(), "%H:%i");
		//var_dump ($today, $_SESSION['glpiactiveprofile']["id"]); die;

			global $DB;
			$dbu = new DbUtils();
			$id_ori=$_POST['projects_id'];
			$date = date ('Y-m-d H:i:s');
			//$date2 = "2024-03-01 00:01:00";
			//$date3 = substr($date,0,10)." ".substr($date2,11,8);
			//var_dump ($date, $date2, $date3); die;
			$iduser=Session::getLoginUserID();
			$entity=$_POST['entities_id'];
			//we can have only id so we do request in order to get entity and name
			$reqDebut=$DB->request("SELECT * FROM glpi_projects WHERE id = '$id_ori'");
			    foreach ($reqDebut as $id => $row) {
                $entities_ori=$row['entities_id'];
				$name=addslashes(str_replace('"', '', $row['name']));
			}
			$nb=$dbu->countElementsInTable('glpi_projects', ['name' => $name, 'entities_id' => $entity]);
			//var_dump($nb, $id_ori, $entities_ori);
				//if($nb<=0){
				if($nb>=0){
				/**glpi_project**/
					 $DB->request("INSERT INTO glpi_projects (name,priority,entities_id,is_recursive,date,date_mod,users_id,content,comment,is_deleted,date_creation,is_template,template_name) SELECT name,priority,'$entity',is_recursive,'$date','$date','$iduser',content,comment,is_deleted,'$date',is_template,template_name FROM glpi_projects WHERE id='$id_ori'");
					 $req=$DB->request("SELECT id FROM glpi_projects WHERE name = '$name' AND entities_id = '$entity'");
					 foreach ($req as $id => $row) {
						$idProjet=$row['id']; //get id of copied project
						//var_dump($idProjet);
					 }
						/**glpi_projecttasks**/
						$reqprojecttasks=$DB->request("SELECT * FROM glpi_projecttasks WHERE projects_id='$id_ori' AND entities_id='$entities_ori'");
						//var_dump(count($reqprojecttasks)); die ;
							if (count($reqprojecttasks)) {
								foreach ($reqprojecttasks as $id => $row) {
									$valC=$row['id'];
									$nameC=addslashes(str_replace('"', '', $row['name']));
									//check value
									$nb=$dbu->countElementsInTable('glpi_projecttasks', ['name' => $nameC, 'entities_id' => $entity]);
									//var_dump($nb); die;
									if($nb>=0){
										$DB->request("INSERT INTO glpi_projecttasks (name,content,comment,entities_id,is_recursive,projects_id,date_creation,date_mod,plan_start_date,projectstates_id,projecttasktypes_id,users_id,is_template,template_name) SELECT name,content,comment,'$entity',is_recursive,'$idProjet','$date','$date',concat(substr('$date',1,10),substr(plan_start_date,11,9)),projectstates_id,projecttasktypes_id,'$iduser',is_template,template_name FROM glpi_projecttasks WHERE id='$valC'");
										//var_dump("tâches ajoutées"); die; //substr('$date',1,10)." ".substr(plan_start_date,12,8)
									}else{
										//we do nothing
									}
									//check value - glpi_projecttasks_tickets
									//var_dump($valC);
									$reqprojecttasks_tickets=$DB->request("SELECT * FROM glpi_projecttasks_tickets WHERE projecttasks_id='$valC'");
									//var_dump(count($reqprojecttasks_tickets));
										if (count($reqprojecttasks_tickets)) {
											foreach ($reqprojecttasks_tickets as $id => $row) {
												$valD=$row['tickets_id'];
												//var_dump($valD);
												$DB->request("INSERT INTO glpi_projecttasks_tickets (tickets_id,projecttasks_id) SELECT tickets_id,projecttasks_id FROM glpi_projecttasks_tickets WHERE projecttasks_id='$valC'");
												//get id of insert
												$lastID=$DB->request("SELECT * FROM `glpi_projecttasks_tickets` WHERE id IN (SELECT MAX(id) FROM `glpi_projecttasks_tickets` WHERE projecttasks_id='$valC' and tickets_id='$valD')");
												if (count($lastID)) {
													foreach ($lastID as $id => $row) {
													   $IDlast=$row['id'];
													}
												}
												//
												$test=$DB->request("SELECT name FROM glpi_projecttasks WHERE id='$valC' AND entities_id='$entities_ori'");
												foreach ($test as $id => $row) {
													$name1=addslashes(str_replace('"', '', $row['name']));
													$test1=$DB->request("SELECT * FROM glpi_projecttasks WHERE name='$name1' AND entities_id='$entity'");

													if (count($test1)>0) {
														foreach ($test1 as $id => $row) {
															$idprojecttasks=$row['id']; // new copied id
														}
														 $DB->request("UPDATE glpi_projecttasks_tickets set projecttasks_id='$idprojecttasks' WHERE projecttasks_id='$valC' and tickets_id='$valD' AND id='$IDlast'");
													}
												}

												//copy ticket
												$DB->request("INSERT INTO glpi_tickets (entities_id,name,date,date_mod,users_id_lastupdater,status,users_id_recipient,requesttypes_id,content,urgency,impact,priority,itilcategories_id,type,global_validation,date_creation) SELECT '$entity',name,'$date','$date','$iduser',status,'$iduser',requesttypes_id,content,urgency,impact,priority,itilcategories_id,type,global_validation,'$date' FROM glpi_tickets WHERE id='$valD'");

												$test=$DB->request("SELECT name FROM glpi_tickets WHERE id='$valD' AND entities_id='$entities_ori'");
												foreach ($test as $id => $row) {
													$name2=addslashes(str_replace('"', '', $row['name']));
													$test1=$DB->request("SELECT * FROM glpi_tickets WHERE name='$name2' AND entities_id='$entity'");

													if (count($test1)>0) {
														foreach ($test1 as $id => $row) {
															$idtickets=$row['id']; // new copied id
														}
														 $DB->request("UPDATE glpi_projecttasks_tickets set tickets_id='$idtickets' WHERE projecttasks_id='$idprojecttasks' AND tickets_id='$valD' AND id='$IDlast'");
													}
												}

												/**glpi_tickettasks**/
												$reqtickettasks=$DB->request("SELECT * FROM glpi_tickettasks WHERE tickets_id='$valD'");
												//var_dump(count($reqtickettasks));
													if (count($reqtickettasks)) {
														foreach ($reqtickettasks as $id => $row) {
															 $valE=$row['id'];
															 $valContent=addslashes(str_replace('"', '', $row['content']));
															 $valuuid=$row['uuid'];
															 // copy ticket task

															 $nb=$dbu->countElementsInTable('glpi_tickettasks', ['content' => $valContent, 'tickets_id' => $idtickets, 'uuid' => $valuuid]);
															 if($nb<=0){
																  $DB->request("INSERT INTO glpi_tickettasks (tickets_id,taskcategories_id,date,users_id,content,state,users_id_tech,date_mod,date_creation,tasktemplates_id) SELECT '$idtickets',taskcategories_id,'$date','$iduser',content,state,'$iduser','$date','$date',tasktemplates_id FROM glpi_tickettasks WHERE tickets_id='$valD' and uuid='$valuuid'");
															 }else{
																 //we do nothing
															 }
															 //copy ticket task
														}
													}
												/**glpi_tickettasks**/
											}
										}else{
										}
									/**glpi_projecttasks_tickets**/
								}
							}else{
								//we do nothing
							}
					/**glpi_project**/
					Html::back();
					return true;
				}else{
					Html::back();
					return false;

				}

   }
   else {
      Html::header(PluginDlteamsRecord::getTypeName(0), '', "grc", "plugindlteamsmenu");
      $config->showForm(0);
      Html::footer();
   }

}
