<?php

    namespace Security;

    class BCrypt
    {
        protected static $_saltPrefix  = '2a';
        protected static $_defaultCost = 8;
        protected static $_saltLength  = 22;

        public static function hash($string)
        {

            $cost = self::$_defaultCost;


            // Salt
            $salt = self::generateRandomSalt();

            // Hash string
            $hashString = self::__generateHashString((int)$cost, $salt);

            $pass = crypt($string, $hashString);
            $explode = explode("$", $pass);

            return $explode[3];
        }

        public static function check($string, $hash)
        {
            $hash = '$2a$08$' . trim($hash);

            return (crypt($string, $hash) === $hash);
        }

        public static function generateRandomSalt()
        {
            // Salt seed
            $seed = uniqid(mt_rand(), true);

            // Generate salt
            $salt = base64_encode($seed);
            $salt = str_replace('+', '.', $salt);

            return substr($salt, 0, self::$_saltLength);
        }

        private static function __generateHashString($cost, $salt)
        {
            return sprintf('$%s$%02d$%s$', self::$_saltPrefix, $cost, $salt);
        }

    }