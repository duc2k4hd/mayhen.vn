<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Services\EmailTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminEmailTemplateController extends Controller
{
    public function __construct(
        protected EmailTemplateService $templateService
    ) {}

    /**
     * Danh sách mẫu thông báo & email tự động (Mục 148 PRD V3).
     */
    public function index(Request $request): View
    {
        // Khởi tạo các mẫu thiết yếu nếu bảng trống
        $this->templateService->seedDefaultTemplates();

        $query = EmailTemplate::query();

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $search = '%'.trim($request->search).'%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                    ->orWhere('code', 'like', $search)
                    ->orWhere('subject', 'like', $search);
            });
        }

        $templates = $query->orderBy('id', 'asc')->paginate(15)->withQueryString();

        $stats = [
            'total' => EmailTemplate::count(),
            'email' => EmailTemplate::where('channel', EmailTemplate::CHANNEL_EMAIL)->count(),
            'sms' => EmailTemplate::where('channel', EmailTemplate::CHANNEL_SMS)->count(),
            'active' => EmailTemplate::where('is_active', true)->count(),
        ];

        return view('admin.pages.email-templates.index', compact('templates', 'stats'));
    }

    /**
     * Màn hình chỉnh sửa mẫu thông báo.
     */
    public function edit(EmailTemplate $template): View
    {
        $sampleData = $this->templateService->getSampleVariables($template->code);

        return view('admin.pages.email-templates.edit', compact('template', 'sampleData'));
    }

    /**
     * Cập nhật nội dung mẫu.
     */
    public function update(Request $request, EmailTemplate $template): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'subject' => 'required|string|max:255',
            'body_html' => 'required|string',
            'body_text' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Vui lòng nhập tên nhận diện của mẫu.',
            'subject.required' => 'Vui lòng nhập tiêu đề email.',
            'body_html.required' => 'Vui lòng nhập nội dung HTML của email.',
        ]);

        $this->templateService->updateTemplate($template, [
            'name' => $validated['name'],
            'subject' => $validated['subject'],
            'body_html' => $validated['body_html'],
            'body_text' => $validated['body_text'] ?? null,
            'is_active' => $request->has('is_active'),
        ], $request->user());

        return redirect()->route('admin.email-templates.edit', $template)
            ->with('success', "Cập nhật mẫu email \"{$template->name}\" thành công.");
    }

    /**
     * Xem trước mẫu email bằng dữ liệu mẫu (Render trong iframe).
     */
    public function preview(EmailTemplate $template): Response
    {
        $sampleData = $this->templateService->getSampleVariables($template->code);
        $rendered = $this->templateService->render($template, $sampleData);

        return response($rendered['body_html'], 200)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Gửi email thử nghiệm tới hòm thư kiểm tra.
     */
    public function sendTest(Request $request, EmailTemplate $template): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'test_email' => 'required|email|max:100',
        ], [
            'test_email.required' => 'Vui lòng nhập địa chỉ email nhận thư thử nghiệm.',
            'test_email.email' => 'Địa chỉ email không đúng định dạng.',
        ]);

        $success = $this->templateService->sendTestEmail($template, $validated['test_email']);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $success
                    ? "Đã gửi email thử nghiệm mẫu \"{$template->name}\" tới {$validated['test_email']} thành công."
                    : 'Gửi email thử nghiệm không thành công. Vui lòng kiểm tra cấu hình Mail server.',
                'last_tested_at' => now()->format('H:i d/m/Y'),
            ]);
        }

        if ($success) {
            return back()->with('success', "Đã gửi email thử nghiệm tới {$validated['test_email']} thành công.");
        }

        return back()->with('error', 'Gửi email thử nghiệm không thành công. Vui lòng kiểm tra cấu hình Mail.');
    }

    /**
     * Khôi phục mẫu về nội dung mặc định của Mây Hẹn Atelier.
     */
    public function reset(Request $request, EmailTemplate $template): RedirectResponse
    {
        $success = $this->templateService->resetToDefault($template, $request->user());

        if ($success) {
            return redirect()->route('admin.email-templates.edit', $template)
                ->with('success', "Đã khôi phục mẫu email \"{$template->name}\" về nội dung mặc định ban đầu.");
        }

        return back()->with('error', 'Không tìm thấy cấu hình mặc định gốc cho mẫu này.');
    }

    /**
     * Bật / tắt nhanh trạng thái kích hoạt.
     */
    public function toggleActive(EmailTemplate $template): JsonResponse
    {
        $template->update([
            'is_active' => ! $template->is_active,
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $template->is_active,
            'message' => $template->is_active ? 'Đã kích hoạt mẫu thông báo.' : 'Đã tạm ngừng mẫu thông báo.',
        ]);
    }
}
