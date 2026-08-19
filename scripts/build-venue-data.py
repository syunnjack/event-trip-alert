"""OpenStreetMap から、遠征先になる会場と、その最寄り駅を取り出す。

出典: OpenStreetMap contributors（ODbL 1.0） https://www.openstreetmap.org/copyright

このリポジトリには以前、「ライブ alert seed 1」といった架空の項目と、
収益計画や技術選定といった運営側のメモがそのまま入っていた。すべて捨てて、
実在する会場（ホール・ライブハウス・スタジアム・展示場）を出典つきで載せる。

会場と駅を同じ問い合わせで取り、最寄り駅は手元で計算する（距離は直線距離）。

使い方: python scripts/build-venue-data.py database/data/venues.json
"""
import json
import math
import re
import sys
import time
import urllib.parse
import urllib.request
from datetime import date
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
CACHE = ROOT / 'scripts' / '.cache'

OVERPASS_ENDPOINTS = [
    'https://overpass-api.de/api/interpreter',
    'https://overpass.kumi.systems/api/interpreter',
    'https://overpass.osm.ch/api/interpreter',
    'https://overpass.private.coffee/api/interpreter',
    'https://overpass.osm.jp/api/interpreter',
]
UA = 'event-trip-alert-data/1.0 (+https://eventtripalert.jp)'
DELAY = 4.0

PREFECTURES = [
    '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県',
    '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県', '新潟県', '富山県', '石川県', '福井県',
    '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府',
    '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県',
    '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県',
    '鹿児島県', '沖縄県',
]

# 会場の種類。左のタグに当たったものを、右の名前で分類する。
VENUE_KINDS = [
    ('leisure=stadium', 'スタジアム・競技場'),
    ('amenity=events_venue', 'イベントホール'),
    ('amenity=music_venue', 'ライブハウス'),
    ('amenity=exhibition_centre', '展示場'),
    ('amenity=theatre', '劇場・ホール'),
]

# 会場と駅は別々に取る。ひとつにまとめると重すぎて 504 が返ることが多い。
VENUE_QUERY = """
[out:json][timeout:300];
area["name"="{prefecture}"]["admin_level"="4"]->.pref;
(
  nwr["leisure"="stadium"]["name"](area.pref);
  nwr["amenity"="events_venue"]["name"](area.pref);
  nwr["amenity"="music_venue"]["name"](area.pref);
  nwr["amenity"="exhibition_centre"]["name"](area.pref);
  nwr["amenity"="theatre"]["name"](area.pref);
);
out tags center;
"""

# 駅は点で十分。全国ぶんを一度に取れるので、都道府県ごとには問い合わせない。
STATION_QUERY = """
[out:json][timeout:420];
area["name"="日本"]["admin_level"="2"]->.jp;
node["railway"="station"]["name"](area.jp);
out;
"""

DENY_NAME = re.compile('跡$|跡地|予定地|案内図')


def fetch(prefecture: str, query: str, suffix: str) -> list[dict]:
    CACHE.mkdir(exist_ok=True)
    path = CACHE / f'{suffix}-{PREFECTURES.index(prefecture):02d}.json'

    if path.exists():
        return json.loads(path.read_text(encoding='utf-8'))

    return fetch_query(query.format(prefecture=prefecture), path)


def fetch_query(query: str, path: Path) -> list[dict]:
    """Overpass へ問い合わせて、結果を path に残す。"""
    CACHE.mkdir(exist_ok=True)
    body = urllib.parse.urlencode({'data': query}).encode()
    payload = None
    last_error = None

    for attempt in range(6):
        endpoint = OVERPASS_ENDPOINTS[attempt % len(OVERPASS_ENDPOINTS)]
        request = urllib.request.Request(endpoint, data=body, headers={'User-Agent': UA})

        try:
            with urllib.request.urlopen(request, timeout=320) as response:
                payload = json.loads(response.read().decode('utf-8', 'replace'))

            # ミラーがエラーを返さずに空を返すことがある。0件は信じない。
            if not payload.get('elements'):
                raise RuntimeError('結果が空でした')

            break
        except Exception as error:
            last_error = error
            wait = DELAY * (attempt + 1)
            print(f"  {error} のため {wait:.0f} 秒待って別のサーバで再試行します", flush=True)
            time.sleep(wait)

    if payload is None:
        raise RuntimeError(f'取得できませんでした: {last_error}')

    path.write_text(json.dumps(payload['elements'], ensure_ascii=False), encoding='utf-8')
    time.sleep(DELAY)

    return payload['elements']


def kind_of(tags: dict) -> str | None:
    for selector, name in VENUE_KINDS:
        key, value = selector.split('=')
        if tags.get(key) == value:
            return name

    return None


def distance_km(lat1: float, lng1: float, lat2: float, lng2: float) -> float:
    radius = 6371.0
    dlat = math.radians(lat2 - lat1)
    dlng = math.radians(lng2 - lng1)
    a = (math.sin(dlat / 2) ** 2
         + math.cos(math.radians(lat1)) * math.cos(math.radians(lat2)) * math.sin(dlng / 2) ** 2)

    return radius * 2 * math.atan2(math.sqrt(a), math.sqrt(1 - a))


def coordinates(element: dict) -> tuple[float, float] | None:
    center = element.get('center') or element
    lat, lng = center.get('lat'), center.get('lon')

    if lat is None or lng is None:
        return None

    return float(lat), float(lng)


def all_stations() -> list[tuple[str, tuple[float, float]]]:
    """全国の鉄道駅。会場から最も近いものを選ぶために使う。"""
    path = CACHE / 'stations-japan.json'

    if path.exists():
        elements = json.loads(path.read_text(encoding='utf-8'))
    else:
        elements = fetch_query(STATION_QUERY, path)

    stations = []
    for element in elements:
        name = (element.get('tags', {}).get('name') or '').strip()
        position = coordinates(element)

        if name and position:
            stations.append((name, position))

    return stations


def main() -> None:
    output = Path(sys.argv[1])
    stations = all_stations()
    print(f'駅 {len(stations)}件を読み込みました', flush=True)

    # 第2引数に「0-15」のように書くと、その範囲の都道府県だけを取りに行く。
    # Overpass が重い日に、何本かに分けて取得するために使う。
    targets = PREFECTURES
    if len(sys.argv) > 2:
        start, end = (int(part) for part in sys.argv[2].split('-'))
        targets = PREFECTURES[start:end]
        print(f'{targets[0]}〜{targets[-1]} だけを取得します', flush=True)

    venues = []

    for prefecture in targets:
        try:
            elements = fetch(prefecture, VENUE_QUERY, 'venues')
        except Exception as error:
            print(f'{prefecture} の取得に失敗しました: {error}', flush=True)
            continue

        found = []
        for element in elements:
            tags = element.get('tags', {})
            name = (tags.get('name') or '').strip()
            position = coordinates(element)

            if not name or position is None:
                continue

            kind = kind_of(tags)

            if kind is None or DENY_NAME.search(name):
                continue

            found.append((element, tags, name, kind, position))

        added = 0
        for element, tags, name, kind, (lat, lng) in found:
            nearest = None

            for station_name, (slat, slng) in stations:
                km = distance_km(lat, lng, slat, slng)

                if nearest is None or km < nearest[1]:
                    nearest = (station_name, km)

            venues.append({
                'name': name,
                'kind': kind,
                'area': prefecture,
                'city': tags.get('addr:city'),
                'address': tags.get('addr:full') or None,
                'capacity': tags.get('capacity') or tags.get('seats'),
                'website': tags.get('website') or tags.get('contact:website'),
                'phone': tags.get('phone') or tags.get('contact:phone'),
                'operator': tags.get('operator'),
                'lat': round(lat, 7),
                'lng': round(lng, 7),
                'nearestStation': nearest[0] if nearest else None,
                'nearestStationKm': round(nearest[1], 2) if nearest else None,
                'sourceRef': f"{element['type']}/{element['id']}",
            })
            added += 1

        print(f'{prefecture} 会場{added}件 / 駅{len(stations)}件', flush=True)

    venues.sort(key=lambda venue: (venue['area'], venue['kind'], venue['name']))

    output.parent.mkdir(parents=True, exist_ok=True)
    output.write_text(json.dumps({
        'confirmedOn': date.today().isoformat(),
        'sourceLabel': 'OpenStreetMap contributors（ODbL 1.0）',
        'sourceUrl': 'https://www.openstreetmap.org/copyright',
        'kinds': [name for _, name in VENUE_KINDS],
        'venues': venues,
    }, ensure_ascii=False), encoding='utf-8')

    print(f'{len(venues)}件を書き出しました')


main()
