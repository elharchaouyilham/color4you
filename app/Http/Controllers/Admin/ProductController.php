<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(): Response
    {
        $products = Product::query()
            ->with('category:id,name')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($product): array => [
                'id' => $product->id,
                'category_id' => $product->category_id,
                'category_name' => $product->category?->name,
                'reference' => $product->reference,
                'name' => $product->name,
                'slug' => $product->slug,
                'description' => $product->description,
                'price' => $product->price,
                'stock_quantity' => $product->stock_quantity,
                'reserved_quantity' => $product->reserved_quantity,
                'available_quantity' => $product->availableQuantity(),
                'status' => $product->status->value,
                'image_url' => $product->getFirstMediaUrl('images') ?: null,
                'thumb_url' => $product->getFirstMediaUrl('images', 'thumb') ?: null,
            ]);

        $categories = Category::productCategories()->active()->get(['id', 'name']);

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'reference' => ['required', 'string', 'unique:products,reference', 'max:100'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        $count = Product::query()->where('slug', $validated['slug'])->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . time();
        }

        $product = Product::create($validated);

        if ($request->hasFile('image')) {
            $product->addMediaFromRequest('image')->toMediaCollection('images');
        }

        return back()->with('success', 'Product created successfully.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'reference' => ['required', 'string', 'max:100', Rule::unique('products', 'reference')->ignore($product->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(ProductStatus::class)],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        
        $count = Product::query()->where('slug', $validated['slug'])->where('id', '!=', $product->id)->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . time();
        }

        $product->update($validated);

        if ($request->hasFile('image')) {
            $product->clearMediaCollection('images');
            $product->addMediaFromRequest('image')->toMediaCollection('images');
        }

        return back()->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->reservations()->active()->exists()) {
            return back()->with('error', 'Cannot delete a product with active reservations.');
        }

        $product->delete();

        return back()->with('success', 'Product deleted successfully.');
    }
}
