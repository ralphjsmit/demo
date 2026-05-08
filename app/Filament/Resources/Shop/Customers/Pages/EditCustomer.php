<?php

namespace App\Filament\Resources\Shop\Customers\Pages;

use App\Filament\Resources\Shop\Customers\CustomerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use RalphJSmit\Filament\Activitylog\Filament\Infolists\Components\Timeline;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

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
                ->withRelations(['orders'])
                ->searchable(),
        ]);
    }
}
