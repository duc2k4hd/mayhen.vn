<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Mây Hẹn Autonomous Intelligence Engine V2 — Resource & Safety Policy
    |--------------------------------------------------------------------------
    | Nguyên tắc tối cao: Production Traffic > Business Jobs > AI Learning Jobs.
    | Mặc định sau khi cài đặt mới, AI Learning ở trạng thái TẮT (false) và ngân
    | sách bằng 0 (chặn chi tiêu cho đến khi Admin cấu hình rõ ràng).
    */

    'learning_enabled' => filter_var(env('AI_LEARNING_ENABLED', false), FILTER_VALIDATE_BOOL),

    // Các chế độ vận hành: conservative | balanced | performance | paused
    'mode' => (string) env('AI_LEARNING_MODE', 'conservative'),

    'max_concurrency' => max(1, (int) env('AI_LEARNING_MAX_CONCURRENCY', 1)),

    'max_tasks_per_cycle' => max(1, min(10, (int) env('AI_LEARNING_MAX_TASKS_PER_CYCLE', 2))),

    'cycle_budget_seconds' => max(5, min(120, (int) env('AI_LEARNING_CYCLE_BUDGET_SECONDS', 20))),

    'max_memory_mb' => max(64, min(1024, (int) env('AI_LEARNING_MAX_MEMORY_MB', 128))),

    'request_timeout_seconds' => max(5, min(90, (int) env('AI_LEARNING_REQUEST_TIMEOUT_SECONDS', 45))),

    // Giá trị 0 = CHẶN sử dụng LLM tự học cho đến khi Admin cấu hình ngân sách thực tế
    'daily_token_budget' => max(0, (int) env('AI_LEARNING_DAILY_TOKEN_BUDGET', 0)),

    // Ngân sách chi phí tối đa/ngày cho Learning (VNĐ). Giá trị 0 = CHẶN cho tới khi Admin cấu hình
    'daily_cost_budget' => max(0, (int) env('AI_LEARNING_DAILY_COST_BUDGET', 0)),

    'auto_pause' => filter_var(env('AI_LEARNING_AUTO_PAUSE', true), FILTER_VALIDATE_BOOL),

    'admin_approval_required' => filter_var(env('AI_LEARNING_ADMIN_APPROVAL_REQUIRED', true), FILTER_VALIDATE_BOOL),

    // V3 Autonomous Web Research Policy (thay thế chính sách chỉ đọc nội bộ V2; Admin có quyền bật/tắt tức thì)
    'external_research_enabled' => filter_var(env('AI_RESEARCH_EXTERNAL_ENABLED', true), FILTER_VALIDATE_BOOL),

    // Cấm tự đẩy candidate chưa xác minh lên tri thức chính thức
    'allow_unverified_promotion' => filter_var(env('AI_LEARNING_ALLOW_UNVERIFIED_PROMOTION', false), FILTER_VALIDATE_BOOL),

    /*
    |--------------------------------------------------------------------------
    | Cấu hình Autonomous Web Research Engine V3 (Tôn trọng robots.txt & SSRF Guard)
    |--------------------------------------------------------------------------
    */
    'research' => [
        'country' => (string) env('AI_RESEARCH_COUNTRY', 'VN'),
        'language' => (string) env('AI_RESEARCH_LANGUAGE', 'vi'),
        'autonomy_level' => (string) env('AI_RESEARCH_AUTONOMY_LEVEL', 'supervised'), // conservative | supervised | autonomous
        'max_urls_per_cycle' => max(1, min(6, (int) env('AI_RESEARCH_MAX_URLS_PER_CYCLE', 2))),
        'max_queries_per_discover' => max(1, min(5, (int) env('AI_RESEARCH_MAX_QUERIES_PER_DISCOVER', 3))),
        'fetch_timeout_seconds' => max(4, min(20, (int) env('AI_RESEARCH_FETCH_TIMEOUT_SECONDS', 8))),
        'max_response_bytes' => max(65536, min(1048576, (int) env('AI_RESEARCH_MAX_RESPONSE_BYTES', 524288))),
        'domain_rate_limit_seconds' => max(2, min(30, (int) env('AI_RESEARCH_DOMAIN_RATE_LIMIT_SECONDS', 3))),
        'recheck_days' => max(3, min(90, (int) env('AI_RESEARCH_RECHECK_DAYS', 14))),
        'user_agent' => 'MayHenResearchBot/3.0 (+https://mayhen.vn; ethical-research)',
        'search_api_provider' => (string) env('AI_SEARCH_API_PROVIDER', ''), // optional: tavily | serper | brave
        'search_api_key' => (string) env('AI_SEARCH_API_KEY', ''),
        'disallowed_domains' => [
            'facebook.com', 'fb.com', 'instagram.com', 'tiktok.com', 'youtube.com', 'youtu.be',
            'twitter.com', 'x.com', 'linkedin.com', 'pinterest.com', 'zalo.me', 'shopee.vn', 'lazada.vn',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cấu hình từng chế độ tài nguyên (Resource Profiles)
    |--------------------------------------------------------------------------
    */
    'modes' => [
        'conservative' => [
            'label' => 'Bảo thủ (Shared Hosting yếu)',
            'max_tasks_per_cycle' => 2,
            'cycle_budget_seconds' => 20,
            'sleep_between_tasks_ms' => 2500,
            'max_memory_mb' => 128,
            'max_db_latency_ms' => 120,
            'max_business_queue_backlog' => 20,
            'max_cpu_load' => 2.5,
        ],
        'balanced' => [
            'label' => 'Cân bằng (VPS nhỏ)',
            'max_tasks_per_cycle' => 4,
            'cycle_budget_seconds' => 35,
            'sleep_between_tasks_ms' => 1500,
            'max_memory_mb' => 256,
            'max_db_latency_ms' => 200,
            'max_business_queue_backlog' => 50,
            'max_cpu_load' => 4.0,
        ],
        'performance' => [
            'label' => 'Hiệu năng cao (Server riêng)',
            'max_tasks_per_cycle' => 6,
            'cycle_budget_seconds' => 50,
            'sleep_between_tasks_ms' => 800,
            'max_memory_mb' => 512,
            'max_db_latency_ms' => 300,
            'max_business_queue_backlog' => 150,
            'max_cpu_load' => 6.0,
        ],
        'paused' => [
            'label' => 'Tạm dừng toàn bộ học nền',
            'max_tasks_per_cycle' => 0,
            'cycle_budget_seconds' => 0,
            'sleep_between_tasks_ms' => 5000,
            'max_memory_mb' => 128,
            'max_db_latency_ms' => 120,
            'max_business_queue_backlog' => 0,
            'max_cpu_load' => 0.0,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Progressive Domain Learning (Level 0 -> Level 4: Học từ hẹp đến rộng)
    |--------------------------------------------------------------------------
    */
    'domain_levels' => [
        0 => [
            'code' => 'L0_CORE_IDENTITY',
            'name' => 'Level 0 — Bản sắc & Chính sách Cốt lõi',
            'description' => 'Mây Hẹn là gì, các gói dịch vụ, bảng giá hiện hành, phạm vi hỗ trợ và cam kết bảo mật.',
            'min_approved_to_advance' => 2,
        ],
        1 => [
            'code' => 'L1_PRODUCT_DEEP',
            'name' => 'Level 1 — Chuyên sâu Sản phẩm & Tính năng',
            'description' => 'Kho mẫu thiệp cưới, Studio tạo thiệp, RSVP, Khách mời, Bàn tiệc, QR Check-in, Album ảnh, Hộp thời gian, Thanh toán VietQR.',
            'min_approved_to_advance' => 4,
        ],
        2 => [
            'code' => 'L2_WORKFLOW_GRAPH',
            'name' => 'Level 2 — Quan hệ & Quy trình Liên kết (Knowledge Graph)',
            'description' => 'Hiểu liên kết giữa Thiệp cưới ↔ Khách mời ↔ RSVP ↔ Bàn tiệc ↔ Check-in ↔ Gói nâng cấp.',
            'min_relations_to_advance' => 5,
        ],
        3 => [
            'code' => 'L3_CUSTOMER_BI',
            'name' => 'Level 3 — Nhu cầu Khách hàng & Business Intelligence',
            'description' => 'Phân tích khoảng trống tri thức (Knowledge Gaps), câu hỏi lặp lại và điểm nghẽn chuyển đổi có cỡ mẫu thực tế.',
            'min_sample_size' => 2,
        ],
        4 => [
            'code' => 'L4_ADVANCED_DOMAIN',
            'name' => 'Level 4 — Mở rộng Tri thức Chuyên sâu có Bằng chứng',
            'description' => 'Chỉ mở khi L0–L2 đã đạt kiểm chứng và phát sinh khoảng trống thực tế từ dữ liệu nội bộ.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Ngưỡng Chống Phình Database & Retention
    |--------------------------------------------------------------------------
    */
    'storage_quotas' => [
        'max_pending_candidates' => 250,
        'candidate_expiry_days' => 30,
        'completed_task_retention_days' => 14,
        'stale_lock_timeout_seconds' => 120,
        'max_consecutive_worker_failures' => 5,
        'max_knowledge_rows_warning' => 5000,
        'max_log_rows_warning' => 100000,
    ],
];
