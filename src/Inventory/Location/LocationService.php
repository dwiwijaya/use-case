<?php

declare(strict_types=1);

namespace App\Inventory\Location;

final readonly class LocationService
{
    public function __construct(
        private LocationRepositoryInterface $locations,
    ) {}

    public function save(LocationInput $input): void
    {
        $this->locations->save($input->toEntity());
    }

    public function delete(int $id): void
    {
        $this->locations->delete($id);
    }
}
