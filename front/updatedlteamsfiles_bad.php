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
/*highlight_string("<?php\n\$data =\n" . var_export($_POST, true) . ";\n?>");*/
//die();

$fileid = $_POST['file_id'];
$refreshToken = PluginDlteamsConfig::gdriveKeys()["refresh_token"];
$accessToken = PluginDlteamsConfig::refreshAccessToken($refreshToken);
$savePath = GLPI_ROOT . "/files/_dlteams/updates/dlregisterlatest.zip";
$extractFolder = GLPI_ROOT . "/files/_dlteams/updates/dlregisterlatest/";
$download_response = PluginDlteamsConfig::downloadFileFromGoogleDrive($accessToken, $fileid, $savePath);


//extract
$zip = new ZipArchive;
if ($zip->open($savePath) === TRUE) {
    $zip->extractTo($extractFolder);
    $zip->close();
    echo  "extrait avec succès";
} else {
    echo  "Impossible d'ouvrir le fichier ZIP.";
}
//die();

//copy files
$directory_destination = GLPI_ROOT . "/marketplace/dlteams/";
$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extractFolder));
$files = array();
foreach ($rii as $file) {
    if ($file->isDir()) {
        continue;
    }
    $files[] = $file->getPathname();
    $message .= "..." . substr($file, -40). nl2br("\n");
}

foreach ($files as $path) {
    $resp = copy($path, GLPI_ROOT . "/marketplace/dlteams/");
    highlight_string("<?php\n\$data =\n" . var_export($resp, true) . ";\n?>");
}
$message .= "Mise à jour éfféctué avec succès";
Session::addMessageAfterRedirect($message);
die();
Html::back();