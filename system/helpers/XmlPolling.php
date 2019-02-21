<?php

    class XmlPolling
    {

        private $xmlFileName = "";
        private $timesTamp = null;
        private $jsonEncode = true;
        private $path = array("../../app/poll/xml");
        private $_timeOutInMinute = 300; // 5 minutos
        private $_gropRegex = "";

        /**
         * @return string
         */
        public function getGropRegex()
        {
            return $this->_gropRegex;
        }

        /**
         * @param string $gropRegex
         */
        public function setGropRegex($gropRegex)
        {
            $this->_gropRegex = $gropRegex;

            return $this;
        }


        /**
         * @return int
         */
        public function getTimeOutInMinute()
        {
            return $this->_timeOutInMinute;
        }

        /**
         * @param int $timeOutInMinute
         */
        public function setTimeOutInMinute($timeOutInMinute)
        {
            $t = $timeOutInMinute * 60;
            $this->_timeOutInMinute = $t;

            return $this;
        }


        /**
         * @return string
         */
        public function getPath()
        {
            return $this->path;
        }

        /**
         * @param string $path
         */
        public function setPath(array $path)
        {
            $this->path = array();
            if (!is_array($path) or count($path) == 0) return $this;

            foreach ($path as $k => $pathFile) {
                $this->path[] = $pathFile;
            }

            return $this;
        }


        /**
         * @return boolean
         */
        public function isJsonEncode()
        {
            return $this->jsonEncode;
        }

        /**
         * @param boolean $jsonEncode
         */
        public function setJsonEncode($jsonEncode)
        {
            $this->jsonEncode = $jsonEncode;

            return $this;
        }


        /**
         * @return string
         */
        public function getXmlFileName()
        {
            return $this->xmlFileName;
        }

        /**
         * @param string $xmlFileName
         */
        public function setXmlFileName($xmlFileName)
        {
            $this->xmlFileName = $xmlFileName;

            return $this;
        }

        /**
         * @return null
         */
        public function getTimesTamp()
        {
            return $this->timesTamp;
        }

        /**
         * @param null $timesTamp
         */
        public function setTimesTamp($timesTamp)
        {
            $this->timesTamp = $timesTamp;

            return $this;
        }

        function __construct()
        {

        }


        function start()
        {
            // $xmlFileName = $this->getXmlFileName();

            set_time_limit(0);
            $path = $this->getPath(); //is array

            foreach ($path as $k => $url) {
                $dir = dirname($url);
                if (!file_exists($dir)) mkdir($dir);
                //Verifica se o arquivo default existe, caso não, cria-o
                $fileDefault = rtrim($dir, '/') . "/default.xml";
                if (!file_exists($fileDefault)) {
                    $xml = new Xml("default");
                    $xml->pathOutput = $dir;
                    $xml->fileName = "default.xml";
                    $xml->createElement(array('description' => 'default folder'));
                    $xml->create();
                }
            }

            $arrfiles = array();
            $timesTamp = (is_null($this->getTimesTamp()) || $this->getTimesTamp() == null) ? time() : $this->getTimesTamp();

            foreach ($path as $k => $url) {
                $glob = glob($url);
                if (count($glob) > 0) {
                    $arrfiles[] = $glob;
                }
            }

            if (count($arrfiles) == 1) {
                $arrfiles[0][] = dirname($arrfiles[0][0]) . "/default.xml";
            }

            $startTime = time();
            $limit = $this->getTimeOutInMinute();


//        sleep(3);
//        echo json_encode($arrfiles);
//        exit();


            while (true) {

                $process = time() - $startTime;

                if ($process > $limit) {

                    $arrayTimeOut = array(
                        'result'   => false,
                        'path'     => '',
                        'filename' => '',
                        'data'     => array()
                    );

                    return ($this->isJsonEncode()) ? json_encode($arrayTimeOut) : $arrayTimeOut;
                    break;

                }

                $arrayResult = array();

                //Busca todos os arquivos da pasta
                $pathResult = "";
                $fileName = "";
                foreach ($arrfiles as $k => $arrayPath) {

                    foreach ($arrayPath as $k1 => $pathFile) {

                        $data_source_file = $pathFile;

                        if (file_exists($data_source_file)) {

                            $extension = pathinfo($data_source_file, PATHINFO_EXTENSION);
                            if ($extension == "xml") {
                                $last_change_in_data_file = filemtime($data_source_file);

                                if ($last_change_in_data_file > $timesTamp) {
                                    clearstatcache();
                                    $data = simplexml_load_file($data_source_file);
                                    $dataArray = xml2array($data);
                                    $arrayResult[] = $dataArray;
                                    $pathResult = dirname($data_source_file);
                                    $fileName = basename($data_source_file);
                                }
                            }
                        }

                    }

                }

                if (count($arrayResult) > 0) {
                    $result = array(
                        'result'   => true,
                        'path'     => $pathResult,
                        'fileName' => $fileName,
                        'data'     => $arrayResult
                    );

                    return ($this->isJsonEncode()) ? json_encode($result) : $result;
                } else {

                    sleep(1);

                    //search files in path
                    $arrfiles = array();
                    foreach ($path as $k => $url) {
                        $glob = glob($url);
                        if (count($glob) > 0) {
                            $arrfiles[] = $glob;
                        }
                    }

                    if (count($arrfiles) == 1) {
                        $arrfiles[0][] = dirname($arrfiles[0][0]) . "/default.xml";
                    }

//                sleep(3);
//                echo json_encode($arrfiles);
//                exit();

                }


            }

        }


    }