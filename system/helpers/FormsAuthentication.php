<?php

    use DateTime\Date;
    use SystemSecurity\Security;

    class FormsAuthentication
    {

        function __construct()
        {
        }

        /**
         * @param        $login
         * @param string $level
         * @param bool   $rememberme
         * @param array  $informations
         */

        public static function setAuthCookie($login, $levels = array(), $maxInative = 0, $rememberme = false, $informations = array())
        {
            sessionStart();

            //Page default by route!

            $rota = RouterConfig::RegisterRoutes();
            $router = $rota["default"]['default'];
            $data = array(
                'login'        => Security::encrypt($login), //'usuario'
                'levels'       => $levels, //array('admin','basic',...)
                'rememberme'   => $rememberme, //bool, se é para manter o login salvo
                'informations' => $informations, //array('nome' => 'walison gomes', 'idade' => '29',...)
                'maxinactive'  => $maxInative, //in minutes
                'lastAccess'   => date("Y-m-d H:i:s"),
                'loginurl'     => $router['controller'] . ($router["action"] == "index_action") ? "" : "/" . $router["action"]
            );


            $_SESSION['HTTP_USER_AGENT'] = md5($_SERVER['HTTP_USER_AGENT']);


            if (self::isLogged()) {

                //Atualiza os dados da sessão. Isto vai acontecer quando estiver logado e não tiver permissao para
                //acessar uma view.

                $session = $_SESSION[self::sessionName()];

                $session['login'] = Security::encrypt($login);
                $session['levels'] = $levels;
                $session['rememberme'] = $rememberme;
                $session['informations'] = $informations;
                $session['maxinactive'] = $maxInative;

                //Não deve atualizar o lastAccess nem o loginurl

            } else {
                session_regenerate_id();
                $_SESSION[self::sessionName()] = $data;
            }

        }

        static function setInformations($info, $value)
        {
            $session = self::sessionName();
            $_SESSION[$session]['informations'][$info] = $value;
        }

        /**
         * @return bool|mixed|string
         */
        public static function sessionName()
        {

            $name = APP::getUserSessionName();

            return $name;

        }

        /**
         * @return array
         */
        public static function getSession()
        {
            return (!self::isLogged()) ? array() : $_SESSION[self::sessionName()];
        }

        /**
         * @return bool
         */
        public static function isLogged()
        {
            sessionStart();

            if (array_key_exists('HTTP_USER_AGENT', $_SESSION)) {
                if ($_SESSION['HTTP_USER_AGENT'] != md5($_SERVER['HTTP_USER_AGENT'])) {

                    self::singOut();

                    return false;
                }
            } else {
                self::singOut();

                return false;
            }
            $name = self::sessionName();


            if ($name !== "" && $name !== null) {
                if (isset($_SESSION[self::sessionName()]) && $_SESSION[self::sessionName()] !== "") return true;
            }

            return false;
        }

        /**
         * @return array
         */
        public static function getInformations($field = null)
        {
            $session = self::getSession();
            $decr = array();
            if (self::isLogged()) {
                $decr = (is_array($session["informations"])) ? $session["informations"]
                    : array();
            }

            $userInfor = (!self::isLogged()) ? array() : $decr;
            if ($field == null || $field == "" || is_null($field) || empty($field)) {
                return $userInfor;
            } else {
                return (isset($userInfor[$field])) ? $userInfor[$field] : "";
            }
        }

        public static function getId()
        {
            return (int)self::getInformations('id');
        }

        /**
         * @return string
         */
        public static function getLogin()
        {
            $session = self::getSession();

            return (!self::isLogged()) ? "" : $session["login"];
        }


        /**
         * @return array
         */

        public static function getLevels()
        {
            $session = self::getSession();

            return (!self::isLogged()) ? array() : $session["levels"];
        }

        /**
         * @return string
         */
        public static function getRememberme()
        {
            $session = self::getSession();

            return (!self::isLogged()) ? "" : $session["rememberme"];
        }

        /**
         * @return int
         */
        public static function getMaxInactive()
        {
            $session = self::getSession();

            return (!self::isLogged()) ? 0 : (int)$session["maxinactive"];
        }

        /**
         * @return null
         */
        public static function getLastAccess()
        {
            $session = self::getSession();

            return (!self::isLogged()) ? null : $session["lastAccess"];
        }

        /**
         * @param $lastAccess
         */
        public static function setLastAccess()
        {
            if (self::isLogged()) {

                $routLogin = Router::getLogin();
                $routLockScreen = Router::getLockSreen();
                $currentController = getCurrentController();

                $lsClass = (isset($routLockScreen['default']['controller'])) ? $routLockScreen['default']['controller'] : "login";
                $lgClass = (isset($routLogin["default"]["controller"])) ? $routLogin["default"]["controller"] : "login";

                if (($lsClass == $currentController && $currentController !== "") || $lgClass == $currentController) {

                } else {
                    $now = date("Y-m-d H:i:s");
                    $_SESSION[self::sessionName()]["lastAccess"] = $now;
                }
            }
        }

        public static function setUrlByLogin($url)
        {
            if (self::isLogged()) {
                $_SESSION[self::sessionName()]["loginurl"] = $url;
            }
        }

        public static function getUrlByLogin()
        {
            if (self::isLogged()) {
                $rota = RouterConfig::RegisterRoutes();
                $default = $rota["default"];
                $session = self::getSession();
                $url = ($session["loginurl"] == "") ? $default["default"]["controller"] . "/" . ($default["default"]["action"] == "index_action") ? "" : $default["default"]["action"] : decrypt($session["loginurl"]);

                return $url;
            }
        }

        /**
         * @return bool
         */
        public static function singOut()
        {

            sessionStart();
            $sessionName = self::sessionName();

            if (isset($_SESSION[DF_SESSION_CONNECION_NAME])) {
                unset($_SESSION[DF_SESSION_CONNECION_NAME]);
            }

            if (isset($_SESSION[DF_SESSION_DATA_CONNECION])) {
                unset($_SESSION[DF_SESSION_DATA_CONNECION]);
            }

            if (isset($_SESSION[$sessionName])) {
                unset($_SESSION[$sessionName]);
                session_destroy();
                return (isset($_SESSION[$sessionName])) ? false : true;
            } else {
                return true;
            }

        }

        public static function inactiveTimeExceded()
        {

            if (self::getMaxInactive() == 0) return false;

            $lastAccess = self::getLastAccess();
            $now = date("Y-m-d H:i:s");
            $diff = Date::compare($lastAccess, $now)->getInterval()->i;

            return $diff >= (int)self::getMaxInactive();
        }


        public static function urlRedirectToInactiveTimeExceded()
        {
            $routLogin = Router::getLogin();
            $routLockScreen = Router::getLockSreen();

            $lsClass = (isset($routLockScreen['default']['controller'])) ? $routLockScreen['default']['controller'] : "";
            $lsAction = (isset($routLockScreen['default']['action'])) ? $routLockScreen['default']['action'] : "";

            $lgClass = (isset($routLogin["default"]["controller"])) ? $routLogin["default"]["controller"] : "login";
            $lgAction = (isset($routLogin["default"]["action"])) ? $routLogin["default"]["action"] : "index_action";

            $url = Html::action($lgClass, ($lgAction == "index_action") ? "" : $lgAction);

            if ($lsClass !== "" && $lsAction !== "") {
                $url = Html::action($lsClass, (($lsAction == "index_action") ? "" : $lsAction));
            }

            return $url;

        }

        public static function isMaster()
        {
            $system = APP::getSystem();

            $masterRole = (isset($system['masterRole'])) ? $system['masterRole'] : null;
            if ($masterRole == null || is_null($masterRole)) return true;

            return in_array($masterRole, self::getLevels());
        }


    }