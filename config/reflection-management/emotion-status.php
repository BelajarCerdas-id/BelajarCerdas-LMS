<?php

return [

    [
        'label' => 'Senang',
        'value' => 'Senang',
        'category' => 'positive',
        'chart_color' => '#22C55E',
        'icon'  => 'fa-face-laugh-beam',

        'classes' => [

            // CARD
            'hover' => '
                hover:bg-green-50
                hover:border-green-200
            ',

            'checked' => '
                group-has-[:checked]:bg-green-50
                group-has-[:checked]:!border-green-400
            ',

            'ring' => '
                group-has-[:checked]:ring-4
                group-has-[:checked]:ring-green-100
            ',

            // ICON
            'icon_hover' => '
                group-hover:text-green-600
            ',

            'icon_checked' => '
                group-has-[:checked]:bg-green-100
                group-has-[:checked]:text-green-600
                group-has-[:checked]:!border-green-200
            ',

            // TEXT
            'text_hover' => '
                group-hover:text-green-700
            ',

            'text_checked' => '
                group-has-[:checked]:text-green-700
            ',

            // CHECK BADGE
            'check' => '
                bg-green-500
                text-white
            ',
        ],
    ],

    [
        'label' => 'Semangat',
        'value' => 'Semangat',
        'category' => 'positive',
        'chart_color' => '#3B82F6',
        'icon'  => 'fa-face-smile',

        'classes' => [

            'hover' => '
                hover:bg-blue-50
                hover:border-blue-200
            ',

            'checked' => '
                group-has-[:checked]:bg-blue-50
                group-has-[:checked]:!border-blue-400
            ',

            'ring' => '
                group-has-[:checked]:ring-4
                group-has-[:checked]:ring-blue-100
            ',

            'icon_hover' => '
                group-hover:text-blue-600
            ',

            'icon_checked' => '
                group-has-[:checked]:bg-blue-100
                group-has-[:checked]:text-blue-600
                group-has-[:checked]:!border-blue-200
            ',

            'text_hover' => '
                group-hover:text-blue-700
            ',

            'text_checked' => '
                group-has-[:checked]:text-blue-700
            ',

            'check' => '
                bg-blue-500
                text-white
            ',
        ],
    ],

    [
        'label' => 'Netral',
        'value' => 'Netral',
        'category' => 'neutral',
        'chart_color' => '#F59E0B',
        'icon'  => 'fa-face-meh',

        'classes' => [

            'hover' => '
                hover:bg-amber-50
                hover:border-amber-200
            ',

            'checked' => '
                group-has-[:checked]:bg-amber-50
                group-has-[:checked]:!border-amber-400
            ',

            'ring' => '
                group-has-[:checked]:ring-4
                group-has-[:checked]:ring-amber-100
            ',

            'icon_hover' => '
                group-hover:text-amber-600
            ',

            'icon_checked' => '
                group-has-[:checked]:bg-amber-100
                group-has-[:checked]:text-amber-600
                group-has-[:checked]:!border-amber-200
            ',

            'text_hover' => '
                group-hover:text-amber-700
            ',

            'text_checked' => '
                group-has-[:checked]:text-amber-700
            ',

            'check' => '
                bg-amber-500
                text-white
            ',
        ],
    ],

    [
        'label' => 'Sedih',
        'value' => 'Sedih',
        'category' => 'attention',
        'chart_color' => '#F97316',
        'icon'  => 'fa-face-frown',

        'classes' => [

            'hover' => '
                hover:bg-orange-50
                hover:border-orange-200
            ',

            'checked' => '
                group-has-[:checked]:bg-orange-50
                group-has-[:checked]:!border-orange-400
            ',

            'ring' => '
                group-has-[:checked]:ring-4
                group-has-[:checked]:ring-orange-100
            ',

            'icon_hover' => '
                group-hover:text-orange-600
            ',

            'icon_checked' => '
                group-has-[:checked]:bg-orange-100
                group-has-[:checked]:text-orange-600
                group-has-[:checked]:!border-orange-200
            ',

            'text_hover' => '
                group-hover:text-orange-700
            ',

            'text_checked' => '
                group-has-[:checked]:text-orange-700
            ',

            'check' => '
                bg-orange-500
                text-white
            ',
        ],
    ],

    [
        'label' => 'Stress',
        'value' => 'Stress',
        'category' => 'attention',
        'chart_color' => '#F43F5E',
        'icon'  => 'fa-face-tired',

        'classes' => [

            'hover' => '
                hover:bg-rose-50
                hover:border-rose-200
            ',

            'checked' => '
                group-has-[:checked]:bg-rose-50
                group-has-[:checked]:!border-rose-400
            ',

            'ring' => '
                group-has-[:checked]:ring-4
                group-has-[:checked]:ring-rose-100
            ',

            'icon_hover' => '
                group-hover:text-rose-600
            ',

            'icon_checked' => '
                group-has-[:checked]:bg-rose-100
                group-has-[:checked]:text-rose-600
                group-has-[:checked]:!border-rose-200
            ',

            'text_hover' => '
                group-hover:text-rose-700
            ',

            'text_checked' => '
                group-has-[:checked]:text-rose-700
            ',

            'check' => '
                bg-rose-500
                text-white
            ',
        ],
    ],

];