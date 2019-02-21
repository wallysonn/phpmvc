<?php

    class OfuscatorJsBlockCode
    {


        function __construct() { }

        static function start()
        {
            ob_start();
        }

        static function ofuscator()
        {
            $captured = ob_get_clean();

            if ($captured !== false) {

                $of = new OfuscatorJs(trim($captured), 'Normal', false, false);

                return $captured; //$of->pack();

            } else {
                return "Js Note Valid!" . PHP_EOL;
            }

        }

    }