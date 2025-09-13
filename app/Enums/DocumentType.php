<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DocumentType: string implements HasLabel
{
    case Tenant = 'tenant';

    case Customer = 'customer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Tenant => 'Global document',
            self::Customer => 'Customer-specific document',
        };
    }
}
