<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

/**
 * Returns singleton PDO connection (supports both 'sqlite' and 'mysql').
 * Automatically runs schema migration & initial seed if tables are empty.
 */
function get_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    if (DB_DRIVER === 'mysql') {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            DB_MYSQL_HOST,
            DB_MYSQL_PORT,
            DB_MYSQL_NAME
        );
        $pdo = new PDO($dsn, DB_MYSQL_USER, DB_MYSQL_PASS, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } else {
        if (!is_dir(STORAGE_PATH)) {
            mkdir(STORAGE_PATH, 0755, true);
        }
        $pdo = new PDO('sqlite:' . DB_SQLITE_PATH, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        $pdo->exec('PRAGMA journal_mode = WAL;');
    }

    bootstrap_schema_and_seed($pdo);
    return $pdo;
}

function bootstrap_schema_and_seed(PDO $pdo): void
{
    $autoInc = DB_DRIVER === 'mysql'
        ? 'INT AUTO_INCREMENT PRIMARY KEY'
        : 'INTEGER PRIMARY KEY AUTOINCREMENT';

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admins (
            id $autoInc,
            username VARCHAR(80) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL,
            display_name VARCHAR(120) NOT NULL DEFAULT 'Administrator',
            updated_at VARCHAR(40) NOT NULL
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS site_settings (
            setting_key VARCHAR(100) PRIMARY KEY,
            setting_value TEXT NOT NULL
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS services (
            id $autoInc,
            slug VARCHAR(120) NOT NULL UNIQUE,
            title VARCHAR(140) NOT NULL,
            tile_class VARCHAR(40) NOT NULL DEFAULT 'tile-violet',
            icon_key VARCHAR(40) NOT NULL DEFAULT 'browser_code',
            short_desc TEXT NOT NULL,
            tagline TEXT NOT NULL,
            overview TEXT NOT NULL,
            deliverables_json TEXT NOT NULL,
            metrics VARCHAR(200) NOT NULL DEFAULT '',
            stack_json TEXT NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_active INT NOT NULL DEFAULT 1
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS projects (
            id $autoInc,
            slug VARCHAR(140) NOT NULL UNIQUE,
            title VARCHAR(180) NOT NULL,
            short_category VARCHAR(160) NOT NULL,
            category VARCHAR(80) NOT NULL,
            year VARCHAR(20) NOT NULL DEFAULT '2026',
            client VARCHAR(160) NOT NULL DEFAULT '',
            role VARCHAR(160) NOT NULL DEFAULT '',
            status_badge VARCHAR(160) NOT NULL DEFAULT '',
            image_path VARCHAR(255) NOT NULL DEFAULT '/assets/work-finova.png',
            live_url VARCHAR(255) NOT NULL DEFAULT '',
            repo_url VARCHAR(255) NOT NULL DEFAULT '',
            summary TEXT NOT NULL,
            challenge TEXT NOT NULL,
            solution TEXT NOT NULL,
            stack_json TEXT NOT NULL,
            metrics_json TEXT NOT NULL,
            arch_json TEXT NOT NULL,
            code_file VARCHAR(180) NOT NULL DEFAULT 'src/index.ts',
            code_snippet TEXT NOT NULL,
            benchmarks_json TEXT NOT NULL,
            is_featured INT NOT NULL DEFAULT 0,
            is_active INT NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS testimonials (
            id $autoInc,
            client_name VARCHAR(140) NOT NULL,
            client_role VARCHAR(160) NOT NULL,
            quote_text TEXT NOT NULL,
            avatar_path VARCHAR(255) NOT NULL DEFAULT '/assets/avatar-rohit.png',
            quote_color VARCHAR(30) NOT NULL DEFAULT '#67e8f9',
            sort_order INT NOT NULL DEFAULT 0,
            is_active INT NOT NULL DEFAULT 1
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS contact_messages (
            id $autoInc,
            sender_name VARCHAR(140) NOT NULL,
            sender_email VARCHAR(180) NOT NULL,
            project_scope VARCHAR(160) NOT NULL DEFAULT '',
            timeline VARCHAR(100) NOT NULL DEFAULT '',
            message_body TEXT NOT NULL,
            status VARCHAR(30) NOT NULL DEFAULT 'unread',
            ip_address VARCHAR(64) NOT NULL DEFAULT '',
            created_at VARCHAR(40) NOT NULL
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tech_items (
            id $autoInc,
            name VARCHAR(80) NOT NULL,
            icon_key VARCHAR(40) NOT NULL DEFAULT 'code',
            sort_order INT NOT NULL DEFAULT 0,
            is_active INT NOT NULL DEFAULT 1
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS skill_bars (
            id $autoInc,
            category VARCHAR(120) NOT NULL,
            skill_name VARCHAR(160) NOT NULL,
            proficiency INT NOT NULL DEFAULT 90,
            sort_order INT NOT NULL DEFAULT 0
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS experiences (
            id $autoInc,
            entry_type VARCHAR(30) NOT NULL DEFAULT 'work',
            period VARCHAR(80) NOT NULL,
            title VARCHAR(180) NOT NULL,
            organization VARCHAR(180) NOT NULL,
            summary TEXT NOT NULL,
            highlights_json TEXT NOT NULL,
            stack_json TEXT NOT NULL,
            sort_order INT NOT NULL DEFAULT 0
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS process_steps (
            id $autoInc,
            step_number VARCHAR(10) NOT NULL,
            title VARCHAR(120) NOT NULL,
            description TEXT NOT NULL,
            icon_key VARCHAR(40) NOT NULL DEFAULT 'arch',
            is_highlighted INT NOT NULL DEFAULT 0,
            sort_order INT NOT NULL DEFAULT 0
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS faqs (
            id $autoInc,
            question TEXT NOT NULL,
            answer TEXT NOT NULL,
            sort_order INT NOT NULL DEFAULT 0,
            is_active INT NOT NULL DEFAULT 1
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS blog_posts (
            id $autoInc,
            slug VARCHAR(160) NOT NULL UNIQUE,
            title VARCHAR(220) NOT NULL,
            category VARCHAR(80) NOT NULL,
            published_date VARCHAR(40) NOT NULL,
            read_time VARCHAR(40) NOT NULL DEFAULT '5 min read',
            excerpt TEXT NOT NULL,
            tags_json TEXT NOT NULL,
            sections_json TEXT NOT NULL,
            is_published INT NOT NULL DEFAULT 1,
            sort_order INT NOT NULL DEFAULT 0
        );
    ");

    // Check if already seeded
    $adminCount = (int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    if ($adminCount > 0) {
        return;
    }

    seed_initial_data($pdo);
}

function seed_initial_data(PDO $pdo): void
{
    $now = date('Y-m-d H:i:s');

    // 1. Default Admin Account
    $stmt = $pdo->prepare('INSERT INTO admins (username, password_hash, display_name, updated_at) VALUES (?, ?, ?, ?)');
    $stmt->execute([
        DEFAULT_ADMIN_USER,
        password_hash(DEFAULT_ADMIN_PASS, PASSWORD_BCRYPT),
        'Jordan Lee',
        $now,
    ]);

    // 2. Site & Profile Settings
    $defaultSettings = [
        'name' => 'Jordan Lee',
        'role' => 'Software Developer',
        'eyebrow' => "HELLO, I'M",
        'tagline' => "I engineer scalable web applications and modern software\nthat are fast, intuitive and built with precision.",
        'portrait_path' => '/assets/profile-cutout.png',
        'hero_badge_years' => '5+',
        'hero_impact_label' => 'System Impact',
        'hero_impact_value' => '+120%',
        'about_heading_line1' => 'Engineering with Precision',
        'about_heading_line2' => 'Building for Scale',
        'stat1_val' => '5+',
        'stat1_label' => 'Years Experience',
        'stat2_val' => '30+',
        'stat2_label' => 'Systems Shipped',
        'stat3_val' => '15+',
        'stat3_label' => 'Global Clients',
        'short_bio' => "I'm a software and full-stack web developer with 5+ years of experience turning complex product requirements into fast, scalable and reliable web applications. I focus on clean architecture, modern UI engineering and cloud performance.",
        'extended_bio_1' => "Over the past 5+ years, I've architected and shipped over 30 production web applications, SaaS platforms, developer tools, and distributed APIs for high-growth startups and enterprise engineering teams.",
        'extended_bio_2' => "My approach bridges frontend craft and backend rigor: pixel-accurate, accessible interfaces powered by React, Next.js, and TypeScript on the surface—backed by resilient PHP, Node.js, Python, PostgreSQL/MySQL, and containerized cloud infrastructure underneath.",
        'extended_bio_3' => "Whether leading greenfield MVP architecture from zero to one or optimizing high-traffic web platforms for sub-50ms latency and 99.98% uptime, I write clean, maintainable, well-tested code designed to scale.",
        'email' => 'hello@example.com',
        'phone' => '+1 (555) 000-0000',
        'location' => 'Remote',
        'location_full' => 'Remote / Worldwide',
        'availability' => 'Available for Full-Stack & Web Projects',
        'copyright' => '© 2026 Jordan Lee. All rights reserved.',
        'github_url' => 'https://github.com/jordanlee-dev',
        'linkedin_url' => 'https://linkedin.com/in/jordanlee-dev',
        'x_url' => 'https://x.com/jordanlee_dev',
        'website_url' => 'https://example.com',
    ];

    $setStmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
    foreach ($defaultSettings as $k => $v) {
        $setStmt->execute([$k, $v]);
    }

    // 3. Services (4 Core Services)
    $services = [
        [
            'fullstack-web', 'Full-Stack Web', 'tile-amber', 'browser_code',
            'Building fast, scalable web apps with modern frontend and backend stacks.',
            'End-to-end web applications engineered for speed, reliability, and scale.',
            'From database schema design to interactive React/Next.js and PHP/Node backends, I deliver complete production-ready SaaS applications, customer portals, and custom CMS platforms.',
            [
                'Next.js / React & PHP / Node.js full-stack web applications',
                'Role-based authentication, OAuth2 / sessions, and admin panels',
                'Stripe billing, webhooks, REST APIs, and real-time WebSocket features',
                'Automated E2E & integration test suites with Playwright',
            ],
            '99+ Lighthouse Performance • <1.2s LCP',
            ['Next.js', 'React', 'TypeScript', 'PHP 8', 'Node.js', 'PostgreSQL'],
            1,
        ],
        [
            'frontend-systems', 'Frontend Systems', 'tile-violet', 'ui_grid',
            'Crafting responsive, accessible interfaces and reusable component libraries.',
            'Pixel-precise UI engineering, design-to-code systems, and fluid interactions.',
            'I translate complex product designs into modular, accessible, high-FPS frontend architectures with strict type safety, zero layout shift, and seamless tablet/mobile responsiveness.',
            [
                'Reusable React / TypeScript component libraries & design tokens',
                'Complex interactive dashboards, data visualizations & state machines',
                'WCAG 2.1 AA accessibility compliance & keyboard navigation',
                'Core Web Vitals optimization, bundle splitting & edge caching',
            ],
            '0.00 CLS • 60 FPS • 320px–4K Responsive',
            ['React 19', 'TypeScript', 'Modern CSS', 'TanStack Query', 'Vite'],
            2,
        ],
        [
            'apis-backend', 'APIs & Backend', 'tile-blue', 'api_servers',
            'Designing secure REST & GraphQL APIs, databases and cloud microservices.',
            'High-throughput APIs, clean domain modeling, and rock-solid data pipelines.',
            'I architect resilient backend services capable of handling high concurrency, background job queues, third-party integrations, and strict OWASP security requirements.',
            [
                'RESTful OpenAPI & GraphQL API architecture with rate-limiting',
                'PostgreSQL / MySQL / SQLite schema modeling & query tuning',
                'Event-driven background workers, queues (Redis) & webhooks',
                'Comprehensive API documentation, structured logging & audit trails',
            ],
            '<38ms P95 API latency • 99.98% Uptime',
            ['Node.js', 'PHP 8', 'Python', 'FastAPI', 'PostgreSQL', 'MySQL'],
            3,
        ],
        [
            'cloud-devops', 'Cloud & DevOps', 'tile-cyan', 'cloud_ops',
            'Automating CI/CD pipelines, Docker deployments and performance tuning.',
            'Repeatable infrastructure, automated pipelines, and production observability.',
            'I streamline software delivery so engineering teams deploy multiple times a day with confidence—combining containerization, automated checks, and real-time monitoring.',
            [
                'Dockerized multi-stage builds & cloud deployments (AWS / VPS / Fly.io)',
                'GitHub Actions CI/CD pipelines with preview environments & rollback',
                'CDN edge caching, SSL, secrets management & automated DB backups',
                'Application telemetry, error tracking & load-testing benchmarks',
            ],
            '4-min CI builds • Zero-downtime rollouts',
            ['Docker', 'GitHub Actions', 'AWS', 'Nginx', 'Cloudflare', 'Linux'],
            4,
        ],
    ];

    $srvStmt = $pdo->prepare('
        INSERT INTO services (slug, title, tile_class, icon_key, short_desc, tagline, overview, deliverables_json, metrics, stack_json, sort_order, is_active)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
    ');
    foreach ($services as $s) {
        $srvStmt->execute([
            $s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6],
            json_encode($s[7], JSON_UNESCAPED_UNICODE),
            $s[8],
            json_encode($s[9], JSON_UNESCAPED_UNICODE),
            $s[10],
        ]);
    }

    // 4. Home Technology Tiles (8 Items)
    $techs = [
        ['React', 'react', 1],
        ['Next.js', 'nextjs', 2],
        ['TypeScript', 'typescript', 3],
        ['Node.js', 'nodejs', 4],
        ['Python', 'python', 5],
        ['PostgreSQL', 'postgresql', 6],
        ['Docker', 'docker', 7],
        ['Git', 'git', 8],
    ];
    $techStmt = $pdo->prepare('INSERT INTO tech_items (name, icon_key, sort_order, is_active) VALUES (?, ?, ?, 1)');
    foreach ($techs as $t) {
        $techStmt->execute($t);
    }

    // 5. About Skill Proficiency Bars
    $skills = [
        ['Frontend Engineering', 'React & Next.js (App Router)', 96, 1],
        ['Frontend Engineering', 'TypeScript / JavaScript (ES2025)', 95, 2],
        ['Frontend Engineering', 'Modern CSS, Glassmorphism & Responsive UI', 97, 3],
        ['Frontend Engineering', 'State Management & Accessibility', 92, 4],
        ['Backend & APIs', 'PHP 8, Node.js & Express / Fastify', 94, 5],
        ['Backend & APIs', 'Python & FastAPI Services', 90, 6],
        ['Backend & APIs', 'REST, GraphQL & WebSockets', 93, 7],
        ['Backend & APIs', 'Auth, CSRF/OWASP Security & Payments', 92, 8],
        ['Databases & Cloud', 'PostgreSQL, MySQL & SQLite PDO', 94, 9],
        ['Databases & Cloud', 'Redis Caching, Queues & Pub/Sub', 89, 10],
        ['Databases & Cloud', 'Docker, CI/CD & GitHub Actions', 91, 11],
        ['Databases & Cloud', 'AWS, Linux VPS & Nginx / Apache', 89, 12],
    ];
    $skStmt = $pdo->prepare('INSERT INTO skill_bars (category, skill_name, proficiency, sort_order) VALUES (?, ?, ?, ?)');
    foreach ($skills as $sk) {
        $skStmt->execute($sk);
    }

    // 6. Projects / Selected Works (6 Complete Software Case Studies; first 3 Featured on Home)
    $projects = [
        [
            'slug' => 'finova-platform',
            'title' => 'Finova – FinTech Platform',
            'short_category' => 'Next.js • TypeScript • Node.js',
            'category' => 'Full-Stack',
            'year' => '2026',
            'client' => 'Finova Financial Inc.',
            'role' => 'Lead Full-Stack Developer',
            'status_badge' => 'Production • 85k+ Active Users',
            'image_path' => '/assets/work-finova.png',
            'live_url' => 'https://finova-demo.example.com',
            'repo_url' => 'https://github.com/jordanlee-dev/finova-fintech-platform',
            'summary' => 'A real-time personal wealth, multi-currency wallet, and smart budgeting platform engineered with end-to-end type safety, instant ledger reconciliation, and sub-50ms API responses.',
            'challenge' => 'Legacy financial dashboards suffered from slow 4.2s page loads, stale banking syncs, and fragile client-side calculations that broke across mobile devices and multi-currency accounts.',
            'solution' => 'Designed an event-driven double-entry ledger API in Node.js + PostgreSQL paired with a responsive Next.js + TypeScript interface using optimistic updates, Redis caching, and WebSocket balance streaming.',
            'stack' => ['Next.js', 'TypeScript', 'Node.js', 'PostgreSQL', 'Redis', 'Docker'],
            'metrics' => [
                ['label' => 'User Engagement', 'value' => '+120%'],
                ['label' => 'API P95 Latency', 'value' => '34ms'],
                ['label' => 'Lighthouse Score', 'value' => '99/100'],
                ['label' => 'System Uptime', 'value' => '99.99%'],
            ],
            'arch' => [
                'client' => 'Next.js Web & PWA Client',
                'gateway' => 'Edge Auth + Rate Limiter',
                'services' => ['Ledger Service (Node.js)', 'Bank Sync Worker (BullMQ)', 'Insights Engine'],
                'data' => ['PostgreSQL (ACID Ledger)', 'Redis Pub/Sub Cache'],
            ],
            'code_file' => 'src/services/ledger-transfer.ts',
            'code_snippet' => "export async function executeAtomicTransfer(\n  db: DatabasePool,\n  input: TransferCommand\n): Promise<TransferReceipt> {\n  return db.transaction('SERIALIZABLE', async tx => {\n    const existing = await tx.ledger.findByIdempotencyKey(input.idempotencyKey);\n    if (existing) return existing.toReceipt();\n\n    const source = await tx.accounts.lockForUpdate(input.fromAccountId);\n    if (source.availableCents < input.amountCents) {\n      throw new InsufficientFundsError(source.id, input.amountCents);\n    }\n\n    const entry = await tx.ledger.insertDoubleEntry({\n      debitAccountId: input.fromAccountId,\n      creditAccountId: input.toAccountId,\n      amountCents: input.amountCents,\n      currency: input.currency,\n      idempotencyKey: input.idempotencyKey,\n    });\n\n    await redisBus.publish(`acct:\${input.fromAccountId}`, entry.summary);\n    return entry.toReceipt();\n  });\n}",
            'benchmarks' => [
                ['name' => 'Lighthouse Performance', 'score' => 99, 'note' => '0.9s First Contentful Paint'],
                ['name' => 'API P95 Throughput', 'score' => 96, 'note' => '34ms median ledger query'],
                ['name' => 'Automated Test Coverage', 'score' => 94, 'note' => 'Unit + Playwright E2E'],
                ['name' => 'Accessibility (WCAG AA)', 'score' => 100, 'note' => 'Full keyboard & screen-reader support'],
            ],
            'is_featured' => 1,
            'sort_order' => 1,
        ],
        [
            'slug' => 'pulse-analytics',
            'title' => 'Pulse – Analytics SaaS',
            'short_category' => 'React • Python • ClickHouse',
            'category' => 'Web Apps',
            'year' => '2026',
            'client' => 'PulseMetrics Cloud',
            'role' => 'Principal Frontend & API Engineer',
            'status_badge' => 'Production • 18M Events/Day',
            'image_path' => '/assets/work-analytics.png',
            'live_url' => 'https://pulse-analytics.example.com',
            'repo_url' => 'https://github.com/jordanlee-dev/pulse-analytics-saas',
            'summary' => 'High-density product telemetry and executive revenue intelligence web application capable of querying tens of millions of events in real time with interactive cohort filtering.',
            'challenge' => 'Product teams waited 12–20 seconds for multi-dimensional funnel queries while browser memory spiked above 900MB rendering live multi-series SVG/Canvas charts.',
            'solution' => 'Built an async Python FastAPI + ClickHouse aggregation layer with materialized rollups, streaming JSON responses into a virtualized React + TypeScript dashboard.',
            'stack' => ['React', 'TypeScript', 'Python', 'FastAPI', 'PostgreSQL', 'Docker'],
            'metrics' => [
                ['label' => 'Query Speedup', 'value' => '14× Faster'],
                ['label' => 'Events Ingested', 'value' => '18M / day'],
                ['label' => 'Memory Footprint', 'value' => '-58%'],
                ['label' => 'Active Teams', 'value' => '420+'],
            ],
            'arch' => [
                'client' => 'React 19 + TanStack Dashboard',
                'gateway' => 'FastAPI Async Gateway',
                'services' => ['Event Collector', 'Funnel Query Planner', 'Anomaly Alert Worker'],
                'data' => ['ClickHouse Columnar Store', 'PostgreSQL Metadata'],
            ],
            'code_file' => 'api/routers/funnel_metrics.py',
            'code_snippet' => "@router.post(\"/v1/funnels/query\", response_model=FunnelSeriesResponse)\nasync def compute_realtime_funnel(\n    payload: FunnelQuerySpec,\n    tenant: TenantContext = Depends(require_authenticated_tenant),\n    ch: AsyncClickHouseClient = Depends(get_clickhouse),\n) -> FunnelSeriesResponse:\n    cache_key = f\"funnel:{tenant.id}:{payload.fingerprint()}\"\n    if cached := await redis_cache.get_json(cache_key):\n        return FunnelSeriesResponse(**cached, cached=True)\n\n    rows = await ch.fetch_all(\n        FUNNEL_WINDOW_SQL,\n        params={\"tenant_id\": tenant.id, \"steps\": [s.event_name for s in payload.steps]},\n    )\n    result = build_conversion_breakdown(rows, payload.steps)\n    await redis_cache.set_json(cache_key, result.model_dump(), ttl=30)\n    return result",
            'benchmarks' => [
                ['name' => 'Dashboard Render FPS', 'score' => 98, 'note' => 'Consistent 60 FPS chart scrubbing'],
                ['name' => 'P95 Aggregation Speed', 'score' => 95, 'note' => '110ms across 10M event rows'],
                ['name' => 'Type-Safe API Contracts', 'score' => 100, 'note' => 'OpenAPI -> TypeScript codegen'],
                ['name' => 'Mobile & Tablet Layout', 'score' => 99, 'note' => 'Adaptive responsive chart cards'],
            ],
            'is_featured' => 1,
            'sort_order' => 2,
        ],
        [
            'slug' => 'cloudscale-saas',
            'title' => 'CloudScale – AI Web Suite',
            'short_category' => 'Full-Stack • GraphQL • AWS',
            'category' => 'Full-Stack',
            'year' => '2025',
            'client' => 'CloudScale AI',
            'role' => 'Full-Stack Web Architect',
            'status_badge' => 'Production • +68% Trial Signups',
            'image_path' => '/assets/work-saas-clean.png',
            'live_url' => 'https://cloudscale.example.com',
            'repo_url' => 'https://github.com/jordanlee-dev/cloudscale-web-suite',
            'summary' => 'Workflow automation platform and high-converting interactive SaaS web suite combining marketing edge pages, live interactive sandbox, and unified GraphQL workspace.',
            'challenge' => 'CloudScale needed a unified web architecture linking ultra-fast public landing pages with an authenticated workflow builder without jarring theme shifts or slow cold starts.',
            'solution' => 'Engineered a Next.js monorepo with shared glassmorphic UI primitives, edge-cached marketing routes, and a federated Node.js + GraphQL workflow execution engine.',
            'stack' => ['Next.js', 'TypeScript', 'GraphQL', 'Node.js', 'AWS', 'Docker'],
            'metrics' => [
                ['label' => 'Trial Conversion', 'value' => '+68%'],
                ['label' => 'Edge TTFB', 'value' => '28ms'],
                ['label' => 'Core Web Vitals', 'value' => '100/100'],
                ['label' => 'Workflow Runs', 'value' => '2.4M/mo'],
            ],
            'arch' => [
                'client' => 'Next.js Edge + Workspace App',
                'gateway' => 'GraphQL Federation Router',
                'services' => ['Workflow Runner', 'AI Action Connector', 'Billing & Quotas'],
                'data' => ['PostgreSQL + Prisma', 'AWS S3 & SQS'],
            ],
            'code_file' => 'packages/workflow-engine/runner.ts',
            'code_snippet' => "export async function runWorkflowGraph(\n  dag: WorkflowDAG,\n  ctx: ExecutionContext\n): Promise<ExecutionSummary> {\n  const orderedSteps = topologicalSort(dag.nodes, dag.edges);\n  const state = new Map<string, unknown>();\n\n  for (const step of orderedSteps) {\n    const resolvedInputs = interpolateBindings(step.inputs, state, ctx.secrets);\n    const output = await executeStepWithRetry(step, resolvedInputs, {\n      maxAttempts: step.retryPolicy?.maxAttempts ?? 3,\n      timeoutMs: step.timeoutMs ?? 8_000,\n    });\n    state.set(step.id, output);\n  }\n  return { status: 'COMPLETED', outputs: Object.fromEntries(state) };\n}",
            'benchmarks' => [
                ['name' => 'Lighthouse SEO & Vitals', 'score' => 100, 'note' => 'Zero layout shift (0.00 CLS)'],
                ['name' => 'Edge Cold-Start TTFB', 'score' => 97, 'note' => '28ms global edge response'],
                ['name' => 'Component Reuse Ratio', 'score' => 94, 'note' => 'Shared across 14 product routes'],
                ['name' => 'CI Pipeline Reliability', 'score' => 99, 'note' => 'Automated visual & API checks'],
            ],
            'is_featured' => 1,
            'sort_order' => 3,
        ],
        [
            'slug' => 'devflow-ci-portal',
            'title' => 'DevFlow – Deployment Hub',
            'short_category' => 'PHP 8 • Docker • WebSockets',
            'category' => 'APIs & DevOps',
            'year' => '2025',
            'client' => 'Open Engineering Tooling',
            'role' => 'Creator & Backend Architect',
            'status_badge' => 'Open Source • 1.8k GitHub Stars',
            'image_path' => '/assets/work-analytics.png',
            'live_url' => 'https://devflow.example.com',
            'repo_url' => 'https://github.com/jordanlee-dev/devflow-deploy-hub',
            'summary' => 'Self-hosted container preview environment manager and live build-log streaming portal for fast-moving GitHub engineering teams.',
            'challenge' => 'Engineering teams lacked instant branch preview URLs with live container logs and automatic teardown when pull requests merged.',
            'solution' => 'Built a lightweight orchestrator wrapping Docker Engine APIs, GitHub webhooks, automatic wildcard TLS routing, and multiplexed WebSocket log streams.',
            'stack' => ['PHP 8', 'Node.js', 'TypeScript', 'Docker', 'React', 'Redis'],
            'metrics' => [
                ['label' => 'Preview Spin-Up', 'value' => '19 sec'],
                ['label' => 'Cloud Cost Saved', 'value' => '-44%'],
                ['label' => 'GitHub Stars', 'value' => '1.8k+'],
                ['label' => 'Log Latency', 'value' => '<25ms'],
            ],
            'arch' => [
                'client' => 'React Live Terminal & PR Dashboard',
                'gateway' => 'Reverse Proxy + Webhook Verifier',
                'services' => ['Docker Orchestrator', 'Log Stream Multiplexer', 'TTL Reclaim Cron'],
                'data' => ['PostgreSQL State', 'Redis Stream Buffer'],
            ],
            'code_file' => 'src/orchestrator/preview-container.ts',
            'code_snippet' => "export async function provisionPullRequestPreview(pr: PullRequestEvent) {\n  const subdomain = `pr-\${pr.number}.\${config.previewBaseDomain}`;\n  const imageTag = await buildIsolatedContainerImage(pr.repo, pr.headSha);\n\n  const container = await docker.createAndStart({\n    image: imageTag,\n    memoryLimitMb: 512,\n    healthcheckPath: '/api/health',\n  });\n\n  await proxyRouter.registerUpstream(subdomain, container.internalAddress);\n  return { subdomain, containerId: container.id };\n}",
            'benchmarks' => [
                ['name' => 'Container Provision Speed', 'score' => 96, 'note' => '19s cached layer build'],
                ['name' => 'WebSocket Stream Stability', 'score' => 98, 'note' => 'Backpressure-safe ring buffer'],
                ['name' => 'Security Isolation', 'score' => 95, 'note' => 'Rootless cgroups + read-only fs'],
                ['name' => 'Developer Satisfaction', 'score' => 99, 'note' => 'Adopted by 30+ teams'],
            ],
            'is_featured' => 0,
            'sort_order' => 4,
        ],
        [
            'slug' => 'shopsphere-headless',
            'title' => 'ShopSphere – Commerce Engine',
            'short_category' => 'Next.js • PostgreSQL • Stripe',
            'category' => 'Full-Stack',
            'year' => '2025',
            'client' => 'ShopSphere Retail Group',
            'role' => 'Lead Ecommerce Software Developer',
            'status_badge' => 'Production • $14M+ Annual GMV',
            'image_path' => '/assets/work-finova.png',
            'live_url' => 'https://shopsphere.example.com',
            'repo_url' => 'https://github.com/jordanlee-dev/shopsphere-headless-commerce',
            'summary' => 'Sub-second headless storefront and inventory reservation API supporting flash-sale traffic spikes without overselling.',
            'challenge' => 'Flash product drops of 15,000 concurrent buyers overwhelmed monolithic cart endpoints and caused race-condition inventory oversells.',
            'solution' => 'Implemented Redis Lua atomic stock reservations, edge-rendered Next.js product catalogs with ISR, and idempotent Stripe PaymentIntent webhooks.',
            'stack' => ['Next.js', 'TypeScript', 'Node.js', 'PostgreSQL', 'Redis'],
            'metrics' => [
                ['label' => 'Checkout Speed', 'value' => '-54% Time'],
                ['label' => 'Oversell Incidents', 'value' => '0'],
                ['label' => 'Mobile Conversion', 'value' => '+41%'],
                ['label' => 'Peak RPS', 'value' => '6,500/s'],
            ],
            'arch' => [
                'client' => 'Next.js Edge Storefront',
                'gateway' => 'CDN Edge + Cart API',
                'services' => ['Atomic Inventory Service', 'Checkout & Stripe Service', 'CMS Sync'],
                'data' => ['Redis Stock Locks', 'PostgreSQL Orders DB'],
            ],
            'code_file' => 'src/inventory/atomic-reserve.ts',
            'code_snippet' => "export async function reserveSkuForCheckout(sku: string, sessionId: string, qty: number) {\n  const remaining = await redis.eval(\n    RESERVE_LUA,\n    [`sku:\${sku}:available`, `hold:\${sessionId}:\${sku}`],\n    [String(qty), '600']\n  );\n  if (remaining < 0) throw new OutOfStockError(sku);\n  return { sku, reserved: qty, remaining };\n}",
            'benchmarks' => [
                ['name' => 'Storefront LCP Speed', 'score' => 99, 'note' => '0.8s on 4G mobile devices'],
                ['name' => 'Concurrency Correctness', 'score' => 100, 'note' => 'Zero race conditions under k6 load'],
                ['name' => 'Checkout Completion', 'score' => 95, 'note' => 'Apple Pay / Stripe 1-tap flow'],
                ['name' => 'Responsive UX', 'score' => 99, 'note' => 'Thumb-friendly mobile cart drawer'],
            ],
            'is_featured' => 0,
            'sort_order' => 5,
        ],
        [
            'slug' => 'syncdocs-workspace',
            'title' => 'SyncDocs – Realtime Docs',
            'short_category' => 'React • CRDT • WebSockets',
            'category' => 'Web Apps',
            'year' => '2024',
            'client' => 'SyncDocs Collaborative',
            'role' => 'Senior Frontend & Realtime Engineer',
            'status_badge' => 'Production • Multiplayer Editing',
            'image_path' => '/assets/work-saas-clean.png',
            'live_url' => 'https://syncdocs.example.com',
            'repo_url' => 'https://github.com/jordanlee-dev/syncdocs-crdt-workspace',
            'summary' => 'Offline-first multiplayer technical documentation and architecture-diagram workspace powered by CRDT conflict-free state synchronization.',
            'challenge' => 'Distributed teams needed simultaneous editing, live cursor presence, and seamless offline recovery when airplane or mobile connections dropped.',
            'solution' => 'Combined Yjs CRDT state vectors stored in IndexedDB on the client with a stateless WebSocket relay cluster and compacted PostgreSQL snapshots.',
            'stack' => ['React', 'TypeScript', 'Node.js', 'PostgreSQL', 'Docker'],
            'metrics' => [
                ['label' => 'Sync Latency', 'value' => '18ms'],
                ['label' => 'Offline Recovery', 'value' => '100% Lossless'],
                ['label' => 'Bundle Size', 'value' => '78 KB gz'],
                ['label' => 'Daily Docs Edited', 'value' => '95k+'],
            ],
            'arch' => [
                'client' => 'React Editor + IndexedDB CRDT',
                'gateway' => 'WebSocket Presence Relay',
                'services' => ['CRDT Merge Worker', 'Snapshot Compactor', 'Search Indexer'],
                'data' => ['PostgreSQL Binary Blobs', 'Redis Presence Mesh'],
            ],
            'code_file' => 'src/collab/crdt-sync-provider.ts',
            'code_snippet' => "export function attachOfflineFirstSync(docId: string, ydoc: Y.Doc) {\n  const localPersistence = new IndexeddbPersistence(`syncdocs:\${docId}`, ydoc);\n  const wsProvider = new WebsocketRelayProvider(WS_ENDPOINT, docId, ydoc);\n  ydoc.on('update', (delta: Uint8Array, origin: unknown) => {\n    if (origin !== wsProvider) wsProvider.broadcastBinaryDelta(delta);\n  });\n  return () => { wsProvider.disconnect(); localPersistence.destroy(); };\n}",
            'benchmarks' => [
                ['name' => 'Keystroke Input Latency', 'score' => 99, 'note' => '<4ms local frame paint'],
                ['name' => 'Conflict-Free Merge', 'score' => 100, 'note' => 'Deterministic CRDT convergence'],
                ['name' => 'Initial JS Payload', 'score' => 96, 'note' => '78 KB gzipped core shell'],
                ['name' => 'Cross-Device Parity', 'score' => 98, 'note' => 'Desktop, iPad & Mobile keyboards'],
            ],
            'is_featured' => 0,
            'sort_order' => 6,
        ],
    ];

    $projStmt = $pdo->prepare('
        INSERT INTO projects (
            slug, title, short_category, category, year, client, role, status_badge,
            image_path, live_url, repo_url, summary, challenge, solution,
            stack_json, metrics_json, arch_json, code_file, code_snippet, benchmarks_json,
            is_featured, is_active, sort_order
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
    ');
    foreach ($projects as $p) {
        $projStmt->execute([
            $p['slug'], $p['title'], $p['short_category'], $p['category'], $p['year'],
            $p['client'], $p['role'], $p['status_badge'], $p['image_path'],
            $p['live_url'], $p['repo_url'], $p['summary'], $p['challenge'], $p['solution'],
            json_encode($p['stack'], JSON_UNESCAPED_UNICODE),
            json_encode($p['metrics'], JSON_UNESCAPED_UNICODE),
            json_encode($p['arch'], JSON_UNESCAPED_UNICODE),
            $p['code_file'], $p['code_snippet'],
            json_encode($p['benchmarks'], JSON_UNESCAPED_UNICODE),
            $p['is_featured'], $p['sort_order'],
        ]);
    }

    // 7. Process Steps (5 Steps)
    $steps = [
        ['01', 'Architecture', 'System specs, data models and API contracts.', 'arch', 0, 1],
        ['02', 'Prototype', 'Interactive UI architecture and technical PoCs.', 'proto', 0, 2],
        ['03', 'Frontend', 'Building responsive components and state flows.', 'frontend', 0, 3],
        ['04', 'Backend & API', 'Developing secure services, auth and databases.', 'backend', 1, 4],
        ['05', 'Test & Deploy', 'Automated testing, CI/CD and cloud scaling.', 'deploy', 0, 5],
    ];
    $stStmt = $pdo->prepare('INSERT INTO process_steps (step_number, title, description, icon_key, is_highlighted, sort_order) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($steps as $st) {
        $stStmt->execute($st);
    }

    // 8. Testimonials
    $testimonials = [
        [
            'Rohit Sharma', 'Engineering Manager, Google',
            '"Jordan is an exceptional software engineer. He architected our web platform and delivered fast, rock-solid code on every sprint."',
            '/assets/avatar-rohit.png', '#cbc5fa', 1,
        ],
        [
            'Neha Verma', 'VP of Product, Microsoft',
            '"Working with Jordan was seamless. He pairs deep backend API rigor with pixel-level frontend craftsmanship."',
            '/assets/avatar-neha.png', '#67e8f9', 2,
        ],
        [
            'Karan Malhotra', 'CTO & Founder, StartupX',
            '"Jordan rebuilt our SaaS web app, cutting load times by 70% and doubling conversions. Highly recommended!"',
            '/assets/avatar-karan.png', '#67e8f9', 3,
        ],
    ];
    $testStmt = $pdo->prepare('INSERT INTO testimonials (client_name, client_role, quote_text, avatar_path, quote_color, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)');
    foreach ($testimonials as $tm) {
        $testStmt->execute($tm);
    }

    // 9. Experience & Education
    $exps = [
        [
            'work', '2024 — Present', 'Senior Full-Stack Software Engineer', 'CloudScale Systems (Remote)',
            'Lead architect for multi-tenant SaaS web applications serving 120k+ active users. Reduced API P95 latency by 62% and spearheaded a unified React + TypeScript component platform.',
            [
                'Architected real-time analytics ingestion & Next.js dashboard handling 18k events/sec',
                'Built zero-downtime CI/CD deployment pipeline cutting release lead time from 2 days to 14 minutes',
                'Mentored 6 frontend and backend engineers across code reviews and system design',
            ],
            ['Next.js', 'TypeScript', 'PHP 8', 'Node.js', 'PostgreSQL', 'Docker'],
            1,
        ],
        [
            'work', '2022 — 2024', 'Full-Stack Web Developer', 'Finova Technologies',
            'Engineered core personal-finance web and cross-platform banking modules, transaction categorization pipelines, and encrypted Open-Banking integrations.',
            [
                'Delivered real-time portfolio & spending insights engine increasing daily engagement by +120%',
                'Implemented strict type safety and 94% automated test coverage across checkout/ledger APIs',
            ],
            ['React', 'TypeScript', 'Python', 'FastAPI', 'PostgreSQL', 'AWS'],
            2,
        ],
        [
            'work', '2021 — 2022', 'Frontend & UI Software Engineer', 'PixelCraft Digital Studio',
            'Built high-converting interactive SaaS websites, custom PHP/Node web apps, and accessible design systems for 15+ global technology startups.',
            [
                'Achieved 98–100 Google Lighthouse Performance & Accessibility scores across all client launches',
                'Created reusable responsive layout primitives cutting project delivery time by 35%',
            ],
            ['React', 'Next.js', 'PHP', 'CSS Architecture', 'MySQL'],
            3,
        ],
        [
            'education', '2017 — 2021', 'B.Tech in Computer Science & Engineering', 'Delhi Technological University (DTU)',
            'First Class with Distinction • Focus on Distributed Systems, Algorithms & Web Engineering',
            [], [], 4,
        ],
        [
            'education', 'Certifications', 'AWS Certified Developer & Full-Stack Specialist', 'Cloud & Modern Web Architecture',
            'Cloud-native microservices, Web Security (OWASP / CSRF / SQLi Defense), and Performance Engineering',
            [], [], 5,
        ],
    ];
    $expStmt = $pdo->prepare('INSERT INTO experiences (entry_type, period, title, organization, summary, highlights_json, stack_json, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    foreach ($exps as $ex) {
        $expStmt->execute([
            $ex[0], $ex[1], $ex[2], $ex[3], $ex[4],
            json_encode($ex[5], JSON_UNESCAPED_UNICODE),
            json_encode($ex[6], JSON_UNESCAPED_UNICODE),
            $ex[7],
        ]);
    }

    // 10. FAQs
    $faqs = [
        [
            'What types of software and web projects do you take on?',
            'I build full-stack SaaS web applications, custom PHP/Node/Python backends & admin CMS platforms, high-converting product websites, REST/GraphQL APIs, and responsive frontend component systems.',
            1,
        ],
        [
            'How do we collaborate during development?',
            'Every engagement includes clear architecture specs, a live staging preview URL updated continuously via GitHub CI/CD, async progress updates, and clean, documented code in your repository from day one.',
            2,
        ],
        [
            'Can you work with an existing design (Figma) or existing PHP/JS codebase?',
            'Yes—both. I routinely translate complex Figma designs into pixel-accurate responsive code, or step into existing PHP, TypeScript, Node.js, or Python codebases to ship new features and improve performance.',
            3,
        ],
        [
            'What does handoff and post-launch support look like?',
            'You receive 100% ownership of clean source code, SQL database schemas, automated tests, deployment configs, admin panel credentials, and optional post-launch support.',
            4,
        ],
    ];
    $faqStmt = $pdo->prepare('INSERT INTO faqs (question, answer, sort_order, is_active) VALUES (?, ?, ?, 1)');
    foreach ($faqs as $fq) {
        $faqStmt->execute($fq);
    }

    // 11. Blog Articles
    $articles = [
        [
            'scalable-nextjs-typescript-architecture',
            'Architecting Scalable Web Apps & Clean Backend Boundaries',
            'Architecture', 'Oct 14, 2026', '6 min read',
            'How to structure modular frontend features, strict PDO/SQL & TypeScript boundaries, and predictable state so web applications stay fast and maintainable.',
            ['Architecture', 'TypeScript', 'PHP 8', 'Performance'],
            [
                [
                    'heading' => '1. Feature Boundaries Over Folder Sprawl',
                    'body' => 'Growing web applications slow down when UI templates, SQL queries, and validation rules leak across unrelated directories. Grouping domain repositories, strict schemas, and reusable UI partials keeps change blast radius predictable.',
                    'code' => '',
                ],
                [
                    'heading' => '2. Prepared Statements & Typed Contracts Everywhere',
                    'body' => 'Parameterizing every database query via PDO prepared statements and validating request payloads at every boundary eliminates SQL injection and runtime surprises.',
                    'code' => "\$stmt = \$pdo->prepare('SELECT id, slug, title FROM projects WHERE is_active = 1 AND category = ? ORDER BY sort_order ASC');\n\$stmt->execute([\$category]);\n\$projects = \$stmt->fetchAll(PDO::FETCH_ASSOC);",
                ],
                [
                    'heading' => '3. Zero-CLS Glassmorphic & Responsive Interfaces',
                    'body' => 'Reserve explicit aspect-ratios on visual containers, serve local WOFF2 variable fonts, and design fluid CSS Grid tiers (1260px, 1024px, 767px, 400px) so desktop precision translates effortlessly to iPads and mobile phones.',
                    'code' => '',
                ],
            ],
            1,
        ],
        [
            'sub-50ms-sql-caching-apis',
            'Engineering Sub-50ms SQL & API Responses in Production',
            'Backend', 'Sep 28, 2026', '7 min read',
            'Practical patterns for composite indexing, WAL concurrency, idempotent writes, and cache invalidation under real traffic spikes.',
            ['SQL', 'PostgreSQL', 'MySQL', 'APIs'],
            [
                [
                    'heading' => '1. Composite Indexes Aligned With Queries',
                    'body' => 'Inspect query plans before adding complexity. A composite index matching your WHERE and ORDER BY clauses turns sequential table scans into sub-millisecond index reads.',
                    'code' => "CREATE INDEX idx_projects_featured_order\nON projects (is_active, is_featured, sort_order);",
                ],
                [
                    'heading' => '2. Idempotency & CSRF Protection on Mutations',
                    'body' => 'Every state-changing form or API endpoint should verify cryptographic CSRF tokens and validate input lengths before touching persistence.',
                    'code' => '',
                ],
            ],
            2,
        ],
        [
            'crafting-60fps-glassmorphism-ui',
            'Crafting High-FPS Glassmorphic Web UIs That Stay Accessible',
            'Frontend', 'Aug 19, 2026', '5 min read',
            'Balancing translucent backdrop-filter surfaces, specular borders, contrast ratios, and GPU compositing across desktop and mobile browsers.',
            ['CSS', 'UI Engineering', 'Accessibility'],
            [
                [
                    'heading' => '1. Layered Specular Rims Create Real Depth',
                    'body' => 'Authentic frosted glass relies less on heavy blur radii and more on subtle 1px top-edge highlights paired with restrained ambient shadows.',
                    'code' => ".glass-surface {\n  background: linear-gradient(135deg, rgba(246,246,252,0.72), rgba(235,236,246,0.54));\n  backdrop-filter: blur(16px) saturate(118%);\n  border: 1px solid rgba(255, 255, 255, 0.84);\n}",
                ],
                [
                    'heading' => '2. Avoid Nested Backdrop-Filter Traps on Mobile',
                    'body' => 'Use high-opacity frosted pearl gradients on dropdown menus inside blurred navbars so text remains effortlessly legible over hero headings.',
                    'code' => '',
                ],
            ],
            3,
        ],
    ];
    $artStmt = $pdo->prepare('
        INSERT INTO blog_posts (slug, title, category, published_date, read_time, excerpt, tags_json, sections_json, is_published, sort_order)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)
    ');
    foreach ($articles as $ar) {
        $artStmt->execute([
            $ar[0], $ar[1], $ar[2], $ar[3], $ar[4], $ar[5],
            json_encode($ar[6], JSON_UNESCAPED_UNICODE),
            json_encode($ar[7], JSON_UNESCAPED_UNICODE),
            $ar[8],
        ]);
    }

    // 12. Sample Welcome Contact Inquiry in Admin Inbox
    $msgStmt = $pdo->prepare('
        INSERT INTO contact_messages (sender_name, sender_email, project_scope, timeline, message_body, status, ip_address, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ');
    $msgStmt->execute([
        'Sarah Jenkins (VP Engineering, FinTech Co.)',
        'sarah.jenkins@example.com',
        'Full-Stack Web Application',
        '1–3 Months',
        'Hi Jordan! We loved your Finova & Pulse case studies and would love to discuss architecting our upcoming customer portal.',
        'unread',
        '127.0.0.1',
        $now,
    ]);
}
