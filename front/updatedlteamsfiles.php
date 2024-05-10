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

$fileid = $_POST['file_id'];
 $refreshToken = PluginDlteamsConfig::gdriveKeys()["refresh_token"];
 $accessToken = PluginDlteamsConfig::refreshAccessToken($refreshToken);
$savePath = GLPI_ROOT . "/files/_dlteams/updates/dlregisterlatest.zip";
$extractFolder = GLPI_ROOT . "/files/_dlteams/updates/dlregisterlatest/";
$download_response = PluginDlteamsConfig::downloadFileFromGoogleDrive($accessToken, $fileid, $savePath);

//extract
/*$zip = new ZipArchive;
if ($zip->open($savePath) === TRUE) {
    $zip->extractTo($extractFolder);
    $zip->close();
    echo  "extrait avec succès";
} else {
    echo  "Impossible d'ouvrir le fichier ZIP.";
}*/

//copy files
$directory_destination = GLPI_ROOT . "/marketplace/dlteams/";
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractFolder));
$files = array();
foreach ($rii as $file) {
    if ($file->isDir()) {
        continue;
    }
$files[] = $file->getPathname();
$message = "..." . substr($file, -40). nl2br("\n");
}
//chmod(GLPI_ROOT . "/marketplace/dlteams/", "")
clearstatcache(); // Efface le cache des résultats de stat() pour assurer des informations à jour
$perms = fileperms(GLPI_ROOT . "/marketplace/dlteams/");

// On récupère seulement les permissions, masquage des autres bits avec 0777
chmod(plugin_dlteams_root, 0777); // ouverture des droits d'écriture
// $permissionOctale = substr(sprintf('%o', $perms), -4);
// chmod(GLPI_ROOT . "/marketplace/dlteams/", 0755);
foreach ($files as $path) {
    var_dump ($file->isDir());
	// chmod($file->isDir(), 0777);
	// chmod("/var/www/dev_dlregister_app/marketplace/dlteams/dlregister/templates/layout", 0777);
	var_dump ($path ." --> ".GLPI_ROOT . "/marketplace/dlteams/".str_replace($extractFolder, "", $path));
	$resp = copy($path, GLPI_ROOT . "/marketplace/dlteams/".str_replace($extractFolder, "", $path));
    /* highlight_string("<?php\n\$data =\n" . var_export($resp, true) . ";\n?>");
    highlight_string("<?php\n\$data =\n" . var_export($path, true) . ";\n?>");
    highlight_string("<?php\n\$data =\n" . var_export(GLPI_ROOT . "/marketplace/dlteams/".str_replace($extractFolder, "", $path), true) . ";\n?>");*/
	// echo ($path . "->" . GLPI_ROOT . "/marketplace/dlteams/".str_replace($extractFolder, "", $path).<br>); 
}
// chmod(GLPI_ROOT . "/marketplace/dlteams/", octdec($permissionOctale));
chmod(plugin_dlteams_root, 0755); // fermeture des droits

$message .= "Mise à jour éfféctué avec succès";
Session::addMessageAfterRedirect($message);

Html::back();