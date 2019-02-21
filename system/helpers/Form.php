<?php

    class Form extends UserHtml
    {

        private $start_html = "";

        function __construct($name, $controller, $action, $method = "post", $class = "", $multipart = false)
        {
            $mp = ($multipart) ? " enctype=\"multipart/form-data\" " : "";
            $newaction = Html::action($controller, $action);
            $html = "<form name='{$name}' id='{$name}' action='{$newaction}' method='{$method}' class='{$class}' {$mp}>";
            $this->start_html = $html;
        }

        function create()
        {
            return $this->start_html;
        }

        function end()
        {
            $html = "</form>";

            return $html;
        }
    }