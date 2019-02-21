<?php

    namespace data;

    class ReturnDelete
    {
        public $success      = false;
        public $affectedRows = 0;
        public $errorMessage = "";
        public $errorNumber  = 0;

        function __construct($success = false, $errorMessage = "", $errorNumber = 0)
        {
            $this->success = $success;
            $this->errorMessage = $errorMessage;
            $this->errorNumber = $errorNumber;
        }

    }