<?php

namespace App\Support;

use Illuminate\Http\Request;

final class PaginationPerPage
{
    /** @var list<int> */
    public const ALLOWED = [5, 10, 15, 20, 25, 50, 100];

    public static function resolve(Request $request, string $queryKey, int $default): int
    {
        $raw = $request->query($queryKey);
        if ($raw === null || $raw === '') {
            return $default;
        }

        $v = (int) $raw;

        return in_array($v, self::ALLOWED, true) ? $v : $default;
    }
}
