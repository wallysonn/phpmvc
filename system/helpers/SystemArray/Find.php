<?php

    namespace SystemArray;

    class Find
    {
        private $array = array();

        function __construct(array $array) { $this->array = $array; }


        /**
         * Retorna um array simples filtrando uma determinada coluna
         *
         * @param $column
         *
         * @return array
         */
        function dataToArray($column)
        {
            return array_column($this->array, $column);
        }

        /**
         * Executa uma função em todos os elementos do array
         *
         * @param $function
         *
         * @return array
         */
        function executeFunction($function)
        {
            return array_map($function, $this->array);
        }

        function executeMethod($class, $method)
        {
            try {

                $param = array(
                    'class'  => $class,
                    'method' => $method
                );

                if (!class_exists($class)) throw new \Exception(systemDirectMail(_LANG_CLASS_NOT_EXISTS_, $param));
                if (!method_exists(new $class, $method)) throw new \Exception(systemDirectMail(_LANG_METHOD_NOT_EXISTS_, $param));
                return array_map("{$class}::{$method}", $this->array);

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

    }