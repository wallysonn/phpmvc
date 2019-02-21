<?php

    namespace PhoneNumber;

    class Format extends Gbl
    {
        private $phone = null;

        function __construct($phone)
        {
            parent::__construct($phone);
            $this->phone = $this->getPhone();
        }

        function getOnlyNumbers()
        {
            return preg_replace("/[^0-9]/", "", $this->phone);
        }

        private function getMask($val, $mask)
        {
            $maskared = '';
            $k = 0;
            for ($i = 0; $i <= strlen($mask) - 1; $i++) {
                if ($mask[$i] == '#') {
                    if (isset($val[$k]))
                        $maskared .= $val[$k++];
                } else {
                    if (isset($mask[$i]))
                        $maskared .= $mask[$i];
                }
            }

            return $maskared;
        }

        function mask()
        {
            $data_phone = $this->phone;

            if ($this->type() == Type::PHONETYPE_UNI) {
                $data_phone = array($data_phone); //transforma em um array
            }

            $ret = array();

            foreach ($data_phone as $phone) {

                $phone = Phone::format($phone)->getOnlyNumbers();
                $phone = Phone::get($phone)->phoneWithDdd();
                if (Phone::validate($phone)->isCell()) {
                    $ret[] = $this->getMask($phone, "(##) #####-####");
                } elseif (Phone::validate($phone)->isResidential()) {
                    $ret[] = $this->getMask($phone, "(##) ####-####");
                } else {
                    if (strlen($phone) == 8) {
                        $ret[] = $this->getMask($phone, "####-####");
                    } else {
                        $ret[] = $phone;
                    }
                }
            }

            return ($this->type() == Type::PHONETYPE_UNI) ? $ret[0] : $ret;


        }

    }