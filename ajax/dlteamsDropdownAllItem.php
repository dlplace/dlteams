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


// This script is an advanced version of glpi native dropdownAllItem script




include_once('../../../inc/includes.php');

header("Content-Type: text/html; charset=UTF-8");
Html::header_nocache();

Session::checkCentralAccess();

/** @global array $CFG_GLPI */

// Make a select box
if ($_POST["idtable"] && class_exists($_POST["idtable"])) {
    // Link to user for search only > normal users
    $link = "getDropdownValue.php";

    if ($_POST["idtable"] == 'User') {
        $link = "getDropdownUsers.php";
    }

    $rand = $_POST['rand'] ?? mt_rand();

    $field_id = Html::cleanId("dropdown_" . $_POST["name"] . $rand);

    $p        = [
        'value'               => 0,
        'valuename'           => Dropdown::EMPTY_VALUE,
        'itemtype'            => $_POST["idtable"],
        'display_emptychoice' => true,
        'displaywith'         => ['otherserial', 'serial'],
        '_idor_token'         => Session::getNewIDORToken($_POST["idtable"]),
    ];
    if (isset($_POST['value'])) {
        $p['value'] = $_POST['value'];
    }
    if (isset($_POST['entity_restrict'])) {
        $p['entity_restrict'] = $_POST['entity_restrict'];
    }
    if (isset($_POST['condition'])) {
        $p['condition'] = $_POST['condition'];
    }
    if (isset($_POST['used'])) {
        $_POST['used'] = Toolbox::jsonDecode($_POST['used'], true);
    }
    if (isset($_POST['used'][$_POST['idtable']])) {
        $p['used'] = $_POST['used'][$_POST['idtable']];
    }
    if (isset($_POST['width'])) {
        $p['width'] = $_POST['width'];
    }

//    var_dump("uu");
    $original_width  = '';
    echo "<div class='btn-group btn-group-sm' role='group'
                style='width: {$original_width}'>";



    echo  Html::jsAjaxDropdown(
        $_POST["name"],
        $field_id,
        $CFG_GLPI['root_doc'] . "/ajax/" . $link,
        $p
    );

//    new


    $add_item_icon="";
    $icon_array = [];
    $item = getItemForItemtype($_POST["idtable"]);
    $add_item_icon .= '<div class="btn btn-outline-secondary"
                           title="' . __s('Add') . '" data-bs-toggle="modal" data-bs-target="#add_' . $field_id . '">';
    $add_item_icon .= Ajax::createIframeModalWindow('add_' . $field_id, $item->getFormURL(), ['display' => false]);
    $add_item_icon .= "<span data-bs-toggle='tooltip'>
              <i class='fa-fw ti ti-plus'></i>
              <span class='sr-only'>" . __s('Add') . "</span>
                </span>";
    $add_item_icon .= '</div>';

    $params = [];
    $params['rand'] = mt_rand();
    $params['name'] = mt_rand();
    $params['value'] = '';

    // Display comment
    $icon_array = [];
//    if ($params['comments']) {
        $comment_id      = Html::cleanId("comment_" . $params['name'] . $params['rand']);
        $link_id         = Html::cleanId("comment_link_" . $params['name'] . $params['rand']);
        $kblink_id       = Html::cleanId("kb_link_" . $params['name'] . $params['rand']);
        $breadcrumb_id   = Html::cleanId("dc_breadcrumb_" . $params['name'] . $params['rand']);
        $options_tooltip = ['contentid' => $comment_id,
            'linkid'    => $link_id,
            'display'   => false
        ];

        if ($item->canView()) {
            if (
                $params['value']
                && $item->getFromDB($params['value'])
                && $item->canViewItem()
            ) {
                $options_tooltip['link']       = $item->getLinkURL();
            } else {
                $options_tooltip['link']       = $item->getSearchURL();
            }
        } else {
            $options_tooltip['awesome-class'] = 'btn btn-outline-secondary fa-info';
        }

        if (empty($comment)) {
            $comment = Toolbox::ucfirst(
                sprintf(
                    __('Show %1$s'),
                    $item::getTypeName(Session::getPluralNumber())
                )
            );
        }

        $paramscomment = [];
        if ($item->canView()) {
            $paramscomment['withlink'] = $link_id;
        }

        // Comment icon
        $comment_icon = Ajax::updateItemOnSelectEvent(
            $field_id,
            $comment_id,
            $CFG_GLPI["root_doc"] . "/ajax/comments.php",
            $paramscomment,
            false
        );
        $options_tooltip['link_class'] = 'btn btn-outline-secondary';
        $comment_icon .= Html::showToolTip($comment, $options_tooltip);
        $icon_array[] = $comment_icon;

        // Add icon
        if (
            ($item instanceof CommonDropdown)
            && $item->canCreate()
            && !isset($_REQUEST['_in_modal'])
            && isset($params['addicon']) && $params['addicon']
        ) {
            $icon_array[] = $add_item_icon;
        }


//    }



    $icon_array[] = $add_item_icon;

    $icons = implode('', $icon_array);

    echo $icons;
    echo "</div>";

    // end new

    if (!empty($_POST['showItemSpecificity'])) {
        $params = ['items_id' => '__VALUE__',
            'itemtype' => $_POST["idtable"]
        ];
        if (isset($_POST['entity_restrict'])) {
            $params['entity_restrict'] = $_POST['entity_restrict'];
        }

        Ajax::updateItemOnSelectEvent(
            $field_id,
            "showItemSpecificity_" . $_POST["name"] . "$rand",
            $_POST['showItemSpecificity'],
            $params
        );

        echo "<br><span id='showItemSpecificity_" . $_POST["name"] . "$rand'>&nbsp;</span>\n";
    }
}
