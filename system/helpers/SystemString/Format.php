<?php
    namespace SystemString;

    class Format
    {
        private $text = '';

        function __construct($text)
        {
            $this->text = $text;
        }

        /**
         * Remove todos os acentos e "ç".
         * @return mixed
         */
        function removeAccents()
        {
            return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/",
                                      "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/",
                                      "/(ñ)/", "/(Ñ)/", "/ç/", "/Ç/"
            ), explode(" ", "a A e E i I o O u U n N c C"), $this->text);
        }

        function email()
        {
            //remove os acentos
            $text = $this->removeAccents();

            //transforma para minusculos
            $text = StringText::convert($text)->toLower();

            //Remove caracteres que não são aceitos no e-mail
            $text = trim($text);
            $text = rtrim($text, ".");

            $text = preg_replace('/[^0-9a-z\.\-_@]/', '', $text);

            if (!StringText::validate($text)->isEmail()) return "";
            return $text;
        }

        function removeRepeatedSpace()
        {
            return preg_replace('/( ){2,}/', '$1', $this->text);
        }

        function mask($mask)
        {
            $maskared = '';
            $k = 0;
            for ($i = 0; $i <= strlen($mask) - 1; $i++) {
                if ($mask[$i] == '#') {
                    if (isset($val[$k]))
                        $maskared .= $val[$k++];
                } else {
                    if (isset($mask[$i]))
                        $maskared .= $mask[$i];
                }
            }

            return $maskared;

        }
    }