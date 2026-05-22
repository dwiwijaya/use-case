<?php

declare(strict_types=1);

namespace App\Catalog\Item;

use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Label;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

use function trim;

final class ItemInput extends FormModel
{
    public string $id = '';

    #[Label('SKU')]
    #[Required(message: 'SKU wajib diisi.')]
    #[Length(min: 3, max: 50)]
    public string $sku = '';

    #[Label('Nama item')]
    #[Required(message: 'Nama item wajib diisi.')]
    #[Length(min: 2, max: 150)]
    public string $name = '';

    #[Label('Unit')]
    #[Required(message: 'Unit wajib dipilih.')]
    #[Integer(min: 1, notNumberMessage: 'Unit wajib dipilih.')]
    public string $unitId = '';

    public function getFormName(): string
    {
        return 'item';
    }

    public static function fromEntity(Item $item): self
    {
        $input = new self();
        $input->id = (string) $item->id;
        $input->sku = $item->sku;
        $input->name = $item->name;
        $input->unitId = (string) $item->unitId;

        return $input;
    }

    public function toEntity(): Item
    {
        return new Item(
            $this->id === '' ? null : (int) $this->id,
            trim($this->sku),
            trim($this->name),
            (int) $this->unitId,
        );
    }
}
