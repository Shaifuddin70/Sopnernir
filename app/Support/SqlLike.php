<?php

namespace App\Support;

final class SqlLike
{
    /**
     * Wrap a trimmed search string for SQL LIKE, escaping %, _, and \.
     */
    public static function term(?string $raw): ?string
    {
        $s = trim((string) $raw);
        if ($s === '') {
            return null;
        }

        return '%'.addcslashes($s, '%_\\').'%';
    }
}
