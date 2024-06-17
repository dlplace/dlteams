<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2023 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

use Glpi\Application\ErrorHandler;
use Glpi\Event;
use Glpi\Plugin\Hooks;
use Glpi\Toolbox\Sanitizer;

/**
 *  Identification class used to login
 */
class PluginDlteamsAuth extends CommonGLPI
{
    /** @var array Array of errors */
    private $errors = [];
    /** @var User User class variable */
    public $user;
    /** @var int External authentication variable */
    public $extauth = 0;
    /** @var array External authentication methods */
    public $authtypes;
    /** @var int Indicates if the user is authenticated or not */
    public $auth_succeded = 0;
    /** @var int Indicates if the user is already present in database */
    public $user_present = 0;
    /** @var int Indicates if the user password expired */
    public $password_expired = false;

    /**
     * Indicated if user was found in the directory.
     * @var boolean
     */
    public $user_found = false;

    /**
     * Indicated if an error occurs during connection to the user LDAP.
     * @var boolean
     */
    public $user_ldap_error = false;

    /** @var resource|boolean LDAP connection descriptor */
    public $ldap_connection;
    /** @var bool Store user LDAP dn */
    public $user_dn = false;

    const DB_GLPI = 1;
    const MAIL = 2;
    const LDAP = 3;
    const EXTERNAL = 4;
    const CAS = 5;
    const X509 = 6;
    const API = 7;
    const COOKIE = 8;
    const NOT_YET_AUTHENTIFIED = 0;

    const USER_DOESNT_EXIST = 0;
    const USER_EXISTS_WITH_PWD = 1;
    const USER_EXISTS_WITHOUT_PWD = 2;

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->user = new User();
    }

    /**
     * Manage use authentication and initialize the session
     *
     * @param string  $login_name      Login
     * @param string  $login_password  Password
     * @param boolean $noauto          (false by default)
     * @param bool    $remember_me
     * @param string  $login_auth      Type of auth - id of the auth
     *
     * @return boolean (success)
     */
    public function login($login_name, $noauto = false, $remember_me = true, $login_auth = '')
    {
        /**
         * @var array $CFG_GLPI
         * @var \DBmysql $DB
         */
        global $CFG_GLPI, $DB;

//        $this->getAuthMethods();
        $this->user_present  = 1;
        $this->auth_succeded = false;
        //In case the user was deleted in the LDAP directory
        $user_deleted_ldap   = false;

        // Trim login_name : avoid LDAP search errors
        $login_name = trim($login_name);

        // manage the $login_auth (force the auth source of the user account)
        $this->user->fields["auths_id"] = 0;
        $authtype = null;

        if (!$this->auth_succeded) {
            if (
                empty($login_name) || strstr($login_name, "\0")
            ) {
                $this->addToError(__('Empty login or password'));
            } else {
                // Try connect local user if not yet authenticated
                if (
                    empty($login_auth)
                    || $this->user->fields["authtype"] == $this::DB_GLPI
                ) {

                    $this->auth_succeded = $this->connection_db(
                        addslashes($login_name),
//                        $login_password
                    );
                }

            }
        }


        // Ok, we have gathered sufficient data, if the first return false the user
        // is not present on the DB, so we add him.
        // if not, we update him.
        if ($this->auth_succeded) {
            //Set user an not deleted from LDAP
            $this->user->fields['is_deleted_ldap'] = 0;

            // Prepare data
            $this->user->fields["last_login"] = $_SESSION["glpi_currenttime"];
            if ($this->extauth) {
                $this->user->fields["_extauth"] = 1;
            }

            if ($DB->isSlave()) {
                if (!$this->user_present) { // Can't add in slave mode
                    $this->addToError(__('User not authorized to connect in GLPI'));
                    $this->auth_succeded = false;
                }
            } else {
                if ($this->user_present) {
                    // Add the user e-mail if present
                    if (isset($email)) {
                        $this->user->fields['_useremails'] = $email;
                    }
                    $this->user->update(Sanitizer::sanitize($this->user->fields));
                } else if ($CFG_GLPI["is_users_auto_add"]) {
                    // Auto add user
                    $input = $this->user->fields;
                    unset($this->user->fields);
                    if ($authtype == self::EXTERNAL && !isset($input["authtype"])) {
                        $input["authtype"] = $authtype;
                    }
                    $this->user->add(Sanitizer::sanitize($input));
                } else {
                    // Auto add not enable so auth failed
                    $this->addToError(__('User not authorized to connect in GLPI'));
                    $this->auth_succeded = false;
                }
            }
        }

        // Log Event (if possible)
        if (!$DB->isSlave()) {
            // GET THE IP OF THE CLIENT
            $ip = getenv("HTTP_X_FORWARDED_FOR") ?
                Sanitizer::encodeHtmlSpecialChars(getenv("HTTP_X_FORWARDED_FOR")) :
                getenv("REMOTE_ADDR");

            if ($this->auth_succeded) {
                if (GLPI_DEMO_MODE) {
                    // not translation in GLPI_DEMO_MODE
                    Event::log(0, "system", 3, "login", $login_name . " log in from " . $ip);
                } else {
                    //TRANS: %1$s is the login of the user and %2$s its IP address
                    Event::log(0, "system", 3, "login", sprintf(
                        __('%1$s log in from IP %2$s'),
                        $login_name,
                        $ip
                    ));
                }
            } else {
                if (GLPI_DEMO_MODE) {
                    Event::log(
                        0,
                        "system",
                        3,
                        "login",
                        "Connection failed for " . $login_name . " ($ip)"
                    );
                } else {
                    //TRANS: %1$s is the login of the user and %2$s its IP address
                    Event::log(0, "system", 3, "login", sprintf(
                        __('Failed login for %1$s from IP %2$s'),
                        $login_name,
                        $ip
                    ));
                }
            }
        }

        PluginDlteamsSession::init($this);

        if ($noauto) {
            $_SESSION["noAUTO"] = 1;
        }

        if ($this->auth_succeded && $CFG_GLPI['login_remember_time'] > 0 && $remember_me) {
            $token = $this->user->getAuthToken('cookie_token', true);

            if ($token) {
                $data = json_encode([
                    $this->user->fields['id'],
                    $token,
                ]);

                //Send cookie to browser
                Auth::setRememberMeCookie($data);
            }
        }

        if ($this->auth_succeded && !empty($this->user->fields['timezone']) && 'null' !== strtolower($this->user->fields['timezone'])) {
            //set user timezone, if any
            $_SESSION['glpi_tz'] = $this->user->fields['timezone'];
            $DB->setTimezone($this->user->fields['timezone']);
        }

        return $this->auth_succeded;
    }

    /**
     * Find a user in the GLPI DB
     *
     * try to connect to DB
     * update the instance variable user with the user who has the name $name
     * and the password is $password in the DB.
     * If not found or can't connect to DB updates the instance variable err
     * with an eventual error message
     *
     * @global DBmysql $DB
     * @param string $name     User Login
     *
     * @return boolean user in GLPI DB with the right password
     */
    public function connection_db($name)
    {
        /**
         * @var array $CFG_GLPI
         * @var \DBmysql $DB
         */
        global $CFG_GLPI, $DB;

        $pass_expiration_delay = (int)$CFG_GLPI['password_expiration_delay'];
        $lock_delay            = (int)$CFG_GLPI['password_expiration_lock_delay'];

        // SQL query
        $result = $DB->request(
            [
                'SELECT' => [
                    'id',
                    'password',
                    new QueryExpression(
                        sprintf(
                            'ADDDATE(%s, INTERVAL %d DAY) AS ' . $DB->quoteName('password_expiration_date'),
                            $DB->quoteName('password_last_update'),
                            $pass_expiration_delay
                        )
                    ),
                    new QueryExpression(
                        sprintf(
                            'ADDDATE(%s, INTERVAL %d DAY) AS ' . $DB->quoteName('lock_date'),
                            $DB->quoteName('password_last_update'),
                            $pass_expiration_delay + $lock_delay
                        )
                    )
                ],
                'FROM'   => User::getTable(),
                'WHERE'  =>  [
                    'name'     => $name,
                    'authtype' => self::DB_GLPI,
                    'auths_id' => 0,
                ]
            ]
        );

        // Have we a result ?
        if ($result->numrows() == 1) {
            $row = $result->current();
            $password_db = $row['password'];

            if (self::checkPassword($password, $password_db)) {
                // Disable account if password expired
                if (
                    -1 !== $pass_expiration_delay && -1 !== $lock_delay
                    && $row['lock_date'] < $_SESSION['glpi_currenttime']
                ) {
                    $user = new User();
                    $user->update(
                        [
                            'id'        => $row['id'],
                            'is_active' => 0,
                        ]
                    );
                }
                if (
                    -1 !== $pass_expiration_delay
                    && $row['password_expiration_date'] < $_SESSION['glpi_currenttime']
                ) {
                    $this->password_expired = 1;
                }

                // Update password if needed
                if (self::needRehash($password_db)) {
                    $input = [
                        'id' => $row['id'],
                    ];
                    // Set glpiID to allow password update
                    $_SESSION['glpiID'] = $input['id'];
                    $input['password'] = $password;
                    $input['password2'] = $password;
                    $user = new User();
                    $user->update($input);
                }
                $this->user->getFromDBByCrit(['id' => $row['id']]);
                $this->extauth                  = 0;
                $this->user_present             = 1;
                $this->user->fields["authtype"] = self::DB_GLPI;
                $this->user->fields["password"] = $password;

                // apply rule rights on local user
                $rules  = new RuleRightCollection();
                $groups = Group_User::getUserGroups($row['id']);
                $groups_id = array_column($groups, 'id');
                $result = $rules->processAllRules(
                    $groups_id,
                    $this->user->fields,
                    [
                        'type'  => Auth::DB_GLPI,
                        'login' => $this->user->fields['name'],
                        'email' => UserEmail::getDefaultForUser($row['id'])
                    ]
                );

                $this->user->fields = $result + [
                        '_ruleright_process' => true,
                    ];

                return true;
            }
        }
        $this->addToError(__('Incorrect username or password'));
        return false;
    }

    /**
     * Add a message to the global identification error message
     *
     * @param string $message the message to add
     *
     * @return void
     */
    public function addToError($message)
    {
        if (!in_array($message, $this->errors)) {
            $this->errors[] = $message;
        }
    }

    /**
     * Is the hash stored need to be regenerated
     *
     * @since 0.85
     *
     * @param string $hash Hash
     *
     * @return boolean
     */
    public static function needRehash($hash)
    {

        return password_needs_rehash($hash, PASSWORD_DEFAULT);
    }

    /**
     * Check is a password match the stored hash
     *
     * @since 0.85
     *
     * @param string $pass Password (pain-text)
     * @param string $hash Hash
     *
     * @return boolean
     */
    public static function checkPassword($pass, $hash)
    {

        $tmp = password_get_info($hash);

        if (isset($tmp['algo']) && $tmp['algo']) {
            $ok = password_verify($pass, $hash);
        } else if (strlen($hash) == 32) {
            $ok = md5($pass) === $hash;
        } else if (strlen($hash) == 40) {
            $ok = sha1($pass) === $hash;
        } else {
            $salt = substr($hash, 0, 8);
            $ok = ($salt . sha1($salt . $pass) === $hash);
        }

        return $ok;
    }

}