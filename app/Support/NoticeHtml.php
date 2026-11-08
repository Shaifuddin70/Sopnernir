<?php

namespace App\Support;

final class NoticeHtml
{
    public static function sanitize(string $html): string
    {
        $allowed = '<p><br><strong><b><em><i><u><s><ul><ol><li><a><h1><h2><h3><h4><blockquote><pre><code><span>';
        $clean = strip_tags($html, $allowed);
        $clean = preg_replace_callback(
            '/<a\s+([^>]*?)>/i',
            static function (array $matches): string {
                $attrs = $matches[1];
                $href = null;
                if (preg_match('/href\s*=\s*("|\')(.*?)\1/i', $attrs, $hrefMatch)) {
                    $candidate = trim(html_entity_decode($hrefMatch[2]));
                    if (preg_match('/^(https?:\/\/|mailto:)/i', $candidate)) {
                        $href = htmlspecialchars($candidate, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    }
                }

                return $href
                    ? '<a href="'.$href.'" target="_blank" rel="noopener noreferrer">'
                    : '<a>';
            },
            $clean,
        ) ?? $clean;

        return trim($clean);
    }
}
