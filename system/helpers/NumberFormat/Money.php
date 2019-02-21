<?php

    namespace NumberFormat;
    class Money
    {
        private $number = null;

        function __construct($number)
        {

            $this->number = ($number == null) ? 0 : $number;
        }


        function BR()
        {


            $number = Number::format($this->number)->toDecimal(2);
            return "R$ {$number}";
        }

    }