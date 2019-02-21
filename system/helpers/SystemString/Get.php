<?php

    namespace SystemString;

    class Get
    {
        private $text = '';

        function __construct($text)
        {
            $this->text = $text;
        }

        /**
         * Retorna o comprimento da string
         * @return int
         */
        function length()
        {
            return strlen($this->text);
        }

        /**
         * @param $caracter
         *
         * @return array
         */
        function split($caracter)
        {
            return explode($caracter, $this->text);
        }
    }