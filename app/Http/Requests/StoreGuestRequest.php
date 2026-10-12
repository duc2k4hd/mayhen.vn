<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'salutation' => 'nullable|string|max:100',
            'group_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'expected_attendees' => 'nullable|integer|min:1|max:20',
        ];
    }
}
