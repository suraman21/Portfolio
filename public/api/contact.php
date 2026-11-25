<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/repository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

// Accept both multipart/form-data / urlencoded POST and raw application/json
$input = $_POST;
if (empty($input)) {
    $raw = file_get_contents('php://input') ?: '';
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}

$name = trim((string)($input['name'] ?? ''));
$email = trim((string)($input['email'] ?? ''));
$project = trim((string)($input['project'] ?? 'General Inquiry'));
$timeline = trim((string)($input['timeline'] ?? ''));
$message = trim((string)($input['message'] ?? ''));
$site = get_site_settings();
$allowedScopes = array_merge(
    configured_contact_options($site, 'contact_scope_options', DEFAULT_CONTACT_SCOPE_OPTIONS),
    array_map(static fn(array $service): string => (string)$service['title'], get_services(true)),
    ['General Inquiry']
);
$allowedTimelines = configured_contact_options($site, 'contact_timeline_options', DEFAULT_CONTACT_TIMELINE_OPTIONS);
$invalidOptions = !in_array($project, $allowedScopes, true)
    || ($timeline !== '' && !in_array($timeline, $allowedTimelines, true));

$wantsJson = str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')
    || str_contains((string)($_SERVER['CONTENT_TYPE'] ?? ''), 'application/json');

if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '' || $invalidOptions) {
    if ($wantsJson) {
        http_response_code(422);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'Please enter a valid name, email address, and message.']);
        exit;
    }
    redirect_to('/contact?status=invalid');
}

save_contact_message([
    'name' => $name,
    'email' => $email,
    'project' => $project,
    'timeline' => $timeline,
    'message' => $message,
]);

$ownerName = explode(' ', trim($site['name'] ?? 'Jordan'))[0] ?: 'there';
$msg = "Thank you! {$ownerName} has received your message and will respond within 24 hours.";

if ($wantsJson) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => true, 'message' => $msg]);
    exit;
}

redirect_to('/contact?status=sent');
