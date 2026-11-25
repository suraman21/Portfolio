<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/* ==========================================================================
   SITE SETTINGS
   ========================================================================== */

function get_site_settings(): array
{
    $pdo = get_db();
    $rows = $pdo->query('SELECT setting_key, setting_value FROM site_settings')->fetchAll();
    $out = [];
    foreach ($rows as $r) {
        $out[$r['setting_key']] = $r['setting_value'];
    }
    if (
        isset($out['name'], $out['copyright'])
        && preg_match('/^© (\d{4}) .+(\. All rights reserved\.)$/u', $out['copyright'], $matches) === 1
    ) {
        $out['copyright'] = '© ' . $matches[1] . ' ' . $out['name'] . $matches[2];
    }
    if (empty($out['extended_bio'])) {
        $parts = array_filter([
            $out['extended_bio_1'] ?? '',
            $out['extended_bio_2'] ?? '',
            $out['extended_bio_3'] ?? '',
        ], fn($v) => trim((string)$v) !== '');
        $out['extended_bio'] = implode("\n\n", $parts);
    }
    return $out;
}

function upsert_site_setting(string $key, string $value): void
{
    $pdo = get_db();
    if (DB_DRIVER === 'mysql') {
        $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)');
        $stmt->execute([$key, $value]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value');
        $stmt->execute([$key, $value]);
    }
}

function update_site_settings(array $pairs): void
{
    foreach ($pairs as $k => $v) {
        upsert_site_setting((string)$k, (string)$v);
    }
}

function get_dashboard_counts(): array
{
    $pdo = get_db();
    return [
        'services' => (int)$pdo->query('SELECT COUNT(*) FROM services')->fetchColumn(),
        'projects' => (int)$pdo->query('SELECT COUNT(*) FROM projects')->fetchColumn(),
        'messages_total' => (int)$pdo->query('SELECT COUNT(*) FROM contact_messages')->fetchColumn(),
        'messages_unread' => (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn(),
        'testimonials' => (int)$pdo->query('SELECT COUNT(*) FROM testimonials')->fetchColumn(),
        'blog_posts' => (int)$pdo->query('SELECT COUNT(*) FROM blog_posts')->fetchColumn(),
    ];
}

/* ==========================================================================
   SERVICES
   ========================================================================== */

function normalize_service_row(array $r): array
{
    $r['deliverables'] = json_decode_array($r['deliverables_json'] ?? '[]');
    $r['stack'] = json_decode_array($r['stack_json'] ?? '[]');
    $r['description'] = $r['overview'] ?? '';
    $r['metrics_badge'] = $r['metrics'] ?? '';
    return $r;
}

function get_services(bool $onlyActive = true): array
{
    $pdo = get_db();
    $sql = $onlyActive
        ? 'SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        : 'SELECT * FROM services ORDER BY sort_order ASC, id ASC';
    $rows = $pdo->query($sql)->fetchAll();
    return array_map('normalize_service_row', $rows);
}

function get_service_by_id(int $id): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM services WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ? normalize_service_row($row) : null;
}

function save_service(array $data, ?int $id = null): int
{
    $pdo = get_db();
    $slug = slugify((string)($data['slug'] ?? $data['title'] ?? 'service'));
    $deliverablesJson = json_encode(array_values($data['deliverables'] ?? []), JSON_UNESCAPED_UNICODE);
    $stackJson = json_encode(array_values($data['stack'] ?? []), JSON_UNESCAPED_UNICODE);

    if ($id !== null && $id > 0) {
        $stmt = $pdo->prepare('
            UPDATE services
            SET slug = ?, title = ?, tile_class = ?, icon_key = ?, short_desc = ?, tagline = ?,
                overview = ?, deliverables_json = ?, metrics = ?, stack_json = ?, sort_order = ?, is_active = ?
            WHERE id = ?
        ');
        $stmt->execute([
            $slug,
            (string)($data['title'] ?? ''),
            (string)($data['tile_class'] ?? 'tile-violet'),
            (string)($data['icon_key'] ?? 'browser_code'),
            (string)($data['short_desc'] ?? ''),
            (string)($data['tagline'] ?? ''),
            (string)($data['description'] ?? $data['overview'] ?? ''),
            $deliverablesJson,
            (string)($data['metrics_badge'] ?? $data['metrics'] ?? ''),
            $stackJson,
            (int)($data['sort_order'] ?? 0),
            (int)($data['is_active'] ?? 1),
            $id,
        ]);
        return $id;
    }

    // Ensure unique slug on insert
    $chk = $pdo->prepare('SELECT COUNT(*) FROM services WHERE slug = ?');
    $chk->execute([$slug]);
    if ((int)$chk->fetchColumn() > 0) {
        $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
    }

    $stmt = $pdo->prepare('
        INSERT INTO services (slug, title, tile_class, icon_key, short_desc, tagline, overview, deliverables_json, metrics, stack_json, sort_order, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $slug,
        (string)($data['title'] ?? ''),
        (string)($data['tile_class'] ?? 'tile-violet'),
        (string)($data['icon_key'] ?? 'browser_code'),
        (string)($data['short_desc'] ?? ''),
        (string)($data['tagline'] ?? ''),
        (string)($data['description'] ?? $data['overview'] ?? ''),
        $deliverablesJson,
        (string)($data['metrics_badge'] ?? $data['metrics'] ?? ''),
        $stackJson,
        (int)($data['sort_order'] ?? 0),
        (int)($data['is_active'] ?? 1),
    ]);
    return (int)$pdo->lastInsertId();
}

function delete_service(int $id): void
{
    $pdo = get_db();
    $stmt = $pdo->prepare('DELETE FROM services WHERE id = ?');
    $stmt->execute([$id]);
}

/* ==========================================================================
   PROJECTS / SELECTED WORKS
   ========================================================================== */

function normalize_project_row(array $r): array
{
    $r['stack'] = json_decode_array($r['stack_json'] ?? '[]');
    $r['metrics'] = json_decode_array($r['metrics_json'] ?? '[]');

    $rawArch = json_decode_array($r['arch_json'] ?? '[]');
    if (isset($rawArch['client']) || isset($rawArch['gateway']) || isset($rawArch['services']) || isset($rawArch['data'])) {
        $nodes = [];
        if (!empty($rawArch['client'])) {
            $nodes[] = ['layer' => 'Client Layer', 'name' => (string)$rawArch['client'], 'detail' => 'Responsive typed UI & state'];
        }
        if (!empty($rawArch['gateway'])) {
            $nodes[] = ['layer' => 'API Gateway', 'name' => (string)$rawArch['gateway'], 'detail' => 'Auth, routing & rate limits'];
        }
        if (!empty($rawArch['services']) && is_array($rawArch['services'])) {
            $nodes[] = ['layer' => 'Core Services', 'name' => implode(' • ', $rawArch['services']), 'detail' => 'Domain business logic & workers'];
        }
        if (!empty($rawArch['data']) && is_array($rawArch['data'])) {
            $nodes[] = ['layer' => 'Data & Persistence', 'name' => implode(' • ', $rawArch['data']), 'detail' => 'ACID storage & caching'];
        }
        $r['architecture'] = $nodes;
    } else {
        $r['architecture'] = array_values($rawArch);
    }

    $rawBench = json_decode_array($r['benchmarks_json'] ?? '[]');
    $benchOut = [];
    foreach ($rawBench as $b) {
        $lbl = $b['label'] ?? $b['name'] ?? 'Benchmark';
        if (!empty($b['note']) && !str_contains($lbl, '—')) {
            $lbl .= ' — ' . $b['note'];
        }
        $benchOut[] = [
            'label' => $lbl,
            'score' => (int)($b['score'] ?? 95),
        ];
    }
    $r['benchmarks'] = $benchOut;

    $r['client_name'] = $r['client'] ?? '';
    $r['role_name'] = $r['role'] ?? '';
    $r['code_filename'] = $r['code_file'] ?? 'src/index.ts';
    $r['is_published'] = (int)($r['is_active'] ?? 1);
    return $r;
}

function get_projects(bool $onlyActive = true, bool $onlyFeatured = false): array
{
    $pdo = get_db();
    $conds = [];
    if ($onlyActive) {
        $conds[] = 'is_active = 1';
    }
    if ($onlyFeatured) {
        $conds[] = 'is_featured = 1';
    }
    $where = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';
    $rows = $pdo->query("SELECT * FROM projects $where ORDER BY sort_order ASC, id DESC")->fetchAll();
    return array_map('normalize_project_row', $rows);
}

function get_project_by_slug(string $slug): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE slug = ? AND is_active = 1 LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return $row ? normalize_project_row($row) : null;
}

function get_project_by_id(int $id): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM projects WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ? normalize_project_row($row) : null;
}

function save_project(array $data, ?int $id = null): int
{
    $pdo = get_db();
    $slug = slugify((string)($data['slug'] ?? $data['title'] ?? 'project'));

    $stackJson = json_encode(array_values($data['stack'] ?? []), JSON_UNESCAPED_UNICODE);
    $metricsJson = json_encode(array_values($data['metrics'] ?? []), JSON_UNESCAPED_UNICODE);
    $archJson = json_encode(array_values($data['architecture'] ?? []), JSON_UNESCAPED_UNICODE);
    $benchJson = json_encode(array_values($data['benchmarks'] ?? []), JSON_UNESCAPED_UNICODE);

    $isActive = isset($data['is_published']) ? (int)$data['is_published'] : (int)($data['is_active'] ?? 1);

    if ($id !== null && $id > 0) {
        $stmt = $pdo->prepare('
            UPDATE projects
            SET slug = ?, title = ?, short_category = ?, category = ?, year = ?, client = ?, role = ?,
                image_path = ?, live_url = ?, repo_url = ?, summary = ?, challenge = ?, solution = ?,
                stack_json = ?, metrics_json = ?, arch_json = ?, code_file = ?, code_snippet = ?,
                benchmarks_json = ?, is_featured = ?, is_active = ?, sort_order = ?
            WHERE id = ?
        ');
        $stmt->execute([
            $slug,
            (string)($data['title'] ?? ''),
            (string)($data['short_category'] ?? ''),
            (string)($data['category'] ?? ''),
            (string)($data['year'] ?? date('Y')),
            (string)($data['client_name'] ?? $data['client'] ?? ''),
            (string)($data['role_name'] ?? $data['role'] ?? ''),
            (string)($data['image_path'] ?? '/assets/work-finova.png'),
            (string)($data['live_url'] ?? ''),
            (string)($data['repo_url'] ?? ''),
            (string)($data['summary'] ?? ''),
            (string)($data['challenge'] ?? ''),
            (string)($data['solution'] ?? ''),
            $stackJson,
            $metricsJson,
            $archJson,
            (string)($data['code_filename'] ?? $data['code_file'] ?? 'src/index.ts'),
            (string)($data['code_snippet'] ?? ''),
            $benchJson,
            (int)($data['is_featured'] ?? 0),
            $isActive,
            (int)($data['sort_order'] ?? 0),
            $id,
        ]);
        return $id;
    }

    $chk = $pdo->prepare('SELECT COUNT(*) FROM projects WHERE slug = ?');
    $chk->execute([$slug]);
    if ((int)$chk->fetchColumn() > 0) {
        $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
    }

    $stmt = $pdo->prepare('
        INSERT INTO projects (
            slug, title, short_category, category, year, client, role, status_badge,
            image_path, live_url, repo_url, summary, challenge, solution,
            stack_json, metrics_json, arch_json, code_file, code_snippet, benchmarks_json,
            is_featured, is_active, sort_order
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $slug,
        (string)($data['title'] ?? ''),
        (string)($data['short_category'] ?? ''),
        (string)($data['category'] ?? ''),
        (string)($data['year'] ?? date('Y')),
        (string)($data['client_name'] ?? $data['client'] ?? ''),
        (string)($data['role_name'] ?? $data['role'] ?? ''),
        'Production',
        (string)($data['image_path'] ?? '/assets/work-finova.png'),
        (string)($data['live_url'] ?? ''),
        (string)($data['repo_url'] ?? ''),
        (string)($data['summary'] ?? ''),
        (string)($data['challenge'] ?? ''),
        (string)($data['solution'] ?? ''),
        $stackJson,
        $metricsJson,
        $archJson,
        (string)($data['code_filename'] ?? $data['code_file'] ?? 'src/index.ts'),
        (string)($data['code_snippet'] ?? ''),
        $benchJson,
        (int)($data['is_featured'] ?? 0),
        $isActive,
        (int)($data['sort_order'] ?? 0),
    ]);
    return (int)$pdo->lastInsertId();
}

function delete_project(int $id): void
{
    $pdo = get_db();
    $stmt = $pdo->prepare('DELETE FROM projects WHERE id = ?');
    $stmt->execute([$id]);
}

/* ==========================================================================
   TESTIMONIALS
   ========================================================================== */

function get_testimonials(bool $onlyActive = true): array
{
    $pdo = get_db();
    $sql = $onlyActive
        ? 'SELECT * FROM testimonials WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        : 'SELECT * FROM testimonials ORDER BY sort_order ASC, id ASC';
    return $pdo->query($sql)->fetchAll();
}

function get_testimonial_by_id(int $id): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM testimonials WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function save_testimonial(array $data, ?int $id = null): int
{
    $pdo = get_db();
    if ($id !== null && $id > 0) {
        $stmt = $pdo->prepare('
            UPDATE testimonials
            SET client_name = ?, client_role = ?, quote_text = ?, avatar_path = ?, quote_color = ?, sort_order = ?, is_active = ?
            WHERE id = ?
        ');
        $stmt->execute([
            (string)($data['client_name'] ?? ''),
            (string)($data['client_role'] ?? ''),
            (string)($data['quote_text'] ?? ''),
            (string)($data['avatar_path'] ?? '/assets/avatar-rohit.png'),
            (string)($data['quote_color'] ?? '#67e8f9'),
            (int)($data['sort_order'] ?? 0),
            (int)($data['is_active'] ?? 1),
            $id,
        ]);
        return $id;
    }

    $stmt = $pdo->prepare('
        INSERT INTO testimonials (client_name, client_role, quote_text, avatar_path, quote_color, sort_order, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        (string)($data['client_name'] ?? ''),
        (string)($data['client_role'] ?? ''),
        (string)($data['quote_text'] ?? ''),
        (string)($data['avatar_path'] ?? '/assets/avatar-rohit.png'),
        (string)($data['quote_color'] ?? '#67e8f9'),
        (int)($data['sort_order'] ?? 0),
        (int)($data['is_active'] ?? 1),
    ]);
    return (int)$pdo->lastInsertId();
}

function delete_testimonial(int $id): void
{
    $pdo = get_db();
    $stmt = $pdo->prepare('DELETE FROM testimonials WHERE id = ?');
    $stmt->execute([$id]);
}

/* ==========================================================================
   CONTACT MESSAGES
   ========================================================================== */

function normalize_message_row(array $r): array
{
    $r['name'] = $r['sender_name'] ?? '';
    $r['email'] = $r['sender_email'] ?? '';
    $r['project'] = $r['project_scope'] ?? 'General Inquiry';
    $r['message'] = $r['message_body'] ?? '';
    return $r;
}

function save_contact_message(array $data): int
{
    $pdo = get_db();
    $stmt = $pdo->prepare('
        INSERT INTO contact_messages (sender_name, sender_email, project_scope, timeline, message_body, status, ip_address, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        mb_substr(trim((string)($data['name'] ?? '')), 0, 140),
        mb_substr(trim((string)($data['email'] ?? '')), 0, 180),
        mb_substr(trim((string)($data['project'] ?? 'General Inquiry')), 0, 160),
        mb_substr(trim((string)($data['timeline'] ?? '')), 0, 100),
        mb_substr(trim((string)($data['message'] ?? '')), 0, 4000),
        'unread',
        mb_substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64),
        date('Y-m-d H:i:s'),
    ]);
    return (int)$pdo->lastInsertId();
}

function get_contact_messages(string $status = 'all'): array
{
    $pdo = get_db();
    if ($status !== 'all' && in_array($status, ['unread', 'read', 'replied', 'archived'], true)) {
        $stmt = $pdo->prepare('SELECT * FROM contact_messages WHERE status = ? ORDER BY id DESC');
        $stmt->execute([$status]);
        $rows = $stmt->fetchAll();
    } else {
        $rows = $pdo->query('SELECT * FROM contact_messages ORDER BY id DESC')->fetchAll();
    }
    return array_map('normalize_message_row', $rows);
}

function get_contact_message_by_id(int $id): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM contact_messages WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ? normalize_message_row($row) : null;
}

function update_contact_message_status(int $id, string $status): void
{
    if (!in_array($status, ['unread', 'read', 'replied', 'archived'], true)) {
        $status = 'read';
    }
    $pdo = get_db();
    $stmt = $pdo->prepare('UPDATE contact_messages SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
}

function delete_contact_message(int $id): void
{
    $pdo = get_db();
    $stmt = $pdo->prepare('DELETE FROM contact_messages WHERE id = ?');
    $stmt->execute([$id]);
}

function count_unread_messages(): int
{
    $pdo = get_db();
    return (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
}

/* ==========================================================================
   TECHNOLOGIES, SKILL BARS & PROCESS STEPS
   ========================================================================== */

function get_tech_items(bool $onlyActive = true): array
{
    $pdo = get_db();
    $sql = $onlyActive
        ? 'SELECT * FROM tech_items WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        : 'SELECT * FROM tech_items ORDER BY sort_order ASC, id ASC';
    return $pdo->query($sql)->fetchAll();
}

function save_tech_item(array $data, ?int $id = null): int
{
    $pdo = get_db();
    if ($id !== null && $id > 0) {
        $stmt = $pdo->prepare('UPDATE tech_items SET name = ?, icon_key = ?, sort_order = ?, is_active = ? WHERE id = ?');
        $stmt->execute([
            (string)($data['name'] ?? ''),
            (string)($data['icon_key'] ?? 'code'),
            (int)($data['sort_order'] ?? 0),
            (int)($data['is_active'] ?? 1),
            $id,
        ]);
        return $id;
    }
    $stmt = $pdo->prepare('INSERT INTO tech_items (name, icon_key, sort_order, is_active) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        (string)($data['name'] ?? ''),
        (string)($data['icon_key'] ?? 'code'),
        (int)($data['sort_order'] ?? 0),
        (int)($data['is_active'] ?? 1),
    ]);
    return (int)$pdo->lastInsertId();
}

function delete_tech_item(int $id): void
{
    $pdo = get_db();
    $stmt = $pdo->prepare('DELETE FROM tech_items WHERE id = ?');
    $stmt->execute([$id]);
}

function get_skill_bars(): array
{
    $pdo = get_db();
    $rows = $pdo->query('SELECT * FROM skill_bars ORDER BY sort_order ASC, id ASC')->fetchAll();
    $groups = [];
    foreach ($rows as $r) {
        $cat = (string)$r['category'];
        if (!isset($groups[$cat])) {
            $groups[$cat] = [
                'id' => (int)$r['id'],
                'group_title' => $cat,
                'sort_order' => (int)$r['sort_order'],
                'items' => [],
            ];
        }
        $groups[$cat]['items'][] = [
            'name' => (string)$r['skill_name'],
            'pct' => (int)$r['proficiency'],
        ];
    }
    return array_values($groups);
}

function save_skill_bar(array $data, ?int $id = null): void
{
    $pdo = get_db();
    $newCat = (string)($data['group_title'] ?? 'Engineering');
    $baseOrder = (int)($data['sort_order'] ?? 1) * 10;

    if ($id !== null && $id > 0) {
        $find = $pdo->prepare('SELECT category FROM skill_bars WHERE id = ? LIMIT 1');
        $find->execute([$id]);
        $oldCat = $find->fetchColumn();
        if ($oldCat !== false) {
            $del = $pdo->prepare('DELETE FROM skill_bars WHERE category = ?');
            $del->execute([(string)$oldCat]);
        }
    }

    $ins = $pdo->prepare('INSERT INTO skill_bars (category, skill_name, proficiency, sort_order) VALUES (?, ?, ?, ?)');
    foreach (array_values($data['items'] ?? []) as $idx => $it) {
        $ins->execute([
            $newCat,
            (string)($it['name'] ?? ''),
            max(0, min(100, (int)($it['pct'] ?? 90))),
            $baseOrder + $idx,
        ]);
    }
}

function delete_skill_bar(int $id): void
{
    $pdo = get_db();
    $find = $pdo->prepare('SELECT category FROM skill_bars WHERE id = ? LIMIT 1');
    $find->execute([$id]);
    $cat = $find->fetchColumn();
    if ($cat !== false) {
        $del = $pdo->prepare('DELETE FROM skill_bars WHERE category = ?');
        $del->execute([(string)$cat]);
    }
}

function get_process_steps(): array
{
    $pdo = get_db();
    return $pdo->query('SELECT * FROM process_steps ORDER BY sort_order ASC, id ASC')->fetchAll();
}

function save_process_step(array $data, ?int $id = null): int
{
    $pdo = get_db();
    if ($id !== null && $id > 0) {
        $stmt = $pdo->prepare('
            UPDATE process_steps
            SET step_number = ?, title = ?, description = ?, icon_key = ?, is_highlighted = ?, sort_order = ?
            WHERE id = ?
        ');
        $stmt->execute([
            (string)($data['step_number'] ?? '01'),
            (string)($data['title'] ?? ''),
            (string)($data['description'] ?? ''),
            (string)($data['icon_key'] ?? 'arch'),
            (int)($data['is_highlighted'] ?? 0),
            (int)($data['sort_order'] ?? 0),
            $id,
        ]);
        return $id;
    }
    $stmt = $pdo->prepare('
        INSERT INTO process_steps (step_number, title, description, icon_key, is_highlighted, sort_order)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        (string)($data['step_number'] ?? '01'),
        (string)($data['title'] ?? ''),
        (string)($data['description'] ?? ''),
        (string)($data['icon_key'] ?? 'arch'),
        (int)($data['is_highlighted'] ?? 0),
        (int)($data['sort_order'] ?? 0),
    ]);
    return (int)$pdo->lastInsertId();
}

/* ==========================================================================
   EXPERIENCES & EDUCATION
   ========================================================================== */

function get_experiences_by_type(string $type): array
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM experiences WHERE entry_type = ? ORDER BY sort_order ASC, id ASC');
    $stmt->execute([$type]);
    $rows = $stmt->fetchAll();
    foreach ($rows as &$r) {
        $r['highlights'] = json_decode_array($r['highlights_json'] ?? '[]');
        $r['stack'] = json_decode_array($r['stack_json'] ?? '[]');
    }
    return $rows;
}

function save_experience(array $data, ?int $id = null): int
{
    $pdo = get_db();
    $hlJson = json_encode(array_values($data['highlights'] ?? []), JSON_UNESCAPED_UNICODE);
    $stJson = json_encode(array_values($data['stack'] ?? []), JSON_UNESCAPED_UNICODE);

    if ($id !== null && $id > 0) {
        $stmt = $pdo->prepare('
            UPDATE experiences
            SET entry_type = ?, period = ?, title = ?, organization = ?, summary = ?, highlights_json = ?, stack_json = ?, sort_order = ?
            WHERE id = ?
        ');
        $stmt->execute([
            (string)($data['entry_type'] ?? 'work'),
            (string)($data['period'] ?? ''),
            (string)($data['title'] ?? ''),
            (string)($data['organization'] ?? ''),
            (string)($data['summary'] ?? ''),
            $hlJson,
            $stJson,
            (int)($data['sort_order'] ?? 0),
            $id,
        ]);
        return $id;
    }

    $stmt = $pdo->prepare('
        INSERT INTO experiences (entry_type, period, title, organization, summary, highlights_json, stack_json, sort_order)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        (string)($data['entry_type'] ?? 'work'),
        (string)($data['period'] ?? ''),
        (string)($data['title'] ?? ''),
        (string)($data['organization'] ?? ''),
        (string)($data['summary'] ?? ''),
        $hlJson,
        $stJson,
        (int)($data['sort_order'] ?? 0),
    ]);
    return (int)$pdo->lastInsertId();
}

function delete_experience(int $id): void
{
    $pdo = get_db();
    $stmt = $pdo->prepare('DELETE FROM experiences WHERE id = ?');
    $stmt->execute([$id]);
}

/* ==========================================================================
   FAQS
   ========================================================================== */

function get_faqs(bool $onlyActive = true): array
{
    $pdo = get_db();
    $sql = $onlyActive
        ? 'SELECT * FROM faqs WHERE is_active = 1 ORDER BY sort_order ASC, id ASC'
        : 'SELECT * FROM faqs ORDER BY sort_order ASC, id ASC';
    return $pdo->query($sql)->fetchAll();
}

function save_faq(array $data, ?int $id = null): int
{
    $pdo = get_db();
    if ($id !== null && $id > 0) {
        $stmt = $pdo->prepare('UPDATE faqs SET question = ?, answer = ?, sort_order = ? WHERE id = ?');
        $stmt->execute([
            (string)($data['question'] ?? ''),
            (string)($data['answer'] ?? ''),
            (int)($data['sort_order'] ?? 0),
            $id,
        ]);
        return $id;
    }
    $stmt = $pdo->prepare('INSERT INTO faqs (question, answer, sort_order, is_active) VALUES (?, ?, ?, 1)');
    $stmt->execute([
        (string)($data['question'] ?? ''),
        (string)($data['answer'] ?? ''),
        (int)($data['sort_order'] ?? 0),
    ]);
    return (int)$pdo->lastInsertId();
}

function delete_faq(int $id): void
{
    $pdo = get_db();
    $stmt = $pdo->prepare('DELETE FROM faqs WHERE id = ?');
    $stmt->execute([$id]);
}

/* ==========================================================================
   DEVELOPER BLOG POSTS
   ========================================================================== */

function get_blog_posts(bool $onlyPublished = true): array
{
    $pdo = get_db();
    $sql = $onlyPublished
        ? 'SELECT * FROM blog_posts WHERE is_published = 1 ORDER BY sort_order ASC, id DESC'
        : 'SELECT * FROM blog_posts ORDER BY sort_order ASC, id DESC';
    $rows = $pdo->query($sql)->fetchAll();
    foreach ($rows as &$r) {
        $r['tags'] = json_decode_array($r['tags_json'] ?? '[]');
        $r['sections'] = json_decode_array($r['sections_json'] ?? '[]');
    }
    return $rows;
}

function get_blog_post_by_slug(string $slug): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE slug = ? AND is_published = 1 LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }
    $row['tags'] = json_decode_array($row['tags_json'] ?? '[]');
    $row['sections'] = json_decode_array($row['sections_json'] ?? '[]');
    return $row;
}

function get_blog_post_by_id(int $id): ?array
{
    $pdo = get_db();
    $stmt = $pdo->prepare('SELECT * FROM blog_posts WHERE id = ? LIMIT 1');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        return null;
    }
    $row['tags'] = json_decode_array($row['tags_json'] ?? '[]');
    $row['sections'] = json_decode_array($row['sections_json'] ?? '[]');
    return $row;
}

function save_blog_post(array $data, ?int $id = null): int
{
    $pdo = get_db();
    $slug = slugify((string)($data['slug'] ?? $data['title'] ?? 'article'));
    $tagsJson = json_encode(array_values($data['tags'] ?? []), JSON_UNESCAPED_UNICODE);
    $sectionsJson = json_encode(array_values($data['sections'] ?? []), JSON_UNESCAPED_UNICODE);

    if ($id !== null && $id > 0) {
        $stmt = $pdo->prepare('
            UPDATE blog_posts
            SET slug = ?, title = ?, category = ?, published_date = ?, read_time = ?,
                excerpt = ?, tags_json = ?, sections_json = ?, is_published = ?, sort_order = ?
            WHERE id = ?
        ');
        $stmt->execute([
            $slug,
            (string)($data['title'] ?? ''),
            (string)($data['category'] ?? 'Engineering'),
            (string)($data['published_date'] ?? date('M j, Y')),
            (string)($data['read_time'] ?? '5 min read'),
            (string)($data['excerpt'] ?? ''),
            $tagsJson,
            $sectionsJson,
            (int)($data['is_published'] ?? 1),
            (int)($data['sort_order'] ?? 0),
            $id,
        ]);
        return $id;
    }

    $chk = $pdo->prepare('SELECT COUNT(*) FROM blog_posts WHERE slug = ?');
    $chk->execute([$slug]);
    if ((int)$chk->fetchColumn() > 0) {
        $slug .= '-' . substr(bin2hex(random_bytes(2)), 0, 4);
    }

    $stmt = $pdo->prepare('
        INSERT INTO blog_posts (slug, title, category, published_date, read_time, excerpt, tags_json, sections_json, is_published, sort_order)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([
        $slug,
        (string)($data['title'] ?? ''),
        (string)($data['category'] ?? 'Engineering'),
        (string)($data['published_date'] ?? date('M j, Y')),
        (string)($data['read_time'] ?? '5 min read'),
        (string)($data['excerpt'] ?? ''),
        $tagsJson,
        $sectionsJson,
        (int)($data['is_published'] ?? 1),
        (int)($data['sort_order'] ?? 0),
    ]);
    return (int)$pdo->lastInsertId();
}

function delete_blog_post(int $id): void
{
    $pdo = get_db();
    $stmt = $pdo->prepare('DELETE FROM blog_posts WHERE id = ?');
    $stmt->execute([$id]);
}
