<?php

    namespace traits;

    trait Directory
    {
        function createDir($dir)
        {
            if (is_dir($dir)) {
                return true;
            }
            mkdir($dir, 7777, true);

            return is_dir($dir);
        }

        function removeFile($file){
            if (!file_exists($file)) return true;
            @unlink($file);
            return !file_exists($file);
        }
    }