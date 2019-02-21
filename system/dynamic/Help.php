<?php

    namespace dynamic;

    class Help extends Console implements iDynamic
    {

        public function run($param=null)
        {
            $data = file_get_contents(__DIR__ . "/dynamic_doc.json");
            if (!empty($data)) {
                $dataArr = json_decode($data, true);
                foreach ($dataArr as $t => $i) {
                    $n = "\t";
                    $msg = $i;
                    $dataExt = [];
                    $color = "cyan";
                    $before = " ";
                    if (is_array($i)) {
                        $n = ":";
                        $msg = "";
                        $dataExt = $i;
                        $color = "yellow";
                        $before = "";
                    }
                    $this->writeLine("{$before}<{$color}>${t}</{$color}>{$n}{$msg}");

                    foreach ($dataExt as $k => $arr) {
                        $this->writeLine(
                            " <cyan>{$arr['command']}</cyan>{$arr['t']}{$arr['description']}"                        );

                    }

                }
            }
        }
    }