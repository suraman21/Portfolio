# Aarav Singh — Software & Web Developer PHP Portfolio & Admin CMS

A true **server-rendered multi-page PHP 8+ portfolio application** with a **full pearlescent frosted-glass `/admin` CMS backend**, powered by **PDO SQLite (zero-config out of the box)** and **MySQL / MariaDB**.

---

## Quick Start (Local / Sandbox)

```bash
cd /home/user/portfolio
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
| `/` | `public/index.php` | 10-Section Pearlescent Frosted-Glass Home Page |
| `/about` | `public/about.php` | Extended Bio, Career Timeline, Skill Proficiency Matrix & Education |
| `/services` | `public/services.php` | Full-Stack Engineering Services, Deliverables, Tech Stacks & FAQ |
| `/projects` | `public/projects.php` | Filterable & Searchable Software Projects Archive |
| `/projects/{slug}` | `public/project.php` | 4-Tab Technical Case Study (Overview, Architecture, Code, Benchmarks) |
| `/blog` | `public/blog.php` | Developer Engineering Blog with Category & Search Filters |
| `/blog/{slug}` | `public/article.php` | Full Article Reader with Syntax Code Blocks & Sticky TOC |
| `/contact` | `public/contact.php` | Interactive Scope/Timeline Planner & Contact Form |
| `/resume.php` | `public/resume.php` | Dynamically Generated Plain-Text Developer Resume Download |

---

## Admin CMS Modules (`/admin`)

- **Dashboard (`/admin/index.php`):** Live counts, unread inbox badge, recent messages, and featured projects.
- **Services & FAQs (`/admin/services.php`):** Create, edit, reorder, hide/show services, deliverables, stack pills, icons, and FAQs.
- **Selected Works (`/admin/projects.php`):** Create/edit portfolio case studies, upload project screenshots (`public/assets/uploads/`), toggle Home Featured status, and edit metrics/architecture/code snippets/benchmarks.
- **Contact Submissions (`/admin/messages.php`):** Full inbox for contact form submissions with `Unread`, `Read`, `Replied`, and `Archived` workflow + 1-click email reply and deletion.
- **Testimonials (`/admin/testimonials.php`):** Manage client quotes, roles, avatar uploads, and Home carousel ordering.
- **Hero, Bio & Settings (`/admin/profile.php`):** Edit global developer name, role, hero tagline, portrait cutout upload, stats, bios, contact links, contact-form scope and timeline choices, and change admin password.
- **Skills & Process (`/admin/skills.php`):** Manage the 8-tile Home technology grid, About skill bars, and 5-step engineering workflow.
- **Career & Education (`/admin/experience.php`):** Manage resume work history and education/certifications.
- **Developer Blog (`/admin/blog.php`):** Publish and edit technical articles with multi-section code blocks.

---

## Switching from SQLite to MySQL / MariaDB (cPanel / XAMPP / VPS)

1. Import `database/schema.mysql.sql` (or simply create an empty UTF8MB4 database—`includes/db.php` auto-migrates and seeds all 11 tables on first load!).
2. Set environment variables or update `includes/config.php`:
   ```php
   define('DB_DRIVER', 'mysql');
   define('MYSQL_HOST', '127.0.0.1');
   define('MYSQL_PORT', '3306');
   define('MYSQL_DATABASE', 'aarav_portfolio');
   define('MYSQL_USERNAME', 'your_db_user');
   define('MYSQL_PASSWORD', 'your_db_password');
   ```
3. Point Apache/Nginx document root to `public/` (Apache `.htaccess` clean-URL rules are preconfigured in `public/.htaccess`).
