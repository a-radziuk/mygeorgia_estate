<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Listing extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'locale',
        'listing_index',
        'code',
        'image',
        'image_alt',
        'kicker',
        'title',
        'price',
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
        return [
            'image' => $this->image,
            'image_alt' => $this->image_alt,
            'kicker' => $this->kicker,
            'title' => $this->title,
            'price' => $this->price,
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
            'listing_index' => 'integer',
            'chips' => 'array',
            'bullets' => 'array',
        ];
    }
}
