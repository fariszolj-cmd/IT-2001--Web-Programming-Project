# FarisCommerce (IT-2001 Web Programming Project)

A student e-commerce storefront prototype with a PHP/MySQL CRUD API.

## Backend API

Install PHP 8.1+ with PDO MySQL and Composer, import `backend/database.sql`, and copy `backend/config/config.example.php` to `backend/config/config.local.php` with your local database credentials. Configure `api_tokens.admin` for full CRUD and `api_tokens.reader` for read-only access. Keep these app tokens separate from `external_api.olx_api_token`; never commit or share credentials.

From the repository root:

```sh
composer install
php -S 127.0.0.1:8000 -t backend backend/index.php
```

Open [http://127.0.0.1:8000/docs](http://127.0.0.1:8000/docs) for Swagger UI or [http://127.0.0.1:8000/openapi.json](http://127.0.0.1:8000/openapi.json) for the OpenAPI specification. Use Swagger's **Authorize** button and enter the token value without the `Bearer ` prefix. The API CRUD resources are `users`, `categories`, `products`, `carts`, `orders`, `payments`, and `order_items`.

External search: `GET /api/external-search?q=phone` uses the public DummyJSON product search. To search one OLX seller's active listings, use `GET /api/external-search?q=phone&source=olx&username=SELLER&page=1`; OLX's documented API only exposes that seller's paginated listings, not global keyword search. Its API token is optional and is configured locally.

## Test the milestone

1. Start the server and open `/docs`.
2. In Swagger, select **Authorize** and enter `api_tokens.admin` from your local config. Use **Try it out** on each entity (`users`, `categories`, `products`, `carts`, `orders`, `payments`, `order_items`). Test collection `GET`/`POST` and record `GET`/`PUT`/`PATCH`/`DELETE`. Create related records before foreign-key records, or use the included seed IDs.
3. Check business validation by submitting a product with a negative price; it should return JSON with HTTP `400`. A valid create should return `201`, reads/updates `200`, missing records `404`, and an unavailable database `500`.
4. To verify authorization, run a CRUD read without a token (401), then authenticate in Swagger with `api_tokens.reader` and try a `POST` (403). The reader token can still make `GET` requests.
5. Test external search in the browser or PowerShell:

   ```powershell
   Invoke-RestMethod "http://127.0.0.1:8000/api/external-search?q=phone"
   ```

   Expected: HTTP `200` with `source`, `query`, `total`, and an `items` array. An empty `q` returns `400`. OLX with no configured token returns `401`; an invalid or unauthorized OLX token can return `401` or `403`. External service timeouts/errors return JSON with HTTP `500`.

## Project handoff

See [CLAUDE.md](./CLAUDE.md) for the repository map, actual implementation status, database schema, API routes, local setup, known gaps, and guidance for continuing development with an AI coding assistant.

The storefront is currently a static prototype and is not connected to the API. Read the handoff before treating account, cart, checkout, or payment interactions as implemented backend features.
