<?php

class Dbh
{
    private $host = 'localhost';
    private $user = 'root';
    private $pwd = '';
    private $dbName = 'user_service';

    protected function connect()
    {
        $dns = 'mysql:host=' . $this->host . ';dbname=' . $this->dbName;
        $pdo = new PDO($dns, $this->user, $this->pwd);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        return $pdo;
    }

    // private $host = 'banking-db';
    // private $user = 'banking_user';
    // private $pwd = 'banking_pass';
    // private $dbName = 'user_service';

    // protected function connect()
    // {
    //     $dns = 'mysql:host=' . $this->host . ';dbname=' . $this->dbName;
    //     $pdo = new PDO($dns, $this->user, $this->pwd);
    //     $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    //     return $pdo;
    // }
}
