<?php

    class BrowserInfor
    {

        public static function version()
        {
            $lista_navegadores = array("MSIE", "Firefox", "Chrome", "Safari", "OPR");
            $navegador_usado = $_SERVER["HTTP_USER_AGENT"];

            foreach ($lista_navegadores as $valor_verificar) {
                if (strrpos($navegador_usado, $valor_verificar)) {
                    $navegador = $valor_verificar;
                    $posicao_inicial = strpos($navegador_usado, $navegador) + strlen($navegador);
                    $versao = substr($navegador_usado, $posicao_inicial, 5);

                    return str_replace(";", "", $versao);
                }
            }
        }

        public static function name()
        {
            $lista_navegadores = array("MSIE", "Firefox", "Chrome", "Safari", "OPR");
            $navegador_usado = $_SERVER["HTTP_USER_AGENT"];

            foreach ($lista_navegadores as $valor_verificar) {
                if (strrpos($navegador_usado, $valor_verificar)) {
                    $navegador = $valor_verificar;

                    return $navegador;
                }
            }
        }

        public static function getArgent()
        {
            return $_SERVER["HTTP_USER_AGENT"];
        }

        public static function isMobile()
        {
            return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", self::getArgent());
        }

        public static function getUrlContent($url)
        {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $contents = curl_exec($ch);
            if (curl_errno($ch)) {
                $contents = '';
            } else {
                curl_close($ch);
            }

            if (!is_string($contents) || !strlen($contents)) {

                $contents = '';
            }

            return $contents;
        }

    }