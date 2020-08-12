<?php

namespace BeberAgua\API\Domain\Model;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema()
 */
class User
{
    private ?int $id;
    private string $name;
    private string $email;
    private string $password;

    public function __construct(?int $id, string $name, string $email, string $password)
    {
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }

    public function defineId(int $id): void
    {
        if (!is_null($this->id)) {
            throw new \DomainException('É possível apenas definir o ID uma vez.');
        }

        $this->id = $id;
    }

    /**
     *  The user id
     *  @var int
     *  @OA\Property()
     */
    public function id(): ?int
    {
        return $this->id;
    }

    /**
     *  The user name
     *  @var string
     *  @OA\Property()
     */
    public function name(): string
    {
        return $this->name;
    }

    public function hashPassword(): string
    {
        return password_hash($this->password, PASSWORD_ARGON2I);
    }

    /**
     *  The user email
     *  @var string
     *  @OA\Property()
     */
    public function email(): string
    {
        return $this->email;
    }

    /**
     *  @var int
     *  @OA\Property(
     *      property="drink_counter",
     *      type="int",
     *      description="Number of drinks through the day."
     *  )
     */
}
