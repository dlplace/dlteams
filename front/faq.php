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

 echo "<!DOCTYPE html>";

define('GLPI_ROOT', '../../..');
include_once GLPI_ROOT . '/inc/includes.php';

Session::checkFaqAccess();
Html::requireJs('jstree');
echo Html::css("marketplace/dlteams/plugin.css");


if (Session::getLoginUserID()) {
   Html::helpHeader(__('FAQ'), $_SERVER['PHP_SELF'], $_SESSION["glpiname"]);
} else {
   $_SESSION["glpilanguage"] = $_SESSION['glpilanguage'] ?? $CFG_GLPI['language'];

   Html::simpleHeader(__('FAQ'), [
      __('FAQ') => $CFG_GLPI['root_doc'].'/marketplace/dlteams/front/faq.php'
   ]);

}

$query_string = "";
if (isset($_GET["id"])) {
   $kb = new KnowbaseItem();
   if ($kb->getFromDB($_GET["id"])) {
      $kb->showFull();
   }

   $query_string = "?" . http_build_query($_GET);


} else {
   // Manage forcetab : non standard system (file name <> class name)
   if (isset($_GET['forcetab'])) {
      Session::setActiveTab('Knowbase', $_GET['forcetab']);
      unset($_GET['forcetab']);
   }

   $kb = new Knowbase();
   $kb->display($_GET);
}

echo "<script src='/marketplace/dlteams/js/faq.js'></script>";
echo "<script>
history.replaceState('', document.title,'/faq$query_string');
</script>";
Html::helpFooter();


