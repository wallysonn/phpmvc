<?php

    namespace SystemString;

    class Find
    {
        private $text = '';

        function __construct($text)
        {
            $this->text = $text;
        }

        /**
         * Verifica se um determinando caractere existe na string
         *
         * @param      $value
         * @param bool $ignoreCase
         *
         * @return bool
         */
        function contains($value, $ignoreCase = true)
        {
            $text = ($ignoreCase) ? StringText::convert($this->text)->toLower() : $this->text;
            $vl = ($ignoreCase) ? StringText::convert($value)->toLower() : $value;

            return strpos($text, $vl) !== false;
        }

        /**
         * Retorna a primeira palavra
         * @return string
         */
        function firstWord()
        {
            return strtok($this->text, " ");
        }

        /**
         * Retorna a última palavra
         * @return mixed
         */
        function lastWord()
        {
            $str = explode(" ", $this->text);
            return array_pop($str);
        }

    }