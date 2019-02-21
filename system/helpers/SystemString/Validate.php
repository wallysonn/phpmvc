<?php

    namespace SystemString;

    class Validate
    {
        private $text = "";

        function __construct($text)
        {
            $this->text = $text;
        }

        /**
         * @return mixed
         */
        function isEmail()
        {
            return filter_var($this->text, FILTER_VALIDATE_EMAIL);
        }

        /**
         * @return mixed
         */
        function isUrl()
        {
            return filter_var($this->text, FILTER_VALIDATE_URL);
        }

        /**
         * @return mixed
         */
        function isRegExp()
        {
            return filter_var($this->text, FILTER_VALIDATE_REGEXP);
        }

    }