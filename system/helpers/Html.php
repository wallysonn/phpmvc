<?php


    use DateTime\Date;
    use SystemString\StringText;

    class HtmlMobile
    {
        private static function actionLink($link, $text, array $attr = null)
        {
            $str_attr = convertArrayToHtmlAttribute($attr, array('href'));

            return "<a href='{$link}' {$str_attr} >{$text}</a>";
        }

        public static function linkToCall($phone, $text, array $attr = null)
        {
            return self::actionLink("tel:{$phone}", $text, $attr);
        }

        public static function linkToMap($url, $text, array $attr = null)
        {
            return self::actionLink("maps:{$url}", $text, $attr);
        }

        public static function linkToSms($phone, $text, array $attr = null)
        {
            return self::actionLink("sms:{$phone}", $text, $attr);
        }

        public static function linkToYoutube($url, $text, array $attr = null)
        {
            return self::actionLink("youtube:{$url}", $text, $attr);
        }

        public static function linkToMail($url, $text, array $attr = null)
        {
            return self::actionLink("mailTo:{$url}", $text, $attr);
        }

        public static function linkToWhatsApp($phone, $text, array $attr = null)
        {
            return self::actionLink("intent://send/{$phone}#Intent;scheme=smsto;package=com.whatsapp;action=android.intent.action.SENDTO;end", $text, $attr);
        }
    }

    class Html
    {
        public static function comment($comment)
        {
            return "<!-- {$comment} -->\n";
        }

        public static function both()
        {
            return "<div style='clear: both'></div>";
        }

        public static function executeJsFunction(array $functions)
        {
            $js = "$(function(){";
            foreach ($functions as $f => $fn) {
                $js .= "$fn();\n";
            }
            $js .= "});";

            $of = new OfuscatorJs($js, 'Normal', false, false);

            return "<script>" . $of->pack() . "</script>";
        }

        public static function div_start($id, $class = "", array $attributes = null)
        {

            $cls = ($class !== "") ? " class='{$class}'" : "";
            $attr = "";
            if ($attributes !== null) {
                foreach ($attributes as $param => $value) {
                    if ($param !== "id" && $param !== "class") {
                        $attr .= " {$param} = '$value'";
                    }
                }
            }

            return "<div id='{$id}'{$cls}{$attr} >";
        }

        public static function div_end()
        {
            return "</div>";
        }

        public static function checkbox($text, $name, $value, $inline = true, array $attributes = null, $checked = false)
        {
            $attr = "";

            if ($attributes !== null) {
                foreach ($attributes as $param => $v) {
                    if ($param !== "name" && $param !== "id") {
                        $attr .= " {$param}='{$v}'";
                    }
                }
            }

            $br = ($inline == false) ? "<br />" : "";
            $ck = ($checked) ? " checked = 'checked' " : "";

            return "<label><input type='checkbox'{$ck}value='{$value}' name='{$name}' id='{$name}'{$attr}>{$text}</label>{$br}";
        }

        public static function radio($text, $name, $value, $inline = true, array $attributes = null, $checked = false)
        {
            $attr = "";

            if ($attributes !== null) {
                foreach ($attributes as $param => $v) {
                    if ($param !== "name" && $param !== "id") {
                        $attr .= " {$param}='{$v}'";
                    }
                }
            }

            $br = ($inline == false) ? "<br />" : "";
            $ck = ($checked) ? " checked = 'checked' " : "";

            return "<label><input type='radio'{$ck}value='{$value}' name='{$name}' id='{$name}'{$attr}>{$text}</label>{$br}";
        }

        public static function label($text = "", $name = "", array $attributes = null)
        {
            $attr = convertArrayToHtmlAttribute($attributes, array('name', 'id'));

            return "<label for='{$name}' id='lbl_{$name}'{$attr}>{$text}</label>";
        }

        public static function hidden($name, $value = "")
        {
            return "<input type='hidden' name='{$name}' id='{$name}' value='{$value}' /> ";
        }

        private static function base_button($type, $name, $text, array $attributes = null)
        {
            $attr = ($attributes == null || !is_array($attributes)) ? array() : $attributes;
            $str_attr = convertArrayToHtmlAttribute($attr, array('name', 'id'));

            return "<button type='{$type}' name='{$name}' id='{$name}' {$str_attr}>{$text}</button>";

        }

        public static function button($name, $text, array $attributes = null)
        {
            return self::base_button('button', $name, $text, $attributes);
        }

        public static function submit($name, $text, array $attributes = null)
        {
            return self::base_button('submit', $name, $text, $attributes);
        }

        public static function reset($name, $text, array $attributes = null)
        {
            return self::base_button('reset', $name, $text, $attributes);
        }

        public static function textarea($name, $default = "", array $attributes = null)
        {
            $attr = convertArrayToHtmlAttribute($attributes, array('name', 'id'));

            return "<textarea name='{$name}' id='{$name}'{$attr}>{$default}</textarea>";
        }

        public static function TextAreaFor($model, $property, array $attributes = null)
        {
            $attr = convertArrayToHtmlAttribute($attributes, array('name', 'id'));

            $prop = self::getProperty($model, $property);

            return "<textarea name='{$prop['name']}' id='{$prop['name']}'{$attr}>{$prop['value']}</textarea>";
        }

        public static function textbox($name, $default = "", $attributes = array())
        {
            return self::input("text", $name, $default, $attributes);
        }

        public static function password($name, $default = "", $attributes = array())
        {
            return self::input("password", $name, $default, $attributes);
        }

        public static function numberbox($name, $default = "", $attributes = array())
        {
            return self::input("number", $name, $default, $attributes);
        }

        public static function input($type, $name, $default = "", array $attributes = null)
        {
            $attr = convertArrayToHtmlAttribute($attributes, array('name', 'id'));

            return "<input type='{$type}' name='{$name}' id='{$name}' value='{$default}'{$attr} />";
        }

        public static function InputFor($type, $model, $property, array $attributes = null)
        {
            $attr = convertArrayToHtmlAttribute($attributes, array('name', 'id'));
            $prop = self::getProperty($model, $property);

            $isRequired = (bool)$prop['required'];
            $required = "";
            $requiredMessage = "";

            if ($isRequired) {
                $required = " required='required'";
                $msg = $prop['requiredMessage'];
                $requiredMessage = " {$msg}";
            }

            $vl = self::EditorValueFormat($prop['value'], $prop['format']);

            $id = str_replace(array("[","]"),"",$prop['name']);
            return "<input type='{$type}' oninput=\"setCustomValidity('')\"  oninvalid=\"this.setCustomValidity('{$requiredMessage}')\" {$required} name='{$prop['name']}' id='{$id}' value='{$vl}'{$attr} />";
        }


        public static function action($controller, $action = "", array $params = null)
        {
            $gets = "";

            if ($params !== null && is_array($params)) {
                if (count($params) > 0) {
                    foreach ($params as $param => $value) {
                        $gets .= "$param=$value&";
                    }
                    $gets = "?" . substr($gets, 0, -1);
                }
            }
            $isApp = MyApp::CONFIG("useApp");
            $app = ($isApp) ? getCurrentApp() . "/" : "";

            return APP::pathProject() . $app  . ltrim($controller,"/") . (($action == "") ? "" : "/{$action}") . $gets;
        }

        public static function actionLink($text, $controller, $action = "", array $params = null, array $attributes = null)
        {
            $attr = "";

            if ($attributes !== null && is_array($attributes)) {
                if (count($attributes) > 0) {
                    foreach ($attributes as $param => $value) {
                        $attr .= " {$param} = '{$value}'";
                    }
                }
            }
            $link = (substr($controller, 0, 1) == "#" || substr($controller, 0,
                    7) == "http://") ? $controller : self::action($controller, $action, $params);

            sessionStart();
            $_SESSION['gets'] = (is_array($params)) ? $params : array();

            return "<a href='" . $link . "'{$attr}>{$text}</a>";
        }


        public static function dropDownList($name, $default_value = "", $data = array(), array $attributes = null)
        {

            $attr = "";
            if (is_array($attributes)) $attr = convertArrayToHtmlAttribute($attributes, array('value', 'name', 'id'));

            $hData = "";
            if (count($data) > 0) {

                $dataFields = $data;
                $optionaldata = '';
                $opData = array();

                $isArray = is_array($default_value);

                foreach ($dataFields as $text => $v) {

                    if (is_array($v)) {
                        //Contem group
                        if (!isset($v['type'])) {

                            $hData .= "<optgroup label='{$text}'>";
                            foreach ($v as $t => $v2) {
                                if (isset($v2['aditional'])) $optionaldata = convertArrayToHtmlAttribute($v2['aditional']);
                                $value = $v2['value'];

                                if ($isArray) {
                                    $selected = (in_array($value, $default_value)) ? "selected='selected'" : "";
                                } else {
                                    $selected = ($value == $default_value) ? "selected='selected'" : "";
                                }

                                $hData .= "<option {$selected} {$optionaldata} value=\"{$value}\">{$t}</option>";
                            }
                            $hData .= "</optgroup>";

                        } else {

                            $value = $v['value'];

                            if ($isArray) {
                                $selected = (in_array($value, $default_value)) ? "selected='selected'" : "";
                            } else {
                                $selected = ($value == $default_value) ? "selected='selected'" : "";
                            }

                            $optionaldata = convertArrayToHtmlAttribute($v['aditional']);

                            $hData .= "<option {$selected} {$optionaldata} value=\"{$value}\">{$text}</option>";
                        }

                    } else {

                        if (is_array($v)) $optionaldata = convertArrayToHtmlAttribute($v['aditional']);
                        $value = (is_array($v) ? $v['value'] : $v);

                        if ($isArray) {
                            $selected = (in_array($value, $default_value)) ? "selected='selected'" : "";
                        } else {
                            $selected = ($value == $default_value) ? "selected='selected'" : "";
                        }

                        $hData .= "<option {$selected} {$optionaldata} value=\"{$value}\">{$text}</option>";

                    }

                }

            }

            $jsName = str_replace('[]', '', $name);
            //$id = (in_str(array('[',']'),$name,true)) ? $jsName."_".uniqid(rand(),false) : $jsName;
            $html = "<select name='{$name}' id='{$jsName}'{$attr}>{$hData}</select>";

            return $html;
        }


        public static function DropDownListFor($model, $property, $data = array(), array $attributes = null)
        {

            $prop = self::getProperty($model, $property);
            $name = $prop['name'];
            $default_value = $prop['value'];

            $isRequired = (bool)$prop['required'];
            $required = "";
            $requiredMessage = "";

            if ($isRequired) {
                $required = " required='required'";
                $msg = $prop['requiredMessage'];
                $requiredMessage = " {$msg}";
            }


            $isArray = is_array($default_value);
            $attr = "";
            if (is_array($attributes)) $attr = convertArrayToHtmlAttribute($attributes, array('value', 'name', 'id'));

            $hData = "";
            if (count($data) > 0) {

                $optionaldata = '';

                foreach ($data as $text => $v) {
                    //                    if (is_array($v)) {
                    //                        //Contem group
                    //                        $hData .= "<optgroup label='{$text}'>";
                    //                        foreach ($v as $t => $v2) {
                    //                            $selected = ($v2 == $default_value) ? "selected='selected'" : "";
                    //                            $hData .= "<option {$selected} value=\"{$v2}\">{$t}</option>";
                    //                        }
                    //                        $hData .= "</optgroup>";
                    //                    } else {
                    //                        $selected = ($v == $default_value) ? "selected='selected'" : "";
                    //                        $hData .= "<option {$selected} value=\"{$v}\">{$text}</option>";
                    //                    }


                    if (is_array($v)) {
                        //Contem group
                        if (!isset($v['type'])) {

                            $hData .= "<optgroup label='{$text}'>";
                            foreach ($v as $t => $v2) {
                                if (isset($v2['aditional'])) $optionaldata = convertArrayToHtmlAttribute($v2['aditional']);
                                $value = $v2['value'];

                                if ($isArray) {
                                    $selected = (in_array($value, $default_value)) ? "selected='selected'" : "";
                                } else {
                                    $selected = ($value == $default_value) ? "selected='selected'" : "";
                                }

                                $hData .= "<option {$selected} {$optionaldata} value=\"{$value}\">{$t}</option>";
                            }
                            $hData .= "</optgroup>";

                        } else {

                            $value = $v['value'];

                            if ($isArray) {
                                $selected = (in_array($value, $default_value)) ? "selected='selected'" : "";
                            } else {
                                $selected = ($value == $default_value) ? "selected='selected'" : "";
                            }

                            $optionaldata = convertArrayToHtmlAttribute($v['aditional']);

                            $hData .= "<option {$selected} {$optionaldata} value=\"{$value}\">{$text}</option>";
                        }

                    } else {


                        if (is_array($v)) $optionaldata = convertArrayToHtmlAttribute($v['aditional']);
                        $value = (is_array($v) ? $v['value'] : $v);
                        $selected = ($value == $default_value) ? "selected='selected'" : "";
                        $hData .= "<option {$selected} {$optionaldata} value=\"{$value}\">{$text}</option>";

                    }

                }
            }

            $jsName = str_replace('[]', '', $name);
            //$id = (in_str(array('[',']'),$name,true)) ? $jsName."_".uniqid(rand(),false) : $jsName;
            $html = "<select onchange=\"setCustomValidity('')\"  oninvalid=\"this.setCustomValidity('{$requiredMessage}')\" name='{$name}'{$required} id='{$jsName}'{$attr}>{$hData}</select>";

            return $html;
        }


        public static function datalist($name, array $data = null, array $attributes = null)
        {

            $attr = "";
            if (is_array($attributes)) $attr = convertArrayToHtmlAttribute($attributes, array('value', 'name', 'id'));

            $hData = "";
            if (is_array($data)) {
                foreach ($data as $value) {
                    $hData .= "<option value='{$value}'>";
                }
            }

            $html = "<input list='{$name}' name='{$name}'{$attr}>";
            $html .= "<datalist id='{$name}'>{$hData}</datalist>";

            return $html;
        }


        public static function file($name, array $attributes = null)
        {

            $attr = '';
            if (is_array($attributes)) {
                foreach ($attributes as $param => $value) {
                    $attr .= " {$param}='{$value}'";
                }
            }

            return "<input name='{$name}' id='{$name}' type='file'{$attr} />";

        }


        //FOR

        static function getProperty($Class, $Property)
        {
            $rClass = new ReflectionClass($Class);
            $class_name = $rClass->getName();
            $rProperty = new ReflectionProperty($class_name, $Property);
            $comment = $rProperty->getDocComment();
            $type = 'text';
            $format = '';

            $return = function ($name, $display, $required, $requiredMessage, $type, $format) use ($Class, $Property, $class_name) {
                return array(
                    'name'            => $name,
                    'display'         => $display,
                    'required'        => $required,
                    'value'           => $Class->{$Property},
                    'type'            => $type,
                    'requiredMessage' => $requiredMessage,
                    'format'          => $format,
                    'table'           => str_replace("\\", "/", $class_name)
                );
            };

            $requiredMessage = 'Por favor, preencha este campo.';

            if ($comment === false) {
                return $return($Property, ucfirst($Property), false, '', $type, $format);
            }

            $required = false;

            $display = ucfirst($Property);
            $name = $Property;

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

                            switch ($param_name) {
                                case 'displayname':
                                    $display = $value;
                                    break;
                                case 'required':
                                    $required = true;
                                    $requiredMessage = $value;
                                    break;
                                case 'datatype':
                                    $type = $value;
                                    break;
                                case 'format':
                                    $format = $value;
                                    break;
                                case 'name':
                                    $name = $value;
                                    break;
                            }
                        }
                    }
                }
            }

            return $return($name, $display, $required, $requiredMessage, $type, $format);

        }

        public static function TextBoxFor($Model, $Property, array $attr = null)
        {
            try {

                if (!is_array($attr) && $attr !== null) throw  new Exception('[$attr] deve ser um array');
                $prop = self::getProperty($Model, $Property);


                $vl = self::EditorValueFormat($prop['value'], $prop['format']);


                return self::textbox($prop['name'], $vl, $attr);

            } catch (Exception $e) {
                return $e->getMessage();
            }

        }




        public static function LabelFor($Model, $Property, array $attr = null)
        {
            try {

                if (!is_array($attr) && $attr !== null) throw  new Exception('[$attr] deve ser um array');
                $prop = self::getProperty($Model, $Property);

                $text = $prop['display'] . (($prop['required']) ? " <span>*</span>" : "");

                if ($prop['required']) {
                    $attr['class'] = (isset($attr['class'])) ? 'required_label ' . $attr['class'] : 'required_label';
                }

                return self::label($text, $prop['name'], $attr);

            } catch (Exception $e) {
                return $e->getMessage();
            }

        }

        public static function DisplayFor($Model, $Property)
        {
            try {
                $prop = self::getProperty($Model, $Property);

                return $prop['display'];
            } catch (Exception $e) {
                return $e->getMessage();
            }

        }

        private static function EditorValueFormat($value, $format)
        {
            $format = strtolower($format);
            $numeric = array('int', 'integer', 'bigint');
            $decimal = array('double', 'float');

            if (in_array($format, $numeric)) {
                return (int)$value;
            } elseif (in_array($format, $decimal)) {
                return number_format($value, 2, ",", ".");
            } elseif ($format == "numeric") {
                return str_replace(".", ",", $value);
            }elseif ($format == "date"){
                if (Date::validate($value)->isDate()) $value = Date::format($value)->usToBr();
            }

            return $value;
        }

        public static function EditorFor($Model, $Property, array $attr = null)
        {
            try {

                if (!is_array($attr) && $attr !== null) throw  new Exception('[$attr] deve ser um array');
                $prop = self::getProperty($Model, $Property);
                $type = StringText::convert($prop['type'])->toLower();
                $vl = self::EditorValueFormat($prop['value'], $type);

                return self::input($type, $prop['name'], $vl, $attr);

            } catch (Exception $e) {
                return $e->getMessage();
            }

        }

    }

    class IncludeModel
    {

        function __construct()
        {
        }
    }