```markdown
# BookBuddy

BookBuddy is a PHP and MySQL online bookstore. Customers can browse books, create accounts, manage a cart, save delivery addresses, place Cash on Delivery orders, and view their order history. Administrators can manage books, categories, discounts, daily deals, stock, and orders.

The project uses server rendered PHP pages with HTML, CSS, and JavaScript. JavaScript uses fetch requests for several forms and account actions.

## 1. Main Features

### Customer Features

Customers can view the home page and browse the complete book catalog.

Customers can filter books by category and view books that are on sale.

Customers can search the catalog from the store interface.

Customers can open a book page and view the author, publisher, ISBN, publication date, description, price, discount, stock, and category.

Customers can create an account with a name, username, email address, password, and PNG profile image.

Customers can log in and log out using a session based account system.

Customers can reset a forgotten password by receiving a six digit email verification code. The code is valid for five minutes.

Customers can add books to the cart and change or remove cart quantities.

Customers can save and update delivery addresses.

Customers can place orders using Cash on Delivery.

Customers can view order details and cancel orders that are still processing.

Customers can delete eligible order history records.

Customers can purchase active daily deal books through the separate daily deal checkout flow.

### Administrator Features

Administrators can access the protected administration area.

Administrators can view books with category filters and pagination.

Administrators can add, edit, and delete books.

Administrators can upload book cover images and manage book details.

Administrators can add, edit, and delete categories.

Administrators can view books that have low or empty stock.

Administrators can apply discounts to books.

Administrators can create and delete twenty four hour daily deals.

Administrators can view orders and open order details.

Administrators can update order and payment statuses.

Administrators can delete orders from the administration area.

## 2. Technology Stack

The application uses the following technologies.

1. PHP 8.1 or a newer version.

2. MySQL or MariaDB.

3. PDO with the MySQL driver for database access.

4. HTML, CSS, and JavaScript for the user interface.

5. Composer for PHP package management.

6. PHPMailer for SMTP email support.

7. Resend for production email delivery when a Resend API key is configured.

8. Docker with PHP 8.2 Alpine for container deployment.

9. Railway deployment configuration through `railway.toml`.

## 3. Requirements

For a local installation, install the following software.

1. PHP 8.1 or newer.

2. PHP cURL extension.

3. PHP GD extension.

4. PHP mbstring extension.

5. PHP PDO extension.

6. PHP PDO MySQL extension.

7. MySQL or MariaDB.

8. Composer.

9. Git.

## 4. Download the Project

Clone the repository with the following command.

```bash
git clone https://github.com/muhammadshaharyaraulakh/BookBuddy.git
```

Move into the project directory.

```bash
cd BookBuddy
```

Install the PHP dependencies.

```bash
composer install
```

## 5. Create the Database

Create a MySQL database named `BookBuddy`.

The application connects to the database before it runs the database seeder, so the database itself must exist first.

You can create it with the following SQL statement.

```sql
CREATE DATABASE BookBuddy CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

The project includes the database structure in `database/bookbuddy.sql`.

The `seed.php` file also creates the required tables and inserts the initial categories, sample books, and initial user records. Run it after configuring the database connection.

```bash
php seed.php
```

The seeder is designed to be safe to run more than once for the existing users, categories, and books. Review and change the initial account records before using the application in production.

## 6. Database Configuration

The main database configuration is in `config/config.php`.

The application supports Railway and Docker database variables. It also supports local variable names.

```text
MYSQLHOST
MYSQLDATABASE
MYSQLUSER
MYSQLPASSWORD
MYSQLPORT
```

The local variable names are listed below.

```text
DB_HOST
DB_NAME
DB_USER
DB_PASSWORD
DB_PORT
```

The application can also read a complete connection URL from one of these variables.

```text
MYSQL_URL
DATABASE_URL
```

For local development, the default database values are the following.

```text
Host: localhost
Database: BookBuddy
User: root
Port: 3306
```

Set a password when your local MySQL user requires one. Do not commit passwords or other private values to the repository.

## 7. Email Configuration

BookBuddy uses email for password reset codes and deal expiration messages.

Resend is preferred when the following variables are configured.

```text
RESEND_API_KEY
RESEND_FROM_EMAIL
```

For local SMTP delivery, configure the following variables.

```text
SMTP_HOST
SMTP_USER
SMTP_PASSWORD
SMTP_PORT
SMTP_SECURE
```

The default SMTP port is 587 and the default security mode is TLS.

If no email provider is configured, password reset and other email actions cannot deliver messages successfully.

## 8. Run the Application Locally

Start the PHP development server from the project directory.

```bash
php -S 0.0.0.0:8080 router.php
```

Open the following address in a browser.

```text
http://localhost:8080
```

The project router provides clean addresses such as the following.

```text
/
/shop
/book?id=1
/login
/register
/cart
/profile
/orders
/admin
/categories
/dailydeal
/adminorders
```

Apache users can use the included `.htaccess` file for the same clean addresses.

## 9. Docker Usage

The project includes a `Dockerfile` based on PHP 8.2 Alpine.

Build the image with the following command.

```bash
docker build -t bookbuddy .
```

Run the container with the following command.

```bash
docker run --rm -p 8080:8080 bookbuddy
```

When a supported database variable is available, the container entrypoint runs `seed.php` before starting the PHP server.

The container uses the `PORT` variable when one is provided. The default port is 8080.

## 10. Railway Deployment

Railway uses the project `Dockerfile` through the settings in `railway.toml`.

The deployment starts the application on port 8080 unless Railway provides another value through `PORT`.

The Railway configuration uses the root address as its health check.

Before deployment, add the database variables and email variables to the Railway service. Never place private values directly in the repository.

## 11. Project Structure

The main folders and files are described below.

```text
admin
  adminPages
  handlers

assests
  css
  js

auth
  forgot
  login
  otp
  registration
  reset

config
database
function
handlers
images
includes
userImages

index.php
shop.php
book.php
cart.php
profile.php
orders.php
seed.php
router.php
Dockerfile
railway.toml
```

The `admin` folder contains the administration pages and their request handlers.

The `auth` folder contains registration, login, logout, and password reset pages.

The `handlers` folder contains customer actions such as cart changes, address management, order placement, and order cancellation.

The `function` folder contains shared application functions and email helpers.

The `includes` folder contains shared page sections such as the header, navigation, footer, and address modals.

The `images` folder contains book covers and general site images.

The `userImages` folder stores uploaded profile images. The folder is kept in Git with `.gitkeep`, but uploaded files are ignored.

## 12. Database Tables

The database contains the following main tables.

1. `user` stores customer and administrator accounts.

2. `categories` stores book categories.

3. `book` stores book details, prices, discounts, stock, and cover images.

4. `deals` stores active and expired daily deals.

5. `reviews` stores the review structure for books.

6. `cart` stores each customer's selected books and quantities.

7. `user_address` stores delivery addresses.

8. `orders` stores customer order information.

9. `order_items` stores the books included in each order.

## 13. Order and Stock Behavior

BookBuddy uses Cash on Delivery for orders.

The checkout process validates the selected address, checks current stock, calculates the current price, creates the order, stores the order items, reduces stock, and clears the cart inside a database transaction.

The application uses row locks and stock checks to reduce the chance of selling more books than are available.

Books that are part of an active daily deal cannot be purchased through the normal cart checkout.. Security Behavior

Passwords are stored using password hashing.

Administrator pages require an administrator session.

Database queries use prepared statements through PDO.

The router blocks direct requests for configuration files, SQL files, environment files, Docker files, lock files, and the seeder.

Uploaded profile images are limited to PNG files with a maximum size of two megabytes during registration.

Keep all database passwords, email passwords, API keys, and session secrets outside the repository.

## 15. Limitations

The current payment method is Cash on Delivery. No online payment gateway is included.

The database contains a reviews table, but the current application does not provide complete review creation and display functionality.

Deal expiration is checked during application requests instead of using a dedicated scheduled job.

The PHP built in server is used by the included Docker entrypoint. A production installation may use a dedicated PHP web server behind a reverse proxy.

Uploaded profile images may not remain available after a container is replaced unless persistent storage is configured.

The project does not currently include an automated test suite.

## 16. Development Notes

The project has no Composer scripts. Use the PHP, Composer, Docker, and database commands described in this file.

Before making changes, test the related customer and administrator flows in a local environment.

Do not commit the `.env` file, private credentials, generated uploads, or dependency folders.

## 17. License

No license file is currently included in this repository. Add a license before distributing the project publicly.

## 18. Contact

Project repository:

https://github.com/muhammadshaharyaraulakh/BookBuddy
```