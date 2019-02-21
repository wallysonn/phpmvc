<?php

    class IOFiles
    {

        const ERRO_FILE = "File <strong>%s</strong> not exists!";

        private $_file        = "";
        private $_realfile    = "";
        private $_pathProject = "";


        /**
         * IOFiles constructor.
         *
         * @param $file
         */
        function __construct($file)
        {
            try {

                $this->_file = $file;
                $this->_pathProject = \APP::pathProject();
                $this->_realfile = $this->realFile();

            } catch (\Exception $e) {

            }
        }

        /**
         * @return string
         */
        private function realFile()
        {
            try {

                $file = $this->_file;
                $pathProject = $this->_pathProject;
                if ($pathProject == "/") $pathProject = "";
                $file = ltrim($file, $pathProject);
                return $file;

            } catch (\Exception $e) {

            }
        }

        /**
         * @return bool
         */
        function exists()
        {
            try {
                return file_exists($this->_realfile);
            } catch (\Exception $e) {
                return false;
            }
        }

        /**
         * Retorna o tamando do arquivo
         *
         * @param bool   $showUnit
         * @param int    $decimal
         * @param string $type
         *
         * @return string
         */
        function size($showUnit = false, $decimal = 0, $type = "auto")
        {
            try {

                if (!$this->exists()) throw new \Exception($this->__message_filenotexists());

                $file = $this->_realfile;
                $size = filesize($file);
                $type = trim(strtoupper($type));

                $real_size = $size;
                $unit = "byte";

                if ($type !== "AUTO") {

                    switch ($type) {
                        case 'B':
                        case 'BYTE':
                            $real_size = round($size, $decimal);
                            $unit = "byte";
                            break;
                        case 'KB':
                            $real_size = round($size / 1024, $decimal);
                            $unit = "Kb";
                            break;
                        case 'MB':
                            $real_size = round(($size / 1024) / 1024, $decimal);

                            $unit = "Mb";
                            break;
                        case 'GB':
                            $real_size = round((($size / 1024) / 1024) / 1024, $decimal);
                            $unit = "Gb";
                            break;
                        case 'TB':
                            $real_size = round(((($size / 1024) / 1024) / 1024) / 1024, $decimal);
                            $unit = "Tb";
                            break;
                    }

                    return $real_size . (($showUnit) ? " {$unit}" : "");
                } else {
                    $unit = ["byte", "Kb", "Mb", "Gb", "Tb"];
                    $exp = floor(log($size, 1024)) | 0;
                    return round($size / (pow(1024, $exp)), $decimal) . (($showUnit) ? " {$unit[$exp]}" : "");
                }


            } catch (\Exception $e) {
                die($e->getMessage());
            }

        }

        /**
         * @return string
         */
        private function __message_filenotexists()
        {
            return sprintf(self::ERRO_FILE, $this->file);
        }

        /**
         * Mostra o nome do diretório onde o arquivo está
         * @return string
         */
        function dirName()
        {
            try {

                if (!$this->exists()) throw new \Exception($this->__message_filenotexists());
                $file = $this->_realfile;
                return dirname($file);

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * Retorna a data que o arquivo foi modificado
         *
         * @param string $format
         *
         * @return false|string
         */
        function dateModify($format = "Y-m-d H:i:s")
        {
            try {

                if (!$this->exists()) throw new \Exception($this->__message_filenotexists());
                $file = $this->_realfile;
                return date($format, filemtime($file));

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * Retorna a data que o arquivo foi criado
         *
         * @param string $format
         *
         * @return false|string
         */
        function dateCreate($format = "Y-m-d H:i:s")
        {
            try {

                if (!$this->exists()) throw new \Exception($this->__message_filenotexists());
                $file = $this->_realfile;
                return date($format, filectime($file));

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * Retorna a extensão do arquivo
         * @return mixed
         */
        function extension()
        {
            try {

                if (!$this->exists()) throw new \Exception($this->__message_filenotexists());
                $file = $this->_realfile;
                $array = explode(".", $file);
                return end($array);

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * Deleta o arquivo. Retorna verdadeiro caso positivo!
         * @return bool
         */
        function delete()
        {
            try {

                if (!$this->exists()) throw new \Exception($this->__message_filenotexists());
                $file = $this->_realfile;
                $this->changePermission(0777);
                unlink($file);
                return !$this->exists();

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * ALtera as permissões do arquivo
         *
         * @param $mode --> 07777, 0755
         *
         * @return bool
         */
        function changePermission($mode)
        {
            try {
                if (!$this->exists()) throw new \Exception($this->__message_filenotexists());
                $file = $this->_realfile;
                if ($this->getPermission() == $mode) return true;
                return @chmod($file, $mode);

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * Retorna a permissão do arquivo
         * @return int
         */
        function getPermission()
        {
            try {
                if (!$this->exists()) throw new \Exception($this->__message_filenotexists());
                $file = $this->_realfile;
                return decoct(fileperms($file) & 0777);

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * Retorna o nome do arquivo
         * @return string
         */
        function getName()
        {
            try {

                if (!$this->exists()) throw new \Exception($this->__message_filenotexists());
                $file = $this->_realfile;
                return basename($file);

            } catch (\Exception $e) {
                die($e->getMessage());
            }

        }

        /**
         * Copia o arquivo para a pasta informada
         *
         * @param $path
         *
         * @return bool
         */
        function copyTo($path)
        {
            try {
                if ($path == "") throw new \Exception("A pasta de destino é obrigatória");
                if (!$this->exists()) throw new \Exception($this->__message_filenotexists());
                $file = $this->_realfile;

                $dir = new IODirectory($path);
                if (!$dir->exist()) $dir->create();

                $name = $this->getName();
                $path = rtrim($path, "/") . "/";
                $newFile = $path . $name;

                return copy($file, $newFile);

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * Move o arquivo para a pasta $path
         *
         * @param $path
         *
         * @return bool
         */
        function moveTo($path)
        {

            try {

                if ($path == "") throw new \Exception("A pasta de destino é obrigatória");
                if (!$this->exists()) throw new \Exception($this->__message_filenotexists());

                $dir = new IODirectory($path);
                if (!$dir->exist()) $dir->create();

                if ($this->copyTo($path)) {
                    return $this->delete();
                } else {
                    return false;
                }

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * Renomeia o arquivo
         *
         * @param $newName
         *
         * @return bool
         */
        function rename($newName)
        {
            try {

                if ($newName == "") throw new \Exception("O nome do arquivo é obrigatório");
                if (!$this->exists()) throw new \Exception($this->__message_filenotexists());

                $file = $this->_realfile;
                $extension = $this->extension();
                $path = $this->dirName();
                $newFile = rtrim($path, "/") . "/" . $newName . ".{$extension}";

                if (file_exists($newFile)) throw  new \Exception("Já existe um arquivo com este nome: {$newName}");

                return rename($file, $newFile);

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * Caminho do arquivo via url
         * @return string
         */
        function url()
        {
            try {

                $host = "http://$_SERVER[HTTP_HOST]";
                $app = \APP::pathProject();
                if ($app == "") $app = "/";
                if ($app !== "/") $app = "/" . (ltrim($app, "/"));
                $file = $this->_realfile;

                return $host . $app . $file;

            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }


    }