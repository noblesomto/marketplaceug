<?php
namespace App\Helpers;

class ContentHelper
{
    public static function sanitizeContent($content)
    {
        if (empty($content)) {
            return '';
        }

        // Step 1: Remove banned patterns (contact info, etc.)
        $bannedPatterns = [
            '/\bphone\b/i',
            '/\bwhatsapp\b/i',
            '/\btelegram\b/i',
            '/\bviber\b/i',
            '/\bimo\b/i',
            '/\bcall\s+me\b/i',
            '/\bcontact\s+me\b/i',
            '/\bmessage\s+me\b/i',
            '/\btext\s+me\b/i',
            '/\bdm\s+me\b/i',
            '/\binbox\s+me\b/i',
            '/\b\d{10,15}\b/',  // Phone numbers
            '/\b\w+@\w+\.\w+\b/',  // Email addresses
            '/\b0\d{10}\b/',  // Nigerian phone numbers
            '/\b\+234\d{10}\b/',  // Nigerian international format
        ];

        foreach ($bannedPatterns as $pattern) {
            $content = preg_replace($pattern, '', $content);
        }

        // Step 2: Replace literal HTML entity strings FIRST (before decoding)
        $content = str_replace(
            ['&nbsp;', '&amp;', '&lt;', '&gt;', '&quot;', '&#39;', '&apos;', '&ndash;', '&mdash;', '&rsquo;', '&lsquo;', '&rdquo;', '&ldquo;'],
            [' ', '&', '<', '>', '"', "'", "'", '-', '-', "'", "'", '"', '"'],
            $content
        );

        // Step 3: Decode HTML entities multiple times (handles double/triple encoding)
        $iterations = 0;
        $previousContent = '';
        while ($content !== $previousContent && $iterations < 5) {
            $previousContent = $content;
            $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $iterations++;
        }

        // Step 4: Strip all HTML/XML tags
        $content = strip_tags($content);

        // Step 5: Replace common HTML tag strings that might be literal text
        $content = str_replace(
            ['<br>', '<br/>', '<br />', '</br>', '<p>', '</p>', '<div>', '</div>', '<span>', '</span>', '&lt;br&gt;', '&lt;/br&gt;'],
            [' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' '],
            $content
        );

        // Step 6: Remove any remaining HTML entity patterns (both &#xxx; and &name;)
        $content = preg_replace('/&[a-zA-Z0-9#]+;/', ' ', $content);

        // Step 7: Remove special characters and symbols (keep letters, numbers, basic punctuation)
        $content = preg_replace('/[^\p{L}\p{N}\s\-.,!?$€£¥₦&@()\'\"\/]/u', '', $content);

        // Step 8: Clean up spacing around punctuation
        $content = preg_replace('/\s*([.,!?;:])\s*/', '$1 ', $content);  // Add single space after punctuation
        $content = preg_replace('/\s+([.,!?;:])/', '$1', $content);  // Remove space before punctuation

        // Step 9: Replace multiple whitespace with single space
        $content = preg_replace('/\s+/', ' ', $content);

        // Step 10: Fix common spacing issues
        $content = preg_replace([
            '/\s*[,:;]\s*[,:;]\s*/',  // Multiple punctuation: ": ," becomes ","
            '/\s*[,:;]\s*$/',         // Trailing punctuation
            '/^\s*[,:;]\s*/',         // Leading punctuation
            '/([.,!?])\1+/',          // Repeated punctuation: "..." becomes "."
        ], [
            ', ',
            '',
            '',
            '$1'
        ], $content);

        // Step 11: Trim and clean up final result
        $content = trim($content);

        // Step 12: Remove empty parentheses and brackets
        $content = preg_replace('/\(\s*\)|\[\s*\]|\{\s*\}/', '', $content);

        // Step 13: Final trim
        return trim($content);
    }

    /**
     * Sanitize title (stricter rules, no line breaks allowed)
     */
    public static function sanitizeTitle($title)
    {
        $title = self::sanitizeContent($title);

        // Remove any remaining newlines or tabs from titles
        $title = preg_replace('/[\r\n\t]+/', ' ', $title);

        // Limit title length if needed (optional)
        $title = mb_substr($title, 0, 200);

        return trim($title);
    }

    /**
     * Sanitize description (allows more formatting)
     */
    public static function sanitizeDescription($description)
    {
        $description = self::sanitizeContent($description);

        // Preserve paragraph breaks (convert multiple newlines to double space)
        $description = preg_replace('/[\r\n]{2,}/', "\n\n", $description);

        return trim($description);
    }

    /**
     * Sanitize for meta tags (most strict)
     */
    public static function sanitizeForMeta($content, $maxLength = 160)
    {
        $content = self::sanitizeContent($content);

        // Remove all newlines for meta tags
        $content = preg_replace('/[\r\n\t]+/', ' ', $content);

        // Trim to max length
        if (mb_strlen($content) > $maxLength) {
            $content = mb_substr($content, 0, $maxLength);
            // Try to cut at last complete word
            $lastSpace = mb_strrpos($content, ' ');
            if ($lastSpace !== false && $lastSpace > $maxLength * 0.8) {
                $content = mb_substr($content, 0, $lastSpace);
            }
            $content .= '...';
        }

        return trim($content);
    }

    /**
     * Sanitize keywords
     */
    public static function sanitizeKeywords($keywords)
    {
        $keywords = self::sanitizeContent($keywords);

        // Convert to lowercase
        $keywords = mb_strtolower($keywords);

        // Remove extra commas
        $keywords = preg_replace('/,+/', ',', $keywords);
        $keywords = trim($keywords, ',');

        return $keywords;
    }
}
