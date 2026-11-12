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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? strip_tags(trim($this->name)) : $this->name,
            'message' => is_string($this->message) ? strip_tags(trim($this->message)) : $this->message,
            'is_private' => filter_var($this->is_private, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', new NoProfanity],
            'message' => ['required', 'string', 'max:1000', new NoProfanity],
            'is_private' => ['nullable', 'boolean'],
            'signature_data' => ['nullable', 'string', 'max:500000'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'photo_data' => ['nullable', 'string', 'max:5000000'],
            'guest_token' => ['nullable', 'string', 'max:64'],
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
