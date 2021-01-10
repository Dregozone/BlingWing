# SEP Assigment Application

This a Symfony application, using Doctrine as ORM, Twig for templating, API Platform for REST API and a few other libs.

## Setup

### Prerequisites

- You need at least PHP 7.3 (CLI) plus a few common PHP extensions.
- You need a MySQL database.
- You have to have `composer` installed.

### Installation

1. Clone the repo
2. Create a file `.env.local` with the following entry: `DATABASE_URL="mysql://db_user:db_pass@127.0.0.1:3306/db_name?serverVersion=5.7"`. Replace `db_user`, `db_pass` and `db_name` with the correct values.
3. Run `composer install` to install dependencies.
4. Run `bin/console doctrine:schema:update --dump-sql --force` to create the database schema.
5. Run `bin/console app:import` to create the fake restaurants/reviews. /* This is now "bin/console app:data:generate" */
6. Run `php -S localhost:8080 -t public`.
7. Open http://localhost:8080 in your browser.

Enjoy!
