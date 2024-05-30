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

$glpiRoot=str_replace('\\', '/', GLPI_ROOT);
$savePath = GLPI_ROOT . "/files/_dlteams/updates/";
$extractFolder = GLPI_ROOT . "/files/_dlteams/updates/dlregisterlatest/";

if (isset($_FILES['file'])) {
    $fileTmpPath = $_FILES['file']['tmp_name'];
    $fileName = $_FILES['file']['name'];
    $fileSize = $_FILES['file']['size'];
    $fileType = $_FILES['file']['type'];
    $fileNameCmps = explode(".", $fileName);
    $fileExtension = strtolower(end($fileNameCmps));

    $allowedExtensions = array('zip', 'rar', 'tar', 'gz');

    if (in_array($fileExtension, $allowedExtensions)) {

        $dest_path = $savePath . $fileName;

        // Créer le répertoire s'il n'existe pas
        if (!is_dir($savePath)) {
            mkdir($savePath, 0755, true);
        }

        // Déplacer le fichier de l'emplacement temporaire à l'emplacement désiré
        if (move_uploaded_file($fileTmpPath, $dest_path)) {
            echo "Le fichier a été téléchargé avec succès.";
        } else {
            echo "Erreur lors du déplacement du fichier téléchargé.";
        }
    } else {
        echo "Le type de fichier n'est pas autorisé. Seuls les fichiers compressés sont acceptés.";
    }
}
else {
    echo "Il y a eu une erreur lors du téléchargement du fichier.";
}


//$fileid = $_POST['file_id'];
// $refreshToken = PluginDlteamsConfig::gdriveKeys()["refresh_token"];
// $accessToken = PluginDlteamsConfig::refreshAccessToken($refreshToken);

//$download_response = PluginDlteamsConfig::downloadFileFromGoogleDrive($accessToken, $fileid, $savePath);

//extract
$zip = new ZipArchive;
if ($zip->open($dest_path) === TRUE) {
    $zip->extractTo($extractFolder);
    $zip->close();
    echo  "extrait avec succès";
} else {
    echo  "Impossible d'ouvrir le fichier ZIP.";
}

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
clearstatcache();
$perms = fileperms($directory_destination);

// On récupère seulement les permissions, masquage des autres bits avec 0777
chmod(plugin_dlteams_root, 0777); // ouverture des droits d'écriture
foreach ($files as $path) {
//    var_dump ($file->isDir());
//	var_dump ($path ." --> ".$directory_destination.str_replace($extractFolder, "", $path));
	$resp = copy($path, $directory_destination.str_replace($extractFolder, "", $path));
}
chmod(plugin_dlteams_root, 0755); // fermeture des droits

$message .= "Mise à jour éfféctué avec succès";
Session::addMessageAfterRedirect($message);
global $CFG_GLPI;
Html::redirect($CFG_GLPI['url_base'] . "/front/central.php");
