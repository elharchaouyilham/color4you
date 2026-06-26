<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        $categories = Category::query()
            ->with('parent:id,name')
            ->orderBy('id', 'desc')
            ->get();

        $parentOptions = Category::query()
            ->select('id', 'name', 'type')
            ->whereNull('parent_id')
            ->get();

        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories,
            'parentOptions' => $parentOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'type' => ['required', 'in:product,session'],
            'is_active' => ['required', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        // Prevent duplicate slug within same type
        $count = Category::query()
            ->where('slug', $validated['slug'])
            ->where('type', $validated['type'])
            ->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . time();
        }

        Category::create($validated);

        return back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'type' => ['required', 'in:product,session'],
            'is_active' => ['required', 'boolean'],
        ]);

        if (isset($validated['parent_id']) && (int)$validated['parent_id'] === $category->id) {
            return back()->withErrors(['parent_id' => 'A category cannot be its own parent.']);
        }

        $validated['slug'] = Str::slug($validated['name']);

        // Prevent duplicate slug
        $count = Category::query()
            ->where('slug', $validated['slug'])
            ->where('type', $validated['type'])
            ->where('id', '!=', $category->id)
            ->count();
        if ($count > 0) {
            $validated['slug'] .= '-' . time();
        }

        $category->update($validated);

        return back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists() || $category->drawingSessions()->exists() || Category::where('parent_id', $category->id)->exists()) {
            return back()->with('error', 'Cannot delete category that has subcategories, products, or sessions.');
        }

        $category->delete();

        return back()->with('success', 'Category deleted successfully.');
    }
}
