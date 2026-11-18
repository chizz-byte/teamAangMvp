# SLU Peer-to-Peer Textbook & Course Material Marketplace

A web-based marketplace where Saint Louis University students buy and sell
secondhand textbooks, reviewers, lab manuals, course materials, and uniforms.
Built with PHP 8.2+, MySQL (PDO), Bootstrap 5, and vanilla JavaScript.

## Features

- Student registration restricted to `@slu.edu.ph` emails
- Password hashing (`password_hash` / `password_verify`), PHP sessions, CSRF tokens
- Marketplace browsing with keyword search, category/department/condition filters, sorting
- Listing CRUD with server-side image upload validation (JPG/PNG/WebP, ≤5MB)
- Buyer–seller messaging per listing (AJAX send + 4s polling)
- Transaction workflow: propose → negotiate → reserve → complete → sold
- 1–5 star ratings with optional feedback (one review per transaction participant)
- Listing reports and an admin dashboard (users, listings, reports, stats)
- Meetup coordination for SLU Main Campus / Maryheights Campus
  (payment handled peer-to-peer: cash, GCash, etc. — no payment gateway)

## Requirements

- WampServer (Apache + MySQL + PHP ≥ 8.2) — tested on WAMP64 with PHP 8.2/8.3 and MySQL 9
- phpMyAdmin (included with WampServer)

## Setup

1. **Start WampServer** (green icon) so Apache and MySQL are running.
2. **Place the project** in the WAMP web root:
   - Either copy `slu-marketplace/` into `C:\wamp64\www\`, or
   - create a junction: `mklink /J C:\wamp64\www\slu-marketplace E:\teamAangMvp\slu-marketplace`
3. **Create the database** — open phpMyAdmin (`http://localhost/phpmyadmin`),
   select **Import**, choose `database/slu_marketplace.sql`, and run it.
   The script creates the `slu_marketplace` database and all tables.
   CLI alternative:
   `C:\wamp64\bin\mysql\mysql9.1.0\bin\mysql.exe -u root < database\slu_marketplace.sql`
4. **Seed demo data** (optional but recommended for the demo):
   `C:\wamp64\bin\php\php8.2.26\php.exe tools\seed.php`
   This creates demo users, 12 listings, and sample images (GD required).
5. **Configure DB credentials** in `config/database.php` if your MySQL
   root password differs from the WAMP default (empty).
6. **Open** `http://localhost/slu-marketplace/` in a browser.

## Demo accounts (after seeding)

| Role    | Email                    | Password      |
|---------|--------------------------|---------------|
| Admin   | admin@slu.edu.ph         | password123   |
| Student | juan.delacruz@slu.edu.ph | password123   |
| Student | ana.reyes@slu.edu.ph     | password123   |
| Student | pedro.lim@slu.edu.ph     | password123   |

Change the admin password before any real deployment.

## Project structure

```
slu-marketplace/
├── index.php               Marketplace dashboard (search + filters)
├── login.php / register.php / logout.php
├── api.php                 AJAX endpoint (messages, transactions)
├── profile.php             Profile, stats, change password
├── config/database.php     PDO connection
├── controllers/            MessageController, TransactionController
├── models/                 User, Listing, Message, Transaction, Review, Report
├── middleware/             auth.php, admin.php guards
├── includes/               functions.php, header/navbar/footer
├── listings/               view, create, edit, mine + shared form/upload
├── messages/               conversation list + chat view (polling)
├── transactions/           transaction history
├── admin/                  dashboard, users, listings, reports
├── assets/                 css + js
├── uploads/listings/       uploaded images (scripts disabled via .htaccess)
├── database/slu_marketplace.sql
└── tools/seed.php          demo data generator
```

## Basic test cases

1. Register with a non-SLU email → rejected; with `@slu.edu.ph` → account created.
2. Login → redirected to marketplace; protected pages redirect guests to login.
3. Search "ITE" or filter by category → only matching listings appear.
4. As a student: create a listing with an image → appears in marketplace and "My Listings".
5. As another student: open listing → "Chat with Seller" → exchange messages (auto-refresh).
6. "Start Transaction" → listing becomes Reserved; agree price/meetup via chat or Update.
7. "Mark Completed" → transaction completed, listing Sold, review form appears.
8. Submit a 1–5 star review → shows on seller profile/listing.
9. "Report" a listing → appears in admin → Reports; admin can remove/restore listings.
10. Student hitting `/admin/*` → 403; admin sees stats and can moderate.

## Security notes

- All SQL via PDO prepared statements; output escaped with `htmlspecialchars()`.
- CSRF tokens on every POST form and AJAX call; session ID regenerated on login.
- Uploads validated by MIME type + `getimagesize()`, random filenames, script execution disabled.
- Ownership checks on edit/delete listing, messaging threads, and transaction actions.
