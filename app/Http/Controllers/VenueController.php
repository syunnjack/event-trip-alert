<?php

namespace App\Http\Controllers;

use App\Support\Venues;
use Illuminate\Support\Facades\Cache;

class VenueController extends Controller
{
    public function index()
    {
        return view('venues.index', [
            'areaCounts' => Venues::areaCounts(),
            'kindCounts' => Venues::kindCounts(),
            'total' => Venues::all()->count(),
            'meta' => Venues::meta(),
        ]);
    }

    public function area(string $areaSlug)
    {
        $area = Venues::areaForSlug($areaSlug);

        if ($area === null) {
            abort(404);
        }

        $venues = Venues::inArea($areaSlug);

        if ($venues->isEmpty()) {
            abort(404);
        }

        return view('venues.area', [
            'area' => $area,
            'areaSlug' => $areaSlug,
            'venues' => $venues->sortBy([['kind', 'asc'], ['name', 'asc']])->values(),
            'kindCounts' => Venues::kindCounts(),
            'areaCounts' => Venues::areaCounts(),
            'meta' => Venues::meta(),
        ]);
    }

    public function kind(string $kindSlug)
    {
        $kind = Venues::kindForSlug($kindSlug);

        if ($kind === null) {
            abort(404);
        }

        $venues = Venues::ofKind($kindSlug);

        if ($venues->isEmpty()) {
            abort(404);
        }

        return view('venues.kind', [
            'kind' => $kind,
            'kindSlug' => $kindSlug,
            'venues' => $venues->sortBy([['area', 'asc'], ['name', 'asc']])->values(),
            'meta' => Venues::meta(),
        ]);
    }

    public function show(string $slug)
    {
        $venue = Venues::find($slug);

        if ($venue === null) {
            abort(404);
        }

        $others = Venues::inArea($venue['area_slug'])
            ->where('slug', '!=', $venue['slug'])
            ->take(12)
            ->values();

        return view('venues.show', [
            'venue' => $venue,
            'others' => $others,
            'meta' => Venues::meta(),
        ]);
    }

    public function sitemap()
    {
        $xml = Cache::remember('sitemap-xml', now()->addHour(), fn () => view('sitemap', [
            'venues' => Venues::all(),
            'areaSlugs' => Venues::areaCounts()->pluck('slug'),
            'kindSlugs' => Venues::kindCounts()->pluck('slug')->filter()->values(),
        ])->render());

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
