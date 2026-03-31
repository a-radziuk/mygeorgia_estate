<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Listing;

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

        /** @var array<string, mixed> $data */
        $data = file_exists($path) ? require $path : require __DIR__.'/content/en.php';

        $data['listings'] = self::listingsKeyedByIndex($locale);

        return $data;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function listingsKeyedByIndex(string $locale): array
    {
        return Listing::query()
            ->where('locale', $locale)
            ->orderBy('listing_index')
            ->get()
            ->keyBy('listing_index')
            ->map(fn (Listing $listing) => $listing->toSiteArray())
            ->all();
    }
}
