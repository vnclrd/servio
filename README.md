# Servio — Book Borrowing Management System

A simple book borrowing app. A PHP (Laravel) JSON API manages books stored in MySQL, and a plain HTML/CSS/JavaScript page talks to it with `fetch()` — no page reloads.

- **Backend:** PHP 8.4+, Laravel, MySQL
- **Frontend:** Blade view + vanilla JavaScript + CSS (bundled with Vite)
- **API base URL (local):** `http://127.0.0.1:8000/api`

---

## Requirements

- PHP 8.4 or newer, with the `pdo_mysql`, `mbstring`, `xml` and `curl` extensions
- Composer
- MySQL 8 (or MariaDB 10.6+)
- Node.js 20+ and npm — only needed if `public/build/` is not included in the submission

## Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Create the environment file and app key
cp .env.example .env
php artisan key:generate
```

Open `.env` and make sure the database settings match your MySQL server:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=servio
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

### Create the database — pick ONE option

**Option A — Laravel migrations (recommended)**

```bash
# create an empty database first
mysql -u root -e "CREATE DATABASE IF NOT EXISTS servio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate --seed
```

**Option B — plain SQL script**

Import `database/servio.sql` with phpMyAdmin, MySQL Workbench, or:

```bash
mysql -u root < database/servio.sql
```

> Use one option or the other, not both. Do not run `php artisan migrate` after importing the SQL script.

### Build the frontend

Skip this step if `public/build/` is already in the project.

```bash
npm install
npm run build
```

### Run

```bash
php artisan serve
```

Open <http://127.0.0.1:8000> in your browser.

---

## Database

Table: `books`

| Column       | Type                            | Notes                         |
|--------------|---------------------------------|-------------------------------|
| `book_id`    | `INT UNSIGNED`, auto-increment  | Primary key                   |
| `title`      | `VARCHAR(255)`                  | Indexed                       |
| `author`     | `VARCHAR(255)`                  | Indexed                       |
| `status`     | `ENUM('Available','Borrowed')`  | Defaults to `Available`       |
| `created_at` | `TIMESTAMP`, nullable           |                               |
| `updated_at` | `TIMESTAMP`, nullable           | Changes whenever status changes |

Schema: `database/migrations/*_create_books_table.php` and `database/servio.sql`.
Sample data: `database/seeders/BookSeeder.php` (6 books) and the `INSERT` statements in `database/servio.sql`.

---

## API Documentation

All endpoints live under `/api`, return **JSON**, and use standard HTTP status codes. Send `Accept: application/json` on every request.

### Response shapes

Successful and application-level error responses use this shape:

```json
{ "success": true | false, "message": "…", "data": … }
```

Request-validation failures (HTTP `422`) use Laravel's standard shape instead:

```json
{
  "message": "The selected status is invalid.",
  "errors": { "status": ["The selected status is invalid."] }
}
```

### Status codes used

| Code | Meaning                                                              |
|------|----------------------------------------------------------------------|
| 200  | Request succeeded                                                    |
| 400  | Bad request — invalid book ID, or the book is already in the requested status |
| 404  | Book not found, or unknown route                                     |
| 405  | HTTP method not allowed on that route                                |
| 409  | Conflict — the book was changed by another request at the same moment |
| 422  | Validation failed — missing, malformed or disallowed parameter value |
| 500  | Unexpected server error                                              |

---

### 1. Search / fetch books

**`GET /api/books`**

Returns all books, or only the books whose **title or author** contains the search text (case-insensitive partial match). Results are ordered by `book_id` ascending.

#### Query parameters

| Name     | Type   | Required | Description                                                    |
|----------|--------|----------|----------------------------------------------------------------|
| `search` | string | No       | Text to look for in title or author. Max 255 characters. If omitted or empty, all books are returned. |
| `q`      | string | No       | Alias for `search` (used only when `search` is not given).     |

#### Example requests

```http
GET /api/books
GET /api/books?search=hail
GET /api/books?search=colleen%20hoover
```

#### Sample success response — `200 OK`

`GET /api/books?search=hail`

```json
{
  "success": true,
  "count": 1,
  "query": "hail",
  "data": [
    {
      "book_id": 5,
      "title": "Project Hail Mary",
      "author": "Andy Weir",
      "status": "Available",
      "updated_at": "2026-11-21T04:12:09.000000Z"
    }
  ]
}
```

When nothing matches, the request still succeeds with an empty list:

```json
{
  "success": true,
  "count": 0,
  "query": "zzzz",
  "data": []
}
```

#### Sample error response — `422 Unprocessable Content`

`GET /api/books?search[]=a` (`search` is not a string):

```json
{
  "message": "The search field must be a string.",
  "errors": {
    "search": ["The search field must be a string."]
  }
}
```

A search text longer than 255 characters returns the same shape with the message `The search field must not be greater than 255 characters.`

---

### 2. Update book status (borrow / return)

**`POST /api/books/{book_id}/status`**
**`PUT  /api/books/{book_id}/status`**

Both methods behave identically. Use it to **borrow** a book (`Borrowed`) or **return** it (`Available`).

#### URL parameter

| Name      | Type    | Required | Description                         |
|-----------|---------|----------|-------------------------------------|
| `book_id` | integer | Yes      | ID of the book. Must be 1 or more.  |

#### Request body (JSON)

| Name     | Type   | Required | Allowed values            |
|----------|--------|----------|---------------------------|
| `status` | string | Yes      | `Available` or `Borrowed` (case-sensitive) |

```http
POST /api/books/3/status
Content-Type: application/json
Accept: application/json

{ "status": "Borrowed" }
```

#### Sample success response — `200 OK`

```json
{
  "success": true,
  "message": "Book status updated successfully.",
  "data": {
    "book_id": 3,
    "title": "The Silent Patient",
    "author": "Alex Michaelides",
    "status": "Borrowed",
    "updated_at": "2026-11-21T04:15:32.000000Z"
  }
}
```

#### Sample error responses

**`400 Bad Request` — book is already borrowed**

```json
{
  "success": false,
  "message": "This book is already borrowed."
}
```

**`400 Bad Request` — book is already available** (returning a book that is not borrowed)

```json
{
  "success": false,
  "message": "This book is already available."
}
```

**`400 Bad Request` — invalid book ID** (e.g. `/api/books/abc/status` or `/api/books/0/status`)

```json
{
  "success": false,
  "message": "Invalid book ID."
}
```

**`404 Not Found` — no book with that ID**

```json
{
  "success": false,
  "message": "Book not found."
}
```

**`409 Conflict` — another request changed the book first**

```json
{
  "success": false,
  "message": "Book was just updated by another request. Please refresh."
}
```

**`422 Unprocessable Content` — missing or invalid status**

Body `{ "status": "Lost" }`:

```json
{
  "message": "The selected status is invalid.",
  "errors": {
    "status": ["The selected status is invalid."]
  }
}
```

Body `{}`:

```json
{
  "message": "The status field is required.",
  "errors": {
    "status": ["The status field is required."]
  }
}
```

**`405 Method Not Allowed` — e.g. `GET /api/books/3/status`**

```json
{
  "message": "The GET method is not supported for route api/books/3/status. Supported methods: POST, PUT."
}
```

**`500 Internal Server Error` — unexpected failure** (with `APP_DEBUG=false`)

```json
{
  "message": "Server Error"
}
```

#### Business rules

- A book whose status is `Borrowed` cannot be borrowed again, and a book that is `Available` cannot be returned (`400`).
- The update only succeeds if the status has not changed since it was read, so two simultaneous requests cannot both borrow the same book (`409` for the loser).
- Only `Available` and `Borrowed` are accepted as statuses (`422` otherwise).
- All database access goes through Eloquent / the query builder, which uses **prepared statements with bound parameters**, so user input is never concatenated into SQL.

---

## Frontend behaviour

- The book list loads automatically when the page opens (`GET /api/books`).
- Typing in the search box updates the list after a short pause (debounced). The **Search** button runs the search immediately, and **Clear** resets it.
- Each row shows a **Borrow** button for `Available` books and a **Return** button for `Borrowed` books.
- Clicking a button calls the status endpoint, then refreshes the table in place. A green or red banner reports the result. The page never reloads.

## Project structure (key files)

```
app/Http/Controllers/Api/BookController.php   API logic (search + status update)
app/Models/Book.php                           Book model
routes/api.php                                API routes
routes/web.php                                Serves the frontend page
resources/views/servio.blade.php              Frontend HTML
resources/css/servio.css                      Frontend styles
resources/js/servio.js                        Frontend logic (fetch calls)
database/migrations/*_create_books_table.php  Table structure
database/seeders/BookSeeder.php               Sample data
database/servio.sql                           Plain MySQL script (structure + data)
```