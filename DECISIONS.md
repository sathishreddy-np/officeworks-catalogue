# Decisions and assumptions

Time box for the brief is 2–3 hours. This pass stays on the critical path: browse, search, product details, and enquiry.

## Assumptions

- The five top-rated products are a merchandising choice, not “the five highest scores in the feed”. Their Fake Store ids live in `config/catalogue.php` so a buyer can change the set without a deploy of UI code. The chosen records are real products with strong ratings: 11, 12, 3, 18, and 5.
- On a category page, “top rated” means whichever of those five sit in the category. If a category contains none of them, the single highest-rated product in that category is the top-rated set and every other product is additional. That keeps the acceptance criteria true even for a category we have not curated.
- Search matches a case-insensitive substring of the title or the description. An empty search shows no products and asks for a term. The customer has to press Search; results are not filtered in the browser.
- Prices are shown as Australian dollars with an `A$` prefix. The API number is the shelf price for this proof of concept. There is no currency conversion.
- The sales inbox receives a real Laravel mailable. Locally `MAIL_MAILER=log`, so the message lands in `storage/logs/laravel.log` and the reviewer does not create a mailbox. The enquiry is also stored in `enquiries`, which is the record we would keep if the mail provider changed.
- Enquiry is synchronous. The default queue is the database driver, and a missed worker would hide the email from the log. The customer should see thank-you only after the mailer has accepted the message.
- Contact details are name, email, phone, and message. Phone allows digits and `+ ( ) . -` so an Australian mobile number validates.
- Category URLs use Laravel’s slug of the API category name (`men's clothing` becomes `mens-clothing`).
- Customers do not need an account. The starter-kit sign-in stays in the header for the existing account pages and is not part of the catalogue path.
- If Fake Store is down and the cache is empty, catalogue pages render a retry state instead of a 500. A successful response is cached so a later outage can still serve the last good catalogue until that cache expires.

## What was left out

- No cart, checkout, stock, or store locator. The brief stops at enquiry.
- No live Officeworks catalogue or logo artwork. The storefront uses the Officeworks name, a red header, and a text wordmark. Product photos come from the data source.
- No separate Node or mobile client. One Inertia app keeps setup to two commands.
- No Redis or paid mail service. Both can be turned on with environment variables when the project outgrows SQLite and the log mailer.

## How this can grow

- `ProductCatalogue` is the boundary. A PIM, a database, or an Officeworks API replaces `FakeStoreCatalogue` without changing the pages.
- Featured ids are configuration. A later admin screen can write the same list.
- Mail is a mailable to `CATALOGUE_SALES_INBOX`. Point `MAIL_MAILER` at SES when the inbox is real. The `enquiries` table remains the source of truth if delivery needs a retry.
- The UI is Inertia and TypeScript. A native app could call the same controllers, or a later API resource can sit beside them, still behind the catalogue contract.

## Another day

- Curate top-rated products per category in config, instead of falling back to the highest rating.
- Add cache locking so a stampede does not call Fake Store once per concurrent miss.
- Show delivery estimates and store stock once a real catalogue exists.
- Queue the mailable once a worker is part of the guaranteed runtime, and record delivery status on the enquiry.
