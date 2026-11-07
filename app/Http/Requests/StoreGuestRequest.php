<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => is_string($this->name) ? strip_tags(trim($this->name)) : $this->name,
            'salutation' => is_string($this->salutation) ? strip_tags(trim($this->salutation)) : $this->salutation,
            'group_name' => is_string($this->group_name) ? strip_tags(trim($this->group_name)) : $this->group_name,
            'table_name' => is_string($this->table_name) ? strip_tags(trim($this->table_name)) : $this->table_name,
            'phone' => is_string($this->phone) ? strip_tags(trim($this->phone)) : $this->phone,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'salutation' => 'nullable|string|max:100',
            'group_name' => 'nullable|string|max:100',
            'table_name' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'expected_attendees' => 'nullable|integer|min:1|max:20',
        ];
    }
}
