<?php

    namespace NumberFormat;

    class Number
    {
        /**
         * @param $number
         *
         * @return Format
         */
        static function format($number)
        {
            return new Format($number);
        }

        /**
         * @param $number
         *
         * @return Get
         */
        static function get($number)
        {
            return new Get($number);
        }

        static function percentageBetweenTwoNumbers($start, $end, $decimal = 1)

        {

            $start = (float)$start;
            $end = (float)$end;

            if ($start == 0) return self::format($end)->toDatabase()->decimal($decimal);

            return self::format((($end / $start) - 1) * 100)->toDatabase()->decimal($decimal);

        }

        static function calcInterest($dateVenc ){

        }
    }
