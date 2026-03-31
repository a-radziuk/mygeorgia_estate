<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'korter_object_id',
        'is_mock',
        'locale',
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
        'description_by_developer',
        'price',
        'price_per_sqm',
        'chips',
        'modal_anchor',
        'modal_title',
        'address',
        'bullets',
        'tip',
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
        ];
    }

    protected function casts(): array
    {
        return [
            'is_mock' => 'boolean',
            'listing_index' => 'integer',
            'built_year' => 'integer',
            'chips' => 'array',
            'bullets' => 'array',
            'images' => 'array',
        ];
    }
}
