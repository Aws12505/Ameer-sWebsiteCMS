<?php

use App\Enums\ContactRequestStatus;
use App\Models\ContactRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

test('the inbox can be searched and filtered by status', function () {
    ContactRequest::factory()->create(['name' => 'Jordan Ellis']);
    ContactRequest::factory()->create(['name' => 'Casey Morgan', 'status' => ContactRequestStatus::Done]);

    $this->get('/admin/contact-requests?search=jordan')
        ->assertInertia(fn ($page) => $page->has('requests.data', 1)->where('requests.data.0.name', 'Jordan Ellis'));

    $this->get('/admin/contact-requests?status=done')
        ->assertInertia(fn ($page) => $page->has('requests.data', 1)->where('requests.data.0.name', 'Casey Morgan'));
});

test('the menu shows how many requests are new', function () {
    ContactRequest::factory()->count(2)->create();
    ContactRequest::factory()->create(['status' => ContactRequestStatus::Done]);

    $this->get('/dashboard')->assertInertia(fn ($page) => $page->where('cms.newContactRequests', 2));
});

test('a request can be marked in progress with internal notes', function () {
    $request = ContactRequest::factory()->create();

    $this->put("/admin/contact-requests/{$request->id}", ['status' => 'in_progress', 'notes' => "Called back.\nVisit on Monday."])
        ->assertSessionHasNoErrors();

    expect($request->fresh())
        ->status->toBe(ContactRequestStatus::InProgress)
        ->notes->toBe("Called back.\nVisit on Monday.");
});

test('the requests export as csv without running spreadsheet formulas', function () {
    ContactRequest::factory()->create(['name' => 'Jordan Ellis', 'message' => '=HYPERLINK("http://evil")']);

    $response = $this->get('/admin/contact-requests/export')->assertOk();

    $csv = $response->streamedContent();
    expect($response->headers->get('Content-Disposition'))->toContain('contact-requests-')
        ->and($csv)->toContain('Jordan Ellis')
        ->and($csv)->toContain("'=HYPERLINK")
        ->and($csv)->toContain('New');
});
