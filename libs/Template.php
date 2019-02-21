<?php
    namespace libs;

    class Template
    {

        private $_magicparams = array();


        public function __construct() {

        }

        public function __set($name, $value)
        {
            $this->_magicparams[$name] = $value;
            return $this;
        }

        public function __get($name)
        {
            try {
                //if (!isset($this->_magicparams[$name])) throw  new \Exception(sprintf(Lang::get('_PARAM_NOT_EXISTS'), $name));
                return $this->_magicparams[$name];

            } catch (\Exception $e) {
                //die($e->getMessage());
            }
        }

        public function showContent($content)
        {



            return $content;
        }

    }