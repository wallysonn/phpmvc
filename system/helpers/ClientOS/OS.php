<?php
    namespace ClientOS;

    use Browser\BrowserInfor;

    class OS
    {
        static function getName(){

            $browser = new BrowserInfor();

            return $browser->getPlatform();
        }
    }