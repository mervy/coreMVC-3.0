<?php

namespace Core;

trait Authenticate
{

    public function login()
    {
       
        return $this->renderView('admin/login', 'layout_login');
    }

    public function auth($request)
    {
        $result = $this->authors->where(" email = '" . $request->post->email . "'")->oneWithJoin();
        
        if(empty($request->post->email)){
            Session::set('error', "E-mail não preenchido!");
        } else if(empty($request->post->password)){
            Session::set('error', "Senha não preenchida!");
        }else if ($result && $result['status']=='not') {
            Session::set('error',"Usuário bloqueado!!");   
             return Redirect::route('/login');         
        } else if ($result == "" || !(password_verify($request->post->password, $result['password'])) || $request->post->email != $result['email']) {
           Session::set('error', "Usuário ou senha não confere!");           
        }

        if ($result && password_verify($request->post->password, $result['password'])) {
            $user = [
                'id' => $result['id'],
                'name' => $result['name'],
                'email' => $result['email']
            ]; 
            Session::set('user', $user);   
            Session::set('sucess2', "Login bem sucedido!!");     
            return Redirect::route('/admin');
        }

        return Redirect::route('/login');
    }

    public function logout()
    {
        Session::destroy('user');
        return Redirect::route('/login');
    }

}
