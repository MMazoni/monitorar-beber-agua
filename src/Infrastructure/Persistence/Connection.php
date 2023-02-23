<?php

namespace BeberAgua\API\Infrastructure\Persistence;

use PDO;
use PDOException;

class Connection
{
    public static function connectDatabase()
    {
        $hostName = $_ENV['MYSQL_HOSTNAME'];
        $port = $_ENV['MYSQL_PORT'];
        $username = $_ENV['MYSQL_USERNAME'];
        $password = $_ENV['MYSQL_PASSWORD'];
        $databaseName = $_ENV['MYSQL_DATABASE'];

        try {
            $conn = new PDO("mysql:host=$hostName;port=$port;dbname=$databaseName", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $conn;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
}
