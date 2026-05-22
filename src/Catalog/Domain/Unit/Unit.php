<?php

declare(strict_types=1);

namespace App\Catalog\Domain\Unit;

final readonly class Unit
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $symbol,
    ) {}
}
