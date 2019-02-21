<?php

    namespace SystemArray;

    abstract class DataArray
    {
        /**
         * @param array $array
         *
         * @return Convert
         */
        static function convert(array $array)
        {
            return new Convert($array);
        }

        /**
         * @param array $array
         *
         * @return Find
         */
        static function find(array $array)
        {
            return new Find($array);
        }
    }

