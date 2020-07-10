<?php

namespace BeberAgua\API\Domain\Model;

class Drink
{
    private ?int $id;
    private float $quantity_ml;
    private string $datetime;

    public function __construct(?int $id, float $quantity_ml, string $datetime)
    {
        $this->id = $id;
        $this->quantity_ml = $quantity_ml;
        $this->datetime = $datetime;
    }
}