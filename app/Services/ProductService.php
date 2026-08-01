<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    /**
     * List products with filtering and pagination.
     *
     * Common queries are cached for 5 minutes to reduce database load.
     * Cache key varies by filters, page, and per_page so each unique
     * combination gets its own cached result.
     */
    public function listProducts(Request $request): LengthAwarePaginator
    {
        $perPage = min((int) $request->integer('per_page', 12), 100);
        $page = (int) $request->integer('page', 1);

        // Build a deterministic cache key from the request parameters
        $cacheKey = 'products.list.' . md5(serialize([
            'category_id' => $request->category_id,
            'search' => $request->search,
            'min_price' => $request->min_price,
            'max_price' => $request->max_price,
            'per_page' => $perPage,
            'page' => $page,
            'sort' => $request->sort ?? 'latest',
        ]));

        // Cache for 5 minutes — short enough to stay fresh, long enough
        // to absorb traffic spikes on popular filter combinations
        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($request, $perPage) {
            // Select only the columns needed for the listing grid
            // to reduce memory and transfer overhead
            $query = Product::select([
                    'id', 'category_id', 'name', 'slug', 'price',
                    'stock', 'image', 'created_at', 'updated_at',
                ])
                ->with('category:id,name')
                ->latest();

            if ($request->filled('category_id')) {
                $query->where('category_id', (int) $request->category_id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($request->filled('min_price')) {
                $query->where('price', '>=', (float) $request->min_price);
            }

            if ($request->filled('max_price')) {
                $query->where('price', '<=', (float) $request->max_price);
            }

            // Apply sorting
            match ($request->sort) {
                'price_asc'  => $query->orderBy('price'),
                'price_desc' => $query->orderByDesc('price'),
                'name_asc'   => $query->orderBy('name'),
                'name_desc'  => $query->orderByDesc('name'),
                default      => $query->latest(), // created_at DESC
            };

            return $query->paginate($perPage);
        });
    }

    /**
     * Get a single product with its relationships, cached.
     *
     * Cache is invalidated whenever the product is updated or stock changes.
     */
    public function getProduct(Product $product): Product
    {
        return Cache::remember("products.{$product->id}", now()->addHours(2), function () use ($product) {
            return $product->load([
                'category:id,name',
                'reviews' => function ($q) {
                    $q->where('approved', true)
                      ->latest()
                      ->limit(50);
                },
                'reviews.user:id,name,avatar',
                'productImages:id,product_id,path',
            ]);
        });
    }

    /**
     * Get price range across all products (cached, heavy query).
     */
    public function getPriceRange(): array
    {
        return Cache::remember('products.price_range', now()->addHours(1), function () {
            return [
                'min' => (float) Product::min('price') ?? 0,
                'max' => (float) Product::max('price') ?? 999,
            ];
        });
    }

    /**
     * Check if a product has sufficient stock.
     */
    public function hasSufficientStock(Product $product, int $quantity): bool
    {
        return $product->stock >= $quantity;
    }

    /**
     * Decrement product stock.
     */
    public function decrementStock(Product $product, int $quantity): void
    {
        $product->decrement('stock', $quantity);
        $this->clearCache($product);
    }

    /**
     * Increment product stock (for cancellations/returns).
     */
    public function incrementStock(Product $product, int $quantity): void
    {
        $product->increment('stock', $quantity);
        $this->clearCache($product);
    }

    /**
     * Get recommendations for a product (same category).
     */
    public function getRecommendations(Product $product, int $limit = 6): iterable
    {
        return Cache::remember("products.recommendations.{$product->id}", now()->addHours(4), function () use ($product, $limit) {
            return Product::select(['id', 'category_id', 'name', 'slug', 'price', 'stock', 'image'])
                ->where('category_id', $product->category_id)
                ->where('id', '!=', $product->id)
                ->inRandomOrder()
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Clear all caches for a product.
     */
    public function clearCache(Product $product): void
    {
        Cache::forget("products.{$product->id}");
        Cache::forget("products.recommendations.{$product->id}");

        // Best list caches — tags only work on Redis/Memcached,
        // so wrap in try/catch to support database/file drivers gracefully
        try {
            Cache::tags(['products'])->flush();
        } catch (\BadMethodCallException) {
            // Cache driver does not support tags — list caches will
            // expire naturally via their 5-minute TTL
        }
    }
}
