<?php

use App\Models\ContactRequest;
use App\Models\Project;
use App\Models\Setting;
use App\Models\User;
use App\Support\DefaultContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

function siteSettingsPayload(array $overrides = []): array
{
    Storage::disk('public')->put('uploads/favicon.png', 'png');

    return [
        ...DefaultContent::site(),
        'favicon' => ['path' => 'uploads/favicon.png'],
        'defaultShareImage' => null,
        ...$overrides,
    ];
}

beforeEach(function () {
    Storage::fake('public');
    Setting::query()->create(['key' => Setting::Site, 'value' => [
        ...DefaultContent::site(),
        'favicon' => ['path' => 'uploads/favicon.png', 'sizes' => ['32' => 'favicons/f-32.png']],
        'notificationEmails' => ['owner@example.com'],
    ]]);
});

test('guests are sent to the login page', function (string $url) {
    $this->get($url)->assertRedirect('/login');
})->with(['/dashboard', '/admin/site-settings', '/admin/contact-requests', '/admin/users']);

test('there is no public sign-up', function () {
    $this->get('/register')->assertNotFound();
});

test('a manager cannot manage users', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/admin/users')->assertForbidden();
    $this->post('/admin/users', ['name' => 'X', 'email' => 'x@example.com', 'role' => 'admin', 'password' => 'password'])
        ->assertForbidden();
});

test('an admin can create a manager login', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->post('/admin/users', [
        'name' => 'Sam Manager',
        'email' => 'sam@example.com',
        'role' => 'manager',
        'password' => 'a-long-password',
    ])->assertRedirect('/admin/users');

    $user = User::query()->where('email', 'sam@example.com')->sole();
    expect($user->isAdmin())->toBeFalse()->and($user->email_verified_at)->not->toBeNull();
});

test('an admin cannot remove their own admin role', function () {
    $admin = User::factory()->admin()->create();
    $this->actingAs($admin);

    $this->put("/admin/users/{$admin->id}", ['name' => $admin->name, 'email' => $admin->email, 'role' => 'manager'])
        ->assertSessionHasErrors('role');

    expect($admin->fresh()->isAdmin())->toBeTrue();
});

test('a manager cannot change the notification emails', function () {
    $this->actingAs(User::factory()->create());

    $this->get('/admin/site-settings')->assertInertia(fn ($page) => $page->where('settings.notificationEmails', null));

    $this->put('/admin/site-settings', siteSettingsPayload(['notificationEmails' => ['me@example.com']]))
        ->assertSessionHasErrors('notificationEmails');

    $this->put('/admin/site-settings', siteSettingsPayload(['name' => 'New name']))->assertSessionHasNoErrors();

    expect(Setting::valueOf(Setting::Site))
        ->name->toBe('New name')
        ->notificationEmails->toBe(['owner@example.com']);
});

test('an admin can change the notification emails', function () {
    $this->actingAs(User::factory()->admin()->create());

    $this->put('/admin/site-settings', siteSettingsPayload(['notificationEmails' => ['a@example.com', 'b@example.com']]))
        ->assertSessionHasNoErrors();

    expect(Setting::valueOf(Setting::Site)['notificationEmails'])->toBe(['a@example.com', 'b@example.com']);
});

test('only an admin can delete contact requests or empty the trash', function () {
    $request = ContactRequest::factory()->create();
    $project = tap(Project::factory()->create())->delete();

    $this->actingAs(User::factory()->create());
    $this->delete("/admin/contact-requests/{$request->id}")->assertForbidden();
    $this->delete("/admin/trash/projects/{$project->id}")->assertForbidden();

    $this->actingAs(User::factory()->admin()->create());
    $this->delete("/admin/contact-requests/{$request->id}")->assertRedirect();
    $this->delete("/admin/trash/projects/{$project->id}")->assertRedirect();

    expect(ContactRequest::query()->count())->toBe(0)
        ->and(Project::withTrashed()->count())->toBe(0);
});
