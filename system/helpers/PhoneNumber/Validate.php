<?php

    namespace PhoneNumber;

    class Validate extends Gbl
    {
        private $phone = null;

        function __construct($phone)
        {
            parent::__construct($phone);
            $this->phone = $this->getPhone();
        }

        /**
         * Para ser um telefone válido, deve ser celular ou fixo
         * @return bool|array
         */
        function isValid()
        {
            $data_cell = $this->isCell();
            $data_residential = $this->isResidential();
            $data_national = $this->isNational();

            if ($this->type() == Type::PHONETYPE_UNI) {
                return ($data_cell || $data_residential || $data_national);
            } else {
                return array_merge($data_cell, $data_residential, $data_national);
            }
        }

        /**
         * Verifica se é um número ou array de números é celular válido. Todos os celulares
         * devem possui "nono dígito".
         *
         * @return bool|array --> caso a validação seja para múltiplos números, a resposta é com array
         */
        function isCell()
        {
            //Retorna os dados
            $data = $this->getType();

            //Verifica os números que são celulares
            if (!isset($data['cell'])) {
                if ($this->type() == Type::PHONETYPE_UNI) return false;
                return array();
            }
            $count = count($data['cell']);

            return ($count == 1 && $this->type() == Type::PHONETYPE_UNI) ? true : $data['cell'];

        }

        /**
         * Verifica se o telefone é um número fixo.
         * @return bool
         */
        function isResidential()
        {
            //Retorna os dados
            $data = $this->getType();

            //Verifica os números que são celulares
            if (!isset($data['residential'])) {
                if ($this->type() == Type::PHONETYPE_UNI) return false;
                return array();
            }
            $count = count($data['residential']);
            return ($count == 1 && $this->type() == Type::PHONETYPE_UNI) ? true : $data['residential'];
        }

        /**
         * Verifica se o telefone é um número nacional, tipo:
         * 40042222
         * 08002050
         * @return bool
         */
        function isNational()
        {
            //Retorna os dados
            $data = $this->getType();

            //Verifica os números que são celulares
            if (!isset($data['national'])) {
                if ($this->type() == Type::PHONETYPE_UNI) return false;
                return array();
            }
            $count = count($data['national']);
            return ($count == 1 && $this->type() == Type::PHONETYPE_UNI) ? true : $data['national'];
        }


        /**
         * Retorna um array com os números informados e devolve seus tipos
         * @return array
         */
        function getType()
        {
            $data_phone = $this->phone;
            if ($data_phone == "" || is_null($data_phone)) return 'undefined';

            if (!is_array($data_phone)) $data_phone = array($data_phone);

            $listDddValid = Region::listDddValid();
            $str_ddd = implode(",", array_values($listDddValid));

            $result = array();

            foreach ($data_phone as $phone) {

                $is_national = 0;
                $is_cell = 0;
                $is_fixed = 0;

                if (strlen($phone) >= 8) {
                    $phone = ltrim($phone, "+"); //remove o "+" do ínicio

                    $exp_cell = '/(?(?=^([' . $str_ddd . ']{2}9[1-9]{1}[0-9]{7})$)(^[0-9+]?[' . $str_ddd . ']{2}9[1-9]{1}[0-9]{7})|^(\d{3,})*(9[1-9]{1}[0-9]{7}))$/';
                    $exp_fixed = '/(?(?=^([' . $str_ddd . ']{2}[2-5]{1}[0-9]{7})$)(^[0-9+]?[' . $str_ddd . ']{2}[2-5]{1}[0-9]{7})|^(\d{3,})*([2-5]{1}[0-9]{7}))$/';

                    $is_cell = preg_match($exp_cell, $phone);
                    $is_fixed = preg_match($exp_fixed, $phone);

                } else {
                    $is_national = 1;
                }

                if ($is_cell === 1) {
                    $result['cell'][] = $phone;
                } elseif ($is_fixed === 1) {
                    $result['residential'][] = $phone;
                } elseif ($is_national === 1) {
                    $result['national'][] = $phone;
                } else {
                    $result['undefined'][] = $phone;
                }
            }

            return $result;
        }
    }