<?php

declare(strict_types=1);

namespace App\Data;

final class SiteRepository
{
    /** @return list<string> */
    public static function locales(): array
    {
        return ['en', 'ru', 'ja'];
    }

    public static function defaultLocale(): string
    {
        return 'en';
    }

    /**
     * @return array<string, mixed>
     */
    public static function forLocale(string $locale): array
    {
        $locale = strtolower($locale);
        if (! in_array($locale, self::locales(), true)) {
            $locale = self::defaultLocale();
        }

        $path = __DIR__.'/content/'.$locale.'.php';

        return file_exists($path) ? require $path : require __DIR__.'/content/en.php';
    }
}
