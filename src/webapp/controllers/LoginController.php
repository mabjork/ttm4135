<?php

namespace ttm4135\webapp\controllers;
use ttm4135\webapp\Auth;
use ttm4135\webapp\models\User;

class LoginController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {
        if (Auth::check()) {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        } else {
            if(isset($_COOKIE["user"]))
                $username = $_COOKIE["user"];
            else
                $username = false;
            $_SESSION["token"] = md5(uniqid(mt_rand(), true));
            $this->render('login.twig', ['title'=>"Login","username" =>$username,'csrf_token' =>$_SESSION["token"]]);
        }
    }

    function login()
    {
        $request = $this->app->request;
        $username = htmlentities($request->post('username'));
        $password = htmlentities($request->post('password'));
	/*
	var_dump($_SESSION["token"]);
        if (isset($_SESSION["token"])) {
            if ($_SESSION["token"] != $request->post("csrf_token")) {
                $this->app->flashNow('error', 'Wrong token');
                echo "Wrong token";
                $this->app->redirect('/');
		
            }
        }
	
	*/
        $attempt = Auth::attempt();
        if(!$attempt){
            $this->setFlashMessage("You are entering passwords to often. Wait ".Auth::$cooldown." seconds",'error');
            $this->render('login.twig', []);
        }
        #was us
        $cookie_name = "user";
        $cookie_value = $username;
        setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");

        if ( Auth::checkCredentials($username, $password) ) {
            $user = User::findByUser($username);
            Auth::logout();
            $_SESSION['userid'] = $user->getId();
	    $this->app->flash('info', "You are now successfully logged in as " . $user->getUsername() . ".");
            $this->app->redirect('/');
        } else {
            $this->app->flashNow('error', 'Incorrect username/password combination.');
            $this->render('login.twig', []);
        }
    }

    function logout()
    {
        Auth::logout();
	session_destroy();
        $this->app->flashNow('info', 'Logged out successfully!!');
        $this->render('base.twig', []);
        return;

    }
}
