<?php

namespace Refaltor\ProtectYourSpawn\Forms\Elements;

use pocketmine\form\FormValidationException;

class Label extends Element
{

    public function getType(): string
    {
        return "label";
    }

    public function serializeElementData(): array
    {
        return [];
    }

    public function validate($value): void
    {
        if ($value !== null) {
            throw new FormValidationException("Expected null, got " . gettype($value));
        }
    }
}