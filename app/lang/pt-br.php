<?php

    /***/
    define("_LANG_APP_NOT_EXISTS_", "ERROR!");
    define("_LANG_FILE_NOT_EXISTS_", "O arquivo <strong>%s</strong> não existe!");
    define("_LANG_PARAM_NOT_EXISTS_", "O parâmetro <strong>%s</strong> não existe!");
    define("_LANG_TEMPORARILY_BLOCKED_ACCESS_", "Acesso bloqueado temporariamente");
    define("_LANG_LAYOUT_NOT_EXISTS_", "Layout <strong>%s</strong> não existe!");
    define("_LANG_VIEW_NOT_EXISTS_", "View <strong>%s</strong> não existe!");
    define("_LANG_CLASS_NOT_EXISTS_", "A Classe <strong>{{class}}</strong> não existe!");
    define("_LANG_METHOD_NOT_EXISTS_", "O método <strong>{{method}}</strong> não existe na classe {{class}}!");
    define("_LANG_SESSION_EXPIRED_", "
        <div class='box blue'><div class=\"box-color text-color\">
            <div class=\"box-header\">
              <h3>Falha de autenticação!</h3>
              <small>Talvez sua sessão tenha expirado</small>          
            </div>
        <div class=\"box-body\">
          <p class=\"m-a-0\">A sessão foi perdida. Faça login novamente, <a class='no-ajax text-info' href='/{{appname}}/login'>Clique aqui.</a></p>
        </div>
      </div></div>");
    define("_LANG_UNAUTHORIZED_ACCESS_", "<div class='note note-danger'><h4 class='break'>Acesso Negado</h4><p>Você não pode visualizar esta página no momento.</p></div>");

    /** Routers **/
    define("_LANG_ROUTER_URL_NOT_EXISTS_", "A url não existe!");

    /** Annotation **/
    define("_LANG_FIELD_IS_REQUIRED_", "Este campo é obrigatório");
    define("_LANG_FIELD_STRING_LENGTH_", "Comprimento errado");
    define("_LANG_FIELD_MIN_LENGTH_", "Comprimento pequeno");
    define("_LANG_FIELD_MAX_LENGTH_", "Comprimento máximo atingido");
    define("_LANG_FIELD_NUMERIC_", "O campo deve ser um número");
    define("_LANG_FIELD_RANGE_", "Valor deve está entre {{min}} e {{max}}");
    define("_LANG_FIELD_DATE_", "O campo deve ser uma data");
    define("_LANG_FIELD_MINDATE_", "O campo {{field}} deve possuir uma data maior que {{mindate}}");
    define("_LANG_FIELD_MAXDATE_", "O campo {{field}} deve possuir uma data menor que {{maxdate}}");
    define("_LANG_FIELD_RANGEDATE_", "O campo {{field}} deve possuir uma data entre {{mindate}} e {{maxdate}}");
    define("_LANG_FIELD_PHONE_", "Telefone incorreto");
    define("_LANG_FIELD_PHONECELL_", "O telefone deve ser um celular");
    define("_LANG_FIELD_PHONERESIDENTIAL_", "O telefone deve ser um fixo válido");
    define("_LANG_FIELD_PHONENATIONAL_", "O não é um númeronacional");
    define("_LANG_REMOTE_ERROR_", "O remoto retornou erro");
    define("_LANG_INCORRECT_MAIL_", "O e-mail informado está incorreto");
    define("_LANG_INCORRECT_URL_", "O e-mail informado está incorreto");
    define("_LANG_INCORRECT_CPF_", "O CPF informado está incorreto");
    define("_LANG_INCORRECT_CNPJ_", "O CNPJ informado está incorreto");
    define("_LANG_INCORRECT_CNPJORCPF_", "CPF ou CNPJ estão incorretos!");
    define("_LANG_INCORRECT_REGEXP_", "A expressão regular está incorreta");
    define("_LANG_FIELD_COMPARE_", "Os campos não são iguais");
    define("_LANG_MAXSMALLERMIN_", "O valor máximo não pode ser menor que o mínimo");

    /** Connection **/
    define("_LANG_CONNECTION_ERROR_", "Falha na conexão com o host %s");
    define("_LANG_INTERNET_CONNECTION_ERROR_", "<p style='margin: 0;' class='alert alert-danger'><i class='fa fa-chain-broken'></i> Verifique sua conexão</p>");


    /** Validation **/
    define("_LANG_INVALID_DATE", "Data %s inválida");

    //#################### CONTANTES ##########################
    /** DATETIME **/
    define("_LANG_MONTH_MINNAME", 'jan,fev,mar,abr,mai,jun,jul,ago,set,out,nov,dez');
    define("_LANG_MONTH_FULLNAME", 'janeiro,fevereiro,março,abril,maio,junho,julho,agosto,setembro,outubro,novembro,dezembro');


    /** Database **/
    define("_LANG_SQL_WHEREEMPTY_", "Condição Where é obrigatória");
    define("_LANG_SQL_HAVINGEMPTY_", "Condição é obrigatória para having");
    define("_LANG_SQL_ERROR_INSERT_", "Erro ao inserir dados");
    define("_LANG_SQL_ERROR_INSERT_NO_DATA_", "Sem dados para inserir");
    define("_LANG_SQL_ERROR_SELECT_", "Erro ao selecionar dados");
    define("_LANG_SQL_ERROR_UPDATE_", "Erro ao atualizar dados");
    define("_LANG_SQL_ERROR_UPDATE_NO_DATA_", "Sem dados para atualizar");
    define("_LANG_SQL_ERROR_DELETE_", "Erro ao excluir dados");
    define("_LANG_SQL_ERROR_QUERY_", "Erro ao executar a consulta");

    /** Directory **/
    define('_LANG_DIRECTORY_NOTDIRECTORY_', 'Não é uma pasta válida');
