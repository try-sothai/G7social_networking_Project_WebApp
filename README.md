# G7Social Networking Web App

វេទិកាបណ្តាញសង្គម (Social Networking Platform) សាងសង់ដោយប្រើ PHP MVC Architecture។

---

## តម្រូវការ (Requirements)

| Software | Version |
|----------|---------|
| **WAMP Server** | 3.x ឬ ខ្ពស់ជាងនេះ |
| **PHP** | 7.4+ (ណែនាំ 8.0+) |
| **MySQL** | 5.7+ ឬ MariaDB 10.x |
| **Apache** | 2.4+ |
| **Composer** | 2.x |

---

## ជំហានដំឡើង (Installation Steps)

### ១. ដំឡើង WAMP Server

ទាញយក និងដំឡើង **WampServer** ពីគេហទំព័រ: https://www.wampserver.com

បន្ទាប់ពីដំឡើង ចូរ Start WAMP ហើយអាន Icon ប្រែទៅ **បخضر (Green)**។

---

### ២. Copy Project

Copy folder **`G7social_networking_Project_WebApp`** ទៅដាក់ក្នុង:

```
C:\wamp64\www\
```

ដូច្នេះ path ពេញគឺ:

```
C:\wamp64\www\G7social_networking_Project_WebApp\
```

---

### ៣. ដំឡើង Dependencies ជាមួយ Composer

បើក **Command Prompt** ឬ **Terminal** ហើយ run:

```bash
cd C:\wamp64\www\G7social_networking_Project_WebApp
composer install
```

វានឹង download **PHPMailer** package ចូល folder `vendor/` ។

> ប្រសិនបើ Composer មិនទាន់មាន: ទាញយកពី https://getcomposer.org/download/

---

### ៤. បង្កើត Database

**វិធីទី ១: ប្រើ phpMyAdmin**

1. បើក Browser ចូល: `http://localhost/phpmyadmin`
2. ចូរ login (username: `root`, password: ទទេ)
3. ចុច **"New"** → ដាក់ឈ្មោះ database: `g7social_networking` → ចុច **Create**
4. Select database `g7social_networking` → ចុច tab **Import**
5. ជ្រើស file: `database/g7social_networking.sql` → ចុច **Go**

**វិធីទី ២: ប្រើ MySQL Command Line**

```bash
mysql -u root -p
CREATE DATABASE g7social_networking;
USE g7social_networking;
SOURCE C:/wamp64/www/G7social_networking_Project_WebApp/database/g7social_networking.sql;
```

---

### ៥. កំណត់ Config Database

បើក file: `config/db_config.php`

```php
$host     = "localhost";
$dbname   = "g7social_networking";
$username = "root";
$password = "";   // ដាក់ password ប្រសិនបើ MySQL របស់អ្នកមាន
```

---

### ៦. បង្កើត .env File

**ចំណាំ:** File `.env` មិន include ក្នុង repository ទេ ព្រោះវាមាន Secret Keys។

បង្កើត file ថ្មី `.env` ក្នុង root folder ហើយដាក់ content ដូចខាងក្រោម:

```env
GOOGLE_CLIENT_ID=your_google_client_id_here
GOOGLE_CLIENT_SECRET=your_google_client_secret_here
FACEBOOK_APP_ID=your_facebook_app_id_here
FACEBOOK_APP_SECRET=your_facebook_app_secret_here
FACEBOOK_REDIRECT_URI=http://localhost/G7social_networking_Project_WebApp/app/Controllers/Auth/facebook_auth.php
```

> សុំ API Keys ពីម្ចាស់ project ដើម្បី Login ជាមួយ Google/Facebook ដំណើរការ។

---

### ៧. Enable Apache mod_rewrite

1. ចូល WAMP Tray Icon → **Apache** → **Apache Modules** → Enable **rewrite_module**
2. Restart WAMP

---

### ៨. កំណត់ Upload Size (PHP)

បើក **WAMP** → **PHP** → **php.ini** ហើយកំណត់:

```ini
upload_max_filesize = 200M
post_max_size       = 250M
max_execution_time  = 300
memory_limit        = 256M
```

ឬ file `.htaccess` ក្នុង root project បានកំណត់ជូនរួចហើយ (Apache override)។

---

## ចូលប្រើ Application

បើ WAMP បានចាប់ផ្តើម ហើយ database Import រួច សូម open browser:

```
http://localhost/G7social_networking_Project_WebApp/
```

---

## Project Structure

```
G7social_networking_Project_WebApp/
├── app/
│   ├── Controllers/        ← Business Logic (Auth, Home, Profile, Messages...)
│   ├── Models/             ← Database Queries
│   ├── Views/              ← HTML Templates
│   └── Helpers/            ← Utility Functions
├── config/
│   ├── db_config.php       ← Database Connection & BASE_URL
│   └── env.php             ← Load .env variables
├── database/
│   └── g7social_networking.sql  ← Database Backup
├── public/
│   ├── css/                ← Stylesheets
│   ├── js/                 ← JavaScript files
│   ├── img/                ← Static Images
│   ├── uploads/            ← User uploaded files (photos/videos)
│   └── index.php           ← Entry Point / Router
├── vendor/                 ← Composer packages (PHPMailer)
├── .env                    ← Secret Keys (NOT in git)
├── .htaccess               ← Apache config (Upload limits)
└── composer.json           ← PHP dependencies
```

---

## Features

- Login / Register (Email, Google OAuth, Facebook OAuth)
- News Feed (Posts ជាមួយ Image/Video)
- Dark Mode
- Friend System
- Groups
- Events
- Messages (Chat)
- Explore / Search
- Admin Panel
- Profile & Edit Profile

---

## Troubleshooting

| បញ្ហា | ដំណោះស្រាយ |
|-------|-----------|
| Page Not Found (404) | ពិនិត្យ `mod_rewrite` enabled ហើយ Restart WAMP |
| Connection failed: database | ពិនិត្យ `db_config.php` username/password ត្រឹមត្រូវ |
| Upload file មិនដំណើរការ | ពិនិត្យ `upload_max_filesize` ក្នុង php.ini ឬ `.htaccess` |
| Google/Facebook login error | ពិនិត្យ `.env` file មានទិន្នន័យត្រឹមត្រូវ |
| Vendor folder missing | Run `composer install` ក្នុង project folder |
| `uploads/` permission error | ត្រូវប្រាកដថា folder `public/uploads/` អាច write បាន |

---

## Tech Stack

- **Backend:** PHP 8+ (MVC Pattern)
- **Database:** MySQL / MariaDB
- **Frontend:** HTML, CSS, JavaScript
- **Email:** PHPMailer
- **Auth:** Google OAuth 2.0, Facebook Login API
- **Server:** Apache (via WAMP)
