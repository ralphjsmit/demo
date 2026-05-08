<?php

namespace App\Filament\Clusters\Products;

use BackedEnum;
use RalphJSmit\Filament\AutoTranslator\Filament\Clusters\Cluster;
use UnitEnum;

class ProductsCluster extends Cluster
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-squares-2x2';

    protected static string | UnitEnum | null $navigationGroup = 'Shop';

    protected static ?int $navigationSort = 0;

    protected static ?string $slug = 'shop/products';
}
