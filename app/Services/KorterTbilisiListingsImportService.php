<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Listing;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Http;

final class KorterTbilisiListingsImportService
{
    public const FIRST_INVALID_PRESET = 9;

    private const USER_AGENT = 'Mozilla/5.0 (compatible; MyGeorgiaEstate/1.0; +https://example.com/bot)';

    /**
     * @var array<int, array{type: string, city: string, city_name: string, market_type: string, url: string, max_page: positive-int}>
     */
    private const PRESETS = [
        1 => [
            'url' => 'https://korter.ge/en/apartments-for-sale-tbilisi?market_types=primary',
            'type' => 'apartment',
            'city' => 'tbilisi',
            'city_name' => 'Tbilisi',
            'market_type' => 'primary',
            'max_page' => 100,
        ],
        2 => [
            'url' => 'https://korter.ge/en/houses-for-sale-tbilisi?market_types=primary',
            'type' => 'house',
            'city' => 'tbilisi',
            'city_name' => 'Tbilisi',
            'market_type' => 'primary',
            'max_page' => 100,
        ],
        3 => [
            'url' => 'https://korter.ge/en/apartments-for-sale-batumi?market_types=primary',
            'type' => 'apartment',
            'city' => 'batumi',
            'city_name' => 'Batumi',
            'market_type' => 'primary',
            'max_page' => 100,
        ],
        4 => [
            'url' => 'https://korter.ge/en/houses-for-sale-batumi?market_types=primary',
            'type' => 'house',
            'city' => 'batumi',
            'city_name' => 'Batumi',
            'market_type' => 'primary',
            'max_page' => 100,
        ],
        5 => [
            'url' => 'https://korter.ge/en/apartments-for-sale-tbilisi?market_types=secondary',
            'type' => 'apartment',
            'city' => 'tbilisi',
            'city_name' => 'Tbilisi',
            'market_type' => 'secondary',
            'max_page' => 100,
        ],
        6 => [
            'url' => 'https://korter.ge/en/houses-for-sale-tbilisi?market_types=secondary',
            'type' => 'house',
            'city' => 'tbilisi',
            'city_name' => 'Tbilisi',
            'market_type' => 'secondary',
            'max_page' => 100,
        ],
        7 => [
            'url' => 'https://korter.ge/en/apartments-for-sale-batumi?market_types=secondary',
            'type' => 'apartment',
            'city' => 'batumi',
            'city_name' => 'Batumi',
            'market_type' => 'secondary',
            'max_page' => 100,
        ],
        8 => [
            'url' => 'https://korter.ge/en/houses-for-sale-batumi?market_types=secondary',
            'type' => 'house',
            'city' => 'batumi',
            'city_name' => 'Batumi',
            'market_type' => 'secondary',
            'max_page' => 100,
        ],
    ];

    public function __construct(
        private readonly KorterInitialStateParser $parser,
        private readonly KorterListingDetailImages $detailImages,
        private readonly KorterListingLayoutExtractor $layoutExtractor,
    ) {}

    public static function presetExists(int $preset): bool
    {
        return isset(self::PRESETS[$preset]);
    }

    /**
     * @return array{type: string, city: string, city_name: string, market_type: string, url: string, max_page?: positive-int}
     */
    public static function presetConfig(int $preset): array
    {
        if (! isset(self::PRESETS[$preset])) {
            throw new \InvalidArgumentException('Preset "'.$preset.'" is not defined.');
        }

        return self::PRESETS[$preset];
    }

    public function importListingPage(
        int $presetKey,
        int $page,
        bool $skipDetail,
        int $delayMs,
        ?OutputStyle $output = null,
    ): KorterPageImportResult {
        $preset = self::presetConfig($presetKey);
        $page = max(1, $page);
        $delayMs = max(0, $delayMs);

        $baseUrl = rtrim($preset['url'], '?&');
        $maxPage = (int) ($preset['max_page'] ?? 0);
        if ($maxPage > 0 && $page > $maxPage) {
            $output?->writeln("Page {$page} is greater than max_page ({$maxPage}) for preset {$presetKey}; skipping fetch and treating preset as complete.");

            return new KorterPageImportResult(
                httpOk: true,
                parseOk: true,
                hadApartments: false,
                importedCount: 0,
                url: $baseUrl.' (skipped: page > max_page)',
            );
        }

        $url = $page === 1 ? $baseUrl : $baseUrl.(str_contains($baseUrl, '?') ? '&' : '?').'page='.$page;

        $output?->writeln("<info>Fetching {$url}</info>");

        $response = Http::withHeaders([
            'User-Agent' => self::USER_AGENT,
            'Accept' => 'text/html,application/xhtml+xml',
            'Accept-Language' => 'en-US,en;q=0.9',
        ])
            ->timeout(60)
            ->get($url);

        if (! $response->successful()) {
            $output?->error("HTTP {$response->status()} for {$url}");

            return new KorterPageImportResult(
                httpOk: false,
                parseOk: false,
                hadApartments: false,
                importedCount: 0,
                url: $url,
            );
        }

        $state = $this->parser->parse($response->body());
        if ($state === null) {
            $output?->warn("Could not parse window.INITIAL_STATE on page {$page}.");

            return new KorterPageImportResult(
                httpOk: true,
                parseOk: false,
                hadApartments: false,
                importedCount: 0,
                url: $url,
            );
        }

        /** @var list<array<string, mixed>>|null $apartments */
        $apartments = data_get($state, 'apartmentListingStore.apartments');
        if (! is_array($apartments) || $apartments === []) {
            $output?->warn("No apartments in INITIAL_STATE on page {$page}.");

            return new KorterPageImportResult(
                httpOk: true,
                parseOk: true,
                hadApartments: false,
                importedCount: 0,
                url: $url,
            );
        }

        $imported = 0;
        foreach ($apartments as $row) {
            $row['city'] = trim($preset['city']);
            $row['type'] = trim($preset['type']);
            $row['city_name'] = trim($preset['city_name']);
            $row['market_type'] = trim($preset['market_type']);
            if ($this->persistApartment($row, $skipDetail, $delayMs, $output)) {
                $imported++;
            }
        }

        $output?->writeln("<info>Upserted {$imported} listing rows from Korter.</info>");

        return new KorterPageImportResult(
            httpOk: true,
            parseOk: true,
            hadApartments: true,
            importedCount: $imported,
            url: $url,
        );
    }

    /**
     * @param  array<string, mixed>  $a
     */
    private function persistApartment(
        array $a,
        bool $skipDetail,
        int $delayMs,
        ?OutputStyle $output,
    ): bool {
        $objectId = (int) ($a['objectId'] ?? 0);
        $layoutId = (int) ($a['layoutId'] ?? 0);
        if ($objectId <= 0 && $layoutId <= 0) {
            return false;
        }

        if ($objectId > 0) {
            $listing = Listing::query()->firstOrNew(['korter_object_id' => $objectId]);
            $listing->korter_layout_id = null;
        } else {
            $listing = Listing::query()->firstOrNew(['korter_layout_id' => $layoutId]);
            $listing->korter_object_id = null;
        }

        if (! $listing->exists) {
            $max = (int) Listing::query()->where('locale', 'en')->max('listing_index');
            $listing->listing_index = $max + 1;
        }

        $listing->locale = 'en';
        if ($objectId > 0) {
            $listing->korter_object_id = $objectId;
        } else {
            $listing->korter_layout_id = $layoutId;
        }

        $code = $objectId > 0 ? 'MG-'.$objectId : 'MG-L'.$layoutId;
        if (strlen($code) > 20) {
            $code = substr($code, 0, 20);
        }
        $listing->code = $code;

        $currency = (string) ($a['currency'] ?? 'USD');
        $price = (float) ($a['price'] ?? 0);
        $area = (float) ($a['area'] ?? 0);

        $listing->price = $this->formatMoney($price, $currency);
        $listing->price_amount = $price > 0 ? $price : null;
        $listing->price_currency = strtoupper(substr($currency, 0, 3));
        $listing->price_per_sqm = $area > 0
            ? $this->formatMoney(round($price / $area, 0), $currency).'/m²'
            : null;
        $listing->price_per_sqm_amount = $area > 0 && $price > 0 ? round($price / $area, 2) : null;

        $cover = data_get($a, 'mediaSrc.default.x2')
            ?? data_get($a, 'mediaSrc.default.x1')
            ?? '';
        $cover = is_string($cover) ? KorterListingDetailImages::normalizeUrl($cover) : '';

        $gallery = [];
        $layoutDetail = $this->layoutExtractor->extractFromState(null);
        $detailState = null;
        $link = (string) ($a['link'] ?? '');
        if (! $skipDetail && $link !== '') {
            $detailUrl = KorterListingDetailImages::normalizeUrl($link);
            if ($output?->isVerbose()) {
                $output->writeln("  Detail: {$detailUrl}");
            }
            $detailResponse = Http::withHeaders([
                'User-Agent' => self::USER_AGENT,
                'Accept' => 'text/html,application/xhtml+xml',
                'Accept-Language' => 'en-US,en;q=0.9',
            ])
                ->timeout(90)
                ->get($detailUrl);

            if ($detailResponse->successful()) {
                $parsed = $this->parser->parse($detailResponse->body());
                if (is_array($parsed)) {
                    $detailState = $parsed;
                    $layoutDetail = $this->layoutExtractor->extractFromState($detailState);
                }
            }
            if ($delayMs > 0) {
                usleep($delayMs * 1000);
            }
        }

        $layoutDetail = $this->mergeLayoutListFallbacks($layoutDetail, $a, $area);
        $headline = $this->formatListingHeadline($a, $layoutDetail);

        if (! $skipDetail && $link !== '' && is_array($detailState)) {
            $gallery = $this->detailImages->extractFromState($detailState, $headline);
        }

        if ($gallery === []) {
            $img = $cover !== '' ? $cover : 'property-1.svg';
            $listing->images = [
                ['file' => $img, 'alt' => $headline],
            ];
            $listing->image_alt = $headline;
        } else {
            $listing->images = $gallery;
            $listing->image_alt = $gallery[0]['alt'];
        }

        $address = (string) ($a['address'] ?? '');
        $district = (string) ($a['subLocalityNominative'] ?? '');
        $buildingName = (string) data_get($a, 'building.name', '');

        $listing->kicker = $code.' · '.$headline;
        $listing->title = $headline;
        $listing->address_line = $address;
        $listing->district = $district;
        $listing->latitude = data_get($a, 'building.position.lat');
        $listing->longitude = data_get($a, 'building.position.lng');
        $listing->developer = $buildingName !== '' ? $buildingName : null;
        $listing->market_type = $a['market_type'];
        $this->applyKorterLayoutFields($listing, $layoutDetail);
        $listing->description_by_developer = data_get($a, 'microMarkupData.description');
        if (($layoutDetail['description'] ?? null) !== null) {
            $listing->description_by_developer = $layoutDetail['description'];
        }

        $chipLead = str_contains($headline, ',')
            ? trim(explode(',', $headline, 2)[0])
            : $headline;
        $floorPart = $layoutDetail['floors_label'] ?? $this->floorLabel($a);
        $chips = array_values(array_filter([
            $chipLead,
            $area > 0 ? $this->formatArea($area) : null,
            $floorPart,
        ]));
        $listing->chips = $chips;

        $idx = (int) $listing->listing_index;
        $listing->modal_anchor = 'p'.$idx;
        if (strlen($listing->modal_anchor) > 10) {
            $listing->modal_anchor = 'p'.substr((string) $idx, -9);
        }
        $listing->modal_title = $headline;

        $listing->address = trim($address.' · '.$district.'');

        $rooms = (int) ($layoutDetail['room_count'] ?? $a['roomCount'] ?? 0);
        $roomStr = $rooms > 0 ? $rooms.' rooms' : 'Studio';
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

        return true;
    }

    /**
     * @param  array<string, mixed>  $layoutDetail
     * @param  array<string, mixed>  $a
     * @return array<string, mixed>
     */
    private function mergeLayoutListFallbacks(array $layoutDetail, array $a, float $listArea): array
    {
        if (($layoutDetail['total_area_sqm'] ?? null) === null && $listArea > 0) {
            $layoutDetail['total_area_sqm'] = $listArea;
        }
        $rc = (int) ($a['roomCount'] ?? 0);
        if (($layoutDetail['room_count'] ?? null) === null && $rc > 0) {
            $layoutDetail['room_count'] = $rc;
        }

        return $layoutDetail;
    }

    /**
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $layoutDetail
     */
    private function formatListingHeadline(array $a, array $layoutDetail): string
    {
        $rooms = (int) ($layoutDetail['room_count'] ?? $a['roomCount'] ?? 0);
        $kind = (string) ($a['type'] ?? 'apartment') === 'house' ? 'house' : 'apartment';

        $district = trim((string) ($a['subLocalityNominative'] ?? ''));
        if ($district === '') {
            $district = trim((string) ($a['city_name'] ?? ''));
        }

        if ($rooms > 0) {
            $headline = $rooms.'-room '.$kind;
        } else {
            $headline = 'Studio '.$kind;
        }

        if ($district !== '') {
            return $headline.', '.$district;
        }

        return $headline;
    }

    /**
     * @param  array<string, mixed>  $layoutDetail
     */
    private function applyKorterLayoutFields(Listing $listing, array $layoutDetail): void
    {
        $listing->built_year = $layoutDetail['built_year'];
        $listing->total_area_sqm = $layoutDetail['total_area_sqm'];
        $listing->living_area_sqm = $layoutDetail['living_area_sqm'];
        $listing->kitchen_area_sqm = $layoutDetail['kitchen_area_sqm'];
        $listing->land_parcel_area_sqm = $layoutDetail['land_parcel_area_sqm'];
        $listing->terrace_area_sqm = $layoutDetail['terrace_area_sqm'];
        $listing->bedroom_count = $layoutDetail['bedroom_count'];
        $listing->bathroom_count = $layoutDetail['bathroom_count'];
        $listing->room_count = $layoutDetail['room_count'];
        $listing->ceiling_height_m = $layoutDetail['ceiling_height_m'];
        $listing->has_balcony = $layoutDetail['has_balcony'];
        $listing->has_terrace = $layoutDetail['has_terrace'];
        $listing->parking = $layoutDetail['parking'];
        $listing->floors_label = $layoutDetail['floors_label'];
        $listing->property_subtype = $layoutDetail['property_subtype'];
    }

    private function floorLabel(array $a): ?string
    {
        $floors = $a['floorNumbers'] ?? null;
        $house = $a['house'] ?? null;
        $total = is_array($house) ? ($house['floorCount'] ?? null) : null;
        if (! is_array($floors) || $floors === []) {
            return null;
        }
        if (count($floors) > 5 && is_numeric($total)) {
            return (int) $total.'-floor building';
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
