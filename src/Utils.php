<?php

declare(strict_types=1);

namespace CoderSapient\JsonApi;

final class Utils
{
    public static function subStrFirst(string $string, string $needle): string
    {
        return $needle === $string[0] ? mb_substr($string, 1) : $string;
    }

    public static function explodeIfNotEmpty(string $string, $delimiter = ','): array
    {
        return '' !== $string ? explode($delimiter, $string) : [];
    }

    public static function explodeIfContains(string $string, $delimiter = ','): array|string
    {
        return str_contains($string, $delimiter) ? explode($delimiter, $string) : $string;
    }
}
