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

    public function allUsers(): array
    {
        $stmt = $this->conn->query('SELECT * from user;');

        return $this->hydrateUserList($stmt);
    }

    
    private function hydrateUserList(\PDOStatement $stmt): array
    {
        $userDataList = $stmt->fetchAll();
        $userList = [];
        
        foreach ($userDataList as $userData) {
            $userList[] = new User(
                $userData['id'],
                $userData['name'],
                $userData['email'],
                $userData['password']
            );
        }
        
        return $userList;
    }
    
    public function getById(int $id): array
    {

        $stmt = $this->conn->prepare('SELECT * FROM user WHERE id_user = ?;');
        $stmt->bindValue(1, $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
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
        $query = 'UPDATE user SET name = :name, email = :email, password = :password WHERE id = :id;';
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':name', $user->name());
        $stmt->bindValue(':email', $user->email());
        $stmt->bindValue(':password', $user->hashPassword());
        $stmt->bindValue(':id', $user->id());

        return $stmt->execute();
    }

    public function remove(User $user): bool
    {
        $stmt = $this->conn->prepare('DELETE FROM user WHERE id = ?;');
        $stmt->bindValue(1, $user->id(), PDO::PARAM_INT);

        return $stmt->execute();
    }

}

