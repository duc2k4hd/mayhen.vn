<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AskChatbotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:2', 'max:600'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.required' => 'Vui lòng nhập câu hỏi của bạn.',
            'message.min' => 'Câu hỏi cần có ít nhất 2 ký tự.',
            'message.max' => 'Câu hỏi tối đa 600 ký tự để đảm bảo phản hồi nhanh nhất.',
        ];
    }
}
