<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Safe HTML output escaping helper (prevents XSS).
 */
function e(mixed $value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function optimized_image_path(string $path): string
{
    $assetPath = parse_url($path, PHP_URL_PATH);
    if (!is_string($assetPath) || !str_starts_with($assetPath, '/assets/')) {
        return $path;
    }

    $extension = strtolower(pathinfo($assetPath, PATHINFO_EXTENSION));
    if (!in_array($extension, ['jpg', 'jpeg', 'png'], true)) {
        return $path;
    }

    $webpPath = substr($assetPath, 0, strrpos($assetPath, '.')) . '.webp';
    $webpFile = dirname(__DIR__) . '/public' . $webpPath;
    if (!is_file($webpFile)) {
        return $path;
    }

    $query = parse_url($path, PHP_URL_QUERY);
    return $webpPath . (is_string($query) ? '?' . $query : '');
}

/**
 * Ensure session is started with iframe-friendly & secure cookie attributes.
 */
function ensure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');

    session_name('PORTFOLIO_SESSID');
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => $isHttps ? 'None' : 'Lax',
    ]);
    session_start();
}

/**
 * Generate or return current session CSRF token.
 */
function csrf_token(): string
{
    ensure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render hidden CSRF input field for forms.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '" />';
}

/**
 * Validate submitted POST CSRF token; abort with 403 if invalid.
 */
function verify_csrf(): void
{
    ensure_session();
    $submitted = (string)($_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $expected = (string)($_SESSION['csrf_token'] ?? '');
    if ($expected === '' || !hash_equals($expected, $submitted)) {
        http_response_code(403);
        exit('Security check failed (invalid or expired CSRF token). Please go back and refresh the page.');
    }
}

/**
 * Flash messaging for Admin PRG (Post-Redirect-Get) actions.
 */
function set_flash(string $type, string $message): void
{
    ensure_session();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function pull_flash(): ?array
{
    ensure_session();
    if (!isset($_SESSION['flash'])) {
        return null;
    }
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

function get_flash(): ?array
{
    return pull_flash();
}

/**
 * Redirect helper.
 */
function redirect_to(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Convert text to clean URL slug.
 */
function slugify(string $text): string
{
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    $text = trim($text, '-');
    return $text !== '' ? $text : 'item-' . substr(bin2hex(random_bytes(3)), 0, 6);
}

/**
 * Parse newline-separated or comma-separated lines into clean array.
 */
function lines_to_array(string $raw): array
{
    $parts = preg_split('/\r\n|\r|\n/', $raw) ?: [];
    $out = [];
    foreach ($parts as $line) {
        $trimmed = trim($line);
        if ($trimmed !== '') {
            $out[] = $trimmed;
        }
    }
    return $out;
}

function configured_contact_options(array $settings, string $key, array $defaults): array
{
    $options = lines_to_array((string)($settings[$key] ?? ''));
    return $options !== [] ? $options : $defaults;
}

function csv_to_array(string $raw): array
{
    $parts = explode(',', $raw);
    $out = [];
    foreach ($parts as $item) {
        $trimmed = trim($item);
        if ($trimmed !== '') {
            $out[] = $trimmed;
        }
    }
    return $out;
}

function json_decode_array(?string $json): array
{
    if (!$json) {
        return [];
    }
    $decoded = json_decode($json, true);
    return is_array($decoded) ? $decoded : [];
}

/**
 * Secure image upload handler for Projects, Avatars, and Profile Portrait.
 * Returns web path (e.g. '/uploads/img_a1b2c3.webp') or null if no file uploaded.
 * Throws RuntimeException on validation failure.
 */
function handle_image_upload(string $fieldName, ?string $prefix = 'img'): ?string
{
    if (empty($_FILES[$fieldName]) || ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fieldName];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('File upload failed with error code ' . (int)$file['error']);
    }

    if ($file['size'] > MAX_UPLOAD_BYTES) {
        throw new RuntimeException('Uploaded image exceeds the 5 MB size limit.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    if (!isset($allowedMimes[$mime])) {
        throw new RuntimeException('Unsupported image format. Allowed: JPG, PNG, WEBP, GIF.');
    }

    if (!is_dir(UPLOADS_DIR)) {
        mkdir(UPLOADS_DIR, 0755, true);
    }

    $safePrefix = preg_replace('/[^a-z0-9_-]/i', '', (string)$prefix) ?: 'img';
    $ext = $allowedMimes[$mime];
    $filename = $safePrefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
    $destPath = UPLOADS_DIR . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        throw new RuntimeException('Could not save uploaded file.');
    }

    return '/uploads/' . $filename;
}

/**
 * Securely store a public CV document and return its randomized web path.
 */
function handle_cv_upload(string $fieldName): ?string
{
    if (empty($_FILES[$fieldName]) || ($_FILES[$fieldName]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES[$fieldName];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('CV upload failed with error code ' . (int)$file['error']);
    }
    if (($file['size'] ?? 0) <= 0 || $file['size'] > MAX_CV_UPLOAD_BYTES) {
        throw new RuntimeException('CV must be a non-empty file no larger than 10 MB.');
    }

    $extension = strtolower(pathinfo((string)($file['name'] ?? ''), PATHINFO_EXTENSION));
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file((string)$file['tmp_name']);
    $handle = fopen((string)$file['tmp_name'], 'rb');
    if ($handle === false) {
        throw new RuntimeException('Could not read the uploaded CV.');
    }
    $signature = fread($handle, 8);
    fclose($handle);

    $allowed = match ($extension) {
        'pdf' => $mime === 'application/pdf' && str_starts_with((string)$signature, '%PDF-'),
        'doc' => in_array($mime, ['application/msword', 'application/x-ole-storage', 'application/CDFV2'], true)
            && $signature === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1",
        'docx' => in_array($mime, [
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/zip',
            'application/x-zip',
            'application/x-zip-compressed',
        ], true) && str_starts_with((string)$signature, "PK\x03\x04"),
        default => false,
    };
    if (!$allowed) {
        throw new RuntimeException('Unsupported or invalid CV file. Upload a valid PDF, DOC, or DOCX document.');
    }

    if (!is_dir(UPLOADS_DIR) && !mkdir(UPLOADS_DIR, 0755, true) && !is_dir(UPLOADS_DIR)) {
        throw new RuntimeException('Could not create the CV upload directory.');
    }

    $filename = 'cv_' . bin2hex(random_bytes(16)) . '.' . $extension;
    if (!move_uploaded_file((string)$file['tmp_name'], UPLOADS_DIR . '/' . $filename)) {
        throw new RuntimeException('Could not save the uploaded CV.');
    }

    return '/uploads/' . $filename;
}

function tech_icon_options(): array
{
    return [
        'react' => 'React', 'nextjs' => 'Next.js', 'typescript' => 'TypeScript',
        'nodejs' => 'Node.js', 'python' => 'Python', 'postgresql' => 'PostgreSQL',
        'docker' => 'Docker', 'git' => 'Git', 'php' => 'PHP', 'aws' => 'AWS',
        'angular' => 'Angular', 'azure' => 'Microsoft Azure', 'css' => 'CSS',
        'express' => 'Express.js', 'fastapi' => 'FastAPI', 'figma' => 'Figma',
        'github' => 'GitHub', 'graphql' => 'GraphQL', 'html' => 'HTML',
        'javascript' => 'JavaScript', 'jest' => 'Jest', 'kubernetes' => 'Kubernetes',
        'laravel' => 'Laravel', 'linux' => 'Linux', 'mongodb' => 'MongoDB',
        'mysql' => 'MySQL', 'playwright' => 'Playwright', 'redis' => 'Redis',
        'sqlite' => 'SQLite', 'tailwind' => 'Tailwind CSS', 'vue' => 'Vue.js',
        'vite' => 'Vite', 'code' => 'Custom / Code',
    ];
}

function service_icon_options(): array
{
    return [
        'browser_code' => 'Browser Code',
        'ui_grid' => 'UI Grid Systems',
        'api_servers' => 'API Servers',
        'cloud_ops' => 'Cloud & DevOps',
        'mobile_app' => 'Mobile Applications',
        'database' => 'Database Engineering',
        'security' => 'Security & Reliability',
        'analytics' => 'Analytics & Data',
        'testing' => 'Testing & Quality',
        'integrations' => 'Integrations & Automation',
    ];
}

/**
 * Curated SVG icon presets for Services, Tech Stack tiles, and Process steps.
 */
function render_service_svg(string $iconKey, string $tileClass = 'tile-violet'): string
{
    $stroke = $tileClass === 'tile-amber' ? '#996527' : '#ffffff';
    return match ($iconKey) {
        'browser_code' => '<svg viewBox="0 0 24 24" width="21" height="21" fill="none" aria-hidden="true"><rect x="3.5" y="4.5" width="17" height="15" rx="3.5" stroke="' . $stroke . '" stroke-width="1.7"/><path d="M3.5 9H20.5" stroke="' . $stroke . '" stroke-width="1.5"/><path d="M9.5 12.2L7.3 14.2L9.5 16.2M14.5 12.2L16.7 14.2L14.5 16.2" stroke="' . $stroke . '" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'ui_grid' => '<svg viewBox="0 0 24 24" width="21" height="21" fill="none" aria-hidden="true"><rect x="4" y="4" width="7" height="7" rx="2" stroke="' . $stroke . '" stroke-width="1.7"/><rect x="13" y="4" width="7" height="7" rx="2" stroke="' . $stroke . '" stroke-width="1.7"/><rect x="4" y="13" width="16" height="7" rx="2" stroke="' . $stroke . '" stroke-width="1.7"/></svg>',
        'api_servers' => '<svg viewBox="0 0 24 24" width="21" height="21" fill="none" aria-hidden="true"><rect x="4" y="5" width="16" height="5.5" rx="2" stroke="' . $stroke . '" stroke-width="1.7"/><rect x="4" y="13.5" width="16" height="5.5" rx="2" stroke="' . $stroke . '" stroke-width="1.7"/><circle cx="7.5" cy="7.75" r="1" fill="' . $stroke . '"/><circle cx="7.5" cy="16.25" r="1" fill="' . $stroke . '"/></svg>',
        'cloud_ops' => '<svg viewBox="0 0 24 24" width="21" height="21" fill="none" aria-hidden="true"><path d="M7.5 18H16.8C19.1 18 20.8 16.3 20.8 14.2C20.8 12.2 19.3 10.6 17.3 10.4C16.6 7.6 14.1 5.6 11.1 5.6C7.6 5.6 4.8 8.3 4.8 11.7C3.8 12.3 3.2 13.4 3.2 14.6C3.2 16.5 4.8 18 6.8 18H7.5Z" stroke="' . $stroke . '" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'mobile_app' => '<svg viewBox="0 0 24 24" width="21" height="21" fill="none" aria-hidden="true"><rect x="7" y="3" width="10" height="18" rx="2.5" stroke="' . $stroke . '" stroke-width="1.7"/><path d="M10 6h4M11 18h2" stroke="' . $stroke . '" stroke-width="1.6" stroke-linecap="round"/></svg>',
        'database' => '<svg viewBox="0 0 24 24" width="21" height="21" fill="none" aria-hidden="true"><ellipse cx="12" cy="6" rx="7.5" ry="3" stroke="' . $stroke . '" stroke-width="1.7"/><path d="M4.5 6v12c0 1.7 3.4 3 7.5 3s7.5-1.3 7.5-3V6M4.5 12c0 1.7 3.4 3 7.5 3s7.5-1.3 7.5-3" stroke="' . $stroke . '" stroke-width="1.7"/></svg>',
        'security' => '<svg viewBox="0 0 24 24" width="21" height="21" fill="none" aria-hidden="true"><path d="M12 3.5l7 3v5.2c0 4.1-2.8 7.4-7 8.8-4.2-1.4-7-4.7-7-8.8V6.5l7-3Z" stroke="' . $stroke . '" stroke-width="1.7" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke="' . $stroke . '" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'analytics' => '<svg viewBox="0 0 24 24" width="21" height="21" fill="none" aria-hidden="true"><path d="M4 19.5h16M6.5 16v-4M11 16V7M15.5 16v-6M20 16V4.5" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round"/></svg>',
        'testing' => '<svg viewBox="0 0 24 24" width="21" height="21" fill="none" aria-hidden="true"><path d="M8 4h8M10 4v6l-5 8.3A1.8 1.8 0 0 0 6.5 21h11a1.8 1.8 0 0 0 1.5-2.7L14 10V4M8 15h8" stroke="' . $stroke . '" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="m10 17 1.4 1.4L14.5 15" stroke="' . $stroke . '" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'integrations' => '<svg viewBox="0 0 24 24" width="21" height="21" fill="none" aria-hidden="true"><circle cx="7" cy="7" r="3" stroke="' . $stroke . '" stroke-width="1.7"/><circle cx="17" cy="17" r="3" stroke="' . $stroke . '" stroke-width="1.7"/><path d="M10 7h3a4 4 0 0 1 4 4v3M14 17h-3a4 4 0 0 1-4-4v-2" stroke="' . $stroke . '" stroke-width="1.7" stroke-linecap="round"/></svg>',
        default => '<svg viewBox="0 0 24 24" width="21" height="21" fill="none" aria-hidden="true"><path d="M8 8L4 12L8 16M16 8L20 12L16 16M13.5 6L10.5 18" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>',
    };
}

function render_tech_tile_icon(string $iconKey, string $name): array
{
    return match ($iconKey) {
        'react' => [
            'wrapClass' => 'tech-white-tile',
            'style' => '',
            'svg' => '<svg viewBox="0 0 32 32" width="26" height="26" fill="none" aria-hidden="true"><circle cx="16" cy="16" r="2.3" fill="#087EA4"/><ellipse cx="16" cy="16" rx="10.5" ry="4.2" stroke="#087EA4" stroke-width="1.6"/><ellipse cx="16" cy="16" rx="10.5" ry="4.2" transform="rotate(60 16 16)" stroke="#087EA4" stroke-width="1.6"/><ellipse cx="16" cy="16" rx="10.5" ry="4.2" transform="rotate(120 16 16)" stroke="#087EA4" stroke-width="1.6"/></svg>',
        ],
        'nextjs' => [
            'wrapClass' => '',
            'style' => 'background:#0d0f18;',
            'svg' => '<svg viewBox="0 0 32 32" width="24" height="24" fill="none" aria-hidden="true"><path d="M10.5 22V10H13L21.8 21.8M21.5 10V18.5" stroke="#ffffff" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        ],
        'typescript' => [
            'wrapClass' => '',
            'style' => 'background:#3178C6;',
            'svg' => '<svg viewBox="0 0 32 32" width="28" height="28" aria-hidden="true"><text x="6.5" y="21.5" font-family="Inter, sans-serif" font-size="14" font-weight="800" fill="#ffffff">TS</text></svg>',
        ],
        'nodejs' => [
            'wrapClass' => 'tech-white-tile',
            'style' => '',
            'svg' => '<svg viewBox="0 0 32 32" width="26" height="26" fill="none" aria-hidden="true"><polygon points="16,4.8 25.5,10.3 25.5,21.7 16,27.2 6.5,21.7 6.5,10.3" fill="#339933"/><text x="9.6" y="19.4" font-family="Inter, sans-serif" font-size="9.5" font-weight="800" fill="#ffffff">JS</text></svg>',
        ],
        'python' => [
            'wrapClass' => 'tech-white-tile',
            'style' => '',
            'svg' => '<svg viewBox="0 0 32 32" width="26" height="26" fill="none" aria-hidden="true"><path d="M15.8 5.5C11.5 5.5 11.8 7.4 11.8 7.4V9.8H16V10.7H9.8C9.8 10.7 6.8 10.4 6.8 15C6.8 19.6 9.4 19.4 9.4 19.4H11.1V17.1C11.1 17.1 11 14.5 13.7 14.5H18.1C18.1 14.5 20.5 14.5 20.5 12.2V7.9C20.5 7.9 20.8 5.5 15.8 5.5Z" fill="#3776AB"/><path d="M16.2 26.5C20.5 26.5 20.2 24.6 20.2 24.6V22.2H16V21.3H22.2C22.2 21.3 25.2 21.6 25.2 17C25.2 12.4 22.6 12.6 22.6 12.6H20.9V14.9C20.9 14.9 21 17.5 18.3 17.5H13.9C13.9 17.5 11.5 17.5 11.5 19.8V24.1C11.5 24.1 11.2 26.5 16.2 26.5Z" fill="#FFD43B"/></svg>',
        ],
        'postgresql' => [
            'wrapClass' => '',
            'style' => 'background:#336791;',
            'svg' => '<svg viewBox="0 0 32 32" width="24" height="24" fill="none" aria-hidden="true"><ellipse cx="16" cy="9.5" rx="7.5" ry="3.2" stroke="#ffffff" stroke-width="1.9"/><path d="M8.5 9.5V16C8.5 17.8 11.86 19.2 16 19.2C20.14 19.2 23.5 17.8 23.5 16V9.5" stroke="#ffffff" stroke-width="1.9"/><path d="M8.5 16V22.5C8.5 24.3 11.86 25.7 16 25.7C20.14 25.7 23.5 24.3 23.5 22.5V16" stroke="#ffffff" stroke-width="1.9"/></svg>',
        ],
        'docker' => [
            'wrapClass' => '',
            'style' => 'background:#2496ED;',
            'svg' => '<svg viewBox="0 0 32 32" width="26" height="26" fill="none" aria-hidden="true"><rect x="8" y="13" width="3.2" height="3" rx="0.5" fill="#ffffff"/><rect x="12" y="13" width="3.2" height="3" rx="0.5" fill="#ffffff"/><rect x="16" y="13" width="3.2" height="3" rx="0.5" fill="#ffffff"/><rect x="12" y="9.2" width="3.2" height="3" rx="0.5" fill="#ffffff"/><rect x="16" y="9.2" width="3.2" height="3" rx="0.5" fill="#ffffff"/><path d="M6 17.2H22.2C23.4 17.2 24.4 16.5 25 15.5C25.9 15.8 26.8 16.5 27 17.5C25.8 18.8 24.3 19.2 22.8 19.2C21.8 22.2 18.8 23.8 14.5 23.8C9.8 23.8 6.8 21.4 6 17.2Z" fill="#ffffff"/></svg>',
        ],
        'git' => [
            'wrapClass' => 'tech-white-tile',
            'style' => '',
            'svg' => '<svg viewBox="0 0 32 32" width="26" height="26" fill="none" aria-hidden="true"><rect x="16" y="5.2" width="15.2" height="15.2" rx="2.8" transform="rotate(45 16 5.2)" fill="#F05032"/><path d="M13 10.5L19 16.5M16 13.5V20.5" stroke="#ffffff" stroke-width="1.9" stroke-linecap="round"/><circle cx="16" cy="13.5" r="1.7" fill="#ffffff"/><circle cx="19" cy="16.5" r="1.7" fill="#ffffff"/><circle cx="16" cy="20.5" r="1.7" fill="#ffffff"/></svg>',
        ],
        'php' => [
            'wrapClass' => '',
            'style' => 'background:#777BB4;',
            'svg' => '<svg viewBox="0 0 32 32" width="28" height="28" aria-hidden="true"><text x="4.5" y="20.2" font-family="Inter, sans-serif" font-size="11" font-weight="800" fill="#ffffff">PHP</text></svg>',
        ],
        default => [
            'wrapClass' => in_array($iconKey, ['mysql', 'mongodb', 'sqlite'], true) ? 'tech-white-tile' : '',
            'style' => 'background:' . ([
                'angular' => '#dd0031', 'aws' => '#232f3e', 'azure' => '#0078d4',
                'css' => '#663399', 'express' => '#303030', 'fastapi' => '#009688',
                'figma' => '#a259ff', 'github' => '#24292f', 'graphql' => '#e10098',
                'html' => '#e34f26', 'javascript' => '#d8b900', 'jest' => '#99425b',
                'kubernetes' => '#326ce5', 'laravel' => '#ff2d20', 'linux' => '#333333',
                'mongodb' => '#00684a', 'mysql' => '#00758f', 'redis' => '#a41e11',
                'sqlite' => '#176099', 'tailwind' => '#06b6d4', 'vue' => '#42b883',
                'vite' => '#646cff', 'playwright' => '#2ead33',
            ][$iconKey] ?? '#0369a1') . ';',
            'svg' => '<svg viewBox="0 0 32 32" width="28" height="28" aria-hidden="true"><text x="16" y="21" text-anchor="middle" font-family="Inter, sans-serif" font-size="' . (strlen($label = strtoupper(substr($name, 0, in_array($iconKey, ['aws', 'css', 'html', 'php'], true) ? 3 : 2))) > 2 ? '10' : '13') . '" font-weight="800" fill="#ffffff">' . e($label) . '</text></svg>',
        ],
    };
}

function render_process_svg(string $iconKey): string
{
    return match ($iconKey) {
        'arch' => '<svg viewBox="0 0 22 22" width="18" height="18" fill="none" aria-hidden="true"><circle cx="8" cy="7.5" r="2.2" stroke="#0369a1" stroke-width="1.6"/><circle cx="14" cy="7.5" r="2.2" stroke="#06b6d4" stroke-width="1.6"/><circle cx="8" cy="14.2" r="2.2" stroke="#0369a1" stroke-width="1.6"/><path d="M12.2 12.4L16.2 16.4M15.8 12.4L12.2 16" stroke="#535076" stroke-width="1.6" stroke-linecap="round"/></svg>',
        'proto' => '<svg viewBox="0 0 22 22" width="18" height="18" fill="none" aria-hidden="true"><circle cx="7.6" cy="7.6" r="2" stroke="#0ea5e9" stroke-width="1.6"/><circle cx="14.4" cy="14.4" r="2" stroke="#0ea5e9" stroke-width="1.6"/><path d="M13 6.2L15.8 9M6.2 13L9 15.8M9.3 9.3L12.7 12.7" stroke="#06b6d4" stroke-width="1.6" stroke-linecap="round"/></svg>',
        'frontend' => '<svg viewBox="0 0 22 22" width="18" height="18" fill="none" aria-hidden="true"><path d="M8.5 7.5L5.2 11L8.5 14.5M13.5 7.5L16.8 11L13.5 14.5" stroke="#0284c7" stroke-width="1.65" stroke-linecap="round" stroke-linejoin="round"/></svg>',
        'backend' => '<svg viewBox="0 0 22 22" width="18" height="18" fill="none" aria-hidden="true"><rect x="5.5" y="5.8" width="11" height="4.2" rx="1.2" fill="#0284c7"/><rect x="5.5" y="12" width="11" height="4.2" rx="1.2" stroke="#5aa9e6" stroke-width="1.5"/></svg>',
        default => '<svg viewBox="0 0 22 22" width="18" height="18" fill="none" aria-hidden="true"><rect x="5.4" y="5.4" width="4.6" height="4.6" rx="1.3" stroke="#535678" stroke-width="1.55"/><rect x="12" y="5.4" width="4.6" height="4.6" rx="1.3" stroke="#535678" stroke-width="1.55"/><rect x="5.4" y="12" width="4.6" height="4.6" rx="1.3" stroke="#535678" stroke-width="1.55"/><rect x="12" y="12" width="4.6" height="4.6" rx="1.3" stroke="#535678" stroke-width="1.55"/></svg>',
    };
}
