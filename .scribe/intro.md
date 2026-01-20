# Introduction

Complete REST API documentation for Marketplace Nigeria platform. This API allows you to manage adverts, users, messages, payments, and more.

<aside>
    <strong>Base URL</strong>: <code>http://127.0.0.1:8030</code>
</aside>

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

