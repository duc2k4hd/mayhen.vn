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
        $rawStatus = is_string($this->attending_status) ? strtolower(trim($this->attending_status)) : $this->attending_status;
        $statusMap = [
            'yes' => 'attending',
            'confirm' => 'attending',
            'confirmed' => 'attending',
            'no' => 'declined',
            'decline' => 'declined',
            'not_attending' => 'declined',
            'maybe' => 'uncertain',
            'pending' => 'uncertain',
            'unsure' => 'uncertain',
        ];
        $normalizedStatus = is_string($rawStatus) && isset($statusMap[$rawStatus])
            ? $statusMap[$rawStatus]
            : $rawStatus;

        $this->merge([
            'guest_name' => is_string($this->guest_name) ? strip_tags(trim($this->guest_name)) : $this->guest_name,
            'phone' => is_string($this->phone) ? strip_tags(trim($this->phone)) : $this->phone,
            'attending_status' => $normalizedStatus,
            'shuttle_bus_needed' => filter_var($this->shuttle_bus_needed, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            'shuttle_pickup_point' => is_string($this->shuttle_pickup_point) ? strip_tags(trim($this->shuttle_pickup_point)) : $this->shuttle_pickup_point,
            'menu_preference' => is_string($this->menu_preference) ? strip_tags(trim($this->menu_preference)) : $this->menu_preference,
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
            'adults_count' => ['nullable', 'integer', 'min:0', 'max:50'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:50'],
            'shuttle_bus_needed' => ['nullable', 'boolean'],
            'shuttle_pickup_point' => ['nullable', 'string', 'max:255', new NoProfanity],
            'shuttle_passengers' => ['nullable', 'integer', 'min:0', 'max:50'],
            'menu_preference' => ['nullable', 'string', 'max:100', new NoProfanity],
            'dietary_requirements' => ['nullable', 'string', 'max:500', new NoProfanity],
            'wishes' => ['nullable', 'string', 'max:1000', new NoProfanity],
            'event_id' => ['nullable', 'integer'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('attending_status') === 'attending') {
                $hasExplicitCounts = $this->has('adults_count') || $this->has('children_count');
                if ($hasExplicitCounts) {
                    $adults = (int) ($this->input('adults_count') ?? 0);
                    $children = (int) ($this->input('children_count') ?? 0);
                    if ($adults + $children < 1) {
                        $validator->errors()->add('adults_count', 'Khi xác nhận tham dự, tổng số người lớn và trẻ em phải từ 1 người trở lên.');
                    }
                }
            }
        });
    }
}
