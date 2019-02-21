<?php

    namespace PhoneNumber;

    abstract class Region
    {
        private static $regionDdd = array(
            'SP' => array(11, 12, 13, 14, 15, 16, 17, 18, 19),
            'RJ' => array(21, 22, 24),
            'ES' => array(27, 28),
            'MG' => array(31, 32, 33, 34, 35, 37, 38),
            'PR' => array(41, 42, 43, 44, 45, 46),
            'SC' => array(47, 48, 49),
            'RS' => array(51, 53, 54, 55),
            'DF' => array(61),
            'GO' => array(62, 64),
            'TO' => array(63),
            'MT' => array(65, 66),
            'MS' => array(67),
            'AC' => array(68),
            'RO' => array(69),
            'BA' => array(71, 73, 74, 75, 77),
            'SE' => array(79),
            'PE' => array(81, 87),
            'AL' => array(82),
            'PB' => array(83),
            'RN' => array(84),
            'CE' => array(85, 88),
            'PI' => array(86, 89),
            'PA' => array(91, 93, 94),
            'AM' => array(92, 97),
            'RR' => array(95),
            'AP' => array(96),
            'MA' => array(98, 99)
        );
        private static $state     = array(
            "AC" => "Acre",
            "AL" => "Alagoas",
            "AM" => "Amazonas",
            "AP" => "Amapá",
            "BA" => "Bahia",
            "CE" => "Ceará",
            "DF" => "Distrito Federal",
            "ES" => "Espírito Santo",
            "GO" => "Goiás",
            "MA" => "Maranhão",
            "MT" => "Mato Grosso",
            "MS" => "Mato Grosso do Sul",
            "MG" => "Minas Gerais",
            "PA" => "Pará",
            "PB" => "Paraíba",
            "PR" => "Paraná",
            "PE" => "Pernambuco",
            "PI" => "Piauí",
            "RJ" => "Rio de Janeiro",
            "RN" => "Rio Grande do Norte",
            "RO" => "Rondônia",
            "RS" => "Rio Grande do Sul",
            "RR" => "Roraima",
            "SC" => "Santa Catarina",
            "SE" => "Sergipe",
            "SP" => "São Paulo",
            "TO" => "Tocantins");

        static function getRegion($ddd)
        {
            $data = self::$regionDdd;
            foreach ($data as $state => $array_ddd) {
                if (in_array($ddd, $array_ddd)) return $state;
            }

            return "undefined";

        }

        static function getDdd($region)
        {
            $data = self::$regionDdd;
            return (isset($data[$region])) ? $data[$region] : null;
        }

        static function getRegionFullName($region)
        {
            $data = self::$state;
            return (isset($data[$region])) ? $data[$region] : "undefined";
        }

        static function listDddValid()
        {
            $data = self::$regionDdd;
            $ret = array();
            foreach ($data as $state => $array_ddd) {
                $ret = array_merge($ret, $array_ddd);
            }

            return $ret;
        }
    }
