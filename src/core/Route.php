<?php

namespace Core;

use Core\Auth;

class Route
{

    private $routes;
    private $param;
    private $found;

    public function __construct(array $routes)
    {
        $this->setRoutes($routes);
        $this->run();
    }

    private function setRoutes($routes)
    {
        foreach ($routes as $route) {
            $explode = explode('@', $route[1]);
            if (@$route[2]) {//3.º parametro rota (auth)
                $r = [$route[0], $explode[0], $explode[1], $route[2]];
            } else {
                $r = [$route[0], $explode[0], $explode[1]];
            }

            $newRotes [] = $r;
        }
        $this->routes = $newRotes;
    }

    private function getRequest()
    {
        $obj = new \stdClass();
        
        foreach ($_GET as $key => $value) {
            @$obj->get->$key = $value;
        }
        
        foreach ($_POST as $key => $value) {
            @$obj->post->$key = $value;
        }
        return $obj;
    }

    public function getUrl()
    {
        return parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    }

    private function run()
    {
        $url = $this->getUrl();
        $urlArray = explode('/', $url);

        foreach ($this->routes as $route) {
            $routeArray = explode('/', $route[0]);
            $this->param = []; //Zera os parametros 'param'
            for ($i = 0; $i < count($routeArray); $i++) {
                if ((strpos($routeArray[$i], "{") !== false) && (count($urlArray) == count($routeArray))) {
                    $routeArray[$i] = $urlArray[$i];
                    $this->param[] = $urlArray[$i];
                }
                $route[0] = implode('/',$routeArray);
            }
            if ($url == $route[0]) {
                $this->found = true;
                $controller = $route[1];
                $action = $route[2];
                $auth = new Auth;
                if (@$route[3] && !$auth->check()) {
                    $action = 'forbidden';
                }
                break;
            }
        }
        if ($this->found) {
            $controller = Container::newController($controller);

            switch (count($this->param)) {
                case 1:
                    $controller->$action($this->param[0], $this->getRequest());
                    break;
                case 2:
                    $controller->$action($this->param[0], $this->param[1], $this->getRequest());
                    break;
                case 3:
                    $controller->$action($this->param[0], $this->param[1], $this->param[2], $this->getRequest());
                    break;
                default:
                    $controller->$action($this->getRequest());
                    break;
            }
        } else {
            Container::pageNotFound();
        }
    }

}
