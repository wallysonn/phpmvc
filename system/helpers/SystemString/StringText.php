<?php

    namespace SystemString;

    abstract class StringText
    {
        /**
         * @param $text
         *
         * @return Find
         */
        static function find($text)
        {
            return new Find($text);
        }

        /**
         * @param $text
         *
         * @return Convert
         */
        static function convert($text)
        {
            return new Convert($text);
        }

        /**
         * @param $text
         *
         * @return Get
         */
        static function get($text)
        {
            return new Get($text);
        }

        /**
         * @param $text
         *
         * @return Format
         */
        static function format($text)
        {
            return new Format($text);
        }

        /**
         * @param $text
         *
         * @return Validate
         */
        static function validate($text)
        {
            return new Validate($text);
        }

        /**
         * @param $text
         *
         * @return Regex
         */
        static function regex($text)
        {
            return new Regex($text);
        }

    }