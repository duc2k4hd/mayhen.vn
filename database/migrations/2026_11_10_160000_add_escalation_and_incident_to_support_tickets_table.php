<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->timestamp('sla_deadline_at')->nullable()->after('priority')->comment('Hạn chót SLA phản hồi');
            $table->timestamp('escalated_at')->nullable()->after('sla_deadline_at')->comment('Thời điểm tự động/thủ công leo thang');
            $table->string('escalation_level', 30)->nullable()->after('escalated_at')->comment('Cấp độ leo thang: manager, super_admin');
            $table->string('escalation_reason', 255)->nullable()->after('escalation_level')->comment('Lý do leo thang SLA');
            $table->boolean('is_incident')->default(false)->after('escalation_reason')->comment('Cờ đánh dấu Sự Cố Vận Hành');
            $table->string('incident_severity', 10)->nullable()->after('is_incident')->comment('Mức độ sự cố: sev1, sev2, sev3');
            $table->timestamp('incident_declared_at')->nullable()->after('incident_severity')->comment('Thời điểm công bố sự cố');
            $table->foreignId('incident_declared_by')->nullable()->after('incident_declared_at')->constrained('users')->nullOnDelete()->comment('Admin công bố sự cố');
            $table->text('root_cause')->nullable()->after('incident_declared_by')->comment('Nguyên nhân gốc rễ sự cố');
            $table->text('mitigation_action')->nullable()->after('root_cause')->comment('Biện pháp khắc phục đã triển khai');
            $table->text('postmortem_notes')->nullable()->after('mitigation_action')->comment('Biên bản rút kinh nghiệm Postmortem');
            $table->timestamp('customer_confirmed_resolved_at')->nullable()->after('resolved_at')->comment('Thời điểm khách xác nhận sự cố đã hết');

            $table->index(['is_incident', 'incident_severity']);
            $table->index(['escalated_at', 'escalation_level']);
            $table->index('sla_deadline_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropIndex(['is_incident', 'incident_severity']);
            $table->dropIndex(['escalated_at', 'escalation_level']);
            $table->dropIndex(['sla_deadline_at']);

            $table->dropForeign(['incident_declared_by']);
            $table->dropColumn([
                'sla_deadline_at',
                'escalated_at',
                'escalation_level',
                'escalation_reason',
                'is_incident',
                'incident_severity',
                'incident_declared_at',
                'incident_declared_by',
                'root_cause',
                'mitigation_action',
                'postmortem_notes',
                'customer_confirmed_resolved_at',
            ]);
        });
    }
};
