<?php

    class Router
    {

        function __construct() { }

        static function getDefault()
        {
            $r = RouterConfig::RegisterRoutes();

            return (isset($r["default"])) ? $r["default"] : null;
        }

        static function getLogin()
        {
            $r = RouterConfig::RegisterRoutes();

            return (isset($r["login"])) ? $r["login"] : null;
        }

        static function getLockSreen()
        {
            $r = RouterConfig::RegisterRoutes();

            return (isset($r["lockscreen"])) ? $r["lockscreen"] : null;
        }

        static function getRouter($router)
        {
            $r = RouterConfig::RegisterRoutes();

            return (isset($r[$router])) ? $r[$router] : null;
        }

        static function getDefaultActionForController ($controller)
        {
            $routers = RouterConfig::RegisterRoutes();
            $index = "index_action";

            $filter = array_filter($routers, function ($ev) use ($controller) {
                $default = (isset($ev['default'])) ? $ev['default'] : null;
                if ($default !== null) {
                    $cnt = (isset($default['controller'])) ? $default['controller'] : "";
                    if ($cnt !== "") {
                        if (strcasecmp($controller, $cnt) == 0) {
                            return (isset($default['action']));
                        }
                    }
                }
            });

            if (count($filter) == 0) return $index;

            $filter = array_merge($filter,array());
            $data = reset($filter);

           return ($data['default']['action'] !== "") ? $data['default']['action'] : $index;



        }

    }