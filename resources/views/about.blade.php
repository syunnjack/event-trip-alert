@extends('layouts.app')

@section('title', 'このサイトについて | '.config('app.name'))
@section('description', config('app.name').'の掲載データの出所と、扱っていることの範囲を説明しています。')

@php $meta = \App\Support\Venues::meta(); @endphp

@section('content')
<div class="container my-4" style="max-width: 720px;">
  <nav class="small mb-3"><a href="{{ route('venues.index') }}">トップ</a> <span class="text-muted mx-1">/</span> <span class="text-muted">このサイトについて</span></nav>

  <h1 class="h4 fw-bold mb-4">このサイトについて</h1>

  <section class="mb-4">
    <h2 class="h6">何ができるサイトか</h2>
    <p class="small">
      ライブ・スポーツ・展示会などで遠征するときに、<strong>会場がどこにあり、最寄り駅がどこか</strong>を調べるためのサイトです。
      都道府県と会場の種類から探せます。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h6">扱っていないこと</h2>
    <ul class="small">
      <li><strong>公演・試合の予定は扱っていません。</strong>開催情報は会場や主催者の公式サイトをご覧ください。</li>
      <li>チケットの販売・仲介は行っていません。</li>
      <li>宿や交通の予約も行っていません。</li>
    </ul>
  </section>

  <section class="mb-4">
    <h2 class="h6">データの出所</h2>
    <p class="small">
      会場の名称・場所・収容人数・電話番号・公式サイトは
      <a href="{{ $meta['sourceUrl'] }}" rel="nofollow noopener" target="_blank">{{ $meta['sourceLabel'] }}</a>
      のデータをもとにしています（{{ $meta['confirmedOn'] }} 取得）。
    </p>
    <p class="small">
      最寄り駅は、同じ地図データに登録されている鉄道駅のうち、会場からの直線距離がいちばん短いものを機械的に選んでいます。
      <strong>直線距離であり、実際の徒歩経路の距離や所要時間とは異なります。</strong>
      川や線路をはさんでいる場合、遠回りになることがあります。
    </p>
    <p class="small">
      掲載していない会場もあります（地図データに登録されていない場合）。誤りを見つけた場合は、
      OpenStreetMap 側を直していただくと、次回の取り込みで反映されます。
    </p>
  </section>

  <section class="mb-4">
    <h2 class="h6">更新について</h2>
    <p class="small">
      取り込みスクリプトを流し直すと最新の地図データに更新されます。閉館した会場は、地図データから削除された時点で掲載されなくなります。
    </p>
  </section>

  <a href="{{ route('venues.index') }}" class="d-block text-center text-muted mt-4">トップページに戻る</a>
</div>
@endsection
