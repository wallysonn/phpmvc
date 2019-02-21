<?php

    namespace libs;

    use APP;
    use FormsAuthentication;
    use MyApp;
    use DbConnection;
    use RouterConfig;
    use Security;

    class Controller extends \System
    {

        private $_layout       = "";
        private $_vars         = null;
        private $_viewName     = "";
        private $_inAjaxOnly   = false;
        private $_parentAction = "";
        private $_parentClass  = "";

        private $_magicparams = array();

        public function __set($name, $value)
        {
            $this->_magicparams[$name] = $value;

            return $this;
        }

        public function __get($name)
        {
            try {

                if (!isset($_SESSION['params_global'][$name])) throw  new \Exception(sprintf(_LANG_PARAM_NOT_EXISTS_, $name));

                return $_SESSION['params_global'][$name];

            } catch (\Exception $e) {
                return $e->getMessage();
            }
        }

        //ERRORS
        private function showErrorPage($errorPage, array $variables = null)
        {
            try {
                $path = APP::pathViews();
                $variables['app'] = \ClientApp::getId();
                $errorPage = str_replace(".phtml", "", $errorPage);
                $file = "{$path}_Errors/{$errorPage}.phtml";
                if (file_exists($file)) {
                    $this->getHtmlContent($file, $variables);
                } else {
                    die(sprintf(_LANG_LAYOUT_NOT_EXISTS_, $file));
                }
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        public function errorAction(array $variables = null)
        {
            return $this->showErrorPage("action", $variables);
        }

        public function errorController(array $variables = null)
        {
            return $this->showErrorPage("controller", $variables);
        }

        public function error404(array $variables = null)
        {
            return $this->showErrorPage("404", $variables);
        }

        public function view($nome = "")
        {

            $class = (function_exists("get_called_class")) ? get_called_class() : getCalledClass();

            $path = ($class == "Controller") ? "" : "{$class}/";
            $pathViews = APP::pathViews();

            if ($nome == "") {
                $callers = debug_backtrace();

                $v = ($callers[1]['function'] == "index_action") ? "index" : $callers[1]['function'];
                $nome = $v;

                $this->_parentAction = $v;
                $this->_parentClass = $callers[1]['class'];

                if (!file_exists($pathViews . $path . $nome . ".phtml")) {
                    $nome = strtolower($v);
                    if (!file_exists($pathViews . $path . $nome . ".phtml")) {
                        $nome = strtoupper($v);
                    }
                }
            }

            FormsAuthentication::setLastAccess();

            $this->_viewName = $path . $nome;

            return $this;
        }

        public function vars($vars = null)
        {
            $this->_vars = $vars;

            return $this;
        }


        public function inLayout($layout = "_layout")
        {
            if (!isRequestAjax()) {
                $this->_layout = $layout;
            } else {
                $this->_layout = "";
            }

            return $this;
        }

        public function showMessage($message)
        {
            $_SESSION['ViewContent']['page'] = $message;

            $path = APP::pathViews();
            $file_layout = $path . '_Shared/' . $this->_layout . '.phtml';

            if ($this->_layout == "") {
                echo $message;
            } else {

                if (file_exists($file_layout)) {
                    return require_once($file_layout);
                } else {
                    die(sprintf(_LANG_LAYOUT_NOT_EXISTS_, $file_layout));
                }
            }
        }

        public function show(array $variables = null)
        {

            $this->authorizeInDoc(); //verifica a autorização pelo comentário
            $variables['app'] = \ClientApp::getId();

            $path = APP::pathViews();
            if (is_array($this->_vars) && count($this->_vars) > 0):
                extract($this->_vars, EXTR_PREFIX_ALL, "");
            endif;

            $fileView = $path . $this->_viewName . '.phtml';

            if ($this->_inAjaxOnly) {
                if (!isRequestAjax()) {
                    $this->_layout = "_layout";
                    $fileView = $path . '_Errors/404.phtml';
                }
            }

            $file_layout = $path . '_Shared/' . $this->_layout . '.phtml';

            $_SESSION['ViewContent'] = array('page' => $fileView, 'authorize' => $this->_authorize, 'roles' => $this->_roles);

            $access = $this->accessEnabled();


            if ($access) {
                if ($this->_layout == "") {
                    if (file_exists($fileView)) {
                        $this->getHtmlContent($fileView, $variables);
                    } else {
                        die(sprintf(_LANG_VIEW_NOT_EXISTS_, $fileView));
                    }
                } else if (file_exists($file_layout)) {
                    $this->getHtmlContent($file_layout, $variables);
                } else {
                    die(sprintf(_LANG_LAYOUT_NOT_EXISTS_, $file_layout));
                }
            } else {

                if ($this->_bloqueckAccess) {
                    $_SESSION['ViewContent']['page'] = $this->_bloquedMessage;
                    if ($this->_layout == "") {
                        echo $this->_bloquedMessage;
                    } else {
                        if (file_exists($file_layout)) {
                            $this->getHtmlContent($file_layout, $variables);
                        } else {
                            die(sprintf(_LANG_LAYOUT_NOT_EXISTS_, $file_layout));
                        }
                    }
                } else {

                    if (!$this->_authorizeNotMoveToLogin) {
                        $this->moveToLogin();
                    } else {
                        //Só mostra a mensagem se estiver logado
                        if (!FormsAuthentication::isLogged()) $this->moveToLogin();

                        $message = ($this->_authorizeMessage == "") ? systemDirectMail(_LANG_UNAUTHORIZED_ACCESS_) : $this->_authorizeMessage;

                        if ($this->_layout == "") {

                            echo $message;

                        } else {

                            $_SESSION['ViewContent']['page'] = $message;
                            if (file_exists($file_layout)) {
                                $this->getHtmlContent($file_layout, $variables);
                            } else {
                                die(sprintf(_LANG_LAYOUT_NOT_EXISTS_, $file_layout));
                            }

                        }
                    }
                }

            }

        }

        private function getHtmlContent($file, $variables)
        {

            $globais = APP::getSystem();
            $define = get_defined_constants(true);
            $lang = (isset($define['user'])) ? $define['user'] : array();

            if ((is_array($variables) && count($variables) > 0) || count($globais) > 0) {

                if (!is_array($variables)) {
                    $all_variables = array_merge($globais, $lang);
                } else {
                    $all_variables = array_merge($globais, $variables, $lang);
                }

                $_SESSION['params_global'] = array(); //limpa
                $_SESSION['params_global'] = $all_variables;

                @ob_start();
                require_once $file;
                $content = @ob_get_clean();
                //Executa a mala direta em todos os valores do array
                $all_variables = array_map('systemDirectMail', $all_variables);
                $content = systemDirectMail($content, $all_variables);
//                $content = phpVue($content);

                echo $content;

            } else {
                return require_once $file;
            }
        }

        public function showInAjaxOnly(array $variables = null)
        {
            $this->_inAjaxOnly = true;
            $this->show($variables);
        }

        public function renderBody()
        {
            return renderBody();
        }


    }