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

$client_id = '768f4fab-805c-463a-8bdc-28d37a1c57ee';
$client_secret = '4yM8Q~46Ko-5Sn-pCHiYf3~IZumEE3gIvnc2zbv7';
$redirect_uri = 'https://dev.dlteams.app/marketplace/dlteams/front/ssologincallback.form.php';
$token_url = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $token_params = [
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'code' => $code,
        'redirect_uri' => $redirect_uri,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init($token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

    $response = curl_exec($ch);
    curl_close($ch);

    $token_response = json_decode($response, true);



    if (isset($token_response['access_token'])) {
        $_SESSION['access_token'] = $token_response['access_token'];


        $access_token = $_SESSION['access_token'];
        $user_info_url = 'https://graph.microsoft.com/v1.0/me';

        $ch = curl_init($user_info_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $access_token,
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $user_info = json_decode($response, true);
        highlight_string("<?php\n\$data =\n" . var_export($user_info, true) . ";\n?>");
        die();
        exit();
    } else {
        echo 'Erreur lors de l\'obtention du token.';
    }
}
else {
    echo 'Code d\'autorisation non disponible.';
}