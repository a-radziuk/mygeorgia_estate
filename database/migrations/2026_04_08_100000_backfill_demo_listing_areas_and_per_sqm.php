<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<int, float> */
    private array $areaByListingIndex = [
        1 => 78.0,
        2 => 132.0,
        3 => 195.0,
        4 => 52.0,
        5 => 39.0,
        6 => 210.0,
    ];

    public function up(): void
    {
        foreach ($this->areaByListingIndex as $index => $sqm) {
            DB::table('listings')
                ->where('listing_index', $index)
                ->whereNull('total_area_sqm')
                ->whereNull('living_area_sqm')
                ->update(['total_area_sqm' => $sqm]);
        }

        foreach (DB::table('listings')->whereNotNull('price_amount')->whereNull('price_per_sqm_amount')->cursor() as $row) {
            $area = $row->total_area_sqm ?? $row->living_area_sqm ?? null;
            if ($area === null || (float) $area <= 0) {
                continue;
            }
            $per = round((float) $row->price_amount / (float) $area, 2);
            DB::table('listings')->where('id', $row->id)->update(['price_per_sqm_amount' => $per]);
        }
    }

    public function down(): void
    {
        // Intentionally empty: demo backfill is not safely reversible.
    }
};
