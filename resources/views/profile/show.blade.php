@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto py-10">
    <div class="bg-white p-8 rounded-2xl shadow-sm border">
        <h3 class="text-xl font-bold text-slate-800 mb-6">Modifier mes informations</h3>
        <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-2">Prénom</label>
                <input type="text" name="prenom" value="{{ Auth::user()->prenom }}" class="w-full rounded-xl border p-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-2">Nom</label>
                <input type="text" name="nom" value="{{ Auth::user()->nom }}" class="w-full rounded-xl border p-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-600 mb-2">Téléphone</label>
                <input type="text" name="telephone" value="{{ Auth::user()->telephone }}" class="w-full rounded-xl border p-3 text-sm">
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-2.5 rounded-xl text-sm">Mettre à jour</button>
        </form>
    </div>
</div>
@endsection