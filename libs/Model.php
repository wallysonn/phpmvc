<?php

    namespace libs;

    use data\ReturnDelete;
    use data\ReturnInsert;
    use data\ReturnSelect;
    use DataAnnotations\Annotation;
    use DateTime\Date;
    use Exception;
    use NumberFormat\Number;
    use ReflectionClass;
    use ReflectionProperty;
    use ReturnType;
    use SystemString\StringText;

    class Model extends Annotation implements iModel
    {
        /**
         *
         */
        protected $_db                    = null;
        private   $_fields                = array();
        private   $_id                    = 0;
        private   $_table                 = '';
        private   $_ignoreMessageError    = false;
        private   $_magicparams           = array();
        private   $_getRequestData        = false;
        private   $_getRequestDataType    = 'post';
        private   $_fieldsRequest         = false;
        private   $_reflectionClass       = null;
        private   $_ignoreValidate        = false;
        private   $_ignoreValidateFields  = null;
        private   $_hasFieldByRequestData = false;
        private   $_comments              = false; //Quando não há comentários, retorna false!
        private   $_properties            = array();
        private   $_jqueryValidate        = array();
        private   $datavalid              = null;
        private   $_dataweb               = array();

        /** Magic Method *
         *
         * @param int $id
         *
         */
        function __construct($id = 0)
        {
            try {

                $fullName = $this->getTableFullName();

                try {
                    $this->_reflectionClass = new ReflectionClass($fullName);
                } catch (\ReflectionException $err) {
                    throw  new Exception($err->getMessage());
                }

                $dbname = $this->getDatabaseName();
//                echo "DB: $dbname <br>";

                $this->_db = new $dbname();

                $this->_table = $this->getTableName();
                $this->_id = $id;

                if ($id > 0) $this->getProperties($id);

                return $this->detailObject();

            } catch (Exception $err) {

                return null;
            }
        }

        function __set($name, $value)
        {
            $this->_magicparams[$name] = $value;

            return $this;
        }

        function __get($name)
        {
            try {
                if (!isset($this->_magicparams[$name])) throw new Exception(sprintf(_LANG_PARAM_NOT_EXISTS_, $name));

                return $this->_magicparams[$name];
            } catch (Exception $e) {
                return $e->getMessage();
            }

        }

        /** Private Function *
         *
         * @param $comment
         * @param $datavalue
         *
         * @return int|mixed|null|string
         */
        private function inputValue($comment, $datavalue)
        {
            $return = $datavalue;

            if ($comment === false) return $return;


            foreach (preg_split("/(\r?\n)/", $comment) as $line) {

                //2 - O comentário deve começar com um asterico
                if (preg_match('/^(?=\s+?\*[^\/])(.+)/', $line, $matches)) {
                    $info = $matches[1];
                    //Remove espaços em branco
                    $info = trim($info);

                    //Remove asterisco a esquerda
                    $info = preg_replace('/^(\*\s+?)/', '', $info);

                    $info = trim(ltrim($info, '*')); //remove o asterisco caso tenha espaços

                    //Deve começar com @
                    if ($info !== "") {
                        if ($info[0] == "@") {

                            //Nome do parametro (nome da função)
                            preg_match('/@(\w+)/', $info, $matches);
                            $param_name = StringText::convert($matches[1])->toLower();

                            $value = str_ireplace("@$param_name", '', $info);
                            $value = rtrim(ltrim(trim($value), "("), ")");
                            $value = rtrim(ltrim($value, "'"), "'");
                            $value = rtrim(ltrim($value, '"'), '"');
                            $value = StringText::convert($value)->toLower();

                            if ($param_name == 'format') {

                                $data_value = explode(",", $value);

                                foreach ($data_value as $vl) {

                                    $vl = trim($vl);

                                    switch ($vl) {
                                        case 'date':
                                            $isDate = Date::validate($datavalue)->isDate();
                                            $return = ($isDate) ? $datavalue : null;
                                            break;
                                        case 'numeric':
                                            $f = Number::get($datavalue)->onlyNumbers();
                                            $return = ($f == "") ? null : $f;
                                            break;
                                        case 'string':
                                            $return = (string)$datavalue;
                                            break;
                                        case 'onlytext':
                                            $return = StringText::convert($datavalue)->toOnlyText();
                                            break;
                                        case 'integer':
                                        case 'int':
                                            $return = Number::format($datavalue)->toDatabase()->integer();
                                            break;
                                        case 'double':
                                        case 'float':
                                            $return = Number::format($datavalue)->toDatabase()->decimal(2);
                                            break;
                                        case 'uppercase':
                                        case 'ucase':
                                            $return = StringText::convert($datavalue)->toUpper();
                                            break;
                                        case 'lowercase':
                                            $return = StringText::convert($datavalue)->toLower();
                                            break;
                                        case 'peoplename':
                                            $return = StringText::convert($datavalue)->toPeopleName();
                                            break;
                                        case 'removeaccents':
                                            $return = StringText::format($datavalue)->removeAccents();
                                            break;
                                        case 'email':
                                            $return = StringText::format($datavalue)->email();
                                            break;
                                        case 'ucfirst':
                                            $return = StringText::convert($datavalue)->toUcFirst();
                                            break;
                                        default:
                                            if (in_str(array('::'), $vl)) {
                                                $spl = explode("::", $vl);
                                                $class = $spl[0];
                                                if (class_exists($class)) {
                                                    if (isset($spl[1])) {
                                                        $method = $spl[1];
                                                        if (method_exists(new $class(), $method)) {
                                                            $return = call_user_func_array(array($class, $method), array($datavalue));
                                                        }
                                                    }
                                                }
                                            } else {
                                                if (function_exists($vl)) {
                                                    $return = call_user_func($vl, $datavalue);
                                                }
                                            }
                                            break;
                                    }

                                    $datavalue = $return;

                                }
                            }

                        }
                    }
                }
            }

            return $return;

        }

        private function getDatabaseName()
        {
            return "\\" . $this->_reflectionClass->getNamespaceName();
        }

        private function getDataTableFields()
        {
            if (!empty($this->_dataweb)) return $this->_dataweb;
            $id = $this->_id;
            $ret = $this->_db->{$this->_table}()->where("id={$id}")->toListFetch()->data;
            $this->_dataweb = (count($ret) > 0) ? $ret[0] : array();

            return $this->_dataweb;
        }

        public function getFields()
        {
            return $this->_fields;
        }

        private function getProperties($fieldId)
        {

            $reflectionClass = $this->_reflectionClass;
            $property = array();
            $data = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);
            $obj = (array)$this;

            $id = $this->_id;
            if ($id == 0) $id = $fieldId;
            if ($id == "") $id = 0;

            $data_web = $this->_dataweb;

            if ($id > 0) {
                //$this->_id = $id;
                $data_web = $this->getDataTableFields();

            }

            foreach ($data as $prop) {
                $name = $prop->getName();
                $comment = $prop->getDocComment();
                $this->_comments[$name] = $comment;
                $jquery_rules[$name] = "";

                if (substr($name, 0, 1) !== "_") {
                    $vl = (isset($data_web[$name])) ? $data_web[$name] : $this->inputValue($comment, $obj[$name]);
                    $property[$name] = $vl;
                }
            }

            $this->_properties = $property;

            $property = null;
            $data = null;

        }

        private function getTableFields()
        {

            try {

//                if ($this->_fieldsRequest) return;

                $property = array();

                $updateLocalField = function () use (&$property) {
                    $real_prop = $this->_properties;

                    $isEditable = $this->_id > 0;

                    $obj = (array)$this;
                    $reflectionClass = $this->_reflectionClass;

                    $data = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

                    $defaultValues = $reflectionClass->getDefaultProperties();

                    foreach ($data as $prop) {
                        $name = $prop->getName();


                        $defaultvalue = (isset($defaultValues[$name])) ? $defaultValues[$name] : null;

                        //echon("$name -- ". (is_null($defaultvalue) ? "NULO" : $defaultvalue) );

                        if (!isset($this->_comments[$name])) {
                            $comment = $prop->getDocComment();
                            $this->_comments[$name] = $comment;
                        }

                        if (substr($name, 0, 1) !== "_") {


                            if ($isEditable) {

                                if (stripos($this->_comments[$name], "@ignoreonupdate") !== false) continue;

                                $vl = $this->inputValue($this->_comments[$name], $obj[$name]);

                                if ($vl !== $real_prop[$name] && $vl !== $defaultvalue) {
                                    $property[$name] = $vl;
                                    $this->{$name} = $vl;
                                } else {
                                    $property[$name] = $real_prop[$name];
                                    $this->{$name} = $real_prop[$name];
                                }
                            } else {
                                $vl = $this->inputValue($this->_comments[$name], $obj[$name]);
                                $this->{$name} = $vl;
                                $property[$name] = $vl;
                            }

                        }
                    }
                };

                $updateLocalField();

                if ($this->_getRequestData) {
                    $this->_hasFieldByRequestData = true;
                    $arr_requestData = ($this->_getRequestDataType == 'post') ? $_POST : $_GET;

                    if ($this->_id == 0 && isset($arr_requestData['id'])) $this->_id = $arr_requestData['id'];

                    //events
                    $class = get_called_class();
                    if ($this->_id > 0) {

                        if (count($this->_properties) == 0) {
                            $this->getProperties($this->_id);
                        }

                        $onUpdate = '__onUpdate';
                        if (method_exists($class, $onUpdate)) {
                            $this->{$onUpdate}();
                        }
                        $updateLocalField();
                    }

                    $onInsert = '__onInsert';
                    if (method_exists($class, $onInsert) && $this->_id == 0) {
                        $this->{$onInsert}();
                        $updateLocalField();
                    }

                    foreach ($arr_requestData as $field => $value) {
                        try {
                            if (substr($field, 0, 1) !== "_") {

                                if (array_key_exists($field, $this->_comments)) {

                                    $comment = $this->_comments[$field];

                                    if ($this->_id > 0 && stripos($comment, "@ignoreonupdate")) continue;

                                    $vl = $this->inputValue($comment, $value);

                                    if (array_key_exists($field, $property)) $property[$field] = $vl;
                                    if (property_exists($this, $field)) $this->{$field} = $vl;

                                }
                            }

//                            if ($field == 'id' && $this->_id == 0) $this->_id = $value;

                        } catch (Exception $e) {

                        }
                    }


                }


                $this->_fields = $property;
                $this->_fieldsRequest = true;

                return $property;

            } catch (Exception $e) {

            }
        }

        private function getTableFullName()
        {
            return get_called_class();
        }

        private function getTableName()
        {
            $fullname = $this->getTableFullName();
            $spl = explode('\\', $fullname);

            return $spl[1];
        }

        /**
         * @param $ret
         * @param $id
         *
         * @return mixed
         */
        private function objectToReturn($ret, $id)
        {
            $rf = $this->_reflectionClass;
            $class = $rf->getName();
            $obj = new $class($id);
            $obj->id = $id;
            $obj->success = $ret->success;
            $obj->errorMessage = $ret->errorMessage;

            if ($ret->success) {

                $rows = $this->_fields;
                $lastRow = end($rows);
                if (count($lastRow) > 0) $rows = $lastRow;

                $reflectionProp = $rf->getProperties(ReflectionProperty::IS_PUBLIC);

                foreach ($reflectionProp as $prop) {
                    $propname = $prop->getName();
                    if (isset($rows[$propname]) && $propname != 'id') $obj->{$propname} = $rows[$propname];

                }

                $reflectionProp = null;

            }


            return $obj;

        }

        /**
         * Retorna um Objeto com os dados inseridos
         *
         */
        private function insert()
        {
            $ins = $this->_db->{$this->_table}()->add($this->_fields);

            if ($this->_ignoreMessageError === true) $ins->ignoreError();
            $ret = $ins->insert();

            return $this->objectToReturn($ret, $ret->lastId);

        }

        //Todo Corrigir isto
        private function insertAndUpdateDuplicateKey()
        {
            $ins = $this->_db->{$this->_table}()->add($this->_fields);
            if ($this->_ignoreMessageError === true) $ins->ignoreError();

            return $ins->insertAndUpdateDuplicateKey(ReturnType::object);

        }

        /**
         * @return mixed
         */
        private function update()
        {
            $up = $this->_db->{$this->_table}()->add($this->_fields);
            if ($this->_ignoreMessageError === true) $up->ignoreError();

            $ret = $up->where("id={$this->_id}")->update();

            return $this->objectToReturn($ret, $this->_id);
        }

        /** Public Function **/
        function isValid()
        {
            $this->getTableFields();
            if ($this->_ignoreValidate && $this->_ignoreValidateFields == null) return true;
            if ($this->datavalid == null) {
                return $this->validate($this->getTableFullName(), $this->_fields, $this, $this->_ignoreValidateFields, ($this->_id > 0));
            } else {
                return $this->datavalid;
            }

        }

        function isValidField($field)
        {
            //$this->getTableFields();
            if ($this->_ignoreValidate && $this->_ignoreValidateFields == null) return true;

            if (array_key_exists($field, $this->_fields)) {
                $nfield[$field] = $this->_fields[$field];

                return $this->validate($this->getTableFullName(), $nfield, $this, $this->_ignoreValidateFields, ($this->_id > 0));
            }

            return true;


        }

        /**
         * @return bool|mixed|null|string
         */
        public function save()
        {
            $this->getTableFields();

            //$valid = $this->_isValidated;
            //if ($this->_isValidated == false)


            $valid = $this->isValid();


            if ($valid !== true) return $valid;

            if ($this->_id > 0) return $this->update();


            return $this->insert();
        }

        //Todo Modificar
        public function saveAndUpdateDuplicateKey()
        {
            $this->getTableFields();
            $valid = $this->isValid();

            if ($valid !== true) return $valid;

            if ($this->_id > 0) return $this->update();

            return $this->insertAndUpdateDuplicateKey();
        }

        public function add(array $fields)
        {
            $this->_fields[] = $fields;

            return $this;
        }

        public function tolist()
        {
            //$this->getTableFields();
            $str_criterion = ($this->_id > 0) ? "id={$this->_id}" : "";

            return $this->_db->{$this->_table}()->where($str_criterion)->tolist()->data;
        }

        public function toCriterion($criterion)
        {

            if (strlen($criterion) == 0) return null;
            $data = $this->_db->{$this->_table}()->where($criterion)->first()->data;
            if (count($data) == 0) return null;

            $object = $this;

            $rc = $this->_reflectionClass;
            foreach ($rc->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
                $colname = $prop->getName();
                if (array_key_exists($colname, $data)) {
                    if ($colname == "id") $this->_id = $data['id'];
                    $object->{$colname} = (Date::validate($data[$colname])->isDate()) ? Date::format($data[$colname])->usToBr() : $data[$colname];
                }
            }

            return $this;
        }

        public function detail()
        {
            //$this->getTableFields();
            $id = $this->_id;
            if ($id == 0) return array();
            $data = $this->_db->{$this->_table}()->where("id={$id}")->tolist()->data;

            return (count($data) > 0) ? $data[0] : array();
        }

        private function detailObject()
        {

            $id = $this->_id;
            if ($id == 0) return null;
            //$data = $this->_db->{$this->_table}()->where("id={$id}")->tolist()->data;
            $data = $this->getDataTableFields();

            if (count($data) == 0) return null;

            //$table = $this->getTableFullName();
            $object = $this;

//            $columns = $this->_db->{$this->_table}()->columns();
//            foreach ($columns as $cKey => $cField) {
//                $colname = $cField['Field'];
//                foreach ($data as $key => $field) {
//                    $object->{$colname} = (Date::validate($field[$colname])->isDate()) ? Date::format($field[$colname])->usToBr() : $field[$colname];
//                }
//            }

            foreach ($data as $colname => $val) {
                    $object->{$colname} = (Date::validate($val)->isDate()) ? Date::format($val)->usToBr() : $val;
            }

            return $this;

        }

        /**
         * Retorna se foi excluido
         *
         * @param array|null $ids
         *
         * @return ReturnDelete
         */
        public function delete(array $ids = null)
        {
            //$this->getTableFields();
            $str_criterion = "id={$this->_id}";
            if (is_array($ids) && $ids !== null) $str_criterion = "id IN (" . implode(",", array_values($ids)) . ")";

            return $this->_db->{$this->_table}()->where($str_criterion)->delete();
        }

        /**
         * @param $sqlCondition
         *
         * @return ReturnDelete
         */
        public function deleteCondition($sqlCondition)
        {
            if ($sqlCondition == "" || $sqlCondition == null || is_array($sqlCondition)) return false;

            return $this->_db->{$this->_table}()->where($sqlCondition)->delete();
        }

        /**
         * Retorna verdadeiro caso tenha sido truncado
         * @return boolean
         */
        public function truncate()
        {
            return $this->_db->{$this->_table}()->truncate();
        }

        /**
         * Retorna o primeiro registro
         * @return ReturnSelect
         */
        public function first()
        {
            return $this->_db->{$this->_table}()->first();
        }

        /**
         * Retorna o último registro
         * @return ReturnSelect
         */
        public function last()
        {
            return $this->_db->{$this->_table}()->last();

        }

        public function ignoreError()
        {
            $this->_ignoreMessageError = true;

            return $this;
        }

        public function requestData($type = 'post')
        {
            $this->_getRequestData = true;
            $this->_getRequestDataType = $type;
            $this->getTableFields();

            return $this;
        }

        public function dataTable()
        {
            return $this->_db->{$this->_table}();
        }

        private function setCallBack($type, $message, $field = "", $jsonResult = false)
        {
            $error = array(
                'message' => $message,
                'field'   => $field,
                'type'    => $type
            );

            $this->datavalid = json_encode($error);

            return ($jsonResult) ? json_encode($error) : $error;

        }

        public function setError($message, $field = "", $jsonResult = false)
        {
            return $this->setCallBack('error', $message, $field, $jsonResult);

        }

        public function setWarning($message, $field = "", $jsonResult = false)
        {
            return $this->setCallBack('warning', $message, $field, $jsonResult);

        }

        public function ignoreValidation($fields = null)
        {
            $this->_ignoreValidate = true;
            $this->_ignoreValidateFields = $fields;

            return $this;
        }

        //Events
        public function onSave()
        {

        }

    }

    /**
     * Model Interface
     */
    interface iModel
    {
        public function save();

        public function add(array $fields);

        public function tolist();

        public function delete(array $ids = null);

        public function truncate();

        public function first();

        public function detail();

    }