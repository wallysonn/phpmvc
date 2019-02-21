<?php

    namespace PhoneNumber;

    use SystemString\StringText;

    class Gbl
    {
        private $phone = null;

        function __construct($phone)
        {
            if (!is_array($phone)) {
                $phone = str_replace(";", ",", $phone);
                if (StringText::find($phone)->contains(",")) $phone = explode(",", $phone);
            }

            $this->phone = $phone;
        }

        protected function getPhone()
        {
            return $this->phone;
        }

        protected function type()
        {
            $phone = $this->phone;

            return (is_array($phone)) ? Type::PHONETYPE_MULT : Type::PHONETYPE_UNI;

        }
    }