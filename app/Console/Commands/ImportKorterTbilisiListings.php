<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Listing;
use App\Services\KorterInitialStateParser;
use App\Services\KorterListingDetailImages;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ImportKorterTbilisiListings extends Command
{
    protected $signature = 'korter:import-tbilisi
                            {--pages=1 : How many listing pages to fetch (Korter pagination)}
                            {--preset=1: Aparatments in Tblisi}
                            {--skip-detail-images : Only use cover image from the list (no per-listing detail requests)}
                            {--delay-ms=150 : Pause between detail page requests (0 to disable)}';

    protected $description = 'Fetch apartment listings from korter.ge (Tbilisi, EN) and upsert into the local listings table.';

    private const USER_AGENT = 'Mozilla/5.0 (compatible; MyGeorgiaEstate/1.0; +https://example.com/bot)';

    private array $presets = [
        1 => [
            'url' => 'https://korter.ge/en/apartments-for-sale-tbilisi',
            'type' => 'apartment',
            'city' => 'tbilisi',
            'city_name' => 'Tbilisi',
        ],
        2 => [
            'url' => 'https://korter.ge/en/houses-for-sale-tbilisi',
            'type' => 'house',
            'city' => 'tbilisi',
            'city_name' => 'Tbilisi',
        ],
        3 => [
            'url' => 'https://korter.ge/en/apartments-for-sale-batumi',
            'type' => 'apartment',
            'city' => 'batumi',
            'city_name' => 'Batumi',
        ],
        4 => [
            'url' => 'https://korter.ge/en/houses-for-sale-batumi',
            'type' => 'house',
            'city' => 'batumi',
            'city_name' => 'Batumi',
        ]
    ];

    public function handle(KorterInitialStateParser $parser, KorterListingDetailImages $detailImages): int
    {
        $pr = (int) $this->option('preset');
        if (!isset($this->presets[$pr])) {
            throw new \Exception('Preset "' . $pr . '" not found');
        }
        $preset = $this->presets[$pr];
        $pages = max(1, (int) $this->option('pages'));
        $baseUrl = rtrim($preset['url'], '?&');
        $skipDetail = (bool) $this->option('skip-detail-images');
        $delayMs = max(0, (int) $this->option('delay-ms'));

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
                $row['city'] = trim($preset['city']);
                $row['type'] = trim($preset['type']);
                $row['city_name'] = trim($preset['city_name']);
                $this->persistApartment($row, $detailImages, $skipDetail, $delayMs);
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
    private function persistApartment(array $a, KorterListingDetailImages $detailImages, bool $skipDetail, int $delayMs): void
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

        // $code = 'KORTER-'.$objectId;
        $code = 'MG-'.$objectId;
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

        $cover = data_get($a, 'mediaSrc.default.x2')
            ?? data_get($a, 'mediaSrc.default.x1')
            ?? '';
        $cover = is_string($cover) ? KorterListingDetailImages::normalizeUrl($cover) : '';
        $titleText = (string) data_get($a, 'microMarkupData.title', 'Apartment in Tbilisi');
        $alt = $titleText;

        $gallery = [];
        $link = (string) ($a['link'] ?? '');
        if (! $skipDetail && $link !== '') {
            $detailUrl = KorterListingDetailImages::normalizeUrl($link);
            if ($this->output->isVerbose()) {
                $this->line("  Detail: {$detailUrl}");
            }
            $detailResponse = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])
                ->timeout(90)
                ->get($detailUrl);

            if ($detailResponse->successful()) {
                $gallery = $detailImages->extractFromHtml($detailResponse->body(), $titleText);
            }
            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        if ($gallery === []) {
            $img = $cover !== '' ? $cover : 'property-1.svg';
            $listing->images = [
                ['file' => $img, 'alt' => $alt],
            ];
            $listing->image_alt = $alt;
        } else {
            $listing->images = $gallery;
            $listing->image_alt = $gallery[0]['alt'];
        }

        $address = (string) ($a['address'] ?? '');
        $district = (string) ($a['subLocalityNominative'] ?? '');
        $buildingName = (string) data_get($a, 'building.name', '');
        $typeLabel = (string) ($a['propertyTypeRoomCountLabel'] ?? 'Apartment');

        $listing->kicker = $code.' · '.$typeLabel.' · ' . $a['city_name'];
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

        $listing->address = trim($address.' · '.$district.'');

        $rooms = (int) ($a['roomCount'] ?? 0);
        $roomStr = $rooms > 0 ? $rooms.' rooms' : $typeLabel;
        $listing->bullets = [
            ['label' => 'Area', 'text' => $area > 0 ? $this->formatArea($area).' · '.$roomStr : $roomStr],
            ['label' => 'Location', 'text' => $district !== '' ? $district.' · '.$address : $address],
            ['label' => 'Source', 'text' => 'Listing data at '.date('d.m.Y')],
        ];

        $listing->tip = 'Use code <b>'.$code.'</b> when contacting us.';

        $listing->city = (string) ($a['city'] ?? '');
        $listing->type = (string) ($a['type'] ?? '');

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
