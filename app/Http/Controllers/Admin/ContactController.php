<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContactStatus;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        // Mark new messages as read when index page is visited
        Contact::query()->where('status', ContactStatus::New)->update([
            'status' => ContactStatus::Read,
            'read_at' => now(),
        ]);

        $contacts = Contact::query()
            ->orderByRaw("CASE WHEN status = 'new' THEN 1 WHEN status = 'read' THEN 2 ELSE 3 END")
            ->latest('created_at')
            ->get()
            ->map(fn ($contact): array => [
                'id' => $contact->id,
                'name' => $contact->name,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'subject' => $contact->subject,
                'message' => $contact->message,
                'status' => $contact->status->value,
                'created_at' => $contact->created_at?->toIso8601String(),
                'read_at' => $contact->read_at?->toIso8601String(),
            ]);

        return Inertia::render('Admin/Contacts/Index', [
            'contacts' => $contacts,
        ]);
    }

    public function resolve(Contact $contact): RedirectResponse
    {
        $contact->update([
            'status' => ContactStatus::Closed,
        ]);

        return back()->with('success', 'Message resolved and closed.');
    }
}
