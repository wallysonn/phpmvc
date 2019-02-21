<?php

    namespace NumberFormat;

    class Get
    {
        private $number = null;

        function __construct($number)
        {
            $this->number = $number;
        }

        /**
         * Remove todos os caracteres deixando apenas números ==> 0 a 9
         * @return mixed
         */
        function onlyNumbers()
        {
            return preg_replace('/[^[0-9]/', '', $this->number);

        }

    }