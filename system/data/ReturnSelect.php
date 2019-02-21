<?php

    namespace data;

    class ReturnSelect
    {
        public $success      = false;
        public $errorMessage = "";
        public $errorNumber  = 0;
        public $data         = array();

        function __construct($data = array(), $success = false, $errorMessage = "", $errorNumber = 0)
        {
            $this->data = $data;
            $this->success = $success;
            $this->errorMessage = $errorMessage;
            $this->errorNumber = $errorNumber;
        }

    }