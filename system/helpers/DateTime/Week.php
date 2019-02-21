<?php
    namespace DateTime;

    class Week extends Gbl
    {
        private $date = null;

        function __construct($date)
        {
            $date = parent::stringToDate($date);
            $this->date = $date;

        }

        function current()
        {
            $w = date("w", strtotime($this->date));
            return $w;
        }

    }