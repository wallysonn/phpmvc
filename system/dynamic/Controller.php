<?php

namespace dynamic;

use traits\Directory;

class Controller extends Console implements iDynamic {

    use Directory;

    const CLT = "Controller";

    public function run($params=null)
    {
        try{

            $controllerName = $params;
            if (stristr($controllerName,self::CLT) == false) $controllerName .= self::CLT;
            $controllerName = str_ireplace(".php","",$controllerName);
            $filename = ROOT_PATH."\\app\\controllers\\{$controllerName}.php";
            if (file_exists($filename)) throw new \Exception("File [{$filename}] has exists");
            $fileTemplate = sprintf("%s\\template\\%s.tp",__DIR__,self::CLT);
            if (!file_exists($fileTemplate)) throw new \Exception("File template [{$fileTemplate}] not exists");
            $className = ucfirst(strtolower(trim(str_replace(self::CLT,"",$controllerName))));
            $templateContent = template(file_get_contents($fileTemplate),[
                'ClassName' => $className
            ]);

            //Criar o arquivo
            @file_put_contents($filename,$templateContent);
            if (!file_exists($filename)) throw new \Exception("Fail to create file [{$filename}]");

            //Cria a pasta em view
            $viewPath = ROOT_PATH."\\app\\views\\{$className}";
            if (!$this->createDir($viewPath)) {
                $this->removeFile($filename);
                throw new \Exception("Fail to create view path [{$viewPath}]");
            }

            //Cria um view index
            $fileView = sprintf("%s\\index.phtml",$viewPath);
            $fileViewTemplate = sprintf("%s\\template\\indexView.tp",__DIR__);
            if (!file_exists($fileViewTemplate)) throw new \Exception("File template [{$fileViewTemplate}] not exists");
            $fileViewContent = template(file_get_contents($fileViewTemplate),[
                'title' => $className
            ]);
            @file_put_contents($fileView,$fileViewContent);

            $this->writeSuccess("Controller {$className} successfully created");

        }catch (\Exception $err){
            $this->writeExceptionError($err);
        }

    }
}