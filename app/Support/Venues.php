<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * 遠征先の会場。OpenStreetMap から取り込んだ実在の施設だけを持つ。
 *
 * このリポジトリには以前、「ライブ alert seed 1」といった架空の項目と、
 * 収益計画や技術選定といった運営側のメモが公開画面に出ていた。
 * それらは削除し、出典をたどれるデータだけを載せている。
 *
 * データは scripts/build-venue-data.py が database/data/venues.json に書き出す。
 */
class Venues
{
    /** 都道府県ページのURLに使うローマ字。 */
    public const AREA_SLUGS = [
        '北海道' => 'hokkaido', '青森県' => 'aomori', '岩手県' => 'iwate', '宮城県' => 'miyagi',
        '秋田県' => 'akita', '山形県' => 'yamagata', '福島県' => 'fukushima', '茨城県' => 'ibaraki',
        '栃木県' => 'tochigi', '群馬県' => 'gunma', '埼玉県' => 'saitama', '千葉県' => 'chiba',
        '東京都' => 'tokyo', '神奈川県' => 'kanagawa', '新潟県' => 'niigata', '富山県' => 'toyama',
        '石川県' => 'ishikawa', '福井県' => 'fukui', '山梨県' => 'yamanashi', '長野県' => 'nagano',
        '岐阜県' => 'gifu', '静岡県' => 'shizuoka', '愛知県' => 'aichi', '三重県' => 'mie',
        '滋賀県' => 'shiga', '京都府' => 'kyoto', '大阪府' => 'osaka', '兵庫県' => 'hyogo',
        '奈良県' => 'nara', '和歌山県' => 'wakayama', '鳥取県' => 'tottori', '島根県' => 'shimane',
        '岡山県' => 'okayama', '広島県' => 'hiroshima', '山口県' => 'yamaguchi', '徳島県' => 'tokushima',
        '香川県' => 'kagawa', '愛媛県' => 'ehime', '高知県' => 'kochi', '福岡県' => 'fukuoka',
        '佐賀県' => 'saga', '長崎県' => 'nagasaki', '熊本県' => 'kumamoto', '大分県' => 'oita',
        '宮崎県' => 'miyazaki', '鹿児島県' => 'kagoshima', '沖縄県' => 'okinawa',
    ];

    /** 会場の種類ページのURLに使うローマ字。 */
    public const KIND_SLUGS = [
        'スタジアム・競技場' => 'stadium',
        'イベントホール' => 'hall',
        'ライブハウス' => 'livehouse',
        '展示場' => 'exhibition',
        '劇場・ホール' => 'theatre',
    ];

    public static function all(): Collection
    {
        return Cache::remember('venues', now()->addHour(), function () {
            $path = database_path('data/venues.json');

            if (! File::exists($path)) {
                throw new RuntimeException('database/data/venues.json が見つかりません。');
            }

            $payload = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

            return collect($payload['venues'])->map(function (array $venue, int $index) {
                $venue['slug'] = self::slugFor($venue, $index);
                $venue['area_slug'] = self::AREA_SLUGS[$venue['area']] ?? null;
                $venue['kind_slug'] = self::KIND_SLUGS[$venue['kind']] ?? null;

                return $venue;
            })->values();
        });
    }

    public static function meta(): array
    {
        $payload = json_decode(File::get(database_path('data/venues.json')), true, 512, JSON_THROW_ON_ERROR);

        return [
            'confirmedOn' => $payload['confirmedOn'],
            'sourceLabel' => $payload['sourceLabel'],
            'sourceUrl' => $payload['sourceUrl'],
        ];
    }

    public static function find(string $slug): ?array
    {
        return self::all()->firstWhere('slug', $slug);
    }

    public static function inArea(string $areaSlug): Collection
    {
        return self::all()->where('area_slug', $areaSlug)->values();
    }

    public static function ofKind(string $kindSlug): Collection
    {
        return self::all()->where('kind_slug', $kindSlug)->values();
    }

    public static function areaForSlug(string $slug): ?string
    {
        return array_search($slug, self::AREA_SLUGS, true) ?: null;
    }

    public static function kindForSlug(string $slug): ?string
    {
        return array_search($slug, self::KIND_SLUGS, true) ?: null;
    }

    /** 都道府県ごとの件数（多い順）。 */
    public static function areaCounts(): Collection
    {
        return self::all()
            ->groupBy('area')
            ->map->count()
            ->sortDesc()
            ->map(fn (int $count, string $area) => [
                'area' => $area,
                'slug' => self::AREA_SLUGS[$area] ?? null,
                'total' => $count,
            ])
            ->filter(fn (array $row) => $row['slug'] !== null)
            ->values();
    }

    /** 種類ごとの件数。 */
    public static function kindCounts(): Collection
    {
        return self::all()
            ->groupBy('kind')
            ->map->count()
            ->sortDesc()
            ->map(fn (int $count, string $kind) => [
                'kind' => $kind,
                'slug' => self::KIND_SLUGS[$kind] ?? null,
                'total' => $count,
            ])
            ->values();
    }

    /**
     * URLに使う名前。OSMの要素IDを混ぜて、同名の会場が衝突しないようにする。
     */
    private static function slugFor(array $venue, int $index): string
    {
        $area = self::AREA_SLUGS[$venue['area']] ?? 'jp';
        $id = Str::afterLast($venue['sourceRef'], '/');
        $type = Str::before($venue['sourceRef'], '/') === 'node' ? 'n' : 'w';

        return "{$area}-{$type}{$id}";
    }
}
