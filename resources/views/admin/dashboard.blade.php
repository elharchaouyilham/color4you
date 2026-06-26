@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
        <div class="bg-white p-5 rounded-2xl border shadow-sm"><span class="text-xs text-slate-400 font-bold uppercase">Volume Catalogue</span><p class="text-2xl font-black text-slate-800">{{ $totalProducts }}</p></div>
        <div class="bg-white p-5 rounded-2xl border shadow-sm"><span class="text-xs text-slate-400 font-bold uppercase">Attentes de validation</span><p class="text-2xl font-black text-amber-600">{{ $pendingReservationsCount }}</p></div>
        <div class="bg-white p-5 rounded-2xl border shadow-sm"><span class="text-xs text-slate-400 font-bold uppercase">Utilisateurs actifs</span><p class="text-2xl font-black text-emerald-600">{{ $activeUsersCount }}</p></div>
    </div>

    <div class="bg-white rounded-2xl border overflow-hidden shadow-sm">
        <div class="p-5 border-b"><h3 class="font-bold text-slate-800">Modération Globale des Flux de Sortie</h3></div>
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-slate-50 text-xs font-bold text-slate-500 uppercase border-b">
                    <th class="p-4">Adhérent</th>
                    <th class="p-4">Statut</th>
                    <th class="p-4 text-right">Actions Métier</th>
                </tr>
            </thead>
            <tbody class="divide-y text-slate-600">
                @foreach($reservations as $res)
                    <tr>
                        <td class="p-4 font-semibold text-slate-800">{{ $res->user->prenom }} {{ $res->user->nom }}</td>
                        <td class="p-4"><span class="text-xs font-bold">{{ $res->status }}</span></td>
                        <td class="p-4 text-right">
                            @if($res->status === 'En attente')
                                <form action="{{ route('admin.reservations.confirm', $res->id) }}" method="POST" class="inline">@csrf<button class="bg-indigo-600 text-white text-xs px-3 py-1.5 rounded-lg font-bold">Valider Sortie</button></form>
                            @elseif($res->status === 'Confirmée')
                                <form action="{{ route('admin.reservations.complete', $res->id) }}" method="POST" class="inline">@csrf<button class="bg-emerald-600 text-white text-xs px-3 py-1.5 rounded-lg font-bold">Enregistrer Retour</button></form>
                            @else
                                <span class="text-xs text-slate-400 italic">Terminé</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection