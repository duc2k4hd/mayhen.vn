<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Bảng điều khiển Quản Lý Yêu Cầu Liên Hệ & Báo Giá (Mục 7.15 DOCX).
     */
    public function index(Request $request): View
    {
        $query = Contact::with('assignedStaff')->latest();

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Lọc theo nhu cầu tư vấn
        if ($request->filled('need')) {
            $query->where('need', $request->need);
        }

        // Lọc theo nhân viên phụ trách
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Tìm kiếm theo tên hoặc số điện thoại
        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('admin_notes', 'like', "%{$term}%");
            });
        }

        $contacts = $query->paginate(20)->withQueryString();

        // Thống kê KPI tổng quan
        $kpis = [
            'total' => Contact::count(),
            'new' => Contact::where('status', 'new')->count(),
            'consulting' => Contact::whereIn('status', ['contacted', 'consulting', 'quoted'])->count(),
            'converted' => Contact::where('status', 'converted')->count(),
            'closed' => Contact::where('status', 'closed')->count(),
        ];

        // Danh sách chuyên viên để phân công
        $staffList = User::whereIn('role', ['admin', 'manager', 'staff'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('admin.pages.contacts.index', compact('contacts', 'kpis', 'staffList'));
    }

    /**
     * Cập nhật trạng thái, nhân viên phụ trách và ghi chú tư vấn.
     */
    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:new,contacted,consulting,quoted,converted,closed'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'admin_notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $oldStatus = $contact->status;
        $newStatus = $validated['status'];

        $updateData = [
            'status' => $newStatus,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'admin_notes' => $validated['admin_notes'] ?? null,
        ];

        // Tự động gán mốc thời gian liên hệ đầu tiên
        if (in_array($newStatus, ['contacted', 'consulting', 'quoted', 'converted'], true) && ! $contact->contacted_at) {
            $updateData['contacted_at'] = now();
        }

        // Tự động gán mốc thời gian chốt đơn
        if ($newStatus === 'converted' && ! $contact->converted_at) {
            $updateData['converted_at'] = now();
        }

        $contact->update($updateData);

        AuditLogger::log(
            action: 'CONTACT_UPDATED',
            targetType: 'Contact',
            targetId: $contact->id,
            description: "Cập nhật yêu cầu tư vấn #{$contact->id} ({$contact->name}) sang trạng thái: {$contact->status_label}",
            details: [
                'contact_id' => $contact->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'assigned_to' => $contact->assigned_to,
            ]
        );

        return back()->with('success', 'Đã cập nhật thông tin xử lý yêu cầu liên hệ thành công.');
    }

    /**
     * Xóa yêu cầu liên hệ rác/spam.
     */
    public function destroy(Contact $contact): RedirectResponse
    {
        $contactId = $contact->id;
        $name = $contact->name;

        $contact->delete();

        AuditLogger::log(
            action: 'CONTACT_DELETED',
            targetType: 'Contact',
            targetId: $contactId,
            description: "Đã xóa yêu cầu tư vấn #{$contactId} của khách hàng {$name}",
            details: ['contact_id' => $contactId, 'name' => $name]
        );

        return back()->with('success', 'Đã xóa yêu cầu liên hệ.');
    }
}
