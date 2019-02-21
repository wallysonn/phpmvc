<?php

    namespace DataAnnotations;

    use DateTime\Date;
    use NumberFormat\Number;
    use PhoneNumber\Phone;
    use ReflectionClass;
    use ReflectionProperty;
    use SystemString\StringText;

    class Annotation
    {
        private $_class                = '';
        private $_obj                  = null;
        private $_ignoreValidateFields = null;
        private $_isEditing            = false;

        protected function getFormValidation($class)
        {

            $this->_class = $class;

            $reflectionClass = new ReflectionClass($class);
            $classProperty = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

            foreach ($classProperty as $prop) {
                $propComment = $prop->getDocComment();
                $propName = $prop->getName();

                if ($propComment !== false) {

                    $field[$propName] = array();

                    $data = $this->dataComment($propComment);
                    foreach ($data as $field => $arrParam) {

                    }
                }
            }
        }

        protected function validate($class, $fieldsValues, $obj, $ignoreFields = null, $isEditing = false)
        {
            $this->_isEditing = $isEditing;
            $this->_class = $class;
            $this->_obj = $obj;
            $this->_ignoreValidateFields = (is_array($ignoreFields)) ? array_map('strtolower', $ignoreFields) : null;


            $reflectionClass = new ReflectionClass($class);
            $classProperty = $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);

            foreach ($classProperty as $prop) {
                $propComment = $prop->getDocComment();
                $propName = $prop->getName();

                if ($propComment !== false) {

                    if ($isEditing && stripos($propComment, "@ignoreonupdate") !== false) continue;

                    if (is_array($this->_ignoreValidateFields)) {
                        $pn = strtolower($propName);
                        if (in_array($pn, $this->_ignoreValidateFields)) continue;
                    }

                    if (array_key_exists($propName, $fieldsValues)) {
                        $ret = $this->getValidationFunctions($propComment, $propName, $fieldsValues[$propName]);


                        if ($ret !== true) return json_encode($ret);
                    }

                }
            }

            return true;
        }

        private function dataComment($comment)
        {

            $arr = array();

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
                            $param_name = $matches[1];

                            $value = str_ireplace("@$param_name", '', $info);
                            $value = trim($value);

                            $arr[$param_name] = $this->getArgsOfString($value);

                        }
                    }

                }
            }

            return $arr;

        }

        private function getValidationFunctions($comment, $fieldName, $formValue)
        {


            foreach ($this->dataComment($comment) as $param_name => $args) {
                $data_args = array($fieldName, $formValue);


                foreach ($args as $arg) {
                    array_push($data_args, $arg);
                }


                $return = $this->execute($param_name, $data_args);
                if ($return !== true) return $return;
            };

            return true;

        }

        private function getArgsOfString($string)
        {
            $string = ltrim($string, "(");
            $string = rtrim($string, ")");
            $result = array();
            preg_match_all("~'[^']++'|\([^)]++\)|[^,]++~", $string, $result);

            $ret = $result[0];
            $ret = array_map(array($this, 'formatString'), $ret);

            return $ret;

        }

        private function getResult($valid, $field, $message)
        {
            if ($valid) return $valid;

            return array('message' => $message, 'field' => $field, 'type' => 'warning');
        }

        private function formatString($str)
        {
            $t = trim($str);
            $t = ltrim($t, "'");
            $t = rtrim($t, "'");

            $t = ltrim($t, '"');
            $t = rtrim($t, '"');

            return $t;

        }

        private function execute($functionName, array $params = array())
        {

            if (method_exists($this, $functionName)) return call_user_func_array(array($this, $functionName), $params);

            return true; //if method not exists, return true
        }

        private function formatOutput($field, $value, $message, $aditionalParam = null)
        {
            $prop = \Html::getProperty($this->_obj, $field);

            $fieldName = (isset($prop['display'])) ? $prop['display'] : $field;
            $fieldName = trim($fieldName);
            $fieldName = rtrim($fieldName, ":");

            $params = array('{{field}}' => $fieldName, '{{value}}' => $value);

            if (is_array($aditionalParam)) $params = array_merge($params, $aditionalParam);

            return systemDirectMail($message, $params);

        }

        /** VALIDATION FUNCTIONS *
         *
         * @param        $field
         * @param        $value
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function Required($field, $value, $errorMessage = _LANG_FIELD_IS_REQUIRED_)
        {
            $errorMessage = $this->formatOutput($field, $value, $errorMessage);

            return (isEmpty($value)) ? $this->getResult(false, $field, $errorMessage) : true;
        }

        /**
         * Valida o comprimento da string.
         * *************************************************
         * SINTAXE:
         * @StringLength minValue, maxValue, errorMessage
         * *************************************************
         * Exemplo:
         * @StringLength 5,15, Deve ter de 5 a 15 caracteres
         *
         * OBS: O maxValue pode ser null ou '' caso não queira definir o valor máximo
         *
         * @param        $field
         * @param        $value
         * @param        $min
         * @param        $max
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function StringLength($field, $value, $min, $max, $errorMessage = _LANG_FIELD_STRING_LENGTH_)
        {
            $min = (int)$min;

            $errorMessage = $this->formatOutput($field, $value, $errorMessage, array(
                '{{min}}' => $min,
                '{{max}}' => $max
            ));
            if (($max > 0) && ($max < $min)) return $this->getResult(false, $field, _LANG_MAXSMALLERMIN_);

            $lng = strlen($value);

            if ($lng == 0) return true;

            $crit = ($max == null || $max == "") ? ($lng >= $min) : ($lng >= $min && $lng <= $max);

            return ($crit) ? true : $this->getResult(false, $field, $errorMessage);
        }

        private function Email($field, $value, $errorMessage = _LANG_INCORRECT_MAIL_)
        {
            $errorMessage = $this->formatOutput($field, $value, $errorMessage);
            if (strlen($value) == 0) return true; //Pode ficar em branco

            return (!filter_var($value, FILTER_VALIDATE_EMAIL)) ? $this->getResult(false, $field, $errorMessage) : true;
        }

        private function Url($field, $value, $errorMessage = _LANG_INCORRECT_URL_)
        {
            $errorMessage = $this->formatOutput($field, $value, $errorMessage);

            return (!filter_var($value, FILTER_VALIDATE_URL)) ? $this->getResult(false, $field, $errorMessage) : true;
        }

        private function Cpf($field, $value, $errorMessage = _LANG_INCORRECT_CPF_)
        {

            $errorMessage = $this->formatOutput($field, $value, $errorMessage);
            $value = Number::get($value)->onlyNumbers();
            $len = strlen($value);

            if ($len == 0) return true;

            $cpf = $value;
            $valid = false;

            if ($len == 11) {

                $cpf = str_pad(preg_replace('/[^0-9]/', '', $cpf), 11, '0', STR_PAD_LEFT);
                if ($cpf == '00000000000' || $cpf == '11111111111' || $cpf == '22222222222' || $cpf == '33333333333' || $cpf == '44444444444' || $cpf == '55555555555' || $cpf == '66666666666' || $cpf == '77777777777' || $cpf == '88888888888' || $cpf == '99999999999') {

                } else { // Calcula os números para verificar se o CPF é verdadeiro
                    $valid = true;
                    for ($t = 9; $t < 11; $t++) {
                        for ($d = 0, $c = 0; $c < $t; $c++) {
                            $d += $cpf{$c} * (($t + 1) - $c);
                        }
                        $d = ((10 * $d) % 11) % 10;
                        if ($cpf{$c} != $d) {
                            $valid = false;
                            break;
                        }
                    }
                }
            }

            return (!$valid) ? $this->getResult(false, $field, $errorMessage) : true;

        }

        private function __multiplyCnpj($cnpj, $posicao = 5)
        {

            // Variável para o cálculo
            $calculo = 0;

            // Laço para percorrer os item do cnpj
            for ($i = 0; $i < strlen($cnpj); $i++) {
                // Cálculo mais posição do CNPJ * a posição
                $calculo = $calculo + ($cnpj[$i] * $posicao);

                // Decrementa a posição a cada volta do laço
                $posicao--;

                // Se a posição for menor que 2, ela se torna 9
                if ($posicao < 2) {
                    $posicao = 9;
                }
            }

            // Retorna o cálculo
            return $calculo;

        }

        private function Cnpj($field, $value, $errorMessage = _LANG_INCORRECT_CNPJ_)
        {
            $errorMessage = $this->formatOutput($field, $value, $errorMessage);
            $value = Number::get($value)->onlyNumbers();
            $len = strlen($value);
            if ($len == 0) return true;

            $isCnpjValid = false;

            if ($len == 14) {
                $cnpj = (string)$value;
                $cnpj_original = $value;

                $primeiros_numeros_cnpj = substr($cnpj, 0, 12);
                $primeiro_calculo = $this->__multiplyCnpj($primeiros_numeros_cnpj);
                $primeiro_digito = ($primeiro_calculo % 11) < 2 ? 0 : 11 - ($primeiro_calculo % 11);

                $primeiros_numeros_cnpj .= $primeiro_digito;

                $segundo_calculo = $this->__multiplyCnpj($primeiros_numeros_cnpj, 6);
                $segundo_digito = ($segundo_calculo % 11) < 2 ? 0 : 11 - ($segundo_calculo % 11);

                $cnpj = $primeiros_numeros_cnpj . $segundo_digito;

                $isCnpjValid = $cnpj === $cnpj_original;

            }

            return (!$isCnpjValid) ? $this->getResult(false, $field, $errorMessage) : true;

        }

        private function CpfOrCpnf($field, $value)
        {

            $value = Number::get($value)->onlyNumbers();
            $len = strlen($value);

            if ($len == 0) return true;
            if ($len == 11) return $this->Cpf($field, $value);
            if ($len == 14) return $this->Cnpj($field, $value);

            return $this->getResult(false, $field, _LANG_INCORRECT_CNPJORCPF_);

        }

        private function CnpjOrCpf($field, $value)
        {
            return $this->CpfOrCpnf($field, $value);
        }

        //################ Validation for numbers ###########################

        /**
         * Verifica se o número está entre dois números
         *
         * -f => remove o menor valor inicial da comparação (f == first)
         * -l => remove o maior final da comparação (l == last)
         * -fl => remove o menor e o maior valor
         *
         * @param        $field
         * @param        $value
         * @param        $min
         * @param        $max
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function Range($field, $value, $min, $max, $errorMessage = _LANG_FIELD_RANGE_, $ignore = '')
        {

            $errorMessage = $this->formatOutput($field, $value, $errorMessage, array(
                '{{min}}' => $min,
                '{{max}}' => $max
            ));

            if (!is_numeric($value)) return $this->getResult(false, $field, $errorMessage);
            if (!is_numeric($min)) return $this->getResult(false, $field, $errorMessage);
            if (!is_numeric($max)) return $this->getResult(false, $field, $errorMessage);

            $validate = ($value >= $min && $value <= $max);

            if ($ignore == '-f') $validate = ($value > $min && $value <= $max);
            if ($ignore == '-l') $validate = ($value >= $min && $value < $max);
            if ($ignore == '-fl' || $ignore == '-lf') $validate = ($value > $min && $value < $max);

            return ($validate) ? true : $this->getResult(false, $field, $errorMessage);
        }

        /**
         * Verifica se é numerico
         *
         * @param        $field
         * @param        $value
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function Numeric($field, $value, $errorMessage = _LANG_FIELD_NUMERIC_)
        {
            $errorMessage = $this->formatOutput($field, $value, $errorMessage);

            return (!is_numeric($value)) ? $this->getResult(false, $field, $errorMessage) : true;
        }

        //################## Validation for DateTime ########################

        /**
         * Verifica se uma data é válida. A data pode ser no formato brasileiro ou americano com ou sem horário.
         * Exemplo:
         * @Date Data inválida
         *
         * @param        $field
         * @param        $value
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function Date($field, $value, $errorMessage = _LANG_FIELD_DATE_)
        {
            $errorMessage = $this->formatOutput($field, $value, $errorMessage);

            if ($value == "" || $value == null) return true;
            $date = Date::format($value)->brToUs();

            return (!Date::validate($date)->isDate()) ? $this->getResult(false, $field, $errorMessage) : true;
        }

        /**
         * Verifica se uma data é válida com base numa data mínima.
         * Exemplo:
         * @MinDate 10/05/2016 ou @MinDate 2016-05-10 (O campo deve ser uma data maior que a informada)
         * @MinDate now (Deve ser uma data maior que a data atual)
         * @MinDate now[-2 day] (Deve ser uma data maior que a data atual subtraindo dois dias)
         * Obs:
         * Incluia os seguintes valores para mapa direta
         * {{field}} ==> retorna o nome do campo
         * {{value}} ==> o valor informado
         * {{mindate}} ==> a data mínima aceita
         *
         * @param        $field
         * @param        $value
         * @param        $minDate
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function MinDate($field, $value, $minDate, $errorMessage = _LANG_FIELD_MINDATE_)
        {

            //$mDate = $this->stringToDate($minDate);

            $mDate = Date::get($minDate)->getDate();

            if ($value == "" || $value == null || is_null($mDate)) return true;

            $errorMessage = $this->formatOutput($field, $value, $errorMessage, array("{{mindate}}" => Date::format($mDate)->usToBr()));
            if (!Date::validate($value)->isDate()) return $this->getResult(false, $field, $errorMessage);

            $date = Date::format($value)->brToUs();

            return ($date < $mDate) ? $this->getResult(false, $field, $errorMessage) : true;
        }

        /**
         * Verifica se uma data é válida com base numa data máxima.
         * Exemplo:
         * @MaxDate 10/05/2016 ou @MaxDate 2016-05-10 (O campo deve ser uma data maior que a informada)
         * @MaxDate now (Deve ser uma data maior que a data atual)
         * @MaxDate now[-2 day] (Deve ser uma data maior que a data atual subtraindo dois dias)
         * Obs:
         * Incluia os seguintes valores para mapa direta
         * {{field}} ==> retorna o nome do campo
         * {{value}} ==> o valor informado
         * {{mindate}} ==> a data mínima aceita
         *
         * @param        $field
         * @param        $value
         * @param        $minDate
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function MaxDate($field, $value, $maxDate, $errorMessage = _LANG_FIELD_MAXDATE_)
        {

            $mDate = Date::get($maxDate)->getDate();

            if ($value == "" || $value == null || is_null($mDate)) return true;

            $errorMessage = $this->formatOutput($field, $value, $errorMessage, array("{{maxdate}}" => Date::format($mDate)->usToBr()));
            if (!Date::validate($value)->isDate()) return $this->getResult(false, $field, $errorMessage);

            $date = Date::format($value)->brToUs();

            return ($date > $mDate) ? $this->getResult(false, $field, $errorMessage) : true;
        }

        /**
         * Verifica se uma data está entre duas datas
         * Exemplo:
         * @RangeDate 01/01/2010,10/10/2011, data não está no período
         * @Rangedate now[-2 day],now[+1 month], data nao está no período {{mindate}} a {{maxdate}}
         * OBS: A data inicial e final serão incluidas por padrão. Para ignorar as datas inicial e final use
         * este comando após a mensagem de erro:
         *
         * -f => remove a data inicial da comparação (f == first)
         * -l => remove a data final da comparação (l == last)
         * -fl => remove a data inicial e a final da comparação
         *
         * @param        $field
         * @param        $value
         * @param        $minDate
         * @param        $maxDate
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function RangeDate($field, $value, $minDate, $maxDate, $errorMessage = _LANG_FIELD_RANGEDATE_, $ignore = '')
        {
            $minDate = Date::get($minDate)->getDate();
            $maxDate = Date::get($maxDate)->getDate();

            if ($value == "" || $value == null || is_null($minDate) || is_null($maxDate)) return true;

            $errorMessage = $this->formatOutput($field, $value, $errorMessage, array(
                "{{mindate}}" => Date::format($minDate)->usToBr(),
                "{{maxdate}}" => Date::format($maxDate)->usToBr()
            ));

            if (!Date::validate($value)->isDate()) return $this->getResult(false, $field, $errorMessage);

            return (!Date::find($value)->isBetween($minDate, $maxDate, $ignore)) ? $this->getResult(false, $field, $errorMessage) : true;
        }

        //################## Validation for PhoneNumber ########################

        /**
         * Verifica se é um telefone válido. Para ser válido, deve ser um fixo, celular ou telefone nacional
         * [TELEFONES DO BRASIL]
         *
         * @param        $field
         * @param        $value
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function Phone($field, $value, $errorMessage = _LANG_FIELD_PHONE_)
        {

            $errorMessage = $this->formatOutput($field, $value, $errorMessage);
            if (empty($value) || $value == null) return true;
            $valid = Phone::validate($value)->isValid();

            return (!$valid) ? $this->getResult(false, $field, $errorMessage) : true;
        }

        /**
         * Varifica se o valor é um número de celular válido
         * [CELULAR DO BRASIL]
         *
         * @param        $field
         * @param        $value
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function PhoneCell($field, $value, $errorMessage = _LANG_FIELD_PHONECELL_)
        {
            $errorMessage = $this->formatOutput($field, $value, $errorMessage);

            if (empty($value) || $value == null) return true;

            return (!Phone::validate($value)->isCell()) ? $this->getResult(false, $field, $errorMessage) : true;
        }

        /**
         * Verifica se é um telefone fixo válido.
         * [TELEFONES DO BRASIL]
         *
         * @param        $field
         * @param        $value
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function PhoneResidential($field, $value, $errorMessage = _LANG_FIELD_PHONERESIDENTIAL_)
        {
            $errorMessage = $this->formatOutput($field, $value, $errorMessage);
            if (empty($value) || $value == null) return true;

            return (!Phone::validate($value)->isResidential()) ? $this->getResult(false, $field, $errorMessage) : true;
        }

        /**
         * Validação Remota.
         * Esta validação executa um método de um controle este devolve um booleano.
         * Exemplo:
         * @Remote pessoas, existe, Esta pessoa ja existe
         *
         * em PessoasController deve existir
         *
         * function existe($value, object $obj){
         *      //$value ==> valor que foi inserido no input
         *      //$obj ==> objeto que foi instanciado. Recebe todos os métodos com os valores preenchidos pelo
         *     formulário
         * }
         *
         * a função "existe" deve retornar um booleano. Neste exemplo se a pessoa ja existe, então tenho que
         * retornar "false", pois não passou na validação
         *
         * @param        $field
         * @param        $value
         * @param        $controller
         * @param        $action
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function Remote($field, $value, $controller, $action, $errorMessage = _LANG_REMOTE_ERROR_)
        {
            $errorMessage = $this->formatOutput($field, $value, $errorMessage);

            if ($value == "" || $value == null) return true;
            $return = true;

            $controller = "\\" . ucfirst($controller);

            //if (!class_exists($controller))  return false;

            $obj = new $controller();
            if (method_exists($obj, $action)) {
                $return = $obj->{$action}($value, $this->_obj);
                if (!is_bool($return)) $return = false;
            }

            return ($return == false) ? $this->getResult(false, $field, $errorMessage) : true;

        }

        //###################### Validation for Regular Expression ########################################

        /**
         * Valida com base numa expressão regular.
         * Exemplo:
         * 1 - O campo só deve aceitar números
         * @RegExp /^[0-9]+$/, Aceita apenas números
         *
         * 2 - Aceita apenas letras minúsculas
         * @RegExp /^[a-z]+$/, Aceita apenas letras minúsculas
         *
         * 3 - Aceita apenas letras maiúsculas
         * @RegExp /^[A-Z]+$/, Aceita apenas letras minúsculas
         *
         * 4 - Aceita apenas letras e números
         * @RegExp /^[A-Za-z0-9]+$/, Aceita apenas letras e números
         *
         *
         * Mais informações sobre as expressões regulares, acesse: http://www.regular-expressions.info/
         *
         * @param        $field
         * @param        $value
         * @param        $regexp
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function RegularExpression($field, $value, $regexp, $errorMessage = _LANG_INCORRECT_REGEXP_)
        {
            $errorMessage = $this->formatOutput($field, $value, $errorMessage);

            $regexp = trim($regexp);
            $regexp = ltrim($regexp, "/");
            $regexp = rtrim($regexp, "/");
            $regexp = "/{$regexp}/";
            $ret = preg_match($regexp, $value);

            return ($ret) ? true : $this->getResult(false, $field, $errorMessage);
        }

        /**
         * O mesmo que o item anterior, pois o nome RegExp é bem aceito.
         *
         * @param        $field
         * @param        $value
         * @param        $regexp
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function RegExp($field, $value, $regexp, $errorMessage = _LANG_INCORRECT_REGEXP_)
        {
            return $this->RegularExpression($field, $value, $regexp, $errorMessage);
        }

        //###################### Others #########################

        /**
         * Compara os valores de dois campos. Ideal para comparar senhas!
         * **************************************
         * SINTAXE:
         * @Compare fieldCompare, ErrorMessage
         * **************************************
         * Exemplo:
         * @Compare newpass, O campo deve ser igual a {{fieldcompare}}
         *
         * @param        $field
         * @param        $value
         * @param        $fieldCompare
         * @param string $errorMessage
         *
         * @return array|bool
         */
        private function Compare($field, $value, $fieldCompare, $errorMessage = _LANG_FIELD_COMPARE_)
        {
            $valueCompare = $this->_obj->{$fieldCompare};
            $errorMessage = $this->formatOutput($field, $value, $errorMessage, array(
                '{{fieldcompare}}' => $fieldCompare,
                '{{valuecompare}}' => $valueCompare
            ));

            return ($valueCompare === $value) ? true : $this->getResult(false, $field, $errorMessage);
        }

    }