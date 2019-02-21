<?php

    namespace DateTime;

    class Gbl
    {
        protected function stringToDate($stringDate)
        {
            if (is_array($stringDate) || $stringDate == "" || $stringDate == null) return null;
            $stringDate = strtolower($stringDate);

            switch ($stringDate) {
                case 'now':
                    return date('Y-m-d H:i:s');
                    break;

                case 'year':
                case 'y':
                    return date('Y');
                    break;

                case 'month':
                case 'm':
                    return date('m');
                    break;

                case 'day':
                case 'd':
                    return date('d');
                    break;

                case 'date':
                    return date('Y-m-d');
                    break;

                case 'time':
                    return date('H:i:s');
                    break;

                case 'hour':
                case 'h':
                    return date('H');
                    break;

                case 'minute':
                case 'i':
                    return date('i');
                    break;

                case 'second':
                case 's':
                    return date('s');
                    break;

            }

            return $stringDate;

        }
    }