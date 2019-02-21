<?php

    class Meta
    {
        private $_metas = array();
        private $_charset = "utf8";

        public function keywords(array $content)
        {

            if (is_array($content)) $content = implode(',',array_values($content));

            $this->_metas["keywords"] = $content;

            return $this;
        }

        public function __set($name, $value)
        {
            // TODO: Implement __set() method.
            $this->_metas[$name] = $value;

            return $this;

        }

        public function author($content)
        {
            $this->_metas["author"] = $content;

            return $this;
        }

        public function description($content)
        {
            $this->_metas["description"] = mb_strimwidth(strip_tags($content),0,300,"...");

            return $this;
        }

        public function viewport($content)
        {
            $this->_metas["viewport"] = $content;

            return $this;
        }

        public function charset($content)
        {
            $this->_charset = $content;

            return $this;
        }

        public function show()
        {
            $html = "<meta charset='{$this->_charset}' />\n";
            foreach ($this->_metas as $name => $content) {
                $html .= "<meta name=\"{$name}\" content=\"{$content}\">\n";
            }
            print $html;
        }
    }