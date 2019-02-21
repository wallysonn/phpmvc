<?php

    namespace DateTime;

    class Find extends Gbl
    {
        private $date = null;

        function __construct($date)
        {
            $this->date = $this->stringToDate($date);
        }

        /**
         * Verifica se a data está entre duas datas.
         *
         * @param        $minDate
         * @param        $maxDate
         * @param string $ignore ==> este parametro serve para ignorar as datas iniciais e final
         *
         * veja:
         * -f => ignora a data inicial (first) @minDate
         * -l => ignora a data final (last) @maxDate
         * -fl => ignora as datas inicial e final
         *
         * @return bool
         */
        function isBetween($minDate, $maxDate, $ignore = '')
        {
            if (!Date::validate($minDate)->isDate()) return false;
            if (!Date::validate($maxDate)->isDate()) return false;

            $date = $this->date;

            if (!Date::validate($date)->isDate()) return false;

            $minDate = Date::format($minDate)->brToUs();
            $maxDate = Date::format($maxDate)->brToUs();
            $date = Date::format($date)->brToUs();

            $ignore = strtolower($ignore);

            if ($ignore == "") return ($date >= $minDate && $date <= $maxDate);
            switch ($ignore) {
                case '-f':
                    return ($date > $minDate && $date <= $maxDate);
                    break;
                case '-l':
                    return ($date >= $minDate && $date < $maxDate);
                    break;
                case '-fl':
                case '-lf':
                    return ($date > $minDate && $date < $maxDate);
                    break;
            }

            return false;

        }
    }