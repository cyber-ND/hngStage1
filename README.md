# String Analyzer Service

## Overview
A RESTful API built with Laravel 12 to analyze strings and store their computed properties (length, palindrome status, unique characters, word count, SHA-256 hash, and character frequency). Supports creating, retrieving, filtering, and deleting strings, with natural language query support.

## Features

1. `POST /strings`: Analyze and store a string with its properties.
2. `GET /strings/{string_value}`: Retrieve a specific string's details.
3. `GET /strings`: List all strings with optional filters (e.g., is_palindrome, min_length).
4. `GET /strings/filter-by-natural-language`: Filter strings using natural language queries (e.g., "all single word palindromic strings").
5. `DELETE /strings/{string_value}`: Delete a specific string.

## ## Prerequisites

1. PHP: 8.2 or higher
2. Composer: Latest version
3. MySQL: 5.7 or higher (or SQLite for local testing)
4. Node.js: Optional, for development tools (not required for this API)
5. Git: For cloning the repository
6. Thunder Client: Recommended for testing API endpoints (or use cURL/Postman)

## ## Setup Instructions

### Windows

1. Clone the repository:
   ```bash
   git clone <your-repo-url>
   cd string-analyzer
   ```

2. Copy the environment file:
   ```bash
   copy .env.example .env
   ```

3. Install PHP dependencies:
   ```bash
   composer install
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Configure MySQL in .env:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=stage1
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

   Alternatively, use SQLite:
   ```env
   DB_CONNECTION=sqlite
   ```

6. Create an empty database/database.sqlite file if using SQLite.

7. Run migrations to create the string_analyses table:
   ```bash
   php artisan migrate
   ```

### MacOS/Linux

1. Clone the repository:
   ```bash
   git clone <your-repo-url>
   cd string-analyzer
   ```

2. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

3. Install PHP dependencies:
   ```bash
   composer install
   ```

4. Generate application key:
   ```bash
   php artisan key:generate
   ```

5. Configure MySQL in .env:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=stage1
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

   Or use SQLite:
   ```env
   DB_CONNECTION=sqlite
   ```

6. Create an empty database/database.sqlite file if using SQLite.

7. Run migrations:
   ```bash
   php artisan migrate
   ```

## Running Locally

1. Start the Laravel development server:
   ```bash
   php artisan serve
   ```

2. The API will be available at http://127.0.0.1:8000
3. Test endpoints using Thunder Client or cURL (see Testing section)

## Dependencies

1. PHP: 8.2+ (install via php.net or package manager like apt/brew)
2. Composer: Dependency manager for PHP (getcomposer.org)
3. Laravel: 12.x (installed via Composer)
4. MySQL/SQLite: Database (MySQL via mysql package or SQLite included with PHP)
5. No additional external packages required

## Installing Dependencies

### Windows
1. PHP: Download from php.net and add to PATH
2. Composer: Download and install from getcomposer.org
3. MySQL: Install via MySQL Installer or use SQLite (no install needed)
4. Run: 
   ```bash
   composer install
   ```
   in project root.

### MacOS/Linux
1. PHP: 
   ```bash
   # MacOS
   brew install php
   
   # Ubuntu
   sudo apt install php php-cli php-mbstring php-xml php-mysql
   ```

2. Composer: 
   ```bash
   curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer
   ```

3. MySQL: 
   ```bash
   # MacOS
   brew install mysql
   
   # Ubuntu
   sudo apt install mysql-server
   ```

4. Run: 
   ```bash
   composer install
   ```
   in project root.


## Testing Endpoints
Use Thunder Client (VS Code extension) or cURL to test the API.

### POST /strings
```bash
curl -X POST http://127.0.0.1:8000/strings \
-H "Content-Type: application/json" \
-d '{"value": "hello world"}'
```
**Expected**: 201 Created with string properties.

### GET /strings/{string_value}
```bash
curl -X GET http://127.0.0.1:8000/strings/hello%20world
```
**Expected**: 200 OK with string details.

### GET /strings
```bash
curl -X GET "http://127.0.0.1:8000/strings?is_palindrome=true&min_length=5&word_count=2"
```
**Expected**: 200 OK with filtered strings.

### GET /strings/filter-by-natural-language
```bash
curl -X GET "http://127.0.0.1:8000/strings/filter-by-natural-language?query=all%20single%20word%20palindromic%20strings"
```
**Expected**: 200 OK with filtered strings and parsed query.

### DELETE /strings/{string_value}
```bash
curl -X DELETE http://127.0.0.1:8000/strings/hello%20world
```
**Expected**: 204 No Content.
## Troubleshooting

1. **Database Errors**: 
   - Ensure `stage1` database exists in MySQL or `database/database.sqlite` file exists for SQLite
   - Run `php artisan migrate`

2. **404 Errors**: 
   - Verify routes with `php artisan route:list`
   - Ensure `routes/api.php` is loaded in `bootstrap/app.php`

3. **419 Errors**: 
   - API routes bypass CSRF
   - Ensure `/strings` routes are in `api.php`

4. **Logs**: 
   - Check `storage/logs/laravel.log` for detailed errors

5. **Cache**: 
   - Clear with:
     ```bash
     php artisan cache:clear
     php artisan config:cache
     ```

## Deployment (Heroku Example)

1. Install Heroku CLI

2. Create app:
   ```bash
   heroku create string-analyzer-app
   ```

3. Add Procfile:
   ```bash
   echo "web: vendor/bin/heroku-php-apache2 public/" > Procfile
   ```

4. Push to Heroku:
   ```bash
   git push heroku main
   ```

5. Run migrations:
   ```bash
   heroku run php artisan migrate
   ```

6. Base URL: `https://string-analyzer-app.herokuapp.com`
