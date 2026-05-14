# Product Catalog API (Laravel + Redis + Sail)

REST API для каталога товаров с фильтрацией, пагинацией и кэшированием списка.

## Реализовано

- `Product` с полями: `id`, `name`, `price`, `category_id`, `created_at`, `updated_at`.
- `Category` с полями: `id`, `name`.
- Связи: `Product belongsTo Category`, `Category hasMany Product`.
- `GET /api/products`:
  - фильтры `category_id`, `price_min`, `price_max`, `name` (LIKE),
  - сортировка по `price` или `created_at` (`sort_by`, `sort_direction`),
  - пагинация по 15 элементов.
- `POST /api/products` (только `auth:sanctum`):
  - валидация: `name` required, `price >= 0`, `category_id` exists,
  - создание товара.
- `PUT /api/products/{product}` и `DELETE /api/products/{product}` (только `auth:sanctum`).
- Кэширование списка через `ProductCacheService`:
  - TTL 5 минут,
  - инвалидация после создания/обновления/удаления.
- Тест: `ProductCacheTest` проверяет инвалидацию кэша после `POST`.

## Быстрый запуск (Docker + Sail)

```bash
cp .env.example .env
composer install
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
```

API будет доступен на `http://localhost`.

## Локальный запуск без Docker

```bash
php artisan serve
php artisan migrate
```

Для Redis-кэша укажите в `.env`: `CACHE_STORE=redis`.

## Тесты

```bash
./vendor/bin/sail test
```
