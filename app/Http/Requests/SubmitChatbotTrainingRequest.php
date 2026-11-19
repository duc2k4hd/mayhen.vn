<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\AiKnowledgeEntry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitChatbotTrainingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'question' => ['required', 'string', 'min:5', 'max:190'],
            'suggested_answer' => ['required', 'string', 'min:10', 'max:1000'],
            'category' => ['nullable', 'string', Rule::in(array_keys(AiKnowledgeEntry::CATEGORIES))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'question.required' => 'Vui lòng nhập câu hỏi cần bổ sung.',
            'question.min' => 'Câu hỏi cần dài ít nhất 5 ký tự.',
            'suggested_answer.required' => 'Vui lòng nhập câu trả lời hoặc thông tin bạn muốn góp ý.',
            'suggested_answer.min' => 'Nội dung góp ý cần dài ít nhất 10 ký tự.',
        ];
    }
}
