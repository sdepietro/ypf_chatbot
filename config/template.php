<?php

return [
    'title' => env('APP_NAME', 'YPF Chat Station'),

    'template_menu' => [
        [
            'text' => 'Dashboard',
            'url' => 'dashboard.index',
            'icon' => 'fas fa-chart-line',
            'can' => null,
        ],
        [
            'text' => 'Chat',
            'url' => 'chat.index',
            'icon' => 'fas fa-comments',
            'can' => null,
        ],
        [
            'text' => 'Agentes',
            'url' => 'agents.index',
            'icon' => 'fas fa-robot',
            'can' => null,
        ],
        [
            'text' => 'Configuraciones',
            'url' => 'configs.index',
            'icon' => 'fas fa-cog',
            'can' => null,
        ],
    ],

    'plugins' => [
        'fontawesome' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
                ],
            ],
        ],
        'toastr' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => 'https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js',
                ],
            ],
        ],
    ],
];
