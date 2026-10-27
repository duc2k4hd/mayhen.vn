<?php

namespace App\Http\Requests;

use App\Rules\NoProfanity;
use Illuminate\Foundation\Http\FormRequest;

class SubmitRsvpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'guest_name' => is_string($this->guest_name) ? strip_tags(trim($this->guest_name)) : $this->guest_name,
            'phone' => is_string($this->phone) ? strip_tags(trim($this->phone)) : $this->phone,
            'dietary_requirements' => is_string($this->dietary_requirements) ? strip_tags(trim($this->dietary_requirements)) : $this->dietary_requirements,
            'wishes' => is_string($this->wishes) ? strip_tags(trim($this->wishes)) : $this->wishes,
        ]);
    }

    public function rules(): array
    {
        return [
            'guest_name' => ['required', 'string', 'max:255', new NoProfanity],
            'phone' => ['nullable', 'string', 'max:30'],
            'attending_status' => ['required', 'in:attending,declined,uncertain'],
            'adults_count' => ['nullable', 'integer', 'min:0', 'max:10'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:10'],
            'dietary_requirements' => ['nullable', 'string', 'max:500', new NoProfanity],
            'wishes' => ['nullable', 'string', 'max:1000', new NoProfanity],
        ];
    }
}
