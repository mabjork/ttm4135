<?php

namespace ttm4135\webapp\models;

class User
{
    const INSERT_QUERY = "INSERT INTO users(username, password, email, bio, isadmin) VALUES(?, ?, ? , ? , ?)";
    const UPDATE_QUERY = "UPDATE users SET username=?, password=?, email=?, bio=?, isadmin=? WHERE id=?";
    const DELETE_QUERY = "DELETE FROM users WHERE id=?";
    const FIND_BY_NAME_QUERY = "SELECT * FROM users WHERE username=?";
    const FIND_BY_ID_QUERY = "SELECT * FROM users WHERE id=?";
    protected $id = null;
    protected $username;
    protected $password;
    protected $email;
    protected $bio = 'Bio is empty.';
    protected $isAdmin = 0;

    static $app;


    static function make($id, $username, $password, $email, $bio, $isAdmin )
    {
        $user = new User();
        $user->id = $id;
        $user->username = $username;
        $user->password = $password;
        $user->email = $email;
        $user->bio = $bio;
        $user->isAdmin = $isAdmin;

        return $user;
    }

    static function makeEmpty()
    {
        return new User();
    }

    /**
     * Insert or update a user object to db.
     */
    function save()
    {
        $this->username = htmlspecialchars($this->username);
        $this->email = htmlspecialchars($this->email);
        $this->bio = htmlspecialchars($this->bio);
        
        if ($this->id === null) {
            $stmt = self::$app->db->prepare(self::INSERT_QUERY);
            $stmt->bindParam(1, $this->username);
            $stmt->bindParam(2, $this->password);
            $stmt->bindParam(3, $this->email);
            $stmt->bindParam(4, $this->bio);
            $stmt->bindParam(5, $this->isAdmin);
            /*
            $query = sprintf(self::INSERT_QUERY,
                $this->username,
                $this->password,
                $this->email,
                $this->bio,
                $this->isAdmin            );
            */
        } else {
            $stmt = self::$app->db->prepare(self::UPDATE_QUERY);
            $stmt->bind_param("ssssii", $username, $password, $email,$bio,$isAdmin,$id);
            $stmt->bindParam(1, $this->username);
            $stmt->bindParam(2, $this->password);
            $stmt->bindParam(3, $this->email);
            $stmt->bindParam(4, $this->bio);
            $stmt->bindParam(5, $this->isAdmin);
            $stmt->bindParam(6, $this->id);
          /*
          $query = sprintf(self::UPDATE_QUERY,
                $this->username,
                $this->password,
                $this->email,
                $this->bio,
                $this->isAdmin,
                $this->id
            );
          */
        }

        return $stmt->execute();
    }

    function delete()
    {
        $stmt = self::$app->db->prepare(self::DELETE_QUERY);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    function getId()
    {
        return $this->id;
    }

    function getUsername()
    {
        return $this->username;
    }

    function getPassword()
    {
        return $this->password;
    }

    function getEmail()
    {
        return $this->email;
    }

    function getBio()
    {
        return $this->bio;
    }

    function isAdmin()
    {
        return $this->isAdmin === "1";
    }

    function setId($id)
    {
        $this->id = $id;
    }

    function setUsername($username)
    {
        $this->username = $username;
    }

    function setPassword($password)
    {
        $this->password = $password;
    }

    function setEmail($email)
    {
        $this->email = $email;
    }

    function setBio($bio)
    {
        $this->bio = $bio;
    }
    function setIsAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;
    }


    /**
     * Get user in db by userid
     *
     * @param string $userid
     * @return mixed User or null if not found.
     */
    static function findById($userid)
    {
        $stmt = self::$app->db->prepare(self::FIND_BY_ID_QUERY);
        $stmt->bindParam(1, $userid);
        $stmt->execute();

        $row = $stmt->fetch();

        if($row == false) {
            return null;
        }

        return User::makeFromSql($row);
    }

    /**
     * Find user in db by username.
     *
     * @param string $username
     * @return mixed User or null if not found.
     */
    static function findByUser($username)
    {
        $stmt = self::$app->db->prepare(self::FIND_BY_NAME_QUERY);
        $stmt->bindParam(1, $username);
        $stmt->execute();
;
        $row = $stmt->fetch();

        if($row == false) {
            return null;
        }

        return User::makeFromSql($row);
    }


    static function all()
    {
        $query = "SELECT * FROM users";
        $results = self::$app->db->query($query);

        $users = [];

        foreach ($results as $row) {
            $user = User::makeFromSql($row);
            array_push($users, $user);
        }

        return $users;
    }

    static function makeFromSql($row)
    {
        return User::make(
            $row['id'],
            $row['username'],
            $row['password'],
            $row['email'],
            $row['bio'],
            $row['isadmin']
        );
    }

}


  User::$app = \Slim\Slim::getInstance();
