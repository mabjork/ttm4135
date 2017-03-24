<?php

namespace ttm4135\webapp;

use ttm4135\webapp\models\User;

class Auth
{
    public static $cooldown = 10;
    function __construct()
    {
    }
	
	static public function checkPass($plaintext, $hash, $username)
    {
	//verify password
    	if(password_verify($plaintext,$hash)) {
    		return true;
    	}else {
    		return false;
    	}
    }

    static function checkCredentials($username, $password)
    {
        $user = User::findByUser($username);
        //var_dump($user);exit;
        if ($user === null) {
            //echo 'no user found';
            return false;
        }
        return self::checkPass($password, $user->getPassword(),$username);
    }

    /**
     * Check if is logged in.
     */
    static function check()
    {
	//return false;
        return isset($_SESSION['userid']);
    }

    /**
     * Check if the person is a guest.
     */
    static function guest()
    {
        return self::check() === false;
    }

    /**
     * Get currently logged in user.
     */
    static function user()
    {
        if (self::check()) {
            return User::findById($_SESSION['userid']);         
        }
    }

    /**
     * Is currently logged in user admin?
     */
    static function isAdmin()
    {
        if (self::check()) {
          return self::user()->isAdmin();	// uses this classes user() method to retrieve the user from sql, then call isadmin on that object.
        }

    }

    /** 
     * Does the logged in user have r/w access to user details identified by $tuserid
     */
    static function userAccess($tuserid) 
    {
        if(self::user()->getId() == $tuserid)   //a user can change their account
        {
          return true;
        }
        if (self::isAdmin() )           //admins can change any account
        {
          return true;
        }
        return false;

    }
    
    static function logout()
    {
        session_unset();
        session_destroy();
        session_start();
        session_regenerate_id();
    }

    static function attempt()
    {
        //return 1;
        $S = &$_SESSION;
        $s_a = 'login_attempt';
        $ct = time();
        $mat = 3;//max attempts
        $cd = self::$cooldown;

        $S[$s_a.$ct] = $ct;
        $an = 0;//attempts number
        foreach ($S as $key => $t) {
            if(preg_match("~^{$s_a}~", $key)){
                if($ct-$t > $cd){
                    unset($S[$key]);
                }else{
                    $an++;
                }
            }
        }
        $ret = ($an<=$mat)?1:0;
        return $ret;
    }
}
