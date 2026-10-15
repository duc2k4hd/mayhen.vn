<?php

namespace App\Rules;

use App\Services\ContentModerationService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class NoProfanity implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        if (ContentModerationService::containsProfanity($value)) {
            $fail('Nội dung chứa từ ngữ không phù hợp hoặc thiếu văn minh. Vui lòng sử dụng ngôn từ lịch sự, trang trọng để gửi lời chúc phúc.');
        }
    }
}
