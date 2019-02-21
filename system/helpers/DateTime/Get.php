<?php

    namespace DateTime;

    use SystemString\StringText;

    class Get extends Gbl
    {
        private $date  = null;
        private $valid = true;

        function __construct($date)
        {
            $date = parent::stringToDate($date);
            $this->date = $date;
            $this->valid = Date::validate($date)->isDate();

            return $this;
        }

        function getDate()
        {
            $dt = $this->date;

            if (Date::validate($dt)->isDate()) return $dt;

            preg_match("/(.*?)(\[)/i", $dt, $destination);
            $date = (isset($destination[1]) ? $destination[1] : $dt);
            $date = trim($date);

            preg_match("/(\[)(.*?)(\])/i", $dt, $destination);
            $expression = (isset($destination[2]) ? $destination[2] : '');
            $expression = trim($expression);

            $date = strtolower($date);
            $date = ($date == 'now' || $date == 'date') ? date('Y-m-d') : Date::format($date)->brToUs();

            if (Date::validate($date)->isDate()) return ($expression == "") ? $date : Date::modify($date)->modify($expression, 'Y-m-d');

            return null;

        }

        function monthName()
        {
            try {

                if (!$this->valid) throw new \Exception(sprintf(_LANG_INVALID_DATE, $this->date));

                return $this->countryFormat("%b");


            } catch (\Exception $e) {

                return null;
            }
        }

        function countryFormat($format)
        {
            try {

                if (!$this->valid) throw new \Exception(sprintf(_LANG_INVALID_DATE, $this->date));

                $str = strftime($format, strtotime($this->date));

                if(!mb_check_encoding($str,'utf-8')) $str=utf8_encode($str);
                return $str;

            } catch (\Exception $e) {

                return null;
            }
        }

        function monthFullName()
        {
            try {

                if (!$this->valid) throw new \Exception(sprintf(_LANG_INVALID_DATE, $this->date));

                return $this->countryFormat("%B");

            } catch (\Exception $e) {

                return null;
            }
        }

        function month()
        {
            try {

                if (!$this->valid) throw new \Exception(sprintf(_LANG_INVALID_DATE, $this->date));

                return date('m', strtotime($this->date));

            } catch (\Exception $e) {

                return null;
            }
        }

        function day()
        {
            try {

                if (!$this->valid) throw new \Exception(sprintf(_LANG_INVALID_DATE, $this->date));

                return date('d', strtotime($this->date));

            } catch (\Exception $e) {

                return null;
            }
        }

        function year()
        {
            try {

                if (!$this->valid) throw new \Exception(sprintf(_LANG_INVALID_DATE, $this->date));

                return date('Y', strtotime($this->date));

            } catch (\Exception $e) {

                return null;
            }
        }

        function hour()
        {
            try {

                if (!$this->valid) throw new \Exception(sprintf(_LANG_INVALID_DATE, $this->date));

                return date('H', strtotime($this->date));

            } catch (\Exception $e) {

                return null;
            }
        }

        function minute()
        {
            try {

                if (!$this->valid) throw new \Exception(sprintf(_LANG_INVALID_DATE, $this->date));

                return date('i', strtotime($this->date));

            } catch (\Exception $e) {

                return null;
            }
        }

        function second()
        {
            try {

                if (!$this->valid) throw new \Exception(sprintf(_LANG_INVALID_DATE, $this->date));

                return date('s', strtotime($this->date));

            } catch (\Exception $e) {

                return null;
            }
        }

        function lastDay()
        {
            try {

                if (!$this->valid) throw new \Exception(sprintf(_LANG_INVALID_DATE, $this->date));

                return date('t', strtotime($this->date));

            } catch (\Exception $e) {

                return null;
            }
        }

        function lastDate($format = "d/m/Y")
        {
            try {
                $format = str_replace("d", "t", $format);
                if (!$this->valid) throw new \Exception(sprintf(_LANG_INVALID_DATE, $this->date));

                return date($format, strtotime($this->date));

            } catch (\Exception $e) {

                return null;
            }
        }

        function firstDate($format = "d/m/Y")
        {
            try {
                $format = str_replace(array("d/", "-d"), "", $format);
                if (!$this->valid) throw new \Exception(sprintf(_LANG_INVALID_DATE, $this->date));

                return ((StringText::find($format)->contains("/")) ? "01/" . (date($format, strtotime($this->date))) : (date($format, strtotime($this->date))) . "-01");

            } catch (\Exception $e) {

                return null;
            }
        }

        function diff($compare, $result = 'days')
        {

            $date_start = Date::format($this->date)->brToUs();
            $date_end = $compare;

            $result = strtolower($result);

            $date1 = new \DateTime($date_start);

            $date2 = new \DateTime($date_end);
            $diff = $date2->diff($date1);

            switch ($result) {
                case 'days':
                case 'day':
                case 'd':
                    //return $diff->d;
                    return $diff->days;
                    break;

                case 'year':
                case 'y':
                    return $diff->y;
                    break;

                case 'month':
                case 'm':
                    return $diff->m;
                    break;

                case 'hour':
                case 'hours':
                case 'h':
                    return $diff->h;
                    break;

                case 'minute':
                case 'min':
                case 'i':
                case 'minutes':
                    return $diff->i;
                    break;

                case 'second':
                case 'seconds':
                case 's':
                case 'sec':
                    $diff->s;
                    break;
            }


            return 0;
        }

        function age()
        {
            $dateBirth = Date::format($this->date)->brToUs();

            return (Date::validate($dateBirth)->isDate()) ? $this->diff(date('Y-m-d'), 'year') : null;
        }

    }

