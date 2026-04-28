<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreArticleRequest;
use App\Http\Requests\Api\UpdateArticleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Articles\Models\Article;
use Modules\Articles\Resources\ArticleResource;

class ArticleController extends Controller
{
    /**
     * List articles relevant to the authenticated patient.
     */
    public function index(Request $request): JsonResponse
    {
        $audience = $request->query('audience');

        $articles = Article::byNewest()
            ->when($audience, fn($q) => $q->forAudience($audience))
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => ArticleResource::collection($articles),
            'meta'    => [
                'current_page' => $articles->currentPage(),
                'last_page'    => $articles->lastPage(),
                'total'        => $articles->total(),
            ],
        ]);
    }

    /**
     * Show a single article.
     */
    public function show(Article $article): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new ArticleResource($article->load('author')),
        ]);
    }

    /**
     * Create a new article (doctor/admin only).
     */
    public function store(StoreArticleRequest $request): JsonResponse
    {
        $article = Article::create([
            ...$request->safe()->except('cover_image'),
            'created_by' => auth()->id(),
        ]);

        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $article
                ->addMedia($file)
                ->usingName(Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)))
                ->toMediaCollection('cover');
        }

        return response()->json([
            'success' => true,
            'message' => __('Article created successfully.'),
            'data'    => new ArticleResource($article->load('author')),
        ], 201);
    }

    /**
     * Update an existing article (doctor/admin only).
     */
    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        $article->update($request->safe()->except('cover_image'));

        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $article
                ->addMedia($file)
                ->usingName(Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)))
                ->toMediaCollection('cover');
        }

        return response()->json([
            'success' => true,
            'message' => __('Article updated successfully.'),
            'data'    => new ArticleResource($article->load('author')),
        ]);
    }

    /**
     * Delete an article (doctor/admin only).
     */
    public function destroy(Article $article): JsonResponse
    {
        $article->delete();

        return response()->json([
            'success' => true,
            'message' => __('Article deleted successfully.'),
        ]);
    }
}
