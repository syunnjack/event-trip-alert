# Event Trip Alert

ライブ・イベント遠征アラート

## Repository

Recommended repository name: `event-trip-alert`

## Domain candidates

Confirmed domain: `eventtripalert.jp`

Other candidates:

- `eventtripalert.jp`
- `enseialert.jp`
- `livestay.jp`
- `eventstay.jp`

## Concept

ライブ、スポーツ、展示会の開催日から宿、交通、周辺飲食、グッズへつなげる遠征通知サービス。

## Technical Selection

- Frontend: Vite + React 19
- Styling: Plain CSS
- Initial data: Static alert seed records in `src/App.jsx`
- Local state: localStorage for MVP saved alerts and UGC requests
- Notification integrations: LINE Messaging API, X API, transactional email provider, Slack Incoming Webhooks
- Future data layer: Supabase or Cloudflare D1
- SEO/AIO/LLMO: structured data, answer block, FAQ, sitemap, robots and `llms.txt`

## Revenue Paths

- 宿泊送客
- 交通送客
- 飲食店掲載
- グッズ affiliate
- スポンサー枠

## Commands

```bash
npm install
npm run dev
npm run lint
npm run build
```
