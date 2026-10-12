<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitRsvpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_name' => 'required|string|max:255',
            'attending_status' => 'required|in:attending,declined,uncertain',
            'adults_count' => 'nullable|integer|min:0|max:10',
            'children_count' => 'nullable|integer|min:0|max:10',
            'dietary_requirements' => 'nullable|string|max:500',
            'wishes' => 'nullable|string|max:1000',
        ];
    }
}
