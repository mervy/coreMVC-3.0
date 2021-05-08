<?php

namespace Core;

//use Core\DataBase;
use Core\Redirect;

class Container
{

    public static function newController($controller)
    {
        $_controller = "App\\Controllers\\" . $controller;
        return new $_controller;
    }

    public static function getModelEx($model, $conn)
    {
        $objModel = "\\App\\Models\\" . $model;
        return new $objModel($conn);
    }

    public static function pageNotFound()
    {
        if (file_exists(__DIR__ . "/../app/Views/404.phtml")) {
           Redirect::route('/404');
          //  return require_once __DIR__ . "/../app/Views/404.phtml";
        } else {
            echo "Erro 404: Page not found";
        }
    }

}
