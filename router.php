<?php
declare(strict_types=1);

/**
 * Clean URL Router for PHP Built-In Web Server:
 *   php -S 0.0.0.0:3000 -t public router.php
 */

$uriPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$publicDir = __DIR__ . '/public';
$candidate = realpath($publicDir . $uriPath);

// 1. Serve existing static files directly (CSS, JS, fonts, images, uploads)
if ($uriPath !== '/' && $candidate !== false && str_starts_with($candidate, realpath($publicDir) ?: $publicDir) && is_file($candidate)) {
    if (str_ends_with(strtolower($candidate), '.php')) {
        require $candidate;
        return true;
    }

    $staticPath = str_replace('\\', '/', $candidate);
    $isLongTermAsset = preg_match('#/public/(assets|fonts|uploads)/#i', $staticPath) === 1;
    $isVersionedFrontendAsset = in_array(strtolower(basename($candidate)), ['styles.css', 'admin.css', 'ui.js'], true);
    if ($isLongTermAsset || $isVersionedFrontendAsset) {
        $modifiedAt = filemtime($candidate);
        $cacheDuration = $isLongTermAsset ? 31536000 : 604800;
        $extension = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
        $contentTypes = [
            'css' => 'text/css; charset=UTF-8',
            'js' => 'application/javascript; charset=UTF-8',
            'svg' => 'image/svg+xml',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
        ];
        $contentType = $contentTypes[$extension] ?? mime_content_type($candidate);
        header('Content-Type: ' . ($contentType ?: 'application/octet-stream'));
        header('Cache-Control: public, max-age=' . $cacheDuration . ($isLongTermAsset ? ', immutable' : ''));
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $modifiedAt) . ' GMT');

        $ifModifiedSince = strtotime((string)($_SERVER['HTTP_IF_MODIFIED_SINCE'] ?? ''));
        if ($ifModifiedSince !== false && $ifModifiedSince >= $modifiedAt) {
            http_response_code(304);
            return true;
        }

        header('Content-Length: ' . filesize($candidate));
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
            readfile($candidate);
        }
        return true;
    }

    return false;
}

// 2. Directory index handling (/admin or /admin/)
$trimmed = rtrim($uriPath, '/');
if ($trimmed === '' || $trimmed === '/index') {
    require $publicDir . '/index.php';
    return true;
}

if ($trimmed === '/admin') {
    require $publicDir . '/admin/index.php';
    return true;
}

// 3. Clean URL routes
$staticRoutes = [
    '/about' => '/about.php',
    '/services' => '/services.php',
    '/projects' => '/projects.php',
    '/blog' => '/blog.php',
    '/contact' => '/contact.php',
    '/resume' => '/resume.php',
];

if (isset($staticRoutes[$trimmed])) {
    require $publicDir . $staticRoutes[$trimmed];
    return true;
}

// /projects/{slug}
if (preg_match('#^/projects/([a-zA-Z0-9_-]+)$#', $trimmed, $m)) {
    $_GET['slug'] = $m[1];
    require $publicDir . '/project.php';
    return true;
}

// /blog/{slug}
if (preg_match('#^/blog/([a-zA-Z0-9_-]+)$#', $trimmed, $m)) {
    $_GET['slug'] = $m[1];
    require $publicDir . '/article.php';
    return true;
}

// 4. Fallback if someone omits .php on a top-level file
$phpFallback = $publicDir . $trimmed . '.php';
if (is_file($phpFallback)) {
    require $phpFallback;
    return true;
}

// 5. 404 Page
http_response_code(404);
$activeNav = '';
$isSubpage = true;
require_once __DIR__ . '/includes/repository.php';
$site = get_site_settings();
$pageTitle = '404 Not Found — ' . ($site['name'] ?? 'Jordan Lee');
require __DIR__ . '/includes/header.php';
?>
      <div class="subpage-shell">
        <section class="glass-panel subpage-hero-panel">
          <span class="eyebrow">404 ERROR</span>
          <h1 class="subpage-title">Page Not Found</h1>
          <p class="subpage-lead">The page <code><?= e($uriPath) ?></code> does not exist.</p>
          <div class="subpage-hero-meta">
            <a href="/" class="btn-dark-pill btn-sm-pill"><span>← Return Home</span></a>
          </div>
        </section>
      </div>
<?php
require __DIR__ . '/includes/footer.php';
return true;
