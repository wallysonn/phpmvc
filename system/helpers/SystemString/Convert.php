<?php

    namespace SystemString;
    class Convert
    {
        private $text   = '';
        private $concat = array('de', 'dos', 'da', 'da', 'e');

        function __construct($text)
        {
            $text = strip_tags($text); //remove tags html
            $this->text = $text;
        }

        /**
         * Converte para minúsculo
         * @return string
         */
        function toLower()
        {
            return strtr(mb_strtolower($this->text, "UTF-8"), 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß', 'àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ');
        }

        /**
         * Converte para maiúsculos
         * @return string
         */
        function toUpper()
        {
            return strtr(mb_strtoupper($this->text, "UTF-8"), 'àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ', 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß');
        }

        /**
         * Transforma para maiusculos a primeira letra da frase
         * @return string
         */
        function toUcFirst()
        {
            return strtr(ucfirst($this->text), 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß', 'àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ');
        }

        /**
         * Transforma para maiusculo a primeira letra de cada palavra
         * @return string
         */
        function toUcWord()
        {
            return $palavra = strtr(ucwords($this->text), 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÜÚÞß', 'àáâãäåæçèéêëìíîïðñòóôõö÷øùüúþÿ');
        }

        /**
         * Transforma o texto para nome de pessoas, com iniciais maisculas e
         * removendo espaços extras
         *
         * @return mixed|string
         */
        function toPeopleName()
        {
            $text = trim($this->text);
            $this->text = $text;

            //transforma para minusculo
            $text = $this->toLower();
            $this->text = $text;

            //Coloca as iniciais em maiúsculos
            $text = $this->toUcWord();

            //Remove espaços repetidos
            $text = StringText::format($text)->removeRepeatedSpace();

            return str_replace(array(
                ' De ', ' Dos ', ' Da ', ' Da ', ' E '
            ), array(
                ' de ', ' dos ', ' da ', ' da ', ' e '
            ), $text);
        }

        /**
         * @return mixed
         */
        function toOnlyText()
        {
            return preg_replace("/[^a-zA-Z]+/", "", $this->text);
        }

        function toFulltextExpression($explodeCharacter = " ",
                                      $expression = "+%s"
    )
        {
            $t = $this->text;

            if (StringText::find($t)->contains("'") || StringText::find($t)->contains("'")){
                $t = str_replace("'",'"',$t);
                return $t;
            }

            if (empty($explodeCharacter)) $explodeCharacter = " ";
            $dataSpl = explode($explodeCharacter, $t);

            return implode($explodeCharacter, array_map(function ($item) use ($expression) {
                return sprintf($expression, $item);
            }, $dataSpl));
        }

        function toMiddleName()
        {
            $name = $this->toPeopleName();
            $split = explode(" ", $name);
            $first = reset($split);
            $last = end($split);

            return ($first == $last) ? $first : "{$first} {$last}";
        }

        function toAbrevName()
        {
            $name = $this->toPeopleName();
            $split = explode(" ", $name);
            $strName = "";

            foreach ($split as $k => $sname) {
                $sname = trim($sname);
                if ($k == 0) {
                    $strName = $sname;
                    continue;
                }

                if ($k == count($split) - 1) {
                    $strName .= sprintf("%s%s", " ", $sname);
                } else {
                    $strName .= (in_array($sname, $this->concat)) ? sprintf("%s%s", " ", $sname) : sprintf("%s%s%s", " ", substr($sname, 0, 1), ".");
                }
            }

            return $strName;

        }

        function SqlStringToObject()
        {

            $sql = $this->text;
            $sql = str_replace(array("\r\n", "\t", "  ", "\r", "\n"), " ", $sql);

            $ret = new SqlString();
            $ret->select = StringText::regex($sql)->getBetween("select", "from");
            $ret->from = StringText::regex($sql)->getBetween("from", "inner|left|right|join|where");
            $ret->where = StringText::regex($sql)->getBetween("where", "group by|having|order by|limit");
            $ret->groupBy = StringText::regex($sql)->getBetween("group by", "having|order by|limit");
            $ret->having = StringText::regex($sql)->getBetween("having", "order by|limit");
            $ret->orderBy = StringText::regex($sql)->getBetween("order by", "limit");
            $ret->limit = StringText::regex($sql)->getBetween("limit", "");

            return $ret;
        }


    }