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
global $DLTEAMS_FILES_TO_RENAME;

$glpi_versions = ["10.0.14"];

$message = "Remplacement des fichiers fork et white label GLPI<->DLTEAMS". nl2br("\n");
if (isset($_POST["fork_on"])) {
    $directory = GLPI_ROOT . "/marketplace/dlteams/install/fork/" . "10.0.14" . "/fork_dlteams";
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $files = array();
    foreach ($rii as $file) {
        if ($file->isDir()) {
            continue;
        }
        $files[] = $file->getPathname();
		$message .= "..." . substr($file, -40). nl2br("\n");
    }

    foreach ($files as $path) {
        $resp = copy($path, GLPI_ROOT . str_replace($directory, "", $path));
    }
    $message .= "Fork activé avec succès";
}

if (isset($_POST["fork_off"])) {
    $directory = GLPI_ROOT . "/marketplace/dlteams/install/fork/" . "10.0.14" . "/origin_glpi";
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $files = array();
    foreach ($rii as $file) {
        if ($file->isDir()) {
            continue;
        }
        $files[] = $file->getPathname();
		$message .= "..." . substr($file, -40). nl2br("\n");
    }

    foreach ($files as $path) {
        copy($path, GLPI_ROOT . str_replace($directory, "", $path));
    }
    $message .= "Fork désactivé avec succès";
}

if (isset($_POST["whitelabel_on"])) {
    $directory = GLPI_ROOT . "/marketplace/dlteams/install/whitelabel/" . "10.0.14" . "/dlteams";
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $files = array();
    foreach ($rii as $file) {
        if ($file->isDir()) {
            continue;
        }
        $files[] = $file->getPathname();
		$message .= "..." . substr($file, -40). nl2br("\n");
    }

    foreach ($files as $path) {
        $resp = copy($path, GLPI_ROOT . str_replace($directory, "", $path));
		/* highlight_string("<?php\n\$data =\n" . var_export(sprintf("fichier %s => %s : %s", $path, GLPI_ROOT . str_replace($directory, "", $path), $resp), true) . ";\n?>"); */
    }
    $message .= "Whitelabel activé avec succès";
}

if (isset($_POST["whitelabel_off"])) {
    $directory = GLPI_ROOT . "/marketplace/dlteams/install/whitelabel/" . "10.0.14" . "/origin_glpi";
    $rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
    $files = array();
    foreach ($rii as $file) {
        if ($file->isDir()) {
            continue;
        }
        $files[] = $file->getPathname();
		$message .= "..." . substr($file, -40). nl2br("\n");
    }

    foreach ($files as $path) {
        copy($path, GLPI_ROOT . str_replace($directory, "", $path));
    }
    $message .= "Whitelabel enlevé avec succès";
}

Session::addMessageAfterRedirect($message);
Html::back();