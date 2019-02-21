<?php
    namespace DateTime;

    /**
     * Gerenciamento de datas
     */
    class Date
    {

        /**Converte a data para um padrão específico
         *
         * @param $date
         *
         * @return Format
         */
        static function format($date)
        {
            return new Format($date);
        }

        /**
         * Compara se a data é válida
         *
         * @param $date
         *
         * @return Validate
         */
        static function validate($date)
        {
            return new Validate($date);
        }

        /**
         * Retorna a data atual com um formato específico
         *
         * @param string $format
         *
         * @return false|string
         */
        static function now($format = "d/m/Y H:i:s")
        {
            return date($format);
        }

        /**
         * Modifica a data
         *
         * @param $date
         *
         * @return Modify
         */
        static function modify($date)
        {
            return new Modify($date);
        }

        /**
         * Retorna informações da data
         *
         * @param $date
         *
         * @return Get
         */
        static function get($date)
        {
            return new Get($date);
        }

        /**
         * Operações para mês
         *
         * @param $month
         *
         * @return Month
         */
        static function month($month)
        {
            return new Month($month);
        }

        /**
         * @param $date
         *
         * @return Find
         */
        static function find($date)
        {
            return new Find($date);
        }

        /**
         * Retorna um array com os meses
         * @param string $type
         *
         * @return array
         */
        static function listMonth($type = 'm')
        {
            $list = ($type == 'm') ? _LANG_MONTH_MINNAME : _LANG_MONTH_FULLNAME;
            $data = explode(",", $list);
            $ret = array();
            foreach ($data as $k => $m) {
                $ret[$m] = $k + 1;
            }
            return $ret;
        }

        /**
         * @param $date1
         * @param $date2
         *
         * @return Compare
         */
        static function compare($date1, $date2){
            return new Compare($date1,$date2);
        }
    }

