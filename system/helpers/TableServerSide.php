<?php

    class TableServerSide
    {

        private $_data = null;
        private $_table = null;
        private $_columns = null;

        public $class = "";
        public $messageNotFound = "Nenhum registro encontrado!";

        function __construct($id, $data)
        {
            $this->_table = new Table($id);
            $this->_data = $data;
        }

        function  column($dataField, $label, $function = "", array $paransFunction = null, $align = "left")
        {
            $this->_table->th($label, "", " style='text-align: {$align};'");
            $this->_columns[] = array(
                'field'           => $dataField,
                'function_name'   => $function,
                'function_params' => ($paransFunction == null) ? array() : $paransFunction,
                'align'           => $align
            );

            return $this;

        }

        function getTable()
        {

            if (count($this->_columns) == 0) {
                return "Not columns!";
            }

            $pregValue = function ($v) {
                return preg_replace("/{%(.*?)}/", "$1", $v);
            };

            foreach ($this->_data as $index => $field) {

                $this->_table->tr();


                foreach ($this->_columns as $key => $column) {
                    $attr = " style = 'text-align: {$column['align']} ' ";
                    if (is_array($column)) {
                        $campo = (isset($column["field"])) ? $pregValue($column["field"]) : "";
                        $datavalue = (isset($field[$campo])) ? $field[$campo] : $campo;
                        $functionName = (isset($column["function_name"])) ? $column["function_name"] : "";
                        $functionParams = (isset($column["function_params"])) ? $column["function_params"] : array();

                        if ($functionName !== "") {
                            $params = array();

                            if (!is_array($functionParams)) {
                                $this->_table->td("Param not is array!");
                            } else {
                                foreach ($functionParams as $k => $v) {
                                    $nameField = $pregValue($v);
                                    $nCol = (isset($field[$nameField])) ? $field[$nameField] : $v;

                                    //echo $field[$nameField];

                                    $params[] = ($v !== "%s") ? $nCol : $datavalue;
                                }


                                $tdValue = call_user_func_array($functionName, $params);
                                $this->_table->td($tdValue, "", $attr);
                            }
                        } else {


                            $data = preg_replace_callback("/{%(.*?)}/", function ($i) use ($field) {
                                $index = $i[1];

                                return (isset($field[$index])) ? $field[$index] : "[{$index} not exists]";
                            }, $column["field"]);

//                        $nCol = (isset($field[$nameField])) ? $field[$nameField] : $datavalue;
//                        $this->_table->td($nCol);
                            $dataSet = (isset($field[$data])) ? $field[$data] : (($data == $column["field"]) ? $column["field"] : $data);


                            $this->_table->td($dataSet, "", $attr);

                        }

                    } else {

                        $column = preg_replace_callback("/{%(.*?)}/", function ($i) use ($field) {
                            $index = $i[1];

                            return (isset($field[$index])) ? $field[$index] : "[{$index} not exists]";
                        }, $column);

                        $col = $pregValue($column);
                        if (isset($field[$col])) {
                            $this->_table->td($field[$col]);
                        } else {
                            $this->_table->td($column);
                        }
                    }
                }
            }

            $this->_table->class = $this->class;

            if (count($this->_data) == 0) {
                $this->_table->tr();
                $this->_table->tdMixed(count($this->_columns), $this->messageNotFound);

            }

            return $this->_table->getTable();

        }


    }