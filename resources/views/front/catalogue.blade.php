@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-col md:flex-row gap-8">
        <aside class="w-full md:w-1/4 bg-white p-6 rounded-2xl shadow-sm border h-fit">
            <h3 class="text-lg font-bold mb-4 text-slate-800">Filtrer</h3>
            <form action="{{ route('catalogue') }}" method="GET" class="space-y-4">
                <select name="categorie" class="w-full rounded-xl border border-slate-200 bg-slate-50 text-sm p-2.5 text-slate-700">
                    <option value="">Toutes les catégories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('categorie') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full bg-indigo-600 text-white font-medium py-2 px-4 rounded-xl text-sm">Appliquer</button>
            </form>
        </aside>

        <main class="w-full md:w-3/4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($products as $product)
                <div class="bg-white rounded-2xl shadow-sm border overflow-hidden flex flex-col justify-between p-5">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wide">{{ $product->categorie->name }}</span>
                        <h4 class="text-lg font-bold text-slate-800 mt-1">{{ $product->nom }}</h4>
                        <p class="text-xl font-black text-indigo-600 my-2">{{ number_format($product->prix, 2) }} DH</p>
                    </div>
                    <div class="mt-4">
                        <div class="text-xs text-slate-500 mb-2">Disponibilité : <span class="font-bold text-slate-700">{{ $product->quantite }} dispo</span></div>
                        @if($product->quantite > 0)
                            <form action="{{ route('reservations.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="hidden" name="date_reservation" value="{{ now()->format('Y-m-d') }}">
                                <button type="submit" class="w-full bg-slate-900 text-white text-sm py-2 rounded-xl font-medium">Réserver</button>
                            </form>
                        @else
                            <button disabled class="w-full bg-slate-100 text-slate-400 text-sm py-2 rounded-xl cursor-not-allowed">Épuisé</button>
                        @endif
                    </div>
                </div>
            @endforeach
        </main>
    </div>
</div>
@endsection