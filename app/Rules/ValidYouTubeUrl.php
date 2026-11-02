<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Services\YouTubeService;

class ValidYouTubeUrl implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || empty(trim($value))) {
            $fail('Sila masukkan pautan YouTube yang sah.');
            return;
        }

        $videoId = YouTubeService::extractVideoId($value);

        if (!$videoId || !YouTubeService::isValidId($videoId)) {
            $fail('Pautan YouTube tidak sah. Sila pastikan format URL YouTube (standard, youtu.be, atau Shorts) mengandungi 11-aksara ID video yang tepat.');
        }
    }
}
