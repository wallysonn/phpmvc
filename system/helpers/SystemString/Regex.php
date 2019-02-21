<?php

    namespace SystemString;
    class Regex
    {
        private $text = '';

        function __construct($text)
        {
            $this->text = $text;
        }

        function replaceBetween($start, $end, $new)
        {
            $source = $this->text;
            return preg_replace('#(' . preg_quote($start) . ')(.*?)(' . preg_quote($end) . ')#si', '$1' . $new . '$3',
                $source);
        }

        function replaceFromTo($start, $end, $new)
        {
            $source = $this->text;
            return preg_replace("/" . preg_quote($start) . ".*?(?=\\s*" . preg_quote($end) . "|\\)|$)/i", $new, $source);
        }

        function getBetween($start, $end)
        {
            $string = $this->text;
            $end = (empty($end)) ? "$" : "$end|$";
            preg_match("/($start)(.*?)($end)/i", $string, $result);

            return (isset($result[2])) ? $result[2] : "";
        }

    }