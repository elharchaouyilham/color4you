@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">
    <h2 class="text-2xl font-black mb-6 text-slate-800">Tableau de bord Enseignant</h2>
    <div class="bg-white rounded-2xl border overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50 text-xs font-bold text-slate-500 uppercase border-b">
                    <th class="p-4">Séance</th>
                    <th class="p-4">Date & Heure</th>
                    <th class="p-4">Statut</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm text-slate-600">
                @foreach($seances as $seance)
                    <tr>
                        <td class="p-4 font-bold text-slate-800">{{ $seance->name }}</td>
                        <td class="p-4">{{ $seance->date_heure->format('d/m/Y H:i') }}</td>
                        <td class="p-4"><span class="px-2 py-0.5 rounded text-xs bg-slate-100 font-medium">{{ $seance->status }}</span></td>
                        <td class="p-4 text-right flex justify-end gap-2">
                            @if($seance->status === 'En attente du formateur')
                                <form action="{{ route('formateur.seances.status', $seance->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" name="action" value="accepter" class="bg-emerald-600 text-white text-xs px-3 py-1.5 rounded-lg font-bold">Accepter</button>
                                    <button type="submit" name="action" value="refuser" class="bg-rose-600 text-white text-xs px-3 py-1.5 rounded-lg font-bold">Refuser</button>
                                </form>
                            @else
                                <a href="{{ route('formateur.seances.export', $seance->id) }}" class="text-xs bg-slate-900 text-white px-3 py-1.5 rounded-lg font-bold">Exporter l'émargement</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection