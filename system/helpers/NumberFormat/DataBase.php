<?php

    namespace NumberFormat;

    class DataBase
    {
        private $number = null;

        function __construct($number)
        {
            $this->number = $number;
        }

        function integer()
        {
            try {
                $number = Number::format($this->number)->removeSpecialCharacter();

                return (int)$number;
            } catch (\Exception $e) {

            }
        }

        /**
         * Retorna o número em decimal compatível com banco de dados sem arredondar os valores
         *
         * @param int $decimals
         *
         * @return string
         */
        function decimal($decimals = 2)
        {
            try {
                if (is_null($decimals) || $decimals == "" || is_array($decimals) || is_object($decimals)) $decimals = 0;

                $number = $this->number;
                if (is_numeric($number)) return $number;
                $number = Number::format($number)->removeSpecialCharacter();
                $number = (double)$number;

                return number_format(floor($number * pow(10, $decimals)) / pow(10, $decimals), $decimals, ".", "");
            } catch (\Exception $e) {
                return 0;
            }
        }

    }