<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KorterImportRun extends Model
{
    protected $table = 'korter_import_runs';

    protected $fillable = [
        'preset',
        'page',
        'imported_count',
        'had_apartments',
        'http_ok',
        'parse_ok',
        'idle_skip',
        'url',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'had_apartments' => 'boolean',
            'http_ok' => 'boolean',
            'parse_ok' => 'boolean',
            'idle_skip' => 'boolean',
        ];
    }
}
