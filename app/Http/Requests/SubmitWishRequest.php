<?php

namespace App\Http\Requests;

use App\Rules\NoProfanity;
use Illuminate\Foundation\Http\FormRequest;

class SubmitWishRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', new NoProfanity],
            'message' => ['required', 'string', 'max:1000', new NoProfanity],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Vui lòng nhập tên của bạn.',
            'name.max' => 'Tên không được vượt quá 100 ký tự.',
            'message.required' => 'Vui lòng nhập lời chúc phúc.',
            'message.max' => 'Lời chúc không được vượt quá 1000 ký tự.',
        ];
    }
}
