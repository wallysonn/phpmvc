<?php

    class RouterConfig
    {

        public static function  RegisterRoutes()
        {
            //Crie suas rotas aqui
            $routes = array();

            //System Router
            $routes["index"] = array(
                'url'     => '{controller}/{action}/{token}',
                'default' => array(
                    'controller' => 'index',
                    'action'     => 'index_action'
                )
            );



            $routes["default"] = array(
                'url'     => '{controller}/{action}/{id}',
                'default' => array(
                    'controller' => 'index',
                    'action'     => 'index_action'
                )
            );

            $routes["login"] = array(
                'url'     => '{controller}/{action}/{id}',
                'default' => array(
                    'controller' => 'login',
                    'action'     => 'index_action'
                )
            );


            return $routes;
        }
    }