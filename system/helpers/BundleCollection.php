<?php

    class BundleCollection
    {

        private $sessionName         = "";
        public  $enableOptimizations = false;
        public  $minityHtml          = false;

        function __construct()
        {
            sessionStart();
            $this->sessionName = BundlesOptions::sessionName;
            $_SESSION[$this->sessionName] = array();
        }

        /**
         * @param       $name
         * @param array $file
         *
         * @return $this
         */
        function js($name, array $file)
        {
            $this->add("js", $name, $file);

            return $this;
        }

        /**
         * @param       $name
         * @param array $file
         *
         * @return $this
         */
        function css($name, array $file)
        {
            $this->add("css", $name, $file);

            return $this;
        }

        function less($name, array $file)
        {
            $this->add("less", $name, $file);

            return $this;
        }


        function combined($name, array $files)
        {
            /*Model default
                $files = array(
                    'js' => array(),
                    'css' => array()
                );
            */
            foreach ($files as $type => $arrayFiles) {
                $this->add($type, $name, $arrayFiles);
            }

            return $this;
        }

        /**
         * @param       $type
         * @param       $name
         * @param array $file
         */
        private function add($type, $name, array $file)
        {
            switch ($type) {
                case 'js':
                    $_SESSION[$this->sessionName]['js'][$name] = $file;
                    break;

                case 'css':
                    $_SESSION[$this->sessionName]['css'][$name] = $file;
                    break;

                case 'less':
                    $_SESSION[$this->sessionName]['less'][$name] = $file;
                    break;
            }

            //Configure Optmizations
            $_SESSION[$this->sessionName]["enableOptimizations"] = $this->enableOptimizations;
            $_SESSION['minityHtml'] = $this->minityHtml;

        }

    }


    class BundlesOptions
    {
        const sessionName = "bundles";

        public static function doc_root()
        {
            return APP::pathProject() . "app";
        }

        public static function css_cache_path()
        {
            $path = "../../app/css/cache";
            self::createPath($path);

            return $path;
        }

        public static function js_cache_path()
        {
            $path = "../../app/js/cache";
            self::createPath($path);

            return $path;
        }

        private static function createPath($path)
        {
            if (!is_dir($path)) {
                mkdir($path);
            }
        }

    }

    class Scripts
    {


        public static function render($bundleName, $onlyIEVersion = null)
        {

            sessionStart();
            $sessionName = BundlesOptions::sessionName;
            $optimization = $_SESSION[$sessionName]['enableOptimizations'];
            $arrayBundles = (isset($_SESSION[$sessionName]['js'][$bundleName])) ? $_SESSION[$sessionName]['js'][$bundleName] : array();

            if (!$optimization) {
                $file = new IncludeFile();
                foreach ($arrayBundles as $k => $f) {
                    $spl = explode("?", $f);
                    if (isset($spl[1])) {
                        $file->js(@$spl[0], urlParamToArray($spl[1]));
                    } else {
                        $file->js(@$spl[0]);
                    }
                }
                if ($onlyIEVersion !== null) {
                    $r = $file->showFiles();;
                    echo "\n<!--[if IE {$onlyIEVersion}]>\n{$r}<![endif]-->\n";
                } else {

                    echo $file->showFiles();
                }


            } else {

                $pathFiles = APP::pathFiles() . "scripts";
                $b = new Bundle($bundleName);
                $b->pathProject = APP::pathProject();
                $b->pathJsCache = "app/files/scripts/bundle";
                $files = array();
                foreach ($arrayBundles as $k => $f) {
                    $files[] = "{$pathFiles}/{$f}";
                }
                $b->addJsFile($files);
                if ($onlyIEVersion !== null) {
                    $r = $b->writeBundleJs();
                    echo "<!--[if IE {$onlyIEVersion}]>{$r}<![endif]-->";
                } else {

                    echo $b->writeBundleJs();
                }

            }

        }

        public static function clear()
        {
            $sessionName = BundlesOptions::sessionName;
            $_SESSION[$sessionName]['js'] = array();
        }
    }

    class Styles
    {

        public static function render($bundleName)
        {

            sessionStart();
            $sessionName = BundlesOptions::sessionName;
            $optimization = $_SESSION[$sessionName]['enableOptimizations'];
            $arrayBundles = (isset($_SESSION[$sessionName]['css'][$bundleName])) ? $_SESSION[$sessionName]['css'][$bundleName] : array();


            if (!$optimization) {

                $file = new IncludeFile();
                foreach ($arrayBundles as $k => $f) {
                    $spl = explode("?", $f);
                    if (isset($spl[1])) {
                        $file->css(@$spl[0], urlParamToArray($spl[1]));
                    } else {
                        $file->css(@$spl[0]);
                    }
                }
                echo $file->showFiles();
            } else {


                $pathFiles = APP::pathFiles() . "css";


                $b = new Bundle($bundleName);
                $b->pathProject = APP::pathProject();
                $b->pathCSSCache = "app/files/css/bundle";
                $files = array();
                foreach ($arrayBundles as $k => $f) {
                    $files[] = "{$pathFiles}/{$f}";
                }
                $b->addCSSFile($files);
                echo $b->writeBundleCSS();

            }
        }

        public static function requestContent($bundleName)
        {

            sessionStart();
            $sessionName = BundlesOptions::sessionName;
            $arrayBundles = (isset($_SESSION[$sessionName]['css'][$bundleName])) ? $_SESSION[$sessionName]['css'][$bundleName] : array();

            $file = new IncludeFile();
            foreach ($arrayBundles as $k => $f) {
                $spl = explode("?", $f);
                if (isset($spl[1])) {
                    $file->css(@$spl[0], urlParamToArray($spl[1]));
                } else {
                    $file->css(@$spl[0]);
                }
            }

            return $file->requestCssContent();


        }

        public static function clear()
        {
            $sessionName = BundlesOptions::sessionName;
            $_SESSION[$sessionName]['css'] = array();
        }

    }


    class StylesLess
    {

        public static function render($bundleName)
        {

            sessionStart();
            $sessionName = BundlesOptions::sessionName;
            $optimization = (isset($_SESSION[$sessionName]['enableOptimizations'])) ? false : $_SESSION[$sessionName]['enableOptimizations'];
            $arrayBundles = (isset($_SESSION[$sessionName]['less'][$bundleName])) ? $_SESSION[$sessionName]['less'][$bundleName] : array();

            $file = new IncludeFile();
            foreach ($arrayBundles as $k => $f) {
                //$file->less($f);
                $spl = explode("?", $f);
                if (isset($spl[1])) {
                    $file->less(@$spl[0], urlParamToArray($spl[1]));
                } else {
                    $file->less(@$spl[0]);
                }
            }

            if (!$optimization) {
                echo $file->showFiles();
            } else {

            }
        }

        public static function clear()
        {
            $sessionName = BundlesOptions::sessionName;
            $_SESSION[$sessionName]['less'] = array();
        }

    }

    class StylesAndScrypt
    {
        public static function render($bundleName)
        {
            Styles::render($bundleName);
            Scripts::render($bundleName);
            StylesLess::render($bundleName);
        }
    }