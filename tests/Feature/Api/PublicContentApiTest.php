<?php

use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Review;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Support\DefaultContent;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('drafts are never returned by the public api', function () {
    $live = ServiceCategory::factory()->create(['slug' => 'residential']);
    ServiceCategory::factory()->draft()->create(['slug' => 'secret']);
    Service::factory()->draft()->create()->categories()->attach($live);
    $draftCategory = ProjectCategory::factory()->draft()->create();
    Project::factory()->draft()->create(['main_category_id' => $draftCategory->id]);
    Review::factory()->draft()->create();

    $this->getJson('/api/v1/service-categories')->assertJsonCount(1)->assertJsonPath('0.key', 'residential');
    $this->getJson('/api/v1/service-filters')->assertJsonCount(1);
    $this->getJson('/api/v1/services')->assertExactJson([]);
    $this->getJson('/api/v1/project-categories')->assertExactJson([]);
    $this->getJson('/api/v1/projects')->assertExactJson([]);
    $this->getJson('/api/v1/reviews')->assertExactJson([]);
    $this->getJson('/api/v1/service-categories/secret')->assertNotFound();
});

test('core services follow the manager order with pinned ones first', function () {
    ServiceCategory::factory()->create(['slug' => 'residential']);
    ServiceCategory::factory()->create(['slug' => 'commercial']);
    ServiceCategory::factory()->external()->create(['slug' => 'new-construction', 'is_pinned' => true]);

    $this->getJson('/api/v1/service-categories')
        ->assertOk()
        ->assertJsonPath('0.key', 'new-construction')
        ->assertJsonPath('0.externalUrl', 'https://pnehomes.com/')
        ->assertJsonPath('1.key', 'residential')
        ->assertJsonPath('2.key', 'commercial')
        ->assertJsonMissingPath('0.body');
});

test('a core service page includes its body, gallery and seo', function () {
    ServiceCategory::factory()->create([
        'slug' => 'residential',
        'body' => ['Lead paragraph.', 'Second paragraph.'],
        'gallery' => [['path' => 'seed/one.jpg', 'alt' => 'Bright kitchen']],
        'seo_title' => 'Remodeling',
    ]);

    $this->getJson('/api/v1/service-categories/residential')
        ->assertOk()
        ->assertJsonPath('body', ['Lead paragraph.', 'Second paragraph.'])
        ->assertJsonPath('gallery.0.alt', 'Bright kitchen')
        ->assertJsonPath('seo.title', 'Remodeling')
        ->assertJsonPath('seo.description', '')
        ->assertJsonPath('seo.image', null);

    $this->getJson('/api/v1/service-categories/unknown')->assertNotFound();
});

test('service categories list only published core service keys', function () {
    $live = ServiceCategory::factory()->create(['slug' => 'residential']);
    $draft = ServiceCategory::factory()->draft()->create(['slug' => 'hidden']);
    Service::factory()->create(['slug' => 'kitchen-remodeling'])->categories()->attach([$live->id, $draft->id]);

    $this->getJson('/api/v1/services')
        ->assertJsonPath('0.id', 'kitchen-remodeling')
        ->assertJsonPath('0.categories', ['residential']);
});

test('projects list the main category first and the cover as the first gallery photo', function () {
    $main = ProjectCategory::factory()->create(['slug' => 'restaurants']);
    $extra = ProjectCategory::factory()->create(['slug' => 'cafes']);
    $project = Project::factory()->featured()->create([
        'main_category_id' => $main->id,
        'cover_path' => 'seed/cover.jpg',
        'gallery' => [['path' => 'seed/two.jpg', 'alt' => 'Bar']],
    ]);
    $project->extraCategories()->attach($extra);

    $response = $this->getJson('/api/v1/projects')->assertOk();

    expect($response->json('0.categories'))->toBe(['restaurants', 'cafes'])
        ->and($response->json('0.featured'))->toBeTrue()
        ->and($response->json('0.gallery'))->toHaveCount(2)
        ->and($response->json('0.gallery.0'))->toBe($response->json('0.cover'))
        ->and($response->json('0.gallery.1.alt'))->toBe('Bar');
});

test('projects follow the manager order, newest first by default', function () {
    $older = Project::factory()->create(['slug' => 'older']);
    $newer = Project::factory()->create(['slug' => 'newer']);

    $this->getJson('/api/v1/projects')->assertJsonPath('0.id', 'newer')->assertJsonPath('1.id', 'older');

    Project::reorder([$older->id, $newer->id]);

    $this->getJson('/api/v1/projects')->assertJsonPath('0.id', 'older');
});

test('reviews include generated initials and only a photo when there is one', function () {
    Review::factory()->create(['name' => 'Danielle Okafor', 'photo_path' => null]);

    $this->getJson('/api/v1/reviews')
        ->assertJsonPath('0.initials', 'DO')
        ->assertJsonMissingPath('0.photo');
});

test('site settings build the call link and never expose notification emails', function () {
    Setting::query()->create(['key' => Setting::Site, 'value' => [
        ...DefaultContent::site(),
        'phone' => ['display' => '(614) 483-0331'],
        'notificationEmails' => ['owner@example.com'],
    ]]);

    $response = $this->getJson('/api/v1/site')
        ->assertOk()
        ->assertJsonPath('phone.href', 'tel:+16144830331')
        ->assertJsonPath('cta.label', 'Book an Appointment')
        ->assertJsonMissingPath('notificationEmails');

    expect($response->getContent())->not->toContain('owner@example.com');
});

test('the page endpoints return the starting content', function () {
    $this->getJson('/api/v1/pages/home')
        ->assertOk()
        ->assertJsonPath('hero.headline', ['Built around your vision.', 'Crafted to last.'])
        ->assertJsonCount(4, 'why.points')
        ->assertJsonPath('seo.title', '');

    $this->getJson('/api/v1/pages/services')->assertJsonPath('title', 'Our Services');
    $this->getJson('/api/v1/pages/projects')->assertJsonPath('title', 'Our Projects');
    $this->getJson('/api/v1/pages/contact')->assertJsonCount(3, 'next.steps');
    $this->getJson('/api/v1/pages/about')->assertNotFound();
});

test('settings start with the reviews section switched off', function () {
    $this->getJson('/api/v1/settings')
        ->assertOk()
        ->assertJsonPath('showReviews', false)
        ->assertJsonPath('heroVideo', null);
});

test('responses carry cache headers and answer 304 for a matching etag', function () {
    $response = $this->getJson('/api/v1/pages/services');

    $etag = $response->headers->get('ETag');
    expect($etag)->not->toBeNull()
        ->and($response->headers->get('Cache-Control'))->toContain('public');

    $this->getJson('/api/v1/pages/services', ['If-None-Match' => $etag])->assertStatus(304);
});
