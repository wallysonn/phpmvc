<?php

    class Bundle
    {

        private $filesJs = array();
        private $filesCSS = array();

        public $pathProject = "/";
        public $pathJsCache = "app/files/scripts/bundle";
        public $pathCSSCache = "app/files/css/bundle";

        protected $templateJs = '<script src="%s" type="text/javascript"></script>';
        protected $templateCss = '<link href="%s" rel="stylesheet" type="text/css">';

        private $bundleName = "";

        function __construct($bundleName)
        {
            $this->bundleName = ($bundleName == "") ? md5((time() . rand(1, 50))) : md5($bundleName);

        }

        function addJsFile($file)
        {

            if (is_array($file)) {
                $this->filesJs = $file;
            } else {
                $this->filesJs[] = $file;
            }

            return $this;
        }

        function addCSSFile($file)
        {

            if (is_array($file)) {
                $this->filesCSS = $file;
            } else {
                $this->filesCSS[] = $file;
            }

            return $this;
        }

        private function maxFileMTimeBundleForJS()
        {
            $output_cache = ltrim(rtrim(trim($this->pathJsCache), '/'), '/');
            $file = "{$output_cache}/{$this->bundleName}.js";
            $mtime = 0;
            if (file_exists($file)) {
                $time = filemtime($file);
                if ($time > $mtime) $mtime = $time;
            }

            return $mtime;
        }

        private function maxFileMTimeBundleForCSS()
        {
            $output_cache = ltrim(rtrim(trim($this->pathCSSCache), '/'), '/');
            $file = "{$output_cache}/{$this->bundleName}.css";
            $mtime = 0;
            if (file_exists($file)) {
                $time = filemtime($file);
                if ($time > $mtime) $mtime = $time;
            }

            return $mtime;
        }

        private function header($fileName)
        {
            $data = "";
            $data .= "/**************************************************" . PHP_EOL;
            $data .= "Bundle for: {$fileName}" . PHP_EOL;
            $data .= "Date: " . date('d/m/Y H:i:s') . PHP_EOL;
            $data .= "By: TwMVC - 2015 " . PHP_EOL;
            $data .= "Author: Wallysonn Gomes - wgwalisongomes@gmail.com " . PHP_EOL;
            $data .= "**************************************************/" . PHP_EOL;

            return $data;

        }

        protected function compressJs($buffer)
        {
            //Remove all whitespaces
            $buffer = preg_replace('/\s+/', ' ', $buffer);
            $buffer = preg_replace('/\s*(?:(?=[=\-\+\|%&\*\)\[\]\{\};:\,\.\\!\@\#\^`~]))/', '', $buffer);
            $buffer = preg_replace('/(?:(?<=[=\-\+\|%&\*\)\[\]\{\};:\,\.\\?\!\@\#\^`~]))\s*/', '', $buffer);
            $buffer = preg_replace('/([^a-zA-Z0-9\s\-=+\|!@#$%^&*()`~\[\]{};:\'",\/?])\s+([^a-zA-Z0-9\s\-=+\|!@#$%^&*()`~\[\]{};:\'",\/?])/', '$1$2', $buffer);

            return $buffer;
        }

        protected function compressCSS($buffer)
        {
            $buffer = $this->removeCommentsFromCSS($buffer);
            $buffer = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $buffer);

            return $buffer;
        }


        protected function removeCommentsFromCSS($buffer)
        {
            $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);

            return $buffer;
        }

        protected function removeCommentsFromJs($buffer)
        {

            //Remove comments

            $buffer = str_replace('/// ', '///', $buffer);
            $buffer = str_replace(',//', ', //', $buffer);
            $buffer = str_replace('{//', '{ //', $buffer);
            $buffer = str_replace('}//', '} //', $buffer);
            $buffer = str_replace('*//*', '*/ /*', $buffer);
            $buffer = str_replace('/**/', '/* */', $buffer);
            $buffer = str_replace('*///', '*/ //', $buffer);
            $buffer = preg_replace("/\/\/.*\n\/\/.*\n/", "", $buffer);
            $buffer = preg_replace("/\s\/\/\".*/", "", $buffer);
            $buffer = preg_replace("/\/\/\n/", "\n", $buffer);
            $buffer = preg_replace("/\/\/\s.*.\n/", "\n \n", $buffer);
            $buffer = preg_replace('/\/\/w[^w].*/', '', $buffer);
            $buffer = preg_replace('/\/\/s[^s].*/', '', $buffer);
            $buffer = preg_replace('/\/\/\*\*\*.*/', '', $buffer);
            $buffer = preg_replace('/\/\/\*\s\*\s\*.*/', '', $buffer);
            $buffer = preg_replace('/[^\*]\/\/[*].*/', '', $buffer);
            $buffer = preg_replace('/([;])\/\/.*/', '$1', $buffer);
            $buffer = preg_replace('/((\r)|(\n)|(\R)|([^0]1)|([^\"]\s*\-))(\/\/)(.*)/', '$1', $buffer);
            $buffer = preg_replace("/([^\*])[\/]+\/\*.*[^a-zA-Z0-9\s\-=+\|!@#$%^&()`~\[\]{};:\'\",?]/", "$1", $buffer);
            $buffer = preg_replace("/\/\*/", "\n/*dddpp", $buffer);
            $buffer = preg_replace('/((\{\s*|:\s*)[\"\']\s*)(([^\{\};\"\']*)dddpp)/', '$1$4', $buffer);
            $buffer = preg_replace("/\*\//", "xxxpp*/\n", $buffer);
            $buffer = preg_replace('/((\{\s*|:\s*|\[\s*)[\"\']\s*)(([^\};\"\']*)xxxpp)/', '$1$4', $buffer);
            $buffer = preg_replace('/([\"\'])\s*\/\*/', '$1/*', $buffer);
            $buffer = preg_replace('/(\n)[^\'"]?\/\*dddpp.*?xxxpp\*\//s', '', $buffer);
            $buffer = preg_replace('/\n\/\*dddpp([^\s]*)/', '$1', $buffer);
            $buffer = preg_replace('/xxxpp\*\/\n([^\s]*)/', '*/$1', $buffer);
            $buffer = preg_replace('/xxxpp\*\/\n([\"])/', '$1', $buffer);
            $buffer = preg_replace('/(\*)\n*\s*(\/\*)\s*/', '$1$2$3', $buffer);
            $buffer = preg_replace('/(\*\/)\s*(\")/', '$1$2', $buffer);
            $buffer = preg_replace('/\/\*dddpp(\s*)/', '/*', $buffer);
            $buffer = preg_replace('/\n\s*\n/', "\n", $buffer);
            $buffer = preg_replace("/([^\'\"]\s*)(?!()).*/", "$1", $buffer);
            $buffer = preg_replace('/([^\n\w\-=+\|!@#$%^&*()`~\[\]{};:\'",\/?\\\\])(\/\/)(.*)/', '$1', $buffer);


            return $buffer;

        }

        function writeBundleJs()
        {

            $data = "";


            $createBundle = false;

            $maxMTime = $this->maxFileMTimeBundleForJS();

            $path = $this->pathProject;

            $fnRealFile = function ($file) use ($path) {
                $real = ltrim(str_replace(array($path), array(''), $file), "/");
                $real = str_replace(".js", "", $real) . ".js";

                return $real;
            };

            foreach ($this->filesJs as $k1 => $f1) {
                $realFile = $fnRealFile($f1);
                if (filemtime($realFile) > $maxMTime) {
                    $createBundle = true;
                }
            }

            if ($createBundle) {

                foreach ($this->filesJs as $k => $file) {

                    $fileName = basename($file);

                    $realFile = $fnRealFile($file);

                    $data .= $this->header($fileName);

                    clearstatcache();
                    $content = @file_get_contents($realFile);

                    if (!$content) {
                        $data .= '/* FILE READ ERROR! */' . PHP_EOL;
                    } else {

                        $content = $this->removeCommentsFromJs($content);

                        $content = JSMin::minify($content);

                        $data .= $content . PHP_EOL;


                    }
                }

                if ($data !== "") {
                    $name = $this->bundleName;
                    $output_cache = ltrim(rtrim(trim($this->pathJsCache), '/'), '/');

                    $this->createFile($output_cache, $name, $data, "js");

                    $output_project = ltrim(rtrim(trim($this->pathProject), '/'), '/');

                    $output_path = "/" . $output_project . "/" . $output_cache . "/" . $name . ".js";

                    return $this->getJsTemplate($output_path) . PHP_EOL;
                } else {

                    return " <!-- Not files JS --> " . PHP_EOL;

                }
            } else {

                $output = ltrim(rtrim(trim($this->pathJsCache), '/'), '/');
                $fileBundle = glob($output . "/*{$this->bundleName}*.js");
                $output_project = ltrim(rtrim(trim($this->pathProject), '/'), '/');
                $html = "";

                foreach ($fileBundle as $k => $f) {
                    $html .= $this->getJsTemplate("/{$output_project}/{$f}") . PHP_EOL;
                }

                return $html;


            }

        }


        function writeBundleCSS()
        {

            $data = "";


            $createBundle = false;

            $maxMTime = $this->maxFileMTimeBundleForCSS();

            $path = $this->pathProject;

            $fnRealFile = function ($file) use ($path) {
                $real = ltrim(str_replace(array($path), array(''), $file), "/");
                $real = str_replace(".css", "", $real) . ".css";

                return $real;
            };

            foreach ($this->filesCSS as $k1 => $f1) {
                $realFile = $fnRealFile($f1);
                if (filemtime($realFile) > $maxMTime) {
                    $createBundle = true;
                }
            }

            if ($createBundle) {

                foreach ($this->filesCSS as $k => $file) {

                    $fileName = basename($file);

                    $realFile = $fnRealFile($file);

                    $data .= $this->header($fileName);

                    clearstatcache();
                    $content = @file_get_contents($realFile);

                    if (!$content) {
                        $data .= '/* FILE READ ERROR! */' . PHP_EOL;
                    } else {
                        $content = $this->compressCSS($content);
                        $data .= $content . PHP_EOL;

                    }
                }

                if ($data !== "") {
                    $name = $this->bundleName;
                    $output_cache = ltrim(rtrim(trim($this->pathCSSCache), '/'), '/');

                    $this->createFile($output_cache, $name, $data, "css");

                    $output_project = ltrim(rtrim(trim($this->pathProject), '/'), '/');

                    $output_path = "/" . $output_project . "/" . $output_cache . "/" . $name . ".css";

                    return $this->getCSSTemplate($output_path) . PHP_EOL;
                } else {

                    return " <!-- Not files CSS --> " . PHP_EOL;

                }
            } else {

                $output = ltrim(rtrim(trim($this->pathCSSCache), '/'), '/');
                $fileBundle = glob($output . "/*{$this->bundleName}*.css");
                $output_project = ltrim(rtrim(trim($this->pathProject), '/'), '/');
                $html = "";

                foreach ($fileBundle as $k => $f) {
                    $html .= $this->getCSSTemplate("/{$output_project}/{$f}") . PHP_EOL;
                }

                return $html;


            }

        }


        private function getJsTemplate($urlFile)
        {
            return sprintf($this->templateJs, $urlFile);
        }

        private function getCSSTemplate($urlFile)
        {
            return sprintf($this->templateCss, $urlFile);
        }

        private function createFile($path, $fileName, $content, $fileExtension)
        {
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $realFile = rtrim(trim($path), "/") . "/" . $fileName . "." . $fileExtension;

            if (false === file_put_contents($realFile, $content, LOCK_EX)) {
                throw new \RuntimeException('Cannot write cache file to "' . $path . $realFile . '"');
            }

            return $realFile;

        }


    }