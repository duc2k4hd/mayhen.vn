<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'current_plan_slug')) {
                    $table->string('current_plan_slug', 20)->default('premium')->after('role')->index();
                }
                if (! Schema::hasColumn('users', 'plan_started_at')) {
                    $table->timestamp('plan_started_at')->nullable()->after('current_plan_slug');
                }
                if (! Schema::hasColumn('users', 'plan_expires_at')) {
                    $table->timestamp('plan_expires_at')->nullable()->after('plan_started_at')->index();
                }
                if (! Schema::hasColumn('users', 'active_plan_order_id')) {
                    $table->unsignedBigInteger('active_plan_order_id')->nullable()->after('plan_expires_at')->index();
                }
            });

            DB::table('users')
                ->where('email', 'guest@mayhen.vn')
                ->update(['current_plan_slug' => 'free']);

            DB::table('users')
                ->where('email', '!=', 'guest@mayhen.vn')
                ->where(function ($q) {
                    $q->whereNull('current_plan_slug')->orWhere('current_plan_slug', '');
                })
                ->update(['current_plan_slug' => 'premium']);
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'period_months')) {
                    $table->unsignedTinyInteger('period_months')->nullable()->after('payment_method');
                }
                if (! Schema::hasColumn('orders', 'starts_at')) {
                    $table->timestamp('starts_at')->nullable()->after('paid_at');
                }
                if (! Schema::hasColumn('orders', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->after('starts_at')->index();
                }
                if (! Schema::hasColumn('orders', 'is_renewal')) {
                    $table->boolean('is_renewal')->default(false)->after('expires_at');
                }
            });
        }

        if (Schema::hasTable('invitations')) {
            Schema::table('invitations', function (Blueprint $table) {
                if (! Schema::hasColumn('invitations', 'owner_user_id')) {
                    $table->unsignedBigInteger('owner_user_id')->nullable()->after('wedding_id')->index();
                }
                if (! Schema::hasColumn('invitations', 'guest_device_id')) {
                    $table->string('guest_device_id', 64)->nullable()->after('owner_user_id')->index();
                }
                if (! Schema::hasColumn('invitations', 'plan_slug')) {
                    $table->string('plan_slug', 20)->default('free')->after('guest_device_id')->index();
                }
                if (! Schema::hasColumn('invitations', 'plan_cycle_order_id')) {
                    $table->unsignedBigInteger('plan_cycle_order_id')->nullable()->after('plan_slug')->index();
                }
                if (! Schema::hasColumn('invitations', 'deleted_by_user_at')) {
                    $table->timestamp('deleted_by_user_at')->nullable()->after('expires_at')->index();
                }
                if (! Schema::hasColumn('invitations', 'purge_queued_at')) {
                    $table->timestamp('purge_queued_at')->nullable()->after('deleted_by_user_at')->index();
                }
                if (! Schema::hasColumn('invitations', 'purged_at')) {
                    $table->timestamp('purged_at')->nullable()->after('purge_queued_at')->index();
                }
                if (! Schema::hasColumn('invitations', 'admin_archive_snapshot')) {
                    $table->json('admin_archive_snapshot')->nullable()->after('purged_at');
                }
                if (! Schema::hasColumn('invitations', 'deleted_at')) {
                    $table->softDeletes();
                }
            });

            // Backfill owner_user_id từ bảng weddings
            if (Schema::hasTable('weddings')) {
                $rows = DB::table('invitations')
                    ->join('weddings', 'weddings.id', '=', 'invitations.wedding_id')
                    ->whereNull('invitations.owner_user_id')
                    ->select('invitations.id', 'weddings.user_id')
                    ->get();

                foreach ($rows as $row) {
                    if ($row->user_id !== null) {
                        DB::table('invitations')
                            ->where('id', $row->id)
                            ->update(['owner_user_id' => $row->user_id]);
                    }
                }
            }
        }

        // Đồng bộ lại thông số 4 gói dịch vụ chuẩn mới (nếu đã tồn tại trong bảng plans)
        if (Schema::hasTable('plans')) {
            $planUpdates = [
                'free' => ['duration_days' => 90, 'max_invitations' => 1],
                'premium' => ['duration_days' => 90, 'max_invitations' => 3],
                'plus' => ['duration_days' => 180, 'max_invitations' => 10],
                'pro' => ['duration_days' => 365, 'max_invitations' => 30],
            ];

            foreach ($planUpdates as $slug => $attrs) {
                DB::table('plans')->where('slug', $slug)->update($attrs);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invitations')) {
            Schema::table('invitations', function (Blueprint $table) {
                $dropCols = array_values(array_filter([
                    'owner_user_id',
                    'guest_device_id',
                    'plan_slug',
                    'plan_cycle_order_id',
                    'deleted_by_user_at',
                    'purge_queued_at',
                    'purged_at',
                    'admin_archive_snapshot',
                    'deleted_at',
                ], fn (string $col) => Schema::hasColumn('invitations', $col)));

                if ($dropCols !== []) {
                    $table->dropColumn($dropCols);
                }
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $dropCols = array_values(array_filter([
                    'period_months',
                    'starts_at',
                    'expires_at',
                    'is_renewal',
                ], fn (string $col) => Schema::hasColumn('orders', $col)));

                if ($dropCols !== []) {
                    $table->dropColumn($dropCols);
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $dropCols = array_values(array_filter([
                    'current_plan_slug',
                    'plan_started_at',
                    'plan_expires_at',
                    'active_plan_order_id',
                ], fn (string $col) => Schema::hasColumn('users', $col)));

                if ($dropCols !== []) {
                    $table->dropColumn($dropCols);
                }
            });
        }
    }
};
