<?php

namespace App\Controllers;

use Core\BaseController;
use Core\Container;
use Core\DataBase;
use Core\Helper;
use Core\Redirect;
use Core\Config;
use Core\Session;

class ArticlesController extends BaseController {

    public $articles;
    public $authors;
    public $categories;
    public $helper;
    public $news;

    public function __construct() {
        parent::__construct();

        $this->setSiteName("coreMVC 3.0");

        $conn = DataBase::getDatabase();
        $this->articles = Container::getModelEx("Article", $conn);
        $this->relations = Container::getModelEx("Article", $conn);
        $this->authors = Container::getModelEx("Author", $conn);
        $this->categories = Container::getModelEx("Category", $conn);
        $this->visitors = Container::getModelEx("Visitor", $conn);
        $this->news = Container::getModelEx("Newsletter", $conn);
        $this->visitorstotal = Container::getModelEx("Visitor", $conn);
        $this->visitorsmais = Container::getModelEx("Visitor", $conn);
        $this->rec = Container::getModelEx("Article", $conn);
        $this->cont_cat = Container::getModelEx("Article", $conn);

        $this->helper = new Helper;

        $this->view->categories = $this->categories->all();

        //Conta os acessos de visitantes por ip único
        $this->view->maisacessados = $this->visitorsmais->data('distinct articles_id, title, created, updated, image, COUNT(*) as qtd')
                ->join('articles', 'visitors.articles_id = articles.id')
                ->groupby('articles_id')
                ->order('qtd DESC')
                ->limit(5)
                ->allWithJoin(null);
        //Lista os artigos mais recentes
        $this->view->recentes = $this->rec->data(['AR.*', 'AU.name as auName', 'CA.name as caName', 'CA.slug as caSlug'])
                ->join('categories CA', 'AR.categories_id = CA.id')
                ->join('authors as AU', 'AR.authors_id = AU.id')
                ->where("AR.status!='not'")
                ->order('id DESC')
                ->limit(3)
                ->allWithJoin('AR');
        //Conta a quantidade de artigos por categoria
        $this->view->artbycat = $this->cont_cat->data('distinct Count(*) categories_id, CA.name, CA.slug')
                ->join('categories CA', 'categories_id = CA.id')
                ->where("AR.status!='not'")
                ->groupby('CA.id')
                ->allWithJoin('AR');
    }

    public function index($request) {
        if (@Session::get('sucess')) {
            $this->view->sucess = Session::get('sucess');
            Session::destroy('sucess');
        }
        if (@Session::get('errors')) {
            $this->view->errors = Session::get('errors');
            Session::destroy('errors');
        }

        $this->setPageTitle('Página inicial');

        $this->view->dados = $this->articles->data(['AR.*', 'AU.name as auName', 'CA.name as caName', 'CA.slug as caSlug'])
                ->join('categories CA', 'AR.categories_id = CA.id')
                ->join('authors as AU', 'AR.authors_id = AU.id')
                ->where("AR.status!='not'")
                ->order('updated DESC')
                ->limit(10)
                ->allWithJoin('AR');

//Validação do form em Index para newsletter
        $this->view->x = $nOne = mt_rand(0, 9);
        $this->view->y = $nTwo = mt_rand(0, 9);

        $this->renderView('article/index', 'layout');
    }

    public function blog($request) {
        $this->setPageTitle("Artigos e tutoriais");

        $this->view->dados = $this->articles->data(['AR.*', 'CA.id as caId', 'CA.name as caName', 'CA.slug as caSlug', 'AU.id as auId', 'AU.name as auName'])
                ->join('categories CA', "AR.categories_id = CA.id")
                ->join('authors as AU', 'AR.authors_id = AU.id')
                ->where("AR.status!='not'")
                ->allWithJoin('AR');

        $p = new Helper();
        $p->paginate($this->view->dados, 11, $request);
        $this->view->dados = $p->result;
        $this->view->contar = $p->contar;
        $this->view->atual = $p->atual;

        $this->renderView('article/blog', 'layout');
    }

    public function show($title, $id) {
        $this->view->article = $this->articles->data(['AR.*', 'CA.id as caId', 'CA.name as caName', 'CA.slug as caSlug', 'AU.id as auId', 'AU.name as auName'])
                ->join('categories CA', "AR.id = $id AND AR.categories_id = CA.id")
                ->join('authors as AU', 'AR.authors_id = AU.id')
                ->where("AR.status!='not'")
                ->oneWithJoin('AR');

        $this->setPageTitle("{$this->view->article['title']}");

        /**
         * Lógica para contador de visitas
         */
        $data = [
            'articles_id' => $this->view->article['id'],
            'ip' => $_SERVER['REMOTE_ADDR'],
//            'ip' => filter_input(INPUT_ENV, 'REMOTE_ADDR'), //Com INPUT_SERVER 
            'acessed_in' => date('Y-m-d H:s:i')
        ];

        $visitor_bd = $this->visitors->where(['articles_id' => $this->view->article['id'],
                    'ip' => $_SERVER['REMOTE_ADDR']])
                ->oneWithJoin();
        if (empty($visitor_bd)) {
            $this->visitors->create($data);
        } elseif ($visitor_bd['ip'] != $_SERVER['REMOTE_ADDR'] && $visitor_bd['articles_id'] != $this->view->article['id']) {
            $this->visitors->create($data);
        }
        $total = $this->visitorstotal->data(['COUNT(articles_id)'])
                ->where(['articles_id' => $this->view->article['id']])
                ->oneWithJoin();
        $this->view->total = $total['COUNT(articles_id)'];

        /**
         * Listar posts relacionados
         */
        $this->view->rel = $this->relations->data(['AR.*', 'CA.name as caName'])
                ->join('categories CA', "CA.name = '" . $this->view->article['caName'] . "' AND AR.categories_id = CA.id AND AR.id <> '" . $this->view->article['id'] . "'")
                ->limit(4)
                ->where("AR.status!='not'")
                ->allWithJoin('AR');
        if (!empty($this->view->article)) {
            $this->renderView('article/show', 'layout');
        } else {
            $this->renderView('404');
        }
    }

    public function category($cat, $request) {
        $this->setPageTitle("Categoria");

        $this->view->dados = $this->articles->data(['AR.*', 'CA.id as caId', 'CA.name as caName', 'CA.slug as caSlug', 'AU.id as auId', 'AU.name as auName'])
                ->join('categories CA', "CA.slug = '$cat' AND AR.categories_id = CA.id")
                ->join('authors as AU', 'AR.authors_id = AU.id')
                ->where("AR.status!='not'")
                ->allWithJoin('AR');

        $p = new Helper();
        $p->paginate($this->view->dados, 3, $request);
        $this->view->dados = $p->result;
        $this->view->contar = $p->contar;
        $this->view->atual = $p->atual;

        $this->renderView('article/category', 'layout');
    }

    public function newsletter($request) {
        $data = [
            'name' => $request->post->name,
            'email' => strtolower($request->post->email),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'registered_in' => date('Y-m-d H:s:i')
        ];

        //Verificação se e-mail já não está cadastrado
        $h = $this->news->where(['email' => $request->post->email])->oneWithJoin();

        if (empty($request->post->name)) {
            Session::set('errors', "Nome não preenchido!");
            return Redirect::route('/#news');
        }
        if (empty($request->post->email)) {
            Session::set('errors', "E-mail não preenchido!");
            return Redirect::route('/#news');
        }
        if (empty($request->post->soma)) {
            Session::set('errors', "Preencha o campo com o valor da soma!");
            return Redirect::route('/#news');
        }
        if ($request->post->soma != $request->post->hidden) {
            Session::set('errors', "Resposta da soma errada!");
            return Redirect::route('/#news');
        }
        if (!empty($h)) {
            Session::set('errors', "Email já cadastrado!");
            return Redirect::route('/#news');
        } else {
            Session::set('sucess', "Cadastro efetivado!");
            $this->news->create($data);
            mail('mervy@gkult.net', "Cadastro no site: " . $this->getSiteName() . "\n", "Houve um cadastro no site: " . $this->getSiteName() . ".\n"
                    . "Nome: " . $data['name'] . "\nE-mail: " . $request->post->email);
            Redirect::route('/#news');
        }
    }

    public function about() {
        $this->setPageTitle('Sobre nós...');
        $this->renderView('article/about', 'layout');
    }

    public function contact() {
        if (@Session::get('sucess')) {
            $this->view->sucess = Session::get('sucess');
            Session::destroy('sucess');
        }
        if (@Session::get('errors')) {
            $this->view->errors = Session::get('errors');
            Session::destroy('errors');
        }
        $this->setPageTitle("Contato");        

        $this->view->x = $nOne = mt_rand(0, 9);
        $this->view->y = $nTwo = mt_rand(0, 9);

        $this->renderView('article/contact', 'layout');
    }

    public function contactSend($request) {
        @$data = [
            'name' => $request->post->name,
            'email' => strtolower($request->post->email),
            'phone' => strtolower($request->post->phone),
            'message' => strtolower($request->post->message),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'registered_in' => date('Y-m-d H:s:i')
        ];

        if (empty($request->post->name)) {
            Session::set('errors', "Nome não preenchido!");
            return Redirect::route('/contact');
        }
        if (empty($request->post->email)) {
            Session::set('errors', "E-mail não preenchido!");
            return Redirect::route('/contact');
        }
        if (empty($request->post->message)) {
            Session::set('errors', "Escreva sua dúvida, elogio ou sugestão!");
            return Redirect::route('/contact');
        }
        if (empty($request->post->soma)) {
            Session::set('errors', "Preencha o campo com o valor da soma!");
            return Redirect::route('/contact');
        }
        if ($request->post->soma != $request->post->hidden) {
            Session::set('errors', "Resposta da soma errada!");
            return Redirect::route('/contact');
        }
        /* if (!empty($request->post->email) && filter_var($request->post->email, FILTER_VALIDATE_EMAIL)) {            
          Session::set('errors', "Formato de e-mail inválido!");
          return Redirect::route('/contact');
          } */ else {
            Session::set('sucess', "Sua mensagem foi enviada!");

            $to = "rogerio@gkult.net";
            $subject = "Mensagem do site: " . $this->getSiteName();
            $headers = "From: " . strip_tags($data['email']) . "\r\n";
            // $headers .= "Reply-To: " . strip_tags($_POST['req-email']) . "\r\n";
            //$headers .= "BCC: ".$data['email']."\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $headers .= "X-Priority: 1\n";
            $message = '<html><body>';
            $message .= "<h3>Mensagem do site: " . $this->getSiteName()."</h3>";
            $message .= '<table rules="all" style="border-color: #666;" cellpadding="10">';
            $message .= "<tr><td><b>Name:</b> </td><td>" . strip_tags($data['name']) . "</td></tr>";
            $message .= "<tr><td><b>Email:</b> </td><td>" . strip_tags($data['email']) . "</td></tr>";
            $message .= "<tr><td><b>Phone:</b> </td><td>" . strip_tags($_POST['phone']) . "</td></tr>";
            $message .= "<tr><td><b>Mensagem:</b> </td><td>" . htmlentities($data['message']) . "</td></tr>";
            $message .= "<tr><td><b>IP:</b> </td><td>" . $data['ip'] . "</td></tr>";
            $message .= "</table>";
            $message .= "</body></html>";

            mail($to, $subject, $message, $headers);            
            
            Redirect::route('/contact');
        }
    }

    public function adblock() {
        $this->renderView('article/adblock', 'layout');
    }

    public function pnf() { //PageNotFound
        $this->renderView('404', 'layout');
    }

}
