<?php

    use data\DbConnection;
    use data\MagicConnection;

    require_once 'system/require.php';

    $path = "app/controllers";
    if (!is_dir($path)) mkdir($path); //Cria a pasta caso não exista

    $saveRule = function(array $controllers){

        $config = MyApp::$AUTHORIZE_SERVER;
        $db = (isset($config['database'])) ? $config['database'] : "";
        $table = (isset($config['table'])) ? $config['table'] : "";
        $page_field = (isset($config['page_field'])) ? $config['page_field'] : "";
        $level_field = (isset($config['level_field'])) ? $config['level_field'] : "";
        $active_field = (isset($config['active_field'])) ? $config['active_field'] : "";

        if ($table !== "" && $page_field !== "" && $level_field !== "" && $active_field !== "") {
            $c = new MagicConnection();

            $cn = $c->conn($db);

            if ($cn !== null) {

                $sql = "INSERT IGNORE INTO `{$table}` (`{$page_field}`) VALUES ";

                foreach ($controllers as $controller_name) {
                    $controller_name = str_replace("Controller.php","",$controller_name);
                    $sql .= "('" . strtolower($controller_name) . "_index'),";
                    $sql .= "('" . strtolower($controller_name) . "_adicionar'),";
                    $sql .= "('" . strtolower($controller_name) . "_editar'),";
                    $sql .= "('" . strtolower($controller_name) . "_excluir'),";
                }

                $sql = rtrim($sql,",");

                try {

                    $rs = $cn->prepare($sql);
                    $rs->execute();
                } catch (Exception $e) {
                    echo "Authorize Server ERROR: Verify if table rules exists!\n";
                }
            }

        } else {
            echo "ERROR DbConnection!\n";
        }

    };

    if (isset($_SERVER['argv'][1]) && $_SERVER['argv'][1] == 'refresh-rules') {
        echo "Updating validation rules...\n";

        //Escolhe a conexão
        $path_connection = "app/databases";

        $controllers = array_map('basename', glob($path . "/*.php"));
        $saveRule($controllers);
        echo "SUCCESS\n";
        exit();
    }

    do {
        echo "Controller Name: ";
        $controller_name = strtolower(fgets_u());
        if (file_exists("{$path}/{$controller_name}Controller.php")) {
            echo "Controller [{$controller_name}Controller.php] already exists. Try another name.\n";
        } else {
            break;
        }
    } while (true);


    do {
        echo "Authorize type: 0 => none, 1 => client, 2 => server:";
        $authorize = fgets_u();

        if ($authorize >= 0 && $authorize <= 2 && is_numeric($authorize)) {
            break;
        } else {
            echo "ERROR: value is: 0, 1 or 2. \n";
        }

    } while (true);

    $str_authorize = ($authorize == 0) ? "" : (($authorize == 1) ? "->authorize()" : "->authorizeServer()");

    if ($authorize == 2) {
        $saveRule(array($controller_name));
    }

    $output = "<?php\n";
    $output .= "
    /**
     * Created on: " . date('d/m/Y H:i:s') . "
     * Author: TWMVC
     */\n";

    $output .= "\n\tuse libs\\Controller;\n\n";

    $output .= "\tclass " . ucfirst($controller_name) . " extends Controller \n\t{\n";

    //Folder view
    $pathview = "app/views/" . ucfirst($controller_name);
    if (!is_dir($pathview)) mkdir($pathview);

    try {

        $output .= "\t\tfunction index_action()\n\t\t{\n";
        $output .= "\t\t\t" . 'return $this->view()' . $str_authorize . '->inLayout()->show();' . "\n";
        $output .= "\t\t}\n\n";

        $output .= "\t\t/** USER ACTION **/\n";

        //End class
        $output .= "\t}";

        $fp = fopen("{$path}/{$controller_name}Controller.php", 'w');
        fwrite($fp, $output);
        fclose($fp);

    } catch (Exception $e) {
    }

    //Create views

    for ($i = 1; $i <= 4; $i++) {

        $output = "<?php\n";
        $output .= "
    /**
     * Created on: " . date('d/m/Y H:i:s') . "
     * Author: TWMVC
     */\n";

        switch ($i) {
            case 1:
                $fileName = "index.phtml";
                break;

            case 2:
                $fileName = "adicionar.phtml";
                break;

            case 3:
                $fileName = "editar.phtml";
                break;

            case 4:
                $fileName = "excluir.phtml";
                break;
        }

        $fileOutPut = "{$pathview}/{$fileName}";

        $fp = fopen($fileOutPut, 'w');
        fwrite($fp, $output);
        fclose($fp);

        //Remove Bom
        $fnRemoveBom = function ($str = "") {
            if (substr($str, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
                $str = substr($str, 3);
            }

            return $str;
        };

        $string = file_get_contents($fileOutPut);
        $string = $fnRemoveBom($string);
        file_put_contents($fileOutPut, $string);


    }


    echo "Success\n";