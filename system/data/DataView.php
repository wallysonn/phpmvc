<?php

    namespace data;
    class DataView
    {
        private $conn = null;

        function __construct($conn)
        {
            $this->conn = $conn;
        }

        function create($view_name, $query, $replace = true)
        {
            $conn = $this->conn;
            $view_name = str_replace("`", "", $view_name);
            $sql_replace = ($replace) ? " OR REPLACE " : "";
            $sql = printf("CREATE %s VIEW `%s` AS %s", $sql_replace, $view_name, $conn->quote($query));

            $ret = new ReturnSql(false, _LANG_SQL_ERROR_QUERY_);
            try {
                $rs = $conn->prepare($sql);
                if ($rs->execute()) {
                    $ret->success = true;
                    $ret->message = "";
                }

                return $ret;
            } catch (\PDOException $e) {
                $ret->message = $e->getMessage();
                $ret->success = false;
                return $ret;
            }

        }

        function update()
        {

        }

        function toList()
        {

        }

        function drop()
        {

        }

    }