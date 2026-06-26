@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10 grid grid-cols-1 md:grid-cols-2 gap-8">
    <div class="bg-white p-6 rounded-2xl border shadow-sm">
        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Mes Emprunts Matériels</h3>
        @forelse($reservations as $res)
            <div class="flex justify-between py-2 text-sm border-b last:border-0">
                <div>
                    <span class="font-bold">Réservation #{{ $res->id }}</span>
                    <p class="text-xs text-slate-400">Date : {{ $res->date_reservation }}</p>
                </div>
                <span class="text-xs px-2.5 py-1 rounded-full font-bold bg-slate-100">{{ $res->status }}</span>
            </div>
        @empty
            <p class="text-sm text-slate-400">Aucun emprunt enregistré.</p>
        @endforelse
    </div>

    <div class="bg-white p-6 rounded-2xl border shadow-sm">
        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b pb-2">Mes Inscriptions aux Ateliers</h3>
        @forelse($inscriptions as $seance)
            <div class="py-2 text-sm border-b last:border-0">
                <p class="font-semibold text-slate-700">{{ $seance->name }}</p>
                <p class="text-xs text-slate-400">Horaire : {{ $seance->date_heure->format('d/m/Y H:i') }}</p>
            </div>
        @empty
            <p class="text-sm text-slate-400">Inscrit à aucun cours de dessin.</p>
        @endforelse
    </div>
</div>
@endsection