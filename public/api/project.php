<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/repository.php';

header('Content-Type: application/json; charset=utf-8');
$slug = trim((string)($_GET['slug'] ?? ''));
if ($slug === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing slug']);
    exit;
}

$project = get_project_by_slug($slug);
if (!$project) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'error' => 'Project not found']);
    exit;
}

echo json_encode(['ok' => true, 'project' => $project], JSON_UNESCAPED_UNICODE);
