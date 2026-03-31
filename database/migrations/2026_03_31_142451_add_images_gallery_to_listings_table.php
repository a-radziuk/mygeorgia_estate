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
            $table->json('images')->nullable()->after('code');
        });

        /** @var array<int, array{0: string, 1: string}> */
        $extraByIndex = [
            1 => ['property-2.svg', 'property-3.svg'],
            2 => ['property-3.svg', 'property-4.svg'],
            3 => ['property-4.svg', 'property-5.svg'],
            4 => ['property-5.svg', 'property-6.svg'],
            5 => ['property-6.svg', 'property-1.svg'],
            6 => ['property-1.svg', 'property-2.svg'],
        ];

        foreach (DB::table('listings')->orderBy('id')->cursor() as $row) {
            $idx = (int) $row->listing_index;
            $extras = $extraByIndex[$idx] ?? ['property-1.svg', 'property-2.svg'];
            $images = [
                ['file' => $row->image, 'alt' => $row->image_alt],
                ['file' => $extras[0], 'alt' => $row->image_alt.' (2)'],
                ['file' => $extras[1], 'alt' => $row->image_alt.' (3)'],
            ];

            DB::table('listings')->where('id', $row->id)->update([
                'images' => json_encode($images, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
            ]);
        }

        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->string('image')->nullable()->after('code');
        });

        foreach (DB::table('listings')->orderBy('id')->cursor() as $row) {
            $images = json_decode((string) $row->images, true, 512, JSON_THROW_ON_ERROR);
            $first = is_array($images) && isset($images[0]['file']) ? (string) $images[0]['file'] : 'property-1.svg';

            DB::table('listings')->where('id', $row->id)->update(['image' => $first]);
        }

        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
