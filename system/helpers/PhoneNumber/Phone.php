<?php

    namespace PhoneNumber;

    abstract class Phone
    {

        /**
         * Valida o telefone
         *
         * @param $phone
         *
         * @return Validate
         */
        static function validate($phone)
        {
            return new Validate($phone);
        }

        /**Retorna informações do telefone
         *
         * @param $phone
         *
         * @return Get
         */
        static function get($phone)
        {
            return new Get($phone);
        }

        /**
         * Formata o número
         *
         * @param $phone
         *
         * @return Format
         */
        static function format($phone)
        {
            return new Format($phone);
        }
    }