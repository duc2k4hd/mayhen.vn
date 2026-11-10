<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\AuditLogger;
use App\Services\CsvSanitizerService;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CouponController extends Controller
{
    /**
     * Danh sách mã giảm giá & Khuyến mãi (Wave 06 - ADM-A11).
     */
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = $request->input('status');

        $query = Coupon::query()->latest('id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $now = Carbon::now();

        if ($status === 'active') {
            $query->where('is_active', true)
                ->where(function ($q) use ($now) {
                    $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                })
                ->where(function ($q) use ($now) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
                })
                ->where(function ($q) {
                    $q->whereNull('usage_limit')->orWhereColumn('used_count', '<', 'usage_limit');
                });
        } elseif ($status === 'expired') {
            $query->whereNotNull('expires_at')->where('expires_at', '<', $now);
        } elseif ($status === 'exhausted') {
            $query->whereNotNull('usage_limit')->whereColumn('used_count', '>=', 'usage_limit');
        } elseif ($status === 'inactive') {
            $query->where('is_active', false);
        }

        $coupons = $query->paginate(20)->withQueryString();

        $allCoupons = Coupon::all();
        $stats = [
            'total_coupons' => $allCoupons->count(),
            'active_coupons' => $allCoupons->filter(fn ($c) => $c->isValid())->count(),
            'total_used_count' => $allCoupons->sum('used_count'),
            'expired_coupons' => $allCoupons->filter(fn ($c) => $c->expires_at && $c->expires_at->isPast())->count(),
        ];

        return view('admin.pages.coupons.index', compact('coupons', 'stats'));
    }

    /**
     * Thêm mới mã giảm giá.
     */
    public function store(Request $request): RedirectResponse
    {
        // Chuẩn hóa mã viết hoa và loại bỏ khoảng trắng thừa
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code', ''))),
        ]);

        $validated = $request->validate([
            'code' => 'required|string|max:50|regex:/^[A-Z0-9_\-]+$/|unique:coupons,code',
            'name' => 'required|string|max:150',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|integer|min:1',
            'min_order_amount' => 'nullable|integer|min:0',
            'max_discount_amount' => 'nullable|integer|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ], [
            'code.required' => 'Mã ưu đãi không được để trống.',
            'code.regex' => 'Mã ưu đãi chỉ được chứa chữ hoa, số và dấu gạch ngang/dưới (VD: MAYHEN20, VIP_2026).',
            'code.unique' => 'Mã ưu đãi này đã tồn tại trong hệ thống.',
            'discount_value.min' => 'Giá trị giảm phải lớn hơn 0.',
            'expires_at.after_or_equal' => 'Ngày hết hạn phải sau hoặc bằng ngày bắt đầu.',
        ]);

        // Phòng vệ giảm giá percent không quá 100%
        if ($validated['discount_type'] === 'percent' && $validated['discount_value'] > 100) {
            return back()->withInput()->withErrors(['discount_value' => 'Mức giảm theo phần trăm không được vượt quá 100%.']);
        }

        $coupon = Coupon::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'min_order_amount' => $validated['min_order_amount'] ?? 0,
            'max_discount_amount' => $validated['discount_type'] === 'percent' ? ($validated['max_discount_amount'] ?? null) : null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'used_count' => 0,
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogger::log(
            'coupon_created',
            'Coupon',
            $coupon->id,
            "Tạo mới mã giảm giá [{$coupon->code}] ({$coupon->name}) - Giảm {$coupon->formatted_discount}",
            [
                'coupon_id' => $coupon->id,
                'code' => $coupon->code,
                'discount_type' => $coupon->discount_type,
                'discount_value' => $coupon->discount_value,
                'usage_limit' => $coupon->usage_limit,
            ]
        );

        return redirect()->route('admin.coupons.index')
            ->with('success', "Mã giảm giá \"{$coupon->code}\" đã được tạo thành công.");
    }

    /**
     * Cập nhật thông tin mã giảm giá.
     */
    public function update(Request $request, Coupon $coupon): RedirectResponse
    {
        $request->merge([
            'code' => strtoupper(trim((string) $request->input('code', ''))),
        ]);

        $validated = $request->validate([
            'code' => "required|string|max:50|regex:/^[A-Z0-9_\\-]+$/|unique:coupons,code,{$coupon->id}",
            'name' => 'required|string|max:150',
            'discount_type' => 'required|in:percent,fixed',
            'discount_value' => 'required|integer|min:1',
            'min_order_amount' => 'nullable|integer|min:0',
            'max_discount_amount' => 'nullable|integer|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ], [
            'code.required' => 'Mã ưu đãi không được để trống.',
            'code.regex' => 'Mã ưu đãi chỉ được chứa chữ hoa, số và dấu gạch.',
            'code.unique' => 'Mã ưu đãi này đã tồn tại trong hệ thống.',
            'discount_value.min' => 'Giá trị giảm phải lớn hơn 0.',
            'expires_at.after_or_equal' => 'Ngày hết hạn phải sau hoặc bằng ngày bắt đầu.',
        ]);

        if ($validated['discount_type'] === 'percent' && $validated['discount_value'] > 100) {
            return back()->withInput()->withErrors(['discount_value' => 'Mức giảm theo phần trăm không được vượt quá 100%.']);
        }

        $coupon->update([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'discount_type' => $validated['discount_type'],
            'discount_value' => $validated['discount_value'],
            'min_order_amount' => $validated['min_order_amount'] ?? 0,
            'max_discount_amount' => $validated['discount_type'] === 'percent' ? ($validated['max_discount_amount'] ?? null) : null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogger::log(
            'coupon_updated',
            'Coupon',
            $coupon->id,
            "Cập nhật mã giảm giá #{$coupon->id} [{$coupon->code}]",
            ['coupon_id' => $coupon->id, 'code' => $coupon->code]
        );

        return redirect()->route('admin.coupons.index')
            ->with('success', "Mã giảm giá \"{$coupon->code}\" đã được cập nhật thành công.");
    }

    /**
     * Bật / Tắt trạng thái mã giảm giá.
     */
    public function toggleActive(Coupon $coupon): RedirectResponse
    {
        $newStatus = ! $coupon->is_active;
        $coupon->update(['is_active' => $newStatus]);

        AuditLogger::log(
            'coupon_status_toggled',
            'Coupon',
            $coupon->id,
            ($newStatus ? 'Kích hoạt' : 'Tạm dừng')." mã ưu đãi [{$coupon->code}]",
            ['coupon_id' => $coupon->id, 'code' => $coupon->code, 'is_active' => $newStatus]
        );

        $statusText = $newStatus ? 'kích hoạt' : 'tạm dừng';

        return back()->with('success', "Đã {$statusText} mã ưu đãi \"{$coupon->code}\".");
    }

    /**
     * Xóa mã giảm giá (Phòng vệ: cảnh báo nếu đã có lượt dùng thực tế).
     */
    public function destroy(Coupon $coupon): RedirectResponse
    {
        $code = $coupon->code;
        $usedCount = $coupon->used_count;

        // Nếu mã đã được sử dụng thực tế, chuyển sang vô hiệu hóa thay vì xóa để bảo toàn lịch sử kế toán
        if ($usedCount > 0) {
            $coupon->update(['is_active' => false]);

            AuditLogger::log(
                'coupon_deactivated_defense',
                'Coupon',
                $coupon->id,
                "Vô hiệu hóa mã giảm giá đã dùng [{$code}] thay vì xóa cứng (Đã dùng {$usedCount} lần)",
                ['coupon_id' => $coupon->id, 'code' => $code, 'used_count' => $usedCount]
            );

            return back()->with('success', "Mã \"{$code}\" đã được sử dụng {$usedCount} lần trong lịch sử nên đã được tự động chuyển sang trạng thái Vô hiệu hóa để bảo vệ sổ cái.");
        }

        $coupon->delete();

        AuditLogger::log(
            'coupon_deleted',
            'Coupon',
            $coupon->id,
            "Xóa mã giảm giá chưa sử dụng [{$code}]",
            ['code' => $code]
        );

        return back()->with('success', "Đã xóa mã ưu đãi \"{$code}\" thành công.");
    }

    /**
     * Xuất danh sách mã giảm giá dạng CSV UTF-8 BOM chống CSV Injection.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        AuditLogger::log('coupon_export_csv', 'Coupon', 'ALL', 'Xuất danh sách mã giảm giá toàn hệ thống dạng CSV');

        $coupons = Coupon::orderBy('id', 'desc')->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="mayhen-coupons-'.date('Ymd-His').'.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($coupons) {
            $handle = fopen('php://output', 'w');
            // Thêm BOM UTF-8 để Excel hiển thị đúng dấu tiếng Việt
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'ID',
                'Mã Ưu Đãi',
                'Tên Chiến Dịch',
                'Loại Giảm Giá',
                'Mức Giảm',
                'Đơn Tối Thiểu',
                'Giảm Tối Đa',
                'Giới Hạn Lượt Dùng',
                'Đã Sử Dụng',
                'Bắt Đầu',
                'Hết Hạn',
                'Trạng Thái',
            ]);

            foreach ($coupons as $c) {
                fputcsv($handle, [
                    $c->id,
                    CsvSanitizerService::sanitize($c->code),
                    CsvSanitizerService::sanitize($c->name),
                    $c->discount_type === 'percent' ? 'Phần trăm (%)' : 'Số tiền cố định',
                    $c->formatted_discount,
                    number_format($c->min_order_amount, 0, ',', '.').'đ',
                    $c->max_discount_amount ? number_format($c->max_discount_amount, 0, ',', '.').'đ' : 'Không giới hạn',
                    $c->usage_limit ?? 'Vô hạn',
                    $c->used_count,
                    $c->starts_at ? $c->starts_at->format('d/m/Y H:i') : 'Ngay lập tức',
                    $c->expires_at ? $c->expires_at->format('d/m/Y H:i') : 'Vĩnh viễn',
                    $c->is_active ? 'Kích hoạt' : 'Vô hiệu hóa',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
