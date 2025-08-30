<?php
require_once "./config.php";

class ConnectDB
{
    private $servername;
    private $username;
    private $password;
    private $dbname;
    private $connection = null;

    function __construct()
    {
        $this->servername = HOST;
        $this->username   = USERNAME;
        $this->password   = PASSWORD;
        $this->dbname     = DBNAME;
    }

    private function connect()
    {
        if ($this->connection === null) {
            try {
                $this->connection = new PDO(
                    "mysql:host={$this->servername};dbname={$this->dbname};charset=utf8mb4",
                    $this->username,
                    $this->password
                );
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                echo "Lỗi kết nối: " . $e->getMessage();
                die();
            }
        }
        return $this->connection;
    }

    function getAll($sql, $params = [])
    {
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function getOne($sql, $params = [])
    {
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function execute($sql, $params = [])
    {
        $stmt = $this->connect()->prepare($sql);
        return $stmt->execute($params);
    }

    function getValue($sql, $params = [])
    {
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    function getLastInsertId()
    {
        return $this->connect()->lastInsertId();
    }
}
