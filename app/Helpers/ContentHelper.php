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

    public static function sanitizeName($name)
    {
        if (empty($name)) {
            return '';
        }

        // Step 1: Remove emojis (all emoji Unicode ranges)
        $name = preg_replace('/[\x{1F600}-\x{1F64F}]/u', '', $name); // Emoticons
        $name = preg_replace('/[\x{1F300}-\x{1F5FF}]/u', '', $name); // Misc Symbols and Pictographs
        $name = preg_replace('/[\x{1F680}-\x{1F6FF}]/u', '', $name); // Transport and Map
        $name = preg_replace('/[\x{1F1E0}-\x{1F1FF}]/u', '', $name); // Flags
        $name = preg_replace('/[\x{2600}-\x{26FF}]/u', '', $name);   // Misc symbols
        $name = preg_replace('/[\x{2700}-\x{27BF}]/u', '', $name);   // Dingbats
        $name = preg_replace('/[\x{1F900}-\x{1F9FF}]/u', '', $name); // Supplemental Symbols and Pictographs
        $name = preg_replace('/[\x{1FA00}-\x{1FA6F}]/u', '', $name); // Chess Symbols
        $name = preg_replace('/[\x{1FA70}-\x{1FAFF}]/u', '', $name); // Symbols and Pictographs Extended-A
        $name = preg_replace('/[\x{FE00}-\x{FE0F}]/u', '', $name);   // Variation Selectors
        $name = preg_replace('/[\x{1F000}-\x{1F02F}]/u', '', $name); // Mahjong Tiles
        $name = preg_replace('/[\x{1F0A0}-\x{1F0FF}]/u', '', $name); // Playing Cards

        // Step 2: Check for banned names/words (case-insensitive)
        $bannedNames = [
            'market',
            'marketplace',
            'marketplace naija',
            'marketplace ng',
            'admin',
            'administrator',
            'moderator',
            'support',
            'system',
            'official',
        ];

        foreach ($bannedNames as $banned) {
            // Replace whole words or the entire string
            $name = preg_replace('/\b' . preg_quote($banned, '/') . '\b/i', '', $name);
        }

        // Step 3: Apply general content sanitization
        $name = self::sanitizeContent($name);

        // Step 4: Remove any remaining special characters that shouldn't be in names
        $name = preg_replace('/[^\p{L}\p{N}\s\-\'\.]/u', '', $name);

        // Step 5: Clean up extra spaces
        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name);

        // Step 6: Limit name length
        $name = mb_substr($name, 0, 100);

        return $name;
    }
}
