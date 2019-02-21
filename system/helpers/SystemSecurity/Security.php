<?php

    namespace SystemSecurity;

    class Security
    {
        public static function isMD5($md5)
        {
            return strlen($md5) == 32 && ctype_xdigit($md5);
        }

        private static function ___encryptmaster($action, $string)
        {
            $output = false;
            $key = (@_STRSEC_ !== "") ? _STRSEC_ : 'pDaOmDtMqDsFuPqTaQnCnPmTaVeJ316'; //String de criptografia, NÃO PODE SER ALTERADO.
            $iv = md5(md5($key));

            if ($action == 'encrypt') {
                $string = $string . "_isEncrypted";
                $output = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, $iv);
                $output = base64_encode($output);
            } else if ($action == 'decrypt') {
                $output = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, $iv);
                $output = rtrim($output, "");
                $output = str_replace("\0", '', $output);
                $output = str_replace("&#x0;", "", $output);
                $output = str_replace("_isEncrypted", "", $output);
            } else if ($action == 'isEncrypted') {
                if (is_array($string)) return false;
                $output = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, $iv);
                $output = rtrim($output, "");
                $output = str_replace("\0", '', $output);
                $output = str_replace("&#x0;", "", $output);

                $lst = explode("_", $output);

                if (($lst[count($lst) - 1]) == "isEncrypted") {
                    return true;
                } else {
                    return false;
                }
            }

            return $output;
        }

        static function isEncrypted($str)
        {
            if (empty($str)):
                return false;
                exit();
            endif;

            return self::___encryptmaster('isEncrypted', $str);
        }

        /**
         * @param $str
         *
         * @return bool|mixed|string
         */
        static function encrypt($str)
        {

            if (is_array($str)) return $str;

            if (empty($str)):
                return "";
                exit();
            endif;

            return self::___encryptmaster('encrypt', $str);
        }

        /**
         * @param $str
         *
         * @return bool|mixed|string
         */
        static function decrypt($str)
        {
            if (is_array($str)) return $str;
            if (empty($str)):
                return "";
                exit();
            endif;

            return self::___encryptmaster('decrypt', $str);
        }

        /**
         * Carrega a imagem encriptografando o caminho
         *
         * @param      $path
         * @param null $attr
         */
        static function getImageEncrypted($imgfile, $attr = null)
        {
            $handle = fopen($imgfile, "r");
            //$imgbinary = fread(fopen($imgfile, "r"), filesize($imgfile));

            $s_attr = (is_array($attr)) ? convertArrayToHtmlAttribute($attr, array('src')) : "";
            $imgbinary = fread($handle, filesize($imgfile));

            return '<img src="data:image/gif;base64,' . base64_encode($imgbinary) . '" ' . $s_attr . ' />';

        }
    }