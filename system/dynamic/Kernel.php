<?php

    namespace dynamic;

    class Kernel
    {

        public function run()
        {
            $this->makeKernel();
        }

        /**
         * @return array
         * */
        private function getArgv()
        {
            $model = [
                "action"    => "help",
                "param"     => "",
                "command"   => ""
            ];

            $server = $_SERVER;

            $argv = $server['argv'];
            $model['action'] = (isset($argv[1])) ? $argv[1] : 'help';

            if (stristr($model['action'], ":") !== false) {
                $splAction = explode(":", $model['action']);
                $model['action'] = $splAction[0];
                $model['param'] = $splAction[1];
            }

            return $model;

        }

        /**
         * Inicia
         * **/
        private function makeKernel()
        {

            $data = $this->getArgv();
            $action = sprintf(
                "\dynamic\%s", ucfirst(strtolower($data['action']))
            );
            $params = $data['param'];
            $InstanceAction = new $action;
            if (!class_exists($action) || !method_exists($InstanceAction,"run")) {
                $InstanceAction = new \dynamic\Help();
            }

            call_user_func_array(array($InstanceAction, "run"), array($params));
        }

    }