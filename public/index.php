<?php

class Index
{

    /**
     * Carrega os arquivos iniciais de autoloading
     * "autoload.php" gerado pelo composer e
     * "bootstrap.php" que inicializa o sistema
     *  
     */
    public function __construct()
    {
        require_once __DIR__ . '/../vendor/autoload.php';

        require_once __DIR__ . '/../src/core/bootstrap.php';
    }

}

$d = new Index();


