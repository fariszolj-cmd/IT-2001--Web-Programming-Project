# FarisCommerce project handoff

This file is the current, code-checked handoff for an AI assistant continuing this repository. Read it before proposing or implementing changes, then inspect the relevant source files because the code remains the source of truth.

## What this repository is

This is **FarisCommerce**, a student e-commerce website prototype for browsing products and a separate PHP/MySQL CRUD API for basic store records. It is not a gym-finder application. The repository currently combines:

- A static, hash-routed HTML storefront based on local Bootstrap/jQuery assets and template pages.
- A small PHP API with generic create/read/update/delete operations for seven MySQL tables.
- A SQL schema and sample data.

The browser UI and API are **not integrated**. The store, authentication, cart, checkout, and order screens mostly use static sample content or demo-only interactions. Do not describe those features as production-ready or persistently functional unless they have since been implemented.

## Repository map

```text
/
  CLAUDE.md                   This AI handoff
  README.md                   Short project introduction
  AiUsage.md                  Brief AI-use disclosure
  Struktura.png               Original entity/relationship diagram
  backend/
    database.sql              MySQL schema and sample records
    index.php                 PHP built-in-server/router entry point
    .htaccess                 Apache rewrite rules
    config/
      Database.php            PDO connection factory
      config.example.php      Safe local-config template
    dao/
      BaseDao.php             Shared table CRUD implementation
      UserDao.php             Table-specific DAO configuration
      CategoryDao.php         Table-specific DAO configuration
      ProductDao.php          Table-specific DAO configuration
      CartDao.php             Table-specific DAO configuration
      OrderDao.php            Table-specific DAO configuration
      OrderItemDao.php        Table-specific DAO configuration
      PaymentDao.php          Table-specific DAO configuration
    services/
      ApiService.php          Entity dispatch and CRUD orchestration
    routes/
      api.php                 HTTP parsing, response status and error handling
    logs/                     Ignored runtime logs; no application logger yet
  frontend/
    index.html                Shared storefront shell and site navigation
    js/
      router.js               Hash route to view loader
      script.js               Demo form handlers and Bootstrap behaviors
      cart.js                 In-memory DOM cart calculations
      jquery-2.0.0.min.js     Vendored jQuery
      bootstrap.bundle.min.js Vendored Bootstrap bundle
    views/                    HTML fragments/templates loaded by router.js
    css/                      Bootstrap and site styles
    assets/                   Product imagery, icons, fonts and other static assets
```

## Stack and conventions currently in use

- **Backend:** PHP, FlightPHP 3, PDO and MySQL/MariaDB-compatible SQL; hand-written DAO/service/route layers managed by Composer.
- **Milestone API:** FlightPHP 3, installed through Composer; CRUD business rules live in one service per entity. `backend/openapi.json` is the OpenAPI 3.1 spec and `backend/docs/index.html` hosts Swagger UI.
- **Frontend:** plain HTML, CSS, JavaScript, jQuery 2.0.0 and Bootstrap styles/scripts. There is no npm manifest, bundler, or frontend framework.
- **Database:** MySQL database named `fariscommerce`; schema and example rows are in `backend/database.sql`.
- **Routing:** frontend routes are URL hashes (for example `#store`); backend endpoints use `/api/...`.
- **Configuration:** `backend/config/config.local.php` is expected locally and ignored by git. Never commit actual credentials.
- There are currently no documented automated tests, lint/type-check commands, CI workflow, or dependency-install command.

## Local setup and running

Prerequisites: PHP with PDO MySQL enabled, and a running MySQL-compatible database. Use a throwaway/local database for the included sample data.

1. Create the schema and sample records by importing `backend/database.sql` into MySQL. The script creates and selects `fariscommerce`.
2. Copy `backend/config/config.example.php` to `backend/config/config.local.php` and replace the example host, database, username, and password with local values. This file is ignored.
3. From the repository root, install the PHP dependency:

   ```sh
   composer install
   ```

4. From the repository root, start the API with PHP's built-in server:

   ```sh
   php -S 127.0.0.1:8000 -t backend backend/index.php
   ```

   The entry point sends non-file requests to `backend/routes/api.php`; the Apache equivalent rewrite is in `backend/.htaccess`.
5. In a separate terminal, serve the frontend directory as static files:

   ```sh
   php -S 127.0.0.1:5500 -t frontend
   ```

   Open `http://127.0.0.1:5500/`. Serving over HTTP is preferable to opening `index.html` directly because the router fetches view fragments.

The storefront does not currently request the API; running both servers does not connect storefront actions to the database. Swagger UI is available at `http://127.0.0.1:8000/docs`, with the raw OpenAPI document at `/openapi.json`.

## Database model

All tables use InnoDB, `utf8mb4`, unsigned integer auto-increment primary keys unless stated otherwise. The complete authoritative DDL and seed rows are in `backend/database.sql`.

| Table | Columns beyond primary key | Relationships / constraints |
|---|---|---|
| `users` | `name`, `email` | `email` unique |
| `categories` | `name` | `name` unique |
| `products` | `category_id`, `name`, `price` (`DECIMAL(10,2)`) | Category FK; category delete restricted |
| `carts` | `user_id` | One cart per user (`user_id` unique); user delete cascades |
| `orders` | `user_id`, `order_date` (`DATE`) | User FK; user delete restricted |
| `payments` | `order_id`, `status` | One payment per order; order delete cascades |
| `order_items` | `order_id`, `product_id`, `quantity` | FKs to order and product; order delete cascades, product delete restricted |

`Struktura.png` is the original diagram, not a fully synchronized schema specification. For example, it omits some foreign-key columns that are present in SQL. Prefer the SQL and actual PHP DAO field allowlists when changing the model.

The seed inserts use fixed IDs and `INSERT IGNORE`, so re-importing the script does not overwrite existing rows with those IDs.

## Backend API

The API is generic CRUD, not a domain-specific ecommerce workflow. Supported entity names are exactly:

```text
users, categories, carts, orders, products, payments, order_items
```

For any entity:

| Request | Behavior |
|---|---|
| `GET /api/{entity}` | Return all rows ordered by primary key |
| `GET /api/{entity}/{id}` | Return one row or 404 |
| `POST /api/{entity}` | Create a row; JSON object body required; returns 201 |
| `PUT /api/{entity}/{id}` | Update supplied fields; returns the row or 404 |
| `PATCH /api/{entity}/{id}` | Same partial-update behavior as PUT |
| `DELETE /api/{entity}/{id}` | Delete; returns 204 or 404 |

There is no pagination, filtering, sorting, relationship expansion, or transaction-level order/cart workflow for database resources.

### API token authentication

CRUD endpoints require `Authorization: Bearer <token>`. Configure role tokens in the ignored `backend/config/config.local.php` under `api_tokens`: `admin` can read and write, while `reader` is read-only. Missing/invalid tokens return 401; a valid reader token attempting a write returns 403. The external search endpoint is public; OLX calls use a separate `external_api.olx_api_token`.

### Services and validation

`backend/services/ApiService.php` dispatches CRUD requests to one service per entity. Each entity service is the DAO caller and validates required fields, writable fields, formats, and basic business constraints before accessing its DAO. Current rules include valid user emails, non-empty bounded names, non-negative bounded product prices, positive foreign-key/quantity IDs, ISO `YYYY-MM-DD` order dates, and payment statuses limited to `pending`, `paid`, `failed`, or `refunded`.

### External search

`GET /api/external-search?q=<term>` uses DummyJSON's public product search by default; no key is required for that public endpoint. The optional `source=olx&username=<seller>&page=1` mode calls OLX's documented `GET /users/:username/listings?page=1`, requires a locally configured OLX bearer token, and filters results from that seller's requested page. OLX's documentation does not provide a global keyword search endpoint, so this mode is not a search across all OLX listings.

External requests use a configurable timeout (default five seconds), validate the JSON response, and return JSON errors. Missing OLX credentials return 401; upstream 401/403/404 are preserved; timeouts, invalid responses, and other upstream failures return a generic 500 response.

### OpenAPI / Swagger

`backend/openapi.json` documents all CRUD routes and external search. With the API server running, browse `http://127.0.0.1:8000/docs`; the underlying spec is `http://127.0.0.1:8000/openapi.json`. Swagger UI assets load from the pinned unpkg CDN.

Writable fields are restricted by DAO configuration:

| Entity | Writable fields |
|---|---|
| `users` | `name`, `email` |
| `categories` | `name` |
| `products` | `category_id`, `name`, `price` |
| `carts` | `user_id` |
| `orders` | `user_id`, `order_date` |
| `payments` | `order_id`, `status` |
| `order_items` | `order_id`, `product_id`, `quantity` |

Requests that write data must send JSON, for example:

```json
{"name":"Example product","category_id":1,"price":19.99}
```

Current response/error behavior in `backend/routes/api.php`:

- Unknown route shape: 404; invalid/non-positive ID: 400.
- Malformed/non-object JSON body: 400.
- Unknown entity or missing record: 404.
- Unsupported method/URL combination: 405.
- DAO field allowlist/scalar validation error: 400.
- Database integrity conflict (`SQLSTATE 23000`): 409.
- Other SQL data errors starting with `22`: 400.
- Other database/unexpected errors: generic 500 response.

`BaseDao` uses prepared statements for values. Table and column identifiers are interpolated from the fixed DAO definitions; keep them trusted and never derive them from request input. Empty create/update payloads and non-scalar field values are rejected. There is no deeper type, range, format, or business-rule validation.

## Frontend behavior and views

`frontend/index.html` contains the shared header, navigation, search form, guest account links, cart badge, and footer. `frontend/js/router.js` listens for hash changes, fetches an HTML fragment into `#app`, then fires `viewLoaded`. Only routes listed in its `routes` object can load through the shell.

Views present in `frontend/views/`:

- `home.html`: static welcome panel and four sample product cards.
- `store.html`: static product listing, category/size/price filter visuals.
- `about.html`, `contact.html`: informational/contact templates.
- `signin.html`, `register.html`: forms with demo submit handlers.
- `cart.html`: sample cart rows; `cart.js` adjusts quantities/removes rows and recalculates displayed subtotal, fixed 7% tax, and total in the current DOM only.
- `checkout.html`: checkout form/template; no persisted order creation.
- `product-detail.html`: static product details.
- `dashboard.html`: static account/order dashboard.
- `search-result.html`: template not registered in the router.
- `place-order.html`: older standalone full-page checkout template, not registered in the router.
- `order_complete.html`: static invoice/success fragment, not registered under the router's expected path.

Demo-only interactions in `frontend/js/script.js`:

- Contact, sign-in, and registration forms show browser alerts; they do not call the backend.
- Search submission changes the hash to `#store`; the search text is not used.
- “Place order” validates the checkout form in the browser and navigates to `#order-complete`; it does not create an order or process payment.
- Bootstrap tooltips and checkbox/radio styling are initialized client-side.

The product cards, account state, dashboard, invoice and most prices are hard-coded markup. Cart totals are derived from DOM text and are not saved across navigation or refresh.

## Known gaps and easy-to-miss issues

1. **No storefront/API integration.** There are no API calls from the storefront. CRUD API work will not automatically change the visible store.
2. **No authentication or authorization.** Sign-in/registration are alerts only; every API CRUD operation is currently unauthenticated. Do not expose this API publicly as-is.
3. **No payment processing.** Payment rows are ordinary CRUD records with a text status; there is no provider integration or secure payment flow.
4. **No production safeguards.** No CORS policy, CSRF protection, rate limiting, structured logging, migrations, deployment config, or centralized request schema validation is present.
5. **Router filename mismatch:** `router.js` maps `order-complete` to `views/order-complete.html`, but the checked-in filename is `views/order_complete.html`. As written, navigating to `#order-complete` displays “Could not load page.”
6. **Unrouted templates:** `search-result.html` and `place-order.html` exist but have no entries in the router map. Search always navigates to the store rather than the search-result page.
7. **Navigation mismatch:** `index.html` has links such as `#store`, `#signin`, and `#register`; `router.js` has matching view keys except that its current route list does not include `search-result` or `place-order`. Verify any other link targets when modifying navigation.
8. **Tax/pricing are demo values:** cart.js hard-codes 7% tax and `$` display formatting. Do not treat those as business rules.
9. **Some templates contain stale/sample copy:** for example, the success invoice has unrelated example customer/order data and a malformed grand-total string.
10. **No automated test suite is checked in.** Use the manual curl/Swagger checks in the README or add project-appropriate tests with future features.

Treat these as current observations, not permission to make unrelated cleanup changes while implementing a feature.

## Guidance for future feature work

- First inspect the relevant PHP, SQL, JS, and view files; this repository does not follow a Next.js/TypeScript/Prisma architecture.
- Preserve the existing separation where practical: HTTP handling in `backend/routes/api.php`, entity coordination in `backend/services/ApiService.php`, and database operations in DAO classes.
- For a new persisted entity, update `backend/database.sql`, add a DAO with a strict writable-column allowlist, and register it in `ApiService`. Keep foreign keys, uniqueness, indexes, and delete behavior intentional.
- Validate all request values beyond merely allowlisting column names. Do not expose raw exception details or credentials in API responses.
- For UI features, follow the existing hash router and delegated event handling for asynchronously loaded views. Add a route entry if the new view should be reachable from `index.html`.
- Clearly state whether data is static, in-memory, or persisted. Do not imply mock checkout is real payment or that a form creates an account.
- Avoid adding dependencies or paid third-party services without explicit approval. No current package manager is configured.
- Update this handoff and `README.md` when architecture, setup, routes, or project capabilities materially change.
