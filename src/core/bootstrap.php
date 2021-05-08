<?php

Class Bootstrap
{

    /**
     * Carrega as configurações iniciais do sistema
     * 
     */
    public function __construct()
    {
        /* start session */
        if (!session_id())
            session_start();

        date_default_timezone_set('America/Sao_Paulo');

        /* Log erros */
        $data = date("Y-m-d_H-i-s");

        ini_set('display_errors', 1);
        ini_set('log_errors', 1);
        ini_set('error_log', "assets/logs/log_$data.log");
        error_reporting(E_ALL);
        error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
        /*         * ** */

        /* start routes */
        $routes = require_once __DIR__ . "/../app/routes.php";
        $route = new \Core\Route($routes);
    }

}

$b = new Bootstrap();
