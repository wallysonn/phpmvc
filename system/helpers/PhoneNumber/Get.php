<?php

    namespace PhoneNumber;

    class Get
    {
        private $phone   = null;
        private $ddd     = 98; //the default ddd is located in file app.config.php
        private $country = 55;

        function __construct($phone)
        {
            $this->phone = $phone;
            $app = \APP::getSystem();
            if (isset($app['ddd'])) $this->ddd = $app['ddd'];
            if (isset($app['country'])) $this->country = $app['country'];
        }

        /**
         * Retorna o DDD, caso não exista, mostra o padrão
         * @return string
         */
        function ddd()
        {
            $phone = $this->phone;
            if (Phone::validate($phone)->isNational()) return "";
            $len = strlen($phone);
            if ($len < 10) return $this->ddd;
            $phone_ddd = $this->phoneWithDdd();
            return substr($phone_ddd, 0, 2);

        }

        /**Retorna o telefone incluindo o ddd. Caso o DDD não exista, inclui o padrão!
         * @return null|string
         */
        function phoneWithDdd()
        {

            try{

                $phone = $this->phone;
                $ddd = $this->ddd;
                $len = strlen($phone);
                if ($len < 8) return $phone;
                if (!Phone::validate($phone)->isValid()) return $phone;
                if ($len == 8 || ($len == 9 && substr($phone, 0, 1) == "9")) return "{$ddd}{$phone}";

                if (Phone::validate($phone)->isResidential()) return substr($phone, -10);
                if (Phone::validate($phone)->isCell()) return substr($phone, -11);

                return $phone;

            }catch (\Exception $e){

            }



        }

        /**
         * Retorna apenas o número, sem DDD. Celular será incluido "9" no inicio
         * @return null|string
         */
        function onlyPhone()
        {
            $phone = $this->phone;
            $len = strlen($phone);

            if ($phone == "" || is_null($phone)) return "";
            if ($len < 8 || Phone::validate($phone)->isNational()) return $phone;

            if (Phone::validate($phone)->isResidential()) return $this->base();
            if (Phone::validate($phone)->isCell()) return substr($phone, -9);

            return $phone;
        }

        /**
         * Retorna o número completo: paise+ddd+telefone
         * @return string
         */
        function full()
        {
            $country = $this->countryCode();
            $ddd = $this->ddd();
            $phone = $this->onlyPhone();
            return "{$country}{$ddd}{$phone}";
        }

        /**
         * Retorna a base do número, ou seja, os 8 ultimos
         * @return null|string
         */
        function base()
        {
            $phone = $this->phone;
            if (Phone::validate($phone)->isNational()) return $phone;
            return substr($phone, -8);
        }

        /**
         * Retorna o código do país
         * @return int|string
         */
        function countryCode()
        {
            $phone = Phone::format($this->phone)->getOnlyNumbers();
            $len = strlen($phone);
            if (Phone::validate($phone)->isCell()) {
                //LEN VALID = 13;
                if ($len < 13) return $this->country;
                return substr($phone, -13, 2);
            } elseif (Phone::validate($phone)->isResidential()) {
                //LEN VALID = 12;
                if ($len < 12) return $this->country;
                return substr($phone, -12, 2);
            }

            return $this->country;
        }

        /**
         * Retorna o Estado abreviado de um DDD, ex. 98 => MA
         * @return int|string
         */
        function region()
        {
            $ddd = $this->ddd();
            return Region::getRegion($ddd);
        }

        /**
         * Retorna o estado com nome completo de um ddd, ex. 98 ==> Maranhão
         * @return mixed|string
         */
        function regionFullName()
        {
            return Region::getRegionFullName($this->region());
        }

        /**Retorna o prefixo com base na ANATEL
         * Exemplo:
         * 98989114437 ==> 9898911
         * 9832225555  ==> 983222
         *
         * O prefixo é os 7 primeiros números quando é celular com nono digito e 6 para fixos ou celulares sem
         * nono digito
         *
         * @return string
         */
        function prefix(){
            $f = $this->phoneWithDdd();
            $len = strlen($f);
            return substr($f, 0, $len - 4);
        }

    }

