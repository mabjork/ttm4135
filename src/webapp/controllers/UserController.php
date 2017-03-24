<?php

namespace ttm4135\webapp\controllers;

use ttm4135\webapp\models\User;
use ttm4135\webapp\Auth;

class UserController extends Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function index()
    {

        $issuer = (isset($_SERVER['REDIRECT_SSL_CLIENT_I_DN_CN']))?$_SERVER['REDIRECT_SSL_CLIENT_I_DN_CN']:false;
        $rightissuer = in_array($issuer,array('Student CA','Staff CA'));
    //echo'qwerasdf';var_dump($rightissuer);exit;
        if (!$issuer||!$rightissuer){
            $this->setFlashMessage('You must be a member of ttm4135 to register.','error');
            $this->app->flashNow('error', 'You must be a member of ttm4135 to register.');
            $this->app->redirect('/');
        }

        if (Auth::guest()) {
            $_SESSION["token"] = md5(uniqid(mt_rand(), true));
            $this->render('newUserForm.twig', array('csrf_token' => $_SESSION["token"]));
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You are already logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function create()
    {

        $issuer = (isset($_SERVER['REDIRECT_SSL_CLIENT_I_DN_CN']))?$_SERVER['REDIRECT_SSL_CLIENT_I_DN_CN']:false;
        $rightissuer = in_array($issuer,array('Student CA','Staff CA'));
	//echo'qwerasdf';var_dump($rightissuer);exit;
    	if (!$issuer||!$rightissuer){
            $this->setFlashMessage('You must be a member of ttm4135 to register.','error');
            $this->app->flash('error', 'You must be a member of ttm4135 to register.');
    		    $this->app->redirect('/register');
    	}

        $request = $this->app->request;

        $username = htmlentities($request->post('username'));
        $password = htmlentities($request->post('password'));


        $user = User::makeEmpty();
        $user->setUsername($username);
        $user->setPassword($password);

        if($_SERVER['REDIRECT_SSL_CLIENT_I_DN_CN'] == 'Staff CA'){
            $user->setIsAdmin("1");
        }

        if($request->post('email'))
        {
          $email = $request->post('email');
          $user->setEmail($email);
        }
        if($request->post('bio'))
        {
          $bio = $request->post('bio');
          $user->setBio($bio);
        }

        $user->save();
        $this->app->flash('info', 'Thanks for creating a user. You may now log in.');
        $this->app->redirect('/login');
    }

    function delete($tuserid)
    {
        if(Auth::userAccess($tuserid))
        {
            $user = User::findById($tuserid);
            $user->delete();
            $this->app->flash('info', 'User ' . $user->getUsername() . '  with id ' . $tuserid . ' has been deleted.');
            $this->app->redirect('/admin');
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function deleteMultiple()
    {
      if(Auth::isAdmin()){
          $request = $this->app->request;
          $userlist = $request->post('userlist');
          $deleted = [];

          if($userlist == NULL){
              $this->app->flash('info','No user to be deleted.');
          } else {
               foreach( $userlist as $duserid)
               {
                    $user = User::findById($duserid);
                    if(  $user->delete() == 1) { //1 row affect by delete, as expect..
                      $deleted[] = $user->getId();
                    }
               }
               $this->app->flash('info', 'Users with IDs  ' . implode(',',$deleted) . ' have been deleted.');
          }

          $this->app->redirect('/admin');
      } else {
          $username = Auth::user()->getUserName();
          $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
          $this->app->redirect('/');
      }
    }


    function show($tuserid)
    {
        if(Auth::userAccess($tuserid))
        {
          $user = User::findById($tuserid);
          $this->render('showuser.twig', [
            'user' => $user
          ]);
        } else {
            $username = Auth::user()->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function newuser()
    {

        $user = User::makeEmpty();

        if (Auth::isAdmin()) {


            $request = $this->app->request;

            $username = htmlentities($request->post('username'));
            $password = htmlentities($request->post('password'));
            $email = htmlentities($request->post('email'));
            $bio = htmlentities($request->post('bio'));

            $isAdmin = ($request->post('isAdmin') != null);


            $user->setUsername($username);
            $user->setPassword($password);
            $user->setBio($bio);
            $user->setEmail($email);
            $user->setIsAdmin($isAdmin);

            $user->save();
            $this->app->flashNow('info', 'Your profile was successfully saved.');

            $this->app->redirect('/admin');


        } else {
            $username = $user->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

    function edit($tuserid)
    {

        $user = User::findById($tuserid);

        if (! $user) {
            throw new \Exception("Unable to fetch logged in user's object from db.");
        } elseif (Auth::userAccess($tuserid)) {


            $request = $this->app->request;

            $username = htmlentities($request->post('username'));
            $password = htmlentities($request->post('password'));
            $email = htmlentities($request->post('email'));
            $bio = htmlentities($request->post('bio'));

            $isAdmin = ($request->post('isAdmin') != null);


            $user->setUsername($username);
	    if ($password)
            	$user->setPassword($password);
            $user->setBio($bio);
            $user->setEmail($email);
            $user->setIsAdmin($isAdmin);

            $user->save();
            $this->app->flashNow('info', 'Your profile was successfully saved.');

            $user = User::findById($tuserid);

            $this->render('showuser.twig', ['user' => $user]);


        } else {
            $username = $user->getUserName();
            $this->app->flash('info', 'You do not have access this resource. You are logged in as ' . $username);
            $this->app->redirect('/');
        }
    }

}
