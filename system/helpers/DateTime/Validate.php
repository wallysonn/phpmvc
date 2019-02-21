<?php

    namespace DateTime;

    class Validate extends Gbl
    {
        private $date = null;

        function __construct($date)
        {
            $date = parent::stringToDate($date);
            $this->date = $date;
            return $this;
        }

        /**
         * Verifica se é uma data válida
         * @return bool
         */
        function isDate()
        {
            try {

                $date = ($this->isDateBr($this->date)) ? Date::Format($this->date)->brToUs() : $this->date;

                $count = substr_count($date, ":");
                $format = ($count > 0) ? (($count == 1) ? "Y-m-d H:i" : "Y-m-d H:i:s") : "Y-m-d";
                $newDate = \DateTime::createFromFormat($format, $date);
                return ($newDate && $newDate->format($format) === $date);

            } catch (\Exception $e) {
                return false;
            }
        }

        /**
         * Veriica se é uma data no padrão brasileiro
         * @return bool
         */
        function isDateBr()
        {
            try {
                $date = $this->date;
                $count = substr_count($date, ":");
                $format = ($count > 0) ? (($count == 1) ? "d/m/Y H:i" : "d/m/Y H:i:s") : "d/m/Y";
                $newDate = \DateTime::createFromFormat($format, $date);
                return ($newDate && $newDate->format($format) === $date);
            } catch (\Exception $e) {
                return false;
            }
        }

    }