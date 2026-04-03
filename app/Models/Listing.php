<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    public $timestamps = false;

    protected $_market_types = [
        'primary' => 'New',
        'secondary' => 'Resale',
    ];

    protected $fillable = [
        'korter_object_id',
        'korter_layout_id',
        'is_mock',
        'locale',
        'city',
        'type',
        'listing_index',
        'code',
        'images',
        'image_alt',
        'kicker',
        'title',
        'address_line',
        'district',
        'latitude',
        'longitude',
        'developer',
        'built_year',
        'total_area_sqm',
        'living_area_sqm',
        'kitchen_area_sqm',
        'land_parcel_area_sqm',
        'terrace_area_sqm',
        'bedroom_count',
        'bathroom_count',
        'room_count',
        'ceiling_height_m',
        'has_balcony',
        'has_terrace',
        'parking',
        'floors_label',
        'property_subtype',
        'description_by_developer',
        'price',
        'price_amount',
        'price_currency',
        'price_per_sqm',
        'price_per_sqm_amount',
        'chips',
        'modal_anchor',
        'modal_title',
        'address',
        'bullets',
        'tip',
        'market_type',
    ];

    /**
     * @return array<string, mixed>
     */
    public function toSiteArray(): array
    {
        /** @var list<array{file: string, alt: string}> $images */
        $images = $this->images ?? [];
        $first = $images[0] ?? ['file' => '', 'alt' => ''];

        return [
            'id' => $this->id,
            'type' => $this->type,
            'is_mock' => (bool) $this->is_mock,
            'images' => $images,
            'image' => $first['file'],
            'image_alt' => $first['alt'],
            'kicker' => $this->kicker,
            'title' => $this->title,
            'address_line' => $this->address_line,
            'district' => $this->district,
            'latitude' => $this->latitude !== null ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null ? (float) $this->longitude : null,
            'developer' => $this->developer,
            'built_year' => $this->built_year,
            'total_area_sqm' => $this->total_area_sqm !== null ? (float) $this->total_area_sqm : null,
            'living_area_sqm' => $this->living_area_sqm !== null ? (float) $this->living_area_sqm : null,
            'kitchen_area_sqm' => $this->kitchen_area_sqm !== null ? (float) $this->kitchen_area_sqm : null,
            'land_parcel_area_sqm' => $this->land_parcel_area_sqm !== null ? (float) $this->land_parcel_area_sqm : null,
            'terrace_area_sqm' => $this->terrace_area_sqm !== null ? (float) $this->terrace_area_sqm : null,
            'bedroom_count' => $this->bedroom_count,
            'bathroom_count' => $this->bathroom_count,
            'room_count' => $this->room_count,
            'ceiling_height_m' => $this->ceiling_height_m !== null ? (float) $this->ceiling_height_m : null,
            'has_balcony' => $this->has_balcony,
            'has_terrace' => $this->has_terrace,
            'parking' => $this->parking,
            'floors_label' => $this->floors_label,
            'property_subtype' => $this->property_subtype,
            'description_by_developer' => $this->description_by_developer,
            'price' => $this->price,
            'price_per_sqm' => $this->price_per_sqm,
            'chips' => $this->chips,
            'modal_anchor' => $this->modal_anchor,
            'modal_title' => $this->modal_title,
            'address' => $this->address,
            'bullets' => $this->bullets,
            'tip' => $this->tip,
            'code' => $this->code,
            'market_type' => $this->_market_types[$this->market_type]
        ];
    }

    protected function casts(): array
    {
        return [
            'is_mock' => 'boolean',
            'listing_index' => 'integer',
            'built_year' => 'integer',
            'total_area_sqm' => 'decimal:2',
            'living_area_sqm' => 'decimal:2',
            'kitchen_area_sqm' => 'decimal:2',
            'land_parcel_area_sqm' => 'decimal:2',
            'terrace_area_sqm' => 'decimal:2',
            'price_amount' => 'decimal:2',
            'price_per_sqm_amount' => 'decimal:2',
            'bedroom_count' => 'integer',
            'bathroom_count' => 'integer',
            'room_count' => 'integer',
            'ceiling_height_m' => 'decimal:2',
            'has_balcony' => 'boolean',
            'has_terrace' => 'boolean',
            'chips' => 'array',
            'bullets' => 'array',
            'images' => 'array',
        ];
    }
}
