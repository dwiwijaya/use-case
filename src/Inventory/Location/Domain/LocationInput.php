<?php

declare(strict_types=1);

namespace App\Inventory\Location\Domain;

use Yiisoft\FormModel\Attribute\Safe;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Label;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

use function trim;

final class LocationInput extends FormModel
{
    #[Safe]
    public string $id = '';

    #[Label('Kode lokasi')]
    #[Required(message: 'Kode lokasi wajib diisi.')]
    #[Length(min: 3, max: 50)]
    public string $code = '';

    #[Label('Nama lokasi')]
    #[Required(message: 'Nama lokasi wajib diisi.')]
    #[Length(min: 2, max: 150)]
    public string $name = '';

    public function getFormName(): string
    {
        return 'location';
    }

    public static function fromEntity(Location $location): self
    {
        $input = new self();
        $input->id = (string) $location->id;
        $input->code = $location->code;
        $input->name = $location->name;

        return $input;
    }

    public function toEntity(): Location
    {
        return new Location(
            $this->id === '' ? null : (int) $this->id,
            trim($this->code),
            trim($this->name),
        );
    }
}
