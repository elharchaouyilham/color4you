<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Categorie;
use Illuminate\Http\Request;

class CatalogueController extends Controller
{
    public function index(Request $request)
    {
        $categories = Categorie::all();
        $products = Product::with('categorie')
            ->when($request->categorie, function ($query, $catId) {
                return $query->where('categorie_id', $catId);
            })->get();

        return view('front.catalogue', compact('products', 'categories'));
    }
}