<?php

    namespace app_start;

    class CheckApp
    {
        const _APPVALID_ = array(
            'top'   => 1,
            'vip'   => 2,
            'delta' => 3
        );

        static function getCode($appName)
        {
            if (empty($appName)) return 0;
            $appvalid = self::_APPVALID_;

            return (isset($appvalid[$appName])) ? $appvalid[$appName] : 0;

        }

        static function allowAccess($appName)
        {
            $code = self::getCode($appName);
            if (\FormsAuthentication::isLogged()) {
                $app = \FormsAuthentication::getInformations('app');
                $app_id = $app['id'];

                return ($app_id == $code);
            } else {
                return ($code > 0);
            }

        }

    }