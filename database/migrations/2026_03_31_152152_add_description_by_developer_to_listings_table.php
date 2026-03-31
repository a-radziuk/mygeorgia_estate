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
            $table->text('description_by_developer')->nullable()->after('built_year');
        });

        foreach (DB::table('listings')->orderBy('id')->cursor() as $row) {
            $locale = (string) $row->locale;
            $idx = (int) $row->listing_index;
            DB::table('listings')->where('id', $row->id)->update([
                'description_by_developer' => $this->descriptionFor($locale, $idx),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('listings', function (Blueprint $table) {
            $table->dropColumn('description_by_developer');
        });
    }

    private function descriptionFor(string $locale, int $idx): string
    {
        /** @var array<string, array<int, string>> */
        $map = [
            'en' => [
                1 => 'Designed for everyday comfort in Vake: generous daylight, a practical two-bedroom layout, and a calm courtyard setting. The building features a secure lobby and elevator access; finishes were selected for durability and easy upkeep.',
                2 => 'Sea-facing living in Batumi with wide glazing and a layout that keeps social spaces open. The development prioritizes ventilation and sound insulation so the promenade stays enjoyable year-round. Underground parking options are available to residents.',
                3 => 'A family-oriented home near Kutaisi’s green belt, with room to grow and space for a home office. We emphasized solid construction, efficient heating, and a garden that connects indoor and outdoor living.',
                4 => 'A compact city apartment close to Gori’s historic core—ideal as a starter home or a stable rental. The scheme focuses on straightforward maintenance, clear common-area rules, and predictable monthly costs.',
                5 => 'Rustavi center convenience with quick access to shops and transport links. The unit is laid out to minimize wasted circulation space, making it efficient for singles and young couples who want a simple move-in path.',
                6 => 'Panoramic views from Hillside Park with larger living volumes and premium specifications. This line targets buyers who want long-term quality: upgraded insulation, generous storage, and private outdoor space.',
            ],
            'ru' => [
                1 => 'Проект ориентирован на комфорт во Ваке: много света, удобная планировка на две спальни и спокойный двор. В доме — охраняемый вход и лифт; отделка подобрана с упором на долговечность и простой уход.',
                2 => 'Жильё у моря в Батуми с панорамным остеклением и открытой гостиной зоной. В проекте усилена вентиляция и шумоизоляция, чтобы набережная оставалась комфортной круглый год. Для жильцов доступен подземный паркинг.',
                3 => 'Дом для семьи у зелёной зоны Кутаиси — с запасом площади и зоной под кабинет. Мы сделали упор на надёжную конструкцию, эффективное отопление и участок, который связывает интерьер с садом.',
                4 => 'Компактная квартира у исторического центра Гори — как первая покупка или сдача в аренду. Решение простое в обслуживании, с понятными правилами по МОП и прогнозируемыми расходами.',
                5 => 'Удобная локация в центре Рустави: рядом магазины и транспорт. Планировка без лишних коридоров — для одиночек и пар, которым важен быстрый въезд.',
                6 => 'Панорамные виды в Hillside Park, просторные комнаты и усиленная комплектация. Линейка для тех, кто рассчитывает на долгий горизонт: улучшенная изоляция, много хранения и приватная зона на улице.',
            ],
            'ja' => [
                1 => 'ヴァケでの暮らしやすさを重視した設計です。採光と2ベッドの実用的な間取り、静かな中庭に面した環境。セキュアなエントランスとエレベーター付きで、内装は耐久性と手入れのしやすさを優先しています。',
                2 => 'バトゥミの海側。大きな開口と開放的なリビングを確保しつつ、換気と遮音に配慮しました。遊歩道の賑わいを一年中快適に楽しめるよう工夫しています。地下駐車の利用も可能です。',
                3 => 'クタイシの緑地帯近く、家族向けの広さと在宅ワークのスペースを確保。構造のしっかりした躯体、効率的な暖房、室内と庭をつなぐ屋外空間を重視しました。',
                4 => 'ゴリ旧市街に近いコンパクトな住戸。初めての購入や賃貸運用にも向きます。維持しやすさ、共用部ルールの明確さ、予測しやすいランニングコストを意識したプランです。',
                5 => 'ルスタヴィ中心部で買い物や交通へのアクセスが良い立地。無駄な廊下を抑えた間取りで、単身・若いカップルの入居をスムーズにします。',
                6 => 'ヒルサイドパークの眺望と、ゆとりのあるリビング、上質な仕様。長期保有を想定し、断熱の強化、収納、プライベートな屋外スペースを備えています。',
            ],
        ];

        return $map[$locale][$idx] ?? $map['en'][$idx];
    }
};
