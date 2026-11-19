# Officeworks catalogue

A proof of concept product catalogue for an Officeworks-style storefront. Customers can browse five chosen top-rated products, open a category, search, view a product, and send an enquiry. The React app never calls the product API. Laravel loads, caches, and shapes that data.

## Stack

- **Client:** React with TypeScript, Inertia, Tailwind CSS, and Motion.
- **Backend:** Laravel 13 on PHP 8.3, with SQLite.
- **Product data:** [Fake Store API](https://fakestoreapi.com/), behind `App\Catalogue\ProductCatalogue`.

Laravel and Inertia are already one application, so a reviewer can install dependencies and run the site with two commands. SQLite, the database cache, and the log mailer need no extra accounts. The same shape can move to AWS later: the app is a normal PHP application, the cache and queue drivers are configured in `.env`, and mail can switch from `log` to SES without a code change.

## For a reviewer

Clone the repository and run these commands from the folder that contains `artisan`. You need PHP 8.3 or newer, Composer, and Node.js 20 or newer. No database server, Redis, or mail account is required. The app uses SQLite, and enquiry email is written to the log.

```bash
git clone https://github.com/sathishreddy-np/officeworks-catalogue.git
cd officeworks-catalogue
git checkout catalogue
composer setup
composer dev
```

`main` is the base branch for the open pull request. The catalogue source is on `catalogue`. Check that branch out before setup.

Open [http://127.0.0.1:8000](http://127.0.0.1:8000).

`composer setup` copies `.env.example` to `.env` when `.env` is missing, generates the app key, creates the SQLite database, runs migrations, installs npm packages, and builds the frontend. `composer dev` starts the PHP server, the queue listener, and Vite. Leave that terminal open while you review.

What you should see:

1. The home page lists five top-rated products, with prices in AUD.
2. A category link shows a top-rated section, then the other products in that category.
3. The Search button returns only products whose title or description matches the term.
4. A product tile opens the product page.
5. Enquire asks for name, email, phone, and message, then shows “Thank you”. The email is appended to `storage/logs/laravel.log`.

`.env` is not in the repository. Do not commit one. Optional settings, if you edit `.env` before `composer setup`:

Copy `.env.example` to `.env` yourself only if you want to change configuration before setup. Useful values:

- `CATALOGUE_BASE_URL` — product API origin. The default is `https://fakestoreapi.com`.
- `CATALOGUE_CACHE_SECONDS` — how long a successful catalogue response is reused. The default is 10 minutes.
- `CATALOGUE_SALES_INBOX` — where enquiry email is addressed. The default is `sales@officeworks.example`.
- `MAIL_MAILER` — `log` locally. Each enquiry is written to `storage/logs/laravel.log`.

The five home-page products are chosen in `config/catalogue.php`:

| Id  | Product                    |
| --- | -------------------------- |
| 11  | Silicon Power 256GB SSD    |
| 12  | WD 4TB gaming drive        |
| 3   | Mens cotton jacket         |
| 18  | MBJ boat-neck top          |
| 5   | John Hardy dragon bracelet |

## What you can do

- Home shows those five products, in that order.
- A category shows its top-rated products, then the other products in that category.
- Search is a Search button. Matching is done on the server against the title and description.
- A product tile opens the product page.
- Enquire sends the contact form to the sales inbox and shows a thank-you message. Read the message in `storage/logs/laravel.log`.

Sign-in is still available for the starter-kit account pages. The catalogue does not require an account.

## Tests

```bash
php artisan test --compact tests/Feature/CatalogueTest.php
```

Catalogue tests fake the product API and the mailer. They do not call Fake Store or send real email.
