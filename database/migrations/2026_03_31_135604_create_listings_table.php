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
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 5);
            $table->unsignedTinyInteger('listing_index');
            $table->string('code', 20);
            $table->string('image');
            $table->text('image_alt');
            $table->string('kicker');
            $table->string('title');
            $table->string('price');
            $table->json('chips');
            $table->string('modal_anchor', 10);
            $table->text('modal_title');
            $table->text('address');
            $table->json('bullets');
            $table->text('tip');
            $table->unique(['locale', 'listing_index']);
        });

        foreach ($this->allRows() as $row) {
            DB::table('listings')->insert($row);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('listings');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function allRows(): array
    {
        return array_merge($this->enRows(), $this->ruRows(), $this->jaRows());
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function enRows(): array
    {
        $j = fn (array $a) => json_encode($a, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        return [
            [
                'locale' => 'en',
                'listing_index' => 1,
                'code' => 'MG-001',
                'image' => 'property-1.svg',
                'image_alt' => 'Placeholder photo for MG-001',
                'kicker' => 'MG-001 · Apartment · Tbilisi',
                'title' => 'Modern 2BR Apartment in Vake',
                'price' => '165,000 GEL',
                'chips' => $j(['78 m²', '2 beds', '1 bath']),
                'modal_anchor' => 'p1',
                'modal_title' => 'MG-001 · Modern 2BR Apartment in Vake (Tbilisi)',
                'address' => 'Address (demo): Vake, 120a Tamarashvili St · Built 2018 · Secure entrance',
                'bullets' => $j([
                    ['label' => 'Area', 'text' => '78 m² · 6th floor · Elevator access'],
                    ['label' => 'Layout', 'text' => '2 bedrooms · 1 bathroom · Spacious living room'],
                    ['label' => 'Highlights', 'text' => 'Panoramic balcony · Dedicated parking spot · Quiet courtyard'],
                ]),
                'tip' => 'Tip: mention <b>MG-001</b> in your message so we can match you with the right offer.',
            ],
            [
                'locale' => 'en',
                'listing_index' => 2,
                'code' => 'MG-002',
                'image' => 'property-2.svg',
                'image_alt' => 'Placeholder photo for MG-002',
                'kicker' => 'MG-002 · Villa · Batumi',
                'title' => 'Coastal Villa with Terrace',
                'price' => '280,000 GEL',
                'chips' => $j(['132 m²', '3 beds', '2 baths']),
                'modal_anchor' => 'p2',
                'modal_title' => 'MG-002 · Coastal Villa with Terrace (Batumi)',
                'address' => 'Address (demo): Batumi, Seaside District · Land 420 m² · Built 2020',
                'bullets' => $j([
                    ['label' => 'Area', 'text' => '132 m² + terrace · Private parking · Storage room'],
                    ['label' => 'Layout', 'text' => '3 bedrooms · 2 bathrooms · Open-plan kitchen'],
                    ['label' => 'Highlights', 'text' => 'Terrace for sunset dining · Garden path · Quiet neighbors'],
                ]),
                'tip' => 'Tip: mention <b>MG-002</b> in your message.',
            ],
            [
                'locale' => 'en',
                'listing_index' => 3,
                'code' => 'MG-003',
                'image' => 'property-3.svg',
                'image_alt' => 'Placeholder photo for MG-003',
                'kicker' => 'MG-003 · House · Kutaisi',
                'title' => '4BR House with Garden',
                'price' => '230,000 GEL',
                'chips' => $j(['195 m²', '4 beds', 'Garden']),
                'modal_anchor' => 'p3',
                'modal_title' => 'MG-003 · 4BR House with Garden (Kutaisi)',
                'address' => 'Address (demo): Kutaisi, Green Belt · Land 560 m² · Built 2016',
                'bullets' => $j([
                    ['label' => 'Area', 'text' => '195 m² · Garden & patio · Workshop space'],
                    ['label' => 'Layout', 'text' => '4 bedrooms · 2 bathrooms · Family dining area'],
                    ['label' => 'Highlights', 'text' => 'Fruit trees (demo) · Low-maintenance landscaping · Safe driveway'],
                ]),
                'tip' => 'Tip: mention <b>MG-003</b> in your message.',
            ],
            [
                'locale' => 'en',
                'listing_index' => 4,
                'code' => 'MG-004',
                'image' => 'property-4.svg',
                'image_alt' => 'Placeholder photo for MG-004',
                'kicker' => 'MG-004 · Apartment · Gori',
                'title' => 'Cozy 1BR Near Old Town',
                'price' => '72,500 GEL',
                'chips' => $j(['52 m²', '1 bed', '3rd floor']),
                'modal_anchor' => 'p4',
                'modal_title' => 'MG-004 · Cozy 1BR Near Old Town (Gori)',
                'address' => 'Address (demo): Gori, Old Town Edge · Built 2014 · Courtyard view',
                'bullets' => $j([
                    ['label' => 'Area', 'text' => '52 m² · 3rd floor · Bright windows'],
                    ['label' => 'Layout', 'text' => '1 bedroom · 1 bathroom · Compact, efficient kitchen'],
                    ['label' => 'Highlights', 'text' => 'Walking distance to cafes · Quiet street (demo) · Storage closet'],
                ]),
                'tip' => 'Tip: mention <b>MG-004</b> in your message.',
            ],
            [
                'locale' => 'en',
                'listing_index' => 5,
                'code' => 'MG-005',
                'image' => 'property-5.svg',
                'image_alt' => 'Placeholder photo for MG-005',
                'kicker' => 'MG-005 · Apartment · Rustavi',
                'title' => 'Modern Studio in City Center',
                'price' => '54,000 GEL',
                'chips' => $j(['39 m²', 'Studio', 'Elevator']),
                'modal_anchor' => 'p5',
                'modal_title' => 'MG-005 · Modern Studio in City Center (Rustavi)',
                'address' => 'Address (demo): Rustavi, City Center · Built 2019 · Elevator building',
                'bullets' => $j([
                    ['label' => 'Area', 'text' => '39 m² · Studio layout · Low monthly maintenance (demo)'],
                    ['label' => 'Layout', 'text' => 'Open-plan sleeping & living area · 1 bathroom'],
                    ['label' => 'Highlights', 'text' => 'Great for first-time buyers · Nearby transit · Practical storage'],
                ]),
                'tip' => 'Tip: mention <b>MG-005</b> in your message.',
            ],
            [
                'locale' => 'en',
                'listing_index' => 6,
                'code' => 'MG-006',
                'image' => 'property-6.svg',
                'image_alt' => 'Placeholder photo for MG-006',
                'kicker' => 'MG-006 · Villa · Tbilisi',
                'title' => 'Luxury Hillside Villa',
                'price' => '410,000 GEL',
                'chips' => $j(['210 m²', '4 beds', 'Private yard']),
                'modal_anchor' => 'p6',
                'modal_title' => 'MG-006 · Luxury Hillside Villa (Tbilisi)',
                'address' => 'Address (demo): Tbilisi, Hillside Park · Land 750 m² · Built 2017',
                'bullets' => $j([
                    ['label' => 'Area', 'text' => '210 m² · Private yard · Outdoor seating zone'],
                    ['label' => 'Layout', 'text' => '4 bedrooms · 3 bathrooms · Study room (demo)'],
                    ['label' => 'Highlights', 'text' => 'Scenic views · Smart security features (demo) · Spacious terrace'],
                ]),
                'tip' => 'Tip: mention <b>MG-006</b> in your message.',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function ruRows(): array
    {
        $j = fn (array $a) => json_encode($a, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        return [
            [
                'locale' => 'ru',
                'listing_index' => 1,
                'code' => 'MG-001',
                'image' => 'property-1.svg',
                'image_alt' => 'Плейсхолдер фото для MG-001',
                'kicker' => 'MG-001 · Квартира · Тбилиси',
                'title' => 'Современная 2BR квартира в Ваке',
                'price' => '165,000 GEL',
                'chips' => $j(['78 м²', '2 спальни', '1 санузел']),
                'modal_anchor' => 'p1',
                'modal_title' => 'MG-001 · Современная 2BR квартира в Ваке (Тбилиси)',
                'address' => 'Адрес (демо): Ваке, ул. Тамарашвили 120а · Построен в 2018 · Безопасный подъезд',
                'bullets' => $j([
                    ['label' => 'Площадь', 'text' => '78 м² · 6 этаж · Лифт'],
                    ['label' => 'Планировка', 'text' => '2 спальни · 1 санузел · Просторная гостиная'],
                    ['label' => 'Плюсы', 'text' => 'Панорамный балкон · Закреплённое парковочное место · Тихий двор'],
                ]),
                'tip' => 'Подсказка: укажите <b>MG-001</b> в сообщении, чтобы мы быстро нашли объект.',
            ],
            [
                'locale' => 'ru',
                'listing_index' => 2,
                'code' => 'MG-002',
                'image' => 'property-2.svg',
                'image_alt' => 'Плейсхолдер фото для MG-002',
                'kicker' => 'MG-002 · Вилла · Батуми',
                'title' => 'Вилла у моря с террасой',
                'price' => '280,000 GEL',
                'chips' => $j(['132 м²', '3 спальни', '2 санузла']),
                'modal_anchor' => 'p2',
                'modal_title' => 'MG-002 · Вилла у моря с террасой (Батуми)',
                'address' => 'Адрес (демо): Батуми, прибрежный район · Участок 420 м² · Построен в 2020',
                'bullets' => $j([
                    ['label' => 'Площадь', 'text' => '132 м² + терраса · Частная парковка · Кладовая'],
                    ['label' => 'Планировка', 'text' => '3 спальни · 2 санузла · Кухня-гостиная'],
                    ['label' => 'Плюсы', 'text' => 'Терраса для ужинов на закате · Садовая дорожка · Тихие соседи'],
                ]),
                'tip' => 'Подсказка: укажите <b>MG-002</b> в сообщении.',
            ],
            [
                'locale' => 'ru',
                'listing_index' => 3,
                'code' => 'MG-003',
                'image' => 'property-3.svg',
                'image_alt' => 'Плейсхолдер фото для MG-003',
                'kicker' => 'MG-003 · Дом · Кутаиси',
                'title' => 'Дом 4BR с садом',
                'price' => '230,000 GEL',
                'chips' => $j(['195 м²', '4 спальни', 'Сад']),
                'modal_anchor' => 'p3',
                'modal_title' => 'MG-003 · Дом 4BR с садом (Кутаиси)',
                'address' => 'Адрес (демо): Кутаиси, зелёная зона · Участок 560 м² · Построен в 2016',
                'bullets' => $j([
                    ['label' => 'Площадь', 'text' => '195 м² · Сад и патио · Мастерская'],
                    ['label' => 'Планировка', 'text' => '4 спальни · 2 санузла · Семейная столовая'],
                    ['label' => 'Плюсы', 'text' => 'Фруктовые деревья (демо) · Простое обслуживание участка · Удобный заезд'],
                ]),
                'tip' => 'Подсказка: укажите <b>MG-003</b> в сообщении.',
            ],
            [
                'locale' => 'ru',
                'listing_index' => 4,
                'code' => 'MG-004',
                'image' => 'property-4.svg',
                'image_alt' => 'Плейсхолдер фото для MG-004',
                'kicker' => 'MG-004 · Квартира · Гори',
                'title' => 'Уютная 1BR рядом со старым городом',
                'price' => '72,500 GEL',
                'chips' => $j(['52 м²', '1 спальня', '3 этаж']),
                'modal_anchor' => 'p4',
                'modal_title' => 'MG-004 · Уютная 1BR рядом со старым городом (Гори)',
                'address' => 'Адрес (демо): Гори, на окраине старого города · Построен в 2014 · Вид во двор',
                'bullets' => $j([
                    ['label' => 'Площадь', 'text' => '52 м² · 3 этаж · Светлые окна'],
                    ['label' => 'Планировка', 'text' => '1 спальня · 1 санузел · Компактная кухня'],
                    ['label' => 'Плюсы', 'text' => 'Рядом кафе · Тихая улица (демо) · Кладовая'],
                ]),
                'tip' => 'Подсказка: укажите <b>MG-004</b> в сообщении.',
            ],
            [
                'locale' => 'ru',
                'listing_index' => 5,
                'code' => 'MG-005',
                'image' => 'property-5.svg',
                'image_alt' => 'Плейсхолдер фото для MG-005',
                'kicker' => 'MG-005 · Квартира · Рустави',
                'title' => 'Современная студия в центре',
                'price' => '54,000 GEL',
                'chips' => $j(['39 м²', 'Студия', 'Лифт']),
                'modal_anchor' => 'p5',
                'modal_title' => 'MG-005 · Современная студия в центре (Рустави)',
                'address' => 'Адрес (демо): Рустави, центр · Построен в 2019 · Дом с лифтом',
                'bullets' => $j([
                    ['label' => 'Площадь', 'text' => '39 м² · Студия · Низкие коммунальные (демо)'],
                    ['label' => 'Планировка', 'text' => 'Объединённая зона сна и гостиной · 1 санузел'],
                    ['label' => 'Плюсы', 'text' => 'Удобно для первой покупки · Транспорт рядом · Практичное хранение'],
                ]),
                'tip' => 'Подсказка: укажите <b>MG-005</b> в сообщении.',
            ],
            [
                'locale' => 'ru',
                'listing_index' => 6,
                'code' => 'MG-006',
                'image' => 'property-6.svg',
                'image_alt' => 'Плейсхолдер фото для MG-006',
                'kicker' => 'MG-006 · Вилла · Тбилиси',
                'title' => 'Премиальная вилла на холме',
                'price' => '410,000 GEL',
                'chips' => $j(['210 м²', '4 спальни', 'Свой двор']),
                'modal_anchor' => 'p6',
                'modal_title' => 'MG-006 · Премиальная вилла на холме (Тбилиси)',
                'address' => 'Адрес (демо): Тбилиси, Hillside Park · Участок 750 м² · Построен в 2017',
                'bullets' => $j([
                    ['label' => 'Площадь', 'text' => '210 м² · Свой двор · Зона отдыха на улице'],
                    ['label' => 'Планировка', 'text' => '4 спальни · 3 санузла · Кабинет (демо)'],
                    ['label' => 'Плюсы', 'text' => 'Виды · Умная безопасность (демо) · Просторная терраса'],
                ]),
                'tip' => 'Подсказка: укажите <b>MG-006</b> в сообщении.',
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function jaRows(): array
    {
        $j = fn (array $a) => json_encode($a, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        return [
            [
                'locale' => 'ja',
                'listing_index' => 1,
                'code' => 'MG-001',
                'image' => 'property-1.svg',
                'image_alt' => 'MG-001 のプレースホルダー画像',
                'kicker' => 'MG-001 · アパート · トビリシ',
                'title' => 'ヴァケのモダン2BR',
                'price' => '165,000 GEL',
                'chips' => $j(['78 m²', '2ベッド', '1バス']),
                'modal_anchor' => 'p1',
                'modal_title' => 'MG-001 · ヴァケのモダン2BR（トビリシ）',
                'address' => '住所（デモ）: Vake, 120a Tamarashvili St · 2018年築 · セキュアエントランス',
                'bullets' => $j([
                    ['label' => '面積', 'text' => '78 m² · 6階 · エレベーター'],
                    ['label' => '間取り', 'text' => '2ベッドルーム · 1バスルーム · 広めのリビング'],
                    ['label' => 'ポイント', 'text' => '眺望バルコニー · 専用駐車枠 · 静かな中庭'],
                ]),
                'tip' => 'ヒント: メッセージに <b>MG-001</b> を入れてください。',
            ],
            [
                'locale' => 'ja',
                'listing_index' => 2,
                'code' => 'MG-002',
                'image' => 'property-2.svg',
                'image_alt' => 'MG-002 のプレースホルダー画像',
                'kicker' => 'MG-002 · ヴィラ · バトゥミ',
                'title' => '海辺のテラス付きヴィラ',
                'price' => '280,000 GEL',
                'chips' => $j(['132 m²', '3ベッド', '2バス']),
                'modal_anchor' => 'p2',
                'modal_title' => 'MG-002 · 海辺のテラス付きヴィラ（バトゥミ）',
                'address' => '住所（デモ）: Batumi, Seaside District · 土地 420 m² · 2020年築',
                'bullets' => $j([
                    ['label' => '面積', 'text' => '132 m² + テラス · 専用駐車 · 収納'],
                    ['label' => '間取り', 'text' => '3ベッドルーム · 2バスルーム · オープンキッチン'],
                    ['label' => 'ポイント', 'text' => 'サンセットテラス · ガーデン小径 · 静かな環境'],
                ]),
                'tip' => 'ヒント: <b>MG-002</b> を記載してください。',
            ],
            [
                'locale' => 'ja',
                'listing_index' => 3,
                'code' => 'MG-003',
                'image' => 'property-3.svg',
                'image_alt' => 'MG-003 のプレースホルダー画像',
                'kicker' => 'MG-003 · 戸建て · クタイシ',
                'title' => '庭付き4BRハウス',
                'price' => '230,000 GEL',
                'chips' => $j(['195 m²', '4ベッド', '庭']),
                'modal_anchor' => 'p3',
                'modal_title' => 'MG-003 · 庭付き4BRハウス（クタイシ）',
                'address' => '住所（デモ）: Kutaisi, Green Belt · 土地 560 m² · 2016年築',
                'bullets' => $j([
                    ['label' => '面積', 'text' => '195 m² · 庭 & パティオ · 作業スペース'],
                    ['label' => '間取り', 'text' => '4ベッドルーム · 2バスルーム · ダイニング'],
                    ['label' => 'ポイント', 'text' => '果樹（デモ） · 手入れしやすい庭 · 安全なドライブウェイ'],
                ]),
                'tip' => 'ヒント: <b>MG-003</b> を記載してください。',
            ],
            [
                'locale' => 'ja',
                'listing_index' => 4,
                'code' => 'MG-004',
                'image' => 'property-4.svg',
                'image_alt' => 'MG-004 のプレースホルダー画像',
                'kicker' => 'MG-004 · アパート · ゴリ',
                'title' => '旧市街近くの1BR',
                'price' => '72,500 GEL',
                'chips' => $j(['52 m²', '1ベッド', '3階']),
                'modal_anchor' => 'p4',
                'modal_title' => 'MG-004 · 旧市街近くの1BR（ゴリ）',
                'address' => '住所（デモ）: Gori, Old Town Edge · 2014年築 · 中庭ビュー',
                'bullets' => $j([
                    ['label' => '面積', 'text' => '52 m² · 3階 · 明るい採光'],
                    ['label' => '間取り', 'text' => '1ベッドルーム · 1バスルーム · コンパクトキッチン'],
                    ['label' => 'ポイント', 'text' => 'カフェ徒歩圏 · 静かな通り（デモ） · 収納'],
                ]),
                'tip' => 'ヒント: <b>MG-004</b> を記載してください。',
            ],
            [
                'locale' => 'ja',
                'listing_index' => 5,
                'code' => 'MG-005',
                'image' => 'property-5.svg',
                'image_alt' => 'MG-005 のプレースホルダー画像',
                'kicker' => 'MG-005 · アパート · ルスタヴィ',
                'title' => '中心部のモダンスタジオ',
                'price' => '54,000 GEL',
                'chips' => $j(['39 m²', 'スタジオ', 'エレベーター']),
                'modal_anchor' => 'p5',
                'modal_title' => 'MG-005 · 中心部のモダンスタジオ（ルスタヴィ）',
                'address' => '住所（デモ）: Rustavi, City Center · 2019年築 · エレベーターあり',
                'bullets' => $j([
                    ['label' => '面積', 'text' => '39 m² · スタジオ · 月額費用低め（デモ）'],
                    ['label' => '間取り', 'text' => 'ワンルーム · 1バスルーム'],
                    ['label' => 'ポイント', 'text' => '初めての購入に · 交通至近 · 収納が実用的'],
                ]),
                'tip' => 'ヒント: <b>MG-005</b> を記載してください。',
            ],
            [
                'locale' => 'ja',
                'listing_index' => 6,
                'code' => 'MG-006',
                'image' => 'property-6.svg',
                'image_alt' => 'MG-006 のプレースホルダー画像',
                'kicker' => 'MG-006 · ヴィラ · トビリシ',
                'title' => '丘の上のラグジュアリーヴィラ',
                'price' => '410,000 GEL',
                'chips' => $j(['210 m²', '4ベッド', '専用庭']),
                'modal_anchor' => 'p6',
                'modal_title' => 'MG-006 · 丘の上のラグジュアリーヴィラ（トビリシ）',
                'address' => '住所（デモ）: Tbilisi, Hillside Park · 土地 750 m² · 2017年築',
                'bullets' => $j([
                    ['label' => '面積', 'text' => '210 m² · 専用庭 · アウトドア席'],
                    ['label' => '間取り', 'text' => '4ベッドルーム · 3バスルーム · 書斎（デモ）'],
                    ['label' => 'ポイント', 'text' => '眺望 · スマートセキュリティ（デモ） · 広いテラス'],
                ]),
                'tip' => 'ヒント: <b>MG-006</b> を記載してください。',
            ],
        ];
    }
};
