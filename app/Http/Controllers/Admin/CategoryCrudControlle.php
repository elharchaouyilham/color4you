<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryCrudController extends Controller
{
    public function index() { if(!Auth::user()->hasRole('Administrateur')) abort(403); $categories = Categorie::all(); return view('admin.categories.index', compact('categories')); }
    public function store(Request $request) { if(!Auth::user()->hasRole('Administrateur')) abort(403); $request->validate(['name' => 'required|unique:categories']); Categorie::create($request->all()); return back(); }
    public function destroy(Categorie $category) { if(!Auth::user()->hasRole('Administrateur')) abort(403); $category->delete(); return back(); }
}