<?php

    namespace data;

    class ReturnInsert
    {
        public $lastId       = 0;
        public $success      = false;
        public $errorMessage = '';
        public $errorNumber  = 0;

        function __construct($lastId = 0, $success = false, $errorMessage = "", $errorNumber = 0)
        {
            $this->lastId = $lastId;
            $this->success = $success;
            $this->errorMessage = $errorMessage;
            $this->errorNumber = $errorNumber;
        }
    }