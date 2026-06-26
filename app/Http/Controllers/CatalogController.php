<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->string('search'));
        $categorySlug = $request->string('category')->toString();

        $selectedCategory = null;

        $products = Product::query()
            ->with('category.parent:id,name,slug')
            ->available()
            ->search($search)
            ->when($categorySlug !== '', function ($query) use ($categorySlug, &$selectedCategory) {
                $selectedCategory = Category::query()
                    ->active()
                    ->productCategories()
                    ->where('slug', $categorySlug)
                    ->first();

                if ($selectedCategory !== null) {
                    $query->byCategory($selectedCategory);
                }
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'reference' => $product->reference,
                'description' => $product->description,
                'price' => $product->price,
                'available_quantity' => $product->availableQuantity(),
                'category' => [
                    'id' => $product->category?->id,
                    'name' => $product->category?->name,
                    'slug' => $product->category?->slug,
                    'parent_name' => $product->category?->parent?->name,
                ],
                'image_url' => $product->getFirstMediaUrl('images', 'thumb') ?: null,
            ]);

        $categories = Category::query()
            ->active()
            ->productCategories()
            ->with('children:id,parent_id,name,slug')
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get(['id', 'name', 'slug'])
            ->map(fn (Category $category): array => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'children' => $category->children
                    ->sortBy('name')
                    ->values()
                    ->map(fn (Category $child): array => $child->only(['id', 'name', 'slug'])),
            ]);

        return Inertia::render('Catalog/Index', [
            'filters' => [
                'search' => $search,
                'category' => $categorySlug,
            ],
            'selectedCategory' => $selectedCategory?->only(['id', 'name', 'slug']),
            'categories' => $categories,
            'products' => $products,
        ]);
    }

    public function show(Product $product): Response
    {
        $product->load('category.parent:id,name,slug');

        $relatedProducts = Product::query()
            ->with('category:id,name,slug')
            ->available()
            ->whereKeyNot($product->id)
            ->where('category_id', $product->category_id)
            ->limit(4)
            ->get()
            ->map(fn (Product $related): array => [
                'id' => $related->id,
                'name' => $related->name,
                'slug' => $related->slug,
                'price' => $related->price,
                'available_quantity' => $related->availableQuantity(),
                'image_url' => $related->getFirstMediaUrl('images', 'thumb') ?: null,
            ]);

        return Inertia::render('Catalog/Show', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'slug' => $product->slug,
                'reference' => $product->reference,
                'description' => $product->description,
                'price' => $product->price,
                'available_quantity' => $product->availableQuantity(),
                'stock_quantity' => $product->stock_quantity,
                'reserved_quantity' => $product->reserved_quantity,
                'status' => $product->status->value,
                'category' => [
                    'id' => $product->category?->id,
                    'name' => $product->category?->name,
                    'slug' => $product->category?->slug,
                    'parent_name' => $product->category?->parent?->name,
                ],
                'image_url' => $product->getFirstMediaUrl('images') ?: null,
            ],
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
