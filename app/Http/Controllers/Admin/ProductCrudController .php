<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductCrudController extends Controller
{
    public function index() { if(!Auth::user()->hasRole('Administrateur')) abort(403); $products = Product::with('categorie')->get(); return view('admin.products.index', compact('products')); }
    public function create() { if(!Auth::user()->hasRole('Administrateur')) abort(403); $categories = Categorie::all(); return view('admin.products.create', compact('categories')); }
    public function store(Request $request)
    {
        if(!Auth::user()->hasRole('Administrateur')) abort(403);
        $request->validate(['nom' => 'required', 'categorie_id' => 'required', 'prix' => 'required|numeric', 'quantite' => 'required|integer']);
        Product::create($request->all());
        return redirect()->route('admin.products.index');
    }
    public function destroy(Product $product) { if(!Auth::user()->hasRole('Administrateur')) abort(403); $product->delete(); return back(); }
}