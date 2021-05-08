<?php

return [
    /**
     * Options (mysql, sqlite)
     */
    'driver' => 'mysql', //mude aqui para sqlite
    'sqlite' => [
        'database' => 'localhost',
    ],
    'mysql' => [
        'host' => 'localhost',
        'database' => 'nameDatabase',
        'user' => 'userDatabse',
        'pass' => 'passDatabse',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci'
    ]
];
