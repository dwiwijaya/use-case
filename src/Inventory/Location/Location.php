<?php

declare(strict_types=1);

namespace App\Inventory\Location;

final readonly class Location
{
    public function __construct(
        public ?int $id,
        public string $code,
        public string $name,
    ) {}
}
