<?php

namespace App\Helpers;

class ContentHelper
{
    public static function sanitizeContent($content)
    {
        $bannedPatterns = [
            '/\bphone\b/i',
            '/\bwhatsapp\b/i',
            '/\btelegram\b/i',
            '/\bviber\b/i',
            '/\bcall\s+me\b/i',
            '/\bcontact\s+me\b/i',
            '/\bmessage\s+me\b/i',
            '/\b\d{10,15}\b/',
            '/\b\w+@\w+\.\w+\b/',
        ];

        // Remove banned content
        foreach ($bannedPatterns as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }

        // Comprehensive cleanup
        $content = preg_replace([
            '/\s*[,:;]\s*[,:;]\s*/',  // Multiple punctuation: ": ," becomes ","
            '/\s*[,:;]\s*$/',         // Trailing punctuation
            '/^\s*[,:;]\s*/',         // Leading punctuation
            '/\s+/',                  // Multiple spaces
            '/^\s+|\s+$/'             // Leading/trailing spaces
        ], [
            ', ',
            '',
            '',
            ' ',
            ''
        ], $content);

        return $content;
    }
}
