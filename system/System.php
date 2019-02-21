<?php

    use app_start\CheckApp;
    use Browser\Browser;
    use data\MagicConnection;
    use NumberFormat\Number;
    use SystemSecurity\Security;
    use SystemString\StringText;

    class System
    {
        /** private **/
        private $_url;
        private $_explode;
        private $_controller;
        private $_action;
        private $_params        = array();
        private $_config        = array();
        private $_routerDefault = array();
        private $_appName       = "";

        /** block **/
        protected $_authorize               = false;
        protected $_roles                   = array();
        protected $_authorizeNotMoveToLogin = false;
        protected $_authorizeMessage        = "";
        protected $_bloqueckAccess          = false;
        protected $_bloquedMessage          = "";

        public function __construct()
        {
            $this->_config = MyApp::CONFIG();
            BundleConfig::registerBundles();
            $routerDefault = Router::getDefault();
            $this->_routerDefault = $routerDefault['default'];

            $this->setUrl();
            $this->setExplode();
            $this->setController();
            $this->setApp();
            $this->setAction();
            $this->setParams();

            //Bundles

            $this->requireLang();

        }

        private function requireLang()
        {

            try {

                $config = MyApp::CONFIG();
                if (!$config['lang']) {
                    throw new Exception(
                        "Param lang not exists in MyApp::CONFIG"
                    );
                }
                $lang = $config['lang'];
                $path = "app/lang";
                if (!is_dir($path)) {
                    mkdir($path);
                }
                $file = "{$path}/{$lang}.php";
                if (!file_exists($file)) {
                    throw new Exception("File [{$file}] not exists!");
                }

                require_once $file;

            } catch (Exception $e) {
                die($e->getMessage());
            }
        }

        private function setUrl()
        {
            //Busca o caminho na rota
            $rota = RouterConfig::RegisterRoutes();
            $routes = $rota["default"];;
            $str = $routes["default"]["controller"] . "/"
                . $routes["default"]["action"];
            $url = (isset($_GET['url'])) ? rtrim($_GET['url'], "/")
                : (($this->_config['useApp']) ? "/" : $str);
            $this->_url = $url;

        }

        private function setExplode()
        {
            $this->_explode = explode('/', rtrim($this->_url, "/"));
        }

        private function setController()
        {
            //case seja um app...
            if ($this->_config['useApp']) {
                //site.com/app/controller/site
                $this->_controller = (isset($this->_explode[1]))
                    ? $this->_explode[1] : $this->_routerDefault['controller'];
            } else {
                $this->_controller = $this->_explode[0];
            }

        }

        private function setApp()
        {
            //case seja um app...
            if ($this->_config['useApp']) {
                //site.com/app/controller/site
                $this->_appName = (isset($this->_explode[0]))
                    ? $this->_explode[0] : "";
            }

        }

        private function setAction()
        {
            //case seja um app...
            if ($this->_config['useApp']) {
                //site.com/app/controller/site
                $ac = (!isset($this->_explode[2]) || $this->_explode[2] == null
                    || $this->_explode[2] == ""
                    || $this->_explode[2] == "index")
                    ? $this->_routerDefault['action'] : $this->_explode[2];
            } else {
                $ac = (!isset($this->_explode[1]) || $this->_explode[1] == null
                    || $this->_explode[1] == ""
                    || $this->_explode[1] == "index")
                    ? $this->_routerDefault['action'] : $this->_explode[1];
            }


            $this->_action = $ac;
        }

        private function setParams()
        {
            unset($this->_explode[0], $this->_explode[1]);
            if (end($this->_explode) == null):
                array_pop($this->_explode);
            endif;
            $i = 0;
            $ind = array();
            $value = array();
            if (!empty($this->_explode)):
                foreach (($this->_explode) as $val) {
                    if ($i % 2 == 0):
                        $ind[] = $val;
                    else:
                        $value[] = $val;
                    endif;
                    $i++;
                }
            endif;

            if (count($ind) == count($value) && !empty($ind) && !empty($value)):
                $this->_params = array_combine($ind, $value);
            else:
                $this->_params = array();
            endif;
        }

        public function getParam($name = null)
        {
            if ($name !== null):

                if (array_key_exists($name, $this->_params)):
                    return ($this->_params[$name] == null
                        || !isset($this->_params[$name])) ? ""
                        : $this->_params[$name];
                else:
                    return null;
                endif;
            else:
                return $this->_params;
            endif;
        }

        private function isInternetConnection()
        {
            try {

                $test = MyApp::CONFIG('testTheConnectionBeforeRequests');
                if (!$test) {
                    return true;
                } //Retorna sempre verdadeiro caso nao queira testar a internet

                return (@fsockopen('www.google.com', 80, $num, $err, 5));

            } catch (\Exception $e) {
                return false;
            }
        }

        private function formatString($str)
        {
            $t = trim($str);
            $t = ltrim($t, "(");
            $t = rtrim($t, ")");
            $t = ltrim($t, "'");
            $t = rtrim($t, "'");
            $t = ltrim($t, '"');
            $t = rtrim($t, '"');

            return $t;

        }

        protected function authorizeInDoc($class = null, $action = null)
        {
            $called = (is_null($class)) ? get_called_class() : $class;

            if (is_object($called)) {
                try {
                    $rClass = new ReflectionClass($called);
                } catch (ReflectionException $e) {
                }
                $called = $rClass->getName();
            }

            if (class_exists($called)) {
                $r_called = new ReflectionClass($called);

                $debug = debug_backtrace();
                $trace = $debug[2];
                $trace_fn = $trace['function'];

                $parent_function_name = (is_null($action)) ? $trace_fn
                    : $action;

                //Verifica as regras para a classe
                //--> O método tem prioridade.
                $comment_class = $r_called->getDocComment();
                if ($comment_class !== false) {
                    $this->getDocAuthorize($comment_class);
                }

                //Verifica as regras para o método

                $method = $r_called->getMethod($parent_function_name);
                $comment_method = $method->getDocComment();

                if ($comment_method !== false) {
                    $this->getDocAuthorize($comment_method);
                }
            }
        }

        protected function authorizeController($controller)
        {
            if (is_object($controller)) {
                $rClass = new ReflectionClass($controller);
                $controller = $rClass->getName();
            }

            if (class_exists($controller)) {
                $r_called = new ReflectionClass($controller);
                $comment = $r_called->getDocComment();
                if ($comment !== false) {
                    $this->getDocAuthorize($comment);
                }
            }
        }

        public function access(
            $bloqued, $message = _LANG_TEMPORARILY_BLOCKED_ACCESS_
        ) {
            $this->_bloqueckAccess = !$bloqued;
            $this->_bloquedMessage = $message;

            return $this;
        }

        protected function getDocAuthorize($comment)
        {
            foreach (preg_split("/(\r?\n)/", $comment) as $line) {

                //2 - O comentário deve começar com um asterico
                if (preg_match('/^(?=\s+?\*[^\/])(.+)/', $line, $matches)) {
                    $info = $matches[1];
                    //Remove espaços em branco
                    $info = trim($info);

                    //Remove asterisco a esquerda
                    $info = preg_replace('/^(\*\s+?)/', '', $info);

                    $info = trim(
                        ltrim($info, '*')
                    ); //remove o asterisco caso tenha espaços

                    //Deve começar com @
                    if ($info !== "") {
                        if ($info[0] == "@") {

                            //Nome do parametro (nome da função)
                            preg_match('/@(\w+)/', $info, $matches);
                            $param_name = $matches[1];
                            $param_name = strtolower($param_name);
                            $value = str_ireplace("@{$param_name}", '', $info);
                            $value = $this->formatString(trim($value));

                            $value = systemDirectMail($value);

                            if ($param_name == "authorize") {
                                if (in_str(array(','), $value)) {
                                    $spl = explode(",", $value);
                                    $spl = array_map("trim", $spl);
                                    $this->authorize($spl);
                                } else {
                                    $value = ($value !== "") ? array($value)
                                        : array();
                                    $this->authorize($value);
                                }
                            } else {
                                if ($param_name == "authorizenotmovetologin") {
                                    $this->_authorizeNotMoveToLogin = true;
                                    if (strlen($value) > 0) {
                                        $this->_authorizeMessage = $value;
                                    }
                                }
                            }
                        }
                    }

                }
            }

        }

        public function authorize($roles = array())
        {
            $this->_authorize = true;
            $this->_roles = $roles;

            return $this;
        }

        public function authorizeServer()
        {
            if (!$this->_authorize) {

                $config = MyApp::$AUTHORIZE_SERVER;
                $page = strtolower($this->_parentClass) . "_" . strtolower(
                        $this->_parentAction
                    );

                //Busca no servidor o nível de acesso desta página. caso não exista, o acesso será liberado!
                $db = (isset($config['database'])) ? $config['database'] : "";
                $table = (isset($config['table'])) ? $config['table'] : "";
                $page_field = (isset($config['page_field']))
                    ? $config['page_field'] : "";
                $level_field = (isset($config['level_field']))
                    ? $config['level_field'] : "";
                $active_field = (isset($config['active_field']))
                    ? $config['active_field'] : "";
                $field_app = (isset($config['app'])) ? $config['app'] : "";

                if ($table !== "" && $page_field !== "" && $level_field !== ""
                    && $active_field !== ""
                ) {
                    $c = new MagicConnection();
                    $cn = $c->conn($db);

                    if ($cn !== null) {

                        $crit_app = "";
                        if ($this->_config['useApp'] && !empty($field_app)) {
                            $myApp = getCurrentApp();
                            $crit_app = " AND {$field_app} = '{$myApp}' ";
                        }

                        $sql
                            = "SELECT `{$level_field}` FROM `{$table}` WHERE `{$page_field}` = '{$page}' AND `{$active_field}` = 1 {$crit_app} LIMIT 0,1";

                        $rs = $cn->prepare($sql);
                        $rs->execute();
                        $lst = $rs->fetchAll();

                        if (count($lst) > 0) {

                            $levels = (isset($lst[0][$level_field]))
                                ? $lst[0][$level_field] : "";

                            if ($levels !== "") {

                                $arrayLevels = explode(",", $levels);
                                $arrLv = array();
                                foreach ($arrayLevels as $k => $l) {
                                    $arrLv[] = trim($l);
                                }
                                $this->authorize($arrLv);
                            } else {
                                $this->authorize();
                            }
                        } else {
                            $this->authorize();
                        }
                    } else {
                        $this->authorize();
                    }
                } else {
                    $this->authorize();
                }
            }

            return $this;

        }

        public function authorizeNotMoveToLogin($message = "")
        {
            if (!$this->_authorizeNotMoveToLogin) {
                $this->_authorizeNotMoveToLogin = true;
                $this->_authorizeMessage = ($message !== "") ? systemDirectMail(
                    $message
                ) : "";
            }

            return $this;
        }

        protected function moveToLogin()
        {
            if (isRequestAjax()) {
                if (!FormsAuthentication::isLogged()) {
                    die(
                    systemDirectMail(
                        _LANG_SESSION_EXPIRED_, ['appname' => $this->_appName]
                    )
                    );
                }
                die(
                systemDirectMail(
                    _LANG_UNAUTHORIZED_ACCESS_,
                    array('appname' => $this->_appName)
                )
                );
            }

            $router = RouterConfig::RegisterRoutes();
            $routLogin = $router["login"];

            $url = Security::encrypt(getCurrentLoginUrl());
            if (FormsAuthentication::isLogged()) {
                FormsAuthentication::setUrlByLogin($url);
            }
            $param = ($url !== "") ? array('redirect' => $url) : array();

            redirectToAction(
                $routLogin["default"]["controller"],
                (($routLogin["default"]["action"] == "index_action") ? ""
                    : $routLogin["default"]["action"]), $param
            );
            exit();
        }

        protected function accessEnabled()
        {

            $system = APP::getSystem();
            $access = false;
            if ($this->_authorize) {
                if (!FormsAuthentication::isLogged()) {
                    $this->moveToLogin();
                } else {
                    //Está logado, verifica se o role é permitido
                    foreach (FormsAuthentication::getLevels() as $level) {
                        if (@in_array($level, $this->_roles)
                            || count(
                                $this->_roles
                            ) == 0
                            || $level == $system["masterRole"]
                        ) {
                            $access = true;
                            break;
                        }
                    }
                }
            } else {
                $access = true;
            }

            return (($this->_bloqueckAccess) ? false : $access);
        }

        private function checkAuthorize($controller, $action)
        {
            $this->authorizeInDoc(
                $controller, $action
            ); //verifica a permissão da action
            if (!$this->accessEnabled()) {
                $this->moveToLogin();
            }
        }

        private function getMethodParam($app, $action)
        {
            try {

                $refrection = new ReflectionClass($app);
                $method = $refrection->getMethod($action);
                $params = $method->getParameters();

                $all_param = array();
                $request_params = array();

                if (isGet()) {
                    $request_params = getUrlParam();
                    $_GET = $request_params;
                }

                if (isPost()) {
                    $request_params = getPost();
                }

                foreach ($params as $param) {
                    $p = $param->getName();
                    $default = "";

                    if ($param->isArray()) {
                        $default = array();
                    }
                    if ($param->allowsNull()) {
                        $default = null;
                    }

                    $v = ($param->isOptional()) ? $param->getDefaultValue()
                        : $default;
                    $all_param[] = array_key_exists($p, $request_params)
                        ? $request_params[$p] : $v;
                }

                return $all_param;

            } catch (Exception $e) {

            }
        }

        public function run()
        {

            //verifica se a aplicação é
            if ($this->_config['useApp']) {
                //Sem APP, move
                if (CheckApp::getCode($this->_appName) == 0) {

                    die(_LANG_APP_NOT_EXISTS_);
                    //                    redirectToAction("login",'cliente');
                } else {
                    //como está usando app. Executa a função de teste
                    //de aplicação para verificar se o usuário
                    //pertence a este cliente. A função retorna 'true' para permitir o acesso e 'false' para
                    //negar, neste caso ele volta para a página de login
                    //do cliente que tentou acessar.
                    $routerDefault = Router::getLogin();
                    $routerLogin = $routerDefault['default'];

                    if ($routerLogin['controller'] !== $this->_controller) {
                        if (!CheckApp::allowAccess($this->_appName)) {
                            $this->moveToLogin();
                        }
                    }

                }
            }

            //verifica se tem bloqueio para IE
            $browsers_block = APP::getSystem('block_browser');
            if (count($browsers_block) > 0) {
                if (getCurrentController() !== "navegador") {
                    //Bloquea acesso para navegadores
                    $b_name = Browser::getName();
                    $b_version = (float)Browser::getVersion();

                    foreach ($browsers_block as $browser => $version) {


                        $version = ($version == '' || $version == null
                            || is_null($version)) ? 0
                            : Number::format($version)->toDatabase()->decimal(
                                2
                            );
                        $block = ($b_version <= $version || $version == 0);

                        if (StringText::find($b_name)->contains($browser)
                            && $block
                        ) {
                            redirectToAction("navegador");
                            exit();
                        }
                    }

                }
            }

            //Verifica se o tempo máximo de inatividade foi alcançado
            if (FormsAuthentication::inactiveTimeExceded()) {
                FormsAuthentication::singOut();
            }
            if (!$this->isInternetConnection()) {
                die(_LANG_INTERNET_CONNECTION_ERROR_);
            }
            DataView::clear(); //Limpa a variável de dataview
            if (substr($this->_action, 0, 1) == "_" && !isRequestAjax()) {
                return "";
            }
            $fileController = $this->_controller . "Controller.php";
            $controller_path = APP::pathControllers() . $fileController;
            $Controller = new \libs\Controller();
            if (isset($_SESSION['minityHtml'])) {
                if ($_SESSION['minityHtml'] == true) {
                    ob_start("sanitize_output");
                }
            }

            if (!file_exists($controller_path)) {
                //Controller não existe.
                $controller_path = sprintf("%sIndexController.php",APP::pathControllers());
                $this->_controller = "Index";

                if (!file_exists($controller_path)) {
                    $Controller->errorController(
                        array('controller' => $controller_path)
                    );
                }
            }

            $app = new $this->_controller();
            $action = $this->_action;

            if (!method_exists($app, $action)) {

                //Como o método não existe, passa como index_action usando a action
                //informada como parametro de url em RouterConfig

                $action = Router::getDefaultActionForController($this->_controller);
                if (!method_exists($app, $action)) {
                    $Controller->errorAction(
                        array('action' => $action)
                    );
                    exit();
                }
            }

            $this->checkAuthorize(
                $app, $action
            ); //verifica se está autorizado a entrar no sistema


            return call_user_func_array(
                array($app, $action), $this->getMethodParam($app, $action)
            );

        }

    }