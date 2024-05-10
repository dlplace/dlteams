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

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class PluginDlteamsProcedure_Variable extends CommonDropdown
{

    static $rightname = 'plugin_dlteams_procedurevariable';
    public $dohistory = true;
    protected $usenotepad = true;

    static function getTypeName($nb = 0)
    {
        return _n("Procedures variable", "Procedures variables", $nb, 'dlteams');
    }

    function showForm($id, $options = [])
    {
        global $CFG_GLPI;

        $this->initForm($id, $options);
        $this->showFormHeader($options);

        echo "<table, th, td width='100%'>";
        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>". " " . "</td>";
        echo "<td width='15%' style='text-align:right' >". __("Name", 'dlteams') . "</td>";
        echo "<td>";
        $name = Html::cleanInputText($this->fields['name']);
        echo "<input type='text' style='width:98%' name='name' required value='" . $name. "'>" . "</td>";
        echo "<td width='15%' style='text-align:right'>". " " . "</td>";
        echo "</tr>" ;

        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>". " " . "</td>";
        echo "<td width='15%' style='text-align:right'>" . __("Content", 'dlteams') . "</td>";
        echo "<td>";
        $content = Html::cleanInputText($this->fields['content']);
        echo "<textarea style='width: 98%;' name='content' rows='3'>" . $content . "</textarea>";
        echo "</td></tr>";

        echo "<tr>";
        echo "<td width='15%' style='text-align:right'>". " " . "</td>";
        echo "<td width='15%' style='text-align:right'>" . __("Comment", 'dlteams') . "</td>";
        echo "<td>";
        $comment = Html::cleanInputText($this->fields['comment']);
        echo "<textarea style='width: 98%;' name='comment' rows='3'>" . $comment . "</textarea>";
        echo "</td></tr>";
        echo "</table>";

        $this->showFormButtons($options);

    }

}
