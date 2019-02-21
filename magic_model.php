<?php

    require_once 'system/require.php';
    require "app/lang/pt-br.php";

    $path_databases = "app/databases/";

    function rmvExt($file)
    {
        return str_replace(".php", "", $file);
    }

    $list_databases = array_map('rmvExt', array_map('basename', glob($path_databases . "*.php")));

    echo "Especify database number:\n\n";

    foreach ($list_databases as $i => $db) {
        echo "$i - $db\n";
    }

    do {

        echo "\nDatabase ID: ";
        $database = fgets_u();
        $database = (isset($list_databases[$database])) ? $list_databases[$database] : "undefined";

        if (file_exists($path_databases . $database . ".php")) {
            $c = new $database();
            $database = get_class($c); //Retorna o nome real

            break;
        } else {
            echo "Database [{$database}] not exists!\n";
        }

    } while (true);

    $path = "app/models/" . $database;
    if (!is_dir($path)) mkdir($path); //Cria a pasta caso não exista

    $db = new $database();
    $reflection_class = new ReflectionClass($db);
    $methods = $reflection_class->getMethods(ReflectionMethod::IS_PUBLIC);

    $array_methods = array();

    echo "\nEspecify method number:\n\n";
    $i = 0;
    foreach ($methods as $mt) {
        if ($mt->class == $database) {
            $mName = $mt->getName();
            $mUcname = ucfirst($mName);

            if (!file_exists("{$path}/{$mUcname}.php")) {
                $array_methods[] = $mName;
                echo "$i - " . $mName . "\n";
                $i++;
            }
        }
    }


    echo "Model ID: ";
    $model_name = fgets_u();
    $model_name = (isset($array_methods[$model_name])) ? ucfirst($array_methods[$model_name]) : 'undefined';


    $output = "<?php\n";
    $output .= "
    /**
     * Created on: " . date('d/m/Y H:i:s') . "
     * Author: TWMVC
     */\n";

    $output .= "\tnamespace {$database}; \n\n";
    $output .= "\tuse {$database};\n";
    $output .= "\tuse SystemBootstrap\\BeginForm;\n";
    $output .= "\tuse SystemBootstrap\\Form;\n";
    $output .= "\tuse libs\\Model;\n\n";

    $output .= "\tclass {$model_name} extends Model \n\t{\n";

    //Busca as colunas da tabela
    try {


        $listcolumns = $db->{$model_name}()->columns();


        if (count($listcolumns) == 0) die("Table nots exists in database" . PHP_EOL);

        $array_insert = "";

        $field_num_auto = "id";

        $arrayGettersAndSetters = array();

        foreach ($listcolumns as $k => $field) {

            $ignore = false;

            $default = (is_numeric($field['Default'])) ? $field['Default'] : "'{$field['Default']}'";

            if (strstr($field['Type'], 'varchar') !== false || strstr($field['Type'], 'text') !== false) $string = true;

            if (strstr($field['Type'], 'int') !== false ||
                strstr($field['Type'], 'double') !== false ||
                strstr($field['Type'], 'bigint') !== false ||
                strstr($field['Type'], 'integer') !== false
            ) {
                if ($field['Default'] == "" || $field['Default'] == null) $default = "null";
                $numeric = true;
            }

            if (strstr($field['Type'], 'tinyint') !== false) {
                if ($field['Default'] == "" || $field['Default'] == 0 || $field['Default'] == null) $default = 0;
            }

            if ($field['Type'] == 'timestamp') $ignore = true;
            if ($field['Default'] == null) $default = 'null';

            if ($field['Extra'] == 'auto_increment') {
                $default = 0;
                $field_num_auto = $field['Field'];
            }

            $array_comment = array();

            if ($field['Extra'] == 'auto_increment') {
                $array_comment[] = "@DataType hidden";
            }

            if ($field['Extra'] !== 'auto_increment') {

                if ($field['Null'] == 'NO') {
                    $fieldname = $field['Field'];
                    $array_comment[] = "@Required O campo {{field}} é obrigatório!";
                }
                if (strstr($field['Type'], 'int') !== false || strstr($field['Type'], 'integer') !== false || strstr($field['Type'], 'bigint') !== false) {
                    $array_comment[] = "@Format numeric";
                }
            }

            if (count($array_comment) > 0) {
                $output .= "\n\t\t/**\n";
                foreach ($array_comment as $vComment) {
                    $output .= "\t\t * {$vComment}\n";
                }
                $output .= "\t\t */\n";
            }

            $comment = ($field['Comment'] !== "") ? " //{$field['Comment']}" : "";

            if ($ignore == false) $output .= "\t\t" . 'public $' . $field['Field'] . " = " . $default . ";{$comment}\n";
        }

        $array_insert = rtrim($array_insert, ",\n");

        $output .= "\n\n\t\t/** METHODS **/\n\n";

        $output .= "\t\t" . 'function showForm($frmName, $controller, $action)' . "\n";
        $output .= "\t\t{\n";
//        $output .= "\t\t\t" . '$' . strtolower($model_name) . ' = new ' . $model_name . '($this->id);' . "\n";
        $output .= "\t\t\t" . '$frm = new BeginForm($frmName,$controller,$action);' . "\n";
//        $output .= "\t\t\t" . '$frm->addContent(Form::EditorFor($' . strtolower($model_name) . ',"id",false));' . "\n\n";
        $output .= "\t\t\t" . '$frm->addContent(Form::EditorFor($this,"id",false));' . "\n\n";
        $output .= "\t\t\t" . 'return $frm->getBeginForm();' . "\n";
        $output .= "\t\t}\n\n";

        //$frm->addContent(Form::EditorFor($vehicle,'id',false));

        //End class
        $output .= "\t}";

        $fileOutPut = "{$path}/{$model_name}.php";

        $fp = fopen($fileOutPut, 'w');
        fwrite($fp, pack("CCC", 0xef, 0xbb, 0xbf)); //UTF-8 charset page
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

    } catch (Exception $e) {
    }

    echo "Success\n";