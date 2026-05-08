<?php

namespace App\Filament\Resources\Shop\Orders\Pages;

use App\Filament\Resources\Shop\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use RalphJSmit\Filament\AutoTranslator\Filament\Resources\Resource\Pages\EditRecord;
use Filament\Schemas\Schema;
use RalphJSmit\Filament\Activitylog\Filament\Infolists\Components\Timeline;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getActions(): array
    {
        return [
            DeleteAction::make(),
            RestoreAction::make(),
            ForceDeleteAction::make(),
        ];
    }

    public function aside(Schema $schema): Schema
    {
        return $schema->components([
            Timeline::make()
                ->withRelations(['payments'])
                ->searchable()
                ->maxHeight('400px')
                ->itemIcon('updated', 'heroicon-o-pencil-square')
                ->itemIcon('created', 'heroicon-o-plus-circle')
                ->itemIconColor('created', 'success')
                ->itemIconColor('updated', 'warning'),
        ]);
    }
}
