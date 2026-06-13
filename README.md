# Ilala Smart Referee Management System

A comprehensive web-based referee management platform for the Ilala district, built with PHP, JavaScript, Bootstrap, CSS, and MySQL.

## Features

| Module | Description |
|--------|-------------|
| **User Management** | Create and manage system users with role-based access |
| **Referee Registration** | Online referee registration with approval workflow |
| **Match Management** | Schedule, edit, and track football matches |
| **Referee Assignment** | Assign officials to matches by role |
| **Venue & Map Navigation** | Manage venues with interactive maps and GPS navigation |
| **Arrival Confirmation** | GPS-based referee arrival check-in |
| **Match Report Submission** | Post-match reports with cards and incidents |
| **Video Upload** | Upload match footage for review |
| **Video Assessment** | Score referee performance from video footage |
| **Payment Verification** | Verify and process referee payments |
| **Match Allowance Management** | Configure allowance rates by role and match type |
| **Training Management** | Schedule training programs and track attendance |
| **License Management** | Issue and track referee licenses |
| **Notifications** | Real-time alerts for assignments, payments, and more |
| **Reports & Analytics** | Dashboard charts and system statistics |

## User Roles

- **Admin** — Full system access
- **Referee** — View assignments, confirm arrival, submit reports
- **Assigner** — Manage matches, assignments, and referees
- **Assessor** — Video upload and performance assessment
- **Finance** — Payment verification and allowance management

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Apache or Nginx web server
- PHP extensions: PDO, pdo_mysql, fileinfo

## Installation

### 1. Clone or copy the project

Place the project folder in your web server directory (e.g., `htdocs/referees` or `www/referees`).

### 2. Create the database

```bash
mysql -u root -p < sql/schema.sql
```

Or import `sql/schema.sql` via phpMyAdmin.

### 3. Configure the application

Local development uses built-in defaults in `config/app.php` and `config/database.php`.

For production or custom settings, copy the example file:

```bash
copy config\local.example.php config\local.php
```

Edit `config/local.php` with your site URL and database credentials.

### 4. Set upload directory permissions

Ensure the `uploads/` directory is writable by the web server:

```bash
mkdir -p uploads/videos uploads/documents uploads/avatars
chmod -R 755 uploads/
```

### 5. Access the application

Open your browser and navigate to:

```
http://localhost/referees
```

## Free Hosting Deployment

Recommended free host: [InfinityFree](https://infinityfree.net) (PHP + MySQL + free SSL).

### 1. Upload files

Upload the entire project to your hosting `htdocs` folder using FTP or the file manager.

Include the `assets/vendor/` folder so the app works offline on mobile.

### 2. Create the database

In your hosting control panel:

1. Create a MySQL database and user
2. Open phpMyAdmin
3. Import `sql/schema.sql`

### 3. Configure production settings

On your computer before upload, or directly on the server:

```bash
copy config\local.example.php config\local.php
```

Edit `config/local.php`:

```php
define('APP_URL', 'https://yoursite.infinityfreeapp.com');
define('DB_HOST', 'sqlXXX.infinityfree.com');
define('DB_NAME', 'if0_xxxxx_ilala_referees');
define('DB_USER', 'if0_xxxxx');
define('DB_PASS', 'your_database_password');
```

Do not upload `config/local.php` to public GitHub.

### 4. Enable SSL

Turn on free SSL in your hosting panel, then use `https://` in `APP_URL`.

This is required for:

- Mobile app install (PWA)
- GPS arrival confirmation on phones

### 5. Verify deployment

Open:

```
https://yoursite.infinityfreeapp.com/deploy-check.php
```

Fix any failed checks, then delete `deploy-check.php`.

### 6. Secure the site

1. Log in with `admin` / `password`
2. Change the admin password immediately
3. Confirm `uploads/` is writable

### Free hosting limits

Most free hosts limit uploads to about 10-64 MB. This project sets safer defaults in `config/local.example.php`:

- Video uploads: 32 MB
- Documents: 8 MB

Increase the limits only if your host allows it.

### Optional HTTPS redirect

After SSL works, uncomment the redirect lines in `.htaccess`.

## Default Login

| Field | Value |
|-------|-------|
| Username | `admin` |
| Password | `password` |

## Project Structure

```
referees/
├── assets/
│   ├── css/style.css
│   └── js/app.js
├── config/
│   ├── app.php
│   ├── database.php
│   ├── local.example.php
│   └── local.php
├── includes/
│   ├── auth.php
│   ├── functions.php
│   ├── header.php
│   └── footer.php
├── modules/
│   ├── analytics/
│   ├── allowances/
│   ├── arrival/
│   ├── assignments/
│   ├── licenses/
│   ├── matches/
│   ├── notifications/
│   ├── payments/
│   ├── referees/
│   ├── reports/
│   ├── training/
│   ├── users/
│   ├── venues/
│   └── videos/
├── sql/
│   └── schema.sql
├── uploads/
├── dashboard.php
├── index.php
├── login.php
├── logout.php
├── profile.php
└── register.php
```

## Technology Stack

- **Backend:** PHP 8+
- **Frontend:** Bootstrap 5, JavaScript
- **Database:** MySQL
- **Maps:** Leaflet.js + OpenStreetMap
- **Charts:** Chart.js

## License

This project is developed for the Ilala Referee Management initiative.
