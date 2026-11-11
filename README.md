# BlingWing Jewelery website Catalog that showcases Jewelery items grouped into their collections.

This a Symfony application, using Doctrine as ORM and Twig for templating.

## Setup

### Prerequisites

- You need at least PHP 8.4 (CLI) plus a few common PHP extensions.
- SQLite is used by default for local development (requires the `pdo_sqlite` PHP extension, which is bundled with most PHP builds). MySQL is optional, e.g. for production.
- You have to have `composer` installed.

### Installation

1. Clone the repo
2. Copy the example environment file: `cp .env.example .env`, and set `APP_SECRET` in it to a random value (e.g. the output of `php -r "echo bin2hex(random_bytes(16));"`). `.env` is gitignored, so never commit it.
3. Run `composer install` to install dependencies.
4. Run `bin/console doctrine:schema:update --force` to create the SQLite database (`var/data.db`) and schema.
5. Run `bin/console app:data:generate` to create the fake members/collections/items.
6. Run `php -S localhost:8080 -t public`.
7. Open http://localhost:8080 in your browser.

### Using MySQL instead

Create `.env.local` (or `.env.prod.local`, or set a real environment variable) with:

```
DATABASE_URL="mysql://db_user:db_pass@127.0.0.1:3306/db_name?serverVersion=8.0&charset=utf8mb4"
```

Replace the credentials and set `serverVersion` to your actual MySQL version. Then run `bin/console doctrine:database:create` (if the database doesn't exist yet), followed by steps 4 and 5 above.
