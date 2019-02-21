<?php

    namespace DateTime;

    class Format extends Gbl
    {
        private $date = null;

        function __construct($date)
        {
            $date = parent::stringToDate($date);

            $this->date = $date;
            return $this;
        }

        /**
         * Converte a data do padrão brasileiro para o americano
         * @return null|string
         */
        function brToUs()
        {
            try {

                $date = $this->date;

                if (!Date::Validate($date)->isDateBr()) {
                    if (!Date::Validate($date)->isDate()) return null;
                    return $date;
                }

                $count = substr_count($date, ":");
                $format = ($count > 0) ? (($count == 1) ? "d/m/Y H:i" : "d/m/Y H:i:s") : "d/m/Y";
                $usFormat = ($count > 0) ? (($count == 1) ? "Y-m-d H:i" : "Y-m-d H:i:s") : "Y-m-d";
                $newDate = \DateTime::createFromFormat($format, $date);
                return $newDate->format($usFormat);

            } catch (\Exception $e) {
                return null;
            }
        }

        /**
         * Converte a data do padrão americano para o brasileiro.
         *
         * @return null|string
         */
        function usToBr()
        {
            try {

                $date = $this->date;

                if (Date::validate($date)->isDateBr()) return $date;
                if (!Date::validate($date)->isDate()) return null;

                $count = substr_count($date, ":");
                $format = ($count > 0) ? (($count == 1) ? "d/m/Y H:i" : "d/m/Y H:i:s") : "d/m/Y";
                $usFormat = ($count > 0) ? (($count == 1) ? "Y-m-d H:i" : "Y-m-d H:i:s") : "Y-m-d";
                $newDate = \DateTime::createFromFormat($usFormat, $date);
                return $newDate->format($format);

            } catch (\Exception $e) {
                return null;
            }
        }

        /**
         * Altera o formato da data conforme string do usuário
         *
         * @param $format
         *
         * @return false|null|string
         */
        function toString($format)
        {
            try {
                $date = $this->brToUs($this->date);
                return date($format, strtotime($date));
            } catch (Exception $e) {
                return null;
            }
        }
    }