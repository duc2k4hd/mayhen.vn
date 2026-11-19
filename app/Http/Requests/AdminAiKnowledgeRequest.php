<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\AiKnowledgeEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminAiKnowledgeRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && method_exists($user, 'canAccessAdminPanel') && $user->canAccessAdminPanel();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'category' => ['required', 'string', Rule::in(array_keys(AiKnowledgeEntry::CATEGORIES))],
            'title' => ['required', 'string', 'min:3', 'max:190'],
            'keywords' => ['nullable', 'string', 'max:500'],
            'answer' => ['required', 'string', 'min:5', 'max:2000'],
            'template_slug' => ['nullable', 'string', 'max:120', 'exists:invitation_templates,slug'],
            'doc_url' => ['nullable', 'string', 'max:190', 'regex:/^\/docs(\/[a-zA-Z0-9\-_]+)*$/'],
            'priority' => ['nullable', 'integer', 'min:1', 'max:10'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'doc_url.regex' => 'Đường dẫn tài liệu phải là đường dẫn nội bộ bắt đầu bằng /docs/...',
            'template_slug.exists' => 'Mã mẫu thiệp không tồn tại trong hệ thống.',
        ];
    }
}
