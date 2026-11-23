<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAiChatSimulatorRunRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && $this->user()->isAdmin();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'min:1', 'max:700'],
            'sandbox_session' => ['nullable', 'string', 'max:80'],
            'scenario_id' => ['nullable', 'integer', 'exists:ai_chat_test_scenarios,id'],
            'expectations' => ['nullable', 'array'],
            'expectations.expected_mode' => ['nullable', 'string', 'in:any_answer,llm_reviewed,out_of_scope,blocked_security'],
            'expectations.expected_cards' => ['nullable', 'string', 'in:any,none,templates_only,docs_only,both'],
            'expectations.must_contain' => ['nullable', 'string', 'max:400'],
            'expectations.must_not_contain' => ['nullable', 'string', 'max:400'],
        ];
    }
}
