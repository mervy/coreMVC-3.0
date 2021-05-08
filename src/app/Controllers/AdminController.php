<?php

namespace App\Controllers;

use Core\Container;
use Core\BaseController;
use Core\DataBase;
use Core\Redirect;
use Core\Session;
use Core\Helper;
use Core\UploadHelper;
use Core\Authenticate;

class AdminController extends BaseController
{

    use Authenticate;

    public $articles;
    public $authors;
    public $categories;
    public $news;

    public function __construct()
    {
        parent::__construct();
      
       $this->setSiteName("coreMVC 3.0");
      
        $conn = DataBase::getDatabase();  //não esquecer do use Core\DataBase;
        $this->articles = Container::getModelEx("Article", $conn);
        $this->authors = Container::getModelEx("Author", $conn);
        $this->categories = Container::getModelEx("Category", $conn);
        $this->visitor = Container::getModelEx("Visitor", $conn);
        $this->news = Container::getModelEx("Newsletter", $conn);
        $this->helper = new Helper;        
    }

    public function index($request)
    {
       /* if (@Session::get('sucess')) {
            $this->view->sucess = Session::get('sucess');
            Session::destroy('sucess');
        }
        if (@Session::get('errors')) {
            $this->view->errors = Session::get('errors');
            Session::destroy('errors');*/
        
        $this->view->articles = $this->articles->data('AR.*, CA.name as caName, AU.name as auName')
                ->join('categories as CA', 'AR.categories_id = CA.id')
                ->join('authors as AU', 'AR.authors_id = AU.id')
                ->order('updated DESC')
                ->allWithJoin('AR');

        $p = new Helper();
        $p->paginate($this->view->articles, 10, $request);
        $this->view->articles = $p->result;
        $this->view->contar = $p->contar;
        $this->view->atual = $p->atual;

        $this->renderView('admin/article/index', 'layout_admin');
    }

    /**
     * 
     * @param type $page
     * Mostra a lista de cadastro e opções para criar, editar e deletar
     */
    public function show($page, $request)
    {
        switch ($page) {
            case "article":
                $this->view->articles = $this->articles->data('AR.*, CA.name as caName, AU.name as auName')
                        ->join('categories as CA', 'AR.categories_id = CA.id')
                        ->join('authors as AU', 'AR.authors_id = AU.id')
                        ->order('updated DESC')
                        ->allWithJoin('AR');

                $p = new Helper();
                $p->paginate($this->view->articles, 10, $request);
                $this->view->articles = $p->result;
                $this->view->contar = $p->contar;
                $this->view->atual = $p->atual;

                $this->renderView('admin/article/index', 'layout_admin');
                break;
            case "author":
                $this->view->authors = $this->authors->All();
                $this->renderView('admin/author/index', 'layout_admin');
                break;
            case "category":
                $p = new Helper();
                $p->paginate($this->categories->All(), 5, $request);
                $this->view->categories = $p->result;
                $this->view->contar = $p->contar;
                $this->view->atual = $p->atual;

                $this->renderView('admin/category/index', 'layout_admin');
                break;
            case "visitor":
                $dados = $this->visitor->data('articles_id, title, visitors.id as viId, ip, acessed_in')
                        ->join('articles', 'visitors.articles_id = articles.id')
                        ->order('viId ASC')
                        ->allWithJoin(null);

                $p = new Helper();
                $p->paginate($dados, 20, $request);
                $this->view->visitor = $p->result;
                $this->view->contar = $p->contar;
                $this->view->atual = $p->atual;

                $this->renderView('admin/visitor/index', 'layout_admin');
                break;
            case "newsletter":
                $dados = $this->news->all();               

                $p = new Helper();
                $p->paginate($dados, 25, $request);
                $this->view->news = $p->result;
                $this->view->contar = $p->contar;
                $this->view->atual = $p->atual;

                $this->renderView('admin/newsletter/index', 'layout_admin');
                break;
        }
    }

    /**
     * 
     * @param type $page
     * Mostra apenas os formulários para inserção de novos dados
     */
    public function create($page)
    {
        switch ($page) {
            case "article":
                $this->setPageTitle("Create new article");
                $this->view->categories = $this->categories->All();
                $this->view->authors = $this->authors->All();
                $this->renderView('admin/article/create', 'layout_admin');
                break;
            case "author":
                $this->setPageTitle("Create new author");
                $this->renderView('admin/author/create', 'layout_admin');
                break;
            case "category":
                $this->setPageTitle("Create new category");
                $this->renderView('admin/category/create', 'layout_admin');
                break;
        }
    }

    /**
     * 
     * @param type $page
     * @param type $request
     * Grava os dados $request (do formulário de create) no banco de dados
     */
    public function store($page, $request)
    {
        switch ($page) {
            case "article":
                $data = [
                    "title" => $request->post->title,
                    "image" => $request->post->image,
                    "content" => $request->post->content,
                    "created" => $request->post->created,
                    "authors_id" => $request->post->authors_id,
                    "categories_id" => $request->post->categories_id,
                ];

                if (!empty($_FILES['files']['name'][0])) {
                    $upload = new UploadHelper();
                    $upload->setFile($_FILES['files'])
                            //Com o helper, o titulo se transforma na pasta das imagens
                            ->setPath('assets/uploads/articles/' . $this->helper->urlSEO($request->post->title, '-'))
                            ->upload();
                }
                $this->articles->create($data);
                Redirect::route('/admin');
                break;
            case "author":
                $data = [
                    "name" => $request->post->name,
                    "email" => $request->post->email,
                    "nickname" => $request->post->nickname,
                    "password" => $request->post->password
                ];
                $data['password'] = password_hash($request->post->password, PASSWORD_BCRYPT);
                $this->authors->create($data);
                Redirect::route('/admin/show/author');
                break;
            case "category":
                $data = [
                    "name" => $request->post->name,
                    "slug" => $request->post->slug,
                    "description" => $request->post->description
                ];
                $this->categories->create($data);
                Redirect::route('/admin/show/category');
                break;
        }
    }

    public function preview($id)
    {
        $this->view->article = $this->articles->data(['AR.*', 'CA.id as caId', 'CA.name as caName', 'CA.slug as caSlug', 'AU.id as auId', 'AU.name as auName'])
                ->join('categories CA', "AR.id = $id AND AR.categories_id = CA.id")
                ->join('authors as AU', 'AR.authors_id = AU.id')
                ->oneWithJoin('AR');

        
        $this->renderView('admin/article/preview', 'layout_preview');
    }

    public function edit($page, $id)
    {
        switch ($page) {
            case "article":
                $this->view->article = $this->articles->data(['AR.*', 'CA.id as caId', 'CA.name as caName', 'CA.slug as caSlug', 'AU.id as auId', 'AU.name as auName'])
                        ->join('categories CA', "AR.id = $id AND AR.categories_id = CA.id")
                        ->join('authors as AU', 'AR.authors_id = AU.id')
                        ->oneWithJoin('AR');

                $this->view->authors = $this->authors->All();
                $this->view->categories = $this->categories->All();

                /**
                 * Preparar visualização de imagens do servidor
                 */
                $dir = 'assets/uploads/articles/' . $this->helper->urlSEO($this->view->article['title'], '-') . '/';
                if (is_dir($dir)) {$this->view->img = scandir($dir);}

                $this->setPageTitle('Edit article - ' . $this->view->article['title']);
                $this->renderView('admin/article/edit', 'layout_admin');
                break;
            case "author":
                $this->view->author = $this->authors->find($id);
                $this->setPageTitle('Edit author - ' . $this->view->author['name']);
                $this->renderView('admin/author/edit', 'layout_admin');
                break;
            case "category":
                $this->view->category = $this->categories->find($id);
                $this->setPageTitle('Edit category - ' . $this->view->category['name']);
                $this->renderView('admin/category/edit', 'layout_admin');
                break;
        }
    }

    public function update($page, $id, $request)
    {
        switch ($page) {
            case "article":
                $data = [
                    "title" => $request->post->title,
                    "image" => $request->post->image,
                    "content" => $request->post->content,
                    "updated" => $request->post->updated,
                    "authors_id" => $request->post->authors_id,
                    "categories_id" => $request->post->categories_id,
                ];

                if (!empty($_FILES['files']['name'][0])) {
                    $upload = new UploadHelper();
                    $upload->setFile($_FILES['files'])
                            //Com o helper, o titulo se transforma na pasta das imagens
                            ->setPath('assets/uploads/articles/' . $this->helper->urlSEO($request->post->title, '-'))
                            ->upload();
                }
                //Exclui múltiplas imagens                
                if (@$request->post->imagedel != null)
                    foreach ($request->post->imagedel as $imgdel) {
                        unlink($imgdel);
                    }

                if ($this->articles->update($data, $id)) {
                    return Redirect::route('/admin', [
                                'sucess' => ['Artigo atualizado com sucesso!']
                    ]);
                } else {
                    return Redirect::route('/admin', [
                                'errors' => ['Erro ao atualizar!']
                    ]);
                }
                break;
            case "author":
                $data = [
                    "name" => $request->post->name,
                    "email" => $request->post->email,
                    "nickname" => $request->post->nickname,
                    "password" => $request->post->password
                ];
                $data['password'] = password_hash($request->post->password, PASSWORD_BCRYPT);
                $this->authors->update($data, $id);
                Redirect::route('/admin/show/author');
                break;
            case "category":
                $data = [
                    "name" => $request->post->name,
                    "slug" => $request->post->slug,
                    "description" => $request->post->description
                ];
                $this->categories->update($data, $id);
                Redirect::route('/admin/show/category');
                break;
        }
    }

    public function approve($page, $id)
    {
        switch ($page) {
            case "articles":
                try {
                    $u = $this->articles->find($id);
                    if ($u['status'] == 'yes') {
                        $this->articles->update(['status' => 'not'], $id);
                    } else {
                        $this->articles->update(['status' => 'yes'], $id);
                    }

                    return Redirect::route('/admin');
                } catch (Exception $exc) {
                    echo $exc->getMessage();
                }
                break;
            case "authors":
                try {
                    $u = $this->authors->find($id);
                    if ($u['status'] == 'yes') {
                        $this->authors->update(['status' => 'not'], $id);
                    } else {
                        $this->authors->update(['status' => 'yes'], $id);
                    }
                    return Redirect::route('/admin/show/author');
                } catch (Exception $exc) {
                    echo $exc->getMessage();
                }
                break;
        }
    }

    public function delete($page, $id)
    {
        switch ($page) {
            case "article":
                $this->articles->delete($id);
                return Redirect::route('/admin');
                break;
            case "category":
                $this->categories->delete($id);
                return Redirect::route('/admin/show/category');
                break;
            case "author":
                $this->authors->delete($id);
                return Redirect::route('/admin/show/author');
                break;
            case "visitor":
                $this->visitor->delete($id);
                return Redirect::route('/admin/show/visitor');
                break;
            case "newsletter":
                $this->news->delete($id);
                return Redirect::route('/admin/show/newsletter');
                break;
        }
    }

}
