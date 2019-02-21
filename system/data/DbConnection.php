<?php

    namespace data;

    use DateTime\Date;
    use DbClients\App;
    use PDOException;
    use PDOExceptionContext;
    use SystemSecurity\Security;
    use SystemString\StringText;

    class DbConnection
    {

        private $conn      = array();
        private $currentDb = null;

        private function setConn($dataname, $conn)
        {
            //if (array_key_exists($dataname, $this->conn)) {
            //Atualiza
            //$this->conn[$dataname] = $conn;
            //} else {
            //Inserir
//                $this->conn[$dataname][] = $conn;
            //}

            if (!isset($this->conn[$dataname])) $this->conn[$dataname] = $conn;

        }

        protected function connection($db = "")
        {
            try {
                sessionStart();
                $APP = \MyApp::CONFIG();
                $connectionMethodSession = $APP['connectionMethod'] == "session" && $db != "clients";
                $dataSession = null;

//                echo "db: $db \n<br>";

                if ($connectionMethodSession) {

//                    echo "method: session \n<br>";

                    if (!isset($_SESSION[DF_SESSION_CONNECION_NAME]) || empty($_SESSION[DF_SESSION_CONNECION_NAME]))
                        throw new \PDOException(utf8_decode("Conexão expirada ou inexistente"));
                    $session = Security::decrypt($_SESSION[DF_SESSION_CONNECION_NAME]);
                    //host|database|user|pass|utf8

                    if (!StringText::find($session)->contains("|"))
                        throw  new \PDOException("Estring de conexão inválida");
                    $session = explode("|", $session);

                    $dataSession['host'] = (isset($session[0])) ? $session[0] : '';
                    $dataSession['database'] = (isset($session[1])) ? $session[1] : '';
                    $dataSession['user'] = (isset($session[2])) ? $session[2] : '';
                    $dataSession['pass'] = (isset($session[3])) ? $session[3] : '';
                    $dataSession['encoding'] = (isset($session[4])) ? $session[4] : 'utf8';
                    $database = $dataSession['database'];

                } else {
//                    echo "method: array \n<br>";
                    $first_dbname = \APP::getFisrtNameDatabase();
                    $database = ($db == "") ? $first_dbname : $db;
                }

                $this->currentDb = $database;
                $activeConnection = (array_key_exists($database, $this->conn)) ? $this->conn[$database] : null;

//                var_dump($activeConnection);

                if (is_null($activeConnection)) {

                    if ($connectionMethodSession) {

                        $host = $dataSession["host"];
                        $user = $dataSession["user"];
                        $pass = $dataSession["pass"];
                        $prefix = '';
                        $data = "{$prefix}{$dataSession["database"]}";
                        $enconding = ($dataSession["encoding"] == "") ? "UTF8" : $dataSession["encoding"];
                    } else {
                        $array_conn = \APP::getDataBaseParams($database);
                        $host = $array_conn["host"];
                        $user = $array_conn["user"];
                        $pass = $array_conn["pass"];
                        $prefix = $array_conn["prefix"];
                        $data = $prefix . $array_conn["db"];

                        $enconding = ($array_conn["encoding"] == "") ? "UTF8" : $array_conn["encoding"];
                    }

                    $system = \APP::getSystem();
                    $maxTimeout = (isset($system['maxConnectionTimeout'])) ? $system['maxConnectionTimeout'] : 5;

                    $op = array(
                        \PDO::ATTR_TIMEOUT            => $maxTimeout,
                        \PDO::MYSQL_ATTR_INIT_COMMAND => sprintf("SET NAMES {$enconding}", (empty($enconding) ? 'utf8' : $enconding)),
                        \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION
                    );

//                    echo "mysql:host={$host};dbname={$data} - {$user} - {$pass}<br>";

                    $activeConnection = @new \PDO("mysql:host={$host};dbname={$data}", $user, $pass, $op);
                    $this->setConn($database, $activeConnection);
                }

                return $activeConnection;

            } catch (PDOException $e) {
                $this->close();
                die(new PDOExceptionContext($e));
            }
        }

        protected function close()
        {
            if (isset($this->conn[$this->currentDb])) $this->conn[$this->currentDb] = null;
            //sessionStart();
            //if (isset($_SESSION[DF_SESSION_DATA_CONNECION][$this->currentDb])) $_SESSION[DF_SESSION_DATA_CONNECION][$this->currentDb] = null;
        }

//        protected function dataForBootstrapTable($sql, $fieldSearch = null, $countField = "*", $countTotal = null, $conn = null)
//        {
//
//            if (!class_exists("BootstrapTable")) return array();
//            if ($sql == "") return array();
//
//            //Parametros
//            $limit = getUrlParam('limit');
//            $offset = getUrlParam('offset');
//            $sort_field = getUrlParam('sort');
//            $order = getUrlParam('order');
//            $search = getUrlParam('search');
//
//            $objSql = StringText::convert($sql)->SqlStringToObject();
//            $countField = (empty($countField)) ? "*" : $countField;
//
//            //Verifica se tem pesquisa
//            $critSearch = "";
//            if ($fieldSearch !== null && is_array($fieldSearch) && $search !== "") {
//                foreach ($fieldSearch as $field) {
//                    $critSearch .= "{$field} LIKE '%{$search}%' OR ";
//                }
//                $objSql->where .= (($objSql->where == "") ? "" : " AND ") . " (" . rtrim($critSearch, " OR ") . ") ";
//            }
//
//            //Verifica se tem order by
//            $objSql->orderBy = ($sort_field !== "") ? " ORDER BY {$sort_field} {$order} " : "";
//
//
//            //Monta o count
//            $where = ($objSql->where == "") ? "" : " WHERE {$objSql->where} ";
//            $sql_total = "SELECT count({$countField}) as total FROM {$objSql->from} {$where} {$objSql->having} ";
//
//            //Verifica se tem limit
//            $objSql->limit = ($limit > 0) ? " LIMIT {$offset},{$limit} " : "";
//
//            //Mosta a query
//            $groupBy = ($objSql->groupBy !== "") ? " GROUP BY {$objSql->groupBy} " : "";
//            $sql_query = "SELECT {$objSql->select} FROM {$objSql->from} {$where} {$groupBy} {$objSql->having}{$objSql->orderBy}{$objSql->limit}";
//
//
//            if ($sqlcount !== "") $sql_total = $sqlcount;
//
//            $dataCount = $this->query($sql_total)->toListFetch();
//            $total = $dataCount[0]['total'];
//
//            if ($total == 0) return array('total' => 0, 'rows' => array());
//
//            $dataRows = $this->query($sql_query)->toListFetch();
//            $ret = array('total' => $total, 'rows' => $dataRows);
//
//
////            if ($debug) {
////                $ret['sqltotal'] = $sql_total;
////                $ret['sqlrows'] = $sql_query;
////                $ret['sqlfull'] = $sql;
////            }
//
//            $sql_ret = preg_replace('/limit.*/i', '', $sql_query);
//            $ret['qrystr'] = Security::encrypt($sql_ret);
//
//            return $ret;
//
//        }


        /**
         * @param        $sql
         * @param null   $fieldSearch
         * @param string $countField
         * @param null   $countTotal
         * @param null   $conn
         * @param bool   $autoIdentifyType
         *
         * @param bool   $fullTextMode
         *
         * @return string
         */
        protected function dataForBootstrapTable($sql, $fieldSearch = null, $countField = "*",
                                                 $countTotal = null, $conn = null,
                                                 $autoIdentifyType = false,$fullTextMode=false)
        {

            $limit = getUrlParam('limit', 1000);
            $offset = getUrlParam('offset', 0);
            $sort_field = getUrlParam('sort');
            $order = getUrlParam('order');
            $search = (Date::validate(getUrlParam('search'))->isDate()) ? Date::format(getUrlParam('search'))->brToUs() : getUrlParam('search');

            $isPaginate = StringText::find($countField)->contains("paginable::");

            $crit_search = "";
            if ($fieldSearch !== null && is_array($fieldSearch) && $search !== "") {

                $critComp = " LIKE '{$search}%' ";
                if ($autoIdentifyType) {
                    if (is_numeric($search)) {
                        $critComp = " = '{$search}' ";
                    }
                }
                if ($fullTextMode){
                    $crit_search = sprintf(" AND MATCH(%s) AGAINST('%s' IN BOOLEAN MODE) ",implode(",",$fieldSearch), StringText::convert(trim($search))->toFulltextExpression());
                }else {
                    foreach ($fieldSearch as $k => $field) {
                        $crit_search .= "{$field} {$critComp} OR ";
                    }
                    $crit_search = sprintf("AND %s",sprintf( (count($fieldSearch) > 1 ? "(%s)" : "%s"),rtrim($crit_search, " OR ")) );
                    //$crit_search = sprintf(" AND CONCAT_WS(' ',%s) LIKE '%s%%' ",implode(",",$fieldSearch), $search);
                }

            }

            if (!in_str(array('where'), $sql)) {
                if (in_str(array('group by'), $sql)) {
                    $crit_search = " WHERE 1=1" . $crit_search;
                    $crit_search = str_ireplace("group by", $crit_search . " group by", $sql);
                } else {
                    $crit_search = $sql . " WHERE 1=1" . $crit_search;
                }
            } else {
                if (in_str(array('group by'), $sql)) {
                    $crit_search = str_ireplace("group by", " {$crit_search} GROUP BY ", $sql);
                } else {
                    $crit_search = $sql . $crit_search;
                }
            }

            $crit_sort = ($sort_field !== "") ? " ORDER BY {$sort_field} {$order} " : " ";
            $crit_limit = ($limit > 0) ? " LIMIT {$offset},{$limit} " : "";

            $sql = $crit_search . $crit_sort . $crit_limit;


            if ($countTotal == null && !$isPaginate) {

                if (in_str(array("having"), $sql)) {

                    $sql_total = preg_replace("/limit (.+)/i", "", $sql);
                    $sql_total = "SELECT count({$countField}) as boot_total_rows FROM ({$sql_total}) as qt";

                } else {

                    $sql_total = \Regex::replaceBetween("select", "from", " count({$countField}) as boot_total_rows ", $sql);
                    $sql_total = preg_replace("/order by.*?(?=\\s*limit|\\)|$)/i", "", $sql_total);
                    $sql_total = preg_replace("/group by.*?(?=\\s*order|having|limit\\)|$)/i", "", $sql_total);
                    $sql_total = preg_replace("/limit (.+)/i", "", $sql_total);
                    //$sql_total = preg_replace("/left join .*?(?=\\s*left join|right join|inner join|outer join|where|order|limit|having\\)|$)/mi", "", $sql_total);
                    //$sql_total = preg_replace("#left join.*?(?=\\s*left join|right join|inner join|outer join|where|order|limit|having\\)|$)#si", "", $sql_total);
                    #$sql_total = preg_replace('#left join.*left join#si', "", $sql_total);
                    $sql_total = preg_replace("/group by.*?(?=\\)|$)/mi", "", $sql_total);
                }

//                $starttime = microtime(true);
                $rs = $conn->prepare($sql_total);
                $countTotal = 0;

                if ($rs->execute()) {

//                    $endtime = microtime(true);
//                    $diff = $endtime - $starttime;
//                    $diff_time = gmdate('H:i:s', $diff);

//                    if (gmdate('s', $diff) >= 1) {
//                        $sql_formatted = str_replace(array("'", "/"), array('"', "\/"), $sql_total);
//                        $save_log = $conn->prepare("INSERT IGNORE INTO slow_log SET query_time='{$diff_time}',sql_text='{$sql_formatted}'");
//                        $save_log->execute();
//                    }

                    $datac = $rs->fetchAll(\PDO::FETCH_ASSOC);
                    $countTotal = $datac[0]['boot_total_rows'];
                }
            }

            if ($countTotal > 0 || $isPaginate) {
//                $starttime = microtime(true);
                $rs = $conn->prepare($sql);
                if ($rs->execute()) {

//                    $endtime = microtime(true);
//                    $diff = $endtime - $starttime;
//                    $diff_time = gmdate('H:i:s', $diff);
//                    if (gmdate('s', $diff) >= 1) {
//                        $sql_formatted = str_replace(array("'", "/"), array('"', "\/"), $sql);
//                        $save_log = $conn->prepare("INSERT IGNORE INTO slow_log SET query_time='{$diff_time}',sql_text='{$sql_formatted}'");
//                        $save_log->execute();
//                    }

                    $datac = $rs->fetchAll(\PDO::FETCH_ASSOC);

                    return json_encode(array('total' => ($isPaginate) ? count($datac) : $countTotal, 'rows' => $datac), JSON_NUMERIC_CHECK);
                }
            }

            return json_encode(array('total' => 0, 'rows' => array()), JSON_NUMERIC_CHECK);

        }

    }