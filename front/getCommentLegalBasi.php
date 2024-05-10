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
 
    $url='';
    // Append the host(domain name, ip) to the URL.   
    $url.= $_SERVER['HTTP_HOST'];   
    
    // Append the requested resource location to the URL   
    //$url.= $_SERVER['REQUEST_URI'];   
	//echo $url; 	
	
	if(strpos($url, "app.dlplace.eu") !== false){
		$db_name="glpi";
		$servername = "localhost";
		$username = "lamine";
		$password = "T0wnX@75zxB#";
	} else if(strpos($url, "dev.dlplace.eu") !== false){
		$db_name="glpi_dev";
		$servername = "localhost";
		$username = "lamine";
		$password = "T0wnX@75zxB#";
	}else if(strpos($url, "dev.dlteams.app") !== false){
		$db_name="glpi_v10";
		$servername = "localhost";
		$username = "lamine";
		$password = "T0wnX@75zxB#";
	}else if(strpos($url, "dlteams.app") !== false){
		$db_name="glpi";
		$servername = "localhost";
		$username = "lamine";
		$password = "T0wnX@75zxB#";
	}else{
		
	}
      
    

		try {
		  $conn = new PDO("mysql:host=$servername;dbname=$db_name", $username, $password);
		  // set the PDO error mode to exception
		  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		 // echo "Connected successfully";
		} catch(PDOException $e) {
		  echo "Connection failed: " . $e->getMessage();
		}


//Session::checkLoginUser();
		$id=$_REQUEST['content'];
		$sql="SELECT comment from glpi_plugin_dlteams_legalbasis WHERE id='$id'";
		if(!$conn->query($sql)) echo "Impossible de se connecter";
		else{
			 foreach ($conn->query($sql) as $row)
			 $data = $row['comment'];
			 
		}

		echo ($data);

		/*global $DB;
		$req=$DB->request(['SELECT' => 'comment', 'FROM' => 'glpi_plugin_genericobject_rgpdconservations', 'WHERE'  => [
							  'id' => 129]
						  ]);
		foreach ($req as $id => $row) {
				$data = $row['comment'];
		}
		
		echo json_encode($data);*/
		
		

?>