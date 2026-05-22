<?php

declare(strict_types=1);

namespace App\Inventory\Stock;

use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Label;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Required;

final class StockInput extends FormModel
{
    #[Label('Lokasi')]
    #[Required(message: 'Lokasi wajib dipilih.')]
    #[Integer(min: 1, notNumberMessage: 'Lokasi wajib dipilih.')]
    public string $locationId = '';

    #[Label('Item')]
    #[Required(message: 'Item wajib dipilih.')]
    #[Integer(min: 1, notNumberMessage: 'Item wajib dipilih.')]
    public string $itemId = '';

    #[Label('Quantity')]
    #[Required(message: 'Quantity stok wajib diisi.')]
    #[Integer(min: 0, notNumberMessage: 'Quantity stok harus berupa angka bulat.')]
    public string $quantity = '0';

    public function getFormName(): string
    {
        return 'stock';
    }

    public static function withLocationId(?int $locationId): self
    {
        $input = new self();
        $input->locationId = $locationId === null ? '' : (string) $locationId;

        return $input;
    }

    public function toEntity(): Stock
    {
        return new Stock(
            (int) $this->locationId,
            (int) $this->itemId,
            (int) $this->quantity,
        );
    }
}
