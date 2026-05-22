<?php

declare(strict_types=1);

namespace App\Catalog\Unit;

use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Label;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

use function trim;

final class UnitInput extends FormModel
{
    public string $id = '';

    #[Label('Nama unit')]
    #[Required(message: 'Nama unit wajib diisi.')]
    #[Length(min: 2, max: 100)]
    public string $name = '';

    #[Label('Simbol')]
    #[Required(message: 'Simbol unit wajib diisi.')]
    #[Length(min: 1, max: 20)]
    public string $symbol = '';

    public function getFormName(): string
    {
        return 'unit';
    }

    public static function fromEntity(Unit $unit): self
    {
        $input = new self();
        $input->id = (string) $unit->id;
        $input->name = $unit->name;
        $input->symbol = $unit->symbol;

        return $input;
    }

    public function toEntity(): Unit
    {
        return new Unit(
            $this->id === '' ? null : (int) $this->id,
            trim($this->name),
            trim($this->symbol),
        );
    }
}
