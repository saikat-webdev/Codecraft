<?php

namespace Database\Seeders\Support;

/**
 * Builds lesson HTML with properly escaped, multi-line code blocks.
 */
class LessonHtml
{
    public static function h2(string $text): string
    {
        return '<h2>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</h2>';
    }

    public static function h3(string $text): string
    {
        return '<h3>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</h3>';
    }

    public static function p(string $text): string
    {
        return '<p>' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</p>';
    }

    public static function ul(array $items): string
    {
        $lis = array_map(
            fn ($item) => '<li>' . htmlspecialchars($item, ENT_QUOTES, 'UTF-8') . '</li>',
            $items
        );

        return '<ul>' . implode('', $lis) . '</ul>';
    }

    public static function code(string $code): string
    {
        return '<pre><code>' . htmlspecialchars($code, ENT_NOQUOTES, 'UTF-8') . '</code></pre>';
    }

    public static function join(array $parts): string
    {
        return implode("\n", $parts);
    }
}
