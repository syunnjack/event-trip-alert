<?php

namespace Tests\Feature;

use App\Support\Venues;
use Tests\TestCase;

class VenuePagesTest extends TestCase
{
    public function test_トップページに会場数と種類が出る(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee(number_format(Venues::all()->count()))
            ->assertSee('都道府県から探す');
    }

    public function test_都道府県ページに会場が並ぶ(): void
    {
        $area = Venues::areaCounts()->first();

        $this->get('/areas/'.$area['slug'])
            ->assertOk()
            ->assertSee($area['area']);
    }

    public function test_会場ページに最寄り駅と出典が出る(): void
    {
        $venue = Venues::all()->firstWhere('nearestStation', '!=', null);

        $this->get('/venues/'.$venue['slug'])
            ->assertOk()
            ->assertSee($venue['name'])
            ->assertSee($venue['nearestStation'])
            ->assertSee('OpenStreetMap');
    }

    public function test_知らないURLは404になる(): void
    {
        $this->get('/areas/nowhere')->assertNotFound();
        $this->get('/kinds/nowhere')->assertNotFound();
        $this->get('/venues/tokyo-n1')->assertNotFound();
    }

    public function test_企画書の文言が残っていない(): void
    {
        $pages = ['/', '/about'];

        foreach (Venues::areaCounts()->take(3) as $area) {
            $pages[] = '/areas/'.$area['slug'];
        }

        // 以前の画面には「収益導線」「技術選定」「alert seed」といった
        // 運営側のメモとダミーデータが出ていた。二度と出さない。
        foreach ($pages as $path) {
            $response = $this->get($path);
            $response->assertOk();

            foreach (['収益導線', '技術選定', 'seed', 'Supabase', 'LLMO'] as $word) {
                $response->assertDontSee($word);
            }
        }
    }

    public function test_サイトマップに会場が載る(): void
    {
        $venue = Venues::all()->first();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee(route('venues.show', $venue['slug']), false);
    }

    public function test_robots_txtがサイトマップを案内する(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap: '.url('/sitemap.xml'), false);
    }
}
