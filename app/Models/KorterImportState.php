<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KorterImportState extends Model
{
    protected $table = 'korter_import_states';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_idle' => 'boolean',
        ];
    }

    public static function cursorRow(): self
    {
        /** @var self */
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'next_preset' => 1,
                'next_page' => 1,
                'is_idle' => false,
            ]
        );
    }
}
