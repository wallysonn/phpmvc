<?php

    class IncludeFile
    {

        private $_filesjs  = array();
        private $_filescss = array();
        private $_linkscss = array();

        function __construct()
        {

        }

        private function getrealfile($path_file)
        {
            $app = app::pathproject();

            return ($app == "/") ? ltrim($path_file, "/") : str_replace($app, "", $path_file);
        }

        private function getfilename($realfile)
        {
            $arr_file = pathinfo($realfile);
            $ext = "";
            if (isset($arr_file["extension"])) $ext = "." . $arr_file["extension"];
            $ext = ($ext !== ".js" && $ext !== ".css") ? "" : $ext;

            return str_replace($ext, "", $realfile);
        }

        public function js($file, array $param = null)
        {
            $strParam = "";

            if (is_array($param) && count($param) > 0) $strParam = "&" . http_build_query($param);


            $model = "<script src=\"%s\" type=\"text/javascript\"></script>\n";

            if (in_str(array('://'), $file)) {
                $strParam = ($strParam !== "") ? "?" . ltrim($strParam, "&") : "";
                $this->_filesjs[] = sprintf($model, $file . $strParam);
            } else {

                $file = $this->getfilename($file);

                $file_and_extension = "{$file}.js";
                $directory = str_replace(array('.', '..'), '', dirname($file_and_extension));
                $file_and_extension = ($directory == "") ? "scripts/{$file_and_extension}" : $file_and_extension;

                $path_file = app::pathfiles() . $file_and_extension;
                $real_file = $this->getrealfile($path_file);

                $arr_files = glob($real_file);
                $path = app::pathProject();
                if ($path !== "/") $path = sprintf("/%s/", rtrim(ltrim($path, "/"), "/"));

                foreach ($arr_files as $f) {
                    if (file_exists($f)) {
                        $path_file = "{$path}{$f}?v=" . filemtime($f) . $strParam;
                        $this->_filesjs[] = sprintf($model, $path_file);
                    }
                }
            }

            return $this;
        }

        public function css($file, array $param = null)
        {
            $model = "<link href = \"%s\" rel = \"stylesheet\" type = \"text/css\" />\n";

            $strParam = "";

            if (is_array($param) && count($param) > 0) $strParam = "&" . http_build_query($param);

            if (in_str(array('://'), $file)) {
                $strParam = ($strParam !== "") ? "?" . ltrim($strParam, "&") : "";
                $this->_filesjs[] = sprintf($model, $file . $strParam);
            } else {

                $file = $this->getfilename($file);

                $file_and_extension = "{$file}.css";
                $directory = str_replace(array('.', '..'), '', dirname($file_and_extension));
                $file_and_extension = ($directory == "") ? "css/{$file_and_extension}" : $file_and_extension;

                $path_file = app::pathfiles() . $file_and_extension;
                $real_file = $this->getrealfile($path_file);

                $arr_files = glob($real_file);
                $path = app::pathProject();
                if ($path !== "/") $path = sprintf("/%s/", rtrim(ltrim($path, "/"), "/"));

                foreach ($arr_files as $f) {

                    if (file_exists($f)) {
                        $path_file = "{$path}{$f}?v=" . filemtime($f) . $strParam;
                        $this->_filescss[] = sprintf($model, $path_file);
                        $this->_linkscss[] = $path_file;
                    }
                }
            }

            return $this;
        }

        public function less($file, array $param = null)
        {
            $file = $this->getfilename($file);

            $strParam = "";

            if (is_array($param) && count($param) > 0) $strParam = "&" . http_build_query($param);

            $file_and_extension = "{$file}.less";
            $directory = str_replace(array('.', '..'), '', dirname($file_and_extension));
            $file_and_extension = ($directory == "") ? "less/{$file_and_extension}" : $file_and_extension;

            $path_file = app::pathfiles() . $file_and_extension;
            $real_file = $this->getrealfile($path_file);

            $arr_files = glob($real_file);
            $path = app::pathProject();
            if ($path !== "/") $path = sprintf("/%s/", rtrim(ltrim($path, "/"), "/"));

            foreach ($arr_files as $f) {
                if (file_exists($f)) {
                    $path_file = "{$path}{$f}?v=" . filemtime($f) . $strParam;
                    $this->_filescss[] = "<link href = \"{$path_file}\" rel = \"stylesheet/less\" type = \"text/css\" />\n";
                }
            }

            return $this;
        }

        public function getfilessystem()
        {
            //files system
            $pathproject = app::pathproject();
            $jssystem = "{$pathproject}system/js/";

            $js = array(
                "<script src='{$pathproject}system/app.config.js.php' type='text/javascript'></script>",
                "<script src=\"{$jssystem}web.config.js\" type=\"text/javascript\"></script>"
            );

            return implode("\n", $js);
        }

        public function showfiles()
        {
            $merge = array_merge($this->_filescss, $this->_filesjs);

            return implode("", array_values($merge));

        }

        public function requestCssContent()
        {
            $allFiles = $this->_linkscss;

            $all_css = "";
            $pathproject = app::pathproject();
            foreach ($allFiles as $file) {
                $f = ltrim($file, $pathproject);
                if (strpos($f, "?") !== false) {
                    $arr = explode("?", $f);
                    $f = $arr[0];
                }
                clearstatcache();
                $content = @file_get_contents($f);
                if ($content !== "") $all_css .= $content;
            }

            return $all_css;

        }

    }