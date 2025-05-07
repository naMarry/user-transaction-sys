<?php

include_once(__DIR__ . '/../db/db.php');

class User extends Dbh
{

    protected $db;

    public function __construct()
    {
        $this->db = $this->connect();
    }

    protected function createAdmin($username, $password)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users(username, password, role) VALUE(?, ?, ?)";
        $stmp = $this->connect()->prepare($sql);

        try {

            $stmp->execute([$username, $hashedPassword, 1]);
            return $stmp->rowCount() > 0 ? true : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    protected function createUser($username, $password, $balance)
    {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users(username, password, balance) VALUE(?, ?, ?)";
        $stmp = $this->connect()->prepare($sql);

        try {

            $stmp->execute([$username, $hashedPassword, $balance]);
            return $stmp->rowCount() > 0 ? true : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    protected function getAdminByUsername($username)
    {
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmp = $this->connect()->prepare($sql);

        try {
            $stmp->execute([$username]);
            $result = $stmp->fetch(PDO::FETCH_ASSOC);
            return $result ? $result : null;
        } catch (PDOException $e) {
            return false;
        }
    }

    protected function getUserById($id)
    {
        $sql = "SELECT * FROM users WHERE id = ?";
        $stmp = $this->connect()->prepare($sql);

        try {
            $stmp->execute([$id]);
            $result = $stmp->fetch(PDO::FETCH_ASSOC);
            return $result ? $result : null;
        } catch (PDOException $e) {
            return false;
        }
    }

    protected function getUsers()
    {
        $sql = "SELECT * FROM users ";
        $stmp = $this->connect()->prepare($sql);

        try {
            $stmp->execute();
            $result = $stmp->fetchAll(PDO::FETCH_ASSOC);
            return $result ? $result : [];
        } catch (PDOException $e) {
            return false;
        }
    }

    protected function updateBalanceById($balance, $id)
    {
        $sql = "UPDATE users SET balance = ? WHERE id = ?";
        $stmp = $this->connect()->prepare($sql);

        try {
            $stmp->execute([$balance, $id]);
            return $stmp->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }
}
