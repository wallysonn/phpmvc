<?php

    namespace data;

    use DateTime\Date;

    class Table extends DbConnection
    {
        private $LocalInfor   = null;
        private $Conn         = null;
        private $SqlCode      = null;
        private $_data        = null; //data to insert or update
        private $_dataPart    = null; //Apenas para insert
        private $_tableName   = "";
        private $_ignoreError = "";
        private $_querySelect = "";

        function __construct($obj, $tableName = "")
        {
            $this->_tableName = $tableName;
            $this->LocalInfor = $this->getLocalInfor($obj);
            $this->SqlCode = new SqlCode();
        }

        function __destruct()
        {
            $this->reset();
            $this->close();
        }

        /****** private **********/
        private function reset()
        {
            $this->SqlCode = new SqlCode();
            $this->_data = null;
            $this->_dataPart = null;
            $this->_querySelect = "";
        }

        private function getLocalInfor($obj)
        {
            $r = new \ReflectionClass($obj);

            $DbClass = $r->getName(); //DbSite
            $DbContext = get_parent_class(new $DbClass());
            $trace = debug_backtrace();
            $tableName = ($this->_tableName == "") ? strtolower($trace[2]['function']) : $this->_tableName;
            $dbName = $DbClass::$db;
            $this->Conn = $this->connection($dbName);
            $li = new LocalInfor();
            $li->DbClass = $DbClass;
            $li->DbContext = $DbContext;
            $li->tableName = $tableName;
            $li->dbName = $dbName;

            return $li;
        }

        /**
         * Monta o campo para ser inserido em MySql
         *
         * @param $field
         *
         * @return int|string
         */
        private function getMysqlField($field)
        {
            if (substr($field, 0, 5) == "sql::") {
                $nField = substr($field, 5);
            } else {
                $field = (Date::validate($field)->isDateBr()) ? Date::format($field)->brToUs() : $field;
                if ($field === false || $field === 0) $field = "0";
                if ($field === true) $field = "1";
                if ($field == "''") $field = "";
                $nField = ($field === null || is_null($field)) ? 'null' :
                    ((is_numeric($field) && $field !== "")
                        ? ((substr($field,0,1) != "0") ? $field : $this->Conn->quote($field))
                        : $this->Conn->quote($field));
            }

            return $nField;
        }

        /**
         * String SQL para Listagem de dados
         * @return string
         */
        private function stringSelect()
        {
            return ($this->_querySelect !== "") ? $this->_querySelect : "
            SELECT 
            {$this->SqlCode->select} 
            FROM 
            `{$this->LocalInfor->tableName}`
            {$this->SqlCode->where}
            {$this->SqlCode->groupby}
            {$this->SqlCode->having}
            {$this->SqlCode->orderby}
            {$this->SqlCode->limit}
            ";
        }

        /**
         * String para a inserção de dados
         * @return string
         */
        private function stringInsert()
        {
            $data = $this->_dataPart;

            if (count($data) == 0) return "";

            $dataFirst = reset($data);

            $all_filds = array_filter($dataFirst, function ($k) {
                return substr($k, 0, 1) !== "_" && $k !== "id";
            },ARRAY_FILTER_USE_KEY);

            $all_filds = array_merge($all_filds, array());

            $listFields = "`" . implode('`,`', array_keys($all_filds)) . "`";
            $listValues = "";

            foreach ($data as $key => $arr) {
                $sqlFields = "";
                foreach ($arr as $k => $v) {
                    if (substr($k, 0, 1) !== "_" && $k !== "id") {
                        $sqlFields .= $this->getMysqlField($v) . ",";
                    }
                }
                $sqlFields = rtrim($sqlFields, ",");
                $listValues .= "({$sqlFields}),";
            }

            $listValues = rtrim($listValues, ",");
            $sql = "INSERT{$this->_ignoreError} INTO `{$this->LocalInfor->tableName}` ({$listFields}) VALUES {$listValues}";

//            echo $sql;

            return $sql;

        }

        /**
         * String para atualizar os registros
         * @return string
         */
        private function stringUpdate()
        {
            $data = $this->_data;

            if (count($data) == 0) return "";

            foreach ($data as $key => $arr) {
                $sqlFields = "";
                foreach ($arr as $column => $v) {
                    if (substr($column, 0, 1) == "_") continue;
                    $sqlFields .= "`{$column}`={$this->getMysqlField($v)},";
                }
            }

            $sqlFields = rtrim($sqlFields, ",");
            $sql = "UPDATE{$this->_ignoreError} `{$this->LocalInfor->tableName}` 
            SET {$sqlFields}{$this->SqlCode->where}{$this->SqlCode->orderby}{$this->SqlCode->limitNotOffset}";

            return $sql;
        }

        /**
         * String para Excluir os registros
         * @return string
         */
        private function stringDelete()
        {

            $sql = "DELETE{$this->_ignoreError} FROM `{$this->LocalInfor->tableName}` 
            {$this->SqlCode->where}{$this->SqlCode->orderby}{$this->SqlCode->limitNotOffset}";

            return $sql;
        }

        /**
         * Retorna os dados
         *
         * @param null $fetch
         *
         * @return ReturnSelect
         */
        private function toListMaster($fetch = null)
        {

            $ret = new ReturnSelect(array(), false, _LANG_SQL_ERROR_SELECT_);

            //Verifica a conexão
            if ($this->Conn == null) {
                $ret->errorMessage = _LANG_CONNECTION_ERROR_;

                return $ret;
            };

            try {

                $string = $this->stringSelect();

                $starttime = microtime(true);

                $rs = $this->Conn->prepare($string);
                if ($rs->execute()) {

                    //Salva o slow_log
                    $endtime = microtime(true);
                    $diff = $endtime - $starttime;
                    $diff_time = gmdate('H:i:s',$diff);
                    if (gmdate('s',$diff) >= 1) {
                        $sql_formatted = str_replace(array("'","/"),array('"',"\/"),$string);
                        $save_log = $this->Conn->prepare("INSERT IGNORE INTO slow_log SET query_time='{$diff_time}',sql_text='{$sql_formatted}'");
                        $save_log->execute();
                    }

                    if ($fetch == null) {
                        $data = $rs->fetchAll();
                    } else {
                        $data = $rs->fetchAll($fetch);
                    }

                    $ret->success = true;
                    $ret->data = $data;
                    $ret->errorMessage = "";
                }

                return $ret;

            } catch (\PDOException $e) {
                $ret->success = false;
                $ret->errorMessage = $e->getMessage();
                $ret->errorMessage = $e->getCode();

                return $ret;
            }


        }

        //CODES
        /**
         * @param string $criterion
         *
         * @return $this
         */
        function where($criterion)
        {
            try {
                if (empty($criterion) || $criterion == null) return $this;
                $this->SqlCode->where = " WHERE {$criterion}";

                return $this;
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * @param $col
         *
         * @return $this
         */
        function groupBy($col)
        {
            $allCols = (is_array($col)) ? $col : array($col);
            foreach ($allCols as $k => $c) {
                $this->SqlCode->groupby .= (($k == 0) ? " GROUP BY " : ", ") . "`{$c}`";
            }

            return $this;
        }

        /**
         * Inclui o que vai ser retornado
         *
         * @param $field
         *
         * @return $this
         */
        function select($field)
        {

            if ($field == "" || $field == null) {
                $this->SqlCode->select = "";

                return $this;
            }
            $allFields = (is_array($field)) ? $field : array($field);
            foreach ($allFields as $k => $c) {
                if (substr($c, 0, 5) !== "sql::") {
                    $c = "`{$c}`";
                } else {
                    $c = substr($c, 5);
                }
                $this->SqlCode->select .= "{$c},";
            }

            $this->SqlCode->select = rtrim($this->SqlCode->select, ",");

            return $this;
        }

        /**
         * @param string $criterion
         *
         * @return $this
         */
        function having($criterion)
        {
            try {
                if (empty($criterion) || $criterion == null) return $this;
                $this->SqlCode->having = " HAVING {$criterion}";

                return $this;
            } catch (\Exception $e) {
                die($e->getMessage());
            }
        }

        /**
         * @param string $col
         * @param string $order
         *
         * @return $this
         */
        function orderBy($col, $order)
        {
            $before = $this->SqlCode->orderby;
            $this->SqlCode->orderby .= (($before == "") ? " ORDER BY " : ", ") . "`{$col}` {$order}";

            return $this;
        }

        /**
         * @param int $offset
         * @param int $limit
         *
         * @return $this
         */
        function limit($offset, $limit)
        {
            $offset = (empty($offset) || $offset == null || is_array($offset)) ? 0 : $offset;
            $limit = (empty($limit) || $limit == null || is_array($limit)) ? 1000 : $limit;
            $offset = (int)$offset;
            $limit = (int)$limit;

            $this->SqlCode->limit = " LIMIT {$offset}, {$limit}";
            $this->SqlCode->limitNotOffset = " LIMIT {$limit}";

            return $this;

        }

        //SELECT...
        protected function toModel()
        {

        }

        /**
         * Retorna o select
         * @return ReturnSelect
         */
        public function toList()
        {
            return $this->toListMaster(null);
        }

        /**
         * Retorna o primeiro registro
         * @return array
         */
        public function first()
        {
            $data = $this->limit(0, 1)->orderBy("id", "asc")->toList()->data;

            return (count($data) > 0) ? $data[0] : array();
        }

        /**
         * Retorna o último registro
         * @return array
         */
        public function last()
        {
            $data = $this->limit(0, 1)->orderBy("id", "desc")->toList()->data;

            return (count($data) > 0) ? $data[0] : array();
        }

        /**
         * Retorna o select aplicando PDO::FETCH_ASSOC
         * @return ReturnSelect
         */
        public function toListFetch()
        {
            return $this->toListMaster(\PDO::FETCH_ASSOC);
        }

        /**
         * Retorna o select em forma de objeto
         * @return ReturnSelect
         */
        public function toListObject()
        {
            return $this->toListMaster(\PDO::FETCH_OBJ);
        }

        /**
         * Conta o número de registros
         * @return int
         */
        public function count()
        {
            $this->select("");
            $this->select("sql::COUNT(id) as total");
            $data = $this->toList()->data;

            return (isset($data[0])) ? $data[0]['total'] : 0;
        }

        /**
         * @param \PDO::FETCH_ASSOC $fetch
         *
         * @return ReturnSelect
         */
        public function toListPDOFetch($fetch)
        {
            return $this->toListMaster($fetch);
        }

        public function toListForBootstrapTable($fieldSearch=null,$countField="*",$countTotal=null){
            $sql = $this->stringSelect();
            return $this->dataForBootstrapTable($sql,$fieldSearch,$countField,$countTotal,$this->Conn);
        }

        //ADD DATA
        function add(array $data)
        {
            if (!isset($data[0])) $data = array($data);

            $this->_data = $data;

            return $this;
        }

        //INSERT
        /**Monta os registros para inserir no banco de dados
         * @return ReturnInsert
         */
        function insert()
        {
            $max = 45000;

            $datainsert = new ReturnInsert(0, false, _LANG_SQL_ERROR_INSERT_);
            $data = $this->_data;

           //$s = "";

            if (count($data) >= $max) {
                $arrayFull = array_chunk($data, $max, true);
                foreach ($arrayFull as $k => $arr) {
                    $this->_dataPart = $arr;
                    $datainsert = $this->_insert();
                    //$s .= "{$datainsert};";

                    if (!$datainsert->success) break;
                }
            } else {
                $this->_dataPart = $data;
                $datainsert = $this->_insert();
//                $s .= $datainsert;
            }
            $this->reset();

//           return $s;

            return $datainsert;
        }



        /**
         * Insere registros no banco de dados
         * @return ReturnInsert
         */
        private function _insert()
        {
            $string = $this->stringInsert();
//           return $string;


            $ret = new ReturnInsert(0, false, _LANG_SQL_ERROR_INSERT_);

            //Valida se há registros para inserir
            if (count($this->_data) == 0) {
                $ret->errorMessage = _LANG_SQL_ERROR_INSERT_NO_DATA_;

                return $ret;
            }

            //Verifica a conexão
            if ($this->Conn == null) {
                $ret->errorMessage = _LANG_CONNECTION_ERROR_;

                return $ret;
            };

            try {
                $rs = $this->Conn->prepare($string);
                if ($rs->execute()) {
                    $ret->lastId = $this->Conn->lastInsertId();
                    $ret->errorMessage = "";
                    $ret->success = true;
                }

                return $ret;

            } catch (\PDOException $e) {
                $ret->lastId = 0;
                $ret->errorMessage = $e->getMessage();
                $ret->errorNumber = $e->getCode();
                $ret->success = false;

                return $ret;
            }


        }

        //UPDATE
        /**
         * Atualiza os registros
         * @return ReturnUpdate
         */
        public function update()
        {
            $string = $this->stringUpdate();

            $ret = new ReturnUpdate(false, _LANG_SQL_ERROR_UPDATE_);

            //Valida se há registros para atualizar
            if (count($this->_data) == 0) {
                $ret->errorMessage = _LANG_SQL_ERROR_UPDATE_NO_DATA_;

                return $ret;
            }

            //Verifica a conexão
            if ($this->Conn == null) {
                $ret->errorMessage = _LANG_CONNECTION_ERROR_;

                return $ret;
            };

            try {

                $rs = $this->Conn->prepare($string);

                if ($rs->execute()) {
                    $ret->errorMessage = "";
                    $ret->affectedRows = $rs->rowCount();
                    $ret->success = true;
                }

                return $ret;

            } catch (\PDOException $e) {

                $ret->errorMessage = $this->PDOExceptionMessage($e->getMessage())   ;
                $ret->errorNumber = $e->getCode();
                $ret->success = false;

                return $ret;
            }
        }

        private function PDOExceptionMessage($message){
            if (in_str(array('Duplicate entry'),$message)){
                return "Registro duplicado no banco";
            }

            return $message;
        }

        //DELETE
        /**
         * Delete os registros
         * @return ReturnDelete
         */
        function delete()
        {
            $string = $this->stringDelete();

            $ret = new ReturnDelete(false, _LANG_SQL_ERROR_DELETE_);

            //Verifica a conexão
            if ($this->Conn == null) {
                $ret->errorMessage = _LANG_CONNECTION_ERROR_;

                return $ret;
            };

            try {

                $rs = $this->Conn->prepare($string);

                if ($rs->execute()) {
                    $ret->errorMessage = "";
                    $ret->affectedRows = $rs->rowCount();
                    $ret->success = true;
                }

                return $ret;

            } catch (\PDOException $e) {
                $ret->errorMessage = $e->getMessage();
                $ret->errorNumber = $e->getCode();
                $ret->success = false;

                return $ret;
            }
        }

        //GLOBAL FUNCTION
        /**
         * Ignore Erros em tempo de execução
         * @return $this
         */
        function ignoreError()
        {
            $this->_ignoreError = " IGNORE";

            return $this;
        }

        /**
         * Trunca a tabela.
         * @return bool
         */
        function truncate()
        {
            $sql = "TRUNCATE TABLE `{$this->LocalInfor->tableName}`";
            $ret = $this->executeQuery($sql);

            return $ret->success;
        }

        /**
         * Retorna as colunas
         * @return array
         */
        function columns()
        {
            $sql = "SHOW FULL COLUMNS FROM `{$this->LocalInfor->tableName}`";

            $ret = $this->toListForQuery($sql);
            if ($ret->success) return $ret->data;

            return array();
        }

        /**
         * @param $sql
         *
         * @return ReturnSelect
         */
        protected function toListForQuery($sql)
        {
            $this->_querySelect = $sql;

            $ret = $this->toList();

            return $ret;
        }

        /**
         * Executa uma instrução sql
         *
         * @param $sql
         *
         * @return ReturnSql
         */
        protected function executeQuery($sql)
        {
            $ret = new ReturnSql(false, _LANG_SQL_ERROR_QUERY_);

            //Verifica a conexão
            if ($this->Conn == null) {
                $ret->message = _LANG_CONNECTION_ERROR_;

                return $ret;
            };

            try {

                $rs = $this->Conn->prepare($sql);

                if ($rs->execute()) {
                    $ret->message = "";
                    $ret->affectedRows = $rs->rowCount();
                    $ret->success = true;
                }

                return $ret;

            } catch (\PDOException $e) {
                $ret->message = $e->getMessage();
                $ret->success = false;

                return $ret;
            }
        }

    }