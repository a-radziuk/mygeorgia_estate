<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Listing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class PropertyListingFilters
{
    public function __construct(
        public readonly ?string $marketType,
        public readonly ?float $priceMin,
        public readonly ?float $priceMax,
        public readonly ?float $pricePerSqmMin,
        public readonly ?float $pricePerSqmMax,
        public readonly ?int $roomsMin,
        public readonly ?int $roomsMax,
        public readonly ?float $areaMin,
        public readonly ?float $areaMax,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $marketType = $request->query('market_type');
        if (! is_string($marketType) || $marketType === '' || ! in_array($marketType, ['primary', 'secondary'], true)) {
            $marketType = null;
        }

        $priceMin = self::optionalNonNegativeFloat($request->query('price_min'));
        $priceMax = self::optionalNonNegativeFloat($request->query('price_max'));
        $psqmMin = self::optionalNonNegativeFloat($request->query('price_sqm_min'));
        $psqmMax = self::optionalNonNegativeFloat($request->query('price_sqm_max'));
        $roomsMin = self::optionalPositiveInt($request->query('rooms_min'));
        $roomsMax = self::optionalPositiveInt($request->query('rooms_max'));
        $areaMin = self::optionalNonNegativeFloat($request->query('area_min'));
        $areaMax = self::optionalNonNegativeFloat($request->query('area_max'));

        if ($roomsMin !== null && $roomsMax !== null && $roomsMin > $roomsMax) {
            [$roomsMin, $roomsMax] = [$roomsMax, $roomsMin];
        }
        if ($priceMin !== null && $priceMax !== null && $priceMin > $priceMax) {
            [$priceMin, $priceMax] = [$priceMax, $priceMin];
        }
        if ($psqmMin !== null && $psqmMax !== null && $psqmMin > $psqmMax) {
            [$psqmMin, $psqmMax] = [$psqmMax, $psqmMin];
        }
        if ($areaMin !== null && $areaMax !== null && $areaMin > $areaMax) {
            [$areaMin, $areaMax] = [$areaMax, $areaMin];
        }

        return new self(
            $marketType,
            $priceMin,
            $priceMax,
            $psqmMin,
            $psqmMax,
            $roomsMin,
            $roomsMax,
            $areaMin,
            $areaMax,
        );
    }

    /**
     * @param  Builder<Listing>  $query
     */
    public function applyTo(Builder $query): void
    {
        if ($this->marketType !== null) {
            $query->where('market_type', $this->marketType);
        }

        if ($this->priceMin !== null || $this->priceMax !== null) {
            $query->whereNotNull('price_amount');
        }

        if ($this->priceMin !== null) {
            $query->where('price_amount', '>=', $this->priceMin);
        }
        if ($this->priceMax !== null) {
            $query->where('price_amount', '<=', $this->priceMax);
        }

        if ($this->pricePerSqmMin !== null || $this->pricePerSqmMax !== null) {
            $query->whereNotNull('price_per_sqm_amount');
        }
        if ($this->pricePerSqmMin !== null) {
            $query->where('price_per_sqm_amount', '>=', $this->pricePerSqmMin);
        }
        if ($this->pricePerSqmMax !== null) {
            $query->where('price_per_sqm_amount', '<=', $this->pricePerSqmMax);
        }

        if ($this->roomsMin !== null) {
            $query->whereNotNull('room_count')->where('room_count', '>=', $this->roomsMin);
        }
        if ($this->roomsMax !== null) {
            $query->whereNotNull('room_count')->where('room_count', '<=', $this->roomsMax);
        }

        if ($this->areaMin !== null) {
            $query->whereRaw('COALESCE(total_area_sqm, living_area_sqm) >= ?', [$this->areaMin]);
        }
        if ($this->areaMax !== null) {
            $query->whereRaw('COALESCE(total_area_sqm, living_area_sqm) <= ?', [$this->areaMax]);
        }
    }

    public function isActive(): bool
    {
        return $this->marketType !== null
            || $this->priceMin !== null
            || $this->priceMax !== null
            || $this->pricePerSqmMin !== null
            || $this->pricePerSqmMax !== null
            || $this->roomsMin !== null
            || $this->roomsMax !== null
            || $this->areaMin !== null
            || $this->areaMax !== null;
    }

    private static function optionalNonNegativeFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_string($value)) {
            $value = str_replace(',', '.', trim($value));
        }
        if (! is_numeric($value)) {
            return null;
        }
        $f = (float) $value;

        return $f >= 0 ? $f : null;
    }

    private static function optionalPositiveInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        $i = filter_var($value, FILTER_VALIDATE_INT);

        return is_int($i) && $i > 0 ? $i : null;
    }
}
