<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Listing;
use App\Services\KorterInitialStateParser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportKorterTbilisiListings extends Command
{
    protected $signature = 'korter:import-tbilisi
                            {--pages=1 : How many listing pages to fetch (Korter pagination)}
                            {--url=https://korter.ge/en/apartments-for-sale-tbilisi : Base URL (page query is appended)}';

    protected $description = 'Fetch apartment listings from korter.ge (Tbilisi, EN) and upsert into the local listings table.';

    private const USER_AGENT = 'Mozilla/5.0 (compatible; MyGeorgiaEstate/1.0; +https://example.com/bot)';

    public function handle(KorterInitialStateParser $parser): int
    {
        $pages = max(1, (int) $this->option('pages'));
        $baseUrl = rtrim((string) $this->option('url'), '?&');

        $imported = 0;
        $parserFailed = 0;

        for ($page = 1; $page <= $pages; $page++) {
            $url = $page === 1 ? $baseUrl : $baseUrl.(str_contains($baseUrl, '?') ? '&' : '?').'page='.$page;
            $this->info("Fetching {$url}");

            $response = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])
                ->timeout(60)
                ->get($url);

            if (! $response->successful()) {
                $this->error("HTTP {$response->status()} for {$url}");

                return self::FAILURE;
            }

            $state = $parser->parse($response->body());
            if ($state === null) {
                $this->warn("Could not parse window.INITIAL_STATE on page {$page}.");
                $parserFailed++;

                continue;
            }

            /** @var list<array<string, mixed>>|null $apartments */
            $apartments = data_get($state, 'apartmentListingStore.apartments');
            if (! is_array($apartments) || $apartments === []) {
                $this->warn("No apartments in INITIAL_STATE on page {$page}.");

                continue;
            }

            foreach ($apartments as $row) {
                $this->persistApartment($row);
                $imported++;
            }
        }

        $this->info("Upserted {$imported} listing rows from Korter.");
        if ($parserFailed > 0) {
            $this->warn("Skipped {$parserFailed} page(s) due to parse errors.");
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $a
     */
    private function persistApartment(array $a): void
    {
        $objectId = (int) ($a['objectId'] ?? 0);
        if ($objectId <= 0) {
            return;
        }

        $listing = Listing::query()->firstOrNew(['korter_object_id' => $objectId]);

        if (! $listing->exists) {
            $max = (int) Listing::query()->where('locale', 'en')->max('listing_index');
            $listing->listing_index = $max + 1;
        }

        $listing->locale = 'en';
        $listing->korter_object_id = $objectId;

        $code = 'KORTER-'.$objectId;
        if (strlen($code) > 20) {
            $code = substr($code, 0, 20);
        }
        $listing->code = $code;

        $currency = (string) ($a['currency'] ?? 'USD');
        $price = (float) ($a['price'] ?? 0);
        $area = (float) ($a['area'] ?? 0);

        $listing->price = $this->formatMoney($price, $currency);
        $listing->price_per_sqm = $area > 0
            ? $this->formatMoney(round($price / $area, 0), $currency).'/m²'
            : null;

        $img = data_get($a, 'mediaSrc.default.x2')
            ?? data_get($a, 'mediaSrc.default.x1')
            ?? '';
        $img = is_string($img) ? $img : '';
        $titleText = (string) data_get($a, 'microMarkupData.title', 'Apartment in Tbilisi');
        $alt = $titleText;

        if ($img === '') {
            $img = 'property-1.svg';
        }

        $listing->images = [
            ['file' => $img, 'alt' => $alt],
        ];
        $listing->image_alt = $alt;

        $address = (string) ($a['address'] ?? '');
        $district = (string) ($a['subLocalityNominative'] ?? '');
        $buildingName = (string) data_get($a, 'building.name', '');
        $typeLabel = (string) ($a['propertyTypeRoomCountLabel'] ?? 'Apartment');

        $listing->kicker = $code.' · '.$typeLabel.' · Tbilisi';
        $listing->title = $buildingName !== '' ? $buildingName.' · '.$typeLabel : $typeLabel.' · '.$district;
        $listing->address_line = $address;
        $listing->district = $district;
        $listing->latitude = data_get($a, 'building.position.lat');
        $listing->longitude = data_get($a, 'building.position.lng');
        $listing->developer = $buildingName !== '' ? $buildingName : null;
        $listing->built_year = null;
        $listing->description_by_developer = data_get($a, 'microMarkupData.description');

        $floorPart = $this->floorLabel($a);
        $chips = array_values(array_filter([
            $typeLabel,
            $area > 0 ? $this->formatArea($area) : null,
            $floorPart,
        ]));
        $listing->chips = $chips;

        $idx = (int) $listing->listing_index;
        $listing->modal_anchor = 'p'.$idx;
        if (strlen($listing->modal_anchor) > 10) {
            $listing->modal_anchor = 'p'.substr((string) $idx, -9);
        }
        $listing->modal_title = $titleText;

        $listing->address = trim($address.' · '.$district.'. Imported from Korter.ge (Tbilisi).');

        $rooms = (int) ($a['roomCount'] ?? 0);
        $roomStr = $rooms > 0 ? $rooms.' rooms' : $typeLabel;
        $listing->bullets = [
            ['label' => 'Area', 'text' => $area > 0 ? $this->formatArea($area).' · '.$roomStr : $roomStr],
            ['label' => 'Location', 'text' => $district !== '' ? $district.' · '.$address : $address],
            ['label' => 'Source', 'text' => 'Listing data from korter.ge at import time.'],
        ];

        $listing->tip = 'Source: <a href="https://korter.ge" rel="noopener noreferrer">Korter.ge</a> — use code <b>'.$code.'</b> when contacting us.';

        $listing->is_mock = false;

        $listing->save();
    }

    private function floorLabel(array $a): ?string
    {
        $floors = $a['floorNumbers'] ?? null;
        $house = $a['house'] ?? null;
        $total = is_array($house) ? ($house['floorCount'] ?? null) : null;
        if (! is_array($floors) || $floors === []) {
            return null;
        }
        $f = implode(', ', array_map('strval', $floors));
        if (is_numeric($total)) {
            return 'Floor '.$f.' of '.$total;
        }

        return 'Floor '.$f;
    }

    private function formatArea(float $area): string
    {
        $s = fmod($area, 1.0) === 0.0 ? (string) (int) $area : (string) $area;

        return $s.' m²';
    }

    private function formatMoney(float $amount, string $currency): string
    {
        $formatted = number_format($amount, fmod($amount, 1.0) === 0.0 ? 0 : 2, '.', ',');

        return match (strtoupper($currency)) {
            'USD' => '$'.$formatted,
            'GEL' => $formatted.' GEL',
            'EUR' => '€'.$formatted,
            default => $formatted.' '.$currency,
        };
    }
}
