<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\AiChatTestScenario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminAiChatTestScenarioRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:2', 'max:160'],
            'category' => ['required', 'string', Rule::in(array_keys(AiChatTestScenario::CATEGORIES))],
            'prompt' => ['required', 'string', 'min:2', 'max:700'],
            'expected_mode' => ['required', 'string', Rule::in(array_keys(AiChatTestScenario::EXPECTED_MODES))],
            'expected_cards' => ['required', 'string', Rule::in(array_keys(AiChatTestScenario::EXPECTED_CARDS))],
            'must_contain' => ['nullable', 'string', 'max:400'],
            'must_not_contain' => ['nullable', 'string', 'max:400'],
        ];
    }
}
