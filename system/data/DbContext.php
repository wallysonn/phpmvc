<?php

    use DateTime\Date;
    use SystemArray\DataArray;
    use SystemSecurity\Security;
    use SystemString\StringText;

    ini_set("max_execution_time", "-1");
    ini_set("memory_limit", "-1");
//    ignore_user_abort(true);
//    set_time_limit(0);


    class DbContext extends DbConnection
    {
        private $conn              = null;
        private $tableName         = null;
        private $methods           = array();
        private $_fields           = array();
        private $_limit            = "";
        private $_rowsAdd          = array(); //Recebe todos os registros
        private $_rowsAddPart      = array(); //Manda de 50.000 em 50.000 registros para o insert
        private $_rowsUpdate       = array();
        private $_where            = "";
        private $_orderby          = "";
        private $_innerJoin        = array();
        private $parentClassName   = "";
        private $parentClass;
        private $objValidator;
        private $validateExiste    = false;
        private $_groups           = "";
        private $_arraygroups      = array();
        private $_having           = "";
        private $classException    = "";
        private $datatableName     = "";
        private $_index            = array();
        private $_validateerros    = null;
        public  $persistConnection = false;
        private $_dbname           = "";
        private $_query            = "";
        private $_insertIgnore     = false;
        private $_errorMessage     = '';
        private $realParentClass   = '';

        function __construct()
        {

            $system = APP::getSystem();
            if (isset($system['class_exceptionError'])) {
                $this->classException = $system['class_exceptionError'];
            }

            $class = get_called_class();
            $method_db = $class::$db;

            try {
                $this->_dbname = $method_db;
                $this->conn = $this->connection($method_db);
            } catch (PDOException $e) {

                throw new Exception($e->getMessage());
            }

        }

        function __destruct()
        {
            $this->closeConnection();
        }

        function setdatavalidation(array $errors)
        {
            $this->_validateerros = $errors;

            return $this;
        }

        function databaseName()
        {
            $myapp = MyApp::$DATABASE;

            return (isset($myapp[$this->_dbname]['db'])) ? $myapp[$this->_dbname]['db'] : "";
        }

        private function reset()
        {
            $this->tableName = null;
            $this->methods = array();
            $this->_fields = array();
            $this->_limit = "";
            $this->_rowsAdd = array();
            $this->_rowsAddPart = array();
            $this->_rowsUpdate = array();
            $this->_where = "";
            $this->_orderby = "";
            $this->_innerJoin = array();
            $this->parentClassName = "";
            $this->parentClass;
            $this->objValidator = null;
            $this->validateExiste = false;
            $this->_groups = "";
            $this->_arraygroups = array();
            $this->_having = "";
            $this->classException = "";
            $this->datatableName = "";
            $this->_index = array();
            $this->_validateerros = null;
            $this->_query = "";
            $this->_insertIgnore = false;
        }

        function getConn()
        {
            return $this->conn;
        }

        function useIndex($index)
        {
            $this->_index[] = $index;

            return $this;

        }

        private function closeConnection()
        {
            if (!$this->persistConnection) {
                $this->close();
            }
        }

        function setTable($name)
        {
            $this->datatableName = $name;
        }

        function getTableName()
        {
            return strtolower($this->datatableName);
        }

        function set($class)
        {
            try {

                $dataTableName = $this->getTableName();
                $this->realParentClass = $class;

                $this->tableName = ($dataTableName == "") ? strtolower($class) : $dataTableName;
                $this->parentClassName = $class;

                $objClass = $class;

                if (!class_exists($class)) {
                    $cls = get_called_class();
                    $objClass = $cls . "_" . $class;
                }


                if (class_exists($objClass)) {

                    $obj = new $objClass;
                    $this->parentClass = $obj;

                    //                    $dtValid = get_parent_class($objClass);
                    //                    $extend_validator = ($dtValid == "DataValidator") ? $dtValid : "";
                    //                    if ($extend_validator !== "") {
                    //                        $this->parentClass->_requestdatavalidate();
                    //                        $this->validateExiste = true;
                    //                    } else {
                    //                        $this->validateExiste = false;
                    //                    }


                    $array_fields = (get_object_vars($obj));

                    foreach ($array_fields as $field => $value) {
                        if ((substr($field, 0, 1) == '_' && substr($field, 0, 2) != "__")) $this->methods[] = $field;
                    }

                    if (count($this->methods) == 0) {
                        $reflect = new ReflectionClass($obj);
                        $props = $reflect->getProperties(ReflectionProperty::IS_PRIVATE);
                        foreach ($props as $prop) {
                            $prop_name = $prop->getName();
                            if ((substr($prop_name, 0, 1) == '_' && substr($prop_name, 0,
                                    2) != "__")
                            ) $this->methods[] = $prop_name;
                        }
                    }
                } else {
                    throw new Exception("The class [{$objClass}] does not exist");
                }
            } catch (Exception $e) {
                //echo "<div class='{$this->classException}'>{$e->getMessage()}</div>";
            }

        }

        function getToListString()
        {
            if ($this->_query !== "") return $this->_query;
            $innerJoin = "";
            foreach ($this->_innerJoin as $key => $param) {
                $innerJoin .= $param['sql'];
            }

            return "SELECT {$this->getFields()} FROM `{$this->tableName}`{$this->getUseIndex()}{$innerJoin}{$this->getWhere()}{$this->getGroupBy()}{$this->getHaving()}{$this->getOrderBy()}{$this->_limit}";
        }

        function toListInJson()
        {
            return json_encode($this->toListMaster());

        }

        /**
         * @param string (array|object) $datatype
         *
         * @return array|object
         */
        function first()
        {
            $data = $this->limit(0, 1)->orderBy('id', 'asc')->toList();
            return (count($data) > 0) ? $data[0] : array();
        }

        function parseToObject($object, array $data)
        {
            try {

                if (!is_object($object)) throw new Exception('the variable ($object) must be an object');
                if (!is_array($data)) throw new Exception('the variable ($data) must be an array');

                foreach ($data as $prop => $value) {
                    if (property_exists($object, $prop)) $object->{$prop} = $value;
                }

                return $object;
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }

        function last()
        {
            $data = $this->limit(0, 1)->orderBy('id', 'desc')->toList();

            return (count($data) > 0) ? $data[0] : array();
        }

        function toList()
        {
            return $this->toListMaster();
        }

        function toListFetch($pdofetchAll = PDO::FETCH_ASSOC)
        {
            return $this->toListMaster($pdofetchAll);
        }

        function tolistForSelectList($field_text, $field_value, $text_noData = "", $noDataValue = "", $fieldGroup = null)
        {
            $data = $this->toList();
            return DataArray::convert($data)->toSelectList($field_value,$field_text,$text_noData,$noDataValue,$fieldGroup);
        }

        function count($field = "*")
        {
            try {

                $this->_fields = array(); //Limpa em caso do usuário já ter passado um parametro
                $field = ($field == "" || $field == null || is_null($field) || empty($field)) ? "*" : $field;
                $this->select("sql::COUNT({$field}) as total");
                $lst = $this->toListMaster();

                return (isset($lst[0]['total'])) ? $lst[0]['total'] : 0;

            } catch (Exception $e) {
                return $e;
            }

        }

        function toListWithDetail()
        {

            $ret = array();

            $start = microtime(true);
            $data = $this->toListMaster();
            $end = microtime(true);
            $ret['data'] = $data;
            $ret['detail'] = array('executetime' => ($end - $start));

            return $ret;

        }

        private function toListMaster($fetch = null)
        {

            $ret = array();

            try {
                if ($this->conn == null) return $ret;
                $rs = $this->conn->prepare($this->getToListString());
                $this->reset();
                if ($rs->execute()) {
                    if ($fetch == null) {
                        $ret = $rs->fetchAll();
                    } else {
                        $ret = $rs->fetchAll($fetch);
                    }

                    return $ret;

                } else {
                    return array();
                }
            } catch (PDOException $e) {

                return array();
                // echo "SQL:: " . $e->getMessage() . "\n";
            }


        }

        function select($field = "", $table = "")

        {
            $tbl = ($table == "") ? $this->tableName : $table;

            $this->_fields[] = array('field' => $field, 'table' => $tbl);

            return $this;
        }

        function selectAllBase()
        {
            $this->_fields[] = array('field' => "sql::" . $this->tableName . ".*", 'table' => $this->tableName);

            return $this;
        }

        /**
         * @param array or string $fields
         *
         * @return $this
         */
        function groupBy($field)
        {

            if (is_array($field)) {
                $this->_arraygroups = $field;
            } else {
                $this->_arraygroups[] = $field;
            }

            return $this;
        }

        function having($condition)
        {
            $this->_having = $condition;

            return $this;
        }

        function ignoreError()
        {
            $this->_insertIgnore = true;

            return $this;
        }

        function includeInner($table, $reverse = false, $joinType = JoinType::inner)
        {

            if (!$reverse) {
                $sql = " {$joinType} JOIN `{$table}` ON {$this->tableName}.{$table}_id = {$table}.id ";
            } else {
                $sql = " {$joinType} JOIN `{$table}` ON {$this->tableName}.id = {$table}.{$this->tableName}_id ";
            }

            $this->_innerJoin[] = array('table' => $table, 'sql' => $sql);

            return $this;

        }

        function includeFullInner($table1, $table2, $reverse = false, $joinType = JoinType::inner)
        {

            if (!$reverse) {
                $sql = " {$joinType} JOIN `{$table1}` ON `{$table2}`.{$table1}_id = `{$table1}`.id ";
            } else {
                $sql = " {$joinType} JOIN `{$table2}` ON `{$table1}`.{$table2}_id = `{$table2}`.id ";
            }

            $this->_innerJoin[] = array('table' => $table1, 'sql' => $sql);

            return $this;

        }

        function orderBy($field, $order)
        {
            $f = ($field == "") ? "" : ((substr($field, 0, 5) == "sql::") ? str_replace("sql::", "",
                $field) : "`{$field}`");
            $order = "{$f} {$order},";
            $this->_orderby .= $order;

            return $this;
        }

        /**
         * @param $condition
         *
         * @return $this
         */
        function where($condition)
        {
            $this->_where = $condition;
            return $this;
        }

        function limit($ini, $end)
        {
            $this->_limit = " LIMIT {$ini}, {$end}";

            return $this;
        }

        function add(array $row, $conditionForUpdate = "")
        {

            if (isset($row[0])) {
                $this->addAll($row);
            } else {
                $this->_rowsAdd[] = $row;
                $this->_rowsUpdate[] = array('fields' => $row, 'condition' => $conditionForUpdate);
            }

            return $this;
        }

        function addAll(array $row)
        {
            $this->_rowsAdd = $row;
            $this->_rowsUpdate = $row;

            return $this;
        }

        function columns()
        {
            $table = $this->tableName;
            $sql = "SHOW FULL COLUMNS FROM `{$table}`";

            return $this->query($sql)->toList();
        }

        /**
         * @param $sql
         *
         * @return $this
         */
        function query($sql)
        {
            $sql = trim($sql);
            $this->_query = $sql;

            return $this;
        }

        function tolistForBootstrapTable(array $fields_for_search = null, $table_order = "", $field_count = "*", $debug = false, $sqlcount = "", array $aditionalData = null)
        {

            if ($this->_where == "") $this->where("1=1");
            if (is_array($fields_for_search)) {
                $fields_for_search = array_map("columnSqlFormat", $fields_for_search);
            }

            return json_encode($this->getDataForBootstrapTable($this->getToListString(), $fields_for_search, $table_order,
                $field_count, $debug, $sqlcount, $aditionalData));
        }

        function getDataForBootstrapTable($sql, array $fields_for_search = null, $table_order = "", $field_count = "*", $debug = false, $sqlcount = "", array $aditionalData = null)
        {

            if (!class_exists("BootstrapTable")) return array();
            if ($sql == "") return array();

            $distinct = (stristr($sql, "group by") !== false) ? " DISTINCT " : "";


                //$sql = preg_replace("/group by.*?(?=\\s*order|having|limit\\)|$)/i", "", $sql);


            $fromcount = BootstrapTableAntigo::getCriterionForPaginationServer($fields_for_search, $table_order, false, $sql);
            $fromcount = str_replace("\r\n", '', $fromcount); // remove as quebras
            $fromcount = trim($fromcount);
            $sqltotal = preg_replace("/from_unixtime.*?/i", "fntime", $fromcount);
            $sqltotal = preg_replace("/from_days.*?/i", "fndays", $sqltotal);

            $sqltotal = trim($sqltotal);
            $exp = explode(" ", $sqltotal);

            foreach ($exp as $k => $text) {
                $text = trim($text);
                if (strtolower($text) == "select") {
                    $exp[$k] = "SELECT count({$distinct}" . (($field_count == "" OR $field_count == null) ? "*" : $field_count) . ") as boot_count_records, ";
                    break;
                }
            }

            $sqltotal = implode(" ", $exp);
            $sqltotal = preg_replace("/order by.*?(?=\\s*limit|\\)|$)/i", "", $sqltotal); //remove order from query
            $sqltotal = preg_replace("/fntime.*?/i", "FROM_UNIXTIME", $sqltotal);
            $sqltotal = preg_replace("/fndays.*?/i", "FROM_DAYS", $sqltotal);
            $sqltotal = preg_replace("/group by.*?(?=\\s*order|having|limit\\)|$)/i", "", $sqltotal); //remove order from query

            if ($sqlcount == "" || $sqlcount == null) {
                $data_count = $this->query($sqltotal)->toList();
                $total = (isset($data_count[0]['boot_count_records'])) ? $data_count[0]['boot_count_records'] : 0;
            } else {
                $where_count = "";
                $search = getUrlParam('search');
                if ($fields_for_search !== null && is_array($fields_for_search) && $search !== "") {
                    foreach ($fields_for_search as $k => $field) {

                        $l = "";
                        $r = "";

                        if (in_str(array('*'), $search)) {

                            $field = trim($search);
                            $first = substr($search, 0, 1);
                            $last = substr($search, -1);

                            if ($first == "*" || $first == "%") {
                                $l = "%";
                                $search = ltrim($search, "*");
                            }

                            if ($last == "*" || $last == "%") {
                                $r = "%";
                                $search = rtrim($search, "*");
                            }

                            if ($l == "" && $r == "") {
                                $l = "%";
                                $r = "%";
                            }


                        } else {
                            $l = "%";
                            $r = "%";
                        }
                        $where_count .= "{$field} LIKE '{$l}{$search}{$r}' OR ";
                    }
                    $where_count = " AND (" . rtrim($where_count, " OR ") . ") ";
                }
                if (!in_str(array('where'), $sqlcount)) {
                    $where_count = " WHERE 1=1" . $where_count;
                }

                $sqlcount = $sqlcount . $where_count;

                $total = $this->query($sqlcount)->count();
                $sqltotal = $sqlcount;
            }


            $condition = BootstrapTable::getCriterionForPaginationServer($fields_for_search, $table_order, true, $sql);
            //if (in_str(array('where'),$sql) == false) $condition = " WHERE 1=1 ".$condition;
            //            $newSql = str_ireplace("group by",$condition." group by",$sql);
            //
            //            $sqlfull =  $newSql;

            $sqlfull = $condition;

            $data = $this->query($sqlfull)->toList();

            $ret = array
            (
                'total'         => $total,
                'rows'          => $data,
                'aditionaldata' => $aditionalData
            );

            if ($debug) {
                $ret['sqltotal'] = $sqltotal;
                $ret['sqlrows'] = $sqlfull;
                $ret['sqlfull'] = $sql;
            }

            $sql_ret = preg_replace('/limit.*/i', '', $sqlfull);
            $ret['qrystr'] = Security::encrypt($sql_ret);

            return $ret;

        }

        function getUpdateString()
        {

            if ($this->_query !== "") return $this->_query;

            $sql = "";
            $innerJoin = "";
            foreach ($this->_innerJoin as $key => $param) {
                $innerJoin .= $param['sql'];
            }

            foreach ($this->_rowsUpdate as $key => $fields) {
                $campos = "";
                foreach ($fields['fields'] as $column => $value) {

                    if (substr($value, 0, 5) !== "sql::") {

                        $vl = (Date::validate($value)->isDateBr()) ? Date::format($value)->brToUs() : $value;

                    } else {
                        $vl = $value;
                    }

                    $campos .= "`{$this->tableName}`.`{$column}` = " . $this->getMysqlField($vl) . ",";

                }
                $campos = substr($campos, 0, -1);
                $where = ($fields['condition'] !== "") ? "WHERE {$fields['condition']}" : $this->getWhere();

                $ignore = ($this->_insertIgnore) ? " IGNORE " : " ";

                $sql .= "UPDATE{$ignore}`{$this->tableName}` {$innerJoin} SET {$campos} {$where};";
            }
            $sql = substr($sql, 0, -1);

            return $sql;
        }

        function returnObjectUpdated($updated)
        {

            $namespace = "\\" . get_called_class();
            $class = $namespace . "\\" . $this->realParentClass;

            $reflectionClass = new ReflectionClass($class);
            $className = $reflectionClass->getName();
            $reflectionProp = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

            $obj = new $className;
            $obj->success = false;

            if ($updated) {

                $rows = $this->_rowsUpdate;
                //move to last
                $rows = end($rows);

                $rows = $rows['fields'];
                $obj->success = true;

                $obj->errorMessage = '';

                foreach ($reflectionProp as $prop) {
                    $propname = $prop->getName();
                    if (isset($rows[$propname])) $obj->{$propname} = $rows[$propname];
                }

                $reflectionClass = null;
                $reflectionProp = null;

            } else {

                $obj->error = json_encode(
                    array(
                        'message' => $this->_errorMessage,
                        'field'   => '',
                        'type'    => 'error'
                    )
                );

            }

            return $obj;

        }

        function getErrorMessage()
        {
            return $this->_errorMessage;
        }

        function update($returnType = ReturnType::integer)
        {

            $ret = new ReturnUpdate();
            $ret->message = "Sem conexão com internet";
            $ret->success = false;
            if ($this->conn == null) return $ret;

            try {
                $rs = $this->conn->prepare($this->getUpdateString());
                $success = $rs->execute();
                if ($success) {
                    $ret->message = "";
                    $ret->success = true;
                } else {
                    $ret->message = _LANG_UNKNOWN_ERROR_;
                    $ret->success = false;
                }
                $object = $this->returnObjectUpdated($success);
                $this->reset();
            } catch (PDOException $e) {
                $this->_errorMessage = $e->getMessage();

                $ret->message = $e->getMessage();
                $ret->success = false;

                return ($returnType == ReturnType::object) ? $this->returnObjectUpdated(false) : $ret;
            }

            return ($returnType == ReturnType::object) ? $object : $ret;

        }

        function getDeleteString()
        {
            if ($this->_query !== "") return $this->_query;

            return "DELETE FROM `{$this->tableName}`{$this->getWhere()}";
        }

        function delete()
        {
            if ($this->conn == null) return false;
            $rs = $this->conn->prepare($this->getDeleteString());
            $this->reset();

            return $rs->execute();
        }

        function requestData()
        {
            if (isset($_POST)) {
                $arr = array();

                foreach ($_POST as $column => $value) {
                    if (substr($column, 0, 1) !== "_") {
                        $arr[$column] = $value;
                    }
                }

                $this->add($arr);
            }

            return $this;
        }

        function getInsertString()
        {
            if ($this->_query !== "") return $this->_query;

            if (count($this->_rowsAddPart) == 0) return "";

            $list_fields = "`" . implode('`,`', array_keys(reset($this->_rowsAddPart))) . "`";
            $list_values = "";

            foreach ($this->_rowsAddPart as $key => $arr) {

                $sql_fields = "";

                foreach ($arr as $k => $v) {

                    if (substr($v, 0, 5) !== "sql::") {
                        $vl = (Date::validate($v)->isDateBr()) ? Date::format($v)->brToUs() : $v;
                    } else {
                        $vl = $v;
                    }

                    $sql_fields .= $this->getMysqlField($vl) . ",";

                }

                $sql_fields = rtrim($sql_fields, ",");
                $list_values .= "({$sql_fields}),";

            }
            $list_values = rtrim($list_values, ",");

            $ignore = ($this->_insertIgnore) ? " IGNORE " : " ";

            $sql = "INSERT{$ignore}INTO `{$this->tableName}` ({$list_fields}) VALUES {$list_values}";
            return $sql;
        }

        private function returnObjectInserted($lastInsertId)
        {
            $namespace = "\\" . get_called_class();
            $class = $namespace . "\\" . $this->realParentClass;
            $reflectionClass = new ReflectionClass($class);
            $className = $reflectionClass->getName();

            $obj = new $className;
            $obj->id = $lastInsertId;

            $obj->success = false;

            if ($lastInsertId > 0) {

                $rows = $this->_rowsAdd;
                $rows = end($rows);

                $reflectionProp = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

                $obj = new $className;
                $obj->id = $lastInsertId;
                $obj->success = true;
                $obj->errorMessage = '';
                foreach ($reflectionProp as $prop) {
                    $propname = $prop->getName();
                    if (isset($rows[$propname]) && $propname !== 'id') $obj->{$propname} = $rows[$propname];
                }

                $reflectionClass = null;
                $reflectionProp = null;
            } else {
                $obj->error = json_encode(
                    array(
                        'message' => $this->_errorMessage,
                        'field'   => '',
                        'type'    => 'error'
                    )
                );
            }

            return $obj;

        }

        function getInsertAndUpdateDuplicateKeyString()
        {
            try {

                $sqlInsert = $this->getInsertString();
                if ($sqlInsert == "") return null;

                $arr_fields = array();

                foreach ($this->_rowsAddPart as $key => $arr) {
                    foreach ($arr as $k => $v) {
                        $arr_fields[] = "`{$k}` = VALUES(`{$k}`)";
                    }
                }

                $fieldsUpdate = implode(",", array_values($arr_fields));
                $sqlInsert .= " ON DUPLICATE KEY UPDATE {$fieldsUpdate}";

                return $sqlInsert;

            } catch (Exception $e) {
                return $e->getMessage();
            }
        }

        /**
         * @param string $returnType
         *
         * @return object|ReturnInsert
         */
        function insertAndUpdateDuplicateKey($returnType = ReturnType::integer)
        {
            $this->_errorMessage = '';
            $max = 45000;
            $array = $this->_rowsAdd;
            $ret = new ReturnInsert();

            if (count($array) >= $max) {
                $array_full = array_chunk($array, $max, true);
                foreach ($array_full as $k => $arr) {
                    $this->_rowsAddPart = $arr;
                    $ret = $this->_insertAndUpdateDuplicateKey();
                    if (!$ret->success) break;
                }
            } else {
                $this->_rowsAddPart = $array;
                $ret = $this->_insertAndUpdateDuplicateKey();
            }

            $object = $this->returnObjectInserted($ret->lastId);
            $this->reset();

            return ($returnType == ReturnType::object) ? $object : $ret;

        }

        /**
         * @return ReturnInsert
         */
        private function _insertAndUpdateDuplicateKey()
        {
            $ret = new ReturnInsert();
            $ret->lastId = 0;
            $ret->success = false;
            $ret->message = _LANG_CONNECTION_ERROR_;

            if (count($this->_rowsAdd) == 0 || $this->tableName == "" || $this->conn == null) return $ret;

            $rs = $this->conn->prepare($this->getInsertAndUpdateDuplicateKeyString());

            try {

                if ($rs->execute()) {
                    $ret->lastId = $this->conn->lastInsertId();
                    $ret->success = true;
                    $ret->message = "";
                } else {
                    $ret->lastId = 0;
                    $ret->success = false;
                    $ret->message = _LANG_UNKNOWN_ERROR_;
                }
                return $ret;

            } catch (PDOException $e) {

                $this->_errorMessage = $e->getMessage();
                $ret->lastId = 0;
                $ret->success = false;
                $ret->message = $e->getMessage();
                return $ret;
            }

        }

        /**
         * @param string $returnType
         *
         * @return mixed|ReturnInsert
         */
        function insert($returnType = ReturnType::integer)
        {

            $this->_errorMessage = '';
            $max = 45000;
            $array = $this->_rowsAdd;
            $datainsert = new ReturnInsert();

            if (count($array) >= $max) {
                $array_full = array_chunk($array, $max, true);
                foreach ($array_full as $k => $arr) {
                    $this->_rowsAddPart = $arr;
                    $datainsert = $this->_insert();
                    if (!$datainsert->success) break;
                }
            } else {
                $this->_rowsAddPart = $array;
                $datainsert = $this->_insert();
            }

            $object = $this->returnObjectInserted($datainsert->lastId);
            $this->reset();
            return ($returnType == ReturnType::object) ? $object : $datainsert;

        }

        private function _insert()
        {

            $ret = new ReturnInsert();
            $ret->lastId = 0;
            $ret->message = "Sem Dados";
            $ret->success = false;

            if (count($this->_rowsAdd) == 0 || $this->tableName == "" || $this->conn == null) return $ret;

            $rs = $this->conn->prepare($this->getInsertString());

            try {

                if ($rs->execute()) {
                    $ret->lastId = $this->conn->lastInsertId();
                    $ret->message = "";
                    $ret->success = true;
                } else {
                    $ret->lastId = 0;
                    $ret->message = "Erro desconhecido";
                    $ret->success = false;
                }

                return $ret;
            } catch (PDOException $e) {
                $this->_errorMessage = $e->getMessage();
                $ret->lastId = 0;
                $ret->message = $e->getMessage();
                $ret->success = false;
                return $ret;
            }


        }

        function truncate()
        {
            $table = $this->tableName;

            $rs = $this->conn->prepare("TRUNCATE TABLE `{$table}`");

            return $rs->execute();

        }

        function toListInPolling($sleep = 0, $compare = "AND", $field_on_update = "on_update")
        {

            if ($this->conn == null) return json_encode(array('result' => false, 'data' => array()));

            set_time_limit(-1);

            $timeStart = $this->currentTimerMysql();
            $where = ($this->getWhere() == "") ? " WHERE `{$field_on_update}` > '{$timeStart}'" : $this->getWhere() . " {$compare} `{$field_on_update}` > '{$timeStart}'";
            $sql = "SELECT {$this->getFields()} FROM `{$this->tableName}`{$where}{$this->getOrderBy()}{$this->_limit}";


            while (true) {

                $rs = $this->conn->prepare($sql);
                if (@$rs->execute()) {
                    $data = $rs->fetchAll();
                    if (count($data) > 0) {
                        return array('result' => true, 'data' => $data);
                        break;
                    } else {
                        if ($sleep > 0) sleep($sleep);
                    }
                } else {
                    break;
                }
            }
        }

        //######################## PRIVATE ####################

        private function getGroupBy()
        {
            $this->_groups = implode(", ", array_values($this->_arraygroups));

            return ($this->_groups == "") ? "" : " GROUP BY {$this->_groups} ";
        }

        private function getHaving()
        {
            return ($this->_having == "") ? "" : " HAVING {$this->_having} ";
        }

        private function currentTimerMysql()
        {
            $rs_timer = $this->conn->prepare("SELECT NOW() as Now");
            $rs_timer->execute();
            $lin_timer = $rs_timer->fetchObject();

            return $lin_timer->Now;
        }

        private function getMysqlField($field)
        {
            if (substr($field, 0, 5) == "sql::") {
                $nField = substr($field, 5);
            } else {
                if ($field === false || $field === 0) $field = "0";
                if ($field === true) $field = "1";
                if ($field == "''") $field = "";
                $nField = ($field === null || is_null($field)) ? 'null' : ((is_numeric($field)) ? $field : $this->conn->quote($field));
            }

            return $nField;
        }

        private function getFields()
        {
            if (count($this->_fields) == 0) {
                $fields = "`" . $this->tableName . "`.*";
                foreach ($this->_innerJoin as $k => $param) {
                    $fields .= ", `{$param["table"]}`.id as '{$param["table"]}id',`{$param["table"]}`.*";
                }

                return $fields;
            }

            $f = "";
            $arr = array();

            foreach ($this->_fields as $k => $param) {
                $tbl = $param["table"];
                $field = $param["field"];

                if (substr($field, 0, 5) == "sql::") {
                    $campo = str_replace("sql::", "", $field);

                    if (in_array($campo, $arr)) {
                        $campo = $campo . " as '{$tbl}_{$campo}'";
                    }

                    $f .= $campo . ", ";
                    $arr[] = $campo;
                } else {

                    if (in_array($field, $arr)) {
                        $field = $field . " as '{$tbl}_{$field}'";
                    }

                    $f .= "`$tbl`.$field, ";
                    $arr[] = $field;
                }

            }

            $arr = null;
            $f = substr($f, 0, -2);

            return $f;

        }

        private function getWhere()
        {
            return ($this->_where != "") ? " WHERE " . $this->_where : "";
        }

        private function getOrderBy()
        {
            return ($this->_orderby != "") ? " ORDER BY " . substr($this->_orderby, 0, -1) : "";
        }

        private function getUseIndex()
        {
            if (count($this->_index) > 0) {
                return " USE INDEX (`" . implode("`, `", array_values($this->_index)) . "`)";
            } else {
                return "";
            }

        }

        /** VIEWS **/

        /**
         * @view_name Nome da view
         * @query     estring sql
         * @replace   se é para alterar caso exista
         **/
        function CREATE_VIEW($view_name, $query, $replace = true)
        {
            $conn = $this->conn;
            $view_name = str_replace("`", "", $view_name);
            $sql_replace = ($replace) ? " OR REPLACE " : "";
            $sql = printf("CREATE %s VIEW `%s` AS %s", $sql_replace, $view_name, $conn->quote($query));

            $rs = $conn->prepare($sql);

            return $rs->execute();

        }

        function LIST_VIEWS($condition = "", $orderby = "NONE")
        {
            $orderby = StringText::convert($orderby)->toUpper();
            $str_condition = ($condition !== "" AND $condition !== null) ? " AND {$condition} " : "";
            $order = ($orderby == "ASC" || $orderby = "DESC") ? " ORDER BY TABLE_NAME {$orderby} " : "";

            $database = $this->databaseName();
            $sql = " SELECT TABLE_NAME  as 'view' FROM information_schema.tables WHERE TABLE_TYPE LIKE 'VIEW' AND TABLE_SCHEMA = '{$database}'{$str_condition}{$order}";

            return $this->query($sql)->toList();
        }

        function DROP_VIEW($view_name)
        {
            $conn = $this->conn;
            $view_name = str_replace("`", "", $view_name);
            $sql = printf("DROP VIEW `%s`", $view_name);

            $rs = $conn->prepare($sql);

            return $rs->execute();
        }

        function UPDATE_VIEW($view_name, $new_query)
        {
            $this->DROP_VIEW($view_name);

            return $this->CREATE_VIEW($view_name, $new_query, true);
        }
    }

    abstract class VIEWSCONDITION
    {
        static function name_begins_with($text)
        {
            return "TABLE_NAME REGEXP '^{$text}.*'";
        }

        static function name_ends_with($text)
        {
            return "TABLE_NAME REGEXP '.*{$text}$'";
        }

        static function name_contains($text)
        {
            return "TABLE_NAME REGEXP '{$text}'";
        }

        static function name_not_contains($text)
        {
            return "TABLE_NAME NOT REGEXP '{$text}'";
        }
    }

    abstract class JoinType
    {
        const onlyJoin = '';
        const left     = "LEFT";
        const right    = "RIGHT";
        const inner    = "INNER";
    }

    abstract class ReturnType
    {
        const integer = 'integer';
        const object  = 'object';
    }