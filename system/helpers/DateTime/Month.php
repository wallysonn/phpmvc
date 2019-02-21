<?php

    namespace DateTime;

    class Month extends Gbl
    {
        private $month = null;
        private $date  = null;

        /**
         * Month constructor.
         *
         * @param $month --> pode ser um mês ou uma data
         */
        function __construct($month)
        {
            if (is_object($month) || is_array($month)) $month = 0;
            if (Date::validate($month)->isDate()) {
                $month = parent::stringToDate($month);
                $this->date = $month;
                $month = Date::get($month)->month();
            } else {
                $this->date = date("Y-") . $month . date("-d");
            }

            $this->month = $month;
        }

        /**
         * Verifica se a data é numerica ou texto
         * @return string
         */
        private function type()
        {
            if (is_numeric($this->month)) return 'numeric';
            return 'string';
        }

        /**Array com os nomes dos meses
         *
         * @param string $viewType
         *
         * @return array
         */
        private function monthNames($viewType = 'm')
        {
            $min = explode(",", _LANG_MONTH_MINNAME);
            $full = explode(",", _LANG_MONTH_FULLNAME);

            return ($viewType == "m") ? $min : $full;

        }

        /**Converte o mês numerico para string.
         * Ex. 01 => jan (parametro m) ou janeiro (parametro M)
         *
         * @param string $viewType ==> m ou M
         *
         * @return false|int|null|string
         */
        function toString($viewType = "m")
        {
            $data = $this->monthNames($viewType);
            $undefined = "undefined";

            if ($this->type() == "numeric") {
                $m = $this->month - 1;
                return (isset($data[$m])) ? $data[$m] : $undefined;
            } else {
                if (in_array(strtolower($this->month), array_map('strtolower', $data))) return $this->month;
            }

            return $undefined;
        }

        /**Converte o mês atual caso texto, para numerico
         * Ex. jan => 01
         * @return false|int|null|string
         */
        function toNumeric()
        {
            if ($this->type() == "string") {

                $data1 = array_map('strtolower', array_flip($this->monthNames('m')));
                $data2 = array_map('strtolower', array_flip($this->monthNames('M')));

                $m = 0;

                $month = strtolower($this->month);

                if (isset($data1[$month])) {
                    $m = $data1[$month];
                    $m = $m + 1;
                } elseif (isset($data2[$month])) {
                    $m = $data2[$month];
                    $m = $m + 1;
                }

                if ($m < 10 && $m > 0) $m = "0{$m}";

                return $m;

            }

            return $this->month;
        }

        /**
         * @return false|int|string
         */
        function countDays()
        {
            try {

                return date('t', strtotime($this->date));

            } catch (\Exception $e) {
                return 0;
            }
        }

    }