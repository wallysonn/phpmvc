<?php

    setlocale(LC_TIME, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    date_default_timezone_set('America/Fortaleza');
    spl_autoload_register('load_models');
    spl_autoload_register('load_controllers');
    spl_autoload_register('load_system');
    spl_autoload_register('load_system_data');
    spl_autoload_register('load_system_helpers');
    spl_autoload_register('load_databases');
    spl_autoload_register('load_libs');
    spl_autoload_register('load_app_start');
    spl_autoload_register('load_user_helpers');
    spl_autoload_register('load_system_annotations');

    function load_models($class)
    {
        $class = str_replace("\\", "/", $class);
        $file = APP::pathModels() . $class . '.php';

        if (!file_exists($file)) return false;
        require_once($file);

        return true;
    }

    function load_controllers($class)
    {
        $file = APP::pathControllers() . $class . 'Controller.php';
        $filelower = APP::pathControllers() . strtolower($class) . 'Controller.php';

        if (!file_exists($file)) {
            if (!file_exists($filelower)) {
                return false;
            }else{
                $file = $filelower;
            }
        }

        require_once($file);

        return true;
    }

    function load_system($class)
    {
        $class = str_replace("\\", "/", $class);
        if (in_str(array("/"), $class)) {
            $spl = explode("/", $class);
            if (count($spl) == 2) $class = $spl[0];
        }

        $file = "system/{$class}.php";
        if (!file_exists($file)) return false;
        require_once($file);

        return true;
    }

    function load_system_data($class)
    {
        $class = str_replace("\\", "/", $class);

        if (in_str(array("/"), $class)) {
            //$spl = explode("/", $class);
            //if (count($spl) == 2) $class = $spl[0];
            $file = "system/{$class}.php";
        }else{
            $file = "system/data/{$class}.php";
        }



        if (!file_exists($file)) return false;
        require_once($file);

        return true;
    }

    function load_system_helpers($class)
    {
        $class = str_replace("\\", "/", $class);

        $file = APP::pathHelpers() . $class . '.php';
        $filenamespace = "{$class}.php";

        if (!file_exists($file)) {



            if (in_str(array("/"), $class)) {
                $spl = explode("/", $class);
                if (count($spl) == 2) $class = $spl[0];
                $file = APP::pathHelpers() . $class . '.php';
            }

            if (file_exists($filenamespace)) {
                require_once($filenamespace);
                return true;
            } else {
                if (!file_exists($file)) return false;
            }

            require_once($file);

            return true;

        } else {
            require_once($file);
            return true;
        }

    }

    function load_system_annotations($class)
    {
        $class = str_replace("\\", "/", $class);
        if (in_str(array("/"), $class)) {
            $spl = explode("/", $class);
            if (count($spl) == 2) $class = $spl[1];
        }

        $file = APP::pathDataAnnotations() . $class . '.php';

        if (!file_exists($file)) return false;
        require_once($file);

        return true;

    }

    function load_databases($class)
    {
        $class = str_replace("\\", "/", $class);
        if (in_str(array("/"), $class)) {
            $spl = explode("/", $class);
            if (count($spl) == 2) $class = $spl[0];

        }

        $file = "app/databases/{$class}.php";
        if (!file_exists($file)) return false;
        require_once($file);

        return true;
    }

    function load_libs($class)
    {
        $class = str_replace("\\", "/", $class);
        if (in_str(array("/"), $class)) {
            $spl = explode("/", $class);
            if (count($spl) == 2) $class = $spl[1];
        }

        $file = "libs/{$class}.php";
        if (!file_exists($file)) return false;
        require_once($file);

        return true;
    }

    function load_app_start($class)
    {
        $class = str_replace("\\", "/", $class);
        if (in_str(array("/"), $class)) {
            $spl = explode("/", $class);
            if (count($spl) == 2) $class = $spl[0];

        }

        $file = "app_start/{$class}.php";
        if (!file_exists($file)) return false;
        require_once($file);

        return true;
    }

    function load_user_helpers($class)
    {

        $path = "app/helpers/";

        $class = str_replace("\\", "/", $class);
        $file = $path . $class . '.php';

        if (!file_exists($file)) {
            if (in_str(array("/"), $class)) {
                $spl = explode("/", $class);
                if (count($spl) == 2) $class = $spl[0];
                $file = $path . $class . '.php';
            }

            if (!file_exists($file)) return false;
            require_once($file);
            return true;

        } else {

            require_once($file);
            return true;
        }


    }