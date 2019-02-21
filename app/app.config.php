<?php

    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    define("_STRSEC_", "vexpDaOmDtMqDsFuPqTaQnCnPmTaVeJ316");    
    define("DF_SESSION_CONNECION_NAME", "zapzapconnection");
    define("DF_SESSION_DATA_CONNECION", "zapzapdataconnection");

    //Local
   define("_IPSRV_", "127.0.0.1");
   define("_USERSRV_", "root");
   define("_PASSSRV_", "");
   define("_DB_", "zapzap");
   define("_PREFIX_", "");

    class MyApp
    {
        public static $DATABASE = array(
            'zap'     => array(
                'host'     => _IPSRV_,
                'user'     => _USERSRV_,
                'pass'     => _PASSSRV_,
                'db'       => _DB_,
                'encoding' => 'utf8',
                'prefix'   => _PREFIX_
            )            
        );

        /*
         * Para usar o bloqueio via server deve existir uma tabela
         * em sua base de dados como a mostrada abaixo:
         *
         * CREATE TABLE `rules` (
              `id` int(15) NOT NULL AUTO_INCREMENT,
              `controller_action` varchar(100) DEFAULT '',
              `level` varchar(100) DEFAULT '',
              `active` tinyint(1) DEFAULT '1',
              PRIMARY KEY (`id`),
              UNIQUE KEY `id` (`id`) USING BTREE,
              UNIQUE KEY `controller_action` (`controller_action`) USING BTREE,
              KEY `level` (`level`) USING BTREE,
              KEY `active` (`active`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='Permissão de acesso';
         *
         * */

        public static $AUTHORIZE_SERVER = array(
            'database'     => 'zapzap', // O nome da sua conexão. Este nome está logo no código acima.
            'table'        => 'rules', //O nome da tabela
            'page_field'   => 'controller_action', //na tabela deve existir esta coluna onde ficará salvo o nome da pagina. O nome da pagina é {controller}_{action}. Se página [contato] é chamada do controller [inicio], então a página no banco é inicio_contato. Nunca inclua underline " _ " nos nomes dos controllers.
            'level_field'  => 'level', //na tabela deve existir esta coluna que deve conter os niveis que terao permissao para abrir a pagina (nivel1, nivel2, nivel3...) ==> os niveis devem ficar separados por vírgula na tabela
            'active_field' => 'active', //na tabela deve existir uma coluna tinyint que determina se o bloqueio esta ativo ou não
            'app'          => 'app'
        );

        public static function CONFIG($param = null)
        {
            $data = array(
                "favicon"                         => "/app/files/images/icons/favicon.png?v=2",
                "key"                             => "AIzaSyAn8myxc6fKtZ-7zfobkc3pOv93AMYZzag",
                "name"                            => "ZAPZAP",
                "email"                           => "wgwalisongomes@gmail.com",
                "full_name"                       => "ZAPZAP",
                "author"                          => "Walison Gomes",
                "title"                           => "ZAPZAP",
                "currentYear"                     => "{{date->Y}}",
                "description"                     => "Encurtador de URL",
                //IS REQUIRED
                "version"                         => "1.0",
                "charset"                         => "utf8",
                "path_project"                    => "/",
                "class_exceptionError"            => "<div style='margin-bottom: 0;' class='alert dark alert-alt alert-warning alert-dismissible'><h4 class='break'>%s</h4><p>%s</p></div>",
                "block_browser"                   => array(
                    'internet explorer' => null,
                    'edge'              => 14
                ),
                "authorizeMessage"                => "<div style='margin-bottom: 0;' class='alert dark alert-alt alert-primary alert-dismissible'><h4 class='block'>Permissão Negada!</h4><p>{salutation->}, você não tem permissão para visualizar o conteúdo desta página.</p></div>",
                "masterRole"                      => "master",
                "lang"                            => 'pt-br',
                "maxConnectionTimeout"            => 45,
                'testTheConnectionBeforeRequests' => false,
                'useApp'                          => false,//caso true o prmeiro parametro de url é o APP. Exemplo: site.com/app/controller/action/... caso fase> site.com/controller/action/...
                'connectionMethod'                => 'array' //session or array
            );

            return ($param !== null && isset($data[$param])) ? $data[$param] : $data;

        }

    }

