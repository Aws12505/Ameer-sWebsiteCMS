<?php

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Storage::disk('public')->put('uploads/photo.jpg', 'photo');
    $this->actingAs(User::factory()->create());
});

function servicePayload(array $overrides = []): array
{
    return [
        'slug' => 'decks',
        'title' => 'Decks',
        'description' => 'New decks and repairs.',
        'category_ids' => [ServiceCategory::factory()->create()->id],
        'includes' => ['Design', 'Build'],
        'image' => ['path' => 'uploads/photo.jpg', 'alt' => 'A new wooden deck'],
        'published' => true,
        ...$overrides,
    ];
}

test('every admin screen renders', function (string $url) {
    ServiceCategory::factory()->create();

    $this->get($url)->assertOk();
})->with([
    '/dashboard',
    '/admin/site-settings',
    '/admin/hero-settings',
    '/admin/pages/home',
    '/admin/pages/services',
    '/admin/pages/projects',
    '/admin/pages/contact',
    '/admin/service-categories',
    '/admin/service-categories/create',
    '/admin/services',
    '/admin/services/create',
    '/admin/project-categories',
    '/admin/project-categories/create',
    '/admin/projects',
    '/admin/projects/create',
    '/admin/reviews',
    '/admin/reviews/create',
    '/admin/contact-requests',
    '/admin/trash',
]);

test('a service is created with its core services and the editor recorded', function () {
    $this->post('/admin/services', servicePayload())->assertRedirect();

    $service = Service::query()->with('categories')->sole();
    expect($service->slug)->toBe('decks')
        ->and($service->categories)->toHaveCount(1)
        ->and($service->updated_by)->toBe(auth()->id())
        ->and($service->first_published_at)->not->toBeNull();
});

test('a photo cannot be saved without a short description', function () {
    $this->post('/admin/services', servicePayload(['image' => ['path' => 'uploads/photo.jpg', 'alt' => '']]))
        ->assertSessionHasErrors(['image.alt' => 'Please add a short description of the photo.']);

    expect(Service::query()->count())->toBe(0);
});

test('a photo path must be a file that was uploaded', function () {
    $this->post('/admin/services', servicePayload(['image' => ['path' => '../.env', 'alt' => 'Sneaky']]))
        ->assertSessionHasErrors('image.path');
});

test('a service needs at least one core service', function () {
    $this->post('/admin/services', servicePayload(['category_ids' => []]))->assertSessionHasErrors('category_ids');
});

test('text fields refuse line breaks', function () {
    $this->post('/admin/services', servicePayload(['title' => "Two\nlines"]))->assertSessionHasErrors('title');
});

test('the key is locked after the first publish', function () {
    $service = Service::factory()->create(['slug' => 'decks']);
    $service->categories()->attach(ServiceCategory::factory()->create());

    $this->put("/admin/services/{$service->id}", servicePayload(['slug' => 'new-decks']))
        ->assertSessionHasErrors('slug');

    expect($service->fresh()->slug)->toBe('decks');
});

test('a draft key can still change', function () {
    $service = Service::factory()->draft()->create(['slug' => 'decks']);

    $this->put("/admin/services/{$service->id}", servicePayload(['slug' => 'new-decks', 'published' => false]))
        ->assertSessionHasNoErrors();

    expect($service->fresh()->slug)->toBe('new-decks');
});

test('only three projects can be featured', function () {
    Project::factory()->featured()->count(3)->create();
    $category = ProjectCategory::factory()->create();

    $this->post('/admin/projects', [
        'slug' => 'cafe',
        'title' => 'Café',
        'location' => 'Columbus, OH',
        'description' => 'A café build-out.',
        'main_category_id' => $category->id,
        'extra_category_ids' => [],
        'featured' => true,
        'cover' => ['path' => 'uploads/photo.jpg', 'alt' => 'Café counter'],
        'gallery' => [],
        'published' => true,
    ])->assertSessionHasErrors('featured');
});

test('only four core services can show on the home page', function () {
    ServiceCategory::factory()->count(4)->create(['show_on_home' => true]);
    $category = ServiceCategory::factory()->create(['show_on_home' => false]);

    $this->put("/admin/service-categories/{$category->id}", [
        'slug' => $category->slug,
        'title' => $category->title,
        'filter_label' => $category->filter_label,
        'summary' => $category->summary,
        'image' => ['path' => 'uploads/photo.jpg', 'alt' => 'A kitchen'],
        'body' => ['First.', 'Second.'],
        'gallery' => [],
        'external_url' => null,
        'show_on_home' => true,
        'published' => true,
    ])->assertSessionHasErrors('show_on_home');
});

test('an external core service needs no page text', function () {
    $this->post('/admin/service-categories', [
        'slug' => 'new-construction',
        'title' => 'New Construction',
        'filter_label' => 'New Construction',
        'summary' => 'For this service, contact PNE Homes.',
        'image' => ['path' => 'uploads/photo.jpg', 'alt' => 'New house'],
        'body' => [],
        'gallery' => [],
        'external_url' => 'https://pnehomes.com/',
        'published' => true,
    ])->assertSessionHasNoErrors();

    expect(ServiceCategory::query()->sole()->external_url)->toBe('https://pnehomes.com/');
});

test('a project category used by projects cannot be deleted', function () {
    $category = ProjectCategory::factory()->create();
    Project::factory()->count(3)->create(['main_category_id' => $category->id]);

    $this->delete("/admin/project-categories/{$category->id}")->assertRedirect();

    expect($category->fresh()->trashed())->toBeFalse();
});

test('a core service used by services cannot be deleted', function () {
    $category = ServiceCategory::factory()->create();
    Service::factory()->create()->categories()->attach($category);

    $this->delete("/admin/service-categories/{$category->id}");

    expect($category->fresh()->trashed())->toBeFalse();
});

test('deleted items go to the trash and can be restored', function () {
    $project = Project::factory()->create();

    $this->delete("/admin/projects/{$project->id}");
    expect($project->fresh()->trashed())->toBeTrue();

    $this->post("/admin/trash/projects/{$project->id}/restore");
    expect($project->fresh()->trashed())->toBeFalse();
});

test('trashed items older than the trash period are pruned', function () {
    $old = Project::factory()->create();
    $recent = Project::factory()->create();
    $old->delete();
    $recent->delete();
    $old->forceFill(['deleted_at' => now()->subDays(31)])->saveQuietly();

    $this->artisan('model:prune', ['--model' => [Project::class]]);

    expect(Project::withTrashed()->find($old->id))->toBeNull()
        ->and(Project::withTrashed()->find($recent->id))->not->toBeNull();
});

test('re-ordering a filtered group keeps the other services in place', function () {
    $residential = ServiceCategory::factory()->create();
    [$a, $b, $c, $d] = Service::factory()->count(4)->create();
    $residential->services()->attach([$a->id, $c->id]);

    $this->put('/admin/services/reorder', ['ids' => [$c->id, $a->id]]);

    expect(Service::query()->ordered()->pluck('id')->all())->toBe([$c->id, $b->id, $a->id, $d->id]);
});

test('the home page text is saved', function () {
    $page = Setting::valueOf(Setting::HomePage);
    $page['hero']['headline'] = ['New headline.', ''];
    $page['about']['image'] = ['path' => 'uploads/photo.jpg', 'alt' => 'Our team'];

    $this->put('/admin/pages/home', $page)->assertSessionHasNoErrors();

    $this->getJson('/api/v1/pages/home')
        ->assertJsonPath('hero.headline', ['New headline.'])
        ->assertJsonPath('about.image.alt', 'Our team');
});

test('the home page needs exactly four reasons', function () {
    $page = Setting::valueOf(Setting::HomePage);
    $page['about']['image'] = ['path' => 'uploads/photo.jpg', 'alt' => 'Our team'];
    array_pop($page['why']['points']);

    $this->put('/admin/pages/home', $page)->assertSessionHasErrors('why.points');
});

test('the hero slider needs at least two photos', function () {
    $this->put('/admin/hero-settings', [
        'showReviews' => true,
        'heroMode' => 'slider',
        'heroSlides' => [['path' => 'uploads/photo.jpg', 'alt' => 'House']],
        'heroVideo' => null,
    ])->assertSessionHasErrors('heroSlides');

    $this->put('/admin/hero-settings', [
        'showReviews' => true,
        'heroMode' => 'image',
        'heroSlides' => [['path' => 'uploads/photo.jpg', 'alt' => 'House']],
        'heroVideo' => null,
    ])->assertSessionHasNoErrors();

    $this->getJson('/api/v1/settings')->assertJsonPath('showReviews', true)->assertJsonPath('heroMode', 'image');
});

test('video mode needs a video and a poster', function () {
    $this->put('/admin/hero-settings', [
        'showReviews' => false,
        'heroMode' => 'video',
        'heroSlides' => [],
        'heroVideo' => null,
    ])->assertSessionHasErrors('heroVideo');
});
