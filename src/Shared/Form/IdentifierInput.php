<?php

declare(strict_types=1);

namespace App\Shared\Form;

use Yiisoft\FormModel\FormModel;
use Yiisoft\Validator\Label;
use Yiisoft\Validator\Rule\Integer;
use Yiisoft\Validator\Rule\Required;

final class IdentifierInput extends FormModel
{
    #[Label('ID')]
    #[Required(message: 'ID wajib diisi.')]
    #[Integer(min: 1, notNumberMessage: 'ID tidak valid.')]
    public string $id = '';

    public function toId(): int
    {
        return (int) $this->id;
    }
}
