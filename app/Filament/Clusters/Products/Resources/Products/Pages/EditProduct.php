<?php

namespace App\Filament\Clusters\Products\Resources\Products\Pages;

use App\Filament\Clusters\Products\Resources\Products\ProductResource;
use Filament\Actions\DeleteAction;
use RalphJSmit\Filament\AutoTranslator\Filament\Resources\Resource\Pages\EditRecord;
use Filament\Schemas\Schema;
use RalphJSmit\Filament\Activitylog\Filament\Infolists\Components\Timeline;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public function aside(Schema $schema): Schema
    {
        return $schema->components([
            Timeline::make()
                ->compact()
                ->searchable()
                ->maxHeight('300px'),
        ]);
    }
}
