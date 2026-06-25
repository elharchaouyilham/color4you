<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Contact/Index');
    }

    public function store(ContactRequest $request)
    {
        Contact::query()->create($request->validated());

        return to_route('contact.create')->with('success', 'Votre message a ete envoye.');
    }
}
