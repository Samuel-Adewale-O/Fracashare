<?php

namespace App\Http\Controllers;

use App\Models\FaqCategory;
use App\Models\Faq;
use App\Models\HelpArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HelpCenterController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:admin'])->only([
            'storeFaqCategory', 'updateFaqCategory', 'deleteFaqCategory',
            'storeFaq', 'updateFaq', 'deleteFaq',
            'storeArticle', 'updateArticle', 'deleteArticle'
        ]);
    }

    public function categories()
    {
        $categories = Cache::remember('faq_categories', 3600, function () {
            return FaqCategory::active()
                ->withCount(['faqs', 'articles'])
                ->orderBy('order')
                ->get();
        });

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    public function faqs(Request $request)
    {
        $faqs = Faq::active()
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->with('category:id,name,slug')
            ->orderBy('order')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $faqs
        ]);
    }

    public function articles(Request $request)
    {
        $articles = HelpArticle::active()
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->featured, fn($q) => $q->featured())
            ->when($request->search, function($q, $search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhereJsonContains('tags', $search);
            })
            ->with('category:id,name,slug')
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $articles
        ]);
    }

    public function showArticle(HelpArticle $article)
    {
        if ($article->is_active) {
            $article->incrementViews();
            $article->load('category:id,name,slug');

            return response()->json([
                'status' => 'success',
                'data' => $article
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Article not found'
        ], 404);
    }

    public function storeFaqCategory(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0'
        ]);

        $category = FaqCategory::create($request->all());
        Cache::forget('faq_categories');

        return response()->json([
            'status' => 'success',
            'message' => 'FAQ category created successfully',
            'data' => $category
        ], 201);
    }

    public function updateFaqCategory(FaqCategory $category, Request $request)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean'
        ]);

        $category->update($request->all());
        Cache::forget('faq_categories');

        return response()->json([
            'status' => 'success',
            'message' => 'FAQ category updated successfully',
            'data' => $category
        ]);
    }

    public function storeFaq(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:faq_categories,id',
            'question' => 'required|string',
            'answer' => 'required|string',
            'order' => 'nullable|integer|min:0'
        ]);

        $faq = Faq::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'FAQ created successfully',
            'data' => $faq->load('category:id,name,slug')
        ], 201);
    }

    public function updateFaq(Faq $faq, Request $request)
    {
        $request->validate([
            'category_id' => 'sometimes|required|exists:faq_categories,id',
            'question' => 'sometimes|required|string',
            'answer' => 'sometimes|required|string',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean'
        ]);

        $faq->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'FAQ updated successfully',
            'data' => $faq->load('category:id,name,slug')
        ]);
    }

    public function storeArticle(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:faq_categories,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'tags' => 'nullable|array',
            'is_featured' => 'nullable|boolean'
        ]);

        $article = HelpArticle::create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Help article created successfully',
            'data' => $article->load('category:id,name,slug')
        ], 201);
    }

    public function updateArticle(HelpArticle $article, Request $request)
    {
        $request->validate([
            'category_id' => 'sometimes|required|exists:faq_categories,id',
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'tags' => 'nullable|array',
            'is_featured' => 'nullable|boolean',
            'is_active' => 'sometimes|boolean'
        ]);

        $article->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Help article updated successfully',
            'data' => $article->load('category:id,name,slug')
        ]);
    }
}