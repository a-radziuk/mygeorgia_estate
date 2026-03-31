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
            $table->string('address_line')->nullable()->after('title');
            $table->string('district')->nullable()->after('address_line');
            $table->decimal('latitude', 10, 7)->nullable()->after('district');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('developer')->nullable()->after('longitude');
            $table->unsignedSmallInteger('built_year')->nullable()->after('developer');
            $table->string('price_per_sqm')->nullable()->after('price');
        });

        foreach (DB::table('listings')->orderBy('id')->cursor() as $row) {
            $locale = (string) $row->locale;
            $idx = (int) $row->listing_index;
            $payload = $this->payloadFor($locale, $idx);
            DB::table('listings')->where('id', $row->id)->update($payload);
        }
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn([
                'address_line',
                'district',
                'latitude',
                'longitude',
                'developer',
                'built_year',
                'price_per_sqm',
            ]);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFor(string $locale, int $idx): array
    {
        /** @var array<int, array{lat: float, lng: float, year: int, area: float, price_gel: int}> */
        $geo = [
            1 => ['lat' => 41.7080000, 'lng' => 44.7720000, 'year' => 2018, 'area' => 78.0, 'price_gel' => 165000],
            2 => ['lat' => 41.6408000, 'lng' => 41.6369000, 'year' => 2020, 'area' => 132.0, 'price_gel' => 280000],
            3 => ['lat' => 42.2702000, 'lng' => 42.6940000, 'year' => 2016, 'area' => 195.0, 'price_gel' => 230000],
            4 => ['lat' => 41.9842000, 'lng' => 44.1098000, 'year' => 2014, 'area' => 52.0, 'price_gel' => 72500],
            5 => ['lat' => 41.5490000, 'lng' => 45.0110000, 'year' => 2019, 'area' => 39.0, 'price_gel' => 54000],
            6 => ['lat' => 41.7200000, 'lng' => 44.7800000, 'year' => 2017, 'area' => 210.0, 'price_gel' => 410000],
        ];

        $g = $geo[$idx] ?? $geo[1];
        $perSqm = (int) round($g['price_gel'] / $g['area']);
        $pricePerSqm = number_format($perSqm, 0, '', ',').' GEL/m²';

        $text = $this->localizedStrings($locale, $idx);

        return [
            'address_line' => $text['address_line'],
            'district' => $text['district'],
            'latitude' => $g['lat'],
            'longitude' => $g['lng'],
            'developer' => $text['developer'],
            'built_year' => $g['year'],
            'price_per_sqm' => $pricePerSqm,
        ];
    }

    /**
     * @return array{address_line: string, district: string, developer: string}
     */
    private function localizedStrings(string $locale, int $idx): array
    {
        $map = [
            'en' => [
                1 => [
                    'address_line' => '120a Tamarashvili St, Tbilisi',
                    'district' => 'Vake',
                    'developer' => 'Vake Residences Co.',
                ],
                2 => [
                    'address_line' => '12 Kobaladze St, Batumi',
                    'district' => 'Seaside',
                    'developer' => 'Black Sea Developments',
                ],
                3 => [
                    'address_line' => '8 Rustaveli Ave, Kutaisi',
                    'district' => 'Green Belt',
                    'developer' => 'Imereti Homes',
                ],
                4 => [
                    'address_line' => '3 Stalin St, Gori',
                    'district' => 'Old Town',
                    'developer' => 'Gori Properties',
                ],
                5 => [
                    'address_line' => '45 Tbilisi Ave, Rustavi',
                    'district' => 'City Center',
                    'developer' => 'Rustavi Urban Group',
                ],
                6 => [
                    'address_line' => 'Hillside Park Rd 7, Tbilisi',
                    'district' => 'Hillside Park',
                    'developer' => 'Capital View Estates',
                ],
            ],
            'ru' => [
                1 => [
                    'address_line' => 'ул. Тамарашвили 120а, Тбилиси',
                    'district' => 'Ваке',
                    'developer' => 'Vake Residences Co.',
                ],
                2 => [
                    'address_line' => 'ул. Кобаладзе 12, Батуми',
                    'district' => 'Приморский',
                    'developer' => 'Black Sea Developments',
                ],
                3 => [
                    'address_line' => 'пр. Руставели 8, Кутаиси',
                    'district' => 'Зелёная зона',
                    'developer' => 'Imereti Homes',
                ],
                4 => [
                    'address_line' => 'ул. Сталина 3, Гори',
                    'district' => 'Старый город',
                    'developer' => 'Gori Properties',
                ],
                5 => [
                    'address_line' => 'пр. Тбилиси 45, Рустави',
                    'district' => 'Центр',
                    'developer' => 'Rustavi Urban Group',
                ],
                6 => [
                    'address_line' => 'Hillside Park Rd 7, Тбилиси',
                    'district' => 'Hillside Park',
                    'developer' => 'Capital View Estates',
                ],
            ],
            'ja' => [
                1 => [
                    'address_line' => 'タマラシヴィリ通り120a, トビリシ',
                    'district' => 'ヴァケ',
                    'developer' => 'Vake Residences Co.',
                ],
                2 => [
                    'address_line' => 'コバラゼ通り12, バトゥミ',
                    'district' => '海辺',
                    'developer' => 'Black Sea Developments',
                ],
                3 => [
                    'address_line' => 'ルスタヴェリ大通り8, クタイシ',
                    'district' => 'グリーンベルト',
                    'developer' => 'Imereti Homes',
                ],
                4 => [
                    'address_line' => 'スターリン通り3, ゴリ',
                    'district' => '旧市街',
                    'developer' => 'Gori Properties',
                ],
                5 => [
                    'address_line' => 'トビリシ大通り45, ルスタヴィ',
                    'district' => '中心部',
                    'developer' => 'Rustavi Urban Group',
                ],
                6 => [
                    'address_line' => 'Hillside Park Rd 7, トビリシ',
                    'district' => 'ヒルサイドパーク',
                    'developer' => 'Capital View Estates',
                ],
            ],
        ];

        return $map[$locale][$idx] ?? $map['en'][$idx];
    }
};
