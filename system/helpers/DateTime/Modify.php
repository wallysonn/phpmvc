<?php

    namespace DateTime;

    class Modify extends Gbl
    {
        private $date = null;

        function __construct($date)
        {
            $date = parent::stringToDate($date);

            $this->date = $date;

            return $this;
        }

        /**
         * Decrementa a data conforme especificações abaixo:
         *
         * @param        $number (Número que corresponda a dia, mes, ano, hora etc)
         * @param        $period 'day, month, year, hour, minute, seconde etc...'
         * @param string $format --> Formato de saída
         *
         * @return null|string
         */
        function substract($number, $period, $format = "d/m/Y")
        {
            return $this->modify("-{$number} {$period}", $format);
        }

        /**
         * Incrementa a data conforme especificações abaixo:
         *
         * @param        $number (Número que corresponda a dia, mes, ano, hora etc)
         * @param        $period 'day, month, year, hour, minute, seconde etc...'
         * @param string $format --> Formato de saída
         *
         * @return null|string
         */
        function add($number, $period, $format = "d/m/Y")
        {
            return $this->modify("+{$number} {$period}", $format);
        }


        /**
         * Retorna o próximo dia útil, excluindo feriados e domingos
         *
         * @param string $format
         *
         * @return string
         */
        function nextBusinessDay($format = "d/m/Y")
        {
            return $this->managerBusinessDay("+", $format);
        }

        /**
         * Retorna o dia útil anterior excluindo feriados e domingo
         *
         * @param string $format
         *
         * @return string
         */
        function beforeBusinessDay($format = "d/m/Y")
        {
            return $this->managerBusinessDay("-", $format);
        }

        /**
         * Calcula o próximo ou o dia útil anterior
         *
         * @param string $type
         * @param string $format
         *
         * @return string
         */
        private function managerBusinessDay($type = "+", $format = "d/m/Y")
        {
            $dateus = Date::format($this->date)->brToUs();
            $date = new \DateTime($dateus);
            $newDate = $date->modify("{$type}1 days");

            do {
                $holidays = Holidays::getNational($newDate->format('Y'));
                $week = $newDate->format('w');
                if (in_array($newDate->format("d/m"), $holidays) || $week == 0) {
                    $newDate = $newDate->modify("{$type}1 days");
                } else {
                    break;
                }
            } while (true);

            return $newDate->format($format);

        }

        /**
         * Modifica a data
         *
         * @param        $string
         * @param string $format
         *
         * @return null|string
         */
        function modify($string, $format = "d/m/Y")
        {
            try {

                $date = new \DateTime($this->date);
                $date->modify($string);

                return $date->format($format);

            } catch (\Exception $e) {
                return null;
            }


        }

    }
