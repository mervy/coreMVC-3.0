<?php

namespace Core;

abstract class BaseController
{

    protected $view;
    protected $auth;
    private $viewPath;
    private $layoutPath;
    private $pageTitle = null;
    private $siteName = null;

    public function __construct()
    {
        $this->view = new \stdClass;
        $this->auth = new Auth;
    }

    /**
     * 
     * @param type $viewPath - caminho da view
     * @param type $layoutPath - caminho do layout base
     * Uso:  $this->renderView('home/index','layout');
     */
    protected function renderView($viewPath, $layoutPath = null)
    {
        $this->viewPath = $viewPath;
        $this->layoutPath = $layoutPath;
        if ($layoutPath) {
            return $this->layout();
        } else {
            return $this->content();
        }
    }

    /**
     * Colocar num arquivo html como *cabeçalho* - $this->content(); - *footer*
     */
    protected function content()
    {
        if (file_exists(__DIR__ . "/../app/Views/{$this->viewPath}.phtml")) {
            require_once __DIR__ . "/../app/Views/{$this->viewPath}.phtml";
        } else {
            echo "Error: View path não encontrada!";
        }
    }

    protected function layout()
    {
        if (file_exists(__DIR__ . "/../app/Views/{$this->layoutPath}.phtml")) {
            require_once __DIR__ . "/../app/Views/{$this->layoutPath}.phtml";
        } else {
            echo "Error: View path não encontrada!";
        }
    }

    protected function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
    }

    protected function getPageTitle($separator = null)
    {
        if ($separator) {
            return $this->pageTitle . " " . $separator . " ";
        } else {
            return $this->pageTitle . " ";
        }
    }

    protected function getSiteName()
    {
        return $this->siteName;
    }

    protected function setSiteName($siteName)
    {
        $this->siteName = $siteName;
    }

    public function forbidden()
    {
        return Redirect::route('/login');
    }

}
