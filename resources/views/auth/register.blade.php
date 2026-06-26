@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto my-16 bg-white p-8 rounded-2xl shadow-sm border">
    <h2 class="text-2xl font-black text-slate-800 mb-6">Créer un compte</h2>
    <form action="{{ route('register') }}" method="POST" class="space-y-4">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Prénom</label>
                <input type="text" name="prenom" required class="w-full rounded-xl border bg-slate-50 p-2.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Nom</label>
                <input type="text" name="nom" required class="w-full rounded-xl border bg-slate-50 p-2.5 text-sm">
            </div>
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Email</label>
            <input type="email" name="email" required class="w-full rounded-xl border bg-slate-50 p-2.5 text-sm">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Mot de passe</label>
            <input type="password" name="password" required class="w-full rounded-xl border bg-slate-50 p-2.5 text-sm">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" required class="w-full rounded-xl border bg-slate-50 p-2.5 text-sm">
        </div>
        <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl text-sm">S'enregistrer</button>
    </form>
</div>
@endsection