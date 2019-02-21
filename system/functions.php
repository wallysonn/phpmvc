<?php

    use DateTime\Date;
    use SystemSecurity\Security;
    use SystemString\StringText;

    /**
     * @param $param
     *
     * @return array
     */
    function urlParamToArray($param)
    {
        //a=1&b=2&c=3...
        $arr = array();
        $spl = explode("&", $param);

        foreach ($spl as $part) {
            $spl_part = explode("=", $part);
            $arr[$spl_part[0]] = (isset($spl_part[1])) ? urldecode($spl_part[1]) : "";
        }

        return $arr;

    }

    function is_decimal($val)
    {
        return is_numeric($val) && floor($val) != $val;
    }

    function template($msg, $newParam = null)
    {
        if (is_array($msg)) return $msg;

        $chave = array();

        if (is_array($newParam)) {
            foreach ($newParam as $param => $value) {
                $param = trim(strtolower($param));
                $param = str_replace("{{", "", $param);
                $param = str_replace("}}", "", $param);

                if (is_array($value)) {
                    $value = json_encode($value);
                } else {
                    if (Date::validate($value)->isDate()) $value = Date::format($value)->usToBr();
                    if (is_decimal($value)) $value = number_format($value, 2, ",", ".");
                }

                $chave["{{{$param}}}"] = $value;
            }
        }

            $data = preg_replace_callback("/{{(.*?)}}/", function ($i) use ($chave, $msg) {
                $index = $i[1];

                if (in_str(array("->"), $index)) {
                    $spl = explode("->", $index);
                    $function_name = trim($spl[0]);
                    $spl_values = explode(",", $spl[1]);

                    foreach ($spl_values as $k => $vl) {
                        $vl = trim($vl);
                        $vl = str_replace("'", "", $vl);
                        $iArray = '{{' . $vl . '}}';
                        if (isset($chave[$iArray])) {
                            $spl_values[$k] = $chave[$iArray];
                        } else {
                            $spl_values[$k] = $vl;
                        }
                    }

                    if (in_str(array("::"), $function_name)) {
                        $spl_class = array_map("trim", explode("::", $function_name));
                        $class_name = $spl_class[0];
                        $method_name = $spl_class[1];
                        if (class_exists($class_name)) {
                            $cname = new $class_name();
                            if (method_exists($cname, $method_name)) {
                                return call_user_func_array(array($class_name, $method_name), $spl_values);
                            } else {
                                //return "METHOD {$method_name} NOT EXISTS IN CLASS {$class_name}";
                                return "";
                            }
                        } else {
                            //return "CLASS {$class_name} NOT EXISTS";
                            return "";
                        }
                    } else {
                        if (function_exists($function_name)) {
                            return call_user_func_array($function_name, $spl_values);
                        } else {
                            //return "[function {$function_name} not exists]";
                            return "";
                        }
                    }
                } else {
                    $index = strtolower($index);
                    $iArray = '{{' . $index . '}}';

                    return (isset($chave[$iArray])) ? $chave[$iArray] : $iArray;
                }

            }, $msg);

        return $data;


    }

   
    function renderBody()
    {

        $fileView = \libs\View::getPage();

        $fSystem = new IncludeFile();
        echo $fSystem->getfilessystem();

        if (file_exists($fileView)):
            require_once "$fileView";
        else:
            if (substr($fileView, 0, -6) == ".phtml") {
                echo("View <strong>" . rtrim($fileView, ".phtml") . "</strong> não existe!");
            } else {
                echo $fileView;
            }
        endif;
    }

    function redirectToAction($controller, $action = "", $params = array())

    {
        $isApp = MyApp::CONFIG("useApp");
        $app = ($isApp) ? getCurrentApp() . "/" : "";

        $url_param = "";
        if (count($params) > 0) {
            foreach ($params as $p => $v) {
                $url_param .= "{$p}={$v}&";
            }
            $url_param = "?" . substr($url_param, 0, -1);
        }

        $url = APP::pathProject() . $app . $controller . "/" . $action . $url_param;

        header("Location: {$url}");
    }

    function redirectToUrl($url)
    {
        header("Location: " . $url);
    }

    function getCurrentUrl()
    {
        $host = $_SERVER['HTTP_HOST'];
        $address = $_SERVER['REQUEST_URI'];

        return "http://" . $host . $address;
    }

    function getCurrentLoginUrl()
    {
        return substr($_SERVER['REQUEST_URI'], 1);
    }

    function actionExist($controller, $action)
    {
        try {

            if ($action == "" || $controller == "") return false;

            if (class_exists($controller)) {
                $c = new $controller();

                return method_exists($c, $action);
            }

            return false;

        } catch (Exception $e) {
            return false;
        }

    }

    function getUrlParam($param = "", $valueIfNull = "")
    {

        $url = getCurrentLoginUrl();
        $remove_http = str_replace('http://', '', $url);
        $split_url = explode('?', $remove_http);
        $allParam = array();

        $isApp = MyApp::CONFIG("useApp");
        $routerDefault = Router::getDefault();
        $routerDefault = $routerDefault['default'];

        if (isset($split_url[1])) {

            $split_url[1] = str_replace("+", "%2B", $split_url[1]);
            parse_str($split_url[1], $array_data);
            foreach ($array_data as $url_param => $url_value) {

                $url_value = (Security::isEncrypted($url_value)) ? Security::decrypt($url_value) : urldecode($url_value);

                if ($param == "") {
                    $allParam[$url_param] = $url_value;
                } else {
                    if ($param == $url_param) return $url_value;
                }
            }

        } else {

            $project = (APP::pathProject() == "/") ? "" : APP::pathProject();
            $project = ltrim($project, "/");

            $remove_http = str_replace($project, "", $remove_http);
            $n_url = explode("/", rtrim($remove_http, "/"));

            if ($isApp) {
                $index_controller = 1;
                $index_action = 2;
            } else {
                $index_controller = 0;
                $index_action = 1;
            }


            $controller = (isset($n_url[$index_controller])) ? $n_url[$index_controller] : $routerDefault['controller'];

            //testa se o controller Existe
            $fileController = sprintf("%s\\app\\controllers\\%sController.php",ROOT_PATH,ucfirst($controller));
            if (!file_exists($fileController)){
                $n_url = [];
                $n_url[] = "Index";
                $n_url[] = "index_action";
                $n_url[] = $controller;
                $controller = "Index";
            }

            $action = (isset($n_url[$index_action])) ? $n_url[$index_action] : "";

            if (!actionExist($controller, $action)) {
                $newAction = array($routerDefault['action']);
                array_splice($n_url, 1, 0, $newAction);
            }

            $routers = RouterConfig::RegisterRoutes();
            $current_controller = $controller;

            $filter = array_filter($routers, function ($ev) use ($current_controller) {
                $default = (isset($ev['default'])) ? $ev['default'] : null;
                if ($default !== null) {
                    $cnt = (isset($default['controller'])) ? $default['controller'] : "";
                    if ($cnt !== "") {
                        return strcasecmp($current_controller, $cnt) == 0;
                    }
                }
            });

            if (count($filter) > 0) {
                $filter = array_merge($filter, array());
                $routers = array();
                $routers[$current_controller] = reset($filter);
            }

            foreach ($routers as $routerName => $r) {

                try {
                    if (!isset($r['url'])) throw new Exception(_LANG_ROUTER_URL_NOT_EXISTS_);
                    $r_url = rtrim($r['url'], "/");

                    $spl = explode("/", $r_url);

                    for ($i = 2; $i <= count($spl) - 1; $i++) {

                        if (isset($spl[$i])) {

                            $i_url = ($isApp) ? $i + 1 : $i;

                            if (isset($n_url[$i_url])) {
                                $urlParam = str_replace(array('{', '}'), '', $spl[$i]);
                                if ($param == "") {
                                    $allParam[$urlParam] = urldecode($n_url[$i_url]);
                                } else {

                                    if ($urlParam == $param) {
                                        return urldecode($n_url[$i_url]);
                                    }
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    die("RouterERROR: {$e->getMessage()}");
                }
            }
        }

        return ($param == "") ? $allParam : $valueIfNull;

    }

    function getPost($param = "", $valueIfNull = "")
    {
        $allParam = array();

        if (isset($_POST)) {
            if ($param == "") {
                $allParam = $_POST;
            } else {
                if (isset($_POST[$param])) {
                    return $_POST[$param];
                }
            }
        }

        return ($param == "") ? $allParam : $valueIfNull;
    }

    function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    function sessionStart()
    {
        if (!isset($_SESSION)) session_start(['cookie_path' => '/']);
    }


    function getCurrentController()
    {
        $url = $_SERVER["REQUEST_URI"];
        $isApp = MyApp::CONFIG("useApp");
        $projectPath = APP::pathProject();
        $routerDefault = Router::getDefault();
        $routerDefault = $routerDefault['default'];

        if ($projectPath !== "/") {
            //retira o nome do projeto
            $url = str_replace(APP::pathProject(), "", $url);
        } else {
            $url = ltrim($url, "/");
        }

        if ($isApp) {
            //site.com/app/controle/action...
            $index = 1;
        } else {
            //site.com/controle/action...
            $index = 0;
        }

        if (strstr($url, "?")) {
            $spl = explode("?", $url);

            if (strstr(rtrim($spl[0], "/"), "/")) {
                $part = explode("/", $spl[0]);

                return (isset($part[$index])) ? $part[$index] : $routerDefault['controller'];
            } else {
                return ($isApp) ? $routerDefault['controller'] : $spl[$index];
            }

        } else {

            if (strstr(rtrim($url, "/"), "/")) {
                $part = explode("/", $url);

                return (isset($part[$index])) ? $part[$index] : $routerDefault['controller'];
            } else {
                return ($isApp) ? $routerDefault['controller'] : $url;

            }
        }
    }

    function getCurrentApp()
    {
        $url = $_SERVER["REQUEST_URI"];
        $isApp = MyApp::CONFIG("isApp");
        $projectPath = APP::pathProject();

        if ($projectPath !== "/") {
            $url = str_replace(APP::pathProject(), "", $url);
        } else {
            $url = ltrim($url, "/");
        }

        if ($isApp) {

            $part = explode("/", rtrim($url, "/"));

            return $part[0];

        } else {
            //site.com/controle/action...
            return "";
        }
    }

    function getCurrentAction()
    {
        $url = $_SERVER["REQUEST_URI"];
        $isApp = MyApp::CONFIG("isApp");
        $projectPath = APP::pathProject();
        $routerDefault = Router::getDefault();
        $routerDefault = $routerDefault['default'];

        if ($projectPath !== "/") {
            $url = str_replace(APP::pathProject(), "", $url);
        } else {
            $url = ltrim($url, "/");
        }

        if ($isApp) {
            //site.com/app/controle/action...
            $index = 2;
        } else {
            //site.com/controle/action...
            $index = 1;
        }

        if (strstr($url, "?")) {
            $spl = explode("?", $url);
            //verifica se tem uma action diferente de index
            if (strstr(rtrim($spl[0], "/"), "/")) {
                $part = explode("/", rtrim($spl[0], "/"));

                return (isset($part[$index])) ? $part[$index] : $routerDefault['action'];
            } else {
                return $routerDefault['action'];
            }

        } else {
            if (strstr(rtrim($url, "/"), "/")) {
                $part = explode("/", rtrim($url, "/"));

                return (isset($part[$index])) ? $part[$index] : $routerDefault['action'];
            } else {
                return $routerDefault['action'];
            }
        }
    }

    function isRequestAjax()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    function in_array_multidimentional($item, array $array)
    {
        return (preg_match('/"' . $item . '"/i', json_encode($array)) > 0) ? true : false;
    }

    function ping($host)
    {
        $p = @fsockopen("ssl://" . $host, 443, $errno, $errstr, 3);
        if ($p) {
            fclose($p);

            return true;
        } else {
            return false;
        }
    }

    function fgets_u()
    {
        return trim(fgets(STDIN, 1024));
    }

    function in_str(array $array_text, $string, $all = false)
    {

        if (!is_array($array_text)) return false;

        $ini = array('[', ']', '(', ')', '*', '/', '.', '|', '^');
        $end = array('\[', '\]', '\(', '\)', '\*', '\/', '\.', '\|', '\^');

        $existe = false;
        //$cumulative = false;

        foreach ($array_text as $text) {

            $procurar = str_replace($ini, $end, $text);
            $ret = preg_match("/({$procurar})/i", $string, $r);

            $existe = $ret !== 0;

            if (!$all) {
                if ($existe) break;
            } else {
                if (!$existe) break;
            }
        }

        return $existe;

    }

    function removeBOM($str = "")
    {
        if (substr($str, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
            $str = substr($str, 3);
        }

        return $str;
    }

    function getOnlyNumbers($str)
    {
        return preg_replace("/[^0-9]/", "", $str);
    }

    function object_to_array($data)
    {
        if (is_array($data) || is_object($data)) {
            $result = array();
            foreach ($data as $key => $value) {
                $result[$key] = object_to_array($value);
            }

            return $result;
        }

        return $data;
    }