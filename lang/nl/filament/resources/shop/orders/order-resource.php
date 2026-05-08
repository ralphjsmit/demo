<?php

return [
    'navigation-label' => 'Bestellingen',
    'model-label' => 'bestelling',
    'plural-model-label' => 'bestellingen',

    'form' => [
        'components' => [
            'number' => [
                'label' => 'Bestelnummer',
            ],
            'shop_customer_id' => [
                'label' => 'Klant',
            ],
            'status' => [
                'label' => 'Status',
            ],
            'currency' => [
                'label' => 'Valuta',
            ],
            'address' => [
                'label' => 'Adres',
            ],
            'notes' => [
                'label' => 'Notities',
            ],
            'items' => [
                'label' => 'Bestelregels',
            ],
            'shop_product_id' => [
                'label' => 'Product',
            ],
            'qty' => [
                'label' => 'Aantal',
            ],
            'unit_price' => [
                'label' => 'Stukprijs',
            ],
        ],
    ],

    'table' => [
        'columns' => [
            'number' => [
                'label' => 'Bestelnummer',
            ],
            'customer.name' => [
                'label' => 'Klant',
            ],
            'status' => [
                'label' => 'Status',
            ],
            'currency' => [
                'label' => 'Valuta',
            ],
            'total_price' => [
                'label' => 'Totaalprijs',
            ],
            'shipping_price' => [
                'label' => 'Verzendkosten',
            ],
            'created_at' => [
                'label' => 'Besteldatum',
            ],
        ],
    ],

    'pages' => [
        'list-orders' => [
            'title' => 'Bestellingen',
        ],
        'create-order' => [
            'title' => 'Bestelling aanmaken',
        ],
        'edit-order' => [
            'title' => 'Bestelling bewerken',
        ],
    ],
];
