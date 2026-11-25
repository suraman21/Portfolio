<?php
declare(strict_types=1);

/**
 * ============================================================================
 * PORTFOLIO CMS CONFIGURATION (SQLite Out-of-the-Box + MySQL/MariaDB Ready)
 * ============================================================================
 * - Default: 'sqlite' (zero configuration; auto-creates storage/portfolio.sqlite)
 * - For cPanel / XAMPP / VPS MySQL: change DB_DRIVER to 'mysql', import
 *   `database/schema.mysql.sql`, and fill in DB_MYSQL_* credentials below (or via env).
 */

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('UPLOADS_DIR', PUBLIC_PATH . '/uploads');

define('DB_DRIVER', getenv('DB_DRIVER') ?: 'sqlite'); // 'sqlite' or 'mysql'
define('DB_SQLITE_PATH', STORAGE_PATH . '/portfolio.sqlite');

define('DEFAULT_CONTACT_SCOPE_OPTIONS', [
    'Full-Stack Web',
    'Frontend Systems',
    'APIs & Backend',
    'Cloud & DevOps',
    'Technical Advisory',
]);
define('DEFAULT_CONTACT_TIMELINE_OPTIONS', [
    'Immediate (1–2 wks)',
    'Q2 / Q3 Roadmap',
    'Ongoing Retainer',
]);

define('DB_MYSQL_HOST', getenv('DB_MYSQL_HOST') ?: '127.0.0.1');
define('DB_MYSQL_PORT', getenv('DB_MYSQL_PORT') ?: '3306');
define('DB_MYSQL_NAME', getenv('DB_MYSQL_NAME') ?: 'portfolio_cms');
define('DB_MYSQL_USER', getenv('DB_MYSQL_USER') ?: 'root');
define('DB_MYSQL_PASS', getenv('DB_MYSQL_PASS') ?: '');

// Default initial admin credentials (seeded on first boot; change inside /admin/profile.php)
define('DEFAULT_ADMIN_USER', 'admin');
define('DEFAULT_ADMIN_PASS', 'Admin@12345');

// Show helpful login hint box on /admin/login.php (set to false in public production if desired)
define('SHOW_DEFAULT_LOGIN_HINT', true);

// Maximum image upload size (5 MB)
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);
// Maximum CV document upload size (10 MB)
define('MAX_CV_UPLOAD_BYTES', 10 * 1024 * 1024);
