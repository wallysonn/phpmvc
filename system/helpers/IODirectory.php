<?php

    define("SORTFILEBY_DATEMODIFY", 1);
    define("SORTFILEBY_DATECREATE", 2);
    define("SORTFILEBY_NAME", 3);

    define("SORTFILE_ASC", "asc");
    define("SORTFILE_DESC", "desc");

    class IODirectory
    {

        private $dir = "";

        /**
         * IODirectory constructor.
         *
         * @param $dir
         */
        function __construct ($dir)
        {
            try {
                if ($dir == "") throw new \Exception("Informa o diretório");
                $this->dir = rtrim($dir, "/") . "/";
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * @return bool
         */
        function exist ()
        {
            return is_dir($this->dir);
        }

        /**
         * @param int  $mode
         * @param bool $recursive
         * @param null $context
         *
         * @return bool
         */
        function create ($mode = 0777, $recursive = false)
        {
            try {

                if ($this->exist()) return true;
                @mkdir($this->dir, $mode, $recursive);
                return $this->exist();

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * @return int
         */
        function countFiles ()
        {
            return count($this->getFiles("*"));
        }

        /**
         * @return int
         */
        function countSubdirectory ()
        {
            return count($this->getSubDirectory());
        }

        function getDir()
        {
            return $this->dir;
        }

        /**
         * @param string $filter
         * @param null   $flag
         * @param null   $sort
         * @param string $order
         * @param null   $limit (--> igual mysql, 0 = primeiro elemento)
         *
         * @return array
         */
        function getFiles ($filter = "*", $flag = null, $sort = null, $order = SORTFILE_ASC, $limit = null)
        {
            try {

                if ($filter == "" || is_null($filter) || $filter == null) $filter = "*";
                $data = array_filter(glob("{$this->dir}{$filter}", $flag), 'is_file');

                switch ($sort) {
                    case SORTFILEBY_DATEMODIFY:

                        if ($order == SORTFILE_ASC) {
                            usort($data, create_function('$a,$b', 'return filemtime($a) - filemtime($b);'));
                        } else {
                            usort($data, create_function('$a,$b', 'return filemtime($b) - filemtime($a);'));
                        }

                        break;
                    case SORTFILEBY_DATECREATE:

                        if ($order == SORTFILE_ASC) {
                            usort($data, create_function('$a,$b', 'return filectime($a) - filectime($b);'));
                        } else {
                            usort($data, create_function('$a,$b', 'return filectime($b) - filectime($a);'));
                        }

                        break;

                    case SORTFILEBY_NAME:

                        echo "SORT BY NAME IS DEPRECIATE!";

                        break;
                }

                if ($limit !== null) {

                    $spl = explode(",", $limit);
                    if (count($spl) > 1) {
                        $ini = (int) trim($spl[0]);
                        $end = (int) trim($spl[1]);

                        $data = array_slice($data, $ini, $end);

                    } else {
                        throw new \Exception("limit is: ini, end. Ex.: '0,50'");
                    }
                }

                return $data;

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }


        /**
         * @param string $filter
         *
         * @return array
         */
        function getSubDirectory ($filter = "*")
        {
            try {

                if ($filter == "" || is_null($filter) || $filter == null) $filter = "*";

                $data = glob("{$this->dir}{$filter}", GLOB_ONLYDIR);
                return $data;

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * @return bool
         */
        function clearFiles ()
        {
            try {
                array_map("unlink", $this->getFiles());
                return ($this->countFiles() == 0);
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        private function clearPath ($dir)
        {
            if ($dd = opendir($dir)) {

                while (false !== ($arq = readdir($dd))) {
                    if ($arq != "." && $arq != "..") {
                        $path = "$dir/$arq";
                        if (is_dir($path)) {
                            $this->clearPath($path);
                        } elseif (is_file($path)) {
                            unlink($path);
                        }
                    }
                }

                closedir($dd);
            }

            if ($dir !== $this->dir) rmdir($dir);

        }

        /**
         *
         */
        function clear ()
        {
            try {

                if (!$this->exist()) throw new Exception(_LANG_DIRECTORY_NOTDIRECTORY_);
                $this->clearPath($this->dir);

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         *
         */
        function delete ()
        {
            try {

                $this->clear();
                return rmdir($this->dir);

            } catch (\Exception $e) {

            }
        }


    }


