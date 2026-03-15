# Ahsan Electronic Shop

Advanced PHP + MySQL catalog website with admin panel and WhatsApp order system.

## Features

- **Product Catalog** - Browse products by category with responsive grid layout
- **WhatsApp Orders** - One-click order via WhatsApp with pre-filled product details
- **Admin Panel** - Full CRUD for products, categories, and site settings
- **Category Icons** - Upload custom icons for each category
- **View Tracking** - Track page views per product with analytics table
- **Rate-Limited Login** - 5 failed attempts = 15-minute lockout (bcrypt hashed passwords)
- **Image Uploads** - Validated uploads (5MB max, JPG/PNG/GIF/WebP)
- **Settings Management** - Site title, logo, WhatsApp number, theme colors, social media links
- **Responsive Design** - Mobile-friendly with Tailwind CSS and hamburger menu
- **Prepared Statements** - All database queries use parameterized queries for security

## Installation (XAMPP)

### Step 1: Copy Project
Place this folder at `/opt/lampp/htdocs/ahsanelectronic_shop`

### Step 2: Start XAMPP
Start Apache and MySQL services.

### Step 3: Database Setup

**Method 1 (Automatic):** Visit in your browser:
```
http://localhost/ahsanelectronic_shop/install.php
```
This will automatically create the database, tables, default admin user, and sample categories.

**Method 2 (Manual):**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create database `ahsanelectronic_shop`
3. Import `database.sql`
4. Default admin user: username `admin`, password `admin123`

### Step 4: Visit the Website
```
http://localhost/ahsanelectronic_shop/
```

> **Important:** Delete `install.php` after setup for security!

## Admin Panel

```
http://localhost/ahsanelectronic_shop/admin/login.php
```

**Default Login:**
- Username: `admin`
- Password: `admin123`

> Change the password immediately via Admin > Settings after first login!

## Project Structure

```
ahsanelectronic_shop/
|-- index.php              # Homepage - categories with icons, latest products
|-- product.php            # Product details with view tracking + WhatsApp button
|-- category.php           # Category page with sidebar navigation
|-- db.php                 # Database connection + helper functions
|-- install.php            # Auto-installation script
|-- database.sql           # SQL schema for import
|-- README.md              # This file
|-- admin/
|   |-- login.php          # Secure login with rate limiting
|   |-- dashboard.php      # Metrics, product/category tables, analytics
|   |-- add-product.php    # Add new product with image upload
|   |-- edit-product.php   # Edit existing product
|   |-- add-category.php   # Add new category with icon upload
|   |-- edit-category.php  # Edit existing category
|   `-- settings.php       # Site settings, theme colors, social links, password change
|-- uploads/               # Product images and category icons
`-- css/                   # Tailwind CSS (CDN)
```

## Database Tables

| Table | Description |
|-------|-------------|
| `products` | id, name, price, image, category_id, description, views, created_at, updated_at |
| `categories` | id, name, icon, created_at, updated_at |
| `settings` | id, setting_key, setting_value, created_at, updated_at |
| `admins` | id, username, password (bcrypt), login_attempts, last_attempt, created_at |
| `analytics` | id, product_id, ip_address, user_agent, viewed_at |

## Security Features

- Passwords hashed with `bcrypt` via `password_hash()` / `password_verify()`
- Login rate limiting (5 attempts, 15-minute lockout)
- All SQL queries use **prepared statements** (mysqli)
- Session-based authentication for admin panel
- Image upload validation (type, size)
- XSS prevention with `htmlspecialchars()` output encoding
- No sensitive credentials exposed in frontend

## Default WhatsApp Number

`+8801768870308` - Change via Admin Panel > Settings.

## Tech Stack

- **Backend:** PHP 7.4+ (mysqli)
- **Database:** MySQL 5.7+ / MariaDB
- **Frontend:** Tailwind CSS (CDN), vanilla JavaScript
- **Server:** XAMPP (Apache + MySQL)
