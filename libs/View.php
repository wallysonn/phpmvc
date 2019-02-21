<?php

    namespace libs;

    class View
    {

        function __construct()
        {

        }

        /**
         * @return mixed
         */
        public static function getRoles()
        {
            if (!self::exists()) return array();
            if (!isset($_SESSION)) session_start();

            return $_SESSION['ViewContent']["roles"];
        }

        /**
         * @return mixed
         */
        public static function getPage()
        {
            if (!self::exists()) return "";
            if (!isset($_SESSION)) session_start();

            return $_SESSION['ViewContent']["page"];
        }

        /**
         * @return mixed
         */
        public static function isAuthorize()
        {
            if (!self::exists()) return false;
            if (!isset($_SESSION)) session_start();

            return $_SESSION['ViewContent']["authorize"];
        }

        protected static function exists()
        {
            if (!isset($_SESSION)) session_start();

            return isset($_SESSION['ViewContent']);
        }

    }