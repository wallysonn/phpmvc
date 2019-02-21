<?php

    class Xml
    {

        //more informations: http://www.itsalif.info/content/php-5-domdocument-creating-basic-xml

        private $nodeElem = null;
        private $doc = null;
        public $fileName = "";
        public $pathOutput = "";

        function __construct($nodeElem, array $nodeElemAttributes = null, $version = "1.0", $encode = "UTF-8", $formatOutput = true)
        {

            $this->doc = new DOMDocument($version, $encode);
            $this->doc->formatOutput = $formatOutput;
            $this->nodeElem = $this->doc->createElement($nodeElem);
            if ($nodeElemAttributes !== null) {
                foreach ($nodeElemAttributes as $attr => $value) {
                    $this->nodeElem->setAttribute($attr, $value);
                }
            }

        }

        function  createElement(array $element)
        {
//        $element = array(
//            'message' => array(
//                'id' => '24',
//                'from' => 'walison gomes'
//            )
//        );
            foreach ($element as $elm => $value) {
                if (!is_array($value)) {
                    $node = $this->doc->createElement($elm, $value);
                    $this->nodeElem->appendChild($node);
                } else {
                    $node2 = $this->doc->createElement($elm);
                    foreach ($value as $childElm => $childValue) {
                        $childValue = str_replace(array('&'), array('&amp;'), $childValue);
                        $subElm = $this->doc->createElement($childElm, $childValue);
                        $node2->appendChild($subElm);
                    }

                    $this->nodeElem->appendChild($node2);

                }
            }

            return $this;

        }

        private function getPathOutput()
        {
            $path = $this->pathOutput;
            if ($path !== "") {
                $path = rtrim(trim($this->pathOutput), "/");
            }

            return $path;
        }


        function readFile($file)
        {
            clearstatcache();
            $data = simplexml_load_file($file);

            return xml2array($data);
        }

        function  create()
        {
            if ($this->fileName == "") $this->fileName = md5(time());
            $file = str_replace(".xml", "", $this->fileName) . ".xml";

            $this->doc->appendChild($this->nodeElem); //Inclui os elementos ao documento
            $this->doc->saveXML(); //Monta o corpo do documento

            if ($this->getPathOutput() !== "") $file = $this->getPathOutput() . "/" . $file;

            return $this->doc->save($file);
        }


    }