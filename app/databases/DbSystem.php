<?php

    use data\Context;
    use data\Table;

    class DbSystem extends Context
    {
        public static $db = "zap";

        function Peoples() { return new Table($this); }      


    }