<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->decimal('price_amount', 14, 2)->nullable()->after('price');
            $table->string('price_currency', 3)->nullable()->after('price_amount');
            $table->decimal('price_per_sqm_amount', 14, 2)->nullable()->after('price_per_sqm');
        });

        foreach (DB::table('listings')->whereNull('price_amount')->cursor() as $row) {
            $parsed = $this->parseLegacyPrice((string) $row->price);
            if ($parsed !== null) {
                DB::table('listings')->where('id', $row->id)->update([
                    'price_amount' => $parsed['amount'],
                    'price_currency' => $parsed['currency'],
                ]);
            }
        }

        foreach (DB::table('listings')->whereNotNull('price_amount')->cursor() as $row) {
            $area = $row->total_area_sqm ?? $row->living_area_sqm ?? null;
            if ($row->price_per_sqm_amount === null && $area !== null && (float) $area > 0 && $row->price_amount !== null) {
                $per = round((float) $row->price_amount / (float) $area, 2);
                DB::table('listings')->where('id', $row->id)->update(['price_per_sqm_amount' => $per]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn(['price_amount', 'price_currency', 'price_per_sqm_amount']);
        });
    }

    /**
     * @return array{amount: float, currency: string}|null
     */
    private function parseLegacyPrice(string $price): ?array
    {
        $price = trim($price);
        if ($price === '' || ! preg_match('/[\d,]+/', $price, $m)) {
            return null;
        }
        $amount = (float) str_replace(',', '', $m[0]);
        if ($amount <= 0) {
            return null;
        }
        $upper = strtoupper($price);
        $currency = 'USD';
        if (str_contains($upper, 'GEL')) {
            $currency = 'GEL';
        } elseif (str_contains($upper, 'EUR') || str_contains($price, '€')) {
            $currency = 'EUR';
        } elseif (str_contains($upper, 'USD') || str_contains($price, '$')) {
            $currency = 'USD';
        }

        return ['amount' => $amount, 'currency' => $currency];
    }
};
