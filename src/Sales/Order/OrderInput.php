<?php

declare(strict_types=1);

namespace App\Sales\Order;

use Yiisoft\FormModel\Attribute\Safe;
use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Label;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Length;
use Yiisoft\Validator\Rule\Required;

use function array_fill;
use function count;
use function is_array;
use function trim;

final class OrderInput extends FormModel
{
    #[Label('Customer')]
    #[Required(message: 'Customer wajib diisi.')]
    #[Length(min: 2, max: 150)]
    public string $customerName = '';

    #[Label('Lokasi stok')]
    #[Required(message: 'Lokasi wajib dipilih.')]
    #[Integer(min: 1, notNumberMessage: 'Lokasi wajib dipilih.')]
    public string $locationId = '';

    #[Label('Catatan')]
    #[Length(max: 1000)]
    public string $notes = '';

    /**
     * @var list<array{item_id:string,quantity:string}>
     */
    #[Safe]
    public array $lines = [];

    public function __construct()
    {
        $this->lines = self::blankLines();
    }

    public function getFormName(): string
    {
        return 'order';
    }

    /**
     * @return list<array{item_id:string,quantity:string}>
     */
    public static function blankLines(): array
    {
        return array_fill(0, 5, ['item_id' => '', 'quantity' => '']);
    }

    public static function withLocationId(?int $locationId): self
    {
        $input = new self();
        $input->locationId = $locationId === null ? '' : (string) $locationId;

        return $input;
    }

    public function getPropertyLabels(): array
    {
        $labels = [];
        foreach ($this->lines as $index => $_line) {
            $position = $index + 1;
            $labels["lines[$index][item_id]"] = "Item baris $position";
            $labels["lines[$index][quantity]"] = "Quantity baris $position";
        }

        return $labels;
    }

    public function validateLines(): void
    {
        $lines = $this->normalizeLines();

        foreach ($lines as $index => $line) {
            $hasItem = $line['item_id'] !== '';
            $hasQuantity = $line['quantity'] !== '';

            if (!$hasItem && !$hasQuantity) {
                continue;
            }

            if (!$hasItem || !$hasQuantity) {
                $this->addError('Item dan quantity harus diisi berpasangan.', ['lines', $index]);
                continue;
            }

            if ((int) $line['item_id'] <= 0) {
                $this->addError('Item order tidak valid.', ['lines', $index, 'item_id']);
            }

            if ((int) $line['quantity'] <= 0) {
                $this->addError('Quantity order harus lebih dari nol.', ['lines', $index, 'quantity']);
            }
        }

        if (count($this->collectLines()) === 0) {
            $this->addError('Order minimal harus punya satu item.');
        }
    }

    /**
     * @return list<OrderLine>
     */
    public function collectLines(): array
    {
        $result = [];
        foreach ($this->normalizeLines() as $line) {
            if ($line['item_id'] === '' || $line['quantity'] === '') {
                continue;
            }

            if ((int) $line['item_id'] <= 0 || (int) $line['quantity'] <= 0) {
                continue;
            }

            $result[] = new OrderLine((int) $line['item_id'], (int) $line['quantity']);
        }

        return $result;
    }

    /**
     * @return list<array{item_id:string,quantity:string}>
     */
    private function normalizeLines(): array
    {
        $normalized = self::blankLines();

        foreach ($this->lines as $index => $line) {
            if (!isset($normalized[$index]) || !is_array($line)) {
                continue;
            }

            $normalized[$index] = [
                'item_id' => trim((string) ($line['item_id'] ?? '')),
                'quantity' => trim((string) ($line['quantity'] ?? '')),
            ];
        }

        $this->lines = $normalized;

        return $normalized;
    }
}
