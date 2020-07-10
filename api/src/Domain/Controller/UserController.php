<?php

namespace BeberAgua\API\Domain\Controller;

use BeberAgua\API\Domain\Model\User;
use BeberAgua\API\Infrastructure\Persistence\Connection;
use BeberAgua\API\Infrastructure\Repository\PdoUserRepository;
use BeberAgua\API\Infrastructure\Security\Authorization;

class UserController
{

    private $conn;
    /**@var  PdoUserRepository $repository*/
    private $repository;

    public function __construct()
    {
        $this->conn = Connection::connectDatabase();
        $this->repository = new PdoUserRepository($this->conn);
        $expireTime = 30000000;
        $this->auth = new Authorization($expireTime);
    }

    public function login()
    {
        $request = json_decode(file_get_contents("php://input"));

        $email = $request->email;
        $password = $request->password;

        $user = $this->repository->getByEmail($email);

        if (!$user) {
            http_response_code(404);
            echo json_encode(
                array("message" => "Email is not registered.")
            );
            return;
        }

        if (!password_verify($password, $user['password'])) {
            http_response_code(404);
            echo json_encode(
                array("message" => "Password is not correct.")
            );
            return;
        }

        http_response_code(200);

        $jwt = $this->auth->token();
        echo json_encode(
            array(
                "token" => $jwt,
                "user_id" => $user['id_user'],
                "email" => $user['email'],
                "name" => $user['name'],
                "drink_counter" => $user['drink_counter'],
            )
        );
    }

    public function signup(): void
    {
        $request = json_decode(file_get_contents("php://input"));

        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        $user = $this->repository->getByEmail($email);

        if ($user) {
            http_response_code(409);
            echo json_encode(
                array("message" => "Email already exists.")
            );
            return;
        }

        $new_user = new User(null, $name, $email, $password);

        $success = $this->repository->save($new_user);

        if (!$success) {
            http_response_code(400);
            echo json_encode(array("message" => "User could not be created."));
            return;
        }

        http_response_code(201);
        echo json_encode(
            array("message" => "User created successfully.")
        );
    }

    public function show(array $data): void
    {
        $this->authenticate();

        $id = intval($data["userid"]);

        $user = $this->repository->getById($id);

        if (!$user) {
            http_response_code(404);
            echo json_encode(array("message" => "User not exist."));
        }

        http_response_code(200);
        echo json_encode($user);
    }

    public function index(): void
    {
        $this->authenticate();

        $users = $this->repository->getAll();

        http_response_code(200);
        echo json_encode($users);
    }

    public function edit(array $data): void
    {
        $this->authenticate();

        $id = intval($data["userid"]);

        $user = $this->repository->getById($id);

        if (!$user) {
            http_response_code(404);
            echo json_encode(array("message" => "User not exist."));
            return;
        }
        $request = json_decode(file_get_contents("php://input"));

        $name = $request->name;
        $email = $request->email;
        $password = $request->password;

        $updated_user = new User($id, $name, $email, $password);

        $success = $this->repository->save($updated_user);

        if (!$success) {
            http_response_code(400);
            echo json_encode(array("message" => "User could not be updated."));
            return;
        }

        http_response_code(201);
        echo json_encode(
            array("message" => "User updated successfully.")
        );
    }

    public function destroy(array $data)
    {
        $this->authenticate();

        $id = intval($data["userid"]);

        $success = $this->repository->remove($id);

        if (!$success) {
            http_response_code(400);
            echo json_encode(array("message" => "User could not be deleted."));
            return;
        }

        http_response_code(204);
        echo json_encode(
            array("message" => "User deleted successfully.")
        );
    }

    public function drinkWater(array $data)
    {
        $this->authenticate();

        $request = json_decode(file_get_contents("php://input"));
        $id = intval($data["userid"]);
        $drink = floatval($request->drink_ml);

        $user = $this->repository->drink($id, $drink);

        if (!$user) {
            http_response_code(404);
            echo json_encode(array("message" => "Operation failed."));
            return;
        }

        http_response_code(200);
        echo json_encode($user);
    }

    private function authenticate(): void
    {
        $jwt = $this->auth->tokenFromHeaders();
        $decodedOrError = $this->auth->decode($jwt);

        if (is_string($decodedOrError)) {
            http_response_code(401);

            echo json_encode(array(
                "message" => "Access denied. " . $decodedOrError,
            ));
            exit();
        }
    }

    public function history(array $data): void
    {
        $this->authenticate();

        $id = intval($data["userid"]);

        $history = $this->repository->getHistory($id);

        if (!$history) {
            http_response_code(404);
            echo json_encode(array("message" => "User not exist."));
            return;
        }

        http_response_code(200);
        echo json_encode($history);
    }

    public function rankToday()
    {
        $ranking = $this->repository->getRanking();

        http_response_code(200);
        echo json_encode($ranking);
    }
}
