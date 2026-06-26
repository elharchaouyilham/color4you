@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto my-16 bg-white p-8 rounded-2xl shadow-sm border">
    <h2 class="text-2xl font-black text-slate-800 mb-6">Connexion à Color4Y</h2>
    <form action="{{ route('login') }}" method="POST" class="space-y-4">
        @csrf
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Email</label>
            <input type="email" name="email" required class="w-full rounded-xl border bg-slate-50 p-3 text-sm">
        </div>
        <div>
            <label class="block text-xs font-bold text-slate-500 uppercase mb-2">Mot de passe</label>
            <input type="password" name="password" required class="w-full rounded-xl border bg-slate-50 p-3 text-sm">
        </div>
        <button type="submit" class="w-full bg-indigo-600 text-white font-bold py-3 rounded-xl text-sm">Se connecter</button>
    </form>
</div>
@endsection