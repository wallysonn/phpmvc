<?php

    namespace DateTime;

    class Compare extends Gbl
    {
        private $date1 = null;
        private $date2 = null;

        function __construct($date1, $date2)
        {
            $this->date1 = Date::format(parent::stringToDate($date1))->brToUs(); //datas no padrão americano
            $this->date2 = Date::format(parent::stringToDate($date2))->brToUs(); //datas no padrão americano
            return $this;
        }

        /**
         * Calcula o intervalo entre as duas datas
         * @return bool|\DateInterval
         */
        function getInterval()
        {

            $d1 = new \DateTime($this->date1);
            $d2 = new \DateTime($this->date2);
            return $d1->diff($d2);
        }

        /**
         * Calcula quantos dias existem entre as duas datas
         * @return int
         */
        function countDay()
        {
            return $this->getInterval()->format("%R%a days");
        }

        /**
         * Calcula quantos meses existem entre as duas datas
         * @return int
         */
        function countMonth()
        {
            return $this->getInterval()->m;
        }

        /**
         * Calcula quantos anos existem entre as duas datas
         * @return int
         */
        function countYear()
        {
            return $this->getInterval()->y;
        }
    }
