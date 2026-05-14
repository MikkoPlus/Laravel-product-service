<?php

namespace App\OpenApi;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'ProductService API',
    description: 'API for authentication, users, categories, and products.'
)]
#[OA\Server(url: '/')]
#[OA\Tag(name: 'Auth')]
#[OA\Tag(name: 'Users')]
#[OA\Tag(name: 'Products')]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'Token',
    description: 'Use token from /api/login as Bearer token.'
)]
#[OA\Schema(
    schema: 'Category',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Electronics'),
    ]
)]
#[OA\Schema(
    schema: 'User',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'john@example.com'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
    ]
)]
#[OA\Schema(
    schema: 'Product',
    type: 'object',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'iPhone 15'),
        new OA\Property(property: 'price', type: 'string', example: '999.99'),
        new OA\Property(property: 'category_id', type: 'integer', example: 1),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
        new OA\Property(property: 'category', ref: '#/components/schemas/Category'),
    ]
)]
#[OA\Schema(
    schema: 'HttpError401',
    description: 'Sanctum / auth middleware — JSON ответ Laravel.',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Unauthenticated.'),
    ]
)]
#[OA\Schema(
    schema: 'HttpError403',
    description: 'Отказ авторизации FormRequest `authorize()` или политики.',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'This action is unauthorized.'),
    ]
)]
#[OA\Schema(
    schema: 'HttpError404',
    description: 'Модель не найдена (implicit binding).',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'No query results for model [App\\Models\\Product] 999.'),
    ]
)]
#[OA\Schema(
    schema: 'ValidationError422',
    description: 'Ошибка валидации (`Illuminate\\Validation\\ValidationException`).',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            description: 'Ключ — имя поля; значение — массив сообщений об ошибках.',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string')
            ),
            example: ['email' => ['These credentials do not match our records.']]
        ),
    ]
)]
class ApiDocumentation
{
    #[OA\Post(
        path: '/api/login',
        tags: ['Auth'],
        summary: 'Login and issue Sanctum token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'test@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password'),
                    new OA\Property(property: 'device_name', type: 'string', example: 'postman'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authenticated',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                        new OA\Property(property: 'token', type: 'string', example: '1|long-sanctum-token'),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации или неверные учётные данные (ValidationException)',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError422')
            ),
        ]
    )]
    public function login(): void {}

    #[OA\Post(
        path: '/api/logout',
        tags: ['Auth'],
        summary: 'Logout current token',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Logged out',
                content: new OA\JsonContent(properties: [
                    new OA\Property(property: 'message', type: 'string', example: 'Logged out'),
                ])
            ),
            new OA\Response(
                response: 401,
                description: 'Нет или неверный Bearer-токен',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError401')
            ),
            new OA\Response(
                response: 403,
                description: 'Запрет в `authorize()` запроса',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError403')
            ),
        ]
    )]
    public function logout(): void {}

    #[OA\Get(
        path: '/api/user',
        tags: ['Users'],
        summary: 'Get current authenticated user',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Current user',
                content: new OA\JsonContent(ref: '#/components/schemas/User')
            ),
            new OA\Response(
                response: 401,
                description: 'Нет или неверный Bearer-токен',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError401')
            ),
            new OA\Response(
                response: 403,
                description: 'Запрет в `authorize()` запроса',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError403')
            ),
        ]
    )]
    public function me(): void {}

    #[OA\Post(
        path: '/api/users',
        tags: ['Users'],
        summary: 'Create user',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Alice'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'alice@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'User created', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError422')
            ),
            new OA\Response(
                response: 401,
                description: 'Нет или неверный Bearer-токен',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError401')
            ),
            new OA\Response(
                response: 403,
                description: 'Запрет в `authorize()` запроса',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError403')
            ),
        ]
    )]
    public function createUser(): void {}

    #[OA\Put(
        path: '/api/users/{user}',
        tags: ['Users'],
        summary: 'Update user',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Alice Updated'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'alice.updated@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'new-password'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'new-password'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'User updated', content: new OA\JsonContent(ref: '#/components/schemas/User')),
            new OA\Response(
                response: 404,
                description: 'Пользователь не найден',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError404')
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError422')
            ),
            new OA\Response(
                response: 401,
                description: 'Нет или неверный Bearer-токен',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError401')
            ),
            new OA\Response(
                response: 403,
                description: 'Запрет в `authorize()` запроса',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError403')
            ),
        ]
    )]
    public function updateUser(): void {}

    #[OA\Delete(
        path: '/api/users/{user}',
        tags: ['Users'],
        summary: 'Delete user',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'User deleted'),
            new OA\Response(
                response: 404,
                description: 'Пользователь не найден',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError404')
            ),
            new OA\Response(
                response: 401,
                description: 'Нет или неверный Bearer-токен',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError401')
            ),
            new OA\Response(
                response: 403,
                description: 'Запрет в `authorize()` запроса',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError403')
            ),
        ]
    )]
    public function deleteUser(): void {}

    #[OA\Get(
        path: '/api/products',
        tags: ['Products'],
        summary: 'List products with filtering, sorting and pagination',
        parameters: [
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'price_min', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'price_max', in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float')),
            new OA\Parameter(name: 'name', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'sort_by', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['price', 'created_at'])),
            new OA\Parameter(name: 'sort_direction', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['asc', 'desc'])),
            new OA\Parameter(name: 'page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated products',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/Product')),
                        new OA\Property(property: 'current_page', type: 'integer', example: 1),
                        new OA\Property(property: 'per_page', type: 'integer', example: 15),
                        new OA\Property(property: 'total', type: 'integer', example: 80),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации query-параметров',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError422')
            ),
        ]
    )]
    public function products(): void {}

    #[OA\Post(
        path: '/api/products',
        tags: ['Products'],
        summary: 'Create product',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'price', 'category_id'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'MacBook Air'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 1299.99),
                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Product created', content: new OA\JsonContent(ref: '#/components/schemas/Product')),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError422')
            ),
            new OA\Response(
                response: 401,
                description: 'Нет или неверный Bearer-токен',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError401')
            ),
            new OA\Response(
                response: 403,
                description: 'Запрет в `authorize()` запроса',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError403')
            ),
        ]
    )]
    public function createProduct(): void {}

    #[OA\Put(
        path: '/api/products/{product}',
        tags: ['Products'],
        summary: 'Update product',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'MacBook Air M3'),
                    new OA\Property(property: 'price', type: 'number', format: 'float', example: 1399.99),
                    new OA\Property(property: 'category_id', type: 'integer', example: 1),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Product updated', content: new OA\JsonContent(ref: '#/components/schemas/Product')),
            new OA\Response(
                response: 404,
                description: 'Товар не найден',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError404')
            ),
            new OA\Response(
                response: 422,
                description: 'Ошибка валидации',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationError422')
            ),
            new OA\Response(
                response: 401,
                description: 'Нет или неверный Bearer-токен',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError401')
            ),
            new OA\Response(
                response: 403,
                description: 'Запрет в `authorize()` запроса',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError403')
            ),
        ]
    )]
    public function updateProduct(): void {}

    #[OA\Delete(
        path: '/api/products/{product}',
        tags: ['Products'],
        summary: 'Delete product',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'product', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Product deleted'),
            new OA\Response(
                response: 404,
                description: 'Товар не найден',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError404')
            ),
            new OA\Response(
                response: 401,
                description: 'Нет или неверный Bearer-токен',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError401')
            ),
            new OA\Response(
                response: 403,
                description: 'Запрет в `authorize()` запроса',
                content: new OA\JsonContent(ref: '#/components/schemas/HttpError403')
            ),
        ]
    )]
    public function deleteProduct(): void {}
}
