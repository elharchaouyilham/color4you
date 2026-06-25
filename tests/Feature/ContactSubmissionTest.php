<?php

namespace Tests\Feature;

use App\Enums\ContactStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_submit_contact_message(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'Visitor Example',
            'email' => 'visitor@example.com',
            'phone' => '+212600000000',
            'subject' => 'Question catalogue',
            'message' => 'Bonjour, je souhaite verifier la disponibilite d un produit du catalogue.',
        ]);

        $response->assertRedirect(route('contact.create'));

        $this->assertDatabaseHas('contacts', [
            'email' => 'visitor@example.com',
            'subject' => 'Question catalogue',
            'status' => ContactStatus::New->value,
        ]);
    }

    public function test_contact_request_requires_valid_payload(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'A',
            'email' => 'not-an-email',
            'subject' => 'Hi',
            'message' => 'short',
        ]);

        $response->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
    }
}
