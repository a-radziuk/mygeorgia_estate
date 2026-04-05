<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Reads extended listing attributes from Korter detail pages
 * (`window.INITIAL_STATE.layoutLandingStore.layout`).
 *
 * @see https://korter.ge/en/apartments-for-sale-tbilisi
 * @see https://korter.ge/en/houses-for-sale-tbilisi
 */
final class KorterListingLayoutExtractor
{
    public function __construct(
        private readonly KorterInitialStateParser $parser,
    ) {}

    /**
     * @return array<string, mixed> All keys present; use null when missing or invalid.
     */
    public function extractFromHtml(string $html): array
    {
        $state = $this->parser->parse($html);

        return $this->extractFromState(is_array($state) ? $state : null);
    }

    /**
     * @param  array<string, mixed>|null  $state
     * @return array{
     *   total_area_sqm: float|null,
     *   living_area_sqm: float|null,
     *   kitchen_area_sqm: float|null,
     *   land_parcel_area_sqm: float|null,
     *   terrace_area_sqm: float|null,
     *   bedroom_count: int|null,
     *   bathroom_count: int|null,
     *   room_count: int|null,
     *   built_year: int|null,
     *   ceiling_height_m: float|null,
     *   has_balcony: bool|null,
     *   has_terrace: bool|null,
     *   parking: string|null,
     *   floors_label: string|null,
     *   property_subtype: string|null,
     *   description: string|null,
     *   korter_listed_at: string|null,
     *   korter_updated_at: string|null,
     * }
     */
    public function extractFromState(?array $state): array
    {
        $defaults = [
            'total_area_sqm' => null,
            'living_area_sqm' => null,
            'kitchen_area_sqm' => null,
            'land_parcel_area_sqm' => null,
            'terrace_area_sqm' => null,
            'bedroom_count' => null,
            'bathroom_count' => null,
            'room_count' => null,
            'built_year' => null,
            'ceiling_height_m' => null,
            'has_balcony' => null,
            'has_terrace' => null,
            'parking' => null,
            'floors_label' => null,
            'property_subtype' => null,
            'description' => null,
            'korter_listed_at' => null,
            'korter_updated_at' => null,
        ];

        if ($state === null) {
            return $defaults;
        }

        /** @var mixed $layout */
        $layout = data_get($state, 'layoutLandingStore.layout');
        if (! is_array($layout)) {
            return $defaults;
        }

        $total = self::positiveFloatOrNull($layout['area'] ?? null)
            ?? self::positiveFloatOrNull($layout['totalArea'] ?? null);
        $living = self::positiveFloatOrNull($layout['livingArea'] ?? null)
            ?? self::positiveFloatOrNull($layout['totalUsableArea'] ?? null);
        $kitchen = self::positiveFloatOrNull($layout['kitchenArea'] ?? null);
        $land = self::positiveFloatOrNull($layout['landArea'] ?? null);
        $terrace = self::positiveFloatOrNull($layout['terraceArea'] ?? null);

        $propertyType = $layout['propertyType'] ?? null;
        $subtype = is_array($propertyType) ? self::nonEmptyString($propertyType['name'] ?? null) : null;

        $parkingRaw = $layout['parking'] ?? null;
        $parking = self::parkingToString($parkingRaw);

        $listedAt = self::parseIsoDatetime($layout['publishTime'] ?? null)
            ?? self::parseIsoDatetime($layout['createTime'] ?? null);
        $updatedAt = self::parseIsoDatetime($layout['actualizeTime'] ?? null);

        return [
            'total_area_sqm' => $total,
            'living_area_sqm' => $living,
            'kitchen_area_sqm' => $kitchen,
            'land_parcel_area_sqm' => $land,
            'terrace_area_sqm' => $terrace,
            'bedroom_count' => self::positiveIntOrNull($layout['bedroomCount'] ?? null),
            'bathroom_count' => self::positiveIntOrNull($layout['bathroomCount'] ?? null),
            'room_count' => self::positiveIntOrNull($layout['roomCount'] ?? null),
            'built_year' => self::yearOrNull($layout['builtYear'] ?? null),
            'ceiling_height_m' => self::positiveFloatOrNull($layout['ceilingHeight'] ?? null),
            'has_balcony' => self::boolOrNull($layout['hasBalcony'] ?? null),
            'has_terrace' => self::boolOrNull($layout['hasTerrace'] ?? null),
            'parking' => $parking,
            'floors_label' => self::floorsLabelFromLayout($layout),
            'property_subtype' => $subtype,
            'description' => self::nonEmptyString($layout['description'] ?? null),
            'korter_listed_at' => $listedAt,
            'korter_updated_at' => $updatedAt,
        ];
    }

    /**
     * Korter layout uses ISO-8601 strings (e.g. publishTime, actualizeTime).
     */
    private static function parseIsoDatetime(mixed $v): ?string
    {
        if (! is_string($v)) {
            return null;
        }
        $t = trim($v);
        if ($t === '') {
            return null;
        }
        try {
            return Carbon::parse($t)->format('Y-m-d H:i:s');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $layout
     */
    private static function floorsLabelFromLayout(array $layout): ?string
    {
        /** @var mixed $rows */
        $rows = $layout['floorsByHouse'] ?? null;
        if (! is_array($rows) || $rows === []) {
            return null;
        }

        $first = $rows[0];
        if (! is_array($first)) {
            return null;
        }

        $floors = $first['floorNumbers'] ?? null;
        $house = $layout['houses'][0] ?? null;
        $total = $first['floorCount'] ?? (is_array($house) ? ($house['floorCount'] ?? null) : null);

        if (! is_array($floors) || $floors === []) {
            if (is_numeric($total) && (int) $total > 0) {
                return 'Building '.$total.' floors';
            }

            return null;
        }

        if (count($floors) > 5 && is_numeric($total) && (int) $total > 0) {
            return (int) $total.'-floor building (multiple levels)';
        }

        $f = implode(', ', array_map('strval', $floors));
        if (is_numeric($total) && (int) $total > 0) {
            return 'Floor '.$f.' of '.$total;
        }

        return 'Floor '.$f;
    }

    private static function parkingToString(mixed $parking): ?string
    {
        if ($parking === null || $parking === '') {
            return null;
        }
        if (is_string($parking)) {
            return self::nonEmptyString($parking);
        }
        if (is_numeric($parking)) {
            return (string) $parking;
        }
        if (is_bool($parking)) {
            return $parking ? 'Yes' : null;
        }
        if (is_array($parking)) {
            $name = $parking['name'] ?? $parking['label'] ?? $parking['title'] ?? null;
            if (is_string($name) && $name !== '') {
                return $name;
            }

            $json = json_encode($parking, JSON_UNESCAPED_UNICODE);

            return self::nonEmptyString($json !== false ? Str::limit($json, 255, '…') : null);
        }

        return null;
    }

    private static function nonEmptyString(mixed $v): ?string
    {
        if (! is_string($v)) {
            return null;
        }
        $t = trim($v);

        return $t === '' ? null : $t;
    }

    private static function positiveFloatOrNull(mixed $v): ?float
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_numeric($v)) {
            $f = (float) $v;

            return $f > 0 ? $f : null;
        }

        return null;
    }

    private static function positiveIntOrNull(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }
        if (is_bool($v)) {
            return null;
        }
        if (is_numeric($v)) {
            $i = (int) $v;

            return $i > 0 ? $i : null;
        }

        return null;
    }

    private static function yearOrNull(mixed $v): ?int
    {
        $i = self::positiveIntOrNull($v);
        if ($i === null || $i < 1700 || $i > 2100) {
            return null;
        }

        return $i;
    }

    private static function boolOrNull(mixed $v): ?bool
    {
        if ($v === null) {
            return null;
        }
        if (is_bool($v)) {
            return $v;
        }
        if ($v === 1 || $v === '1' || $v === 'true') {
            return true;
        }
        if ($v === 0 || $v === '0' || $v === 'false') {
            return false;
        }

        return null;
    }
}
