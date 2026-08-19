# イベント遠征アラート（eventtripalert.jp）

ライブ・スポーツ・展示会で遠征するときに、**会場の場所と最寄り駅**を調べるサイト。

## 経緯

このリポジトリには以前、React の静的ページに「ライブ alert seed 1」といった
架空の項目と、「収益導線」「技術選定」「MVPは静的seed + localStorage」などの
運営側のメモがそのまま入っており、それが本番で公開されていた。
すべて削除し、出典をたどれる実データだけを載せる構成に作り直した。

## データ

| 内容 | 出所 |
|---|---|
| 会場（スタジアム・イベントホール・ライブハウス・展示場・劇場） | OpenStreetMap（ODbL 1.0） |
| 最寄り駅と距離 | 同じ地図データの鉄道駅から、直線距離で最も近いものを機械的に選定 |

**扱っていないもの**: 公演・試合の予定、チケット、宿や交通の予約。

## データの更新

```
python scripts/build-venue-data.py database/data/venues.json
```

`scripts/.cache/` に取得済みの応答が残るので、再実行しても必要な分しか
Overpass に問い合わせない。取得できなかった県は、そのキャッシュを消して
再実行すれば埋まる。

## 構成

| URL | 内容 |
|---|---|
| `/` | 種類別・都道府県別の入口 |
| `/kinds/{種類}` | 種類ごとの全国一覧（stadium / hall / livehouse / exhibition / theatre） |
| `/areas/{ローマ字}` | 都道府県ごとの会場一覧 |
| `/venues/{slug}` | 会場ページ（最寄り駅・収容人数・公式サイト・出典） |
| `/about` | データの出所と、扱っていないことの説明 |

## デプロイ

main へ push すると GitHub Actions が Xserver へ rsync する。
データベースは使わないので migrate も seed も無い。
必要なシークレット: `SSH_HOST` `SSH_USERNAME` `SSH_PRIVATE_KEY` `APP_KEY`。
任意: `GA_MEASUREMENT_ID` `GOOGLE_SITE_VERIFICATION`。
