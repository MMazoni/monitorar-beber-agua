<?php

namespace BeberAgua\API\Domain\Controller;

use Firebase\JWT\JWT;
use BeberAgua\API\Domain\Model\User;
use BeberAgua\API\Infrastructure\Persistence\Connection;
use BeberAgua\API\Infrastructure\Repository\PdoUserRepository;

class UserController
{

    private $conn;
    /**@var  PdoUserRepository $repository*/
    private $repository;

    public function __construct()
    {
        $this->conn = Connection::connectDatabase();
        $this->repository = new PdoUserRepository($this->conn);
    }

    public function login()
    {
        http_response_code(200);
        echo json_encode(
            array("message" => "Teste.")
        );
    }

    public function signup(): void
    {
        $data = json_decode(file_get_contents("php://input"));

        $name = $data->name;
        $email = $data->email;
        $password = $data->password;

        $user = new User(null, $name, $email, $password);

        $success = $this->repository->save($user);

        if ($success) {
            http_response_code(200);
            echo json_encode(
                array("message" => "User created successfully.")
            );
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "User could not be created."));
        }
    }

    public function show(array $data)
    {
        $id = intval($data["userid"]);

        $user = $this->repository->getById($id);

        if (!isset($user) || empty($user)) {
            http_response_code(404);
            echo json_encode(array("message" => "User not exist."));
        }

        http_response_code(200);
            echo json_encode($user);
    }

    public function index()
    {

    }

    public function edit(int $user_id)
    {

    }

    public function destroy(int $user_id)
    {

    }

    public function drinkWater(int $user_id)
    {

    }
}