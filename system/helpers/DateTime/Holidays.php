<?php

    namespace DateTime;

    class Holidays
    {
        /**
         * Calcula os feriados nacionais do Brasil
         *
         * @param null $year
         *
         * @return array
         */
        static function getNational($year = null)
        {

            $year = ($year === null) ? intval(date('Y')) : intval($year);

            $fixedDay = 86400;
            $calculateDates = array();
            $calculateDates['easter'] = easter_date($year);
            $calculateDates['good_friday'] = $calculateDates['easter'] - (2 * $fixedDay);
            $calculateDates['carnival'] = $calculateDates['easter'] - (47 * $fixedDay);
            $calculateDates['corpus_cristi'] = $calculateDates['easter'] + (60 * $fixedDay);

            return array(
                '01/01', //Confraternização Universal
                date('d/m', $calculateDates['carnival']), //Carnaval
                date('d/m', $calculateDates['good_friday']), //Sexta-Feira Santa
                date('d/m', $calculateDates['easter']), //Páscoa
                '21/04', //Tiradentes
                '01/05', //Dia do trabalho
                #date('d/m',$datas['corpus_cristi']),
                '07/09', //Independencia do Brasil
                '12/10', //Nossa Sra Aparecida
                '02/11', //Finados
                '15/11', //Proclamação da República
                '25/12', //Natal
            );
        }
    }