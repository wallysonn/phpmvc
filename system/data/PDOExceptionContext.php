<?php

    class PDOExceptionContext extends PDOException
    {
        public function __construct(PDOException $e)
        {
            $this->message = $e->getMessage();

            if (strstr($e->getMessage(), 'SQLSTATE[')) {
                preg_match('/SQLSTATE\[(\w+)\] \[(\w+)\] (.*)/', $e->getMessage(), $matches);

                if (isset($matches[1])) $this->code = ($matches[1] == 'HT000' ? $matches[2] : $matches[1]);
                $msg = $matches[3];
                $this->message = (in_str(array('Duplicate entry'),$msg)) ? "Registro duplicado no banco" : $msg;
            }

        }

        function __toString()
        {
            $code = $this->getCode();
            return sprintf(APP::getSystem('class_exceptionError'),"ERROR <strong>{$code}</strong>", utf8_encode($this->getMessage()));
        }

    }