# Product Catalog API (Laravel + Redis + Sail)

REST API: каталог товаров с фильтрацией, пагинацией, кэшированием списка, аутентификацией Sanctum, пользователями и Swagger-документацией.

## Стек

- PHP 8.x, Laravel 13
- MySQL (через Sail), Redis для `CACHE_STORE` в продакшене
- Laravel Sanctum (Bearer token)
- `darkaonline/l5-swagger` — OpenAPI / Swagger UI

## Возможности

### Модели

- **Category** — `id`, `name`
- **Product** — `id`, `name`, `price`, `category_id`, `created_at`, `updated_at`; связь `belongsTo(Category)`
- **User** — стандартная модель с `HasApiTokens`

### Товары (`/api/products`)

| Метод | Путь | Доступ | Описание |
|--------|------|--------|----------|
| `GET` | `/api/products` | публичный | Список: фильтры `category_id`, `price_min`, `price_max`, `name` (LIKE), сортировка `sort_by` (`price` \| `created_at`), `sort_direction` (`asc` \| `desc`), пагинация **15** на страницу |
| `POST` | `/api/products` | `auth:sanctum` | Создание (`name`, `price`, `category_id`) |
| `PUT` | `/api/products/{product}` | `auth:sanctum` | Обновление |
| `DELETE` | `/api/products/{product}` | `auth:sanctum` | Удаление |

Валидация списка — `IndexProductRequest`; создание/обновление — `StoreProductRequest` / `UpdateProductRequest`. Бизнес-логика в **`App\Services\ProductService`**.

### Кэш списка товаров

- **`App\Services\ProductCacheService`**
  - **`rememberList(array $filters, Closure $resolver, int $ttlSeconds = 300)`** — кладёт в кэш результат пагинации; ключ строится из **отсортированных** query-параметров и **версии** списка (`md5(json_encode($filters))` после `ksort`).
  - **`invalidateList()`** — инкрементирует версию (`products:list:version`); старые ключи с прежней версией перестают использоваться (TTL по-прежнему 5 минут для «осиротевших» ключей).
  - **`listCacheVersion()`** — текущая версия (по умолчанию `1`, если ключ ещё не создавали).
- Инвалидация вызывается из **`ProductService`** после `create` / `update` / `delete` товара.

### Аутентификация

| Метод | Путь | Доступ | Описание |
|--------|------|--------|----------|
| `POST` | `/api/login` | публичный | `email`, `password`, опционально `device_name` — ответ: `user`, `token` |
| `POST` | `/api/logout` | `auth:sanctum` | Удаляет текущий токен |
| `GET` | `/api/user` | `auth:sanctum` | Текущий пользователь |

Логика в **`App\Services\AuthService`**, валидация — `LoginRequest` / `LogoutRequest`.

### Пользователи

| Метод | Путь | Доступ | Описание |
|--------|------|--------|----------|
| `POST` | `/api/users` | публичный | Регистрация (`name`, `email`, `password`, `password_confirmation`) |
| `PUT` | `/api/users/{user}` | `auth:sanctum` | Обновление |
| `DELETE` | `/api/users/{user}` | `auth:sanctum` | Удаление |

**`App\Services\UserService`**, запросы — `StoreUserRequest` / `UpdateUserRequest`.

### Swagger

- UI: **`GET /docs`**
- JSON: **`GET /api/documentation`**
- Генерация: `php artisan l5-swagger:generate`
- Описание эндпоинтов и схем ошибок: `app/OpenApi/ApiDocumentation.php`

### Сиды и фабрики

- `Database\Seeders\DatabaseSeeder` — `UserSeeder`, `CategorySeeder`, `ProductSeeder`
- Фабрики: `UserFactory`, `CategoryFactory`, `ProductFactory`
- Демо-пользователь после сида: `test@example.com` / пароль **`password`** (см. `UserSeeder`)

```bash
php artisan migrate:fresh --seed
```

## Запуск (Docker + Sail)

```bash
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
```

Приложение и API обычно на `http://localhost` (порт см. `docker-compose` / Sail).

## Локально без Docker

```bash
composer install
cp .env.example .env
php artisan key:generate
```

В `.env` укажите доступный MySQL и **`CACHE_STORE=redis`** (или `array` / `database` для разработки без Redis). Для Sail в шаблоне `.env.example` задан `DB_HOST=mysql` — с хоста без Docker замените на `127.0.0.1` и свои креды.

```bash
php artisan migrate --seed
php artisan serve
```

## Тесты

Используется SQLite **in-memory** и драйвер кэша **`array`** (см. `phpunit.xml`).

```bash
php artisan test
# или
./vendor/bin/sail test
```

### Кэш

- **`tests/Unit/ProductCacheServiceTest`** — поведение сервиса кэша: один вызов резолвера при повторном `rememberList` с теми же фильтрами; разные фильтры — разные записи; инкремент версии после `invalidateList`; стабильность ключа при разном порядке ключей в массиве фильтров; версия по умолчанию.
- **`tests/Feature/ProductCacheTest`** — HTTP: инвалидация после `POST` / `PUT` / `DELETE` товара; два одинаковых `GET /api/products` дают идентичный JSON до мутаций.

## Полезные команды

```bash
php artisan route:list --path=api
php artisan l5-swagger:generate
```
