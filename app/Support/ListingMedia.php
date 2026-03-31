<?php

declare(strict_types=1);

namespace App\Support;

final class ListingMedia
{
    public static function url(string $file): string
    {
        if (str_starts_with($file, 'http://') || str_starts_with($file, 'https://')) {
            return $file;
        }

        return asset('assets/'.$file);
    }
}
