<?php

    namespace Bootstrap;

    use DateFormat;
    use DateTime\Date;
    use Html; //Use Html element default

    class NavBar
    {

        private $_menu        = array();
        private $_currentmenu = 0;
        private $_html        = "";

        function __construct($id, $class = "", $brand = "", $attr = null, $inverse = false)
        {
            $clsinverse = ($inverse) ? " navbar-inverse" : "";
            $attr = (is_array($attr)) ? convertArrayToHtmlAttribute($attr) : "";
            $this->_html = "<nav id='{$id}' class='navbar {$class}{$clsinverse}'{$attr}>";
            $this->_html .= "<div class='container_fluid'>";
            $this->_html .= "<div class='navbar-header'>";
            $this->_html .= "<button type='button' class='navbar-toggle collapsed' data-toggle='collapse' data-target='#{$id}' aria-expanded='false' aria-controls='navbar'>";
            $this->_html .= "<span class='sr-only'>Alternar navegação</span>";
            $this->_html .= "<span class='icon-bar'></span>";
            $this->_html .= "<span class='icon-bar'></span>";
            $this->_html .= "<span class='icon-bar'></span>";
            $this->_html .= "</button>";

            if ($brand !== "" && $brand !== null) {
                $this->_html .= "<a class='navbar-brand' href='" . Html::action("", "") . "'>";
                $this->_html .= $brand;
                $this->_html .= "</a>";
            }

            $this->_html .= "</div>";
        }

        function addMenu($text, $link = "#", $attr = null)
        {
            $this->_currentmenu++;
            $this->_menu[$this->_currentmenu] = array(
                'text'      => $text,
                'link'      => $link,
                'link_attr' => $attr,
                'submenu'   => array()
            );

            return $this;
        }

        function addSeparatorSubItem()
        {
            $indexRandon = md5(rand(1, 9999) . time());
            $this->_menu[$this->_currentmenu]['submenu']['li_separator_' . $indexRandon] = null;

            return $this;
        }

        function addHeaderSubItem($text)
        {
            $indexRandon = md5(rand(1, 9999) . time());
            $this->_menu[$this->_currentmenu]['submenu']['header_' . $indexRandon] = $text;

            return $this;
        }

        function addSubItem($text, $link = null, $linkattr = null, $faIcon = null)
        {
            $this->_menu[$this->_currentmenu]['submenu'][$text] = array(
                'link' => $link,
                'attr' => $linkattr,
                'icon' => $faIcon
            );

            return $this;
        }

        function show()
        {

            $this->_html .= "<div class='collapse navbar-collapse'>";
            $this->_html .= "<ul class='nav navbar-nav'>";

            foreach ($this->_menu as $key => $menu) {
                $is_submenu = (isset($menu['submenu']) && count($menu['submenu']) > 0) ? true : false;
                $this->_html .= "<li" . (($is_submenu) ? " class='dropdown'" : "") . "><a" . (($is_submenu) ? " class='dropdown-toggle' data-toggle='dropdown' role='button' aria-haspopup='true' aria-expanded='false' href='javascript:void(0);' " : " href='" . $menu['link'] . "'" . convertArrayToHtmlAttribute($menu['link_attr'])) . ">" . $menu['text'] . "</a>";
                if ($is_submenu) {
                    if (is_array($menu['submenu'])) {
                        $this->_html .= "<ul class='dropdown-menu'>";
                        foreach ($menu['submenu'] as $text => $option) {
                            if (substr($text, 0, 12) == 'li_separator') {
                                $this->_html .= "<li role='separator' class='divider'></li>";
                            } else {
                                if (substr($text, 0, 7) == 'header_') {
                                    $this->_html .= "<li class='dropdown-header'>{$option}</li>";
                                } else {
                                    $icon = ($option['icon'] !== null && $option['icon'] !== "") ? "<i class='fa {$option['icon']}'></i> " : "";
                                    $this->_html .= "<li><a href='{$option['link']}'" . convertArrayToHtmlAttribute($option['attr']) . ">{$icon}{$text}</a></li>";
                                }
                            }

                        }
                        $this->_html .= "</ul>";
                    }
                }
                $this->_html .= "</li>";
            }

            $this->_html .= "</ul></div></nav>";
            $this->_menu = null;

            return $this->_html;
        }


    }

    class Panel
    {
        /*Public*/
        public $panelStyle          = BootStyle::bs_Default;
        public $removePadding       = false;
        public $maxHeight           = null; //px
        public $removePaddingBottom = false;

        /*Protexted*/
        protected $_title   = "";
        protected $_content = array();
        protected $_footer  = "";
        protected $_id      = "";
        protected $_attr    = null;


        /*Private*/
        private $_class = "panel";

        function __construct($id = "", array $attributes = null)
        {
            $this->_id = $id;
            $this->_attr = $attributes;
        }

        function addTitle($title)
        {
            $this->_title = $title;

            return $this;
        }

        function addContent($content)
        {
            $this->_content[] = $content;

            return $this;
        }

        function addFooter($content)
        {
            $this->_footer = $content;

            return $this;
        }

        function getPanel()
        {
            $style = str_replace('panel-', '', $this->panelStyle);
            $attr = $this->_attr;
            $attr['class'] = (isset($attr['class'])) ? $attr['class'] . " {$this->_class} {$this->panelType}" : "{$this->_class} panel-{$style}";
            $str_attr = convertArrayToHtmlAttribute($attr);
            $content = implode("\n", array_values($this->_content));
            $title = ($this->_title !== "" && $this->_title !== null) ? "<div class='panel-heading'>{$this->_title}</div>" : "";

            $style = ($this->removePadding) ? "padding:0;" : "";
            $style .= ($this->maxHeight > 0) ? "max-height: {$this->maxHeight}px; overflow:auto;" : "";
            $style .= ($this->removePaddingBottom) ? "padding-bottom:0;" : "";

            $body_style = ($style !== "") ? "style='{$style}' " : "";

            $html = "<div id='{$this->_id}' {$str_attr}>";
            $html .= $title;
            $html .= "<div {$body_style}class='panel-body'>{$content}</div>";
            $html .= ($this->_footer !== "" && $this->_footer !== null) ? "<div class='panel-footer'>{$this->_footer}</div>" : "";
            $html .= "</div>";

            return $html;

        }

    }

    class Row
    {
        /*Public*/
        public $withContainer = false;

        /*Protected*/
        protected $_id      = "";
        protected $_attr    = null;
        protected $_content = array();

        /*Private*/
        private $_class = "row";

        function __construct($id = "", array $attributes = null)
        {
            $this->_id = ($id == "" || $id == null) ? md5(rand(0, 255) . microtime(true)) : $id;
            $this->_attr = $attributes;

            return $this;
        }

        function addContent($content)
        {
            $this->_content[] = $content;

            return $this;
        }

        static function add($content, $attr = null)
        {

            $html = "";
            $strattr = convertArrayToHtmlAttribute($attr,array('class'));

            if (is_array($content)) {
                $html .= "<div {$strattr} class='row'>";
                foreach ($content as $row) {
                    $html .= $row;
                }
                $html .= "</div>";
            } else {
                $html .= "<div {$strattr} class='row'>{$content}</div>";
            }

            return $html;
        }

        function getRow()
        {
            $attr = $this->_attr;
            $attr['class'] = (isset($attr['class'])) ? $attr['class'] . " {$this->_class}" : "{$this->_class}";
            $str_attr = convertArrayToHtmlAttribute($attr);
            $content = implode("\n", array_values($this->_content));

            $html = ($this->withContainer) ? "<div class='container-fluid'>" : "";
            $html .= "<div id='{$this->_id}' {$str_attr}>{$content}</div>";
            $html .= ($this->withContainer) ? "</div>" : "";

            return $html;
        }

    }

    class Col
    {

        /*Protected*/
        protected $_id      = "";
        protected $_attr    = null;
        protected $_content = array();
        protected $_width   = 12;

        /*Private*/
        private $_class = " col-md-";

        function __construct($id = "", $width = 12, array $attributes = null)
        {
            $this->_id = ($id == "" || $id == null) ? md5(rand(0, 255) . microtime(true)) : $id;
            $this->_width = ($width > 12) ? 12 : $width;
            $this->_attr = $attributes;
        }

        function addContent($content)
        {
            $this->_content[] = $content;

            return $this;
        }

        function getCol()
        {
            $attr = $this->_attr;
            $attr['class'] = (isset($attr['class'])) ? $attr['class'] . " {$this->_class}{$this->_width}" : "{$this->_class}{$this->_width}";
            $str_attr = convertArrayToHtmlAttribute($attr);
            $content = implode("\n", array_values($this->_content));


            $html = "<div id='{$this->_id}' {$str_attr}>{$content}</div>";

            return $html;
        }

        static function addCol($content, $width)
        {
            if ($width > 12) $width = 12;

            return "<div class=' col-xs-{$width}'>{$content}</div>";
        }


    }

    class Form
    {

        /**
         * @param            $type
         * @param            $name
         * @param string     $label
         * @param string     $default
         * @param null       $col
         * @param string     $placeholder
         * @param array|null $attributes
         * @param string     $ADDON_LEFT
         * @param string     $addon_right
         *
         * @return string
         */
        static private function input($type, $name, $label = "", $default = "", $col = null, $placeholder = "",
            array $attributes = null, $addon_left = "", $addon_right = "", $button = "")
        {

            if (Date::validate($default)->isDate()) $default = (!Date::validate($default)->isDateBr()) ? Date::format($default)->usToBr() : $default;

            $html = "<div class='form-group'>";
            $html .= ($label !== "") ? \Html::label($label, "lbl_{$name}", array('for' => $name)) : "";

            if ($addon_left !== "" || $addon_right !== "") {
                $html .= "<div class='input-group date'>";
                $html .= ($addon_left !== "" && $addon_left !== null) ? "<div class='input-group-addon'>{$addon_left}</div>" : "";
            }

            if ($button !== "" && $button !== null) $html .= "<div class='input-group'>";

            $attr = ($attributes == null || !is_array($attributes)) ? array() : $attributes;
            $attr['class'] = (isset($attr['class'])) ? $attr['class'] . ' form-control ucase' : 'form-control ucase';

            $attr['placeHolder'] = $placeholder;

            $html .= \Html::input($type, $name, $default, $attr);
            if ($button !== "" && $button !== null) {
                $html .= "<span class='input-group-btn'>{$button}</span>";
            }

            if ($addon_left !== "" || $addon_right !== "") {
                $html .= ($addon_right !== "" && $addon_right !== null) ? "<div class='input-group-addon'>{$addon_right}</div>" : "";
                $html .= "</div>";
            }
            if ($button !== "" && $button !== null) $html .= "</div>";
            $html .= "</div>";

            if ($col > 0) {
                $col = new Col("col_{$name}", $col);
                $col->addContent($html);

                $html = $col->getCol();
            }

            return $html;
        }


        static private function InputFor($type, $model, $property, $withLabel = true, $col = null, $placeholder = "",
            array $attributes = null, $addon_left = "", $addon_right = "", $button = "")
        {

            //if (DateFormat::isDate($default)) $default = (!DateFormat::isDateBr($default)) ? DateFormat::convertUsToBr($default) : $default;


            $html = "<div class='form-group'>";
            $html .= ($withLabel) ? \Html::LabelFor($model, $property) : "";

            if ($addon_left !== "" || $addon_right !== "") {
                $html .= "<div class='input-group date'>";
                $html .= ($addon_left !== "" && $addon_left !== null) ? "<div class='input-group-addon'>{$addon_left}</div>" : "";
            }

            if ($button !== "" && $button !== null) $html .= "<div class='input-group'>";

            $attr = ($attributes == null || !is_array($attributes)) ? array() : $attributes;
            $attr['class'] = (isset($attr['class'])) ? $attr['class'] . ' form-control ucase ' : 'form-control ucase';

            $attr['placeHolder'] = $placeholder;

            $html .= \Html::InputFor($type, $model, $property, $attr);
            if ($button !== "" && $button !== null) {
                $html .= "<span class='input-group-btn'>{$button}</span>";
            }

            if ($addon_left !== "" || $addon_right !== "") {
                $html .= ($addon_right !== "" && $addon_right !== null) ? "<div class='input-group-addon'>{$addon_right}</div>" : "";
                $html .= "</div>";
            }
            if ($button !== "" && $button !== null) $html .= "</div>";
            $html .= "</div>";

            if ($col > 0) {
                $col = new Col("", $col);
                $col->addContent($html);

                $html = $col->getCol();
            }

            return $html;
        }

        /**
         * @param            $type
         * @param            $name
         * @param            $text
         * @param string     $style
         * @param null       $col
         * @param array|null $attributes
         *
         * @return string
         */
        static private function base_button($type, $name, $text, $style = BootStyle::bs_Default, $size = BootSize::bs_Default,
            $block = false, $col = null, array $attributes = null)
        {
            $attr = ($attributes == null || !is_array($attributes)) ? array() : $attributes;

            $style = str_replace("btn-", "", $style);
            $size = str_replace("btn-", "", $size);

            $attr['class'] = (isset($attr['class'])) ? $attr['class'] . ' btn' : 'btn';
            $attr['class'] .= " btn-{$style}";

            if ($size !== "") $attr['class'] .= " btn-{$size}";
            if ($block) $attr['class'] .= " btn-block";

            $str_attr = convertArrayToHtmlAttribute($attr, array('name', 'id'));

            $html = "<button type='{$type}' name='{$name}' id='{$name}' {$str_attr}>{$text}</button>";

            if ($col > 0) {
                $col = new Col("col_{$name}", $col);
                $col->addContent($html);

                $html = $col->getCol();
            }

            return $html;
        }

        /**
         * @param            $name
         * @param            $text
         * @param string     $style
         * @param string     $size
         * @param bool|false $block
         * @param null       $col
         * @param array|null $attributes
         *
         * @return string
         */
        static function submit($name, $text, $style = BootStyle::bs_Success, $size = BootSize::bs_Default,
            $block = false, $col = null, array $attributes = null)
        {
            return self::base_button('submit', $name, $text, $style, $size, $block, $col, $attributes);
        }

        /**
         * @param            $name
         * @param            $text
         * @param string     $style
         * @param string     $size
         * @param bool|false $block
         * @param null       $col
         * @param array|null $attributes
         *
         * @return string
         */
        static function button($name, $text, $style = BootStyle::bs_Default, $size = BootSize::bs_Default,
            $block = false, $col = null, array $attributes = null)
        {
            return self::base_button('button', $name, $text, $style, $size, $block, $col, $attributes);
        }

        /**
         * @param            $name
         * @param            $text
         * @param string     $style
         * @param string     $size
         * @param bool|false $block
         * @param null       $col
         * @param array|null $attributes
         *
         * @return string
         */
        static function reset($name, $text, $style = BootStyle::bs_Default, $size = BootSize::bs_Default,
            $block = false, $col = null, array $attributes = null)
        {
            return self::base_button('reset', $name, $text, $style, $size, $block, $col, $attributes);
        }

        /**
         * @param            $name
         * @param string     $label
         * @param string     $default
         * @param null       $col
         * @param string     $placeholder
         * @param array|null $attributes
         * @param string     $button
         *
         * @return string
         */
        static function textbox($name, $label = "", $default = "", $col = null, $placeholder = "", array $attributes = null,
            $button = "", $button_left = "")
        {
            return self::input('text', $name, $label, $default, $col, $placeholder, $attributes, $button_left, '', $button);
        }

        static function TextBoxFor($model, $property, $withLabel = true, $col = null, $placeholder = "", array $attributes = null,
            $button = "")
        {
            return self::InputFor('text', $model, $property, $withLabel, $col, $placeholder, $attributes, '', '', $button);
        }

        static function EditorFor($model, $property, $withLabel = true, $col = null, $placeholder = "", array $attributes = null,
            $button = "")
        {
            $prop = Html::getProperty($model, $property);

            return self::InputFor($prop['type'], $model, $property, $withLabel, $col, $placeholder, $attributes, '', '', $button);
        }

        static function PasswordFor($model, $property, $withLabel = true, $col = null, $placeholder = "", array $attributes = null,
            $button = "")
        {
            return self::InputFor('password', $model, $property, $withLabel, $col, $placeholder, $attributes, '', '', $button);
        }

        static function password($name, $label = "", $default = "", $col = null, $placeholder = "", array $attributes = null,
            $button = "")
        {
            return self::input('password', $name, $label, $default, $col, $placeholder, $attributes, '', '', $button);
        }


        /**
         * @param            $name
         * @param string     $label
         * @param string     $default
         * @param null       $col
         * @param string     $placeholder
         * @param array|null $attributes
         *
         * @return string
         */
        static function textarea($name, $label = "", $default = "", $col = null, $placeholder = "", $rows = null, array $attributes = null)
        {
            $attr = ($attributes == null || !is_array($attributes)) ? array() : $attributes;
            $attr['class'] = (isset($attr['class'])) ? $attr['class'] . ' form-control' : 'form-control';
            $attr['placeholder'] = $placeholder;
            if ($rows > 0) $attr['rows'] = $rows;

            $html = ($label == "") ? "" : "<div class='form-group'>" . Html::label($label);

            $html .= Html::textarea($name, $default, $attr);
            $html .= ($label == "") ? "" : "</div>";

            if ($col > 0) {
                $col = new Col("col_{$name}", $col);
                $col->addContent($html);

                $html = $col->getCol();
            }

            return $html;
        }

        static function TextAreaFor($model, $property, $withLabel = true, $col = null, $placeholder = "", $rows = null, array $attributes = null)
        {
            $attr = ($attributes == null || !is_array($attributes)) ? array() : $attributes;
            $attr['class'] = (isset($attr['class'])) ? $attr['class'] . ' form-control ucase' : 'form-control ucase';
            $attr['placeholder'] = $placeholder;
            if ($rows > 0) $attr['rows'] = $rows;

            $html = ($withLabel) ? "<div class='form-group'>" . Html::LabelFor($model, $property) : "";
            $html .= Html::TextAreaFor($model, $property, $attr);
            $html .= ($withLabel) ? "</div>" : "";

            if ($col > 0) {
                $col = new Col("", $col);
                $col->addContent($html);

                $html = $col->getCol();
            }

            return $html;
        }


        /**
         * @param            $name
         * @param string     $addon_left
         * @param string     $addon_right
         * @param string     $label
         * @param string     $default
         * @param null       $col
         * @param string     $placeholder
         * @param array|null $attributes
         */
        static function textbox_addon($name, $addon_left = "", $addon_right = "", $label = "", $default = "", $col = null,
            $placeholder = "", array $attributes = null)
        {
            return self::input('text', $name, $label, $default, $col, $placeholder, $attributes, $addon_left, $addon_right);
        }

        /**
         * @param            $name
         * @param string     $label
         * @param string     $default
         * @param null       $col
         * @param array|null $data
         * @param array|null $attributes
         *
         * @return string
         */
        static function dropdownlist($name, $label = "", $default = "", $col = null, array $data = null, array $attributes = null)
        {
            $attr = ($attributes == null || !is_array($attributes)) ? array() : $attributes;

            $attr['class'] = (isset($attr['class'])) ? $attr['class'] . ' form-control ' : 'form-control ';

            $html = ($label == "") ? "" : "<div class='form-group'>" . Html::label($label);
            $html .= Html::dropDownList($name, $default, (($data == null || !is_array($data)) ? array() : $data), $attr);
            $html .= ($label == "") ? "" : "</div>";

            if ($col > 0) {
                $col = new Col("col_{$name}", $col);
                $col->addContent($html);
                $html = $col->getCol();
            }

            return $html;
        }

        static function DropDownListFor($model, $property, $withLabel = true, $col = null, array $data = null, array $attributes = null
        )
        {
            $attr = ($attributes == null || !is_array($attributes)) ? array() : $attributes;

            $attr['class'] = (isset($attr['class'])) ? $attr['class'] . ' form-control ' : 'form-control ';

            $html = ($withLabel) ? "<div class='form-group'>" . Html::LabelFor($model, $property) : "";
            $html .= Html::DropDownListFor($model, $property, (($data == null || !is_array($data)) ? array() : $data), $attr);
            $html .= ($withLabel) ? "</div>" : "";

            if ($col > 0) {
                $col = new Col("", $col);
                $col->addContent($html);
                $html = $col->getCol();
            }

            return $html;

        }

    }

    class Tab
    {
        /**Public**/
        public $dockFill = false;

        /**Private**/
        private $_size     = BootSize::bs_Default;
        private $_elements = array();
        private $_active   = false;

        /**Protected**/
        protected $_id = "";
        protected $_attr;

        /**
         * @param            $id
         * @param array|null $attributes
         */
        function __construct($id, array $attributes = null)
        {
            $attr = ($attributes == null || !is_array($attributes)) ? array() : $attributes;
            $attr['class'] = (isset($attr['class'])) ? $attr['class'] . ' nav nav-tabs nav-tabs-line' : 'nav nav-tabs nav-tabs-line';

            $this->_attr = $attr;
            $this->_id = $id;
        }

        /**
         * @param            $text
         * @param            $content
         * @param bool|false $active
         * @param array|null $attributes
         *
         * @return $this
         */
        function add($text, $content, $active = false, array $attributes = null, $dataPadding = "")
        {
            $attr = ($attributes == null || !is_array($attributes)) ? array() : $attributes;
            $expanded = "false";
            $cslactive = "";
            if ($active && !$this->_active) {
                $attr['class'] = (isset($attr['class'])) ? $attr['class'] . ' active' : 'active';
                $expanded = "true";
                $cslactive = "active in";
                $this->_active = true; //Para não existir dois ativos
            }
            $str_attr = convertArrayToHtmlAttribute($attr);

            $tabid = $this->_id . "_tab_" . (uniqid(rand(), false));

            $attr_title = (strlen(strip_tags($text)) > 13) ? " title='" . strip_tags($text) . "' " : "";

            $this->_elements['li'][] = "<li role='presentation'{$str_attr}><a href='#{$tabid}'{$attr_title}id='{$tabid}-tab' role='tab'
            data-toggle='tab' aria-controls='{$tabid}' aria-expanded='{$expanded}' >{$text}</a></li>";
            $dp = ($dataPadding !== "") ? " padding: {$dataPadding};" : "";
            $style = ($dp !== "") ? "style='{$dp}'" : "";
            $this->_elements['content'][] = "<div {$style} role='tabpanel' class='tab-pane fade {$cslactive}' id='$tabid' aria-labelledby='{$tabid}-tab'>{$content}</div>";

            return $this;
        }

        /**
         * @param string $size
         */
        function size($size = BootSize::bs_Default)
        {
            $this->_size = $size;
        }

        /**
         * @return string
         */
        function getTab()
        {

            $attr = $this->_attr;
            $attr['class'] = ($this->dockFill) ? $attr['class'] . ' tab-fixed' : $attr['class'];
            switch ($this->_size) {
                case BootSize::bs_Default:
                    break;
                case BootSize::bs_Large:
                    $attr['class'] = ($this->dockFill) ? $attr['class'] . ' tab-lg' : $attr['class'];
                    break;
                case BootSize::bs_Mini:
                    $attr['class'] = ($this->dockFill) ? $attr['class'] . ' tab-xs' : $attr['class'];
                    break;
                case BootSize::bs_Small:
                    $attr['class'] = ($this->dockFill) ? $attr['class'] . ' tab-sm' : $attr['class'];
                    break;
            }

            $str_attr = convertArrayToHtmlAttribute($attr, array('id'));
            $lis = implode('', array_values($this->_elements['li']));
            $content = implode('', array_values($this->_elements['content']));


            $html = "<style>
                .nav-tabs > li > a{
                    max-width         : 200px;
                    display           : table-cell;
                    overflow          : hidden;
                    -ms-text-overflow : ellipsis;
                    -o-text-overflow  : ellipsis;
                    text-overflow     : ellipsis;
                    white-space       : nowrap;
                }
                .tab-fixed li:first-child > a  {
                    border-left: none !important;
                }
                .tab-fixed li > a  {
                    border-top: none   !important;
                    border-radius: 0;
                }
                .tab-lg > li > a{
                    padding           : 15px 15px;
                }
                .tab-xs > li > a{
                    padding           : 5px 10px;
                }
                .tab-sm > li > a{
                    padding           : 10px 10px;
                }
            </style>";
            $html .= "<div class='tabbable-custom tabbable tabbable-tabdrop'>";
            $html .= "<ul id='{$this->_id}'{$str_attr} role='tablist'>{$lis}</ul>";
            $html .= "<div id='{$this->_id}_content' class='tab-content'>{$content}</div>";
            $html .= "</div>";


            return $html;

        }

    }

    class Alert
    {
        static function show($id, $content, $style = BootStyle::bs_Default, $title = "", $showButtonClose = false, array $attributes = null)
        {

            $style = str_replace('alert-', '', $style);
            $style = " alert-{$style}";
            $clsButton = "";
            $button = "";

            if ($showButtonClose) {
                $clsButton = " alert-dismissible";
                $button = "<button type='button' class='close' data-dismiss='alert' aria-label='Fechar'><span aria-hidden='true'>&times;</span></button>";
            }

            $attr = ($attributes == null || !is_array($attributes)) ? array() : $attributes;
            $attr['class'] = (isset($attr['class'])) ? $attr['class'] . " alert{$style}{$clsButton} " : "alert{$style}{$clsButton}";
            $attr['role'] = "alert";
            $attr['id'] = $id;

            $str_attr = convertArrayToHtmlAttribute($attr);
            $v_title = ($title !== "" && $title !== null) ? "<strong>{$title}</strong> " : "";

            return "<div style='margin-bottom: 0' {$str_attr}>{$button}{$v_title}{$content}</div>";

        }
    }

    class GroupButtons
    {
        private $_id      = "";
        private $_attr    = array();
        private $_buttons = array();

        function __construct($id, array $attr = null)
        {
            $this->_id = $id;
            $this->_attr = $attr;
        }

        function addButton($btn)
        {
            $this->_buttons[] = $btn;

            return $this;
        }

        function getGroupButtons()
        {
            $btn = implode("\n", $this->_buttons);
            $this->_attr['class'] = (isset($this->_attr['class'])) ? $this->_attr['class'] . " btn-group" : "btn-group";
            $attr = convertArrayToHtmlAttribute($this->_attr, array('id'));

            return "<div role='group' id='{$this->_id}'{$attr}>{$btn}</div>";

        }


    }

    class DropDownButton
    {
        private $itens            = array();
        private $position         = 'auto';
        public  $text             = "[INFORME O TEXTO DO BOTÃO]";
        public  $buttonId         = "";
        public  $caretPosition    = "right"; //right or left
        public  $dropDownPosition = "left";
        public  $style            = BootStyle::bs_Default;
        public  $class            = "";
        public  $size             = BootSize::bs_Default;
        public  $showCaret        = true;


        /**
         * DropDownButton constructor.
         *
         * @param string $position ==> default value is "auto". Other value: down or up
         */
        function __construct($position = 'auto')
        {
            $this->position = trim(str_replace("drop", "", $position));
            $this->buttonId = "button_" . uniqid(rand(), false);

        }

        function addLink($text, $href, $enable = true, array $attr = null)
        {
            $clsLi = (!$enable) ? " class='disabled' " : "";
            $a_attr = (is_array($attr)) ? convertArrayToHtmlAttribute($attr, array('href')) : '';
            $this->itens[] = "<li{$clsLi}><a href='{$href}' {$a_attr}>{$text}</a></li>";

            return $this;

        }

        function addHeader($text)
        {
            $this->itens[] = "<li class='dropdown-header'>{$text}</li>";
            return $this;
        }

        function addSeparator()
        {
            $this->itens[] = "<li role='separator' class='divider'></li>";
            return $this;
        }

        function show()
        {
            $button = $this->getButton();
            $cls = ($this->position == "auto") ? "dropdown" : "drop{$this->position}";
            $html = "<div class='{$cls}'>";
            $html .= $button;
            $html .= "<ul class='dropdown-menu pull-{$this->dropDownPosition}' aria-labelledby='{$this->buttonId}'>";
            $html .= implode("\n", array_values($this->itens));
            $html .= "</ul>";
            $html .= "";
            $html .= "";
            $html .= "</div>";

            return $html;

        }

        ///private
        private function getButton()
        {
            $caret = ($this->showCaret) ? "<span class='caret'></span>" : "";
            $text = ($this->caretPosition == "right") ? "{$this->text} {$caret}" : "{$caret} {$this->text}";
            $size = ($this->size !== BootSize::bs_Default) ? "btn-{$this->size}" : "";
            $html = "<button class='btn btn-{$this->style} dropdown-toggle {$this->class} {$size}' type='button' id='{$this->buttonId}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
            {$text}</button>";

            return $html;
        }

    }

    /*********** |ABSTRACTS - ENUMS | **************/
    /*default style for bootstrap element*/

    abstract class BootStyle
    {
        const bs_Default = 'default';
        const bs_Primary = 'primary';
        const bs_Success = 'success';
        const bs_Info    = 'info';
        const bs_Warning = 'warning';
        const bs_Danger  = 'danger';
        const bs_Link    = 'link';
    }

    /*Default size of bootstrap element*/

    abstract class BootSize
    {
        const bs_Large   = 'lg';
        const bs_Default = '';
        const bs_Small   = 'sm';
        const bs_Mini    = 'xs';
    }

    /*Icons from Fa-icons and glyphicon*/

    abstract class BootIcon
    {
        /**
         * @param $icon
         *
         * @return string
         */
        static function fa($icon, array $attr = null)
        {
            /** @var TYPE_NAME $icon */
            $icon = str_replace('fa-', '', $icon);

            if (is_array($attr)) {
                if (isset($attr['class'])) {
                    $attr['class'] = "fa fa-{$icon} {$attr['class']}";
                }
            } else {
                $attr['class'] = "fa fa-{$icon}";
            }

            $str = convertArrayToHtmlAttribute($attr);

            return "<span {$str}></span>";
        }

        static function wb($icon, array $attr = null)
        {
            /** @var TYPE_NAME $icon */
            $icon = str_replace('wb-', '', $icon);

            if (is_array($attr)) {
                if (isset($attr['class'])) {
                    $attr['class'] = "wb-{$icon} {$attr['class']}";
                }
            } else {
                $attr['class'] = "wb-{$icon}";
            }

            $str = convertArrayToHtmlAttribute($attr);

            return "<span {$str}></span>";
        }

        /**
         * @param $icon
         *
         * @return string
         */
        static function glyphicon($icon, array $attr = null)
        {
            /** @var TYPE_NAME $icon */
            $icon = str_replace('glyphicon-', '', $icon);

            if (is_array($attr)) {
                if (isset($attr['class'])) {
                    $attr['class'] = "glyphicon glyphicon-{$icon} {$attr['class']}";
                }
            } else {
                $attr['class'] = "glyphicon glyphicon-{$icon}";
            }

            $str = convertArrayToHtmlAttribute($attr);

            return "<span {$str}></span>";

        }

    }