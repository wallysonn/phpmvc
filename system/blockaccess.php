<?php

    require_once "app_start/RouterConfig.php";
    $uri = explode("/", (isset($_GET['url']) ? $_GET['url'] : ""));
    $system = APP::getSystem();

    if ($system["block_access_ie"] !== "") {
        if ($uri[0] !== "navegador") {
            //Bloquea acesso para IE
            if (BrowserInfor::name() == "MSIE" && BrowserInfor::version() <= $system["block_access_ie"]) {
                header("Location: " . APP::pathproject() . 'navegador');
                exit();
            }
        }
    }

    $routLogin = Router::getLogin();
    $routLockScreen = Router::getLockSreen();