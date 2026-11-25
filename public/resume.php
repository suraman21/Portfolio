<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/repository.php';

$site = get_site_settings();

$resumePath = (string)($site['resume_path'] ?? '');
if (preg_match('#^/uploads/(cv_[a-f0-9]{32})\.(pdf|docx?)$#i', $resumePath, $match) === 1) {
    $uploadDirectory = realpath(UPLOADS_DIR);
    $filePath = realpath(UPLOADS_DIR . DIRECTORY_SEPARATOR . basename($resumePath));
    if ($uploadDirectory !== false && $filePath !== false
        && dirname($filePath) === $uploadDirectory && is_file($filePath) && is_readable($filePath)) {
        $extension = strtolower($match[2]);
        $contentTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        $downloadName = slugify((string)($site['name'] ?? 'developer')) . '-cv.' . $extension;
        header('Content-Type: ' . $contentTypes[$extension]);
        header('Content-Disposition: attachment; filename="' . $downloadName . '"');
        header('Content-Length: ' . (string)filesize($filePath));
        header('X-Content-Type-Options: nosniff');
        readfile($filePath);
        exit;
    }
}

$work = get_experiences_by_type('work');
$edu = get_experiences_by_type('education');
$projects = get_projects(true, false);
$techs = get_tech_items(true);

$name = $site['name'] ?? 'Jordan Lee';
$role = $site['role'] ?? 'Software & Web Developer';
$filename = slugify($name) . '-developer-resume.txt';

header('Content-Type: text/plain; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$lines = [];
$lines[] = strtoupper($name . ' — ' . $role);
$lines[] = 'Location: ' . ($site['location_full'] ?? $site['location'] ?? '');
$lines[] = 'Email: ' . ($site['email'] ?? '') . ' | Phone: ' . ($site['phone'] ?? '');
$lines[] = 'GitHub: ' . ($site['github_url'] ?? '') . ' | LinkedIn: ' . ($site['linkedin_url'] ?? '');
$lines[] = str_repeat('=', 72);
$lines[] = '';
$lines[] = 'PROFESSIONAL SUMMARY';
$lines[] = $site['short_bio'] ?? '';
$lines[] = '';
$lines[] = 'CORE TECHNOLOGIES';
$lines[] = implode(', ', array_map(fn($t) => $t['name'], $techs));
$lines[] = '';
$lines[] = 'ENGINEERING EXPERIENCE';
foreach ($work as $w) {
    $lines[] = "• {$w['title']} — {$w['organization']} ({$w['period']})";
    $lines[] = "  {$w['summary']}";
    foreach ($w['highlights'] as $h) {
        $lines[] = "    - {$h}";
    }
    $lines[] = '';
}
$lines[] = 'SELECTED SOFTWARE PROJECTS';
foreach (array_slice($projects, 0, 5) as $p) {
    $lines[] = "• {$p['title']} [" . implode(', ', $p['stack']) . "]";
    $lines[] = "  {$p['summary']}";
    $lines[] = '';
}
$lines[] = 'EDUCATION & CERTIFICATIONS';
foreach ($edu as $ed) {
    $lines[] = "• {$ed['title']} — {$ed['organization']} ({$ed['period']})";
}

echo implode("\n", $lines) . "\n";
