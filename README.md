# Developer Portfolio & Admin CMS

A server-rendered multi-page PHP 8+ portfolio application with a frosted-glass `/admin` CMS backend, powered by **PDO SQLite (zero-config out of the box)** and **MySQL / MariaDB**.

---

## Quick Start (Local / Sandbox)

```bash
cd /home/user/Portfolio
bash start.sh
```
Or directly with PHP 8+:
```bash
php -S 0.0.0.0:3000 -t public router.php
```

- **Public Website:** `http://localhost:3000/`
- **Admin CMS Panel:** `http://localhost:3000/admin/`
  - **Default Username:** `admin`
  - **Default Password:** `Admin@12345` *(changeable inside `/admin/profile.php`)*

---

## Multi-Page Public Routes

| Clean URL | PHP Script | Description |
|---|---|---|
| `/` | `public/index.php` | Multi-section frosted-glass home page |
| `/about` | `public/about.php` | Extended bio, career timeline, skill proficiency matrix & education |
| `/services` | `public/services.php` | Engineering services, deliverables, tech stacks & FAQ |
| `/projects` | `public/projects.php` | Filterable & searchable projects archive |
| `/projects/{slug}` | `public/project.php` | Technical case study (Overview, Architecture, Code, Benchmarks) |
| `/blog` | `public/blog.php` | Engineering blog with category & search filters |
| `/blog/{slug}` | `public/article.php` | Article reader with code blocks & sticky TOC |
| `/contact` | `public/contact.php` | Scope/timeline planner & contact form |
| `/resume.php` | `public/resume.php` | Dynamically generated resume download |

---

## Admin CMS Modules (`/admin`)

- **Dashboard (`/admin/index.php`):** Live counts, unread inbox badge, recent messages, and featured projects.
- **Services & FAQs (`/admin/services.php`):** Create, edit, reorder, hide/show services, deliverables, stack pills, icons, and FAQs.
- **Selected Works (`/admin/projects.php`):** Create/edit case studies, upload screenshots (`public/uploads/`), toggle Home Featured status, and edit metrics/architecture/code snippets/benchmarks.
- **Contact Submissions (`/admin/messages.php`):** Inbox for contact form submissions with `Unread`, `Read`, `Replied`, and `Archived` workflow + 1-click email reply and deletion.
- **Testimonials (`/admin/testimonials.php`):** Manage client quotes, roles, avatar uploads, and Home carousel ordering.
- **Hero, Bio & Settings (`/admin/profile.php`):** Edit global name, role, hero tagline, portrait upload, stats, bios, contact links, contact-form scope and timeline choices, and change admin password.
- **Skills & Process (`/admin/skills.php`):** Manage the Home technology grid, About skill bars, and 5-step engineering workflow.
- **Career & Education (`/admin/experience.php`):** Manage work history and education/certifications.
- **Developer Blog (`/admin/blog.php`):** Publish and edit technical articles with multi-section code blocks.

---

## Switching from SQLite to MySQL / MariaDB

1. Import `database/schema.mysql.sql` (or create an empty UTF8MB4 database—`includes/db.php` auto-migrates and seeds all tables on first load when `DB_DRIVER` is set to `mysql`).
2. Set environment variables or update `includes/config.php`:
   ```php
   define('DB_DRIVER', 'mysql');
   define('MYSQL_HOST', '127.0.0.1');
   define('MYSQL_PORT', '3306');
   define('MYSQL_DATABASE', 'portfolio_cms');
   define('MYSQL_USERNAME', 'your_db_user');
   define('MYSQL_PASSWORD', 'your_db_password');
   ```
3. Point your web server document root to `public/`. For clean URLs on Apache, add an `.htaccess` rewrite to `public/` as needed; the built-in PHP router (`router.php`) handles clean URLs for local development.
