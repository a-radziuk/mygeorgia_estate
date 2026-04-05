<?php

declare(strict_types=1);

namespace App\Data;

use App\Models\Listing;

final class SiteRepository
{
    public const LISTINGS_PER_PAGE = 15;

    /** @return list<string> */
    public static function locales(): array
    {
        return ['en', 'ru', 'ja'];
    }

    public static function defaultLocale(): string
    {
        return 'en';
    }

    /** @return list<string> */
    public static function cities(): array
    {
        return ['tbilisi', 'batumi'];
    }

    public static function defaultCity(): string
    {
        return 'tbilisi';
    }

    /** @return list<string> */
    public static function listingTypes(): array
    {
        return ['apartment', 'house'];
    }

    /**
     * @return array<string, mixed>
     */
    public static function forLocale(string $locale, string $city): array
    {
        $locale = strtolower($locale);
        if (! in_array($locale, self::locales(), true)) {
            $locale = self::defaultLocale();
        }

        $city = strtolower($city);
        if (! in_array($city, self::cities(), true)) {
            $city = self::defaultCity();
        }

        $path = __DIR__.'/content/'.$locale.'.php';

        /** @var array<string, mixed> $data */
        $data = file_exists($path) ? require $path : require __DIR__.'/content/en.php';

        $data['listings'] = self::listingsKeyedByIndex($locale, $city);

        return $data;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private static function listingsKeyedByIndex(string $locale, string $city): array
    {
        return Listing::query()
            ->withListedPrice()
            ->where('locale', $locale)
            ->where('city', $city)
            ->orderBy('listing_index')
            ->get()
            ->keyBy('listing_index')
            ->map(fn (Listing $listing) => $listing->toSiteArray())
            ->all();
    }
}
