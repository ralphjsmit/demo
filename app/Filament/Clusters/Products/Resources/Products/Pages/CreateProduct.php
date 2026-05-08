<?php

namespace App\Filament\Clusters\Products\Resources\Products\Pages;

use App\Filament\Clusters\Products\Resources\Products\ProductResource;
use RalphJSmit\Filament\AutoTranslator\Filament\Resources\Resource\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
