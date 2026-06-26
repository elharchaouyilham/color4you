<?php

namespace App\Http\Controllers;

use App\Models\SeanceDessin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SeanceDessinController extends Controller
{
    private function checkFormateur()
    {
        if (Auth::user()->status === 'banni' || !Auth::user()->hasRole('Formateur')) {
            abort(403, 'Espace réservé aux formateurs.');
        }
    }

    public function formateurDashboard()
    {
        $this->checkFormateur();
        $seances = SeanceDessin::where('formateur_id', Auth::id())->get();
        return view('formateur.dashboard', compact('seances'));
    }

    public function updateStatus(Request $request, SeanceDessin $seance)
    {
        $this->checkFormateur();
        if ($seance->formateur_id !== Auth::id()) abort(403);

        $request->validate(['action' => 'required|in:accepter,refuser']);
        $seance->update(['status' => $request->action === 'accepter' ? 'Ouverte' : 'Annulée']);
        return back()->with('success', 'Statut de la séance actualisé.');
    }

    public function inscrire(SeanceDessin $seance)
    {
        if (Auth::user()->status === 'banni' || !Auth::user()->hasRole('Client')) abort(403);
        if ($seance->status !== 'Ouverte') return back()->with('error', 'Inscription fermée.');

        try {
            DB::beginTransaction();
            $seanceData = SeanceDessin::lockForUpdate()->find($seance->id);
            $count = $seanceData->participants()->count();

            if ($count >= $seanceData->max_participants) {
                $seanceData->update(['status' => 'Complète']);
                DB::commit();
                return back()->with('error', 'Atelier complet.');
            }

            $seanceData->participants()->attach(Auth::id());

            if (($count + 1) === $seanceData->max_participants) {
                $seanceData->update(['status' => 'Complète']);
            }

            DB::commit();
            return back()->with('success', 'Inscrit avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur.');
        }
    }

    public function exportEmargement(SeanceDessin $seance)
    {
        $this->checkFormateur();
        if ($seance->formateur_id !== Auth::id()) abort(403);

        $participants = $seance->participants()->get();
        $filename = "emargement_{$seance->id}.csv";
        $headers = ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$filename"];

        return response()->stream(function() use($participants) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nom', 'Prénom', 'Email']);
            foreach ($participants as $p) {
                fputcsv($file, [$p->nom, $p->prenom, $p->email]);
            }
            fclose($file);
        }, 200, $headers);
    }
}