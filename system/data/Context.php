<?php

    namespace data;

    class Context extends DbConnection
    {
        /**
         * Executa uma instrução sql e retorna o valor correspondente
         *
         * @param $sql
         *
         * @return ReturnDelete|ReturnInsert|ReturnSelect|ReturnSql|ReturnUpdate|string
         */
        public function query($sql)
        {
            $conn = $this->getConn();
            $type = $this->stringType($sql);


            switch ($type) {
                case 'insert':
                    $ret = new ReturnInsert(0, false, _LANG_SQL_ERROR_INSERT_);
                    if ($conn == null) {
                        $ret->message = _LANG_CONNECTION_ERROR_;

                        return $ret;
                    };

                    try {
                        $rs = $conn->prepare($sql);
                        if ($rs->execute()) {
                            $ret->lastId = $conn->lastInsertId();
                            $ret->message = "";
                            $ret->success = true;
                        }

                        return $ret;

                    } catch (\PDOException $e) {
                        $ret->lastId = 0;
                        $ret->message = $e->getMessage();
                        $ret->success = false;

                        return $ret;
                    }

                    break;
                case 'update':
                    $ret = new ReturnUpdate(false, _LANG_SQL_ERROR_UPDATE_);
                    if ($conn == null) {
                        $ret->message = _LANG_CONNECTION_ERROR_;

                        return $ret;
                    };

                    try {
                        $rs = $conn->prepare($sql);
                        if ($rs->execute()) {
                            $ret->success = true;
                            $ret->affectedRows = $rs->rowCount();
                        }

                        return $ret;

                    } catch (\PDOException $e) {
                        $ret->message = $e->getMessage();
                        $ret->success = false;

                        return $ret;
                    }

                    break;
                case 'select':

                    $ret = new ReturnSelect(false, _LANG_SQL_ERROR_SELECT_);
                    if ($conn == null) {
                        $ret->message = _LANG_CONNECTION_ERROR_;

                        return $ret;
                    };

                    try {

                        $rs = $conn->prepare($sql);

                        if ($rs->execute()) {
//                            showArray($rs->fetchAll());

                            $ret->success = true;
                            $ret->data = $rs->fetchAll();
                            $ret->message = "";
                        }

                        return $ret;

                    } catch (\PDOException $e) {
                        $ret->message = $e->getMessage();
                        $ret->success = false;

                        return $ret;
                    }

                    break;
                case 'delete':
                    $ret = new ReturnDelete(false, _LANG_SQL_ERROR_DELETE_);
                    if ($conn == null) {
                        $ret->message = _LANG_CONNECTION_ERROR_;

                        return $ret;
                    };

                    try {
                        $rs = $conn->prepare($sql);
                        if ($rs->execute()) {
                            $ret->success = true;
                            $ret->affectedRows = $rs->rowCount();
                        }

                        return $ret;

                    } catch (\PDOException $e) {
                        $ret->message = $e->getMessage();
                        $ret->success = false;

                        return $ret;
                    }

                    break;
                default :

                    $ret = new ReturnSql(false, _LANG_SQL_ERROR_QUERY_);
                    if ($conn == null) {
                        $ret->message = _LANG_CONNECTION_ERROR_;

                        return $ret;
                    };

                    try {
                        $rs = $conn->prepare($sql);
                        if ($rs->execute()) {
                            $ret->success = true;
                        }

                        return $ret;

                    } catch (\PDOException $e) {
                        $ret->message = $e->getMessage();
                        $ret->success = false;

                        return $ret;
                    }

                    break;

            }

            return $this->stringType($sql);
        }

        public function toListForBootstrapTable($sql,$fieldSearch=null,$countField="*",$countTotal=null,
                                                $autoIdentifyType=true,$fullTextMode=false){
            return $this->dataForBootstrapTable($sql,$fieldSearch,$countField,$countTotal, $this->getConn(),$autoIdentifyType,$fullTextMode);
        }

        public function toListForBootstrapTablePaginable($sql,$fieldSearch=null, $currentPage=1, $fullTextMode=false){
            return $this->dataForBootstrapTable($sql,$fieldSearch,"paginable::{$currentPage}",null, $this->getConn(),true,$fullTextMode);
        }

        public function view()
        {
            $conn = $this->getConn();

            return new DataView($conn);
        }

        private function stringType($string)
        {

            $_TRIM_MASK_WITH_PAREN = "( \t\n\r\0\x0B";

            $type = strtoupper(substr(ltrim($string, $_TRIM_MASK_WITH_PAREN), 0, 6));
            $type4 = strtoupper(substr(ltrim($string, $_TRIM_MASK_WITH_PAREN), 0, 4));

            if ($type === "SELECT" || $type4 === "SHOW") return "select";
            if ($type === "UPDATE") return "update";
            if ($type === "DELETE") return "delete";
            if ($type === "INSERT") return "insert";

            return "";

        }

        private function getConn()
        {
            $class = get_called_class();
            $method_db = $class::$db;

            return $this->connection($method_db);
        }

    }