<?php

declare(strict_types=1);

namespace App\Inventory\Location\Domain;

use DomainException;

final readonly class LocationService
{
    public function __construct(
        private LocationRepositoryInterface $locations,
    ) {}

    public function save(LocationInput $input): void
    {
        $location = $input->toEntity();

        if ($this->locations->existsByCode($location->code, $location->id)) {
            throw new DomainException('Kode lokasi harus unik.');
        }

        $this->locations->save($location);
    }

    public function delete(int $id): void
    {
        if ($this->locations->isInUse($id)) {
            throw new DomainException('Lokasi sudah dipakai oleh stok atau transaksi.');
        }

        $this->locations->delete($id);
    }
}
