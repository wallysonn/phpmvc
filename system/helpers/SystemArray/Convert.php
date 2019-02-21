<?php
    namespace SystemArray;

    use SystemString\StringText;

    class Convert
    {
        private $array = array();

        function __construct(array $array)
        {
            $this->array = $array;
        }

        function toList($separator = ",", $inner = "")
        {
            $data = $this->array;
            $separator = ($separator == "" || is_null($separator)) ? "," : $separator;
            $list = implode($separator, $data);

            if ($inner !== "" && $inner !== null) {

                $len = count($data);
                $str = "";
                if ($len > 1) {
                    foreach ($data as $k => $elm) {
                        $str .= ($k == $len - 1) ? "{$inner}{$elm}" : "{$separator}{$elm}";
                    }
                    $str = StringText::format($str)->removeRepeatedSpace();
                    $str = ltrim($str, $separator);
                } else {
                    $str = $list;
                }

                return $str;

            } else {

                return $list;
            }

        }

        function toIterator()
        {
            $it = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($this->array));

            return array_unique(iterator_to_array($it, false));
        }

        function toModel($model){
            $arr = $this->array;
            foreach ($arr as $k => $v){
                if (property_exists($model,$k)) $model->$k = $v;
            }
            return $model;
        }

        function toSelectList($fieldValue, $fieldText, $textNoData = null, $noDataValue = "", $fieldGroup = null, array $aditionalData = null)
        {

            $array = $this->array;

            if (count($array) == 0) return array('Nada encontrado' => '');

            $default_text = ($textNoData == null) ? "Escolha..." : $textNoData;
            $arr = ($textNoData == "" && $textNoData !== null) ? array() : array($default_text => $noDataValue);

            if ($textNoData == 'nothing') $arr = array();

            if (strstr($fieldText, "|") <> false) {

                $spl = explode("|", $fieldText);

                foreach ($array as $row => $field) {

                    if (isset($row[0])) {

                        foreach ($field as $k_g => $f_g) {

                            $text = "";

                            foreach ($spl as $k => $f) {
                                $f2 = trim($f);
                                if (isset($f_g[$f2])) {
                                    $text .= (isset($f_g[$f2]) && $f !== " ") ? $f_g[$f2] : $f2;
                                } else {
                                    $text .= (isset($f_g[$f]) && $f !== " ") ? $f_g[$f] : $f;
                                }
                            }

                            if (in_str(array('::'), $text)) {
                                $spl_function = explode("::", $text);
                                $function_name = trim($spl_function[0]);
                                $param = trim($spl_function[1]);
                                $text = call_user_func($function_name, $param);
                            }

                            if (in_str(array('::'), $fieldValue)) {
                                $spl_function = explode("::", $fieldValue);
                                $function_name = trim($spl_function[0]);
                                $param = trim($spl_function[1]);

                                $result = call_user_func($function_name, $param);

                                $arr[$row][$text] = $f_g[$result];

                            } else {
                                $arr[$row][$text] = $f_g[$fieldValue];
                            }
                        }
                    } else {

                        if ($fieldGroup !== null && $fieldGroup !== "") {
                            $groupname = "";
                            //verifica se tem |
                            if (strstr($fieldGroup, "|")) {
                                $splgroup = explode("|", $fieldGroup);
                                if (count($splgroup) > 0) {
                                    foreach ($splgroup as $kgroup => $vgroup) {
                                        $v2 = trim($vgroup);
                                        if (isset($field[$v2])) {
                                            $groupname .= (isset($field[$v2]) && $v2 !== " ") ? $field[$v2] : $v2;
                                        } else {
                                            $groupname .= (isset($field[$vgroup]) && $vgroup !== " ") ? $field[$vgroup] : $vgroup;
                                        }
                                    }
                                }
                            }

                            $groupname = ($groupname == "") ? ((isset($field[$fieldGroup])) ? $field[$fieldGroup] : "Grupo") : $groupname;

                            $text = "";

                            foreach ($spl as $k => $f) {
                                $f2 = trim($f);
                                if (isset($field[$f2])) {
                                    $text .= (isset($field[$f2]) && $f !== " ") ? $field[$f2] : $f2;
                                } else {
                                    $text .= (isset($field[$f]) && $f !== " ") ? $field[$f] : $f;
                                }
                            }
                            $arr[$groupname][$text] = $field[$fieldValue];

                        } else {

                            $text = "";

                            foreach ($spl as $k => $f) {
                                $f2 = trim($f);
                                if (isset($field[$f2])) {
                                    $text .= (isset($field[$f2]) && $f !== " ") ? $field[$f2] : $f2;
                                } else {
                                    $text .= (isset($field[$f]) && $f !== " ") ? $field[$f] : $f;
                                }
                            }
                            $arr[$text] = $field[$fieldValue];
                        }

                    }
                }

            } else {

                foreach ($array as $row => $field) {

                    if (isset($row[0])) {
                        foreach ($field as $k => $f) {
                            $arr[$row][$f[$fieldText]] = $f[$fieldValue];
                        }
                    } else {

                        if ($fieldGroup !== null && $fieldGroup !== "") {

                            $groupname = "";

                            if (strstr($fieldGroup, "|")) {
                                $splgroup = explode("|", $fieldGroup);
                                if (count($splgroup) > 0) {
                                    foreach ($splgroup as $kgroup => $vgroup) {
                                        $v2 = trim($vgroup);
                                        if (isset($field[$v2])) {
                                            $groupname .= (isset($field[$v2]) && $v2 !== " ") ? $field[$v2] : $v2;
                                        } else {
                                            $groupname .= (isset($field[$vgroup]) && $vgroup !== " ") ? $field[$vgroup] : $vgroup;
                                        }
                                    }
                                }
                            }

                            $groupname = ($groupname == "") ? ((isset($field[$fieldGroup])) ? $field[$fieldGroup] : "Grupo") : $groupname;

                            if (is_array($aditionalData)) {
                                $myAdd = array();
                                foreach ($aditionalData as $adText) {
                                    if (isset($field[$adText])) {
                                        $myAdd["column-{$adText}"] = $field[$adText];
                                    }
                                }
                                $arr[$groupname][$field[$fieldText]] = array(
                                    'type'      => 'group',
                                    'value'     => $field[$fieldValue],
                                    'aditional' => $myAdd
                                );
                            } else {
                                $arr[$groupname][$field[$fieldText]] = array(
                                    'type'  => 'group',
                                    'value' => $field[$fieldValue]
                                );
                            }
                        } else {

                            if (is_array($aditionalData)) {
                                $myAdd = array();
                                foreach ($aditionalData as $adText) {
                                    if (isset($field[$adText])) {
                                        $myAdd["column-{$adText}"] = $field[$adText];
                                    }
                                }

                                $arr[$field[$fieldText]] = array(
                                    'type'      => 'item',
                                    'value'     => $field[$fieldValue],
                                    'aditional' => $myAdd
                                );
                            } else {
                                $arr[$field[$fieldText]] = $field[$fieldValue];
                            }

                        }
                    }
                }
            }

            return $arr;
        }



    }
