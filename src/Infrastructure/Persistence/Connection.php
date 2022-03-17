<?php

namespace BeberAgua\API\Infrastructure\Persistence;

use PDO;
use PDOException;

class Connection
{
    public static function connectDatabase()
{
        $servername = "mysql";
        $port = "3306";
        $username = "root";
        $password = "monitoramento";
        $dbname = "monitoramento_agua";

        try {
            $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $conn;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    }
}
