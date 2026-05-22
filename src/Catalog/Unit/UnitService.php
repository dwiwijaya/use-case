<?php

declare(strict_types=1);

namespace App\Catalog\Unit;

final readonly class UnitService
{
    public function __construct(
        private UnitRepositoryInterface $units,
    ) {}

    public function save(UnitInput $input): void
    {
        $this->units->save($input->toEntity());
    }

    public function delete(int $id): void
    {
        $this->units->delete($id);
    }
}
