<?php

    class DataView
    {


        function __construct()
        {

        }

        /**
         * @return string
         */
        protected static function  getDataViewName()
        {
            return md5("viewdata" . date('Ymd'));
        }

        /**
         * @param $name
         * @param $value
         */
        public static function set($name, $value)
        {
            sessionStart();
            $name_session = self::getDataViewName();
            $_SESSION[$name_session][] = array($name => $value);
        }

        /**
         * @param $name
         *
         * @return mixed
         */
        public static function get($name)
        {
            sessionStart();
            $name_session = self::getDataViewName();
            foreach ($_SESSION[$name_session] as $key => $val) {
                if (isset($val[$name])) {
                    return $val[$name];
                }
            }

            return "";
        }

        /**
         * @return array
         */
        public static function clear()
        {
            if (!isset($_SESSION)) session_start();
            $name_session = self::getDataViewName();

            return $_SESSION[$name_session] = array();
        }
    }