<?php

namespace BeberAgua\API\Infrastructure\Repository;

use PDO;
use BeberAgua\API\Domain\Model\User;

class PdoUserRepository
{
    /**@var PDO $conn */
    private $conn;

    public function __construct(PDO $connection)
    {
        $this->conn = $connection;
    }

    public function getAll(): array
    {
        if ($_GET["page"] && $_GET["row_per_page"]) {
            $page = $_GET["page"];
            $rows = $_GET["row_per_page"];
            $begin = ($page * $rows) - $rows;

            $stmt = $this->conn->prepare('SELECT * from user LIMIT :begin, :rows;');
            $stmt->bindValue(':begin', $begin, PDO::PARAM_INT);
            $stmt->bindValue(':rows', $rows, PDO::PARAM_INT);
            $stmt->execute();
            
            return $this->hydrateUserList($stmt);
        }
        $stmt = $this->conn->query('SELECT * from user;');

        return $this->hydrateUserList($stmt);
    }

    
    private function hydrateUserList(\PDOStatement $stmt): array
    {
        $userDataList = $stmt->fetchAll();
        $userList = [];
        
        foreach ($userDataList as $userData) {
            $userList[] = array(
                "user_id" => $userData['id_user'],
                "email" => $userData['email'],
                "name" => $userData['name'],
                "drink_counter" => $userData['drink_counter']
            );
        }
        return $userList;
    }
    
    public function getById(int $id): array
    {

        $stmt = $this->conn->prepare('SELECT * FROM user WHERE id_user = ?;');
        $stmt->bindValue(1, $id, PDO::PARAM_INT);
        $stmt->execute();
        $userData = $stmt->fetch();

        if (!$userData) {
            return [];
        }

        return array(
            "user_id" => $userData["id_user"],
            "name" => $userData['name'],
            "email" => $userData['email'],
            "drink_counter" => $userData['drink_counter']
        );
    }

    public function getByEmail(string $email): array
    {

        $stmt = $this->conn->prepare('SELECT * FROM user WHERE email = :email;');
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $userData = $stmt->fetch();

        if (!$userData) {
            return [];
        }

        return $userData;
    }

    public function save(User $user): bool
    {
        if ($user->id() === null) {
            return $this->insert($user);
        }

        return $this->update($user);
    }

    private function insert(User $user): bool
    {
        $query = 'INSERT INTO user (name, email, password) VALUES (:name, :email, :password);';
        $stmt = $this->conn->prepare($query);
        $success = $stmt->execute([
            ':name' => $user->name(),
            ':email' => $user->email(),
            ':password' => $user->hashPassword()
        ]);

        if ($success) {
            $user->defineId($this->conn->lastInsertId());
        }

        return $success;
    }
    
    private function update(User $user): bool
    {
        $query = 'UPDATE user SET name = :name, email = :email, password = :password WHERE id_user = :id;';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':name', $user->name());
        $stmt->bindValue(':email', $user->email());
        $stmt->bindValue(':password', $user->hashPassword());
        $stmt->bindValue(':id', $user->id());

        return $stmt->execute();
    }

    public function remove(int $id): bool
    {
        $user = $this->getById($id);

        if (!$user) {
            return false;
        }

        $stmt = $this->conn->prepare('DELETE FROM user WHERE id_user = ?;');
        $stmt->bindValue(1, $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function drink(int $id, float $drink): array
    {
        $user = $this->getById($id);

        if (!$user) {
            return false;
        }

        $counter = $user["drink_counter"] + 1;
        $query = 'UPDATE user SET drink_counter = :counter WHERE id_user = :id;';
        $firstStmt = $this->conn->prepare($query);
        $firstStmt->bindValue(':counter', $counter, PDO::PARAM_INT);
        $firstStmt->bindValue(':id', $id, PDO::PARAM_INT);
        $success[] = $firstStmt->execute();

        $query = 'INSERT INTO drink (id_user, drink_ml) VALUES (:id, :drink);';
        $secondStmt = $this->conn->prepare($query);
        $secondStmt->bindValue(':id', $id, PDO::PARAM_INT);
        $secondStmt->bindValue(':drink', $drink);
        $success[] = $secondStmt->execute();
        
        if (!$success[0] || !$success[1]) {
            return [];
        }
        
        return array(
            "user_id" => $id,
            "email" => $user['email'],
            "name" => $user['name'],
            "drink_counter" => $counter
        );
    
    }

}

