<?php

use Knuckles\Scribe\Extracting\Strategies;
use Knuckles\Scribe\Config\Defaults;
use Knuckles\Scribe\Config\AuthIn;
use function Knuckles\Scribe\Config\{removeStrategies, configureStrategy};

// Complete Scribe API Documentation Configuration
// See https://scribe.knuckles.wtf/laravel/reference/config for all options

return [
    // The HTML <title> for the generated documentation
    'title' => 'Marketplace Nigeria API Documentation',

    // A short description of your API
    'description' => 'Complete REST API documentation for Marketplace Nigeria platform. This API allows you to manage adverts, users, messages, payments, and more.',

    // The base URL displayed in the docs
    // This will automatically use APP_URL from .env (local or production)
    'base_url' => env('APP_URL', 'https://www.marketplace.ng'),

    // API version
    'version' => '3.0',

    // Theme
    'theme' => 'default',

    // Type of documentation output
    // 'static' generates HTML files in public/docs
    // 'laravel' generates Blade views with Laravel routing
    'type' => 'static',

    'static' => [
        'output_path' => 'public/docs',
    ],

    'laravel' => [
        'add_routes' => true,
        'docs_url' => '/docs',
        'assets_directory' => null,
        'middleware' => [],
    ],

    // Routes to include in documentation
    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],
                'domains' => ['*'],
            ],
            'include' => [],
            'exclude' => [
                // Exclude internal/debug routes if any
            ],
            'apply' => [
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ],
        ],
    ],

    // Authentication configuration
    'auth' => [
        // Set to true since API uses authentication
        'enabled' => true,

        // Set to false since not all endpoints require auth
        'default' => false,

        // Auth method: bearer token (Laravel Sanctum)
        'in' => AuthIn::BEARER->value,

        // Parameter name
        'name' => 'token',

        // Token value for testing (from .env)
        'use_value' => env('SCRIBE_AUTH_KEY'),

        // Placeholder shown in docs
        'placeholder' => '{YOUR_AUTH_TOKEN}',

        // Additional auth info for users
        'extra_info' => 'You can obtain your authentication token by logging in through the `/api/login` endpoint. The token should be included in the Authorization header as a Bearer token for all authenticated requests.',
    ],

    // Try It Out feature
    'try_it_out' => [
        // Enable the "Try It Out" button
        'enabled' => true,

        // Base URL for testing (uses APP_URL from environment)
        'base_url' => env('APP_URL', 'https://www.marketplace.ng'),

        // Use CSRF tokens for Sanctum
        'use_csrf' => false,

        'csrf_url' => '/sanctum/csrf-cookie',
    ],

    // Example request languages
    'example_languages' => [
        'bash',
        'javascript',
        'php',
        'python',
    ],

    // Generate Postman collection
    'postman' => [
        'enabled' => true,
        'overrides' => [
            'info.version' => '3.0',
        ],
    ],

    // Generate OpenAPI spec
    'openapi' => [
        'enabled' => true,
        'version' => '3.0.3',
        'overrides' => [
            'info.version' => '3.0',
        ],
    ],

    // Group configuration
    'groups' => [
        'default' => 'Endpoints',
        'order' => [
            'Authentication',
            'Adverts',
            'User Management',
            'Messages',
            'Payments',
            'Search',
            'Locations',
        ],
    ],

    // Logo (set to false or provide path)
    'logo' => false,

    // Last updated format
    'last_updated' => 'Last updated: {date:F j, Y}',

    // Example requests configuration
    'examples' => [
        'faker_seed' => null,
        'models_source' => ['factoryCreate', 'factoryMake', 'databaseFirst'],
    ],

    // Strategies for extracting API information
    // Using only strategies available in Scribe 5.6.0
    'strategies' => [
        'metadata' => [
            Strategies\Metadata\GetFromDocBlocks::class,
        ],
        'urlParameters' => [
            Strategies\UrlParameters\GetFromLaravelAPI::class,
            Strategies\UrlParameters\GetFromUrlParamAttribute::class,
        ],
        'queryParameters' => [
            Strategies\QueryParameters\GetFromFormRequest::class,
            Strategies\QueryParameters\GetFromInlineValidator::class,
            Strategies\QueryParameters\GetFromQueryParamAttribute::class,
        ],
        'headers' => [
            Strategies\Headers\GetFromHeaderAttribute::class,
            Strategies\Headers\GetFromHeaderTag::class,
        ],
        'bodyParameters' => [
            Strategies\BodyParameters\GetFromFormRequest::class,
            Strategies\BodyParameters\GetFromInlineValidator::class,
            Strategies\BodyParameters\GetFromBodyParamAttribute::class,
        ],
        'responses' => [
            Strategies\Responses\UseResponseAttributes::class,
            Strategies\Responses\UseApiResourceTags::class,
            Strategies\Responses\UseTransformerTags::class,
            Strategies\Responses\UseResponseTag::class,
            Strategies\Responses\UseResponseFileTag::class,
        ],
        'responseFields' => [
            Strategies\ResponseFields\GetFromResponseFieldAttribute::class,
        ],
    ],

    // Intro text
    'intro_text' => <<<INTRO
        Welcome to the Marketplace Nigeria API documentation. This documentation provides comprehensive information about all available endpoints, authentication methods, and request/response formats.

        <aside>As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).
        You can switch the language used with the tabs at the top right (or from the nav menu at the top left on mobile).</aside>

        ## Base URL

        The base URL for all API requests depends on your environment:
        - **Production**: `https://www.marketplace.ng/api`
        - **Local Development**: `http://127.0.0.1:8030/api`

        ## Authentication

        This API uses Laravel Sanctum for authentication. Most endpoints require a Bearer token in the Authorization header.

        To authenticate:
        1. Login via `/api/login` to receive an access token
        2. Include the token in subsequent requests: `Authorization: Bearer {your-token}`
    INTRO,
];
