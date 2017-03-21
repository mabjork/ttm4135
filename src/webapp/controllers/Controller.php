<?php

namespace ttm4135\webapp\controllers;
use ttm4135\webapp\Auth;
class Controller
{
    protected $app;
    private static $c_fm = "flashmes";

    function __construct()
    {
        $this->app = \Slim\Slim::getInstance();
    }

    function render($template, $variables = [])
    {     
      if (! Auth::guest()) {
            $user = Auth::user();
            $variables['isLoggedIn'] = true;
            $variables['isAdmin'] = $user->isAdmin();
            $variables['loggedInUsername'] = $user->getUsername();
            $variables['loggedInID'] = $user->getId();
        }
        if(isset($_SESSION[self::$c_fm]) && $_SESSION[self::$c_fm]){
            echo "<hr>".$_SESSION[self::$c_fm]."<hr>";
            unset($_SESSION[self::$c_fm]);
        }
        print $this->app->render($template, $variables);
    }

    protected function setFlashMessage($message, $type=null){
        $_SESSION[self::$c_fm] = $message;
    }

    public function test(){
        $pw = "fuckthesec";
        echo $pwh = password_hash($pw, PASSWORD_BCRYPT);
        echo "<br>";
        var_dump(password_verify($pw,$pwh));
        exit;
    }
}
