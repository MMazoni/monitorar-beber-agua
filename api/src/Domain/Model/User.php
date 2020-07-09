<?php

namespace BeberAgua\API\Domain\Model;

use BeberAgua\API\Domain\Model\Drink;

class User
{
    private ?int $id;
    private string $name;
    private string $email;
    private string $password;
    /** @var Drink[] */
    private array $drinks;

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

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function hashPassword(): string
    {
        return password_hash($this->password, PASSWORD_ARGON2I);
    }

    public function email(): string
    {
        return $this->email;
    }

    public function addDrinkWater(?int $id, float $quantity_ml, string $datetime): void
    {
        $this->drinks[] = new Drink($id, $quantity_ml, $datetime);
    }

    /** @return Drink[] */
    public function drink(): array
    {
        return $this->drinks;
    }
}
