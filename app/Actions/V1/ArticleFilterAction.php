<?php

namespace App\Actions\V1;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArticleFilterAction
{
    public function execute(Request $request)
    {
        $page = (int) ($request->query('page') ?? 1);
        $limit = (int) ($request->query('limit') ?? 20);
        $sort = $request->query('sort') ?? 'published_at_desc';

        $sources = $request->query('sources') ?? $request->query('selected_sources') ?? null;
        $categories = $request->query('categories') ?? $request->query('selected_categories') ?? null;
        $authors = $request->query('selected_authors') ?? null;

        $cacheKey = 'articles:'.md5(json_encode([
            'date_from' => $request->query('date_from') ?? null,
            'date_to' => $request->query('date_to') ?? null,
            'sources' => $sources,
            'categories' => $categories,
            'authors' => $authors,
            'sort' => $sort,
            'page' => $page,
            'limit' => $limit,
        ]));

        return Cache::remember($cacheKey, now()->addDay(), function () use ($request, $sources, $categories, $authors, $sort, $page, $limit) {

            $articles = Article::query()
                ->when($request->query('date_from') ?? null, fn ($query, $from) => $query->where('published_at', '>=', $from))
                ->when($request->query('date_to') ?? null, fn ($query, $to) => $query->where('published_at', '<=', $to))
                ->when($sources, fn ($query, $arr) => $query->whereIn('source', (array) $arr))
                ->when($categories, fn ($query, $arr) => $query->whereIn('category', (array) $arr))
                ->when($authors, function ($query, $arr) {
                    return $query->where(fn ($w) => collect($arr)
                        ->reduce(fn ($wq, $a) => $wq->orWhereJsonContains('authors', $a), $w));
                });

            [$column, $direction] = match ($sort) {
                'published_at_asc' => ['published_at', 'asc'],
                'created_at_desc' => ['created_at', 'desc'],
                'created_at_asc' => ['created_at', 'asc'],
                default => ['published_at', 'desc'],
            };

            return $articles->orderBy($column, $direction)
                ->paginate($limit, ['*'], 'page', $page);
        });
    }
}
