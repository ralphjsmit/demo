<?php

return [
    'navigation-label' => 'Producten',
    'model-label' => 'product',
    'plural-model-label' => 'producten',

    'form' => [
        'components' => [
            'name' => [
                'label' => 'Naam',
            ],
            'slug' => [
                'label' => 'Slug',
            ],
            'description' => [
                'label' => 'Beschrijving',
            ],
            'media' => [
                'label' => 'Afbeeldingen',
            ],
            'price' => [
                'label' => 'Prijs',
            ],
            'old_price' => [
                'label' => 'Oude prijs',
            ],
            'cost' => [
                'label' => 'Kostprijs',
            ],
            'sku' => [
                'label' => 'Artikelnummer',
            ],
            'barcode' => [
                'label' => 'Barcode',
            ],
            'qty' => [
                'label' => 'Hoeveelheid',
            ],
            'security_stock' => [
                'label' => 'Veiligheidsvoorraad',
            ],
            'is_visible' => [
                'label' => 'Zichtbaar',
            ],
            'published_at' => [
                'label' => 'Gepubliceerd op',
            ],
            'shop_brand_id' => [
                'label' => 'Merk',
            ],
            'categories' => [
                'label' => 'Categorieën',
            ],
            'backorder' => [
                'label' => 'Nabestelling',
            ],
            'requires_shipping' => [
                'label' => 'Verzending vereist',
            ],
            'featured' => [
                'label' => 'Uitgelicht',
            ],
        ],
    ],

    'table' => [
        'columns' => [
            'name' => [
                'label' => 'Naam',
            ],
            'brand.name' => [
                'label' => 'Merk',
            ],
            'is_visible' => [
                'label' => 'Zichtbaar',
            ],
            'price' => [
                'label' => 'Prijs',
            ],
            'sku' => [
                'label' => 'Artikelnummer',
            ],
            'qty' => [
                'label' => 'Voorraad',
            ],
            'security_stock' => [
                'label' => 'Veiligheidsvoorraad',
            ],
            'published_at' => [
                'label' => 'Gepubliceerd op',
            ],
        ],
    ],

    'pages' => [
        'list-products' => [
            'title' => 'Producten',
        ],
        'create-product' => [
            'title' => 'Product aanmaken',
        ],
        'edit-product' => [
            'title' => 'Product bewerken',
        ],
    ],
];
