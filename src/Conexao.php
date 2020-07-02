<?php

namespace TestePratico\StautRH;

use PDO;
use PDOException;

class Conexao
{
    public static function connect_database()
    {
        $servername = "127.0.0.1";
        $port = "3307";
        $username = "root";
        $password = "monitoramento";
        $dbname = "monitoramento_agua";

        try{
            $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname",$username,$password);
            $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch(PDOException $e){
            echo "Connection failed: " . $e->getMessage();
            return false;
        }
    }
}