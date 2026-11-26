<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tự động kiểm tra và gửi thư thời gian kỷ niệm ngày cưới lúc 08:00 sáng
Schedule::command('mayhen:check-time-capsules')->dailyAt('08:00');

// Tự động dọn dẹp dữ liệu AI Memory & Chat Logs quá hạn Retention lúc 03:30 sáng
Schedule::command('mayhen:ai-retention-purge')->dailyAt('03:30');

// Tự động lưu bản tóm tắt phân tích & dọn sạch dữ liệu/link của 9 công cụ quá hạn 6 tháng lúc 03:45 sáng
Schedule::command('mayhen:prune-expired-tools --months=6')
    ->dailyAt('03:45')
    ->withoutOverlapping();

// Tự động kiểm tra hết hạn gói Pro/Plus (gỡ gói hết hạn về Premium, luôn giữ Pro cho Admin) và đóng đơn pending quá hạn 30 phút
Schedule::command('mayhen:sync-plan-expirations')
    ->everyFiveMinutes()
    ->withoutOverlapping();

// Tự động quét và đưa các thiệp hết hạn (3 tháng / 6 tháng / 1 năm) hoặc khách đã xóa vào hàng đợi xóa media vĩnh viễn & lưu hồ sơ trọn đời cho Admin
Schedule::command('mayhen:purge-expired-invitations --limit=100')
    ->hourly()
    ->withoutOverlapping();

// Chạy nền từng vi-bước AI Learning siêu nhẹ mỗi phút (nếu có phiên đang running, thoát ngay sau 1 bước để không quá tải hosting yếu)
Schedule::command('mayhen:ai-self-train-step')
    ->everyMinute()
    ->withoutOverlapping();

// Autonomous Intelligence Engine V4 (tự học liên tục mỗi phút, nhịp đều đặn 3 tác vụ/phút cho đến khi hết hạn mức token/tiền ngày đã cài trong Admin)
Schedule::command('ai:brain:work --once --budget-seconds=40 --max-tasks=3')
    ->everyMinute()
    ->withoutOverlapping(2);

// Tự động xử lý hàng đợi gửi mail trên Hosting (dừng khi hết việc để tiết kiệm RAM & CPU, không bị CloudLinux kill)
Schedule::command('queue:work --stop-when-empty --tries=3 --timeout=25')
    ->everyMinute()
    ->withoutOverlapping();
