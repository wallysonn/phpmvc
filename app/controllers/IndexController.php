<?php

    use libs\Controller;

    class Index extends Controller
    {
        public function index_action($token="")
        {
            try {

                echo getCurrentUrl();

            } catch (Exception $err) {

            }
        }

    }