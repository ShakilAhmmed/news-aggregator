<?php

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed a small, deterministic dataset
    Article::factory()->count(3)->source('guardian')->category('Technology')
        ->published('2025-11-05 10:00:00')->create();

    Article::factory()->count(2)->source('nyt')->category('World')
        ->published('2025-11-03 12:00:00')->create();

    Article::factory()->count(1)->source('newsapi')->category('Sports')
        ->published('2025-11-01 08:00:00')->create();
});

it('returns paginated articles sorted by published_at desc by default', function () {
    $res = $this->getJson('/api/v1/articles?page=1&limit=3');

    $res->assertOk()
        ->assertJsonPath('meta.per_page', 3)
        ->assertJsonPath('meta.current_page', 1)
        ->assertJsonStructure([
            'data' => [
                ['id', 'source', 'title', 'category', 'published_at', 'url'],
            ],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);

    // Ensure desc ordering by published_at (first is the newest)
    $data = $res->json('data');
    expect($data)->toHaveCount(3)
        ->and(strtotime($data[0]['published_at']))->toBeGreaterThanOrEqual(strtotime($data[1]['published_at']));
});

it('filters by date range', function () {
    // Should include items from 2025-11-03 to 2025-11-05, exclude 2025-11-01
    $res = $this->getJson('/api/v1/articles?date_from=2025-11-03&date_to=2025-11-06');

    $res->assertOk();
    $data = $res->json('data');

    // none older than 2025-11-03
    foreach ($data as $row) {
        expect(strtotime($row['published_at']))->toBeGreaterThanOrEqual(strtotime('2025-11-03 00:00:00'));
    }
});

it('filters by source and category', function () {
    $res = $this->getJson('/api/v1/articles?sources[]=guardian&categories[]=Technology');

    $res->assertOk();
    foreach ($res->json('data') as $row) {
        expect($row['source'])->toBe('guardian')
            ->and($row['category'])->toBe('Technology');
    }
});

it('applies user preferences when direct filters are absent (selected_sources, selected_categories)', function () {
    // No "sources" or "categories" query params; only selected_* provided
    $res = $this->getJson('/api/v1/articles?selected_sources[]=nyt&selected_categories[]=World');

    $res->assertOk();
    foreach ($res->json('data') as $row) {
        expect($row['source'])->toBe('nyt')
            ->and($row['category'])->toBe('World');
    }
});

it('paginates with limit and page parameters', function () {
    $res = $this->getJson('/api/v1/articles?page=2&limit=2');

    $res->assertOk()
        ->assertJsonPath('meta.current_page', 2)
        ->assertJsonPath('meta.per_page', 2);
});
