<?php

    /*Configurações do servidor*/
    use SystemSecurity\Security;

    ini_set('memory_limit', '4000M');

    class APP
    {

        public static function getSystem($param = null)
        {
            $system = MyApp::CONFIG();
            if ($param == null) return $system;
            return (isset($system[$param])) ? $system[$param] : "";
        }

        public static function pathProject()
        {
            $arr = self::getSystem();

            return $arr['path_project'];

        }

        public static function pathControllers() { return "app/controllers/"; }

        public static function pathViews() { return "app/views/"; }

        public static function pathModels() { return "app/models/"; }

        public static function pathFiles() { return self::pathproject() . 'app/files/'; }

        public static function pathHelpers() { return "system/helpers/"; }

        public static function pathDataAnnotations() { return "system/DataAnnotations/"; }

        public static function pathScriptsSystem() { return self::pathproject() . 'system/scripts/'; }

        public static function pathPoll() { return self::pathProject() . "app/poll/"; }

        public static function pathXmlPoll()
        {
            $path = "app/poll/xml";
            if (!file_exists($path)) {
                mkdir($path);
            }

            return $path . "/";
        }

        public static function getDataBaseParams($dataname)
        {
            if (!array_key_exists($dataname, MyApp::$DATABASE)) return array();

            return MyApp::$DATABASE[$dataname];
        }

        public static function getDNS($dataname)
        {
            if (!array_key_exists($dataname, MyApp::$DATABASE)) return "";
            $arr = self::getDataBaseParams($dataname);
            $host = $arr["host"];
            $db = $arr["db"];

            return "mysql:host={$host};dbname=$db";
        }

        public static function getUserSessionName()
        {

            $arr = self::getSystem();
            $session_name = Security::encrypt($arr["name"] . date('dmY'));
            if (!isset($_SESSION)) session_name(md5($arr["name"] . date('dmY')));

            return $session_name;
        }

        public static function getFisrtDatabase()
        {
            foreach (MyApp::$DATABASE as $name => $values) {
                return $values;
                break;
            }
        }

        public static function getFisrtNameDatabase()
        {
            foreach (MyApp::$DATABASE as $name => $values) {
                return $name;
                break;
            }
        }
    }