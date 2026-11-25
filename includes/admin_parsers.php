<?php
declare(strict_types=1);

/**
 * Convert comma-separated string into clean string array.
 */
function parse_csv_tags(string $raw): array
{
    $parts = array_map('trim', explode(',', $raw));
    return array_values(array_filter($parts, fn($v) => $v !== ''));
}

/**
 * Convert newline-separated lines into clean string array.
 */
function parse_lines_list(string $raw): array
{
    $lines = preg_split('/\R+/', $raw) ?: [];
    $lines = array_map('trim', $lines);
    return array_values(array_filter($lines, fn($v) => $v !== ''));
}

/**
 * Parse "Value | Label" lines into [['value' => ..., 'label' => ...], ...]
 */
function parse_metrics_lines(string $raw): array
{
    $out = [];
    foreach (parse_lines_list($raw) as $line) {
        $parts = array_map('trim', explode('|', $line, 2));
        if (count($parts) === 2 && $parts[0] !== '') {
            $out[] = ['value' => $parts[0], 'label' => $parts[1]];
        }
    }
    return $out;
}

function format_metrics_lines(array $metrics): string
{
    return implode("\n", array_map(fn($m) => ($m['value'] ?? '') . ' | ' . ($m['label'] ?? ''), $metrics));
}

/**
 * Parse "Layer | Name | Detail" lines into architecture array.
 */
function parse_architecture_lines(string $raw): array
{
    $out = [];
    foreach (parse_lines_list($raw) as $line) {
        $parts = array_map('trim', explode('|', $line, 3));
        if (count($parts) >= 2 && $parts[0] !== '') {
            $out[] = [
                'layer' => $parts[0],
                'name' => $parts[1],
                'detail' => $parts[2] ?? '',
            ];
        }
    }
    return $out;
}

function format_architecture_lines(array $arch): string
{
    return implode("\n", array_map(
        fn($a) => ($a['layer'] ?? '') . ' | ' . ($a['name'] ?? '') . ' | ' . ($a['detail'] ?? ''),
        $arch
    ));
}

/**
 * Parse "Label | Score" lines into benchmarks array.
 */
function parse_benchmarks_lines(string $raw): array
{
    $out = [];
    foreach (parse_lines_list($raw) as $line) {
        $parts = array_map('trim', explode('|', $line, 2));
        if ($parts[0] !== '') {
            $out[] = [
                'label' => $parts[0],
                'score' => max(0, min(100, (int)($parts[1] ?? 95))),
            ];
        }
    }
    return $out;
}

function format_benchmarks_lines(array $benchmarks): string
{
    return implode("\n", array_map(fn($b) => ($b['label'] ?? '') . ' | ' . ($b['score'] ?? 95), $benchmarks));
}

/**
 * Parse "Skill Name | 95" lines into skill bar items.
 */
function parse_skill_bar_lines(string $raw): array
{
    $out = [];
    foreach (parse_lines_list($raw) as $line) {
        $parts = array_map('trim', explode('|', $line, 2));
        if ($parts[0] !== '') {
            $out[] = [
                'name' => $parts[0],
                'pct' => max(0, min(100, (int)($parts[1] ?? 90))),
            ];
        }
    }
    return $out;
}

function format_skill_bar_lines(array $items): string
{
    return implode("\n", array_map(fn($i) => ($i['name'] ?? '') . ' | ' . ($i['pct'] ?? 90), $items));
}

/**
 * Parse blog sections separated by "---" blocks:
 * Heading: ...
 * Body: ...
 * CodeTitle: ... (optional)
 * Code:
 * ...
 */
function parse_blog_sections_text(string $raw): array
{
    $blocks = preg_split('/^\s*---\s*$/m', trim($raw)) ?: [];
    $sections = [];
    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        $heading = 'Overview';
        $body = '';
        $codeTitle = '';
        $code = '';

        if (preg_match('/^Heading:\s*(.+)$/mi', $block, $m)) {
            $heading = trim($m[1]);
        }
        if (preg_match('/^CodeTitle:\s*(.+)$/mi', $block, $m)) {
            $codeTitle = trim($m[1]);
        }

        // Split at Code: if present
        $parts = preg_split('/^Code:\s*$/mi', $block, 2);
        $topPart = $parts[0] ?? '';
        if (isset($parts[1])) {
            $code = trim($parts[1]);
        }

        // Extract Body: everything in topPart after removing Heading: and CodeTitle: lines
        $cleanLines = [];
        foreach (preg_split('/\R/', $topPart) ?: [] as $line) {
            if (preg_match('/^(Heading|CodeTitle):\s*/i', $line)) {
                continue;
            }
            $line = preg_replace('/^Body:\s*/i', '', $line);
            $cleanLines[] = $line;
        }
        $body = trim(implode("\n", $cleanLines));
        $sec = ['heading' => $heading, 'body' => $body];
        if ($code !== '') {
            $sec['codeTitle'] = $codeTitle !== '' ? $codeTitle : 'snippet.ts';
            $sec['code'] = $code;
        }
        $sections[] = $sec;
    }
    return $sections;
}

function format_blog_sections_text(array $sections): string
{
    $chunks = [];
    foreach ($sections as $s) {
        $lines = [];
        $lines[] = 'Heading: ' . ($s['heading'] ?? '');
        $lines[] = 'Body: ' . ($s['body'] ?? '');
        if (!empty($s['code'])) {
            $lines[] = 'CodeTitle: ' . ($s['codeTitle'] ?? 'snippet.ts');
            $lines[] = 'Code:';
            $lines[] = $s['code'];
        }
        $chunks[] = implode("\n", $lines);
    }
    return implode("\n---\n", $chunks);
}
