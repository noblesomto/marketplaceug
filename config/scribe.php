<?php

use Knuckles\Scribe\Extracting\Strategies;
use Knuckles\Scribe\Config\Defaults;
use Knuckles\Scribe\Config\AuthIn;
use function Knuckles\Scribe\Config\{removeStrategies, configureStrategy};

// Only the most common configs are shown. See the https://scribe.knuckles.wtf/laravel/reference/config for all.

return [
    'theme' => 'default',

    // Your API info
    'title' => 'Marketplace Nigeria API Documentation',
    'description' => 'Complete API documentation for Marketplace Nigeria platform',
    'base_url' => env('APP_URL', 'https://www.marketplace.ng'),

    // API version
    'version' => '3.0',

    // Routes to document
    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],
                'domains' => ['*'],
            ],
            'include' => [],
            'exclude' => [],
            'apply' => [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ],
        ],
    ],

    // Authentication
    'auth' => [
        'enabled' => true,
        'default' => true,
        'in' => 'bearer',
        'name' => 'token',
        'use_value' => env('SCRIBE_AUTH_KEY'),
        'placeholder' => 'YOUR_TOKEN_HERE',

        'method' => env('DOCS_AUTH_METHOD', 'token'),
        'token' => env('DOCS_ACCESS_TOKEN'),
        'users' => env('DOCS_AUTH_USERS'),
        'skip_local' => env('DOCS_SKIP_LOCAL_AUTH', true),
    ],

    // Try it out feature
    'try_it_out' => [
        'enabled' => true,
        'base_url' => env('APP_URL', 'https://www.marketplace.ng'),
    ],

    // Static output or Laravel route
    'type' => 'static',  // Change from 'laravel'
    'static' => [
        'output_path' => 'public/docs',
    ],

    // Example requests
    'examples' => [
        'faker_seed' => null,
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],
];
