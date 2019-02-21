<?php

    class Ajax
    {

        public $url = "";
        public $type = "POST";
        public $dataArray = array();
        public $dataJson = "{}";
        public $async = true;

        private $content = "";

        function beforeSend($jsFunction)
        {
            $this->content .= $jsFunction;
        }

        function sucess($result, $status, $xhr)
        {

        }

        function getAjax()
        {

        }


    }