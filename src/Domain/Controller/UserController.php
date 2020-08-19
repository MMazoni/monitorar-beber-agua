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


    /**
    *   @OA\Post(
    *       path="/login",
    *       operationId="login",
    *       tags={"users"},
    *       summary="JWT Login",
    *       description="If the email and password are correct, it authenticates.",
    *       @OA\RequestBody(
    *           description="User email and password.",
    *           required=true,
    *           @OA\JsonContent(
    *               @OA\Property(
    *                   property="email",
    *                   description="User email",
    *                   format="email",
    *                   type="string",
    *               ),
    *               @OA\Property(
    *                   property="password",
    *                   description="User password",
    *                   format="password",
    *                   type="string",
    *               ),
    *           ),
    *       ),
    *       @OA\Response(
    *           response=200,
    *           description="Successful operation",
    *           @OA\JsonContent(ref="#/components/schemas/User")
    *       ),
    *       @OA\Response(
    *           response=404,
    *           description="Resource not found."
    *       )
    *   )
    */
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
        $this->auth->setUserId($user['id_user']);
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

    /**
     *  @OA\Post(
     *      path="/users",
     *      operationId="signup",
     *      tags={"users"},
     *      summary="Register a user",
     *      @OA\RequestBody(
     *          description="User information.",
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(
     *                  property="email",
     *                  description="User email",
     *                  format="email",
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="name",
     *                  description="User name",    
     *                  type="string",
     *              ),
     *              @OA\Property(
     *                  property="password",
     *                  description="User password",
     *                  format="password",
     *                  type="string",
     *              ),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="User could not be created."
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Email already exists."
     *      )
     *  )
     */
    public function signup(): void
    {
        $request = json_decode(file_get_contents("php://input"));

        $name = filter_var($request->name, FILTER_SANITIZE_STRING);
        $email = $request->email;
        $password = $request->password;

        if (empty($name) || empty($email) || empty($password)) {
            http_response_code(400);
            echo json_encode(
                array("message" => "It is necessary to fill all the fields.")
            );
            return;
        }

        $this->validateEmail($email);

        $user = $this->repository->getByEmail($email);

        if ($user) {
            http_response_code(409);
            echo json_encode(
                array("message" => "Email already exists.")
            );
            exit();
        }

        $newUser = new User(null, $name, $email, $password);

        $success = $this->repository->save($newUser);

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

    /**
     *  @OA\Get(
     *      path="/users/{id}",
     *      operationId="show",
     *      tags={"users"},
     *      summary="Get a user.",
     *      @OA\Parameter(
     *          name="id",
     *          in="query",
     *          required=true,
     *          description="The user ID",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *      ),
     *  )
     */
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

    /**
     *  @OA\Get(
     *      path="/users",
     *      operationId="index",
     *      tags={"users"},
     *      summary="Get all users.",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  ref="#/components/schemas/User"
     *              )
     *          )
     *      ),
     *  )
     */
    public function index(): void
    {
        $this->authenticate();

        $users = $this->repository->getAll();

        http_response_code(200);
        echo json_encode($users);
    }

    public function edit(array $data): void
    {
        $id = intval($data["userid"]);

        $jwt = $this->authenticate();

        if ($id != $jwt->user_id) {
            http_response_code(404);
            echo json_encode(array("message" => "This id does not belongs to your user."));
            return;
        }

        $user = $this->repository->getById($id);

        if (!$user) {
            http_response_code(404);
            echo json_encode(array("message" => "User not exist."));
            return;
        }
        $request = json_decode(file_get_contents("php://input"));

        $name = filter_var($request->name, FILTER_SANITIZE_STRING);
        $email = $request->email;
        $password = $request->password;

        $this->validateEmail($email);

        $user = $this->repository->getByEmail($email);

        if (isset($user) && $email == $user['email'] && $user['id_user'] != $id) {
            http_response_code(409);
            echo json_encode(
                array("message" => "Email already exists.")
            );
            exit();
        }

        $updatedUser = new User($id, $name, $email, $password);

        $success = $this->repository->save($updatedUser);

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
        $jwt = $this->authenticate();

        $id = intval($data["userid"]);

        if ($id != $jwt->user_id) {
            http_response_code(404);
            echo json_encode(array("message" => "This id does not belongs to your user."));
            return;
        }

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
        $jwt = $this->authenticate();
        $id = intval($data["userid"]);

        if ($id != $jwt->user_id) {
            http_response_code(404);
            echo json_encode(array("message" => "This id does not belongs to your user."));
            return;
        }

        $request = json_decode(file_get_contents("php://input"));
        $drink = $request->drink_ml;
        $search = ',';
        $replace = '.';
        $count = 1;
        $formattedQuantity = floatval(str_replace($search, $replace, $drink, $count));

        $user = $this->repository->drink($id, $formattedQuantity);

        if (!$user) {
            http_response_code(404);
            echo json_encode(array("message" => "Operation failed."));
            return;
        }

        http_response_code(200);
        echo json_encode($user);
    }

    private function authenticate()
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
        return $decodedOrError;
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

    private function validateEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(422);
            echo json_encode(
                array("message" => "Email {$email} is not valid.")
            );
            exit();
        }
    }
}
