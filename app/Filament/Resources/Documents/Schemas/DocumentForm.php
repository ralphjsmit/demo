<?php

namespace App\Filament\Resources\Documents\Schemas;

use App\Enums\DocumentType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class DocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ToggleButtons::make('type')
                    ->options(DocumentType::class)
                    ->inline()
                    ->required(),
                Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload()
                    ->visibleJs(function () {
                        $documentTypeCustomer = DocumentType::Customer;

                        return <<<JS
                            \$get('type') === '{$documentTypeCustomer->value}'
                        JS;
                    })
                    ->dehydrated(function (Get $get) {
                        $type = $get('type');

                        ray($type);

                        $documentTypeCustomer = DocumentType::Customer;

                        $isDehydrated = $type === $documentTypeCustomer;

                        ray('Dehydrated customer id: ' . $isDehydrated ? 'true' : 'false');

                        return $isDehydrated;
                    })
                    ->requiredUnless('type', DocumentType::Customer->value),
            ]);
    }
}
