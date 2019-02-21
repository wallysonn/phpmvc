<?php

    namespace NumberFormat;
    class Format
    {
        private $number = null;

        function __construct($number)
        {
            $this->number = $number;
        }

        function toDecimal($decimals = 2)
        {
            $number = $this->number;
            if (!is_numeric($number)) $number = $this->removeSpecialCharacter();

            $decimals = (int)$decimals;

            return number_format($number, $decimals, ",", ".");
        }
//
//        function toInteger()
//        {
//
//        }

        function toMoney()
        {
            return new Money($this->number);
        }

        function removeSpecialCharacter()
        {
            $number = str_replace(array(".", ","), array("", "."), $this->number);

            return preg_replace("/[^0-9\.\,]/", "", $number);
        }

        function code($lng = 6)
        {

            return str_pad($this->number, $lng, 0, STR_PAD_LEFT);
        }


        /**
         * @return DataBase
         */
        function toDatabase()
        {
            return new DataBase($this->number);
        }

    }