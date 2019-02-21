<?php

namespace dynamic;

class Console{
    private $dataColors = array(
        "black"    => "30m",
        "red_b"    => "31m",
        "red"      => "1;31m",
        "error"    => "31m",
        "green_b"  => "32m",
        "green"    => "1;32m",
        "success"  => "32m",
        "yellow_b" => "33m",
        "yellow"   => "1;33m",
        "danger"   => "33m",
        "blue_b"   => "34m",
        "blue"     => "1;34m",
        "purple"   => "35m",
        "cyan_b"   => "36m",
        "cyan"     => "1;36m",
        "info"     => "36m",
        "white"    => "1;37m",
        "grey"     => "90m",
    );

    function writeLine($message){
        $msg = $this->getWriteLine($message);
        echo sprintf("%s%s",$msg,PHP_EOL);
    }

    function writeError($errorMessage){
        $this->writeLine("<red>ERROR</red>: <yellow>{$errorMessage}</yellow>");
    }

    function writeSuccess($succesMessage){
        $this->writeLine("<green>SUCCESS</green>: <white>{$succesMessage}</white>");
    }

    function writeExceptionError(\Exception $err){
        $this->writeLine("<red>ERROR {$err->getCode()}</red>\n <yellow>{$err->getMessage()}</yellow>\n <cyan>{$err->getFile()} #line {$err->getLine()}</cyan>
        ");
    }
    function getWriteLine($message){
        $message = preg_replace_callback('#\<(.*?)>{1}(.*?)\</(\w*)>{1}#i',function($m) {
            if (!isset($m['2'])) return "";
            $style = "";
            $dataTag = explode(" ",strtolower($m[1]));
            $tag = reset($dataTag);
            if (in_array("bold",$dataTag)) $style = "1;";
            $text = trim($m[2]);
            $color = (array_key_exists($tag,$this->dataColors))
                ? $this->dataColors[$tag]
                : $this->dataColors['white'];
            return sprintf("\e[%s%s\e[0m",$color,$text,$style);
        },$message);
        return strip_tags($message);
    }
}